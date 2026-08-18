#!/usr/bin/env python3
from __future__ import annotations

import json
import os
import re
import subprocess
import sys
from pathlib import Path, PurePosixPath
from typing import Iterable, Optional, Sequence, Tuple

SUPPORTED_SOURCES = {"startup", "resume", "clear", "compact"}
MAX_CHANGED_PATHS = 20
MAX_PENDING_STEPS = 5
MAX_CONTEXT_CHARS = 6000
GIT_TIMEOUT_SECONDS = 2
SENSITIVE_SUBSTRINGS = (
    ".env",
    "wp-config.php",
    "id_rsa",
    "id_ed25519",
    ".pem",
    ".key",
    ".p12",
    ".pfx",
    "credential",
    "secret",
)
SENSITIVE_SUFFIXES = (".sql", ".dump", ".backup")

StatusEntry = Tuple[str, str, str]


def run_git(directory: Path, *args: str) -> Optional[str]:
    try:
        result = subprocess.run(
            ["git", "-C", str(directory), *args],
            stdout=subprocess.PIPE,
            stderr=subprocess.DEVNULL,
            timeout=GIT_TIMEOUT_SECONDS,
            check=False,
        )
    except (OSError, subprocess.TimeoutExpired):
        return None

    if result.returncode != 0:
        return None

    try:
        return result.stdout.decode("utf-8")
    except UnicodeDecodeError:
        return None


def effective_cwd(raw_cwd: object) -> Path:
    if isinstance(raw_cwd, str) and raw_cwd.strip():
        try:
            candidate = Path(raw_cwd).expanduser()
            if candidate.is_dir():
                return candidate.resolve()
        except (OSError, RuntimeError):
            pass

    try:
        return Path.cwd().resolve()
    except (OSError, RuntimeError):
        return Path.cwd()


def repository_root(cwd: Path) -> Optional[Path]:
    output = run_git(cwd, "rev-parse", "--show-toplevel")
    if output is None:
        return None

    value = output.strip()
    if not value:
        return None

    try:
        root = Path(value).resolve()
    except (OSError, RuntimeError):
        return None

    return root if root.is_dir() else None


def parse_status_porcelain(raw: str) -> list[StatusEntry]:
    parts = raw.split("\0")
    entries: list[StatusEntry] = []
    index = 0

    while index < len(parts):
        record = parts[index]
        index += 1
        if not record:
            continue
        if len(record) < 4 or record[2] != " ":
            continue

        staged_code = record[0]
        unstaged_code = record[1]
        path = record[3:]

        if staged_code in {"R", "C"} and index < len(parts):
            index += 1

        entries.append((staged_code, unstaged_code, path))

    return entries


def status_entries(root: Path) -> Optional[list[StatusEntry]]:
    output = run_git(
        root,
        "status",
        "--porcelain=v1",
        "-z",
        "--untracked-files=all",
    )
    return None if output is None else parse_status_porcelain(output)


def status_counts(entries: Sequence[StatusEntry]) -> dict[str, int]:
    staged = 0
    unstaged = 0
    untracked = 0
    deleted = 0

    for index_code, worktree_code, _path in entries:
        if index_code == "?" and worktree_code == "?":
            untracked += 1
            continue
        if index_code not in {" ", "?"}:
            staged += 1
        if worktree_code not in {" ", "?"}:
            unstaged += 1
        if index_code == "D" or worktree_code == "D":
            deleted += 1

    return {
        "staged": staged,
        "unstaged": unstaged,
        "untracked": untracked,
        "deleted": deleted,
    }


def unique_status_paths(entries: Sequence[StatusEntry]) -> list[str]:
    seen: set[str] = set()
    paths: list[str] = []
    for _index_code, _worktree_code, path in entries:
        if path not in seen:
            seen.add(path)
            paths.append(path)
    return paths


def is_sensitive_path(path: str) -> bool:
    normalized = path.replace("\\", "/").lower()
    if any(token in normalized for token in SENSITIVE_SUBSTRINGS):
        return True
    return normalized.endswith(SENSITIVE_SUFFIXES)


def relative_path(root: Path, path: Path) -> Optional[str]:
    try:
        return path.resolve().relative_to(root.resolve()).as_posix()
    except (OSError, RuntimeError, ValueError):
        return None


def applicable_agents_files(root: Path, cwd: Path) -> list[str]:
    results: list[str] = []
    root_agents = root / "AGENTS.md"
    if root_agents.is_file():
        results.append("AGENTS.md")

    try:
        current = cwd.resolve()
        root_resolved = root.resolve()
        current.relative_to(root_resolved)
    except (OSError, RuntimeError, ValueError):
        return results

    nearest: Optional[Path] = None
    probe = current
    while True:
        candidate = probe / "AGENTS.md"
        if candidate.is_file() and candidate != root_agents:
            nearest = candidate
            break
        if probe == root_resolved:
            break
        probe = probe.parent

    if nearest is not None:
        rel = relative_path(root, nearest)
        if rel and rel not in results:
            results.append(rel)

    return results[:2]


def existing_markdown_files(directory: Path, excluded_names: set[str]) -> list[Path]:
    if not directory.is_dir():
        return []

    try:
        candidates = [
            path
            for path in directory.rglob("*.md")
            if path.is_file() and path.name not in excluded_names
        ]
    except (OSError, PermissionError):
        return []

    return candidates


