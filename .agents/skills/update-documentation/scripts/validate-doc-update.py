#!/usr/bin/env python3
"""Validate an atomic PageTMD documentation update plan."""

import argparse
import json
import sys
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any


AREAS = {"architecture", "domain", "runbooks", "status", "spec", "agents"}
ALLOWED = {
    "architecture": {"docs/architecture/REPO_MAP.md"},
    "domain": {
        "docs/domain/BUSINESS_RULES.md",
        "docs/domain/INVENTORY.md",
        "docs/domain/NAVIGATION.md",
        "docs/domain/SEO.md",
        "docs/domain/COMMERCE.md",
    },
    "runbooks": {
        "docs/runbooks/PRODUCTION.md",
        "docs/runbooks/DEPLOYMENT.md",
        "docs/runbooks/BACKUP_RESTORE.md",
    },
    "status": {"docs/status/CURRENT_STATE.md"},
    "agents": {"AGENTS.md"},
}


def nonempty(value: Any) -> bool:
    return isinstance(value, str) and bool(value.strip())


def valid_document(area: str, document: str) -> bool:
    if area == "spec":
        return (
            document.startswith("docs/specs/")
            and document.endswith(".md")
            and document not in {"docs/specs/README.md", "docs/specs/TEMPLATE.md"}
        )
    return document in ALLOWED.get(area, set())


