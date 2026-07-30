#!/usr/bin/env bash
set -u

tmp_script=$(mktemp "${TMPDIR:-/tmp}/validate-before-stop.XXXXXX") || {
    printf '{}\n'
    exit 0
}
trap 'rm -f "$tmp_script"' EXIT

cat > "$tmp_script" <<'PY'
from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path

INCOMPLETE_STATES = {
    "BLOCKED",
    "NEEDS_INPUT",
    "AWAITING_SPEC_APPROVAL",
    "READY_FOR_IMPLEMENTATION",
    "IN_PROGRESS",
}
COMPLETE_STATES = {"DELIVERED"}
NEGATED_COMPLETION = (
    r"\bno\s+(?:est[aá]|qued[oó])\s+(?:terminad[oa]|completad[oa]|entregad[oa]|list[oa])\b",
    r"\b(?:a[uú]n|todav[ií]a)\s+no\s+(?:est[aá]\s+)?(?:terminad[oa]|completad[oa]|list[oa])\b",
    r"\bsin\s+(?:terminar|completar|entregar)\b",
    r"\bnot\s+(?:done|completed|delivered|finished|ready)\b",
    r"\bstill\s+(?:incomplete|unfinished|pending)\b",
)
COMPLETION_WORDS = re.compile(
    r"\b(?:terminad[oa]|completad[oa]|entregad[oa]|finished|completed|delivered|done)\b",
    re.IGNORECASE,
)
READY_CONTEXT = re.compile(
    r"\b(?:trabajo|cambio|implementaci[oó]n|tarea|work|change|implementation|task)\s+"
    r"(?:est[aá]\s+|is\s+)?(?:list[oa]|ready)\b",
    re.IGNORECASE,
)
STATUS_LINE = re.compile(
    r"(?im)^\s*(?:estado|status)\s*:\s*([A-Z_]+)\s*$"
)
CATEGORY_PATTERNS = {
    "Archivos": re.compile(
        r"(?im)^\s*(?:[-*]\s*)?(?:#{1,6}\s*)?(?:archivos?|files?)\s*:?(?:\s|$)"
    ),
    "Validación": re.compile(
        r"(?im)^\s*(?:[-*]\s*)?(?:#{1,6}\s*)?"
        r"(?:validaci[oó]n|pruebas?|validation|tests?|checks?)\s*:?(?:\s|$)"
    ),
    "Verificación": re.compile(
        r"(?im)^\s*(?:[-*]\s*)?(?:#{1,6}\s*)?"
        r"(?:verificaci[oó]n|verification)\s*:?(?:\s|$)"
    ),
    "Pendiente": re.compile(
        r"(?im)^\s*(?:[-*]\s*)?(?:#{1,6}\s*)?"
        r"(?:pendiente|pendientes|riesgo|riesgos|limitaci[oó]n|limitaciones|"
        r"pending|remaining risks?|limitations?|blockers?)\s*:?(?:\s|$)"
    ),
}


def allow(message: str | None = None) -> int:
    if message:
        print(f"validate-before-stop: {message}", file=sys.stderr)
    print("{}")
    return 0


def stripped_message(message: str) -> str:
    without_fences = re.sub(r"```.*?```", " ", message, flags=re.DOTALL)
    without_inline = re.sub(r"`[^`]*`", " ", without_fences)
    without_quotes = "\n".join(
        line for line in without_inline.splitlines() if not line.lstrip().startswith(">")
    )
    return without_quotes


def claims_completion(message: str) -> bool:
    status = STATUS_LINE.search(message)
    if status:
        state = status.group(1)
        if state in INCOMPLETE_STATES:
            return False
        if state in COMPLETE_STATES:
            return True

    normalized = stripped_message(message)
    for pattern in NEGATED_COMPLETION:
        normalized = re.sub(pattern, " ", normalized, flags=re.IGNORECASE)
    return bool(COMPLETION_WORDS.search(normalized) or READY_CONTEXT.search(normalized))


def missing_categories(message: str) -> list[str]:
    return [
        category
        for category, pattern in CATEGORY_PATTERNS.items()
        if not pattern.search(message)
    ]


def git_root(cwd: Path) -> Path | None:
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
    return Path(result.stdout.strip())


def dirty(root: Path) -> bool | None:
    try:
        result = subprocess.run(
            ["git", "-C", str(root), "status", "--short"],
            check=True,
            capture_output=True,
            text=True,
            timeout=3,
        )
    except (OSError, subprocess.SubprocessError):
        return None
    return bool(result.stdout.strip())


def main() -> int:
    try:
        event = json.load(sys.stdin)
    except json.JSONDecodeError:
        return allow("invalid JSON; allowing stop")
    except OSError as error:
        return allow(f"input error ({type(error).__name__}); allowing stop")

    if not isinstance(event, dict):
        return allow()
    if event.get("stop_hook_active") is True:
        return allow()

    raw_cwd = event.get("cwd")
    cwd = Path(raw_cwd) if isinstance(raw_cwd, str) and raw_cwd else Path.cwd()
    root = git_root(cwd)
    if root is None:
        return allow()
    worktree_dirty = dirty(root)
    if worktree_dirty is None:
        return allow("git status unavailable; allowing stop")
    if not worktree_dirty:
        return allow()

    message = event.get("last_assistant_message")
    if not isinstance(message, str) or not claims_completion(message):
        return allow()

    missing = missing_categories(message)
    if not missing:
        return allow()

    reason = (
        "Completa el informe final antes de cerrar. Faltan categorías explícitas: "
        + ", ".join(missing)
        + ". Añade evidencia de lo realizado y de lo no verificado; no modifiques "
        "código ni ejecutes pruebas no solicitadas solo para satisfacer este gate."
    )
    print(json.dumps({"decision": "block", "reason": reason}, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
PY

python3 "$tmp_script"
