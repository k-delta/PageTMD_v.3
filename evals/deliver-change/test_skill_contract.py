from __future__ import annotations

import re
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SKILL_DIR = ROOT / ".agents/skills/deliver-change"
SKILL = SKILL_DIR / "SKILL.md"
ROUTING = SKILL_DIR / "references/task-routing.md"
REPORT = SKILL_DIR / "references/completion-report.md"


def frontmatter(text: str) -> dict[str, str]:
    match = re.match(r"\A---\n(.*?)\n---\n", text, re.DOTALL)
    if not match:
        return {}
    fields: dict[str, str] = {}
    for line in match.group(1).splitlines():
        if ":" in line:
            key, value = line.split(":", 1)
            fields[key.strip()] = value.strip()
    return fields


class DeliverChangeContractTests(unittest.TestCase):
    def setUp(self) -> None:
        self.skill = SKILL.read_text(encoding="utf-8")

    def test_frontmatter_is_minimal_trigger_only(self) -> None:
        fields = frontmatter(self.skill)
        self.assertEqual(set(fields), {"name", "description"})
        self.assertEqual(fields["name"], "deliver-change")
        self.assertTrue(fields["description"].startswith("Use when "))
        process_words = {"clarified", "specified", "implemented", "reviewed", "tested"}
        words = set(re.findall(r"[a-z-]+", fields["description"].lower()))
        self.assertTrue(process_words.isdisjoint(words))

    def test_skill_is_concise_and_links_both_references(self) -> None:
        self.assertLessEqual(len(self.skill.split()), 500)
        self.assertIn(
            "[task-routing.md](references/task-routing.md)", self.skill
        )
        self.assertIn(
            "[completion-report.md](references/completion-report.md)", self.skill
        )

    def test_routing_reference_assigns_every_specialized_owner(self) -> None:
        routing = ROUTING.read_text(encoding="utf-8")
        required = {
            "ticket-to-spec",
            "implement-spec",
            "review-change",
            "update-documentation",
            "safe-deploy",
            "superpowers:brainstorming",
            "superpowers:systematic-debugging",
            "superpowers:receiving-code-review",
            "superpowers:verification-before-completion",
        }
        for owner in required:
            self.assertIn(owner, routing)

    def test_completion_report_separates_evidence_and_authority(self) -> None:
        report = REPORT.read_text(encoding="utf-8")
        for field in (
            "Solicitud",
            "SPEC",
            "Archivos",
            "Pruebas",
            "Revisión",
            "Documentación",
            "Autorización",
            "Producción",
            "Pendiente",
        ):
            self.assertIn(field, report)

    def test_independent_work_and_intermediate_states_are_unambiguous(self) -> None:
        routing = ROUTING.read_text(encoding="utf-8")
        report = REPORT.read_text(encoding="utf-8")
        self.assertIn("SPEC separado", routing)
        for state in (
            "NEEDS_INPUT",
            "AWAITING_SPEC_APPROVAL",
            "READY_FOR_IMPLEMENTATION",
            "IN_PROGRESS",
            "BLOCKED",
            "DELIVERED",
        ):
            self.assertIn(state, report)
        self.assertIn("Gate actual", report)

    def test_brainstorming_and_ticket_to_spec_are_exclusive_branches(self) -> None:
        routing = ROUTING.read_text(encoding="utf-8")
        compact = " ".join(routing.split())
        self.assertIn("ramas excluyentes", routing.lower())
        self.assertIn("único SPEC canónico", routing)
        self.assertIn("No uses `ticket-to-spec` en esa misma rama", compact)
        self.assertIn("plan existente", routing)
        self.assertIn("No uses parcialmente", routing)

    def test_closure_and_authority_have_no_implicit_shortcuts(self) -> None:
        routing = ROUTING.read_text(encoding="utf-8")
        report = REPORT.read_text(encoding="utf-8")
        self.assertIn("Solicitud no creativa sin SPEC aprobado", routing)
        self.assertIn("SPEC canónico aprobado y plan", routing)
        self.assertIn("UPDATED, NO_UPDATE o BLOCKED", report)
        for action in ("Commit", "Push", "PR", "Merge", "Producción"):
            self.assertRegex(report, rf"(?m)^-\s+{action}:")
        self.assertIn("todo el alcance acordado", report)
        self.assertIn("estado `Terminado`", report)

    def test_plan_ownership_and_blocked_recovery_are_explicit(self) -> None:
        routing = ROUTING.read_text(encoding="utf-8")
        self.assertIn("Planificación creativa", routing)
        self.assertIn("Planificación restante", routing)
        self.assertIn("Ejecución", routing)
        self.assertIn("creación inicial", routing)
        self.assertIn("recuperación indicada por `implement-spec`", routing)
        self.assertIn("No reabras Brainstorming", routing)
        self.assertIn("devuelve `BLOCKED`", routing)


if __name__ == "__main__":
    unittest.main()
