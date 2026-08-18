#!/usr/bin/env python3
from __future__ import annotations

import json
import os
import re
import shlex
import subprocess
import sys
from pathlib import Path
from typing import Iterable, Sequence

SEPARATOR_CHARS = ";&|\n"
ASSIGNMENT = re.compile(r"^[A-Za-z_][A-Za-z0-9_]*=.*$")


def diagnostic(message: str) -> None:
    print(f"block-dangerous-commands: {message}", file=sys.stderr)


def deny(operation: str, safer_alternative: str) -> None:
    reason = (
        f"Blocked irreversible operation: {operation}. "
        f"Use {safer_alternative} or obtain an explicit, reversible workflow."
    )
    print(
        json.dumps(
            {
                "hookSpecificOutput": {
                    "hookEventName": "PreToolUse",
                    "permissionDecision": "deny",
                    "permissionDecisionReason": reason,
                }
            },
            ensure_ascii=False,
        )
    )


def split_segments(command: str) -> list[list[str]]:
    lexer = shlex.shlex(command, posix=True, punctuation_chars=SEPARATOR_CHARS)
    lexer.whitespace = " \t\r"
    lexer.whitespace_split = True
    lexer.commenters = ""
    segments: list[list[str]] = []
    current: list[str] = []
    for token in lexer:
        if token and all(character in SEPARATOR_CHARS for character in token):
            if current:
                segments.append(current)
                current = []
            continue
        current.append(token)
    if current:
        segments.append(current)
    return segments


def strip_wrappers(tokens: Sequence[str]) -> list[str]:
    remaining = list(tokens)
    while remaining and ASSIGNMENT.fullmatch(remaining[0]):
        remaining.pop(0)

    if remaining and Path(remaining[0]).name == "sudo":
        remaining.pop(0)
        while remaining and remaining[0].startswith("-"):
            option = remaining.pop(0)
            if option == "--":
                break
            if option in {"-u", "--user", "-g", "--group", "-h", "--host"} and remaining:
                remaining.pop(0)

    if remaining and Path(remaining[0]).name in {"command", "builtin"}:
        remaining.pop(0)
        while remaining and remaining[0].startswith("-"):
            remaining.pop(0)

    if remaining and Path(remaining[0]).name == "env":
        remaining.pop(0)
        while remaining and (remaining[0].startswith("-") or ASSIGNMENT.fullmatch(remaining[0])):
            remaining.pop(0)

    return remaining


def git_subcommand(arguments: Sequence[str]) -> tuple[str | None, list[str]]:
    index = 0
    while index < len(arguments):
        token = arguments[index]
        if token == "--":
            index += 1
            break
        if not token.startswith("-"):
            break
        if token in {"-C", "-c", "--git-dir", "--work-tree", "--namespace"}:
            index += 2
        else:
            index += 1
    if index >= len(arguments):
        return None, []
    return arguments[index], list(arguments[index + 1 :])


def before_double_dash(arguments: Sequence[str]) -> list[str]:
    try:
        end = arguments.index("--")
    except ValueError:
        return list(arguments)
    return list(arguments[:end])


def short_flag(arguments: Iterable[str], flag: str) -> bool:
    for argument in arguments:
        if not argument.startswith("-") or argument.startswith("--"):
            continue
        if flag in argument[1:]:
            return True
    return False


def detect_git(arguments: Sequence[str]) -> tuple[str, str] | None:
    subcommand, subargs = git_subcommand(arguments)
    option_args = before_double_dash(subargs)
    if subcommand == "reset" and any(
        argument == "--hard" or argument.startswith("--hard=") for argument in option_args
    ):
        return "git reset --hard", "git reset --soft, git reset --mixed, or a backup branch"

    if subcommand == "clean":
        dry_run = short_flag(option_args, "n") or any(
            argument == "--dry-run" for argument in option_args
        )
        forced = short_flag(option_args, "f") or any(
            argument == "--force" for argument in option_args
        )
        if forced and not dry_run:
            return "git clean -f", "git clean -n first and remove only reviewed paths"

    if subcommand == "push":
        forced = short_flag(option_args, "f") or any(
            argument == "--force"
            or argument.startswith("--force=")
            or argument == "--force-with-lease"
            or argument.startswith("--force-with-lease=")
            for argument in option_args
        )
        if forced:
            return "force push", "a normal push or a new branch without rewriting shared history"

    return None


def rm_options(arguments: Sequence[str]) -> tuple[bool, bool, list[str]]:
    recursive = False
    forced = False
    targets: list[str] = []
    parsing_options = True
    for argument in arguments:
        if parsing_options and argument == "--":
            parsing_options = False
            continue
        if parsing_options and argument.startswith("--"):
            recursive = recursive or argument == "--recursive"
            forced = forced or argument == "--force"
            continue
        if parsing_options and argument.startswith("-") and argument != "-":
            flags = argument[1:]
            recursive = recursive or "r" in flags or "R" in flags
            forced = forced or "f" in flags
            continue
        targets.append(argument)
    return recursive, forced, targets


def repository_root(cwd: Path) -> Path | None:
    try:
        result = subprocess.run(
            ["git", "-C", str(cwd), "rev-parse", "--show-toplevel"],
            check=True,
            capture_output=True,
            text=True,
            timeout=2,
        )
    except (OSError, subprocess.SubprocessError):
        return None
    return Path(result.stdout.strip()).resolve()


def dangerous_rm_target(target: str, cwd: Path, repo_root: Path | None) -> bool:
    if target in {"/", "/*", "~", "~/", "$HOME", "${HOME}", "*", "./*"}:
        return True
    if any(character in target for character in "$`(){}"):
        return False
    try:
        resolved = Path(os.path.expanduser(target))
        if not resolved.is_absolute():
            resolved = cwd / resolved
        resolved = resolved.resolve(strict=False)
    except (OSError, RuntimeError):
        return False
    if resolved in {Path("/"), Path.home().resolve()}:
        return True
    return repo_root is not None and resolved == repo_root


def detect_segment(tokens: Sequence[str], cwd: Path) -> tuple[str, str] | None:
    remaining = strip_wrappers(tokens)
    if not remaining:
        return None
    executable = Path(remaining[0]).name
    arguments = remaining[1:]
    if executable == "git":
        return detect_git(arguments)
    if executable == "rm":
        recursive, forced, targets = rm_options(arguments)
        if recursive and forced:
            root = repository_root(cwd)
            if any(dangerous_rm_target(target, cwd, root) for target in targets):
                return (
                    "recursive forced removal",
                    "an explicit reviewed path, a backup, or a non-destructive listing first",
                )
    return None


def main() -> int:
    try:
        event = json.load(sys.stdin)
    except json.JSONDecodeError:
        diagnostic("invalid JSON; allowing command")
        return 0
    except OSError as error:
        diagnostic(f"input error ({type(error).__name__}); allowing command")
        return 0

    if not isinstance(event, dict):
        return 0

    try:
        if event.get("hook_event_name") != "PreToolUse" or event.get("tool_name") != "Bash":
            return 0
        tool_input = event.get("tool_input")
        command = tool_input.get("command") if isinstance(tool_input, dict) else None
        if not isinstance(command, str) or not command.strip():
            return 0
        raw_cwd = event.get("cwd")
        cwd = Path(raw_cwd) if isinstance(raw_cwd, str) and raw_cwd else Path.cwd()
        for segment in split_segments(command):
            match = detect_segment(segment, cwd)
            if match:
                deny(*match)
                return 0
    except (OSError, RuntimeError, ValueError) as error:
        diagnostic(f"internal {type(error).__name__}; allowing command")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
