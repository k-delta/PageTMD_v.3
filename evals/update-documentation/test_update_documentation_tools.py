import json
import re
import subprocess
import tempfile
import unittest
from datetime import datetime, timezone
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SCRIPTS = ROOT / ".agents/skills/update-documentation/scripts"
DETECT = SCRIPTS / "detect-doc-impact.py"
VALIDATE = SCRIPTS / "validate-doc-update.py"
PLAN_FORMAT = (
    ROOT
    / ".agents/skills/update-documentation/references/update-plan-format.md"
)
AREAS = {"architecture", "domain", "runbooks", "status", "spec", "agents"}


class DetectDocumentationImpactTests(unittest.TestCase):
    def detect(self, paths: list[str], diff: str) -> set[str]:
        with tempfile.TemporaryDirectory() as temp_dir:
            temp = Path(temp_dir)
            paths_file = temp / "paths.txt"
            diff_file = temp / "change.diff"
            paths_file.write_text("\n".join(paths) + "\n", encoding="utf-8")
            diff_file.write_text(diff, encoding="utf-8")
            result = subprocess.run(
                [
                    "python3",
                    str(DETECT),
                    "--paths-file",
                    str(paths_file),
                    "--diff-file",
                    str(diff_file),
                ],
                check=False,
                capture_output=True,
                text=True,
            )
            self.assertEqual(result.returncode, 0, result.stderr)
            payload = json.loads(result.stdout)
            return {item["document"] for item in payload["candidates"]}

    def test_private_refactor_has_no_document_candidate(self) -> None:
        candidates = self.detect(
            ["wp-content/plugins/tm-chatbot-fase1/includes/helpers.php"],
            "-function private_helper_old() {}\n+function private_helper_new() {}",
        )
        self.assertEqual(candidates, set())

    def test_inventory_contract_across_boundaries_routes_two_documents(self) -> None:
        candidates = self.detect(
            [
                "wp-content/plugins/tm-equipos-destacados-v2/plugin.php",
                "wp-content/themes/blocksy-child/inc/tmd-inventory-api.php",
            ],
            "+// Firebase inventory response contract now exposes condition",
        )
        self.assertEqual(
            candidates,
            {
                "docs/architecture/REPO_MAP.md",
                "docs/domain/INVENTORY.md",
            },
        )

    def test_deployment_script_routes_only_deployment_runbook(self) -> None:
        candidates = self.detect(
            ["scripts/sync-production.sh"],
            "+# deployment drift check now requires explicit target",
        )
        self.assertEqual(candidates, {"docs/runbooks/DEPLOYMENT.md"})

    def test_seo_fix_is_candidate_not_forced_update(self) -> None:
        candidates = self.detect(
            ["wp-content/themes/blocksy-child/inc/tmd-seo.php"],
            "+add_filter('rank_math/frontend/canonical', 'tmd_canonical');",
        )
        self.assertEqual(candidates, {"docs/domain/SEO.md"})


