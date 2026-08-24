# SPEC: Alineación automática de main con producción

## Estado

- Aprobado

## Contexto

[Solicitud] Cada push a la rama `main` debe desplegarse al servidor productivo y la documentación debe describir ese flujo real.

[Evidencia inicial: .github/workflows/deploy-production.yml:3] Al redactar este SPEC, el workflow solo declaraba `workflow_dispatch`; por tanto, un push a `main` no iniciaba el despliegue.

[Evidencia inicial: docs/runbooks/DEPLOYMENT.md:20] Al redactar este SPEC, el runbook representaba el flujo `main -> GitHub Actions -> SSH -> deploy-production.sh`, pero [Evidencia inicial: docs/runbooks/DEPLOYMENT.md:29] todavía afirmaba que su ejecución era manual.

[Evidencia productiva: inspección SSH de solo lectura 2026-08-24] El checkout `/opt/tecnimontacargas/app` estaba limpio en `3139ec06aa8d9025b72ebfd1e01d430e798dc67a`, mientras el `main` local y `origin/main` verificados estaban en `c8d0b6aa17a667197ccdd4cd01737699442a3f57`; la diferencia confirma que el flujo vigente no mantuvo automáticamente el checkout productivo en el último SHA de `main`.

## Problema

El repositorio ya contiene un mecanismo de despliegue por SHA, pero el workflow requiere activación manual. Esto permite que `main` y el checkout productivo queden en commits distintos y hace que la documentación describa un proceso que no coincide con el resultado esperado.

## Objetivo

Conseguir que cada push aceptado en `main` inicie el workflow productivo para desplegar el SHA de ese push mediante el mecanismo existente, manteniendo las protecciones, verificaciones y rollback actuales, y documentar de forma precisa tanto el flujo automático como la vía manual de respaldo.

## Fuera del alcance

- Hacer commit, push, merge o desplegar a producción sin una autorización posterior y explícita para cada acción externa.
- Eliminar o debilitar protecciones del environment `production`, controles SSH, secrets, validación PHP, health check, rollback o control de concurrencia.
- Modificar WordPress, MariaDB, uploads, Docker Compose, contenedores, volúmenes, DNS, TLS o puertos.
- Convertir producción en fuente canónica o desplegar cambios productivos de regreso hacia Git.
- Incorporar una plataforma de CI/CD distinta de GitHub Actions.
- Resolver la limpieza de cachés, temporales o backups del servidor, que pertenece a un track independiente.

## Requisitos funcionales

1. [Solicitud] Todo evento `push` cuyo destino sea la rama `main` debe iniciar automáticamente el workflow de despliegue productivo.
2. [Solicitud] El despliegue automático debe enviar al servidor el SHA exacto asociado al evento que inició el workflow.
3. [Evidencia: .github/workflows/deploy-production.yml:16] El job automático debe seguir asociado al environment `production` para conservar sus secrets y reglas de protección configuradas.
4. [Evidencia: .github/workflows/deploy-production.yml:9] El workflow debe conservar una única ejecución activa para producción y no cancelar un despliegue que ya esté en curso cuando llegue un push posterior.
5. [Evidencia: scripts/deploy-production.sh:23] Cada ejecución debe continuar rechazando un checkout productivo con cambios locales.
6. [Evidencia: scripts/deploy-production.sh:50] Cada ejecución debe continuar comprobando que el SHA objetivo pertenece al historial de `origin/main` antes de publicarlo.
7. [Evidencia: scripts/deploy-production.sh:60] Cada ejecución debe conservar la validación PHP previa, el checkout por SHA, el health check HTTP y el rollback automático existentes.
8. [Solicitud] El disparo manual debe conservarse como vía operativa de respaldo sin impedir el disparo automático de los pushes a `main`.
9. [Solicitud] El runbook de despliegue debe explicar el trigger automático, el SHA desplegado, la concurrencia, las protecciones del environment, la verificación posterior y el uso de la vía manual.
10. [Regla: AGENTS.md] La documentación no debe incluir valores de secrets, claves privadas, credenciales ni datos sensibles del servidor.

## Reglas de negocio

- [Regla: AGENTS.md] Git sigue siendo la fuente canónica del código propio y no se deben reconciliar divergencias copiando silenciosamente desde producción.
- [Regla: docs/runbooks/DEPLOYMENT.md:9] El despliegue automático cubre el child theme y los plugins propios versionados; MariaDB, uploads, WordPress core, el tema padre y plugins de terceros conservan sus fuentes canónicas actuales.
- [Regla: AGENTS.md] Una aprobación del SPEC autoriza implementación local, pero no autoriza commit, push, merge ni despliegue productivo.
- [Regla: AGENTS.md] Si una comprobación productiva detecta deriva o cambios locales, la operación debe detenerse y compararse antes de publicar.

## Contratos

### Entrada

```json
{
  "event": "push|workflow_dispatch",
  "ref": "refs/heads/main|<manual-reference>",
  "targetSha": "<git-commit-sha>",
  "constraint": "push requiere refs/heads/main; workflow_dispatch requiere que targetSha pertenezca al historial de origin/main"
}
```

### Salida

