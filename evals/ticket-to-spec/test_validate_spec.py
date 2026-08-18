import subprocess
import tempfile
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
VALIDATOR = ROOT / ".agents/skills/ticket-to-spec/scripts/validate-spec.py"

VALID_SPEC = """# SPEC: Ajuste verificable

## Estado

- Borrador

## Contexto

El ticket TMD-123 reporta el comportamiento actual.

## Problema

El resultado observado difiere del esperado.

## Objetivo

Definir el resultado verificable.

## Fuera del alcance

- Despliegue.

## Requisitos funcionales

1. [Solicitud] Conservar el comportamiento no afectado.

## Reglas de negocio

- Aplican las reglas de `docs/domain/BUSINESS_RULES.md`.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- Entrada incompleta.

## Archivos o módulos relacionados

- `wp-content/plugins/tm-quiz-equipo-ideal/`.

## Criterios de aceptación

1. [Solicitud] El caso reportado produce el resultado acordado.

## Validación

- Pruebas unitarias: caso reportado.
- Pruebas de integración: no aplica.
- Validación manual: reproducir el flujo.
- Validación productiva: pendiente de autorización.

## Riesgos

- Cambiar recomendaciones no relacionadas.

## Decisiones pendientes

- Confirmar el resultado esperado.
"""


class ValidateSpecTests(unittest.TestCase):
    def run_validator(self, filename: str, content: str) -> subprocess.CompletedProcess[str]:
        with tempfile.TemporaryDirectory() as temp_dir:
            spec = Path(temp_dir) / filename
            spec.write_text(content, encoding="utf-8")
            return subprocess.run(
                ["python3", str(VALIDATOR), str(spec)],
                check=False,
                capture_output=True,
                text=True,
            )

    def test_accepts_complete_draft_with_canonical_filename(self) -> None:
        result = self.run_validator("2026-07-29-ajuste-verificable.md", VALID_SPEC)
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_rejects_noncanonical_filename(self) -> None:
        result = self.run_validator("QUIZ_SPEC.md", VALID_SPEC)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("nombre", result.stderr.lower())

    def test_rejects_missing_required_section(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace("## Riesgos\n\n- Cambiar recomendaciones no relacionadas.\n\n", ""),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("riesgos", result.stderr.lower())

    def test_rejects_self_approved_spec(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace("- Borrador", "- Aprobado"),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("borrador", result.stderr.lower())

    def test_rejects_unfilled_template_prompt(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace(
                "El ticket TMD-123 reporta el comportamiento actual.",
                "¿Qué ocurre actualmente?",
            ),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("plantilla", result.stderr.lower())

    def test_rejects_untraceable_requirement_or_acceptance_criterion(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace(
                "1. [Solicitud] El caso reportado produce el resultado acordado.",
                "1. El caso reportado produce el resultado acordado.",
            ),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("trazabilidad", result.stderr.lower())

    def test_rejects_pending_items_outside_decisions_section(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace(
                "1. [Solicitud] Conservar el comportamiento no afectado.",
                "1. [Solicitud] Pendiente: definir el comportamiento.",
            ),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("decisiones pendientes", result.stderr.lower())

    def test_rejects_skill_itself_as_requirement_authority(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace(
                "1. [Solicitud] Conservar el comportamiento no afectado.",
                "1. [Regla: .agents/skills/ticket-to-spec/SKILL.md] "
                "Conservar el comportamiento.",
            ),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("skill", result.stderr.lower())

    def test_rejects_contract_subheading_outside_contract_section(self) -> None:
        result = self.run_validator(
            "2026-07-29-ajuste-verificable.md",
            VALID_SPEC.replace(
                "## Contratos\n\n### Entrada\n\nNo aplica.\n\n### Salida",
                "### Entrada\n\nNo aplica.\n\n## Contratos\n\n### Salida",
            ),
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("contratos", result.stderr.lower())


if __name__ == "__main__":
    unittest.main()
