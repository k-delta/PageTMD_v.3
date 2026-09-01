# SPEC: Reemplazar la imagen de Nuestro equipo en Trabaja con nosotros

## Estado

- Aprobado

## Contexto

[Solicitud: capturas y URL pública aportadas el 2026-09-01] Se solicita reemplazar la imagen de la sección “Nuestro equipo” de la página “Trabaja con nosotros” por el archivo de Medios `gerencia.webp`, publicado en `https://tecnimontacargas.com/wp-content/uploads/2026/09/gerencia.webp`.

[Evidencia histórica: wp-content/themes/blocksy-child/page-273.php:3,100-153] La página “Trabaja con nosotros” es la página WordPress `ID 273`; antes de este cambio su filtro de contenido resolvía un adjunto WebP mediante el término genérico `gerencia` y reemplazaba referencias que contuvieran ese término.

[Evidencia histórica: scripts/update-jobs-management-image.php:3-4,14-57,81-143] El repositorio disponía de un procedimiento específico para reemplazar la imagen de “Nuestro equipo”, pero antes de este cambio buscaba de forma ambigua el recurso identificado como `gerencia.webp`.

[Evidencia: scripts/update-jobs-management-image.php:145-250] El procedimiento existente valida el bloque, ofrece `TMD_DRY_RUN=1` y actualiza únicamente el contenido de la página cuando se ejecuta sin dry-run.

## Problema

[Solicitud] La sección de “Nuestro equipo” no está mostrando la imagen específica seleccionada en los Medios de WordPress y la resolución genérica por “gerencia” puede escoger otro adjunto.

## Objetivo

[Solicitud] Mostrar en producción la imagen exacta `gerencia.webp` en la sección “Nuestro equipo” de “Trabaja con nosotros”, conservando el resto del contenido, el encuadre y las demás imágenes de la página.

## Fuera del alcance

- [Solicitud] No cambiar el hero, el testimonio, el avatar, textos, formulario, vacantes, enlaces ni estructura de la página.
- [Solicitud] No reemplazar, eliminar ni volver a subir archivos de la Biblioteca de medios.
- [Solicitud] No modificar otras páginas, el mega menú, el tema padre, WordPress core ni plugins de terceros.
- [Regla: AGENTS.md] No modificar el snapshot de producción como sustituto del contenido administrado.
- [Regla: AGENTS.md] No ejecutar la escritura productiva sin backup verificable, control de deriva y reversión identificada.

## Requisitos funcionales

1. [Solicitud] La sección “Nuestro equipo” de la página WordPress `ID 273` debe usar el attachment cuyo nombre de archivo exacto sea `gerencia.webp`.
2. [Solicitud] La resolución del attachment debe distinguir el nombre exacto del archivo y detenerse si no existe exactamente un attachment WebP coincidente o si existen varios.
3. [Solicitud] El contenido de la sección debe conservar todos sus textos, atributos no relacionados, clases, orden y estructura.
4. [Solicitud] El hero, el testimonio, el avatar y las demás imágenes de “Trabaja con nosotros” deben conservarse sin cambios.
5. [Solicitud] La imagen debe conservar el encuadre y las reglas responsive actuales de la página `ID 273`, sin recortes nuevos, deformación ni overflow horizontal.
6. [Regla: AGENTS.md] La escritura persistente debe realizarse únicamente sobre el contenido administrado de la página `ID 273`, mediante un procedimiento versionado e idempotente.
7. [Regla: docs/runbooks/DEPLOYMENT.md:9-13] La actualización de contenido persistente debe mantenerse separada del despliegue del código del child theme.
8. [Solicitud] La publicación productiva debe incluir backup verificable, dry-run previo, ejecución controlada, purga de caché aplicable y comprobación posterior.

## Reglas de negocio

- [Regla: AGENTS.md] WordPress y sus Medios son la fuente canónica del contenido y la multimedia administrada.
- [Regla: AGENTS.md] No deben inventarse ni modificarse datos comerciales o imágenes distintas de la proporcionada por el usuario.
- [Regla: docs/runbooks/BACKUP_RESTORE.md] Toda modificación de contenido persistido requiere un backup verificable y una reversión identificada.

