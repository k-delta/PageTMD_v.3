from __future__ import annotations

import json
import subprocess
import sys
import unittest
from pathlib import Path
from typing import Any, Dict

ROOT = Path(__file__).resolve().parents[2]
HOOK = ROOT / ".codex/hooks/validate-subagent-report.py"
REVIEWERS = [
    "architecture-reviewer",
    "database-reviewer",
    "performance-reviewer",
    "security-reviewer",
    "test-reviewer",
]

CLEAN_REVIEW = """\
## Veredicto

Aprobado

## Alcance

Se revisó el diff completo del cambio.

## Sin hallazgos

No se identificaron hallazgos dentro del alcance revisado.

## Validación

Se inspeccionaron contratos, pruebas y configuración.

## Riesgos

Ninguno identificado.
"""

VALID_FINDING = """\
## Veredicto

Requiere cambios

## Alcance

Se revisó el módulo de autenticación.

## Hallazgos

### Important — Falta autorización

- Ubicación: src/Auth/Policy.php:42
- Evidencia: El endpoint permite continuar sin verificar el rol.
- Impacto: Un usuario sin permisos podría ejecutar la operación.
- Recomendación: Validar el rol antes de invocar el servicio.

## Validación

Se inspeccionó el diff y la prueba asociada.

## Riesgos

La cobertura de integración sigue pendiente.
"""

VALID_RUNNER = """\
## Comandos

- python3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v

## Resultados

- Pruebas ejecutadas: 57
- Pruebas aprobadas: 57
- Fallos: 0

## Pruebas omitidas

Ninguna.

## Riesgos

Ninguno identificado.
"""


def sectionless_event(**overrides: Any) -> Dict[str, Any]:
    event: Dict[str, Any] = {
        "hook_event_name": "SubagentStop",
        "agent_type": "architecture-reviewer",
        "stop_hook_active": False,
        "last_assistant_message": CLEAN_REVIEW,
    }
    event.update(overrides)
    return event


