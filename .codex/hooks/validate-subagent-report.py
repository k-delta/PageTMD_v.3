#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import sys
import unicodedata
from typing import Dict, Iterable, List, Mapping, Sequence, Tuple

REVIEWER_AGENTS = {
    "architecture-reviewer",
    "database-reviewer",
    "performance-reviewer",
    "security-reviewer",
    "test-reviewer",
}
RUNNER_AGENT = "test-runner"
SELECTED_AGENTS = REVIEWER_AGENTS | {RUNNER_AGENT}

REVIEWER_ERROR_ORDER = [
    "Veredicto",
    "Alcance",
    "Hallazgos o Sin hallazgos",
    "Severidad de hallazgos",
    "Referencia ruta:línea",
    "Evidencia",
    "Impacto",
    "Recomendación",
    "Validación",
    "Riesgos",
]
RUNNER_ERROR_ORDER = [
    "Comandos",
    "Resultados",
    "Pruebas ejecutadas",
    "Pruebas aprobadas",
    "Fallos",
    "Consistencia de resultados",
    "Justificación de cero pruebas",
    "Pruebas omitidas",
    "Riesgos",
]

SECTION_ALIASES = {
    "veredicto": "veredicto",
    "alcance": "alcance",
    "hallazgos": "hallazgos",
    "sin hallazgos": "sin hallazgos",
    "validacion": "validacion",
    "riesgos": "riesgos",
    "comandos": "comandos",
    "resultados": "resultados",
    "pruebas omitidas": "pruebas omitidas",
}
SUPPORTED_SEVERITIES = {"critical", "important", "minor"}
VERDICTS = {"aprobado", "requiere cambios", "bloqueado"}

HEADING_RE = re.compile(r"^\s*#{2,3}\s+(.+?)\s*$")
FINDING_HEADING_RE = re.compile(
    r"^\s*#{3,6}\s+(critical|important|minor)\b(?:\s*[—–:-]\s*.*)?$",
    re.IGNORECASE,
)
ANY_SUBHEADING_RE = re.compile(r"^\s*#{3,6}\s+(.+?)\s*$")
FENCE_RE = re.compile(r"^\s*(```|~~~)")
PATH_LINE_RE = re.compile(
    r"(?<![A-Za-z0-9_:/])"
    r"(?!https?://)"
    r"(?![A-Za-z]:[\\/])"
    r"([A-Za-z0-9_. /-]*[A-Za-z0-9_.-]):([1-9][0-9]*)\b",
    re.IGNORECASE,
)
COUNT_PATTERNS = {
    "executed": [
        re.compile(r"(?im)^\s*(?:[-*+]\s*)?(?:\*\*)?(?:pruebas ejecutadas|tests run|executed tests)(?:\*\*)?\s*:\s*(-?\d+)\b"),
    ],
    "passed": [
        re.compile(r"(?im)^\s*(?:[-*+]\s*)?(?:\*\*)?(?:pruebas aprobadas|tests passed|passed)(?:\*\*)?\s*:\s*(-?\d+)\b"),
    ],
    "failures": [
        re.compile(r"(?im)^\s*(?:[-*+]\s*)?(?:\*\*)?(?:fallos|failures|failed)(?:\*\*)?\s*:\s*(-?\d+)\b"),
    ],
}
COMMAND_PREFIX_RE = re.compile(
    r"^(?:\$\s*)?(?:python(?:3)?|php|npm|npx|composer|bash|sh|zsh|fish|git|pytest|phpunit|make|docker|wp|curl|node|ruby|go|cargo|java|mvn|gradle)(?:\s|$)",
    re.IGNORECASE,
)
INLINE_COMMAND_RE = re.compile(r"`([^`\n]+)`")
NO_COMMAND_REASON_RE = re.compile(
    r"\b(?:no se pudo ejecutar|no fue posible ejecutar|ningun comando pudo ejecutarse|ningún comando pudo ejecutarse|could not execute|unable to run)\b",
    re.IGNORECASE,
)
ZERO_TEST_REASON_RE = re.compile(
    r"\b(?:no se pudo|no fue posible|sin entorno|dependencia ausente|bloqueado|could not|unable to|environment unavailable|missing dependency)\b",
    re.IGNORECASE,
)


def normalize_text(value: str) -> str:
    decomposed = unicodedata.normalize("NFKD", value)
    without_accents = "".join(char for char in decomposed if not unicodedata.combining(char))
    lowered = without_accents.casefold()
    lowered = re.sub(r"[.!?:;]+\s*$", "", lowered.strip())
    return re.sub(r"\s+", " ", lowered)


