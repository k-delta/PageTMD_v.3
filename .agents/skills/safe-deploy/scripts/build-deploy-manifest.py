#!/usr/bin/env python3
"""Build a deterministic manifest for exact PageTMD deployment files."""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import sys
from pathlib import Path, PurePosixPath


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


def fail(message: str) -> None:
    print(f"Error: {message}", file=sys.stderr)


def normalize(raw: str) -> str:
    value = raw.strip().replace("\\", "/")
    path = PurePosixPath(value)
    if not value or path.is_absolute() or ".." in path.parts:
        raise ValueError(f"ruta no permitida: {raw}")
    return path.as_posix()


def classify(path: str, allow_infrastructure: bool) -> str:
    pure = PurePosixPath(path)
    if pure.name in SECRET_NAMES or pure.suffix.lower() in SECRET_SUFFIXES:
        raise ValueError(f"ruta no permitida: {path}")
    if path.startswith(THEME_PREFIX) or path.startswith(PLUGIN_PREFIXES):
        return "application"
    if path == "docker-compose.prod.yml" and allow_infrastructure:
        return "infrastructure"
    raise ValueError(f"ruta no permitida: {path}")


def manifest_digest(environment: str, files: list[dict]) -> str:
    canonical = json.dumps(
        {"environment": environment, "files": files},
        ensure_ascii=False,
        sort_keys=True,
        separators=(",", ":"),
    ).encode("utf-8")
    return hashlib.sha256(canonical).hexdigest()


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--repo-root", required=True, type=Path)
    parser.add_argument("--paths-file", required=True, type=Path)
    parser.add_argument("--output", required=True, type=Path)
    parser.add_argument("--environment", required=True, choices=["production"])
    parser.add_argument("--allow-infrastructure", action="store_true")
    args = parser.parse_args()

    repo_root = args.repo_root.resolve()
    raw_paths = [
        line
        for line in args.paths_file.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]
    if not raw_paths:
        fail("la lista de archivos está vacía")
        return 1

    try:
        paths = [normalize(item) for item in raw_paths]
        if len(set(paths)) != len(paths):
            raise ValueError("la lista contiene archivos duplicados")
        files = []
        for relative in sorted(paths):
            source = repo_root / relative
            candidate = source.resolve()
            if os.path.commonpath([str(repo_root), str(candidate)]) != str(repo_root):
                raise ValueError(f"ruta no permitida: {relative}")
            if source.is_symlink() or not candidate.is_file():
                raise ValueError(f"el objetivo debe ser un archivo regular: {relative}")
            risk_class = classify(relative, args.allow_infrastructure)
            content = candidate.read_bytes()
            if not content:
                raise ValueError(f"el archivo de despliegue está vacío: {relative}")
            files.append(
                {
                    "path": relative,
                    "size": len(content),
                    "sha256": hashlib.sha256(content).hexdigest(),
                    "risk_class": risk_class,
                }
            )
    except (OSError, ValueError) as error:
        fail(str(error))
        return 1

    manifest = {
        "version": 1,
        "environment": args.environment,
        "files": files,
        "manifest_sha256": manifest_digest(args.environment, files),
    }
    args.output.write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(f"Manifest creado: {args.output}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
