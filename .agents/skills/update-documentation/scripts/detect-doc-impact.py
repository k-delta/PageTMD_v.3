#!/usr/bin/env python3
"""Suggest documentation candidates from an explicit path list and diff."""

import argparse
import json
from pathlib import Path


def add(candidates: dict[str, list[str]], document: str, reason: str) -> None:
    candidates.setdefault(document, [])
    if reason not in candidates[document]:
        candidates[document].append(reason)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--paths-file", required=True, type=Path)
    parser.add_argument("--diff-file", required=True, type=Path)
    args = parser.parse_args()

    paths = [
        line.strip()
        for line in args.paths_file.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]
    diff = args.diff_file.read_text(encoding="utf-8")
    lowered_paths = "\n".join(paths).lower()
    lowered = diff.lower()
    candidates: dict[str, list[str]] = {}

    inventory_signal = any(
        term in lowered_paths or term in lowered
        for term in (
            "tmd-inventory",
            "equipos-destacados",
            "firebase",
            "firestore",
            "listarequipos",
            "inventory response contract",
        )
    )
    if inventory_signal:
        add(
            candidates,
            "docs/domain/INVENTORY.md",
            "Hay señales del contrato o integración de inventario.",
        )

    crosses_boundary = (
        any("/themes/" in path for path in paths)
        and any("/plugins/" in path for path in paths)
    )
    architectural_terms = ("contract", "responsibility", "source of truth", "canonical")
    if crosses_boundary and any(term in lowered for term in architectural_terms):
        add(
            candidates,
            "docs/architecture/REPO_MAP.md",
            "El cambio cruza componentes y señala un contrato o responsabilidad.",
        )

    if "tmd-seo.php" in lowered_paths or "rank_math" in lowered:
        add(candidates, "docs/domain/SEO.md", "Hay señales de comportamiento SEO.")

    if any(
        term in lowered_paths
        for term in ("tmd-header.php", "tmd-footer.php", "tmd-equipment-type-guides.php")
    ):
        add(
            candidates,
            "docs/domain/NAVIGATION.md",
            "Hay señales de navegación o componentes compartidos.",
        )

    if "tmd-account.php" in lowered_paths or "woocommerce" in lowered:
        add(candidates, "docs/domain/COMMERCE.md", "Hay señales de cuenta o comercio.")

    if any(
        term in lowered
        for term in ("alquiler", "venta", "mantenimiento", "marca pública")
    ):
        add(
            candidates,
            "docs/domain/BUSINESS_RULES.md",
            "Hay señales de reglas comerciales permanentes.",
        )

    if "scripts/sync-production.sh" in lowered_paths or "deployment" in lowered:
        add(
            candidates,
            "docs/runbooks/DEPLOYMENT.md",
            "Hay señales del procedimiento de despliegue.",
        )
    if any(term in lowered_paths or term in lowered for term in ("backup", "restore")):
        add(
            candidates,
            "docs/runbooks/BACKUP_RESTORE.md",
            "Hay señales de backup o restauración.",
        )
    if any(term in lowered for term in ("docker compose", "openlitespeed", "dns", "tls")):
        add(
            candidates,
            "docs/runbooks/PRODUCTION.md",
            "Hay señales de operación productiva.",
        )

    print(
        json.dumps(
            {
                "candidates": [
                    {"document": document, "reasons": candidates[document]}
                    for document in sorted(candidates)
                ],
                "note": "Candidatos solamente; confirmar impacto semántico.",
            },
            ensure_ascii=False,
            indent=2,
        )
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
