from __future__ import annotations

import hashlib
import json
import subprocess
import tempfile
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SCRIPTS = ROOT / ".agents/skills/safe-deploy/scripts"
BUILD = SCRIPTS / "build-deploy-manifest.py"
VALIDATE = SCRIPTS / "validate-deploy-record.py"


class SafeDeployToolsTests(unittest.TestCase):
    def setUp(self) -> None:
        self.temp_dir = tempfile.TemporaryDirectory()
        self.repo = Path(self.temp_dir.name) / "repo"
        self.repo.mkdir()
        self.relative_path = (
            "wp-content/themes/blocksy-child/assets/css/tmd-inventory-api.css"
        )
        self.target = self.repo / self.relative_path
        self.target.parent.mkdir(parents=True)
        self.target.write_text(".inventory { display: grid; }\n", encoding="utf-8")

    def tearDown(self) -> None:
        self.temp_dir.cleanup()

    def build_manifest(
        self, paths: list[str] | None = None
    ) -> tuple[subprocess.CompletedProcess[str], dict | None]:
        paths_file = self.repo / "paths.txt"
        paths_file.write_text(
            "\n".join(paths or [self.relative_path]) + "\n", encoding="utf-8"
        )
        output = self.repo / "manifest.json"
        result = subprocess.run(
            [
                "python3",
                str(BUILD),
                "--repo-root",
                str(self.repo),
                "--paths-file",
                str(paths_file),
                "--output",
                str(output),
                "--environment",
                "production",
            ],
            check=False,
            capture_output=True,
            text=True,
        )
        manifest = (
            json.loads(output.read_text(encoding="utf-8"))
            if output.exists()
            else None
        )
        return result, manifest

    def validate(
        self, record: dict
    ) -> subprocess.CompletedProcess[str]:
        record_file = self.repo / "deploy-record.json"
        record_file.write_text(json.dumps(record), encoding="utf-8")
        return subprocess.run(
            [
                "python3",
                str(VALIDATE),
                str(record_file),
                "--repo-root",
                str(self.repo),
            ],
            check=False,
            capture_output=True,
            text=True,
        )

    def manifest(self) -> dict:
        files = [
            {
                "path": self.relative_path,
                "size": self.target.stat().st_size,
                "sha256": hashlib.sha256(self.target.read_bytes()).hexdigest(),
                "risk_class": "application",
            }
        ]
        canonical = json.dumps(
            {"environment": "production", "files": files},
            ensure_ascii=False,
            sort_keys=True,
            separators=(",", ":"),
        ).encode("utf-8")
        return {
            "version": 1,
            "environment": "production",
            "files": files,
            "manifest_sha256": hashlib.sha256(canonical).hexdigest(),
        }

    def ready_record(self, manifest: dict) -> dict:
        return {
            "version": 1,
            "deployment_id": "deploy-001",
            "mode": "prepare",
            "environment": "production",
            "manifest": manifest,
            "authorization": None,
            "preflight": {
                "review_status": "READY",
                "local_validations_passed": True,
                "secrets_scan_passed": True,
                "hashes_reverified": True,
                "drift": {
                    "status": "not-run",
                    "paths": [],
                    "evidence": "Se ejecutará inmediatamente antes de escribir.",
                },
            },
            "rollback": [
                {
                    "target": self.relative_path,
                    "strategy": "restore-backup",
                    "artifact": None,
                    "restore_steps": ["Restaurar únicamente el archivo objetivo."],
                    "verification_steps": [
                        "Validar asset, HTTP y flujo afectado.",
                        "Ejecutar sync-production.sh --check.",
                    ],
                    "performed": False,
                    "verified": False,
                }
            ],
            "execution": {
                "performed": False,
                "completed": False,
                "files": [],
            },
            "postchecks": [],
            "rollback_checks": [],
            "post_sync": {"status": "not-run", "evidence": ""},
            "blockers": [],
            "result": "READY",
        }

    def deployed_record(self, manifest: dict) -> dict:
        record = self.ready_record(manifest)
        record["mode"] = "execute"
        record["authorization"] = {
            "authorized": True,
            "current_request": True,
            "environment": "production",
            "manifest_sha256": manifest["manifest_sha256"],
            "scope": [self.relative_path],
        }
        record["preflight"]["drift"] = {
            "status": "intended-only",
            "paths": [self.relative_path],
            "evidence": "La diferencia corresponde al manifiesto autorizado.",
        }
        record["rollback"][0]["artifact"] = {
            "path": "/restricted/backups/file.css",
            "size": 31,
            "sha256": "a" * 64,
            "verified": True,
        }
        record["execution"] = {
            "performed": True,
            "completed": True,
            "files": [self.relative_path],
        }
        record["postchecks"] = [
            {
                "name": "HTTP y flujo afectado",
                "required": True,
                "status": "passed",
                "evidence": "HTTP 200 y comprobación visual completada.",
            }
        ]
        record["post_sync"] = {
            "status": "passed",
            "evidence": "Producción coincide con las rutas versionadas.",
        }
        record["result"] = "DEPLOYED"
        return record

    def authorized_record(self, manifest: dict) -> dict:
        record = self.deployed_record(manifest)
        record["execution"] = {
            "performed": False,
            "completed": False,
            "files": [],
        }
        record["postchecks"] = []
        record["post_sync"] = {"status": "not-run", "evidence": ""}
        record["result"] = "AUTHORIZED"
        return record

    def test_builds_sorted_manifest_with_hash_and_size(self) -> None:
        second_relative = (
            "wp-content/plugins/tm-popup-bienvenida/assets/popup.js"
        )
        second = self.repo / second_relative
        second.parent.mkdir(parents=True)
        second.write_text("window.popupReady = true;\n", encoding="utf-8")

        result, manifest = self.build_manifest(
            [second_relative, self.relative_path]
        )

        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(
            [item["path"] for item in manifest["files"]],
            sorted([second_relative, self.relative_path]),
        )
        first = next(
            item for item in manifest["files"] if item["path"] == self.relative_path
        )
        self.assertEqual(first["size"], self.target.stat().st_size)
        self.assertEqual(
            first["sha256"], hashlib.sha256(self.target.read_bytes()).hexdigest()
        )
        self.assertRegex(manifest["manifest_sha256"], r"^[0-9a-f]{64}$")

    def test_rejects_directory_instead_of_exact_files(self) -> None:
        result, _ = self.build_manifest(
            ["wp-content/themes/blocksy-child"]
        )
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("archivo", result.stderr.lower())

    def test_rejects_symlink_even_when_target_is_allowed_file(self) -> None:
        real = self.target.with_name("real.css")
        self.target.rename(real)
        self.target.symlink_to(real)
        result, _ = self.build_manifest()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("archivo regular", result.stderr.lower())

    def test_rejects_zero_byte_deploy_file(self) -> None:
        self.target.write_bytes(b"")
        result, _ = self.build_manifest()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("vacío", result.stderr.lower())

    def test_rejects_secret_core_and_historical_paths(self) -> None:
        unsafe_paths = [
            "wp-config.php",
            "wp-admin/core.php",
            ".codex-tmp/file.php",
            "production-snapshot/pages.json",
            "wp-content/plugins/third-party/plugin.php",
        ]
        for relative in unsafe_paths:
            unsafe = self.repo / relative
            unsafe.parent.mkdir(parents=True, exist_ok=True)
            unsafe.write_text("unsafe\n", encoding="utf-8")
        result, _ = self.build_manifest(unsafe_paths)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("no permitida", result.stderr.lower())

    def test_accepts_ready_prepare_without_execution_authorization(self) -> None:
        manifest = self.manifest()
        result = self.validate(self.ready_record(manifest))
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_accepts_authorized_execute_before_first_write(self) -> None:
        manifest = self.manifest()
        result = self.validate(self.authorized_record(manifest))
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_accepts_blocked_record_with_failed_preflight_gate(self) -> None:
        manifest = self.manifest()
        record = self.ready_record(manifest)
        record["preflight"]["review_status"] = "BLOCKED"
        record["preflight"]["local_validations_passed"] = False
        record["blockers"] = ["La revisión tiene un hallazgo Important."]
        record["result"] = "BLOCKED"
        result = self.validate(record)
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_validator_rejects_handwritten_manifest_outside_allowlist(self) -> None:
        unsafe_relative = "wp-content/plugins/third-party/plugin.php"
        unsafe = self.repo / unsafe_relative
        unsafe.parent.mkdir(parents=True)
        unsafe.write_text("<?php\n", encoding="utf-8")
        item = {
            "path": unsafe_relative,
            "size": unsafe.stat().st_size,
            "sha256": hashlib.sha256(unsafe.read_bytes()).hexdigest(),
            "risk_class": "application",
        }
        manifest = {
            "version": 1,
            "environment": "production",
            "files": [item],
            "manifest_sha256": hashlib.sha256(
                json.dumps(
                    {"environment": "production", "files": [item]},
                    ensure_ascii=False,
                    sort_keys=True,
                    separators=(",", ":"),
                ).encode("utf-8")
            ).hexdigest(),
        }
        record = self.ready_record(manifest)
        record["rollback"][0]["target"] = unsafe_relative
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("no permitida", result.stderr.lower())

    def test_rejects_clean_drift_with_listed_paths(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["preflight"]["drift"] = {
            "status": "clean",
            "paths": [self.relative_path],
            "evidence": "La salida dice clean pero enumera diferencias.",
        }
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("deriva", result.stderr.lower())

    def test_rejects_stale_manifest_after_file_changes(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        self.target.write_text(".inventory { display: block; }\n", encoding="utf-8")
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("hash", result.stderr.lower())

    def test_rejects_authorization_for_different_manifest(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["authorization"]["manifest_sha256"] = "b" * 64
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("autoriz", result.stderr.lower())

    def test_rejects_unresolved_drift(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["preflight"]["drift"] = {
            "status": "unresolved",
            "paths": ["wp-content/themes/blocksy-child/inc/hotfix.php"],
            "evidence": "Hotfix productivo ajeno.",
        }
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("deriva", result.stderr.lower())

    def test_rejects_zero_byte_backup(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["rollback"][0]["artifact"]["size"] = 0
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("backup", result.stderr.lower())

    def test_rejects_execution_outside_manifest(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["execution"]["files"].append(
            "wp-content/themes/blocksy-child/functions.php"
        )
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("manifest", result.stderr.lower())

    def test_rejects_deployed_with_failed_postcheck(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["postchecks"][0]["status"] = "failed"
        record["postchecks"][0]["evidence"] = "Cuenta responde HTTP 500."
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("DEPLOYED", result.stderr)

    def test_rejects_unverified_rollback(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["result"] = "ROLLED_BACK"
        record["rollback"][0]["performed"] = True
        record["rollback"][0]["verified"] = False
        result = self.validate(record)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("rollback", result.stderr.lower())

    def test_accepts_failed_unverified_after_partial_execution(self) -> None:
        manifest = self.manifest()
        record = self.deployed_record(manifest)
        record["execution"]["completed"] = False
        record["postchecks"][0]["status"] = "failed"
        record["post_sync"] = {"status": "failed", "evidence": "Deriva pendiente."}
        record["blockers"] = ["Estado productivo no verificado."]
        record["result"] = "FAILED_UNVERIFIED"
        result = self.validate(record)
        self.assertEqual(result.returncode, 0, result.stderr)


if __name__ == "__main__":
    unittest.main()
