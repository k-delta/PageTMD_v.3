from __future__ import annotations

import json
import os
import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
HOOKS_JSON = ROOT / ".codex/hooks.json"
CONFIG_TOML = ROOT / ".codex/config.toml"
COMMAND_GUARD = ROOT / ".codex/hooks/block-dangerous-commands.py"
DIFF_HYGIENE = ROOT / ".codex/hooks/check-diff-hygiene.py"
STOP_GATE = ROOT / ".codex/hooks/validate-before-stop.sh"


class HookConfigurationTests(unittest.TestCase):
    def setUp(self) -> None:
        self.config = json.loads(HOOKS_JSON.read_text(encoding="utf-8"))

    def test_registers_exactly_the_three_designed_events(self) -> None:
        hooks = self.config["hooks"]
        self.assertEqual(set(hooks), {"PreToolUse", "PostToolUse", "Stop"})
        self.assertEqual(len(hooks["PreToolUse"]), 1)
        self.assertEqual(len(hooks["PostToolUse"]), 1)
        self.assertEqual(len(hooks["Stop"]), 1)

    def test_pre_tool_use_registration_is_bash_only(self) -> None:
        group = self.config["hooks"]["PreToolUse"][0]
        self.assertEqual(group["matcher"], "^Bash$")
        self.assertEqual(len(group["hooks"]), 1)
        handler = group["hooks"][0]
        self.assertEqual(handler["type"], "command")
        self.assertEqual(handler["timeout"], 5)
        self.assertIn("git rev-parse --show-toplevel", handler["command"])
        self.assertIn(".codex/hooks/block-dangerous-commands.py", handler["command"])
        self.assertIn("python3", handler["command"])

    def test_post_tool_use_registration_targets_file_writes(self) -> None:
        group = self.config["hooks"]["PostToolUse"][0]
        self.assertEqual(group["matcher"], "^(apply_patch|Edit|Write)$")
        self.assertEqual(len(group["hooks"]), 1)
        handler = group["hooks"][0]
        self.assertEqual(handler["type"], "command")
        self.assertEqual(handler["timeout"], 5)
        self.assertEqual(handler["statusMessage"], "Checking diff hygiene")
        self.assertIn("git rev-parse --show-toplevel", handler["command"])
        self.assertIn(".codex/hooks/check-diff-hygiene.py", handler["command"])
        self.assertIn("python3", handler["command"])

    def test_stop_registration_has_no_ignored_matcher(self) -> None:
        group = self.config["hooks"]["Stop"][0]
        self.assertNotIn("matcher", group)
        self.assertEqual(len(group["hooks"]), 1)
        handler = group["hooks"][0]
        self.assertEqual(handler["type"], "command")
        self.assertEqual(handler["timeout"], 5)
        self.assertIn("git rev-parse --show-toplevel", handler["command"])
        self.assertIn(".codex/hooks/validate-before-stop.sh", handler["command"])

    def test_all_referenced_scripts_exist_and_stop_hook_is_executable(self) -> None:
        self.assertTrue(COMMAND_GUARD.is_file())
        self.assertTrue(DIFF_HYGIENE.is_file())
        self.assertTrue(STOP_GATE.is_file())
        self.assertTrue(os.access(STOP_GATE, os.X_OK))

    def test_config_toml_does_not_duplicate_hooks(self) -> None:
        source = CONFIG_TOML.read_text(encoding="utf-8") if CONFIG_TOML.exists() else ""
        self.assertIsNone(re.search(r"(?m)^\s*\[hooks\]\s*$", source))
        self.assertIsNone(re.search(r"(?m)^\s*\[\[hooks\.", source))
        self.assertIsNone(re.search(r"(?m)^\s*hooks\s*=", source))


if __name__ == "__main__":
    unittest.main()
