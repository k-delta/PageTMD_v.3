# Despliegue

## Propósito

Define el procedimiento seguro y rápido para desplegar código propio en producción manteniendo Git como fuente canónica.

## Modelo canónico

- GitHub gobierna el código propio: `blocksy-child` y los plugins `tm-*` versionados.
- MariaDB gobierna páginas, posts, opciones y demás contenido persistente de WordPress.
- `wp-content/uploads` gobierna multimedia y no se despliega desde Git.
- WordPress core, tema padre y plugins de terceros no se despliegan desde este repositorio.
- `production-snapshot/` es auditoría; no es una fuente primaria ni un requisito para cada deploy.

## Flujo normal

El flujo normal es unidireccional:

```text
branch/PR -> main -> GitHub Actions -> SSH -> deploy-production.sh -> checkout del SHA
```

Los componentes propios se montan read-only desde `/opt/tecnimontacargas/app` dentro del contenedor WordPress. Por eso un cambio de commit se refleja sin copiar archivos al contenedor ni reiniciarlo.

## GitHub Actions

Workflow: `.github/workflows/deploy-production.yml`.

Cada push a `main` inicia automáticamente el despliegue del SHA asociado al evento. El workflow conserva `workflow_dispatch` como vía manual de respaldo y ambos disparos usan el environment `production`.

Las reglas de protección configuradas en el environment siguen aplicando. Si exige aprobación, el workflow se inicia con el push, pero el job espera esa autorización antes de acceder a los secrets y desplegar.

La concurrencia `production-deploy` permite una sola ejecución productiva activa y no cancela la que ya está en curso. Si llegan varios pushes mientras está activa, GitHub conserva el pendiente más reciente; al contener este el historial acumulado de `main`, producción converge al último SHA pendiente en lugar de ejecutar simultáneamente estados intermedios.

Secrets requeridos:

- `DEPLOY_SSH_HOST`
- `DEPLOY_SSH_USER`
- `DEPLOY_SSH_KEY`
- `DEPLOY_KNOWN_HOSTS`

No guardar estos valores en archivos del repositorio.

## Modelo SSH validado

El despliegue usa dos relaciones SSH independientes. No reutilizar la misma clave para ambos sentidos.

### Producción -> GitHub

El servidor de producción necesita acceso de lectura al repositorio para ejecutar `git fetch`. Usar una Deploy key exclusiva del repositorio y mantener **Allow write access** desactivado.

El transporte validado usa GitHub SSH sobre el puerto 443:

```text
ssh://git@ssh.github.com:443/k-delta/PageTMD_v.3.git
```

Antes de confiar en `ssh.github.com`, obtener su host key y comparar el fingerprint con el publicado actualmente por GitHub. No aceptar un fingerprint sin verificarlo.

Ejemplo:

```bash
TMP_HOSTKEY="$(mktemp)"
ssh-keyscan -t ed25519 -p 443 ssh.github.com > "$TMP_HOSTKEY" 2>/dev/null
ssh-keygen -lf "$TMP_HOSTKEY"
```

Después de verificar el fingerprint, registrar la host key en el `known_hosts` del usuario que ejecuta el deploy.

La instalación validada usa una clave dedicada en el servidor y fija el remote y el comando SSH en el checkout:

```bash
cd /opt/tecnimontacargas/app

git remote set-url origin \
  ssh://git@ssh.github.com:443/k-delta/PageTMD_v.3.git

git config --local core.sshCommand \
  'ssh -i /root/.ssh/pagetmd_github -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes'
```

Comprobar siempre:

```bash
git ls-remote origin HEAD
git fetch --prune origin
```

### GitHub Actions -> producción

GitHub Actions usa una segunda clave SSH exclusiva para entrar al host de producción. La clave pública se instala en `authorized_keys` del usuario de deploy y la privada completa se guarda únicamente en `DEPLOY_SSH_KEY` dentro del environment `production`.

`DEPLOY_KNOWN_HOSTS` debe contener la línea completa de `known_hosts` del host productivo, no solo el fingerprint. Obtenerla desde una máquina confiable y comparar primero el fingerprint con `/etc/ssh/ssh_host_ed25519_key.pub` del servidor.

Ejemplo de validación desde la máquina administradora:

```bash
ssh-keyscan -t ed25519 <host-produccion> > /tmp/pagetmd_known_hosts
ssh-keygen -lf /tmp/pagetmd_known_hosts

ssh \
  -i ~/.ssh/pagetmd_github_actions \
  -o IdentitiesOnly=yes \
  -o BatchMode=yes \
  -o StrictHostKeyChecking=yes \
  -o UserKnownHostsFile=/tmp/pagetmd_known_hosts \
  <usuario-deploy>@<host-produccion> \
  'echo github-actions-ssh=OK'
```