def validate(plan: Any) -> list[str]:
    errors: list[str] = []
    if not isinstance(plan, dict):
        return ["el plan debe ser un objeto JSON"]

    required = {
        "target",
        "scope",
        "changed_documents",
        "spec_transition",
        "temporal_evidence",
        "unverified_facts",
        "blockers",
        "result",
    }
    missing = required - set(plan)
    if missing:
        return ["faltan campos: " + ", ".join(sorted(missing))]

    target = plan["target"]
    target_reference = target.get("reference") if isinstance(target, dict) else None
    if (
        not isinstance(target, dict)
        or not nonempty(target.get("kind"))
        or not nonempty(target.get("reference"))
    ):
        errors.append("target requiere kind y reference")

    scope = plan["scope"]
    seen_areas: set[str] = set()
    selected_documents: set[str] = set()
    selected_by_area: dict[str, set[str]] = {area: set() for area in AREAS}
    if not isinstance(scope, list):
        errors.append("scope debe ser una lista")
        scope = []
    for index, entry in enumerate(scope):
        if not isinstance(entry, dict):
            errors.append(f"scope[{index}] debe ser un objeto")
            continue
        area = entry.get("area")
        decision = entry.get("decision")
        documents = entry.get("documents")
        if area not in AREAS:
            errors.append(f"scope[{index}] tiene area desconocida")
            continue
        if area in seen_areas:
            errors.append(f"scope duplica el área {area}")
        seen_areas.add(area)
        if decision not in {"selected", "skipped"}:
            errors.append(f"scope[{index}] requiere selected o skipped")
        if not isinstance(documents, list) or not all(nonempty(item) for item in documents):
            errors.append(f"scope[{index}].documents debe ser una lista de rutas")
            documents = []
        if decision == "selected" and not documents:
            errors.append(f"scope[{index}] selected requiere documentos")
        if decision == "skipped" and documents:
            errors.append(f"scope[{index}] skipped no puede incluir documentos")
        if not nonempty(entry.get("reason")):
            errors.append(f"scope[{index}] requiere reason")
        evidence = entry.get("evidence")
        if (
            not isinstance(evidence, list)
            or not evidence
            or not all(nonempty(item) for item in evidence)
        ):
            errors.append(f"scope[{index}] requiere evidence")
        for document in documents:
            if not valid_document(area, document):
                errors.append(f"documento no permitido para {area}: {document}")
            if document in selected_documents:
                errors.append(f"documento seleccionado más de una vez: {document}")
            selected_documents.add(document)
            selected_by_area[area].add(document)
    if seen_areas != AREAS:
        errors.append("scope incompleto; faltan áreas: " + ", ".join(sorted(AREAS - seen_areas)))

    changed = plan["changed_documents"]
    if not isinstance(changed, list) or not all(nonempty(item) for item in changed):
        errors.append("changed_documents debe ser una lista de rutas")
        changed = []
    changed_set = set(changed)
    if len(changed_set) != len(changed):
        errors.append("changed_documents contiene duplicados")
    if not changed_set.issubset(selected_documents):
        errors.append("changed_documents debe pertenecer al scope selected")

    transition = plan["spec_transition"]
    spec_documents = {
        document
        for document in selected_documents
        if document.startswith("docs/specs/")
    }
    if spec_documents and not isinstance(transition, dict):
        errors.append("spec_transition es obligatorio cuando spec está selected")
    elif not spec_documents and transition is not None:
        errors.append("spec_transition requiere un SPEC selected")
    elif isinstance(transition, dict):
        path = transition.get("path")
        if path not in spec_documents:
            errors.append("spec_transition.path debe estar selected")
        for field in ("previous_status", "new_status"):
            if not nonempty(transition.get(field)):
                errors.append(f"spec_transition requiere {field}")
        if transition.get("new_status") == "Terminado":
            if not (
                transition.get("acceptance_verified") is True
                and transition.get("validations_verified") is True
                and transition.get("open_decisions") is False
            ):
                errors.append(
                    "Terminado exige criterios y validaciones verificados, sin decisiones abiertas"
                )
        if plan.get("result") == "UPDATED" and transition.get("new_status") != "Terminado":
            errors.append("un SPEC relacionado y verificado debe pasar a Terminado")

    temporal = plan["temporal_evidence"]
    if not isinstance(temporal, list):
        errors.append("temporal_evidence debe ser una lista")
        temporal = []
    current_state_selected = "docs/status/CURRENT_STATE.md" in selected_documents
    target_environment = target.get("environment") if isinstance(target, dict) else None
    if current_state_selected and not nonempty(target_environment):
        errors.append("CURRENT_STATE selected requiere target.environment")
    for index, item in enumerate(temporal):
        if not isinstance(item, dict) or not all(
            nonempty(item.get(field))
            for field in ("source", "environment", "checked_at", "target_reference")
        ):
            errors.append(
                f"temporal_evidence[{index}] requiere source, environment, checked_at y target_reference"
            )
            continue
        if item["target_reference"] != target_reference:
            errors.append(
                f"temporal_evidence[{index}].target_reference no coincide con target"
            )
        if item["environment"] != target_environment:
            errors.append(
                f"temporal_evidence[{index}].environment no coincide con target.environment"
            )
        try:
            checked_at = datetime.fromisoformat(item["checked_at"].replace("Z", "+00:00"))
            if checked_at.tzinfo is None:
                raise ValueError("timezone requerida")
            age = datetime.now(timezone.utc) - checked_at.astimezone(timezone.utc)
            if age > timedelta(hours=24) or age < -timedelta(minutes=5):
                errors.append(
                    f"temporal_evidence[{index}] no pertenece a la revisión actual"
                )
        except ValueError:
            errors.append(f"temporal_evidence[{index}].checked_at debe ser ISO 8601")

    unverified = plan["unverified_facts"]
    blockers = plan["blockers"]
    valid_unverified: list[dict[str, str]] = []
    if not isinstance(unverified, list):
        errors.append("unverified_facts debe ser una lista")
        unverified = []
    for index, item in enumerate(unverified):
        if not isinstance(item, dict) or not all(
            nonempty(item.get(field)) for field in ("fact", "area", "document")
        ):
            errors.append(
                f"unverified_facts[{index}] requiere fact, area y document"
            )
            continue
        if (
            item["area"] not in AREAS
            or item["document"] not in selected_by_area.get(item["area"], set())
        ):
            errors.append(
                f"unverified_facts[{index}].document debe estar selected en su area"
            )
            continue
        valid_unverified.append(item)
    if not isinstance(blockers, list) or not all(nonempty(item) for item in blockers):
        errors.append("blockers debe ser una lista de textos")
        blockers = []
    if current_state_selected and not temporal:
        if plan.get("result") == "UPDATED":
            errors.append("CURRENT_STATE selected requiere temporal_evidence")
        elif not any(
            item["document"] == "docs/status/CURRENT_STATE.md"
            for item in valid_unverified
        ):
            errors.append(
                "CURRENT_STATE sin temporal_evidence requiere unverified_facts vinculado"
            )

    result = plan["result"]
    if result == "UPDATED":
        if not changed:
            errors.append("UPDATED requiere changed_documents")
        if blockers or unverified:
            errors.append("UPDATED no admite blockers ni unverified_facts")
        if changed_set != selected_documents:
            errors.append("UPDATED exige cambiar exactamente el scope selected")
    elif result == "NO_UPDATE":
        if changed or selected_documents or blockers or unverified:
            errors.append("NO_UPDATE exige scope omitido, sin cambios ni bloqueos")
    elif result == "BLOCKED":
        if changed:
            errors.append("BLOCKED no permite changed_documents ni edición parcial")
        if not blockers:
            errors.append("BLOCKED requiere al menos un blocker")
    else:
        errors.append("result debe ser UPDATED, NO_UPDATE o BLOCKED")
    if unverified and result != "BLOCKED":
        errors.append("unverified_facts exige resultado BLOCKED")
    return errors


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("plan", type=Path)
    args = parser.parse_args()
    try:
        plan = json.loads(args.plan.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        print(f"Error: no se pudo leer JSON: {error}", file=sys.stderr)
        return 1
    errors = validate(plan)
    for error in errors:
        print(f"Error: {error}", file=sys.stderr)
    if errors:
        return 1
    print("Plan documental válido.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
