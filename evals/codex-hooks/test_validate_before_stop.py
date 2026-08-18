from __future__ import annotations

import json
import subprocess
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
HOOK = ROOT / ".codex/hooks/validate-before-stop.sh"

COMPLETE_REPORT = """Estado: DELIVERED

Archivos
- `.codex/hooks/example`: actualizado.

Validación
- `python3 -m unittest`: PASS.

Verificación
- Verificado localmente. Producción no verificada porque está fuera del alcance.

Pendiente
- Ningún riesgo pendiente dentro del alcance.
"""


class StopHookTests(unittest.TestCase):
    def setUp(self) -> None:
        self.tempdir = tempfile.TemporaryDirectory()
        self.repo = Path(self.tempdir.name)
        subprocess.run(["git", "init", "-q", str(self.repo)], check=True)
        subprocess.run(
            ["git", "-C", str(self.repo), "config", "user.email", "hooks@example.test"],
            check=True,
        )
        subprocess.run(
            ["git", "-C", str(self.repo), "config", "user.name", "Hook Tests"],
            check=True,
        )
        tracked = self.repo / "tracked.txt"
        tracked.write_text("initial\n", encoding="utf-8")
        subprocess.run(["git", "-C", str(self.repo), "add", "tracked.txt"], check=True)
        subprocess.run(
            ["git", "-C", str(self.repo), "commit", "-qm", "test fixture"],
            check=True,
        )

    def tearDown(self) -> None:
        self.tempdir.cleanup()

    def make_dirty(self) -> None:
        (self.repo / "tracked.txt").write_text("changed\n", encoding="utf-8")

    def run_hook(
        self,
        message: str | None,
        *,
        cwd: Path | None = None,
        stop_hook_active: bool = False,
        raw_input: str | None = None,
    ):
        payload = {
            "hook_event_name": "Stop",
            "cwd": str(cwd or self.repo),
            "stop_hook_active": stop_hook_active,
            "last_assistant_message": message,
        }
        return subprocess.run(
            ["bash", str(HOOK)],
            input=raw_input if raw_input is not None else json.dumps(payload),
            cwd=cwd or self.repo,
            capture_output=True,
            text=True,
            check=False,
        )

    def output(self, result: subprocess.CompletedProcess[str]) -> dict:
        self.assertEqual(result.returncode, 0, result.stderr)
        return json.loads(result.stdout)

    def test_clean_worktree_allows_terse_completion(self) -> None:
        self.assertEqual(self.output(self.run_hook("Listo.")), {})

    def test_dirty_worktree_without_completion_claim_allows(self) -> None:
        self.make_dirty()
        self.assertEqual(
            self.output(self.run_hook("Sigo investigando; aún no está terminado.")),
            {},
        )

    def test_dirty_worktree_with_complete_report_allows(self) -> None:
        self.make_dirty()
        self.assertEqual(self.output(self.run_hook(COMPLETE_REPORT)), {})

    def test_feminine_completion_claim_requires_evidence(self) -> None:
        self.make_dirty()
        output = self.output(self.run_hook("Implementación terminada."))
        self.assertEqual(output["decision"], "block")
        self.assertIn("Archivos", output["reason"])

    def test_each_missing_evidence_category_blocks_and_names_it(self) -> None:
        self.make_dirty()
        categories = {
            "Archivos": "Archivos\n- `.codex/hooks/example`: actualizado.\n",
            "Validación": "Validación\n- `python3 -m unittest`: PASS.\n",
            "Verificación": (
                "Verificación\n- Verificado localmente. Producción no verificada "
                "porque está fuera del alcance.\n"
            ),
            "Pendiente": "Pendiente\n- Ningún riesgo pendiente dentro del alcance.\n",
        }
        for category, block in categories.items():
            with self.subTest(category=category):
                message = COMPLETE_REPORT.replace(block, "")
                result = self.run_hook(message)
                output = self.output(result)
                self.assertEqual(output["decision"], "block")
                self.assertIn(category, output["reason"])
                self.assertEqual(result.stderr, "")

    def test_stop_hook_active_prevents_a_second_continuation(self) -> None:
        self.make_dirty()
        output = self.output(
            self.run_hook("Trabajo completado.", stop_hook_active=True)
        )
        self.assertEqual(output, {})

    def test_blocked_and_waiting_states_are_not_completion_claims(self) -> None:
        self.make_dirty()
        messages = [
            "Estado: BLOCKED\nFalta autorización para continuar.",
            "Estado: NEEDS_INPUT\nFalta una decisión de negocio.",
            "Estado: AWAITING_SPEC_APPROVAL\nEl borrador está listo para revisión.",
            "The work is not completed; one validation remains.",
        ]
        for message in messages:
            with self.subTest(message=message):
                self.assertEqual(self.output(self.run_hook(message)), {})

    def test_nested_working_directory_uses_repository_root(self) -> None:
        nested = self.repo / "a" / "b"
        nested.mkdir(parents=True)
        self.make_dirty()
        output = self.output(self.run_hook("Terminado.", cwd=nested))
        self.assertEqual(output["decision"], "block")
        self.assertIn("Archivos", output["reason"])

    def test_malformed_json_and_non_repository_fail_open(self) -> None:
        malformed = self.run_hook(None, raw_input="{not-json")
        self.assertEqual(self.output(malformed), {})
        self.assertIn("invalid JSON", malformed.stderr)
        self.assertNotIn("{not-json", malformed.stderr)

        with tempfile.TemporaryDirectory() as directory:
            outside = Path(directory)
            result = self.run_hook("Completed.", cwd=outside)
            self.assertEqual(self.output(result), {})


if __name__ == "__main__":
    unittest.main()