La salida esperada es `github-actions-ssh=OK`.

## Requisitos del servidor

El host de producción necesita únicamente para el flujo de código:

- `git`
- `docker`
- `curl`

La validación PHP se ejecuta con el runtime del contenedor `tmd_ols_wordpress`; no requiere instalar PHP adicional en Alpine.

En Alpine, si falta Git:

```bash
apk add --no-cache git
```

El proyecto Compose productivo se llama `pagetmd_v3`. El `docker-compose.prod.yml` fija este nombre para que los comandos operen sobre los contenedores existentes aunque se ejecuten desde `/opt/tecnimontacargas`.

## Preparación única del servidor

### 1. Preparar acceso Git de solo lectura

Crear y registrar la Deploy key read-only, verificar la host key de GitHub y clonar `main` directamente sobre SSH 443:

```bash
GIT_SSH_COMMAND='ssh -i /root/.ssh/pagetmd_github -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes' \
  git clone \
    --branch main \
    --single-branch \
    ssh://git@ssh.github.com:443/k-delta/PageTMD_v.3.git \
    /opt/tecnimontacargas/app
```

Después configurar `core.sshCommand` de forma local al repositorio como se indica arriba.

Confirmar:

```bash
cd /opt/tecnimontacargas/app
git status --short
git branch --show-current
git rev-parse HEAD
git rev-parse origin/main
```

El checkout debe estar limpio y `HEAD` debe coincidir con `origin/main` antes del bootstrap.

### 2. Comparar Git contra el código vivo

Antes de introducir bind mounts, preservar una copia temporal de los cinco componentes canónicos que actualmente ejecuta WordPress y compararla con el checkout.

```bash
rm -rf /tmp/tmd-current
mkdir -p /tmp/tmd-current/themes /tmp/tmd-current/plugins

docker cp \
  tmd_ols_wordpress:/var/www/vhosts/localhost/html/wp-content/themes/blocksy-child \
  /tmp/tmd-current/themes/

for plugin in \
  tm-chatbot-fase1 \
  tm-equipos-destacados-v2 \
  tm-popup-bienvenida \
  tm-quiz-equipo-ideal
do
  docker cp \
    "tmd_ols_wordpress:/var/www/vhosts/localhost/html/wp-content/plugins/$plugin" \
    /tmp/tmd-current/plugins/
done
```

Comparar:

```bash
diff -qr \
  /tmp/tmd-current/themes/blocksy-child \
  /opt/tecnimontacargas/app/wp-content/themes/blocksy-child || true

for plugin in \
  tm-chatbot-fase1 \
  tm-equipos-destacados-v2 \
  tm-popup-bienvenida \
  tm-quiz-equipo-ideal
do
  diff -qr \
    "/tmp/tmd-current/plugins/$plugin" \
    "/opt/tecnimontacargas/app/wp-content/plugins/$plugin" || true
done
```

Detener el bootstrap ante cualquier diferencia de contenido canónico. Archivos auxiliares como AppleDouble (`._*`), `.DS_Store` o backups manuales `*.bak*` no deben convertirse en fuente canónica; preservarlos en el backup y revisarlos por separado.

### 3. Crear y validar backup

Aplicar `BACKUP_RESTORE.md` antes de cambiar Compose o recrear WordPress. El procedimiento validado para este bootstrap incluye:

- dump completo de MariaDB;
- copia de los cinco componentes propios;
- copia del Compose productivo anterior;
- copia local restringida de `.env.prod`;
- tamaño, existencia y hashes de los artefactos.

No continuar si el dump está vacío o si no existe una ruta de rollback identificada.

### 4. Instalar el Compose aprobado

Mantener `/opt/tecnimontacargas/.env.prod` únicamente en el servidor y copiar la versión aprobada del repositorio:

```bash
cp \
  /opt/tecnimontacargas/app/docker-compose.prod.yml \
  /opt/tecnimontacargas/docker-compose.prod.yml
```

Validar sin imprimir la configuración interpolada:

```bash
docker compose \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  config -q
```

Confirmar que Compose identifica el stack existente:

```bash
docker compose \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  ps
```

Deben aparecer `tmd_db`, `tmd_ols_wordpress` y `tmd_phpmyadmin`.

### 5. Activar los bind mounts

Recrear únicamente WordPress:

```bash
docker compose \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  up -d --no-deps wordpress
```

