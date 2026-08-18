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

Inicialmente el despliegue es manual mediante `workflow_dispatch` y el environment `production`.

Secrets requeridos:

- `DEPLOY_SSH_HOST`
- `DEPLOY_SSH_USER`
- `DEPLOY_SSH_KEY`
- `DEPLOY_KNOWN_HOSTS`

No guardar estos valores en archivos del repositorio.

## Requisitos del servidor

El host de producción necesita únicamente para el flujo de código:

- `git`
- `docker`
- `curl`

La validación PHP se ejecuta con el runtime del contenedor `tmd_ols_wordpress`; no requiere instalar PHP adicional en Alpine.

El proyecto Compose productivo se llama `pagetmd_v3`. El `docker-compose.prod.yml` fija este nombre para que los comandos operen sobre los contenedores existentes aunque se ejecuten desde `/opt/tecnimontacargas`.

## Preparación única del servidor

Antes del primer deploy con este modelo:

1. Crear un backup verificable del stack y del código propio actual.
2. Asegurar que `/opt/tecnimontacargas/app` sea un checkout limpio de este repositorio en `main`.
3. Mantener `/opt/tecnimontacargas/.env.prod` únicamente en el servidor.
4. Actualizar `/opt/tecnimontacargas/docker-compose.prod.yml` con la versión aprobada del repositorio.
5. Validar la configuración cargando explícitamente el archivo de entorno:

```bash
docker compose \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  config
```

6. Confirmar que Compose identifica el stack existente:

```bash
docker compose \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  ps
```

7. Recrear únicamente el servicio WordPress para aplicar los bind mounts:

```bash
docker compose \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  up -d --no-deps wordpress
```

8. Confirmar que los cinco componentes propios aparecen montados read-only y que el sitio responde correctamente.

Esta recreación es necesaria solo al adoptar o cambiar los mounts; los deploys de código posteriores no requieren recrear el contenedor.

## Ejecución del deploy

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

Para rollback explícito, ejecutar nuevamente el workflow seleccionando un commit estable anterior que pertenezca a `main` o invocar en el servidor:

```bash
/opt/tecnimontacargas/app/scripts/deploy-production.sh <sha-estable>
```

El script también revierte automáticamente al SHA previo si falla su health check posterior al checkout.

Un rollback de código no revierte MariaDB ni uploads. Si el cambio modificó datos persistentes, aplicar el procedimiento de `BACKUP_RESTORE.md`.

## Seguridad de secretos

Los archivos `.env*` reales no se versionan. Solo `.env.example` puede permanecer en Git.

Si una credencial fue publicada alguna vez en Git, eliminar el archivo del árbol actual no es suficiente: la credencial debe rotarse y, cuando corresponda, el historial debe sanearse mediante un procedimiento coordinado.