```json
{
  "workflow": "Deploy production",
  "environment": "production",
  "targetSha": "<git-commit-sha>",
  "productionSha": "<git-commit-sha>",
  "healthCheck": "passed|failed",
  "result": "success|failure|cancelled"
}
```

## Casos límite

- Dos o más pushes llegan a `main` mientras existe un despliegue activo; el activo no se cancela y producción debe terminar convergiendo al SHA pendiente más reciente conservado por la concurrencia.
- Un push modifica únicamente documentación o archivos no montados en WordPress; el checkout productivo aun así debe converger al SHA de `main` para mantener alineado el repositorio completo.
- El environment `production` tiene reglas de aprobación; el workflow se inicia automáticamente, pero el job debe respetar la espera exigida por GitHub.
- El checkout del servidor contiene cambios locales; el script cancela el despliegue y no los sobrescribe.
- El SHA del evento ya no pertenece a `origin/main`; el script rechaza la publicación.
- La validación PHP falla; el checkout productivo no debe cambiar.
- El health check falla después del checkout; el script debe restaurar el SHA productivo anterior.
- Los secrets o la conectividad SSH no están disponibles; el workflow debe fallar sin imprimir secretos y sin modificar el checkout.
- Una ejecución manual y una automática coinciden; ambas deben compartir el mismo grupo de concurrencia productiva.

## Archivos o módulos relacionados

- `.github/workflows/deploy-production.yml`
- `scripts/deploy-production.sh`
- `docs/runbooks/DEPLOYMENT.md`
- `docs/runbooks/PRODUCTION.md`
- `docs/runbooks/BACKUP_RESTORE.md`
- `/opt/tecnimontacargas/app` en producción, solo para validación autorizada.

## Criterios de aceptación

1. [Solicitud] La configuración validada del workflow contiene un trigger `push` restringido a `main` y conserva `workflow_dispatch`.
2. [Solicitud] Un push autorizado a `main` crea una ejecución de **Deploy production** cuyo SHA objetivo coincide con el SHA del evento.
3. [Evidencia: .github/workflows/deploy-production.yml:16] La ejecución utiliza el environment `production` y no expone los cuatro secrets de despliegue en archivos o logs.
4. [Evidencia: .github/workflows/deploy-production.yml:9] Dos triggers concurrentes no ejecutan simultáneamente dos despliegues productivos ni cancelan el que ya estaba activo.
5. [Evidencia: scripts/deploy-production.sh:50] El servidor rechaza un SHA ajeno al historial de `origin/main` y preserva el checkout anterior.
6. [Evidencia: scripts/deploy-production.sh:60] Un fallo de sintaxis PHP impide publicar el SHA y un fallo del health check restaura el SHA anterior.
7. [Solicitud] Tras una ejecución automática exitosa, `git status --short` queda vacío, `HEAD` productivo coincide con el SHA esperado de `main` y `https://tecnimontacargas.com/` responde `HTTP 200`.
8. [Solicitud] `docs/runbooks/DEPLOYMENT.md` deja de presentar el despliegue normal como exclusivamente manual y distingue el trigger automático de la ejecución manual de respaldo.
9. [Regla: AGENTS.md] El diff final se limita al workflow, su documentación operativa y este SPEC, sin cambios en datos, infraestructura o código funcional de WordPress.

## Validación

- Pruebas unitarias: No aplica; no cambia lógica PHP, JavaScript ni CSS del producto.
- Pruebas de integración: validar la sintaxis del workflow y comprobar que los eventos `push` a `main` y `workflow_dispatch` producen el contexto esperado, conservan `github.sha`, el environment y el grupo de concurrencia.
- Validación manual: revisar el diff completo y confirmar que no se alteran comandos SSH, nombres de secrets, permisos, controles del script ni archivos fuera del alcance.
- Validación productiva: únicamente con autorización posterior para commit, push y deploy; observar la ejecución automática en GitHub Actions, confirmar su resultado, verificar por SSH el checkout limpio y el SHA exacto, comprobar `HTTP 200`, revisar errores nuevos y ejecutar `./scripts/sync-production.sh --check`.

## Riesgos

- Un push defectuoso a `main` puede iniciar inmediatamente un despliegue; las protecciones de rama y del environment pasan a ser controles críticos.
- El workflow de calidad versionado está vacío, por lo que este cambio por sí solo no establece una compuerta CI previa al despliegue.
- Con la concurrencia vigente, pushes rápidos pueden reemplazar una ejecución pendiente anterior; esto omite SHAs intermedios, aunque el SHA más reciente contiene su historial acumulado.
- Una aprobación requerida por el environment puede hacer que el trigger sea automático pero el despliegue no sea desatendido.
- Cambiar el propio script de despliegue en un commit futuro requiere considerar que la invocación inicial usa la versión ya presente en el checkout productivo antes de hacer `git fetch`.
- Un fallo de GitHub Actions, SSH, GitHub SSH sobre puerto 443 o del health check puede dejar producción temporalmente detrás de `main`, aunque sin sobrescribir cambios locales.

## Decisiones pendientes

- No aplica. La solicitud define el evento (`push`), la rama (`main`), el destino (producción) y el resultado esperado; el trigger manual se conserva como respaldo compatible con el flujo vigente.
