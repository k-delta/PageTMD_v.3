# SPEC: Depuración de datos temporales y backups obsoletos del servidor

## Estado

- Aprobado

## Contexto

[Solicitud] Se requiere eliminar del servidor productivo los datos temporales innecesarios de la página, incluyendo backups que ya no deban conservarse.

[Evidencia: inspección SSH productiva de solo lectura 2026-08-24] El sistema de archivos principal tenía 39 GB libres y 22% de uso; la operación es de higiene y reducción de residuos, no una respuesta a falta crítica de espacio.

[Evidencia: inspección SSH productiva de solo lectura 2026-08-24] Docker reportó tres imágenes activas, tres contenedores activos, cuatro volúmenes activos y `0 B` reclamables; `docker prune`, la eliminación de imágenes y la eliminación de volúmenes no aportarían espacio seguro.

[Evidencia: inspección SSH productiva de solo lectura 2026-08-24] El contenedor WordPress acumulaba aproximadamente 503 MB en `/usr/local/lsws/cachedata`, 3 MB en `wp-content/litespeed` y 2 MB en su `/tmp`; los dos primeros corresponden a datos regenerables de caché u optimización, mientras el tercero contiene estado de runtime y no puede eliminarse por patrón global.

[Evidencia: inspección SSH productiva de solo lectura 2026-08-24] El `/tmp` del host ocupaba aproximadamente 472 KB y contenía scripts PHP, copias CSS/HTML/XML e imágenes usados en operaciones anteriores; también contenía sockets y archivos de runtime que deben excluirse.

[Evidencia: inspección SSH productiva de solo lectura 2026-08-24] `/opt/tecnimontacargas/backups` contenía `media-cleanup-20260811-202124` con aproximadamente 31 MB y `pre-git-deploy-20260818-024551` con aproximadamente 17 MB. El segundo incluye una copia restringida de `.env.prod`, por lo que cualquier inventario o eliminación debe evitar imprimir su contenido.

No se eliminó, purgó, movió ni modificó información productiva durante la inspección y redacción de este SPEC.

## Problema

El servidor conserva cachés regenerables, artefactos operativos temporales y backups históricos que consumen espacio o retienen información sensible más tiempo del necesario. Una eliminación global o basada únicamente en nombres puede borrar sockets activos, estado de seguridad, contenido persistente o el último punto de recuperación utilizable.

## Objetivo

Generar y aprobar un manifiesto exacto, conservar un punto de recuperación vigente y verificado, y retirar exclusivamente cachés regenerables, archivos temporales antiguos y backups obsoletos, sin afectar el sitio, sus datos persistentes, servicios activos ni capacidad mínima de recuperación.

## Fuera del alcance

- Eliminar o modificar MariaDB, uploads, adjuntos, páginas, entradas, opciones, usuarios o cualquier dato persistente de WordPress.
- Eliminar volúmenes, imágenes o contenedores Docker; ejecutar `docker system prune`, `docker volume prune` o equivalentes.
- Eliminar logs activos de OpenLiteSpeed, Wordfence, MariaDB, auditoría, seguridad, TLS o sistema sin una política de retención específica.
- Eliminar sockets, locks activos, PID files, swap de OpenLiteSpeed u otros archivos de runtime.
- Modificar WordPress, plugins, temas, dependencias, Docker Compose, DNS, TLS, puertos o servicios.
- Eliminar el checkout Git, `.git`, scripts versionados, `production-snapshot` o fuentes canónicas.
- Copiar backups con datos reales o secretos al repositorio, tickets, logs o conversaciones.
- Ejecutar commit, push, despliegue de código o el track pendiente de automatización.

## Requisitos funcionales

