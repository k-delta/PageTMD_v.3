#!/usr/bin/env bash

set -euo pipefail

MODE="${1:---check}"
SSH_TARGET="${TMD_SSH_TARGET:-root@149.28.97.249}"
WP_CONTAINER="${TMD_WP_CONTAINER:-tmd_ols_wordpress}"
WP_PATH="${TMD_WP_PATH:-/var/www/vhosts/localhost/html}"
REMOTE_COMPOSE="${TMD_REMOTE_COMPOSE:-/opt/tecnimontacargas/docker-compose.prod.yml}"

case "$MODE" in
  --check|--pull)
    ;;
  *)
    echo "Uso: $0 [--check|--pull]" >&2
    exit 2
    ;;
esac

for command in ssh tar jq rsync diff git shasum; do
  command -v "$command" >/dev/null 2>&1 || {
    echo "Falta el comando requerido: $command" >&2
    exit 1
  }
done

REPO_ROOT="$(git rev-parse --show-toplevel)"
TEMP_ROOT="$(mktemp -d /tmp/tmd-production-sync.XXXXXX)"
trap 'rm -rf "$TEMP_ROOT"' EXIT

mkdir -p \
  "$TEMP_ROOT/wp-content/themes" \
  "$TEMP_ROOT/wp-content/plugins" \
  "$TEMP_ROOT/production-snapshot"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "docker exec '$WP_CONTAINER' tar -czf - \
    --exclude='*.bak' --exclude='*.bak-*' --exclude='*.before-*' \
    --exclude='._*' --exclude='.DS_Store' \
    -C '$WP_PATH/wp-content/themes' blocksy-child" |
  tar -xzf - -C "$TEMP_ROOT/wp-content/themes"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "docker exec '$WP_CONTAINER' tar -czf - \
    --exclude='*.bak' --exclude='*.bak-*' --exclude='*.before-*' \
    --exclude='._*' --exclude='.DS_Store' \
    -C '$WP_PATH/wp-content/plugins' \
    tm-chatbot-fase1 tm-equipos-destacados-v2 \
    tm-popup-bienvenida tm-quiz-equipo-ideal" |
  tar -xzf - -C "$TEMP_ROOT/wp-content/plugins"

WP="docker exec $WP_CONTAINER wp --allow-root --skip-plugins --skip-themes --path=$WP_PATH"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "$WP post list --post_type=page --post_status=publish,draft \
    --fields=ID,post_title,post_name,post_parent,post_status,post_modified,post_content,post_excerpt \
    --format=json" |
  jq 'sort_by(.ID | tonumber)' >"$TEMP_ROOT/production-snapshot/pages.json"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "$WP post list --post_type=post --post_status=publish,draft \
    --fields=ID,post_title,post_name,post_parent,post_status,post_modified,post_content,post_excerpt \
    --format=json" |
  jq 'sort_by(.ID | tonumber)' >"$TEMP_ROOT/production-snapshot/posts.json"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "$WP plugin list --fields=name,status,version,auto_update --format=json" |
  jq 'sort_by(.name)' >"$TEMP_ROOT/production-snapshot/plugins.json"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "$WP theme list --fields=name,status,version,auto_update --format=json" |
  jq 'sort_by(.name)' >"$TEMP_ROOT/production-snapshot/themes.json"

ssh -o BatchMode=yes "$SSH_TARGET" \
  "$WP eval 'global \$wpdb; \$table=\$wpdb->prefix . \"snippets\"; \$exists=\$wpdb->get_var(\$wpdb->prepare(\"SHOW TABLES LIKE %s\", \$wpdb->esc_like(\$table))); if (\"\" !== \$wpdb->last_error) { WP_CLI::error(\"No se pudo comprobar la tabla de snippets.\"); } if (\$exists !== \$table) { echo \"[]\"; return; } \$rows=\$wpdb->get_results(\"SELECT id,name,description,code,tags,scope,priority,active,modified FROM {\$wpdb->prefix}snippets ORDER BY id\", ARRAY_A); if (\"\" !== \$wpdb->last_error || ! is_array(\$rows)) { WP_CLI::error(\"No se pudo exportar la tabla de snippets.\"); } echo wp_json_encode(\$rows, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);'" |
  jq . >"$TEMP_ROOT/production-snapshot/snippets.json"

