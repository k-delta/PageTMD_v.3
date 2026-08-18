from __future__ import annotations

import json
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
HOOK = ROOT / ".codex/hooks/check-diff-hygiene.py"


class DiffHygieneHookTests(unittest.TestCase):
    def setUp(self) -> None:
        self.tempdir = tempfile.TemporaryDirectory()
        self.base = Path(self.tempdir.name)
        self.repo = self.base / "repo"
        subprocess.run(["git", "init", "-q", str(self.repo)], check=True)
        subprocess.run(
            ["git", "-C", str(self.repo), "config", "user.email", "hooks@example.test"],
            check=True,
        )
        subprocess.run(
            ["git", "-C", str(self.repo), "config", "user.name", "Hook Tests"],
            check=True,
        )
        for name in ("tracked.txt", "other.txt", "second.txt"):
            (self.repo / name).write_text(f"initial {name}\n", encoding="utf-8")
        subprocess.run(
            ["git", "-C", str(self.repo), "add", "tracked.txt", "other.txt", "second.txt"],
            check=True,
        )
        subprocess.run(
            ["git", "-C", str(self.repo), "commit", "-qm", "test fixture"],
            check=True,
        )

    def tearDown(self) -> None:
        self.tempdir.cleanup()

    def run_hook(
        self,
        tool_input: object,
        *,
        tool_name: str = "apply_patch",
        event_name: str = "PostToolUse",
        cwd: Path | None = None,
        raw_input: str | None = None,
    ) -> subprocess.CompletedProcess[str]:
        payload = {
            "hook_event_name": event_name,
            "tool_name": tool_name,
            "tool_input": tool_input,
            "tool_response": {"status": "completed"},
            "cwd": str(cwd or self.repo),
        }
        return subprocess.run(
            [sys.executable, str(HOOK)],
            input=raw_input if raw_input is not None else json.dumps(payload),
            cwd=cwd or self.repo,
            capture_output=True,
            text=True,
            check=False,
        )

    def output(self, result: subprocess.CompletedProcess[str]) -> dict:
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(result.stderr, "")
        if not result.stdout:
            return {}
        return json.loads(result.stdout)

    def context(self, output: dict) -> str:
        return output["hookSpecificOutput"]["additionalContext"]

    def test_clean_edited_file_produces_no_output(self) -> None:
        (self.repo / "tracked.txt").write_text("clean change\n", encoding="utf-8")
        result = self.run_hook({"file_path": "tracked.txt"})
        self.assertEqual(self.output(result), {})

    def test_trailing_whitespace_in_touched_file_blocks(self) -> None:
        (self.repo / "tracked.txt").write_text("bad whitespace   \n", encoding="utf-8")
        output = self.output(self.run_hook({"file_path": "tracked.txt"}))
        self.assertEqual(output["decision"], "block")
        self.assertIn("tracked.txt:1: trailing whitespace", self.context(output))
        self.assertNotIn("+bad whitespace", self.context(output))

    def test_trailing_whitespace_in_new_untracked_file_blocks(self) -> None:
        (self.repo / "new-file.txt").write_text("new bad whitespace   \n", encoding="utf-8")
        output = self.output(self.run_hook({"file_path": "new-file.txt"}))
        self.assertEqual(output["decision"], "block")
        self.assertIn("new-file.txt:1: trailing whitespace", self.context(output))
        self.assertNotIn("new bad whitespace", self.context(output))

    def test_unresolved_conflict_marker_blocks(self) -> None:
        (self.repo / "tracked.txt").write_text(
            "<<<<<<< HEAD\nours\n=======\ntheirs\n>>>>>>> branch\n",
            encoding="utf-8",
        )
        output = self.output(self.run_hook({"path": "tracked.txt"}))
        self.assertEqual(output["decision"], "block")
        self.assertIn("tracked.txt:1: unresolved merge-conflict marker", self.context(output))

    def test_existing_problem_in_unrelated_file_is_ignored(self) -> None:
        (self.repo / "other.txt").write_text("unrelated problem   \n", encoding="utf-8")
        (self.repo / "tracked.txt").write_text("clean touched file\n", encoding="utf-8")
        result = self.run_hook({"file_path": "tracked.txt"})
        self.assertEqual(self.output(result), {})

    def test_multiple_touched_files_are_inspected(self) -> None:
        (self.repo / "tracked.txt").write_text("bad   \n", encoding="utf-8")
        (self.repo / "second.txt").write_text(
            "<<<<<<< HEAD\nours\n=======\ntheirs\n>>>>>>> branch\n",
            encoding="utf-8",
        )
        output = self.output(
            self.run_hook({"paths": ["tracked.txt", "second.txt"]})
        )
        context = self.context(output)
        self.assertEqual(output["decision"], "block")
        self.assertIn("tracked.txt:1: trailing whitespace", context)
        self.assertIn("second.txt:1: unresolved merge-conflict marker", context)

    def test_duplicate_candidate_paths_are_deduplicated(self) -> None:
        (self.repo / "tracked.txt").write_text("bad   \n", encoding="utf-8")
        output = self.output(
            self.run_hook({"paths": ["tracked.txt", "tracked.txt"]})
        )
        self.assertEqual(
            self.context(output).count("tracked.txt:1: trailing whitespace"),
            1,
        )

    def test_path_traversal_outside_repository_is_ignored(self) -> None:
        (self.base / "outside.txt").write_text("outside problem   \n", encoding="utf-8")
        result = self.run_hook({"file_path": "../outside.txt"})
        self.assertEqual(self.output(result), {})

    def test_malformed_json_fails_open(self) -> None:
        result = self.run_hook({}, raw_input="{not-json")
        self.assertEqual(self.output(result), {})

    def test_unsupported_top_level_json_shape_fails_open(self) -> None:
        result = self.run_hook({}, raw_input="[]")
        self.assertEqual(self.output(result), {})

    def test_unsupported_event_or_tool_name_is_ignored(self) -> None:
        cases = (
            {"event_name": "PreToolUse", "tool_name": "apply_patch"},
            {"event_name": "PostToolUse", "tool_name": "Bash"},
        )
        for case in cases:
            with self.subTest(case=case):
                result = self.run_hook(
                    {"file_path": "tracked.txt"},
                    event_name=case["event_name"],
                    tool_name=case["tool_name"],
                )
                self.assertEqual(self.output(result), {})

    def test_execution_outside_git_fails_open(self) -> None:
        outside = self.base / "not-a-repo"
        outside.mkdir()
        result = self.run_hook({"file_path": "file.txt"}, cwd=outside)
        self.assertEqual(self.output(result), {})

    def test_deleted_or_missing_paths_do_not_crash(self) -> None:
        (self.repo / "tracked.txt").unlink()
        output = self.output(self.run_hook({"file_path": "tracked.txt"}))
        self.assertEqual(output, {})

    def test_apply_patch_header_path_extraction(self) -> None:
        (self.repo / "tracked.txt").write_text("bad from patch   \n", encoding="utf-8")
        patch = """*** Begin Patch
*** Update File: tracked.txt
@@
-initial tracked.txt
+bad from patch
*** End Patch
"""
        output = self.output(self.run_hook({"command": patch}))
        self.assertEqual(output["decision"], "block")
        self.assertIn("tracked.txt:1: trailing whitespace", self.context(output))

    def test_diagnostics_do_not_include_file_contents(self) -> None:
        (self.repo / "tracked.txt").write_text("PRIVATE_VALUE   \n", encoding="utf-8")
        (self.repo / "other.txt").write_text("UNRELATED_SECRET   \n", encoding="utf-8")
        output = self.output(self.run_hook({"filePath": "tracked.txt"}))
        context = self.context(output)
        self.assertNotIn("PRIVATE_VALUE", context)
        self.assertNotIn("UNRELATED_SECRET", context)


if __name__ == "__main__":
    unittest.main()
