#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path
from typing import Iterable, List, Optional, Sequence

SUPPORTED_TOOLS = {"apply_patch", "Edit", "Write"}
PATH_KEYS = ("path", "file_path", "filePath", "files", "paths")
PATCH_HEADER_PATTERNS = (
    re.compile(r"^\*\*\* (?:Update|Add|Delete) File: (.+)$"),
    re.compile(r"^\+\+\+ b/(.+)$"),
)
CONFLICT_MARKER = re.compile(r"(?:<{7}(?: .*)?|={7}|>{7}(?: .*)?)")
TRAILING_WHITESPACE = re.compile(r"[ \t]+$")
MAX_DIAGNOSTICS = 20


def extract_string_values(value: object) -> List[str]:
    if isinstance(value, str):
        return [value]
    if isinstance(value, list):
        return [item for item in value if isinstance(item, str)]
    return []


def extract_patch_paths(command: str) -> List[str]:
    paths: List[str] = []
    for line in command.splitlines():
        for pattern in PATCH_HEADER_PATTERNS:
            match = pattern.match(line)
            if match:
                paths.append(match.group(1).strip())
                break
    return paths


def extract_candidate_paths(tool_input: object) -> List[str]:
    if not isinstance(tool_input, dict):
        return []

    candidates: List[str] = []
    for key in PATH_KEYS:
        candidates.extend(extract_string_values(tool_input.get(key)))

    if not candidates:
        command = tool_input.get("command")
        if isinstance(command, str):
            candidates.extend(extract_patch_paths(command))

    deduplicated: List[str] = []
    seen = set()
    for candidate in candidates:
        if candidate not in seen:
            seen.add(candidate)
            deduplicated.append(candidate)
    return deduplicated


def repository_root(cwd: Path) -> Optional[Path]:
    try:
        result = subprocess.run(
            ["git", "-C", str(cwd), "rev-parse", "--show-toplevel"],
            check=True,
            capture_output=True,
            text=True,
            timeout=2,
        )
    except (OSError, subprocess.SubprocessError, UnicodeError):
        return None

    root_text = result.stdout.strip()
    if not root_text:
        return None
    try:
        return Path(root_text).resolve(strict=False)
    except OSError:
        return None


def safe_repository_paths(root: Path, candidates: Iterable[str]) -> List[str]:
    safe_paths: List[str] = []
    seen = set()

    for raw_path in candidates:
        if not raw_path or raw_path == "/dev/null" or "\x00" in raw_path:
            continue

        candidate = Path(raw_path)
        target = candidate if candidate.is_absolute() else root / candidate
        try:
            resolved = target.resolve(strict=False)
            relative = resolved.relative_to(root)
        except (OSError, ValueError):
            continue

        relative_text = relative.as_posix()
        if relative_text in ("", ".") or relative_text in seen:
            continue
        seen.add(relative_text)
        safe_paths.append(relative_text)

    return safe_paths


def sanitized_git_diagnostics(output: str) -> List[str]:
    diagnostics: List[str] = []
    for line in output.splitlines():
        stripped = line.strip()
        if not stripped or line.startswith("+"):
            continue
        diagnostics.append(stripped[:500])
    return diagnostics


def git_diff_diagnostics(root: Path, paths: Sequence[str]) -> Optional[List[str]]:
    try:
        result = subprocess.run(
            ["git", "-C", str(root), "diff", "--check", "--", *paths],
            check=False,
            capture_output=True,
            text=True,
            timeout=3,
        )
    except (OSError, subprocess.SubprocessError, UnicodeError):
        return None

    if result.returncode == 0:
        return []
    if result.returncode != 2:
        return None

    diagnostics = sanitized_git_diagnostics(result.stdout)
    return diagnostics or None