def parse_sections(message: str) -> Dict[str, str]:
    sections: Dict[str, List[str]] = {}
    current: str | None = None
    in_fence = False
    fence_token = ""

    for raw_line in message.replace("\r\n", "\n").replace("\r", "\n").split("\n"):
        fence_match = FENCE_RE.match(raw_line)
        if fence_match:
            token = fence_match.group(1)
            if not in_fence:
                in_fence = True
                fence_token = token
            elif token == fence_token:
                in_fence = False
                fence_token = ""
            if current is not None:
                sections.setdefault(current, []).append(raw_line)
            continue

        if not in_fence:
            heading_match = HEADING_RE.match(raw_line)
            if heading_match:
                normalized = normalize_text(heading_match.group(1))
                canonical = SECTION_ALIASES.get(normalized)
                if canonical is not None:
                    current = canonical
                    sections.setdefault(current, [])
                    continue

        if current is not None:
            sections.setdefault(current, []).append(raw_line)

    return {name: "\n".join(lines).strip() for name, lines in sections.items()}


def strip_fenced_blocks(text: str) -> str:
    output: List[str] = []
    in_fence = False
    fence_token = ""
    for line in text.splitlines():
        match = FENCE_RE.match(line)
        if match:
            token = match.group(1)
            if not in_fence:
                in_fence = True
                fence_token = token
            elif token == fence_token:
                in_fence = False
                fence_token = ""
            output.append("")
            continue
        output.append("" if in_fence else line)
    return "\n".join(output)


def add_error(errors: List[str], category: str) -> None:
    if category not in errors:
        errors.append(category)


def ordered_errors(errors: Iterable[str], order: Sequence[str]) -> List[str]:
    positions = {name: index for index, name in enumerate(order)}
    return sorted(set(errors), key=lambda item: positions.get(item, len(order)))


def extract_labeled_value(block: str, label: str) -> str:
    normalized_label = normalize_text(label)
    for line in block.splitlines():
        candidate = re.sub(r"^\s*(?:[-+]\s+|\*\s+)", "", line).strip()
        bold_match = re.match(r"^\*\*\s*([^*]+?)\s*:?\s*\*\*\s*:?[ \t]*(.*)$", candidate)
        if bold_match and normalize_text(bold_match.group(1)) == normalized_label:
            return bold_match.group(2).strip()
        plain_match = re.match(r"^([^:]+?)\s*:\s*(.*)$", candidate)
        if plain_match and normalize_text(plain_match.group(1)) == normalized_label:
            return plain_match.group(2).strip()
    return ""


def finding_blocks(hallazgos: str) -> Tuple[List[str], bool]:
    lines = hallazgos.splitlines()
    starts: List[Tuple[int, bool]] = []
    for index, line in enumerate(lines):
        if FINDING_HEADING_RE.match(line):
            starts.append((index, True))
        elif ANY_SUBHEADING_RE.match(line):
            starts.append((index, False))

    if not starts:
        return [], True

    blocks: List[str] = []
    unsupported = False
    for position, (start, supported) in enumerate(starts):
        end = starts[position + 1][0] if position + 1 < len(starts) else len(lines)
        if supported:
            blocks.append("\n".join(lines[start:end]).strip())
        else:
            unsupported = True
    return blocks, unsupported


def has_path_line_reference(block: str) -> bool:
    visible = strip_fenced_blocks(block)
    for line in visible.splitlines():
        if re.search(r"https?://", line, re.IGNORECASE):
            continue
        if re.search(r"\b[A-Za-z]:[\\/]", line):
            continue
        matches = list(PATH_LINE_RE.finditer(line))
        for match in matches:
            path = match.group(1).strip()
            prefix = normalize_text(line.split(":", 1)[0]) if ":" in line else ""
            if "/" in path or "." in path or prefix == "ubicacion":
                return True
    return False


def validate_reviewer(message: str) -> List[str]:
    sections = parse_sections(message)
    errors: List[str] = []

    verdict = sections.get("veredicto", "")
    if not verdict or not any(value in normalize_text(verdict) for value in VERDICTS):
        add_error(errors, "Veredicto")

    if not sections.get("alcance", "").strip():
        add_error(errors, "Alcance")

    has_findings = "hallazgos" in sections
    has_clean = "sin hallazgos" in sections
    if has_findings == has_clean:
        add_error(errors, "Hallazgos o Sin hallazgos")
    elif has_clean:
        if not sections.get("sin hallazgos", "").strip():
            add_error(errors, "Hallazgos o Sin hallazgos")
    else:
        hallazgos = sections.get("hallazgos", "")
        blocks, unsupported = finding_blocks(hallazgos)
        if unsupported or not blocks:
            add_error(errors, "Severidad de hallazgos")
        for block in blocks:
            if not has_path_line_reference(block):
                add_error(errors, "Referencia ruta:línea")
            if not extract_labeled_value(block, "Evidencia"):
                add_error(errors, "Evidencia")
            if not extract_labeled_value(block, "Impacto"):
                add_error(errors, "Impacto")
            if not extract_labeled_value(block, "Recomendación"):
                add_error(errors, "Recomendación")

    if not sections.get("validacion", "").strip():
        add_error(errors, "Validación")
    if not sections.get("riesgos", "").strip():
        add_error(errors, "Riesgos")

    return ordered_errors(errors, REVIEWER_ERROR_ORDER)