## Contratos

### Entrada

```json
{
  "pageId": 273,
  "pagePath": "/nosotros/trabaja-con-nosotros/",
  "section": "Nuestro equipo",
  "attachmentFilename": "gerencia.webp"
}
```

### Salida

```json
{
  "pageId": 273,
  "sectionImage": "gerencia.webp",
  "otherPageContent": "unchanged",
  "otherMedia": "unchanged",
  "duplicateApplication": "no additional replacement"
}
```

## Casos límite

- [Inferencia técnica] Si el attachment exacto no existe, el procedimiento debe bloquearse sin escritura.
- [Inferencia técnica] Si existen varios attachments WebP con el mismo nombre exacto en rutas distintas, el procedimiento debe bloquearse para evitar seleccionar una multimedia ambigua.
- [Inferencia técnica] Si la página no contiene exactamente la referencia esperada de la imagen de “Nuestro equipo”, el procedimiento debe bloquearse sin modificar contenido parcial.
- [Inferencia técnica] Si la imagen objetivo ya está aplicada, la segunda ejecución debe ser idempotente y no modificar el contenido.
- [Inferencia técnica] Si LiteSpeed conserva HTML o contenido cacheado, la purga debe realizarse antes de evaluar la visibilidad productiva.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/page-273.php`
- `scripts/update-jobs-management-image.php`
- Prueba focalizada de resolución exacta y transformación de la imagen de “Nuestro equipo”.
- Página WordPress `ID 273`, `/nosotros/trabaja-con-nosotros/`.
- Attachment de Medios `gerencia.webp`.

## Criterios de aceptación

1. [Solicitud] El HTML de la sección “Nuestro equipo” en la página `ID 273` referencia el attachment exacto `gerencia.webp`.
2. [Solicitud] El resto del contenido de la página permanece sin cambios funcionales ni textuales.
3. [Solicitud] El hero, avatar, testimonio y demás recursos visuales permanecen iguales.
4. [Solicitud] La imagen conserva el encuadre, proporción y comportamiento responsive actuales en escritorio y móvil.
5. [Regla: AGENTS.md] Una referencia ausente o ambigua bloquea la escritura sin cambios parciales.
6. [Solicitud] La ejecución repetida no duplica ni altera nuevamente la referencia de la imagen.
7. [Solicitud] Después del procedimiento productivo, la página responde correctamente y muestra la imagen nueva sin errores de consola ni overflow horizontal.

## Validación

- Pruebas unitarias: validar resolución por nombre exacto, ausencia de attachment, coincidencia ambigua, transformación única e idempotencia.
- Pruebas de integración: ejecutar el procedimiento en dry-run sobre la página `ID 273`, confirmar el attachment y verificar que solo se actualiza la referencia de “Nuestro equipo”; ejecutar `php -l` sobre los PHP modificados.
- Validación manual: revisar la página en escritorio y móvil, confirmar encuadre, proporción, carga, hero, avatar, testimonio, formulario, consola y overflow.
- Validación productiva: con autorización vigente, verificar backup y `./scripts/sync-production.sh --check`, ejecutar el dry-run, aplicar la escritura controlada, purgar LiteSpeed, comprobar HTTP/navegador/logs y repetir el control de sincronización.

## Riesgos

- [Inferencia técnica] Un selector o búsqueda genérica por “gerencia” podría seleccionar una imagen distinta a la solicitada.
- [Evidencia, URL pública aportada el 2026-09-01] WordPress publica el attachment objetivo como `gerencia.webp` bajo `/wp-content/uploads/2026/09/`.
- [Inferencia técnica] La caché de página o navegador puede ocultar el cambio después de la escritura productiva.
- [Inferencia técnica] Una edición concurrente del contenido de la página puede invalidar la precondición antes de escribir.

## Decisiones pendientes

- El archivo exacto queda confirmado por la URL pública aportada por el usuario: `gerencia.webp`.

## Registro de aprobación

- [Aprobación, 2026-09-01] El usuario aprobó la implementación local y la publicación productiva de este SPEC.