class ValidateDocumentationPlanTests(unittest.TestCase):
    def plan(self) -> dict:
        return {
            "target": {"kind": "range", "reference": "abc123..def456"},
            "scope": [
                {
                    "area": area,
                    "decision": "skipped",
                    "documents": [],
                    "reason": "El cambio no afecta esta categoría documental.",
                    "evidence": ["diff revisado"],
                }
                for area in sorted(AREAS)
            ],
            "changed_documents": [],
            "spec_transition": None,
            "temporal_evidence": [],
            "unverified_facts": [],
            "blockers": [],
            "result": "NO_UPDATE",
        }

    def validate(self, plan: dict) -> subprocess.CompletedProcess[str]:
        with tempfile.TemporaryDirectory() as temp_dir:
            plan_file = Path(temp_dir) / "plan.json"
            plan_file.write_text(json.dumps(plan), encoding="utf-8")
            return subprocess.run(
                ["python3", str(VALIDATE), str(plan_file)],
                check=False,
                capture_output=True,
                text=True,
            )

    def select(self, plan: dict, area: str, documents: list[str]) -> None:
        entry = next(item for item in plan["scope"] if item["area"] == area)
        entry["decision"] = "selected"
        entry["documents"] = documents
        entry["reason"] = "El comportamiento documentado cambió."

    def test_accepts_no_update_with_complete_scope(self) -> None:
        result = self.validate(self.plan())
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_documented_example_is_valid(self) -> None:
        content = PLAN_FORMAT.read_text(encoding="utf-8")
        match = re.search(r"```json\n(.*?)\n```", content, re.DOTALL)
        self.assertIsNotNone(match, "Falta el ejemplo JSON documentado.")
        result = self.validate(json.loads(match.group(1)))
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_rejects_updated_without_changed_documents(self) -> None:
        plan = self.plan()
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("changed_documents", result.stderr)

    def test_rejects_empty_scope_evidence(self) -> None:
        plan = self.plan()
        plan["scope"][0]["evidence"] = []
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("evidence", result.stderr)

    def test_rejects_document_outside_selected_scope(self) -> None:
        plan = self.plan()
        plan["changed_documents"] = ["docs/domain/SEO.md"]
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("selected", result.stderr.lower())

    def test_rejects_completed_spec_without_evidence(self) -> None:
        plan = self.plan()
        spec = "docs/specs/2026-07-29-change.md"
        self.select(plan, "spec", [spec])
        plan["changed_documents"] = [spec]
        plan["spec_transition"] = {
            "path": spec,
            "previous_status": "En desarrollo",
            "new_status": "Terminado",
            "acceptance_verified": False,
            "validations_verified": True,
            "open_decisions": False,
        }
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("Terminado", result.stderr)

    def test_rejects_verified_spec_left_in_progress(self) -> None:
        plan = self.plan()
        spec = "docs/specs/2026-07-29-change.md"
        self.select(plan, "spec", [spec])
        plan["changed_documents"] = [spec]
        plan["spec_transition"] = {
            "path": spec,
            "previous_status": "En desarrollo",
            "new_status": "En desarrollo",
            "acceptance_verified": True,
            "validations_verified": True,
            "open_decisions": False,
        }
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("Terminado", result.stderr)

    def test_rejects_current_state_without_fresh_evidence(self) -> None:
        plan = self.plan()
        plan["target"]["environment"] = "production"
        current = "docs/status/CURRENT_STATE.md"
        self.select(plan, "status", [current])
        plan["changed_documents"] = [current]
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("temporal_evidence", result.stderr)

    def test_rejects_temporal_evidence_for_different_target(self) -> None:
        plan = self.plan()
        plan["target"]["environment"] = "production"
        current = "docs/status/CURRENT_STATE.md"
        self.select(plan, "status", [current])
        plan["changed_documents"] = [current]
        plan["temporal_evidence"] = [
            {
                "source": "HTTP verification",
                "environment": "production",
                "checked_at": "2026-07-29T10:00:00-05:00",
                "target_reference": "another-target",
            }
        ]
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("target_reference", result.stderr)

    def test_rejects_stale_temporal_evidence(self) -> None:
        plan = self.plan()
        plan["target"]["environment"] = "production"
        current = "docs/status/CURRENT_STATE.md"
        self.select(plan, "status", [current])
        plan["changed_documents"] = [current]
        plan["temporal_evidence"] = [
            {
                "source": "HTTP verification",
                "environment": "production",
                "checked_at": "2020-01-01T00:00:00+00:00",
                "target_reference": plan["target"]["reference"],
            }
        ]
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("actual", result.stderr.lower())

    def test_accepts_blocked_current_state_when_missing_evidence_is_declared(self) -> None:
        plan = self.plan()
        plan["target"]["environment"] = "production"
        current = "docs/status/CURRENT_STATE.md"
        self.select(plan, "status", [current])
        plan["unverified_facts"] = [
            {
                "fact": "Estado productivo actual.",
                "area": "status",
                "document": current,
            }
        ]
        plan["blockers"] = ["Falta verificación productiva actual."]
        plan["result"] = "BLOCKED"
        result = self.validate(plan)
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_rejects_blocked_current_state_without_linked_unverified_fact(self) -> None:
        plan = self.plan()
        plan["target"]["environment"] = "production"
        current = "docs/status/CURRENT_STATE.md"
        self.select(plan, "status", [current])
        plan["blockers"] = ["Falta verificación productiva actual."]
        plan["result"] = "BLOCKED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("unverified_facts", result.stderr)

    def test_rejects_unverified_fact_for_unselected_document(self) -> None:
        plan = self.plan()
        plan["unverified_facts"] = [
            {
                "fact": "Métrica productiva desconocida.",
                "area": "status",
                "document": "docs/status/CURRENT_STATE.md",
            }
        ]
        plan["blockers"] = ["Falta una métrica ajena al cambio."]
        plan["result"] = "BLOCKED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("selected", result.stderr.lower())

    def test_rejects_temporal_evidence_from_different_environment(self) -> None:
        plan = self.plan()
        plan["target"]["environment"] = "production"
        current = "docs/status/CURRENT_STATE.md"
        self.select(plan, "status", [current])
        plan["changed_documents"] = [current]
        plan["temporal_evidence"] = [
            {
                "source": "HTTP verification",
                "environment": "staging",
                "checked_at": datetime.now(timezone.utc).isoformat(),
                "target_reference": plan["target"]["reference"],
            }
        ]
        plan["result"] = "UPDATED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("environment", result.stderr)

    def test_blocked_plan_cannot_partially_modify_documents(self) -> None:
        plan = self.plan()
        self.select(plan, "runbooks", ["docs/runbooks/DEPLOYMENT.md"])
        plan["changed_documents"] = ["docs/runbooks/DEPLOYMENT.md"]
        plan["blockers"] = ["Falta evidencia obligatoria del SPEC."]
        plan["result"] = "BLOCKED"
        result = self.validate(plan)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("BLOCKED", result.stderr)


if __name__ == "__main__":
    unittest.main()