class SubagentReportHookTests(unittest.TestCase):
    maxDiff = None

    def run_hook_raw(self, raw: bytes) -> subprocess.CompletedProcess[bytes]:
        return subprocess.run(
            [sys.executable, str(HOOK)],
            input=raw,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            check=False,
        )

    def run_hook(self, **overrides: Any) -> subprocess.CompletedProcess[bytes]:
        payload = json.dumps(sectionless_event(**overrides), ensure_ascii=False).encode("utf-8")
        return self.run_hook_raw(payload)

    def output(self, result: subprocess.CompletedProcess[bytes]) -> Dict[str, Any]:
        self.assertEqual(result.returncode, 0, result.stderr.decode("utf-8", errors="replace"))
        self.assertEqual(result.stderr, b"")
        if not result.stdout:
            return {}
        return json.loads(result.stdout.decode("utf-8"))

    def assert_allowed(self, **overrides: Any) -> None:
        self.assertEqual(self.output(self.run_hook(**overrides)), {})

    def assert_blocked(self, category: str, **overrides: Any) -> Dict[str, Any]:
        output = self.output(self.run_hook(**overrides))
        self.assertEqual(output.get("decision"), "block")
        self.assertIn(category, output.get("reason", ""))
        self.assertLessEqual(len(output.get("reason", "")), 700)
        return output

    def test_clean_approved_report_without_line_references_is_allowed(self) -> None:
        self.assert_allowed(last_assistant_message=CLEAN_REVIEW)

    def test_report_with_one_valid_important_finding_is_allowed(self) -> None:
        self.assert_allowed(last_assistant_message=VALID_FINDING)

    def test_all_selected_reviewers_use_reviewer_contract(self) -> None:
        for reviewer in REVIEWERS:
            with self.subTest(reviewer=reviewer):
                self.assert_allowed(agent_type=reviewer, last_assistant_message=CLEAN_REVIEW)

    def test_multiple_findings_each_require_their_own_reference_and_fields(self) -> None:
        report = VALID_FINDING.replace(
            "## Validación",
            """### Minor — Falta una prueba negativa

- Ubicación: tests/AuthPolicyTest.php:88
- Evidencia: Solo existe el caso autorizado.
- Impacto: Una regresión de autorización podría pasar inadvertida.
- Recomendación: Añadir un caso sin rol permitido.

## Validación""",
        )
        self.assert_allowed(last_assistant_message=report)
        missing_second_reference = report.replace("- Ubicación: tests/AuthPolicyTest.php:88\n", "")
        self.assert_blocked("Referencia ruta:línea", last_assistant_message=missing_second_reference)

    def test_missing_or_invalid_verdict_blocks(self) -> None:
        cases = [
            CLEAN_REVIEW.replace("## Veredicto\n\nAprobado\n\n", ""),
            CLEAN_REVIEW.replace("Aprobado", "Parece correcto"),
        ]
        for report in cases:
            with self.subTest(report=report[:40]):
                self.assert_blocked("Veredicto", last_assistant_message=report)

    def test_missing_scope_blocks(self) -> None:
        report = CLEAN_REVIEW.replace("## Alcance\n\nSe revisó el diff completo del cambio.\n\n", "")
        self.assert_blocked("Alcance", last_assistant_message=report)

    def test_missing_or_ambiguous_findings_mode_blocks(self) -> None:
        missing = CLEAN_REVIEW.replace(
            "## Sin hallazgos\n\nNo se identificaron hallazgos dentro del alcance revisado.\n\n",
            "",
        )
        both = CLEAN_REVIEW.replace(
            "## Validación",
            "## Hallazgos\n\n### Minor — Nota\n\n- Ubicación: src/a.py:1\n- Evidencia: x\n- Impacto: y\n- Recomendación: z\n\n## Validación",
        )
        for report in (missing, both):
            with self.subTest(report=report[:40]):
                self.assert_blocked("Hallazgos o Sin hallazgos", last_assistant_message=report)

    def test_unsupported_finding_severity_blocks(self) -> None:
        report = VALID_FINDING.replace("### Important", "### Warning")
        self.assert_blocked("Severidad de hallazgos", last_assistant_message=report)

    def test_finding_without_path_line_blocks(self) -> None:
        report = VALID_FINDING.replace("src/Auth/Policy.php:42", "src/Auth/Policy.php")
        self.assert_blocked("Referencia ruta:línea", last_assistant_message=report)

    def test_reference_inside_code_fence_does_not_count(self) -> None:
        report = VALID_FINDING.replace(
            "- Ubicación: src/Auth/Policy.php:42",
            "```text\nsrc/Auth/Policy.php:42\n```",
        )
        self.assert_blocked("Referencia ruta:línea", last_assistant_message=report)

    def test_invalid_reference_shapes_block(self) -> None:
        invalid_values = [
            "https://example.com/file.php:42",
            r"C:\\src\\file.php:42",
            "src/Auth/Policy.php:0",
            "42",
        ]
        for value in invalid_values:
            with self.subTest(value=value):
                report = VALID_FINDING.replace("src/Auth/Policy.php:42", value)
                self.assert_blocked("Referencia ruta:línea", last_assistant_message=report)

    def test_each_finding_field_is_required(self) -> None:
        cases = {
            "Evidencia": "- Evidencia: El endpoint permite continuar sin verificar el rol.\n",
            "Impacto": "- Impacto: Un usuario sin permisos podría ejecutar la operación.\n",
            "Recomendación": "- Recomendación: Validar el rol antes de invocar el servicio.\n",
        }
        for category, line in cases.items():
            with self.subTest(category=category):
                self.assert_blocked(category, last_assistant_message=VALID_FINDING.replace(line, ""))

    def test_bold_and_plain_finding_labels_are_accepted(self) -> None:
        report = VALID_FINDING.replace(
            "- Evidencia: El endpoint permite continuar sin verificar el rol.",
            "**Evidencia:** El endpoint permite continuar sin verificar el rol.",
        ).replace(
            "- Impacto: Un usuario sin permisos podría ejecutar la operación.",
            "Impacto: Un usuario sin permisos podría ejecutar la operación.",
        )
        self.assert_allowed(last_assistant_message=report)

    def test_missing_validation_or_risks_blocks(self) -> None:
        cases = {
            "Validación": "## Validación\n\nSe inspeccionaron contratos, pruebas y configuración.\n\n",
            "Riesgos": "## Riesgos\n\nNinguno identificado.\n",
        }
        for category, section in cases.items():
            with self.subTest(category=category):
                self.assert_blocked(category, last_assistant_message=CLEAN_REVIEW.replace(section, ""))

    def test_risks_none_identified_is_allowed(self) -> None:
        self.assert_allowed(last_assistant_message=CLEAN_REVIEW)

    def test_headings_are_case_insensitive_and_allow_level_three_and_punctuation(self) -> None:
        report = CLEAN_REVIEW.replace("## Veredicto", "### VEREDICTO:").replace(
            "## Sin hallazgos", "### sin HALLAZGOS."
        )
        self.assert_allowed(last_assistant_message=report)

    def test_headings_inside_code_fences_are_ignored(self) -> None:
        report = CLEAN_REVIEW.replace(
            "## Validación",
            "```markdown\n## Hallazgos\n```\n\n## Validación",
        )
        self.assert_allowed(last_assistant_message=report)

    def test_complete_successful_runner_report_is_allowed(self) -> None:
        self.assert_allowed(agent_type="test-runner", last_assistant_message=VALID_RUNNER)

    def test_complete_failed_runner_report_is_allowed(self) -> None:
        report = VALID_RUNNER.replace("Pruebas aprobadas: 57", "Pruebas aprobadas: 55").replace(
            "Fallos: 0", "Fallos: 2"
        ).replace("Ninguno identificado.", "Persisten dos pruebas fallidas.")
        self.assert_allowed(agent_type="test-runner", last_assistant_message=report)

    def test_missing_or_non_concrete_commands_block(self) -> None:
        missing = VALID_RUNNER.replace(
            "## Comandos\n\n- python3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v\n\n",
            "",
        )
        generic = VALID_RUNNER.replace(
            "- python3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v",
            "Se ejecutaron las pruebas.",
        )
        for report in (missing, generic):
            with self.subTest(report=report[:40]):
                self.assert_blocked("Comandos", agent_type="test-runner", last_assistant_message=report)

    def test_fenced_and_inline_commands_are_allowed(self) -> None:
        fenced = VALID_RUNNER.replace(
            "- python3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v",
            "```bash\npython3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v\n```",
        )
        inline = VALID_RUNNER.replace(
            "- python3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v",
            "Se ejecutó `python3 -m unittest -v`.",
        )
        for report in (fenced, inline):
            with self.subTest(report=report[:50]):
                self.assert_allowed(agent_type="test-runner", last_assistant_message=report)

    def test_explicit_inability_with_reason_allows_zero_counts(self) -> None:
        report = VALID_RUNNER.replace(
            "- python3 -m unittest discover -s evals/codex-hooks -p 'test_*.py' -v",
            "No se pudo ejecutar ningún comando porque falta Python.",
        ).replace("57", "0")
        self.assert_allowed(agent_type="test-runner", last_assistant_message=report)

    def test_missing_numeric_counts_block(self) -> None:
        labels = {
            "Pruebas ejecutadas": "- Pruebas ejecutadas: 57\n",
            "Pruebas aprobadas": "- Pruebas aprobadas: 57\n",
            "Fallos": "- Fallos: 0\n",
        }
        for category, line in labels.items():
            with self.subTest(category=category):
                report = VALID_RUNNER.replace(line, "")
                self.assert_blocked(category, agent_type="test-runner", last_assistant_message=report)

    def test_negative_counts_block(self) -> None:
        for label in ("Pruebas ejecutadas", "Pruebas aprobadas", "Fallos"):
            with self.subTest(label=label):
                report = VALID_RUNNER.replace(f"{label}: 57", f"{label}: -1").replace(
                    "Fallos: 0", "Fallos: -1" if label == "Fallos" else "Fallos: 0"
                )
                expected = label
                self.assert_blocked(expected, agent_type="test-runner", last_assistant_message=report)

    def test_inconsistent_counts_block(self) -> None:
        reports = [
            VALID_RUNNER.replace("Pruebas aprobadas: 57", "Pruebas aprobadas: 58"),
            VALID_RUNNER.replace("Fallos: 0", "Fallos: 58"),
            VALID_RUNNER.replace("Pruebas aprobadas: 57", "Pruebas aprobadas: 56").replace(
                "Fallos: 0", "Fallos: 2"
            ),
        ]
        for report in reports:
            with self.subTest(report=report[-100:]):
                self.assert_blocked(
                    "Consistencia de resultados",
                    agent_type="test-runner",
                    last_assistant_message=report,
                )

    def test_zero_executed_without_explanation_blocks(self) -> None:
        report = VALID_RUNNER.replace("57", "0")
        self.assert_blocked(
            "Justificación de cero pruebas",
            agent_type="test-runner",
            last_assistant_message=report,
        )

    def test_missing_omitted_tests_or_risks_blocks(self) -> None:
        cases = {
            "Pruebas omitidas": "## Pruebas omitidas\n\nNinguna.\n\n",
            "Riesgos": "## Riesgos\n\nNinguno identificado.\n",
        }
        for category, section in cases.items():
            with self.subTest(category=category):
                report = VALID_RUNNER.replace(section, "")
                self.assert_blocked(category, agent_type="test-runner", last_assistant_message=report)

    def test_unknown_agents_and_other_events_are_allowed(self) -> None:
        self.assert_allowed(agent_type="implementer")
        self.assert_allowed(hook_event_name="Stop")

    def test_stop_hook_active_prevents_second_continuation(self) -> None:
        self.assert_allowed(stop_hook_active=True, last_assistant_message="")

    def test_malformed_non_object_and_undecodable_input_fail_open(self) -> None:
        for raw in (b"{", b"[]", b"null", b"\xff"):
            with self.subTest(raw=raw):
                self.assertEqual(self.output(self.run_hook_raw(raw)), {})

    def test_empty_reports_block_once_for_selected_agents(self) -> None:
        reviewer = self.assert_blocked("Veredicto", last_assistant_message="")
        runner = self.assert_blocked("Comandos", agent_type="test-runner", last_assistant_message="")
        self.assertEqual(reviewer["decision"], "block")
        self.assertEqual(runner["decision"], "block")

    def test_diagnostics_do_not_repeat_report_contents(self) -> None:
        secret = "TOKEN-SHOULD-NOT-LEAK"
        output = self.assert_blocked("Veredicto", last_assistant_message=secret)
        self.assertNotIn(secret, json.dumps(output, ensure_ascii=False))

    def test_blocking_output_is_valid_json_and_deterministically_ordered(self) -> None:
        output = self.output(self.run_hook(last_assistant_message=""))
        reason = output["reason"]
        self.assertLess(reason.index("Veredicto"), reason.index("Alcance"))
        self.assertLess(reason.index("Alcance"), reason.index("Hallazgos o Sin hallazgos"))
        self.assertLess(reason.index("Validación"), reason.index("Riesgos"))


if __name__ == "__main__":
    unittest.main()
