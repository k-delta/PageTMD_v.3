#!/usr/bin/env python3
"""Validate a PageTMD deployment record and its current local files."""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import sys
from pathlib import Path, PurePosixPath
from typing import Any


SHA256 = re.compile(r"^[0-9a-f]{64}$")
RESULTS = {
    "BLOCKED",
    "READY",
    "AUTHORIZED",
    "DEPLOYED",
    "ROLLED_BACK",
    "FAILED_UNVERIFIED",
}
THEME_PREFIX = "wp-content/themes/blocksy-child/"
PLUGIN_PREFIXES = tuple(
    f"wp-content/plugins/{name}/"
    for name in (
        "tm-chatbot-fase1",
        "tm-equipos-destacados-v2",
        "tm-popup-bienvenida",
        "tm-quiz-equipo-ideal",
    )
)
SECRET_NAMES = {".env", "wp-config.php"}
SECRET_SUFFIXES = {".key", ".pem", ".p12", ".sql", ".bak"}


def nonempty(value: Any) -> bool:
    return isinstance(value, str) and bool(value.strip())


def canonical_digest(environment: str, files: list[dict]) -> str:
    payload = json.dumps(
        {"environment": environment, "files": files},
        ensure_ascii=False,
        sort_keys=True,
        separators=(",", ":"),
    ).encode("utf-8")
    return hashlib.sha256(payload).hexdigest()


def allowed_manifest_path(path: str, risk_class: str) -> bool:
    pure = PurePosixPath(path)
    if (
        pure.is_absolute()
        or ".." in pure.parts
        or pure.name in SECRET_NAMES
        or pure.suffix.lower() in SECRET_SUFFIXES
    ):
        return False
    if risk_class == "application":
        return path.startswith(THEME_PREFIX) or path.startswith(PLUGIN_PREFIXES)
    return risk_class == "infrastructure" and path == "docker-compose.prod.yml"


def valid_checks(checks: Any, require_all_passed: bool) -> bool:
    if not isinstance(checks, list) or not checks:
        return False
    required_count = 0
    for item in checks:
        if not isinstance(item, dict):
            return False
        if (
            not nonempty(item.get("name"))
            or item.get("status") not in {"passed", "failed", "pending"}
            or not nonempty(item.get("evidence"))
        ):
            return False
        if item.get("required") is True:
            required_count += 1
            if require_all_passed and item["status"] != "passed":
                return False
    return required_count > 0