MariaDB y phpMyAdmin no deben recrearse en este paso.

Verificar los mounts:

```bash
docker inspect tmd_ols_wordpress \
  --format '{{range .Mounts}}{{println .Type .Source "->" .Destination "RW=" .RW}}{{end}}'
```

Los siguientes cinco componentes deben aparecer como bind mounts con `RW= false`:

- `wp-content/themes/blocksy-child`
- `wp-content/plugins/tm-chatbot-fase1`
- `wp-content/plugins/tm-equipos-destacados-v2`
- `wp-content/plugins/tm-popup-bienvenida`
- `wp-content/plugins/tm-quiz-equipo-ideal`

Verificar salud HTTP:

```bash
curl -fsS \
  -o /dev/null \
  -w 'HTTP %{http_code}\n' \
  https://tecnimontacargas.com/
```

La respuesta esperada es `HTTP 200`.

### 6. Probar el deploy con el SHA actual

Antes de depender de Actions, ejecutar el mecanismo con el mismo SHA ya publicado. Esta prueba valida fetch, pertenencia a `main`, lint PHP, checkout y health check sin introducir un cambio funcional:

```bash
cd /opt/tecnimontacargas/app
TARGET="$(git rev-parse origin/main)"
./scripts/deploy-production.sh "$TARGET"
```

Después:

```bash
git status --short
git rev-parse HEAD
git rev-parse origin/main
curl -fsS -o /dev/null -w 'HTTP %{http_code}\n' https://tecnimontacargas.com/
```

`git status --short` debe quedar vacío, los SHA deben coincidir y el sitio debe responder `HTTP 200`.

El script publica mediante `git checkout --detach`; por ello `git branch --show-current` puede quedar vacío después de un deploy correcto.

### 7. Validar GitHub Actions

Antes de habilitar el trigger automático, crear los cuatro secrets del environment `production`, usando una clave independiente de la Deploy key del repositorio, y ejecutar manualmente **Deploy production** sobre `main`.

El primer workflow debe terminar en `Success`. Después de la ejecución, confirmar nuevamente que `HEAD` coincide con `origin/main` y que el sitio responde `HTTP 200`.

Con la preparación validada, cada push posterior a `main` inicia **Deploy production** automáticamente. La vía `workflow_dispatch` permanece disponible para volver a ejecutar de forma controlada el SHA de `main` o de otra referencia cuyo commit pertenezca a su historial.

Esta recreación del contenedor es necesaria solo al adoptar o cambiar los mounts; los deploys de código posteriores no requieren recrear WordPress.

## Ejecución del deploy

El trigger `push` está restringido a `main`. Para ese evento, `${{ github.sha }}` identifica el commit que originó la ejecución y se entrega al servidor como SHA objetivo. Los pushes a otras ramas no inician este workflow.

Un cambio que solo afecte documentación u otros archivos no montados en WordPress también actualiza el checkout productivo. Esto mantiene `/opt/tecnimontacargas/app` alineado con el estado completo de `main`, aunque no cambie el contenido visible del sitio.

El workflow invoca:

```bash
/opt/tecnimontacargas/app/scripts/deploy-production.sh <commit-sha>
```

El script:

1. Rechaza el deploy si el checkout productivo tiene modificaciones locales.
2. Ejecuta `git fetch --prune origin`.
3. Verifica que el SHA exista y pertenezca al historial de `origin/main`.
4. Crea un worktree temporal del SHA objetivo.
5. Ejecuta `php -l` dentro de `tmd_ols_wordpress` sobre todo el PHP de los componentes propios antes de publicarlo.
6. Cambia el checkout productivo al SHA objetivo mediante `git checkout --detach`.
7. Ejecuta un health check HTTP sobre el dominio canónico.
8. Si falla el health check, restaura automáticamente el SHA anterior.

## Troubleshooting validado

### `docker compose ps` no muestra contenedores

Durante una migración desde un Compose antiguo que todavía no contiene `name: pagetmd_v3`, Compose puede inferir otro nombre de proyecto a partir del directorio actual y no mostrar los contenedores existentes.

Hasta instalar el Compose nuevo, consultar el stack explícitamente:

```bash
docker compose \
  -p pagetmd_v3 \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  ps
```

### Git HTTPS falla con `TLS alert handshake failure`

Si `git` falla antes de autenticarse y `GIT_CURL_VERBOSE=1` muestra que intenta una ruta IPv6 que termina en `TLS alert, handshake failure`, mientras `curl -4` u OpenSSL por IPv4 validan correctamente GitHub, no desactivar `http.sslVerify`.

Diagnóstico útil:

```bash
GIT_CURL_VERBOSE=1 \
  git -c http.version=HTTP/1.1 \
  ls-remote https://github.com/k-delta/PageTMD_v.3.git HEAD

curl -4Iv --tlsv1.2 --tls-max 1.2 https://github.com/
```

La solución permanente validada para este host es usar el remote SSH por `ssh.github.com:443`. No fijar una IP de GitHub de forma permanente porque puede cambiar.

### `Host key verification failed`

Comprobar que `DEPLOY_KNOWN_HOSTS` contiene la línea completa generada por `ssh-keyscan` después de verificar su fingerprint. El fingerprint por sí solo no es un archivo `known_hosts` válido.

### `Load key ... error in libcrypto`

El secreto `DEPLOY_SSH_KEY` debe contener la clave privada OpenSSH completa, preservando saltos de línea, incluida la cabecera y el pie:

```text
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

Validar localmente sin imprimir la clave:

```bash
ssh-keygen -y -f ~/.ssh/pagetmd_github_actions >/dev/null \
  && echo 'private-key=OK'
```

No usar la clave pública `.pub` como `DEPLOY_SSH_KEY`.

## Deriva

No editar PHP, CSS o JavaScript propio directamente en producción durante la operación normal.

Si `git status --porcelain` devuelve cambios en `/opt/tecnimontacargas/app`, el deploy debe detenerse. Determinar primero por qué producción fue modificada fuera de Git.

`scripts/sync-production.sh` se conserva para auditoría, comparación y recuperación excepcional de cambios autorizados realizados en producción. No es el mecanismo principal de despliegue y no debe convertir producción en fuente canónica del código.

## Hotfix excepcional

Si una emergencia obliga a modificar código directamente en producción:

1. Crear backup del componente afectado.
2. Aplicar únicamente el cambio mínimo autorizado.
3. Verificar el sitio.
4. Recuperar el cambio hacia una rama usando el mecanismo de sincronización correspondiente.
5. Revisar y hacer commit.
6. Volver a desplegar desde Git para restaurar la fuente canónica.

No dejar cambios manuales sin reconciliar.

## No desplegar

- WordPress core.
- Tema padre.
- Plugins de terceros.
- Uploads.
- `.env`, `wp-config.php`, certificados o secretos.
- Backups, logs, cachés o temporales.
- Código histórico como `tmd-site-kit/`.
- Cambios no relacionados con la tarea.

## Validaciones

Antes de mergear, ejecutar las validaciones focalizadas correspondientes. Para PHP, usar un runtime compatible con producción.

Después del deploy:

- Verificar HTTP.
- Probar el flujo modificado.
- Revisar consola y logs cuando aplique.
- Confirmar que el checkout productivo quedó en el SHA esperado.

## Rollback

Para volver a ejecutar el SHA actual de `main` o de una referencia cuyo commit pertenezca a su historial puede usarse `workflow_dispatch`. Para un rollback explícito a un commit estable anterior que pertenezca a `main`, invocar en el servidor:

```bash
/opt/tecnimontacargas/app/scripts/deploy-production.sh <sha-estable>
```

El script también revierte automáticamente al SHA previo si falla su health check posterior al checkout.

Para revertir específicamente el bootstrap de bind mounts, restaurar el Compose guardado en el backup y recrear únicamente WordPress. Como el Compose anterior puede no contener `name: pagetmd_v3`, fijar el project name explícitamente:

```bash
BACKUP=/opt/tecnimontacargas/backups/<backup-validado>

cp \
  "$BACKUP/docker-compose.prod.yml" \
  /opt/tecnimontacargas/docker-compose.prod.yml

docker compose \
  -p pagetmd_v3 \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  up -d --no-deps --force-recreate wordpress
```

Un rollback de código no revierte MariaDB ni uploads. Si el cambio modificó datos persistentes, aplicar el procedimiento de `BACKUP_RESTORE.md`.

## Seguridad de secretos

Los archivos `.env*` reales no se versionan. Solo `.env.example` puede permanecer en Git.

Si una credencial fue publicada alguna vez en Git, eliminar el archivo del árbol actual no es suficiente: la credencial debe rotarse y, cuando corresponda, el historial debe sanearse mediante un procedimiento coordinado.

Mantener separadas las claves VPS -> GitHub y GitHub Actions -> VPS. La clave del repositorio debe ser read-only. El acceso SSH de Actions debe limitarse al usuario y permisos estrictamente necesarios; si el bootstrap inicial usa `root`, migrar posteriormente a un usuario de deploy dedicado como medida de endurecimiento.
