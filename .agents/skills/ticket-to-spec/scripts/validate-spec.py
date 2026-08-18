#!/usr/bin/env python3
"""Validate the deterministic structure of a ticket-to-spec draft."""

from __future__ import annotations

import re
import sys
from pathlib import Path


FILENAME = re.compile(r"\d{4}-\d{2}-\d{2}-[a-z0-9]+(?:-[a-z0-9]+)*\.md")
REQUIRED_HEADINGS = [
    "## Estado",
    "## Contexto",
    "## Problema",
    "## Objetivo",
    "## Fuera del alcance",
    "## Requisitos funcionales",
    "## Reglas de negocio",
    "## Contratos",
    "## Casos límite",
    "## Archivos o módulos relacionados",
    "## Criterios de aceptación",
    "## Validación",
    "## Riesgos",
    "## Decisiones pendientes",
]
TEMPLATE_PROMPTS = {
    "¿Qué ocurre actualmente?",
    "¿Qué comportamiento debe corregirse o agregarse?",
    "¿Qué resultado debe conseguir esta tarea?",
    "¿Qué no debe hacerse?",
}
TRACEABLE_ITEM = re.compile(
    r"\d+\. \[(?:Solicitud|Regla: [^\]]+|Evidencia: [^\]]+)\] \S.*"
)


def section_lines(lines: list[str], heading_index: int) -> list[str]:
    body: list[str] = []
    for line in lines[heading_index + 1 :]:
        if line.startswith("## "):
            break
        body.append(line)
    return body


def section_body(lines: list[str], heading_index: int) -> list[str]:
    return [
        line.strip()
        for line in section_lines(lines, heading_index)
        if line.strip() and not line.startswith("### ")
    ]


def numbered_items(lines: list[str], heading: str) -> list[str]:
    start = lines.index(heading)
    return [
        line.strip()
        for line in section_lines(lines, start)
        if re.match(r"\d+\.\s", line.strip())
    ]


def validate(path: Path) -> list[str]:
    errors: list[str] = []

    if not FILENAME.fullmatch(path.name):
        errors.append(
            "El nombre debe seguir YYYY-MM-DD-nombre-corto.md con minúsculas y guiones."
        )

    try:
        text = path.read_text(encoding="utf-8")
    except OSError as error:
        return [f"No se pudo leer el SPEC: {error}"]

    lines = text.splitlines()
    if not lines or not re.fullmatch(r"# SPEC: \S.*", lines[0]):
        errors.append("El primer encabezado debe ser '# SPEC: <nombre concreto>'.")
    elif lines[0] == "# SPEC: Nombre de la funcionalidad":
        errors.append("El título conserva el placeholder de la plantilla.")

    positions: list[int] = []
    for heading in REQUIRED_HEADINGS:
        matches = [index for index, line in enumerate(lines) if line == heading]
        if len(matches) != 1:
            errors.append(f"El encabezado requerido '{heading}' debe aparecer una vez.")
            continue
        positions.append(matches[0])
        if not section_body(lines, matches[0]):
            errors.append(f"La sección '{heading}' no puede quedar vacía.")

    if len(positions) == len(REQUIRED_HEADINGS) and positions != sorted(positions):
        errors.append("Los encabezados no siguen el orden de docs/specs/TEMPLATE.md.")

    contract_lines = (
        section_lines(lines, lines.index("## Contratos"))
        if "## Contratos" in lines
        else []
    )
    for subheading in ("### Entrada", "### Salida"):
        if lines.count(subheading) != 1 or contract_lines.count(subheading) != 1:
            errors.append(
                f"Contratos requiere exactamente un encabezado '{subheading}' "
                "dentro de su sección."
            )

    if "### Entrada" in contract_lines and "### Salida" in contract_lines:
        if contract_lines.index("### Entrada") > contract_lines.index("### Salida"):
            errors.append("En Contratos, Entrada debe aparecer antes de Salida.")

    if "## Estado" in lines:
        state = section_body(lines, lines.index("## Estado"))
        if state != ["- Borrador"]:
            errors.append("El estado debe ser exactamente '- Borrador'.")

    if TEMPLATE_PROMPTS.intersection(line.strip() for line in lines):
        errors.append("El SPEC conserva preguntas sin completar de la plantilla.")

    if any(line.strip() in {"-", "1.", "2.", "3."} for line in lines):
        errors.append("El SPEC conserva elementos vacíos de la plantilla.")

    for heading in ("## Requisitos funcionales", "## Criterios de aceptación"):
        if heading not in lines:
            continue
        items = numbered_items(lines, heading)
        if not items or any(not TRACEABLE_ITEM.fullmatch(item) for item in items):
            errors.append(
                f"Cada elemento de '{heading}' necesita trazabilidad "
                "[Solicitud], [Regla: ruta] o [Evidencia: ruta:línea]."
            )
        if any("pendiente" in item.lower() for item in items):
            errors.append(
                f"Los elementos pendientes de '{heading}' pertenecen a "
                "Decisiones pendientes."
            )
        if any(".agents/skills/" in item for item in items):
            errors.append(
                f"Un Skill no puede ser autoridad para elementos de '{heading}'."
            )

    return errors


def main() -> int:
    if len(sys.argv) != 2:
        print("Uso: validate-spec.py RUTA_SPEC", file=sys.stderr)
        return 2

    errors = validate(Path(sys.argv[1]))
    if errors:
        for error in errors:
            print(f"ERROR: {error}", file=sys.stderr)
        return 1

    print("SPEC válido.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