def validate(record: Any, repo_root: Path) -> list[str]:
    errors: list[str] = []
    if not isinstance(record, dict):
        return ["el registro debe ser un objeto JSON"]
    required = {
        "version",
        "deployment_id",
        "mode",
        "environment",
        "manifest",
        "authorization",
        "preflight",
        "rollback",
        "execution",
        "postchecks",
        "rollback_checks",
        "post_sync",
        "blockers",
        "result",
    }
    missing = required - set(record)
    if missing:
        return ["faltan campos: " + ", ".join(sorted(missing))]

    if record["version"] != 1 or not nonempty(record["deployment_id"]):
        errors.append("version y deployment_id inválidos")
    if record["mode"] not in {"prepare", "execute"}:
        errors.append("mode debe ser prepare o execute")
    if record["environment"] != "production":
        errors.append("environment debe ser production")
    if record["result"] not in RESULTS:
        errors.append("result inválido")

    manifest = record["manifest"]
    files: list[dict] = []
    manifest_paths: set[str] = set()
    manifest_digest_value = None
    if not isinstance(manifest, dict):
        errors.append("manifest debe ser un objeto")
    else:
        manifest_digest_value = manifest.get("manifest_sha256")
        files = manifest.get("files", [])
        if (
            manifest.get("version") != 1
            or manifest.get("environment") != record["environment"]
            or not isinstance(files, list)
            or not files
        ):
            errors.append("manifest inválido")
            files = []
        for index, item in enumerate(files):
            if not isinstance(item, dict):
                errors.append(f"manifest.files[{index}] inválido")
                continue
            path = item.get("path")
            if (
                not nonempty(path)
                or not isinstance(item.get("size"), int)
                or item["size"] < 1
                or not isinstance(item.get("sha256"), str)
                or not SHA256.fullmatch(item["sha256"])
                or item.get("risk_class") not in {"application", "infrastructure"}
            ):
                errors.append(f"manifest.files[{index}] inválido")
                continue
            if not allowed_manifest_path(path, item["risk_class"]):
                errors.append(f"ruta no permitida en manifest: {path}")
                continue
            if path in manifest_paths:
                errors.append(f"manifest duplica {path}")
            manifest_paths.add(path)
            local = repo_root / path
            if not local.is_file() or local.is_symlink():
                errors.append(f"archivo local inválido: {path}")
                continue
            content = local.read_bytes()
            if len(content) != item["size"] or hashlib.sha256(content).hexdigest() != item["sha256"]:
                errors.append(f"hash o tamaño local cambió para {path}")
        expected_digest = canonical_digest(record["environment"], files)
        if manifest_digest_value != expected_digest:
            errors.append("manifest_sha256 inválido")

    preflight = record["preflight"]
    if not isinstance(preflight, dict):
        errors.append("preflight debe ser un objeto")
        preflight = {}
    review_status = preflight.get("review_status")
    if review_status not in {"PENDING", "BLOCKED", "READY", "READY_WITH_MINORS"}:
        errors.append("preflight.review_status inválido")
    for field in (
        "local_validations_passed",
        "secrets_scan_passed",
        "hashes_reverified",
    ):
        if not isinstance(preflight.get(field), bool):
            errors.append(f"preflight.{field} debe ser booleano")
    drift = preflight.get("drift")
    if not isinstance(drift, dict):
        errors.append("preflight.drift inválido")
        drift = {}
    drift_status = drift.get("status")
    drift_paths = drift.get("paths")
    if (
        drift_status not in {"not-run", "clean", "intended-only", "unresolved"}
        or not isinstance(drift_paths, list)
        or not all(nonempty(path) for path in drift_paths)
        or not nonempty(drift.get("evidence"))
    ):
        errors.append("preflight.drift inválido")
        drift_paths = []
    if drift_status == "intended-only" and not set(drift_paths).issubset(manifest_paths):
        errors.append("deriva intended-only contiene rutas fuera del manifest")
    if drift_status == "clean" and drift_paths:
        errors.append("deriva clean no puede enumerar rutas")

    rollback = record["rollback"]
    rollback_targets: set[str] = set()
    if not isinstance(rollback, list):
        errors.append("rollback debe ser una lista")
        rollback = []
    for index, item in enumerate(rollback):
        if not isinstance(item, dict):
            errors.append(f"rollback[{index}] inválido")
            continue
        target = item.get("target")
        strategy = item.get("strategy")
        if target in rollback_targets:
            errors.append(f"rollback duplica {target}")
        if nonempty(target):
            rollback_targets.add(target)
        if (
            target not in manifest_paths
            or strategy not in {"restore-backup", "remove-created"}
            or not isinstance(item.get("restore_steps"), list)
            or not item["restore_steps"]
            or not all(nonempty(step) for step in item["restore_steps"])
            or not isinstance(item.get("verification_steps"), list)
            or not item["verification_steps"]
            or not all(nonempty(step) for step in item["verification_steps"])
            or not isinstance(item.get("performed"), bool)
            or not isinstance(item.get("verified"), bool)
        ):
            errors.append(f"rollback[{index}] inválido")
            continue
        artifact = item.get("artifact")
        if strategy == "restore-backup" and record["result"] not in {"READY", "BLOCKED"}:
            if (
                not isinstance(artifact, dict)
                or not nonempty(artifact.get("path"))
                or not isinstance(artifact.get("size"), int)
                or artifact["size"] < 1
                or not isinstance(artifact.get("sha256"), str)
                or not SHA256.fullmatch(artifact["sha256"])
                or artifact.get("verified") is not True
            ):
                errors.append(f"backup de rollback[{index}] inválido")
        if strategy == "remove-created" and artifact is not None:
            errors.append(f"rollback[{index}] remove-created no usa backup")
    if rollback_targets != manifest_paths:
        errors.append("rollback debe cubrir exactamente el manifest")

    execution = record["execution"]
    if not isinstance(execution, dict):
        errors.append("execution inválido")
        execution = {}
    execution_files = execution.get("files")
    if not isinstance(execution_files, list):
        errors.append("execution.files debe ser una lista")
        execution_files = []

    blockers = record["blockers"]
    if not isinstance(blockers, list) or not all(nonempty(item) for item in blockers):
        errors.append("blockers debe ser una lista de textos")
        blockers = []

    result = record["result"]
    authorization = record["authorization"]
    if result not in {"BLOCKED"}:
        if review_status not in {"READY", "READY_WITH_MINORS"}:
            errors.append("preflight requiere revisión aprobada")
        for field in (
            "local_validations_passed",
            "secrets_scan_passed",
            "hashes_reverified",
        ):
            if preflight.get(field) is not True:
                errors.append(f"preflight.{field} debe ser true")

    if result == "READY":
        if (
            record["mode"] != "prepare"
            or authorization is not None
            or execution.get("performed") is not False
            or execution.get("completed") is not False
            or execution_files
            or blockers
        ):
            errors.append("READY solo representa preparación sin escritura")
    elif result == "BLOCKED":
        if execution.get("performed") is not False or not blockers:
            errors.append("BLOCKED exige cero escrituras y blockers")
    else:
        if record["mode"] != "execute" or not isinstance(authorization, dict):
            errors.append("ejecución exige autorización")
            authorization = {}
        if (
            authorization.get("authorized") is not True
            or authorization.get("current_request") is not True
            or authorization.get("environment") != record["environment"]
            or authorization.get("manifest_sha256") != manifest_digest_value
            or set(authorization.get("scope", [])) != manifest_paths
        ):
            errors.append("autorización no coincide con el manifest")
        if drift_status not in {"clean", "intended-only"}:
            errors.append("deriva no resuelta impide ejecutar")
        if result == "AUTHORIZED":
            if (
                execution.get("performed") is not False
                or execution.get("completed") is not False
                or execution_files
                or blockers
            ):
                errors.append("AUTHORIZED exige gates completos y cero escrituras")
        else:
            if execution.get("performed") is not True:
                errors.append("el estado de ejecución requiere performed=true")
            if set(execution_files) != manifest_paths:
                errors.append("execution.files debe coincidir exactamente con el manifest")

    post_sync = record["post_sync"]
    if not isinstance(post_sync, dict):
        errors.append("post_sync inválido")
        post_sync = {}
    if result == "DEPLOYED":
        if (
            execution.get("completed") is not True
            or blockers
            or not valid_checks(record["postchecks"], True)
            or post_sync.get("status") != "passed"
            or not nonempty(post_sync.get("evidence"))
        ):
            errors.append("DEPLOYED exige ejecución y verificaciones completas")
    elif result == "ROLLED_BACK":
        if (
            not rollback
            or not all(
                item.get("performed") is True and item.get("verified") is True
                for item in rollback
                if isinstance(item, dict)
            )
            or not valid_checks(record["rollback_checks"], True)
            or post_sync.get("status") != "passed"
            or not nonempty(post_sync.get("evidence"))
        ):
            errors.append("rollback ROLLED_BACK debe estar ejecutado y verificado")
    elif result == "FAILED_UNVERIFIED":
        if execution.get("performed") is not True or not blockers:
            errors.append("FAILED_UNVERIFIED exige escritura y blockers")
    return errors


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("record", type=Path)
    parser.add_argument("--repo-root", required=True, type=Path)
    args = parser.parse_args()
    try:
        record = json.loads(args.record.read_text(encoding="utf-8"))
        errors = validate(record, args.repo_root.resolve())
    except (OSError, json.JSONDecodeError) as error:
        print(f"Error: no se pudo leer el registro: {error}", file=sys.stderr)
        return 1
    for error in errors:
        print(f"Error: {error}", file=sys.stderr)
    if errors:
        return 1
    print("Registro de despliegue válido.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
