#!/usr/bin/env python3
"""Validate the consolidated review report contract."""

import argparse
import json
import sys
from pathlib import Path
from typing import Any


REVIEWERS = {
    "architecture_reviewer",
    "database_reviewer",
    "performance_reviewer",
    "security_reviewer",
    "test_reviewer",
}
OWNERS = REVIEWERS | {"generic_reviewer"}
REQUIRED_FINDING = {
    "id",
    "severity",
    "owner",
    "path",
    "line",
    "root_cause",
    "evidence",
    "impact",
    "recommendation",
    "confidence",
}


def fail(message: str) -> None:
    print(f"Error: {message}", file=sys.stderr)


def nonempty(value: Any) -> bool:
    return isinstance(value, str) and bool(value.strip())


def validate(report: Any) -> list[str]:
    errors: list[str] = []
    if not isinstance(report, dict):
        return ["el reporte debe ser un objeto JSON"]

    required = {
        "target",
        "generic_review",
        "coverage",
        "findings",
        "validation_gaps",
        "verdict",
    }
    missing = required - set(report)
    if missing:
        errors.append(f"faltan campos superiores: {', '.join(sorted(missing))}")
        return errors

    target = report["target"]
    if not isinstance(target, dict) or not nonempty(target.get("kind")):
        errors.append("target debe identificar el objetivo")

    generic = report["generic_review"]
    if (
        not isinstance(generic, dict)
        or generic.get("status") not in {"reused", "generated"}
        or not nonempty(generic.get("reference"))
    ):
        errors.append("generic_review requiere status reused/generated y reference")

    coverage = report["coverage"]
    seen_reviewers: set[str] = set()
    decisions: dict[str, str] = {}
    if not isinstance(coverage, list):
        errors.append("coverage debe ser una lista")
    else:
        for index, entry in enumerate(coverage):
            if not isinstance(entry, dict):
                errors.append(f"coverage[{index}] debe ser un objeto")
                continue
            reviewer = entry.get("reviewer")
            if reviewer in seen_reviewers:
                errors.append(f"coverage duplica al revisor {reviewer}")
            if reviewer not in REVIEWERS:
                errors.append(f"coverage contiene un revisor desconocido: {reviewer}")
            else:
                seen_reviewers.add(reviewer)
            if entry.get("decision") not in {"selected", "skipped"}:
                errors.append(f"coverage[{index}] requiere decision selected/skipped")
            elif reviewer in REVIEWERS:
                decisions[reviewer] = entry["decision"]
            if not nonempty(entry.get("reason")):
                errors.append(f"coverage[{index}] requiere una razón")
        if seen_reviewers != REVIEWERS:
            missing_coverage = REVIEWERS - seen_reviewers
            errors.append(
                "coverage incompleta; faltan: " + ", ".join(sorted(missing_coverage))
            )

    findings = report["findings"]
    severities: list[str] = []
    ids: set[str] = set()
    roots: set[tuple[str, int, str]] = set()
    if not isinstance(findings, list):
        errors.append("findings debe ser una lista")
        findings = []
    for index, finding in enumerate(findings):
        if not isinstance(finding, dict):
            errors.append(f"findings[{index}] debe ser un objeto")
            continue
        missing_fields = REQUIRED_FINDING - set(finding)
        if missing_fields:
            errors.append(
                f"findings[{index}] carece de: {', '.join(sorted(missing_fields))}"
            )
            continue
        if not all(
            nonempty(finding[field])
            for field in REQUIRED_FINDING - {"line"}
            if field not in {"severity", "owner", "confidence"}
        ):
            errors.append(f"findings[{index}] contiene texto vacío")
        if finding["severity"] not in {"Critical", "Important", "Minor"}:
            errors.append(f"findings[{index}] tiene severidad inválida")
        else:
            severities.append(finding["severity"])
        if finding["owner"] not in OWNERS:
            errors.append(f"findings[{index}] tiene owner inválido")
        elif (
            finding["owner"] in REVIEWERS
            and decisions.get(finding["owner"]) != "selected"
        ):
            errors.append(
                f"findings[{index}] exige que {finding['owner']} esté selected"
            )
        if finding["confidence"] not in {"high", "medium", "low"}:
            errors.append(f"findings[{index}] tiene confidence inválida")
        if not isinstance(finding["line"], int) or finding["line"] < 1:
            errors.append(f"findings[{index}] requiere una línea positiva")
            continue
        if finding["id"] in ids:
            errors.append(f"hallazgo duplicado por id: {finding['id']}")
        ids.add(finding["id"])
        root_key = (finding["path"], finding["line"], finding["root_cause"])
        if root_key in roots:
            errors.append(
                "hallazgo duplicado por ruta, línea y causa raíz: "
                f"{finding['path']}:{finding['line']} {finding['root_cause']}"
            )
        roots.add(root_key)

    gaps = report["validation_gaps"]
    if not isinstance(gaps, list) or not all(nonempty(item) for item in gaps):
        errors.append("validation_gaps debe ser una lista de textos no vacíos")

    verdict = report["verdict"]
    if verdict not in {"READY", "READY_WITH_MINORS", "BLOCKED"}:
        errors.append("verdict inválido")
    elif any(item in {"Critical", "Important"} for item in severities):
        if verdict != "BLOCKED":
            errors.append("hallazgos Critical/Important exigen verdict BLOCKED")
    elif severities:
        if verdict != "READY_WITH_MINORS":
            errors.append("solo hallazgos Minor exigen verdict READY_WITH_MINORS")
    elif verdict != "READY":
        errors.append("sin hallazgos el verdict debe ser READY")
    return errors


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("report", type=Path)
    args = parser.parse_args()
    try:
        report = json.loads(args.report.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        fail(f"no se pudo leer JSON: {error}")
        return 1
    errors = validate(report)
    for error in errors:
        fail(error)
    if errors:
        return 1
    print("Reporte de revisión válido.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
