# SPEC: Ocultar historias de éxito cuando no hay publicaciones

## Estado

- Borrador

## Contexto

[Evidencia: producción `/var/www/vhosts/localhost/html/wp-content/plugins/tmd-historias-exito/tmd-historias-exito.php`:294] El shortcode `tmd_historias_exito` consulta únicamente historias publicadas.

[Evidencia: producción `/var/www/vhosts/localhost/html/wp-content/plugins/tmd-historias-exito/tmd-historias-exito.php`:295] Cuando la consulta está vacía, los usuarios con permiso `edit_posts` reciben una sección HTML con el mensaje “Historias de éxito: todavía no hay historias publicadas”; los demás usuarios reciben una cadena vacía.

[Evidencia: production-snapshot/plugins.json:87] El plugin productivo activo se llama `tmd-historias-exito` y no está incluido actualmente en `wp-content/plugins/` del repositorio ni en las rutas administradas por `scripts/sync-production.sh`.

[Evidencia: production-snapshot/pages.json:39] La portada ejecuta el shortcode `[tmd_historias_exito]`.

## Problema

[Solicitud] Cuando no existen historias de éxito publicadas, un usuario con sesión y permisos editoriales ve un mensaje de estado dentro de una sección que ocupa altura en la portada.

## Objetivo

[Solicitud] No renderizar contenido ni contenedor del módulo de historias de éxito cuando la consulta no devuelve publicaciones, independientemente del usuario que visite la portada.

## Fuera del alcance

- [Solicitud] Crear, publicar, editar o eliminar historias de éxito.
- [Regla: AGENTS.md] Inventar testimonios, clientes, imágenes o hechos comerciales.
- [Solicitud] Rediseñar el carrusel cuando sí existen historias publicadas.
- [Regla: AGENTS.md] Desplegar a producción, modificar datos o purgar caché sin autorización explícita posterior.

## Requisitos funcionales

1. [Solicitud] Si no hay historias de éxito con estado `publish`, el shortcode debe devolver una salida vacía para visitantes anónimos y usuarios autenticados de cualquier rol.
2. [Solicitud] En el estado vacío no debe renderizarse el mensaje “Historias de éxito: todavía no hay historias publicadas.”
3. [Solicitud] En el estado vacío no debe renderizarse la sección, el `div` envolvente ni otro elemento del plugin que reserve altura.
4. [Solicitud] Si existe al menos una historia publicada, el carrusel debe conservar su contenido y comportamiento actuales.

## Reglas de negocio

- [Regla: AGENTS.md] Solo se muestran historias reales publicadas mediante la fuente administrada por el plugin; no se crean datos sustitutos.

## Contratos

### Entrada

Estado de la consulta de WordPress para `tmd_success_story` con `post_status=publish`.

### Salida

- Sin publicaciones: cadena vacía.
- Con publicaciones: HTML vigente del carrusel.

## Casos límite

- Usuario anónimo sin historias publicadas.
- Administrador o editor autenticado sin historias publicadas.
- Historias existentes únicamente en borrador, privadas o papelera.
- Una o más historias publicadas.

## Archivos o módulos relacionados

- `wp-content/plugins/tmd-historias-exito/tmd-historias-exito.php` (debe incorporarse desde la copia productiva verificada antes de editar).
- `wp-content/plugins/tmd-historias-exito/assets/css/tmd-historias-exito.css`.
- `wp-content/plugins/tmd-historias-exito/assets/js/tmd-historias-exito.js`.
- `scripts/sync-production.sh`.
- `AGENTS.md`.
- `docs/architecture/REPO_MAP.md`.
- `production-snapshot/plugins.json`.
- `production-snapshot/pages.json`.

## Criterios de aceptación

1. [Solicitud] Con cero historias publicadas y usuario anónimo, el shortcode produce exactamente una cadena vacía.
2. [Solicitud] Con cero historias publicadas y usuario con permiso `edit_posts`, el shortcode produce exactamente una cadena vacía.
3. [Solicitud] Con cero historias publicadas, el HTML de la portada no contiene `tmd-success-showcase` ni el mensaje de estado vacío.
4. [Solicitud] Con al menos una historia publicada, el shortcode sigue renderizando `tmd-success-showcase` y las historias recuperadas.
5. [Regla: AGENTS.md] El plugin queda versionado como fuente canónica y cubierto por el control de sincronización productiva antes de un eventual despliegue.

## Validación

- Pruebas unitarias: comprobación focalizada de las ramas sin publicaciones y con publicaciones del shortcode.
- Pruebas de integración: validar que el shortcode vacío no agregue HTML y que el carrusel siga generándose con una publicación.
- Validación manual: revisar el HTML de la portada en sesión anónima y con usuario editorial, en escritorio y móvil, confirmando ausencia de texto y espacio vacío.
- Validación productiva: pendiente de autorización explícita de despliegue; después, backup, despliegue solo de archivos modificados, purga de caché, verificación en navegador y `./scripts/sync-production.sh --check`.

## Riesgos

- El plugin activo existe en producción pero falta del repositorio; modificar una copia incompleta o no verificada podría introducir deriva.
- Una caché de página por rol o sesión puede conservar temporalmente el mensaje tras un despliegue si no se purga y verifica.
- La sección contenedora de la portada que contiene el shortcode podría conservar espacio propio aunque el plugin devuelva vacío; la validación visual debe distinguir altura generada por el plugin de altura del bloque editor.

## Decisiones pendientes

- Ninguna decisión funcional pendiente.

