#!/usr/bin/env bash
set -euo pipefail

usage() {
  echo "Uso: $0 --output ARCHIVO (--range BASE HEAD | --staged | --working)" >&2
}

output=""
mode=""
target_count=0
base=""
head=""

while (($#)); do
  case "$1" in
    --output)
      [[ $# -ge 2 ]] || { usage; exit 2; }
      output="$2"
      shift 2
      ;;
    --range)
      [[ $# -ge 3 ]] || { usage; exit 2; }
      mode="range"
      target_count=$((target_count + 1))
      base="$2"
      head="$3"
      shift 3
      ;;
    --staged)
      mode="staged"
      target_count=$((target_count + 1))
      shift
      ;;
    --working)
      mode="working"
      target_count=$((target_count + 1))
      shift
      ;;
    *)
      usage
      exit 2
      ;;
  esac
done

[[ -n "$output" && -n "$mode" && "$target_count" -eq 1 ]] || {
  usage
  exit 2
}
[[ -d "$(dirname "$output")" ]] || {
  echo "El directorio de salida no existe: $(dirname "$output")" >&2
  exit 2
}
git rev-parse --is-inside-work-tree >/dev/null

if [[ "$mode" == "range" ]]; then
  git rev-parse --verify "${base}^{commit}" >/dev/null
  git rev-parse --verify "${head}^{commit}" >/dev/null
  target="range ${base}..${head}"
  diff_args=("$base" "$head")
elif [[ "$mode" == "staged" ]]; then
  target="staged"
  diff_args=(--cached)
else
  target="working tree against HEAD"
  git rev-parse --verify "HEAD^{commit}" >/dev/null
  diff_args=(HEAD)
fi

review_tmp_root="${TMPDIR:-/tmp}"
temp_file="$(mktemp "${review_tmp_root%/}/review-package.XXXXXX")"
trap 'rm -f "$temp_file"' EXIT

{
  echo "# Review package"
  echo
  echo "- Target: $target"
  if [[ "$mode" == "range" ]]; then
    echo "- Base: $base"
    echo "- Head: $head"
  fi
  echo
  echo "## Git status"
  echo '```text'
  git status --short
  echo '```'
  echo
  echo "## Changed paths"
  echo '```text'
  git diff --no-ext-diff --find-renames --name-status "${diff_args[@]}"
  if [[ "$mode" == "working" ]]; then
    git ls-files --others --exclude-standard
  fi
  echo '```'
  echo
  echo "## Stat"
  echo '```text'
  git diff --no-ext-diff --find-renames --stat "${diff_args[@]}"
  echo '```'
  echo
  echo "## Diff"
  echo '```diff'
  git diff --no-ext-diff --find-renames "${diff_args[@]}"
  if [[ "$mode" == "working" ]]; then
    while IFS= read -r -d '' untracked_path; do
      untracked_status=0
      git diff --no-ext-diff --no-index -- /dev/null "$untracked_path" ||
        untracked_status=$?
      if [[ "$untracked_status" -gt 1 ]]; then
        exit "$untracked_status"
      fi
    done < <(git ls-files --others --exclude-standard -z)
  fi
  echo '```'
} >"$temp_file"

mv "$temp_file" "$output"
trap - EXIT
echo "Paquete creado: $output"