1. [Solicitud] Antes de borrar debe generarse un manifiesto productivo de solo lectura con cada ruta candidata, tipo, fecha, tamaño, categoría y motivo de elegibilidad.
2. [Regla: AGENTS.md] El manifiesto no debe mostrar contenidos de `.env.prod`, SQL, claves, credenciales, datos personales ni otros secretos; para backups solo debe registrar metadatos, estructura segura y comprobaciones de integridad.
3. [Solicitud] El manifiesto debe separar caché LiteSpeed, assets optimizados regenerables, temporales del host y backups históricos para que cada conjunto pueda reconciliarse después de la operación.
4. [Regla: docs/runbooks/BACKUP_RESTORE.md:168] Antes de cualquier eliminación debe existir al menos un punto de recuperación vigente, restringido, no vacío, con integridad comprobada y ruta de restauración identificada.
5. [Solicitud] Los backups `media-cleanup-20260811-202124` y `pre-git-deploy-20260818-024551` solo serán elegibles cuando el punto de recuperación vigente cubra base de datos, configuración y los componentes necesarios para restaurar el estado actual; el backup retenido queda excluido del manifiesto de borrado.
6. [Solicitud] La caché de página y los assets optimizados deben purgarse mediante mecanismos soportados por WordPress/LiteSpeed o mediante rutas exactas previamente verificadas como regenerables; no se deben usar patrones recursivos amplios fuera de esas rutas.
7. [Solicitud] En `/tmp` del host solo deben eliminarse archivos regulares identificados en el manifiesto como artefactos cerrados de operaciones anteriores; `.ICE-unix`, `.X11-unix`, sockets, locks vigentes y archivos abiertos quedan excluidos.
8. [Solicitud] `/tmp/lshttpd`, los volúmenes productivos, los logs activos y `wp-content/wflogs` deben conservarse.
9. [Solicitud] La ejecución debe detenerse si el manifiesto cambia, aparece una ruta no clasificada, falta el punto de recuperación, un archivo candidato está abierto o alguna comprobación previa de salud falla.
10. [Solicitud] La operación debe producir un reporte con rutas eliminadas, omitidas y fallidas, motivo por elemento, espacio antes/después y hash del manifiesto aprobado.
11. [Regla: AGENTS.md] Después de la limpieza deben verificarse estado de contenedores, HTTP, navegador, logs recientes, regeneración de caché y sincronización repositorio-producción.

## Reglas de negocio

- [Regla: AGENTS.md] Inventario/Firebase, MariaDB y uploads conservan sus responsabilidades canónicas y no pueden tratarse como datos temporales.
- [Regla: docs/runbooks/PRODUCTION.md:117] La consulta de uso de Docker no autoriza una limpieza automática; los recursos activos quedan preservados.
- [Regla: docs/runbooks/BACKUP_RESTORE.md:50] Los backups con secretos permanecen fuera de Git y con acceso restringido durante su retención y eliminación.
- [Regla: AGENTS.md] La aprobación del SPEC no sustituye la aprobación del manifiesto exacto ni autoriza ampliar el borrado a rutas no enumeradas.
- [Regla: AGENTS.md] La capacidad mínima de recuperación debe preservarse incluso cuando se eliminen backups históricos obsoletos.

## Contratos

### Entrada

```json
{
  "environment": "production",
  "mode": "dry-run|execute",
  "manifestHash": "sha256:...",
  "categories": ["litespeed-cache", "optimized-assets", "host-temp", "obsolete-backups"],
  "retainedRecoveryPoint": "/restricted/path/to/current-backup"
}
```

### Salida

```json
{
  "manifestHash": "sha256:...",
  "beforeBytes": 0,
  "afterBytes": 0,
  "freedBytes": 0,
  "deleted": [{"path": "/exact/path", "category": "host-temp"}],
  "skipped": [{"path": "/exact/path", "reason": "active-or-not-approved"}],
  "failed": [{"path": "/exact/path", "reason": "error"}],
  "health": "passed|failed"
}
```

## Casos límite

- Un archivo de `/tmp` pertenece a una operación anterior, pero sigue abierto por un proceso.
- LiteSpeed regenera archivos mientras se calcula o ejecuta el manifiesto.
- Una caché purgada produce temporalmente misses y aumenta el tiempo de respuesta inicial.
- Un backup histórico contiene el único ejemplar de una configuración, upload o estado anterior necesario para restauración.
- El backup retenido existe, pero está vacío, corrupto, tiene permisos inseguros o no cubre la restauración actual.
- Una ruta de backup incluye `.env.prod` u otro secreto; solo se exponen metadatos y el borrado no imprime contenido.
- Un candidato cambia de tamaño, fecha o hash después de aprobar el manifiesto.
- Un directorio tiene archivos no clasificados junto a candidatos válidos.
- La eliminación es parcial y deben conservarse los fallos para una decisión posterior.
- El sitio responde HTTP 200, pero la caché no se regenera, aparecen errores nuevos o un contenedor deja de estar saludable.

