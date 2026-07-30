#!/usr/bin/env python3
"""Suggest specialist reviewers from an explicit path list and diff."""

import argparse
import json
import re
from pathlib import Path


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("--paths-file", required=True, type=Path)
    parser.add_argument("--diff-file", required=True, type=Path)
    return parser.parse_args()


def add(reasons: dict[str, list[str]], reviewer: str, reason: str) -> None:
    reasons.setdefault(reviewer, [])
    if reason not in reasons[reviewer]:
        reasons[reviewer].append(reason)


def main() -> int:
    args = parse_args()
    paths = [
        line.strip()
        for line in args.paths_file.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]
    diff = args.diff_file.read_text(encoding="utf-8")
    lowered = diff.lower()
    reasons: dict[str, list[str]] = {}

    functional = any(
        Path(path).suffix.lower() in {".php", ".js", ".ts", ".tsx", ".jsx"}
        for path in paths
    )
    if functional:
        add(reasons, "test_reviewer", "El diff cambia código funcional.")

    security_terms = (
        "current_user_can",
        "permission_callback",
        "wp_verify_nonce",
        "check_admin_referer",
        "authorization",
        "authenticate",
        "sanitize_",
        "esc_html",
        "esc_attr",
    )
    if any(term in lowered for term in security_terms):
        add(reasons, "security_reviewer", "El diff toca controles o entradas de seguridad.")

    sql_terms = ("$wpdb", "select ", "insert ", "update ", "delete from", "create table")
    has_sql = any(term in lowered for term in sql_terms)
    if has_sql:
        add(reasons, "database_reviewer", "El diff contiene SQL o acceso WPDB.")

    unsafe_sql = bool(
        re.search(
            r"""(?is)(?:select|insert|update|delete).{0,240}(?:\$\w+|\{\$\w+\})""",
            diff,
        )
    )
    if has_sql and unsafe_sql and "prepare(" not in lowered:
        add(reasons, "security_reviewer", "La consulta parece interpolar entrada sin prepare().")

    non_sql_performance = (
        "wp_remote_get",
        "wp_remote_post",
        "set_transient",
        "get_transient",
        "sleep(",
        "usleep(",
        "pagination",
        "paginate",
        "json_encode",
    )
    if any(term in lowered for term in non_sql_performance):
        add(reasons, "performance_reviewer", "Hay señales de rendimiento no SQL.")

    areas = set()
    for path in paths:
        if "/themes/" in path:
            areas.add("theme")
        if "/plugins/" in path:
            areas.add("plugin")
        if "firebase" in path.lower() or "functions/" in path.lower():
            areas.add("firebase")
    if len(areas) > 1:
        add(reasons, "architecture_reviewer", "El cambio cruza límites de componentes.")

    payload = {
        "reviewers": [
            {"name": name, "reasons": reasons[name]} for name in sorted(reasons)
        ],
        "note": "Candidatos solamente; confirmar contra el diff y reviewer-routing.md.",
    }
    print(json.dumps(payload, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
