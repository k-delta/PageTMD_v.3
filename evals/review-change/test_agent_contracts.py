import json
import re
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
AGENTS = ROOT / ".codex/agents"
CASES = Path(__file__).resolve().parent / "agent-cases"
REVIEWERS = {
    "architecture-reviewer.toml": "architecture_reviewer",
    "security-reviewer.toml": "security_reviewer",
    "database-reviewer.toml": "database_reviewer",
    "performance-reviewer.toml": "performance_reviewer",
    "test-reviewer.toml": "test_reviewer",
}
RUNNER = {"test-runner.toml": "test_runner"}


def read_agent(filename: str) -> str:
    return (AGENTS / filename).read_text(encoding="utf-8")


def normalized_agent(filename: str) -> str:
    return re.sub(r"\s+", " ", read_agent(filename).lower())


def scalar(source: str, key: str) -> str:
    match = re.search(rf'(?m)^{re.escape(key)}\s*=\s*"([^"]+)"\s*$', source)
    if not match:
        raise AssertionError(f"{key} no está definido como string")
    return match.group(1)


class AgentConfigurationContractTests(unittest.TestCase):
    def test_toml_uses_one_required_scalar_and_one_instruction_block(self) -> None:
        for filename in set(REVIEWERS) | set(RUNNER):
            with self.subTest(agent=filename):
                source = read_agent(filename)
                for key in ("name", "description", "sandbox_mode"):
                    self.assertEqual(
                        len(re.findall(rf"(?m)^{key}\s*=", source)),
                        1,
                        f"{filename}: {key}",
                    )
                    scalar(source, key)
                self.assertEqual(source.count('developer_instructions = """'), 1)
                self.assertEqual(source.count('"""'), 2)

    def test_only_approved_specialists_and_runner_exist(self) -> None:
        expected = set(REVIEWERS) | set(RUNNER)
        actual = {path.name for path in AGENTS.glob("*.toml")}
        self.assertEqual(actual, expected)
        self.assertNotIn("clean-code-reviewer.toml", actual)

    def test_agent_names_and_sandboxes_match_their_role(self) -> None:
        for filename, expected_name in REVIEWERS.items():
            with self.subTest(agent=expected_name):
                source = read_agent(filename)
                self.assertEqual(scalar(source, "name"), expected_name)
                self.assertEqual(scalar(source, "sandbox_mode"), "read-only")

        source = read_agent("test-runner.toml")
        self.assertEqual(scalar(source, "name"), "test_runner")
        self.assertEqual(scalar(source, "sandbox_mode"), "workspace-write")

    def test_reviewers_limit_context_and_use_the_finding_contract(self) -> None:
        required = {
            "contexto limitado",
            "no_findings",
            "alcance inspeccionado",
            "archivo",
            "línea",
            "causa raíz",
            "evidencia",
            "impacto",
            "severidad",
            "recomendación",
            "confianza",
        }
        for filename, expected_name in REVIEWERS.items():
            with self.subTest(agent=expected_name):
                source = normalized_agent(filename)
                missing = sorted(term for term in required if term not in source)
                self.assertEqual(missing, [])

    def test_reviewers_declare_exclusive_boundaries(self) -> None:
        expected_terms = {
            "architecture-reviewer.toml": {
                "fuente canónica",
                "no reportes seguridad",
                "no reportes rendimiento",
                "no reportes pruebas",
            },
            "security-reviewer.toml": {
                "explotabilidad",
                "sql correcto o eficiente",
                "no reportes rendimiento",
                "no reportes cobertura",
            },
            "database-reviewer.toml": {
                "n+1 sql",
                "no reportes llamadas externas",
                "no ejecutes escrituras",
                "no reportes cobertura",
            },
            "performance-reviewer.toml": {
                "rendimiento no sql",
                "no reportes sql",
                "no reportes seguridad",
                "no reportes cobertura",
            },
            "test-reviewer.toml": {
                "no ejecutes pruebas",
                "no reportes estilo",
                "no reportes arquitectura",
                "no reportes vulnerabilidades",
            },
        }
        for filename, terms in expected_terms.items():
            with self.subTest(agent=filename):
                source = normalized_agent(filename)
                missing = sorted(term for term in terms if term not in source)
                self.assertEqual(missing, [])

    def test_runner_executes_only_explicit_focal_commands(self) -> None:
        source = normalized_agent("test-runner.toml")
        required = {
            "comando focalizado explícito",
            "decisión `run`",
            "decisión `refuse`",
            "decisión `blocked`",
            "no ejecutes producción",
            "no modifiques código fuente",
            "no modifiques pruebas",
            "código de salida",
            "bloqueo de entorno",
            "regresión del cambio",
            "no generes hallazgos",
        }
        missing = sorted(term for term in required if term not in source)
        self.assertEqual(missing, [])


class AgentEvaluationCaseTests(unittest.TestCase):
    def test_each_agent_has_positive_negative_and_false_positive_cases(self) -> None:
        expected_agents = set(REVIEWERS.values()) | set(RUNNER.values())
        actual_agents = set()

        for case_file in CASES.glob("*.json"):
            payload = json.loads(case_file.read_text(encoding="utf-8"))
            actual_agents.add(payload["agent"])
            self.assertEqual(
                set(payload["cases"]),
                {"positive", "negative", "false_positive"},
                case_file.name,
            )
            for case_name, case in payload["cases"].items():
                with self.subTest(agent=payload["agent"], case=case_name):
                    self.assertTrue(case["change"].strip())
                    self.assertIn(
                        case["expected_outcome"],
                        {"FINDING", "NO_FINDINGS", "RUN", "REFUSE", "BLOCKED"},
                    )
                    self.assertTrue(case["reason"].strip())
                    if case["expected_outcome"] == "FINDING":
                        self.assertTrue(case["path"].strip())
                        self.assertGreater(case["line"], 0)
                        self.assertTrue(case["evidence"].strip())
                    if case["expected_outcome"] == "RUN":
                        self.assertTrue(case["command"].strip())
                        self.assertTrue(case["working_directory"].strip())
                    if case["expected_outcome"] in {"RUN", "BLOCKED"}:
                        self.assertTrue(case["scope"].strip())
                        self.assertTrue(case["motive"].strip())
                        self.assertTrue(case["authorization"].strip())
                    if payload["agent"] == "test_runner":
                        self.assertTrue(case["requested_action"].strip())
                    if case["expected_outcome"] == "BLOCKED":
                        self.assertTrue(case["command"].strip())
                        self.assertTrue(case["working_directory"].strip())
                        self.assertTrue(case["evidence"].strip())

        self.assertEqual(actual_agents, expected_agents)


if __name__ == "__main__":
    unittest.main()