def newest_path(paths: Iterable[Path]) -> Optional[Path]:
    ranked: list[tuple[int, str, Path]] = []
    for path in paths:
        try:
            ranked.append((path.stat().st_mtime_ns, path.as_posix(), path))
        except (OSError, PermissionError):
            continue
    if not ranked:
        return None
    ranked.sort(reverse=True)
    return ranked[0][2]


def select_markdown_candidate(
    root: Path,
    relative_directory: str,
    entries: Optional[Sequence[StatusEntry]],
    excluded_names: set[str],
) -> Optional[str]:
    directory = root / relative_directory
    candidates = existing_markdown_files(directory, excluded_names)
    if not candidates:
        return None

    changed: list[Path] = []
    if entries is not None:
        changed_paths = set(unique_status_paths(entries))
        for path in candidates:
            rel = relative_path(root, path)
            if rel in changed_paths:
                changed.append(path)

    selected = newest_path(changed) or newest_path(candidates)
    return None if selected is None else relative_path(root, selected)


def pending_plan_steps(plan_path: Optional[Path]) -> list[str]:
    if plan_path is None or not plan_path.is_file():
        return []

    try:
        lines = plan_path.read_text(encoding="utf-8").splitlines()
    except (OSError, PermissionError, UnicodeDecodeError):
        return []

    pending: list[str] = []
    for line in lines:
        if not line.startswith("- [ ]"):
            continue
        title = line[5:].strip()
        if title:
            pending.append(title)
        if len(pending) == MAX_PENDING_STEPS:
            break
    return pending


def bounded_context(lines: Sequence[str]) -> str:
    suffix = "- Context truncated"
    output: list[str] = []

    for line in lines:
        candidate = "\n".join([*output, line])
        if len(candidate) <= MAX_CONTEXT_CHARS:
            output.append(line)
            continue

        with_suffix = "\n".join([*output, suffix])
        while output and len(with_suffix) > MAX_CONTEXT_CHARS:
            output.pop()
            with_suffix = "\n".join([*output, suffix])
        if len(with_suffix) <= MAX_CONTEXT_CHARS:
            output.append(suffix)
        break

    return "\n".join(output)


def build_context(payload: dict[str, object]) -> Optional[str]:
    cwd = effective_cwd(payload.get("cwd"))
    root = repository_root(cwd)
    if root is None:
        return None

    lines = ["Repository context", f"- Root: {root}"]
    cwd_rel = relative_path(root, cwd)
    lines.append(f"- Working directory: {cwd_rel or '.'}")

    branch = run_git(root, "branch", "--show-current")
    if branch is not None:
        branch_name = branch.strip()
        lines.append(f"- Branch: {branch_name}" if branch_name else "- Branch: detached HEAD")

    entries = status_entries(root)
    if entries is not None:
        if entries:
            counts = status_counts(entries)
            lines.append(
                "- Worktree: dirty — "
                f"{counts['staged']} staged, "
                f"{counts['unstaged']} unstaged, "
                f"{counts['untracked']} untracked, "
                f"{counts['deleted']} deleted"
            )
        else:
            lines.append("- Worktree: clean")

        all_paths = unique_status_paths(entries)
        visible_paths = [path for path in all_paths if not is_sensitive_path(path)]
        omitted_sensitive = len(all_paths) - len(visible_paths)
        shown = visible_paths[:MAX_CHANGED_PATHS]
        if shown:
            lines.append("- Changed paths:")
            lines.extend(f"  - {path}" for path in shown)
        remaining = len(visible_paths) - len(shown)
        if remaining > 0:
            lines.append(f"  - ... and {remaining} more")
        if omitted_sensitive:
            lines.append(f"- Sensitive paths omitted: {omitted_sensitive}")

    agents = applicable_agents_files(root, cwd)
    if agents:
        lines.append("- Applicable instructions:")
        lines.extend(f"  - {path}" for path in agents)

    spec = select_markdown_candidate(
        root,
        "docs/specs",
        entries,
        {"README.md", "TEMPLATE.md"},
    )
    if spec:
        lines.append(f"- Active SPEC candidate: {spec}")

    plan = select_markdown_candidate(
        root,
        "docs/superpowers/plans",
        entries,
        set(),
    )
    if plan:
        lines.append(f"- Active plan candidate: {plan}")
        steps = pending_plan_steps(root / PurePosixPath(plan))
        if steps:
            lines.append("- Pending plan steps:")
            lines.extend(f"  - {step}" for step in steps)

    if entries:
        lines.append("- Validation: required before claiming completion")

    return bounded_context(lines)


def main() -> int:
    try:
        payload = json.load(sys.stdin)
        if not isinstance(payload, dict):
            return 0
        if payload.get("hook_event_name") != "SessionStart":
            return 0
        if payload.get("source") not in SUPPORTED_SOURCES:
            return 0

        context = build_context(payload)
        if not context:
            return 0

        json.dump(
            {
                "hookSpecificOutput": {
                    "hookEventName": "SessionStart",
                    "additionalContext": context,
                }
            },
            sys.stdout,
            ensure_ascii=False,
        )
        return 0
    except Exception:
        return 0


if __name__ == "__main__":
    raise SystemExit(main())
