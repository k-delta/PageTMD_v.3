from __future__ import annotations

import json
import os
import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path
from typing import Optional

ROOT = Path(__file__).resolve().parents[2]
HOOK = ROOT / ".codex/hooks/load-session-context.py"


class SessionStartContextHookTests(unittest.TestCase):
    def setUp(self) -> None:
        self.tempdir = tempfile.TemporaryDirectory()
        self.base = Path(self.tempdir.name)
        self.repo = self.base / "repo"
        subprocess.run(["git", "init", "-q", str(self.repo)], check=True)
        self.git("config", "user.email", "hooks@example.test")
        self.git("config", "user.name", "Hook Tests")
        self.git("checkout", "-qb", "context-tests")
        (self.repo / "AGENTS.md").write_text("root instructions\n", encoding="utf-8")
        (self.repo / "tracked.txt").write_text("initial tracked\n", encoding="utf-8")
        (self.repo / "deleted.txt").write_text("initial deleted\n", encoding="utf-8")
        self.git("add", "AGENTS.md", "tracked.txt", "deleted.txt")
        self.git("commit", "-qm", "baseline context fixture")

    def tearDown(self) -> None:
        self.tempdir.cleanup()

    def git(self, *args: str, check: bool = True) -> subprocess.CompletedProcess[str]:
        return subprocess.run(
            ["git", "-C", str(self.repo), *args],
            capture_output=True,
            text=True,
            check=check,
        )

    def run_hook(
        self,
        *,
        source: str = "startup",
        event_name: str = "SessionStart",
        cwd: Optional[Path] = None,
        raw_input: Optional[str] = None,
        env: Optional[dict[str, str]] = None,
    ) -> subprocess.CompletedProcess[str]:
        payload = {
            "hook_event_name": event_name,
            "source": source,
            "cwd": str(cwd or self.repo),
        }
        return subprocess.run(
            [sys.executable, str(HOOK)],
            input=raw_input if raw_input is not None else json.dumps(payload),
            cwd=cwd or self.repo,
            capture_output=True,
            text=True,
            check=False,
            env=env,
        )

    def output(self, result: subprocess.CompletedProcess[str]) -> dict:
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(result.stderr, "")
        return {} if result.stdout == "" else json.loads(result.stdout)

    def context(self, result: subprocess.CompletedProcess[str]) -> str:
        output = self.output(result)
        return output["hookSpecificOutput"]["additionalContext"]

    def test_clean_repository_context(self) -> None:
        context = self.context(self.run_hook())
        self.assertIn("Repository context", context)
        self.assertIn(f"- Root: {self.repo.resolve()}", context)
        self.assertIn("- Working directory: .", context)
        self.assertIn("- Branch: context-tests", context)
        self.assertIn("- Worktree: clean", context)
        self.assertIn("  - AGENTS.md", context)
        self.assertNotIn("Validation: required", context)

    def test_reports_staged_unstaged_untracked_and_deleted_counts(self) -> None:
        (self.repo / "staged.txt").write_text("staged\n", encoding="utf-8")
        self.git("add", "staged.txt")
        (self.repo / "tracked.txt").write_text("modified\n", encoding="utf-8")
        (self.repo / "untracked.txt").write_text("untracked\n", encoding="utf-8")
        (self.repo / "deleted.txt").unlink()

        context = self.context(self.run_hook())
        self.assertIn(
            "- Worktree: dirty — 1 staged, 2 unstaged, 1 untracked, 1 deleted",
            context,
        )
        self.assertIn("- Validation: required before claiming completion", context)

    def test_nested_working_directory_is_repository_relative(self) -> None:
        nested = self.repo / "src/modules/example"
        nested.mkdir(parents=True)
        context = self.context(self.run_hook(cwd=nested))
        self.assertIn("- Working directory: src/modules/example", context)

    def test_selects_root_and_nearest_nested_agents_file(self) -> None:
        outer = self.repo / "src/AGENTS.md"
        nearest = self.repo / "src/module/AGENTS.md"
        nested = self.repo / "src/module/deep"
        nested.mkdir(parents=True)
        outer.write_text("outer\n", encoding="utf-8")
        nearest.write_text("nearest\n", encoding="utf-8")
        self.git("add", "src/AGENTS.md", "src/module/AGENTS.md")
        self.git("commit", "-qm", "add nested instructions")

        context = self.context(self.run_hook(cwd=nested))
        self.assertIn("  - AGENTS.md", context)
        self.assertIn("  - src/module/AGENTS.md", context)
        self.assertNotIn("src/AGENTS.md", context)

    def test_changed_spec_is_preferred_over_newest_unchanged_spec(self) -> None:
        specs = self.repo / "docs/specs"
        specs.mkdir(parents=True)
        changed = specs / "changed.md"
        newest = specs / "newest.md"
        changed.write_text("changed baseline\n", encoding="utf-8")
        newest.write_text("newest baseline\n", encoding="utf-8")
        (specs / "README.md").write_text("ignored\n", encoding="utf-8")
        (specs / "TEMPLATE.md").write_text("ignored\n", encoding="utf-8")
        self.git("add", "docs/specs")
        self.git("commit", "-qm", "add specs")
        changed.write_text("changed now\n", encoding="utf-8")
        os.utime(newest, ns=(newest.stat().st_atime_ns, newest.stat().st_mtime_ns + 5_000_000))

        context = self.context(self.run_hook())
        self.assertIn("- Active SPEC candidate: docs/specs/changed.md", context)

    def test_changed_plan_is_preferred_over_newest_unchanged_plan(self) -> None:
        plans = self.repo / "docs/superpowers/plans"
        plans.mkdir(parents=True)
        changed = plans / "changed.md"
        newest = plans / "newest.md"
        changed.write_text("# Changed\n", encoding="utf-8")
        newest.write_text("# Newest\n", encoding="utf-8")
        self.git("add", "docs/superpowers/plans")
        self.git("commit", "-qm", "add plans")
        changed.write_text("# Changed now\n", encoding="utf-8")
        os.utime(newest, ns=(newest.stat().st_atime_ns, newest.stat().st_mtime_ns + 5_000_000))

        context = self.context(self.run_hook())
        self.assertIn("- Active plan candidate: docs/superpowers/plans/changed.md", context)

    def test_extracts_at_most_five_pending_plan_steps(self) -> None:
        plans = self.repo / "docs/superpowers/plans"
        plans.mkdir(parents=True)
        plan = plans / "active.md"
        plan.write_text(
            "# Plan\n"
            "- [x] Completed\n"
            + "".join(f"- [ ] Pending step {index}\n" for index in range(1, 8)),
            encoding="utf-8",
        )

        context = self.context(self.run_hook())
        for index in range(1, 6):
            self.assertIn(f"  - Pending step {index}", context)
        self.assertNotIn("Pending step 6", context)

    def test_limits_changed_paths_to_twenty_and_reports_remainder(self) -> None:
        for index in range(23):
            (self.repo / f"file-{index:02d}.txt").write_text("new\n", encoding="utf-8")

        context = self.context(self.run_hook())
        self.assertIn("  - file-00.txt", context)
        self.assertIn("  - file-19.txt", context)
        self.assertNotIn("file-20.txt", context)
        self.assertIn("  - ... and 3 more", context)

    def test_context_is_limited_to_six_thousand_characters(self) -> None:
        plans = self.repo / "docs/superpowers/plans"
        plans.mkdir(parents=True)
        plan = plans / "active.md"
        plan.write_text(
            "# Plan\n"
            + "".join(f"- [ ] Step {index} " + ("x" * 1800) + "\n" for index in range(5)),
            encoding="utf-8",
        )
        for index in range(20):
            directory = self.repo / (f"directory-{index:02d}-" + ("y" * 120))
            directory.mkdir()
            (directory / "changed.txt").write_text("new\n", encoding="utf-8")

        context = self.context(self.run_hook())
        self.assertLessEqual(len(context), 6000)
        self.assertTrue(context.endswith("- Context truncated"))

    def test_sensitive_paths_are_counted_without_revealing_names(self) -> None:
        sensitive = (
            ".env",
            "wp-config.php",
            "private.key",
            "database.sql",
            "secret-notes.txt",
        )
        for name in sensitive:
            (self.repo / name).write_text("sensitive\n", encoding="utf-8")

        context = self.context(self.run_hook())
        self.assertIn("- Sensitive paths omitted: 5", context)
        for name in sensitive:
            self.assertNotIn(name, context)

    def test_supported_session_sources_are_accepted(self) -> None:
        for source in ("startup", "resume", "clear", "compact"):
            with self.subTest(source=source):
                context = self.context(self.run_hook(source=source))
                self.assertIn("Repository context", context)

    def test_unsupported_event_and_source_are_ignored(self) -> None:
        self.assertEqual(self.output(self.run_hook(event_name="PostToolUse")), {})
        self.assertEqual(self.output(self.run_hook(source="unsupported")), {})

    def test_malformed_json_is_ignored(self) -> None:
        self.assertEqual(self.output(self.run_hook(raw_input="{")), {})

    def test_unsupported_top_level_json_shape_is_ignored(self) -> None:
        self.assertEqual(self.output(self.run_hook(raw_input="[]")), {})

    def test_execution_outside_git_is_ignored(self) -> None:
        outside = self.base / "outside"
        outside.mkdir()
        self.assertEqual(self.output(self.run_hook(cwd=outside)), {})

    def test_detached_head_is_reported_without_failure(self) -> None:
        self.git("checkout", "--detach", "-q")
        context = self.context(self.run_hook())
        self.assertIn("- Branch: detached HEAD", context)

    def test_partial_git_failure_preserves_available_context(self) -> None:
        real_git = shutil.which("git")
        self.assertIsNotNone(real_git)
        fake_bin = self.base / "fake-bin"
        fake_bin.mkdir()
        wrapper = fake_bin / "git"
        wrapper.write_text(
            "#!/usr/bin/env python3\n"
            "import os\n"
            "import sys\n"
            f"REAL_GIT = {real_git!r}\n"
            "if 'branch' in sys.argv and '--show-current' in sys.argv:\n"
            "    raise SystemExit(1)\n"
            "os.execv(REAL_GIT, [REAL_GIT, *sys.argv[1:]])\n",
            encoding="utf-8",
        )
        wrapper.chmod(0o755)
        env = os.environ.copy()
        env["PATH"] = str(fake_bin) + os.pathsep + env.get("PATH", "")

        context = self.context(self.run_hook(env=env))
        self.assertIn(f"- Root: {self.repo.resolve()}", context)
        self.assertIn("- Worktree: clean", context)
        self.assertNotIn("- Branch:", context)

    def test_output_excludes_file_contents_diff_lines_and_commit_messages(self) -> None:
        secret_phrase = "PRIVATE-CONTENT-SHOULD-NOT-APPEAR"
        commit_phrase = "PRIVATE-COMMIT-MESSAGE"
        (self.repo / "tracked.txt").write_text(secret_phrase + "\n", encoding="utf-8")
        self.git("add", "tracked.txt")
        self.git("commit", "-qm", commit_phrase)
        (self.repo / "tracked.txt").write_text(secret_phrase + " changed\n", encoding="utf-8")

        context = self.context(self.run_hook())
        self.assertNotIn(secret_phrase, context)
        self.assertNotIn(commit_phrase, context)
        self.assertNotIn("+" + secret_phrase, context)


if __name__ == "__main__":
    unittest.main()