## Archivos o módulos relacionados

- Host productivo `149.28.97.249`, limitado a inspección y operación autorizada.
- `/tmp` del host.
- `/opt/tecnimontacargas/backups/`.
- Contenedor `tmd_ols_wordpress`.
- `/usr/local/lsws/cachedata` dentro de WordPress.
- `/var/www/vhosts/localhost/html/wp-content/litespeed` dentro de WordPress.
- `docs/runbooks/PRODUCTION.md`.
- `docs/runbooks/BACKUP_RESTORE.md`.
- `scripts/sync-production.sh` únicamente para verificación de sincronización.

## Criterios de aceptación

1. [Solicitud] Un dry-run vigente entrega un manifiesto exacto y su hash sin modificar producción ni revelar secretos.
2. [Solicitud] El usuario aprueba explícitamente el hash y las rutas del manifiesto antes de ejecutar la eliminación.
3. [Regla: docs/runbooks/BACKUP_RESTORE.md:168] El punto de recuperación retenido existe, no está vacío, tiene permisos restringidos, integridad comprobada y procedimiento de restauración identificado.
4. [Solicitud] Solo desaparecen las rutas exactas incluidas en el manifiesto aprobado; cualquier elemento nuevo, activo o modificado se omite.
5. [Solicitud] Los dos backups históricos identificados dejan de existir únicamente después de verificar el punto de recuperación retenido, sin mostrar ni copiar sus contenidos sensibles.
6. [Solicitud] No se eliminan volúmenes, contenedores, imágenes activas, datos de MariaDB, uploads, logs activos, sockets, locks vigentes ni archivos de runtime.
7. [Solicitud] El reporte final reconcilia cada candidato como eliminado, omitido o fallido y muestra el espacio efectivamente liberado.
8. [Regla: AGENTS.md] Después de ejecutar, los contenedores esperados siguen activos, el sitio canónico responde HTTP 200, las rutas representativas cargan en navegador y no aparecen errores críticos nuevos.
9. [Regla: AGENTS.md] La caché y los assets regenerables vuelven a crearse mediante tráfico controlado sin cambios de contenido ni respuestas rotas.
10. [Regla: AGENTS.md] `./scripts/sync-production.sh --check` termina sin deriva atribuible a la limpieza.

## Validación

- Pruebas unitarias: No aplica; la operación no añade código de producto. Validar de forma focalizada el generador de manifiesto si se crea una utilidad temporal.
- Pruebas de integración: ejecutar dry-run, comprobar exclusiones de rutas activas, verificar detección de archivos abiertos y simular un manifiesto alterado sin ejecutar el borrado.
- Validación manual: revisar cada ruta, tamaño, fecha, categoría, motivo, punto de recuperación retenido y hash; comprobar que no haya secretos ni patrones globales.
- Validación productiva: con aprobación separada del manifiesto, capturar disco, contenedores, HTTP y logs previos; ejecutar las rutas exactas; comprobar disco y contenedores posteriores, HTTP, navegador, regeneración de caché, errores nuevos y `./scripts/sync-production.sh --check`.

## Riesgos

- Eliminar todos los backups sin un punto de recuperación vigente puede hacer irreversible un incidente posterior.
- Un borrado recursivo mal delimitado puede afectar uploads, configuración, logs, sockets o volúmenes activos.
- Purgar caché puede aumentar temporalmente latencia y carga mientras se regenera.
- Eliminar assets optimizados puede causar estilos o scripts transitoriamente ausentes si la regeneración falla.
- Los backups contienen base de datos, uploads y configuración sensible; sus nombres y tamaños pueden registrarse, pero no su contenido.
- La ganancia de espacio esperada es moderada frente a los 39 GB libres y no justifica ampliar el alcance.

## Decisiones pendientes

- No aplica. El alcance conserva obligatoriamente un único punto de recuperación vigente y propone eliminar únicamente los backups históricos después de verificarlo; las rutas exactas todavía requieren aprobación mediante el manifiesto de dry-run.
