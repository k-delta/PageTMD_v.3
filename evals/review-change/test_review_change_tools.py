import json
import re
import subprocess
import tempfile
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SCRIPTS = ROOT / ".agents/skills/review-change/scripts"
DETECT = SCRIPTS / "detect-change-areas.py"
VALIDATE = SCRIPTS / "validate-findings.py"
COLLECT = SCRIPTS / "collect-diff.sh"
REVIEWERS = {
    "architecture_reviewer",
    "database_reviewer",
    "performance_reviewer",
    "security_reviewer",
    "test_reviewer",
}


class DocumentationContractTests(unittest.TestCase):
    def test_review_change_internal_links_resolve(self) -> None:
        documents = [
            ROOT / ".agents/skills/review-change/SKILL.md",
            ROOT / ".agents/skills/review-change/references/reviewer-routing.md",
            ROOT / ".agents/skills/review-change/references/finding-format.md",
        ]
        for document in documents:
            source = document.read_text(encoding="utf-8")
            for target in re.findall(r"\[[^\]]+\]\(([^)]+)\)", source):
                if target.startswith(("http://", "https://", "#")):
                    continue
                resolved = (document.parent / target.split("#", 1)[0]).resolve()
                with self.subTest(document=document.name, target=target):
                    self.assertTrue(resolved.exists(), resolved)


class DetectChangeAreasTests(unittest.TestCase):
    def run_detect(self, paths: list[str], diff: str) -> set[str]:
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
            return {item["name"] for item in payload["reviewers"]}

    def test_routes_sql_n_plus_one_to_database_not_performance(self) -> None:
        reviewers = self.run_detect(
            ["wp-content/themes/blocksy-child/inc/tmd-account.php"],
            """
            foreach ($users as $user) {
                $rows = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM table WHERE user_id = %d", $user->ID)
                );
            }
            """,
        )
        self.assertIn("database_reviewer", reviewers)
        self.assertIn("test_reviewer", reviewers)
        self.assertNotIn("performance_reviewer", reviewers)

    def test_routes_authorization_change_without_architecture_by_default(self) -> None:
        reviewers = self.run_detect(
            ["wp-content/themes/blocksy-child/inc/tmd-account.php"],
            "if (! current_user_can('edit_users')) { return; }",
        )
        self.assertEqual(reviewers, {"security_reviewer", "test_reviewer"})

    def test_routes_sql_injection_to_security_and_database(self) -> None:
        reviewers = self.run_detect(
            ["wp-content/plugins/tm-chatbot-fase1/api.php"],
            '$rows = $wpdb->get_results("SELECT * FROM table WHERE id = $id");',
        )
        self.assertEqual(
            reviewers,
            {"database_reviewer", "security_reviewer", "test_reviewer"},
        )

    def test_does_not_select_specialists_for_css_and_docs_only(self) -> None:
        reviewers = self.run_detect(
            [
                "wp-content/themes/blocksy-child/assets/css/header.css",
                "docs/status/note.md",
            ],
            ".header { gap: 1rem; }",
        )
        self.assertEqual(reviewers, set())

    def test_detector_never_emits_removed_clean_code_role(self) -> None:
        reviewers = self.run_detect(
            ["wp-content/plugins/tm-chatbot-fase1/generated.js"],
            "\n".join(f"+const value{index} = {index};" for index in range(220)),
        )
        self.assertNotIn("clean_code_reviewer", reviewers)


class ValidateFindingsTests(unittest.TestCase):
    def report(self) -> dict:
        return {
            "target": {"kind": "range", "base": "abc123", "head": "def456"},
            "generic_review": {"status": "reused", "reference": "review.md"},
            "coverage": [
                {
                    "reviewer": reviewer,
                    "decision": "skipped",
                    "reason": "Sin señales en el diff.",
                }
                for reviewer in sorted(REVIEWERS)
            ],
            "findings": [],
            "validation_gaps": [],
            "verdict": "READY",
        }

    def run_validate(self, report: dict) -> subprocess.CompletedProcess[str]:
        with tempfile.TemporaryDirectory() as temp_dir:
            report_file = Path(temp_dir) / "report.json"
            report_file.write_text(json.dumps(report), encoding="utf-8")
            return subprocess.run(
                ["python3", str(VALIDATE), str(report_file)],
                check=False,
                capture_output=True,
                text=True,
            )

    def test_accepts_complete_consolidated_report(self) -> None:
        result = self.run_validate(self.report())
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_rejects_duplicate_root_cause(self) -> None:
        report = self.report()
        finding = {
            "id": "RC-001",
            "severity": "Important",
            "owner": "database_reviewer",
            "path": "inc/file.php",
            "line": 20,
            "root_cause": "query-in-loop",
            "evidence": "Consulta por cada elemento.",
            "impact": "N+1.",
            "recommendation": "Agrupar la consulta.",
            "confidence": "high",
        }
        report["findings"] = [finding, {**finding, "id": "RC-002"}]
        report["verdict"] = "BLOCKED"
        result = self.run_validate(report)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("duplic", result.stderr.lower())

    def test_rejects_ready_verdict_with_important_finding(self) -> None:
        report = self.report()
        report["findings"] = [
            {
                "id": "RC-001",
                "severity": "Important",
                "owner": "security_reviewer",
                "path": "inc/file.php",
                "line": 10,
                "root_cause": "missing-authorization",
                "evidence": "La acción no valida permisos.",
                "impact": "Acceso no autorizado.",
                "recommendation": "Validar capability.",
                "confidence": "high",
            }
        ]
        result = self.run_validate(report)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("blocked", result.stderr.lower())

    def test_rejects_incomplete_coverage(self) -> None:
        report = self.report()
        report["coverage"] = report["coverage"][:-1]
        result = self.run_validate(report)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("coverage", result.stderr.lower())

    def test_rejects_finding_owned_by_skipped_reviewer(self) -> None:
        report = self.report()
        report["findings"] = [
            {
                "id": "RC-001",
                "severity": "Minor",
                "owner": "security_reviewer",
                "path": "inc/file.php",
                "line": 10,
                "root_cause": "weak-validation",
                "evidence": "La validación acepta un valor ambiguo.",
                "impact": "La entrada puede producir un estado inesperado.",
                "recommendation": "Validar el valor permitido.",
                "confidence": "medium",
            }
        ]
        report["verdict"] = "READY_WITH_MINORS"
        result = self.run_validate(report)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("selected", result.stderr.lower())

    def test_rejects_removed_clean_code_reviewer_in_coverage(self) -> None:
        report = self.report()
        report["coverage"].append(
            {
                "reviewer": "clean_code_reviewer",
                "decision": "selected",
                "reason": "Revisión general duplicada.",
            }
        )
        result = self.run_validate(report)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("desconocido", result.stderr.lower())