ssh -o BatchMode=yes "$SSH_TARGET" "cat '$REMOTE_COMPOSE'" \
  >"$TEMP_ROOT/docker-compose.prod.yml"

(
  cd "$TEMP_ROOT"
  find wp-content production-snapshot -type f ! -name SHA256SUMS -print0 |
    sort -z |
    xargs -0 shasum -a 256
  shasum -a 256 docker-compose.prod.yml
) >"$TEMP_ROOT/production-snapshot/SHA256SUMS"

sync_directory() {
  local source="$1"
  local destination="$2"
  mkdir -p "$destination"
  rsync -a --delete "$source/" "$destination/"
}

if [[ "$MODE" == "--pull" ]]; then
  protected_paths=(
    wp-content/themes/blocksy-child
    wp-content/plugins/tm-chatbot-fase1
    wp-content/plugins/tm-equipos-destacados-v2
    wp-content/plugins/tm-popup-bienvenida
    wp-content/plugins/tm-quiz-equipo-ideal
    production-snapshot
    docker-compose.prod.yml
  )

  if [[ -n "$(git -C "$REPO_ROOT" status --porcelain -- "${protected_paths[@]}")" ]]; then
    echo "Hay cambios locales en rutas productivas. Revísalos o guárdalos antes de usar --pull." >&2
    git -C "$REPO_ROOT" status --short -- "${protected_paths[@]}" >&2
    exit 1
  fi

  sync_directory \
    "$TEMP_ROOT/wp-content/themes/blocksy-child" \
    "$REPO_ROOT/wp-content/themes/blocksy-child"

  for plugin in \
    tm-chatbot-fase1 \
    tm-equipos-destacados-v2 \
    tm-popup-bienvenida \
    tm-quiz-equipo-ideal
  do
    sync_directory \
      "$TEMP_ROOT/wp-content/plugins/$plugin" \
      "$REPO_ROOT/wp-content/plugins/$plugin"
  done

  sync_directory \
    "$TEMP_ROOT/production-snapshot" \
    "$REPO_ROOT/production-snapshot"

  rsync -a "$TEMP_ROOT/docker-compose.prod.yml" "$REPO_ROOT/docker-compose.prod.yml"
  echo "Snapshot de producción actualizado. Revisa git diff antes de hacer commit."
  exit 0
fi

DRIFT=0

compare_directory() {
  local production="$1"
  local repository="$2"
  local label="$3"

  if ! diff -qr "$production" "$repository"; then
    echo "Diferencia detectada: $label" >&2
    DRIFT=1
  fi
}

compare_file() {
  local production="$1"
  local repository="$2"
  local label="$3"

  if ! diff -u "$repository" "$production"; then
    echo "Diferencia detectada: $label" >&2
    DRIFT=1
  fi
}

compare_directory \
  "$TEMP_ROOT/wp-content/themes/blocksy-child" \
  "$REPO_ROOT/wp-content/themes/blocksy-child" \
  "tema blocksy-child"

for plugin in \
  tm-chatbot-fase1 \
  tm-equipos-destacados-v2 \
  tm-popup-bienvenida \
  tm-quiz-equipo-ideal
do
  compare_directory \
    "$TEMP_ROOT/wp-content/plugins/$plugin" \
    "$REPO_ROOT/wp-content/plugins/$plugin" \
    "plugin $plugin"
done

compare_directory \
  "$TEMP_ROOT/production-snapshot" \
  "$REPO_ROOT/production-snapshot" \
  "contenido y manifiestos"

compare_file \
  "$TEMP_ROOT/docker-compose.prod.yml" \
  "$REPO_ROOT/docker-compose.prod.yml" \
  "docker-compose.prod.yml"

if ((DRIFT != 0)); then
  echo "Producción y repositorio no coinciden." >&2
  exit 1
fi

echo "Producción y repositorio coinciden en todas las rutas versionadas."
