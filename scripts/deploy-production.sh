#!/usr/bin/env bash
set -euo pipefail

TARGET_SHA="${1:-}"
APP_DIR="${TMD_APP_DIR:-/opt/tecnimontacargas/app}"
SITE_URL="${TMD_SITE_URL:-https://tecnimontacargas.com}"

if [[ -z "$TARGET_SHA" ]]; then
  echo "Uso: $0 <commit-sha>" >&2
  exit 2
fi

for command in git php curl; do
  command -v "$command" >/dev/null 2>&1 || {
    echo "Falta el comando requerido: $command" >&2
    exit 1
  }
done

cd "$APP_DIR"

if [[ -n "$(git status --porcelain)" ]]; then
  echo "El checkout productivo tiene cambios locales. Se cancela el despliegue." >&2
  git status --short >&2
  exit 1
fi

PREVIOUS_SHA="$(git rev-parse HEAD)"
TEMP_WORKTREE="$(mktemp -d /tmp/tmd-deploy.XXXXXX)"
trap 'git -C "$APP_DIR" worktree remove --force "$TEMP_WORKTREE" >/dev/null 2>&1 || true; rm -rf "$TEMP_WORKTREE"' EXIT

echo "Actualizando referencias de Git..."
git fetch --prune origin

git cat-file -e "${TARGET_SHA}^{commit}" 2>/dev/null || {
  echo "El commit $TARGET_SHA no existe en el repositorio local después de git fetch." >&2
  exit 1
}

echo "Validando PHP antes de publicar..."
git worktree add --detach "$TEMP_WORKTREE" "$TARGET_SHA" >/dev/null
while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
done < <(find \
  "$TEMP_WORKTREE/wp-content/themes/blocksy-child" \
  "$TEMP_WORKTREE/wp-content/plugins/tm-chatbot-fase1" \
  "$TEMP_WORKTREE/wp-content/plugins/tm-equipos-destacados-v2" \
  "$TEMP_WORKTREE/wp-content/plugins/tm-popup-bienvenida" \
  "$TEMP_WORKTREE/wp-content/plugins/tm-quiz-equipo-ideal" \
  -type f -name '*.php' -print0)

git worktree remove --force "$TEMP_WORKTREE" >/dev/null
rm -rf "$TEMP_WORKTREE"
TEMP_WORKTREE=""

echo "Publicando $TARGET_SHA..."
git checkout --detach "$TARGET_SHA"

health_check() {
  curl --fail --silent --show-error --location \
    --max-time 20 \
    --output /dev/null \
    "$SITE_URL/"
}

if ! health_check; then
  echo "Health check falló. Restaurando $PREVIOUS_SHA..." >&2
  git checkout --detach "$PREVIOUS_SHA"
  health_check || true
  exit 1
fi

echo "Despliegue correcto: $(git rev-parse HEAD)"
