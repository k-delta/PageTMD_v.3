from __future__ import annotations

import json
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
HOOK = ROOT / ".codex/hooks/block-dangerous-commands.py"


class DangerousCommandHookTests(unittest.TestCase):
    def setUp(self) -> None:
        self.tempdir = tempfile.TemporaryDirectory()
        self.repo = Path(self.tempdir.name)
        subprocess.run(
            ["git", "init", "-q", str(self.repo)],
            check=True,
            capture_output=True,
            text=True,
        )

    def tearDown(self) -> None:
        self.tempdir.cleanup()

    def run_hook(self, command: str, *, payload_overrides: dict | None = None):
        payload = {
            "hook_event_name": "PreToolUse",
            "tool_name": "Bash",
            "cwd": str(self.repo),
            "tool_input": {"command": command},
        }
        if payload_overrides:
            payload.update(payload_overrides)
        return subprocess.run(
            [sys.executable, str(HOOK)],
            input=json.dumps(payload),
            cwd=self.repo,
            capture_output=True,
            text=True,
            check=False,
        )

    def assert_denied(self, command: str, operation_fragment: str) -> None:
        result = self.run_hook(command)
        self.assertEqual(result.returncode, 0, result.stderr)
        output = json.loads(result.stdout)
        hook_output = output["hookSpecificOutput"]
        self.assertEqual(hook_output["hookEventName"], "PreToolUse")
        self.assertEqual(hook_output["permissionDecision"], "deny")
        self.assertIn(operation_fragment, hook_output["permissionDecisionReason"])
        self.assertNotIn(command, result.stderr)

    def assert_allowed(self, command: str) -> None:
        result = self.run_hook(command)
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(result.stdout, "")

    def test_denies_destructive_git_commands(self) -> None:
        cases = {
            "git reset --hard HEAD~1": "git reset --hard",
            "git clean -fd": "git clean -f",
            "git clean -dfx": "git clean -f",
            "git clean --force -d": "git clean -f",
            "git push origin main --force": "force push",
            "git push -f origin main": "force push",
            "git push --force-with-lease": "force push",
            "git clean -f -- -n": "git clean -f",
            "git clean --force -- --dry-run": "git clean -f",
            "cd subdir && git reset --hard": "git reset --hard",
            "/usr/bin/git clean -fd": "git clean -f",
        }
        for command, fragment in cases.items():
            with self.subTest(command=command):
                self.assert_denied(command, fragment)

    def test_denies_destructive_commands_after_a_line_separator(self) -> None:
        self.assert_denied("echo safe\ngit reset --hard", "git reset --hard")

    def test_denies_only_unambiguous_rm_targets(self) -> None:
        cases = [
            "rm -rf /",
            "rm -fr /*",
            "rm --recursive --force ~",
            "rm -rf *",
            "rm -rf ./",
            "rm / -rf",
            "rm ./ --recursive --force",
            f"rm -rf {self.repo}",
        ]
        for command in cases:
            with self.subTest(command=command):
                self.assert_denied(command, "recursive forced removal")

    def test_allows_reversible_or_dry_run_commands(self) -> None:
        cases = [
            "git reset --soft HEAD~1",
            "git reset --mixed HEAD~1",
            "git reset -- --hard",
            "git clean -nd",
            "git clean --dry-run -d",
            "git push origin main",
            "git commit -m 'force cleanup wording'",
            "git merge feature/hooks",
            "git revert HEAD",
            "rm -rf /tmp/page-tmd-hook-test",
            "rm -rf ./build/*",
            "cleanup() { git clean -fd; }",
        ]
        for command in cases:
            with self.subTest(command=command):
                self.assert_allowed(command)

    def test_allows_quoted_or_documented_dangerous_text(self) -> None:
        cases = [
            "echo 'git reset --hard'",
            "printf '%s\\n' 'git clean -fd'",
            "cat docs/example-force-push.md",
        ]
        for command in cases:
            with self.subTest(command=command):
                self.assert_allowed(command)

    def test_allows_unrelated_or_malformed_events(self) -> None:
        unrelated = self.run_hook(
            "git reset --hard",
            payload_overrides={"tool_name": "apply_patch"},
        )
        self.assertEqual(unrelated.returncode, 0)
        self.assertEqual(unrelated.stdout, "")

        malformed = subprocess.run(
            [sys.executable, str(HOOK)],
            input="{not-json",
            cwd=self.repo,
            capture_output=True,
            text=True,
            check=False,
        )
        self.assertEqual(malformed.returncode, 0)
        self.assertEqual(malformed.stdout, "")
        self.assertIn("invalid JSON", malformed.stderr)
        self.assertNotIn("{not-json", malformed.stderr)

    def test_allows_valid_json_with_unsupported_top_level_shape(self) -> None:
        for raw_input in ("[]", "null", '"text"'):
            with self.subTest(raw_input=raw_input):
                result = subprocess.run(
                    [sys.executable, str(HOOK)],
                    input=raw_input,
                    cwd=self.repo,
                    capture_output=True,
                    text=True,
                    check=False,
                )
                self.assertEqual(result.returncode, 0, result.stderr)
                self.assertEqual(result.stdout, "")

    def test_allows_unparseable_shell_syntax_without_leaking_command(self) -> None:
        command = "echo 'unterminated"
        result = self.run_hook(command)
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(result.stdout, "")
        self.assertIn("internal ValueError", result.stderr)
        self.assertNotIn(command, result.stderr)


if __name__ == "__main__":
    unittest.main()