def untracked_paths(root: Path, paths: Sequence[str]) -> Optional[List[str]]:
    try:
        result = subprocess.run(
            [
                "git",
                "-C",
                str(root),
                "ls-files",
                "--others",
                "--exclude-standard",
                "-z",
                "--",
                *paths,
            ],
            check=False,
            capture_output=True,
            timeout=3,
        )
    except (OSError, subprocess.SubprocessError):
        return None

    if result.returncode != 0:
        return None
    try:
        return [path for path in result.stdout.decode("utf-8").split("\x00") if path]
    except UnicodeDecodeError:
        return None


def untracked_whitespace_diagnostics(
    root: Path, paths: Sequence[str]
) -> Optional[List[str]]:
    untracked = untracked_paths(root, paths)
    if untracked is None:
        return None

    diagnostics: List[str] = []
    for relative_path in untracked:
        file_path = root / relative_path
        try:
            if not file_path.is_file():
                continue
            data = file_path.read_bytes()
        except OSError:
            continue

        if b"\x00" in data:
            continue
        try:
            text = data.decode("utf-8")
        except UnicodeDecodeError:
            continue

        for line_number, line in enumerate(text.splitlines(), start=1):
            if TRAILING_WHITESPACE.search(line.rstrip("\r")):
                diagnostics.append(
                    f"{relative_path}:{line_number}: trailing whitespace."
                )
    return diagnostics


def conflict_marker_diagnostics(root: Path, paths: Sequence[str]) -> List[str]:
    diagnostics: List[str] = []
    for relative_path in paths:
        file_path = root / relative_path
        try:
            if not file_path.is_file():
                continue
            data = file_path.read_bytes()
        except OSError:
            continue

        if b"\x00" in data:
            continue
        try:
            text = data.decode("utf-8")
        except UnicodeDecodeError:
            continue

        for line_number, line in enumerate(text.splitlines(), start=1):
            if CONFLICT_MARKER.fullmatch(line.rstrip("\r")):
                diagnostics.append(
                    f"{relative_path}:{line_number}: unresolved merge-conflict marker."
                )
    return diagnostics


def unique_diagnostics(diagnostics: Iterable[str]) -> List[str]:
    result: List[str] = []
    seen = set()
    for diagnostic in diagnostics:
        if diagnostic not in seen:
            seen.add(diagnostic)
            result.append(diagnostic)
    return result


def block_response(diagnostics: Sequence[str]) -> dict:
    visible = diagnostics[:MAX_DIAGNOSTICS]
    context = (
        "The write already happened. Correct these diff hygiene issues before "
        "continuing:\n"
        + "\n".join(f"- {diagnostic}" for diagnostic in visible)
        + "\nRun git diff --check on the affected paths after correcting them."
    )
    return {
        "decision": "block",
        "reason": "Diff hygiene issues were introduced by the completed file write.",
        "hookSpecificOutput": {
            "hookEventName": "PostToolUse",
            "additionalContext": context,
        },
    }


def main() -> int:
    try:
        event = json.load(sys.stdin)
    except (json.JSONDecodeError, OSError, UnicodeError):
        return 0

    if not isinstance(event, dict):
        return 0
    if event.get("hook_event_name") != "PostToolUse":
        return 0
    if event.get("tool_name") not in SUPPORTED_TOOLS:
        return 0

    tool_input = event.get("tool_input")
    candidates = extract_candidate_paths(tool_input)
    if not candidates:
        return 0

    raw_cwd = event.get("cwd")
    cwd = Path(raw_cwd) if isinstance(raw_cwd, str) and raw_cwd else Path.cwd()
    root = repository_root(cwd)
    if root is None:
        return 0

    paths = safe_repository_paths(root, candidates)
    if not paths:
        return 0

    git_diagnostics = git_diff_diagnostics(root, paths)
    if git_diagnostics is None:
        return 0

    untracked_diagnostics = untracked_whitespace_diagnostics(root, paths)
    if untracked_diagnostics is None:
        return 0

    diagnostics = unique_diagnostics(
        [
            *git_diagnostics,
            *untracked_diagnostics,
            *conflict_marker_diagnostics(root, paths),
        ]
    )
    if not diagnostics:
        return 0

    print(json.dumps(block_response(diagnostics), ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