def has_concrete_command(content: str) -> bool:
    if NO_COMMAND_REASON_RE.search(content):
        return True

    in_fence = False
    fence_token = ""
    for line in content.splitlines():
        fence_match = FENCE_RE.match(line)
        if fence_match:
            token = fence_match.group(1)
            if not in_fence:
                in_fence = True
                fence_token = token
            elif token == fence_token:
                in_fence = False
                fence_token = ""
            continue
        candidate = re.sub(r"^\s*(?:[-+]\s+|\*\s+)", "", line).strip()
        if in_fence and candidate and not candidate.startswith("#"):
            return True
        if COMMAND_PREFIX_RE.match(candidate):
            return True
        for inline in INLINE_COMMAND_RE.findall(line):
            if COMMAND_PREFIX_RE.match(inline.strip()):
                return True
    return False


def extract_count(content: str, kind: str) -> int | None:
    for pattern in COUNT_PATTERNS[kind]:
        match = pattern.search(content)
        if match:
            try:
                return int(match.group(1))
            except ValueError:
                return None
    return None


def validate_runner(message: str) -> List[str]:
    sections = parse_sections(message)
    errors: List[str] = []

    commands = sections.get("comandos", "")
    results = sections.get("resultados", "")
    omitted = sections.get("pruebas omitidas", "")
    risks = sections.get("riesgos", "")

    if not commands.strip() or not has_concrete_command(commands):
        add_error(errors, "Comandos")
    if not results.strip():
        add_error(errors, "Resultados")

    executed = extract_count(results, "executed") if results else None
    passed = extract_count(results, "passed") if results else None
    failures = extract_count(results, "failures") if results else None

    if executed is None or executed < 0:
        add_error(errors, "Pruebas ejecutadas")
    if passed is None or passed < 0:
        add_error(errors, "Pruebas aprobadas")
    if failures is None or failures < 0:
        add_error(errors, "Fallos")

    if executed is not None and passed is not None and failures is not None:
        if executed < 0 or passed < 0 or failures < 0:
            add_error(errors, "Consistencia de resultados")
        elif passed > executed or failures > executed or (executed > 0 and passed + failures > executed):
            add_error(errors, "Consistencia de resultados")
        if executed == 0 and not (
            NO_COMMAND_REASON_RE.search(commands)
            or ZERO_TEST_REASON_RE.search(commands)
            or ZERO_TEST_REASON_RE.search(results)
        ):
            add_error(errors, "Justificación de cero pruebas")

    if not omitted.strip():
        add_error(errors, "Pruebas omitidas")
    if not risks.strip():
        add_error(errors, "Riesgos")

    return ordered_errors(errors, RUNNER_ERROR_ORDER)


def blocking_payload(errors: Sequence[str]) -> Mapping[str, str]:
    joined = ", ".join(errors)
    reason = (
        "Completa el informe del subagente. Faltan o son inválidos: "
        f"{joined}. Usa el contrato requerido y vuelve a finalizar."
    )
    return {"decision": "block", "reason": reason[:700]}


def read_event() -> Mapping[str, object] | None:
    try:
        raw = sys.stdin.buffer.read()
        decoded = raw.decode("utf-8")
        payload = json.loads(decoded)
    except (UnicodeDecodeError, json.JSONDecodeError):
        return None
    return payload if isinstance(payload, dict) else None


def main() -> int:
    event = read_event()
    if event is None:
        return 0
    if event.get("hook_event_name") != "SubagentStop":
        return 0

    agent_type = event.get("agent_type")
    if not isinstance(agent_type, str) or agent_type not in SELECTED_AGENTS:
        return 0
    if event.get("stop_hook_active") is True:
        return 0

    message = event.get("last_assistant_message")
    if not isinstance(message, str):
        message = ""
    message = message.replace("\r\n", "\n").replace("\r", "\n").strip()

    errors = validate_runner(message) if agent_type == RUNNER_AGENT else validate_reviewer(message)
    if errors:
        print(json.dumps(blocking_payload(errors), ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