class CollectDiffTests(unittest.TestCase):
    def test_rejects_mixed_targets(self) -> None:
        with tempfile.TemporaryDirectory() as temp_dir:
            output = Path(temp_dir) / "review-package.md"
            result = subprocess.run(
                [
                    "bash",
                    str(COLLECT),
                    "--output",
                    str(output),
                    "--staged",
                    "--working",
                ],
                cwd=ROOT,
                check=False,
                capture_output=True,
                text=True,
            )
            self.assertNotEqual(result.returncode, 0)

    def test_collects_explicit_git_range(self) -> None:
        with tempfile.TemporaryDirectory() as temp_dir:
            repo = Path(temp_dir) / "repo"
            repo.mkdir()
            subprocess.run(["git", "init", "-q"], cwd=repo, check=True)
            subprocess.run(
                ["git", "config", "user.email", "tests@example.invalid"],
                cwd=repo,
                check=True,
            )
            subprocess.run(
                ["git", "config", "user.name", "Review Tests"],
                cwd=repo,
                check=True,
            )
            tracked = repo / "file.txt"
            tracked.write_text("before\n", encoding="utf-8")
            subprocess.run(["git", "add", "file.txt"], cwd=repo, check=True)
            subprocess.run(["git", "commit", "-qm", "before"], cwd=repo, check=True)
            base = subprocess.run(
                ["git", "rev-parse", "HEAD"],
                cwd=repo,
                check=True,
                capture_output=True,
                text=True,
            ).stdout.strip()
            tracked.write_text("after\n", encoding="utf-8")
            subprocess.run(["git", "add", "file.txt"], cwd=repo, check=True)
            subprocess.run(["git", "commit", "-qm", "after"], cwd=repo, check=True)
            head = subprocess.run(
                ["git", "rev-parse", "HEAD"],
                cwd=repo,
                check=True,
                capture_output=True,
                text=True,
            ).stdout.strip()
            output = repo / "review-package.md"

            result = subprocess.run(
                [
                    "bash",
                    str(COLLECT),
                    "--output",
                    str(output),
                    "--range",
                    base,
                    head,
                ],
                cwd=repo,
                check=False,
                capture_output=True,
                text=True,
            )

            self.assertEqual(result.returncode, 0, result.stderr)
            package = output.read_text(encoding="utf-8")
            self.assertIn(base, package)
            self.assertIn(head, package)
            self.assertIn("file.txt", package)
            self.assertIn("+after", package)

    def test_working_package_includes_untracked_files(self) -> None:
        with tempfile.TemporaryDirectory() as temp_dir:
            repo = Path(temp_dir) / "repo"
            repo.mkdir()
            subprocess.run(["git", "init", "-q"], cwd=repo, check=True)
            subprocess.run(
                ["git", "config", "user.email", "tests@example.invalid"],
                cwd=repo,
                check=True,
            )
            subprocess.run(
                ["git", "config", "user.name", "Review Tests"],
                cwd=repo,
                check=True,
            )
            tracked = repo / "tracked.txt"
            tracked.write_text("tracked\n", encoding="utf-8")
            subprocess.run(["git", "add", "tracked.txt"], cwd=repo, check=True)
            subprocess.run(["git", "commit", "-qm", "initial"], cwd=repo, check=True)
            (repo / "new-file.php").write_text("<?php echo 'new';\n", encoding="utf-8")
            output = repo / "review-package.md"

            result = subprocess.run(
                ["bash", str(COLLECT), "--output", str(output), "--working"],
                cwd=repo,
                check=False,
                capture_output=True,
                text=True,
            )

            self.assertEqual(result.returncode, 0, result.stderr)
            package = output.read_text(encoding="utf-8")
            self.assertIn("new-file.php", package)
            self.assertIn("+<?php echo 'new';", package)


if __name__ == "__main__":
    unittest.main()
