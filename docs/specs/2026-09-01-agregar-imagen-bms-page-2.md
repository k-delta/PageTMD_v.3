# SPEC: Incorporar imagen de apoyo en la página BMS

## Estado

- Aprobado

## Contexto

[Solicitud] Se solicita agregar a `/energia/bms/` la imagen `BMS-page-2.webp`, que ya fue subida a los Medios de WordPress, dejando libertad para elegir la ubicación visual más adecuada.

[Evidencia: production-snapshot/pages.json:333-339] La página publicada de BMS es la página WordPress `ID 792`, con slug `bms`; su `post_content` contiene el hero, la navegación interna, la introducción y las secciones informativas.

[Evidencia: production-snapshot/pages.json:339] El contenido observado de la página BMS no contiene el archivo `BMS-page-2.webp`.

[Evidencia: wp-content/themes/blocksy-child/page-792.php:1-139] El child theme tiene ajustes de presentación específicos para `page-id-792`; estos ajustes no incluyen actualmente una regla para una imagen editorial adicional.

[Evidencia: scripts/update-bms-content.php:88-139] El repositorio ya utiliza transformaciones focalizadas e idempotentes sobre el `post_content` de la página `ID 792`, con bloqueo ante precondiciones inválidas.

## Problema

[Solicitud] La página `/energia/bms/` no muestra la imagen `BMS-page-2.webp` disponible en los Medios de WordPress.

## Objetivo

[Solicitud] Mostrar `BMS-page-2.webp` dentro de la página BMS en una ubicación visual que complemente el hero y la introducción, conservando el contenido, la navegación y el diseño existente.

## Fuera del alcance

- [Solicitud] No cambiar el texto, orden, enlaces, CTAs ni navegación interna de la página BMS.
- [Solicitud] No reemplazar la imagen del menú de Energía ni modificar otras páginas.
- [Regla: AGENTS.md] No inventar, transformar, sustituir ni volver a subir la imagen; se usará el adjunto existente en WordPress.
- [Regla: AGENTS.md] No modificar `production-snapshot/pages.json` como sustituto del contenido administrado.
- [Regla: AGENTS.md] No escribir en producción, purgar caché ni desplegar como parte de la implementación local sin autorización operativa, backup y reversión identificada.
- [Regla: AGENTS.md] No modificar el tema padre, WordPress core ni plugins de terceros.

## Requisitos funcionales

1. [Solicitud] La página BMS `ID 792` debe mostrar una única instancia de la imagen cuyo nombre de archivo es `BMS-page-2.webp`.
2. [Solicitud] La imagen debe aparecer después de la navegación interna de BMS y antes del bloque introductorio “Comprenda cómo trabaja la batería”, como apoyo visual de entrada al contenido.
3. [Solicitud] La imagen debe usar un texto alternativo contextual: `BMS para monitoreo de baterías de montacargas`.
4. [Solicitud] La imagen debe conservar su proporción, quedar contenida dentro del ancho del contenido BMS y mostrar bordes, fondo y separación coherentes con el diseño actual.
5. [Regla: docs/domain/NAVIGATION.md] La incorporación debe conservar la navegación interna y no producir overflow horizontal en escritorio ni móvil.
6. [Regla: AGENTS.md] La actualización del marcado debe aplicarse al `post_content` administrado de la página BMS, no al snapshot de auditoría.
7. [Evidencia: scripts/update-bms-content.php:8-31,88-139] El procedimiento debe resolver la URL mediante el adjunto de WordPress identificado por el nombre exacto `BMS-page-2.webp`; si no encuentra exactamente un adjunto, debe detenerse sin escritura.
8. [Evidencia: scripts/update-bms-content.php:8-31,88-139] La aplicación debe ser idempotente: si el bloque canónico ya existe, no debe duplicarlo ni modificar el resto del contenido.

## Reglas de negocio

- [Regla: AGENTS.md] WordPress y sus Medios son la fuente canónica del contenido y de la multimedia administrada.
- [Regla: docs/runbooks/DEPLOYMENT.md:9-13] El código propio se gobierna desde Git, mientras que las páginas y los uploads permanecen bajo sus fuentes productivas correspondientes.
- [Regla: docs/runbooks/BACKUP_RESTORE.md:5-24] Toda modificación de contenido persistido requiere un backup verificable y una reversión identificada antes de ejecutarse en producción.
- [Regla: AGENTS.md] El cambio debe ser focalizado y preservar el comportamiento existente.

## Contratos

### Entrada

```json
{
  "pageId": 792,
  "attachmentFilename": "BMS-page-2.webp",
  "placement": "after tmd-bms-nav and before the introductory tmd-bms-intro block",
  "alt": "BMS para monitoreo de baterías de montacargas"
}
```

### Salida

```json
{
  "pageId": 792,
  "media": "one BMS-page-2.webp image block",
  "existingContent": "unchanged",
  "duplicateApplication": "no additional image block",
  "write": "only after attachment and content preconditions pass"
}
```

## Casos límite

- [Inferencia técnica] Si no existe ningún adjunto con el nombre exacto `BMS-page-2.webp`, la transformación debe bloquearse sin escritura.
- [Inferencia técnica] Si existen varios adjuntos con ese nombre, la transformación debe bloquearse para evitar seleccionar una multimedia ambigua.
- [Inferencia técnica] Si la página no contiene exactamente un bloque de navegación BMS seguido del bloque introductorio esperado, la transformación debe bloquearse sin escritura parcial.
- [Inferencia técnica] Si el nombre de archivo ya aparece en un marcado diferente al bloque canónico, la transformación debe bloquearse para evitar duplicar o reinterpretar contenido existente.
- [Inferencia técnica] Si el bloque canónico ya existe, la segunda aplicación debe informar estado idempotente y conservar el contenido byte por byte.

## Archivos o módulos relacionados

- `production-snapshot/pages.json` como evidencia de la página BMS, no como destino de edición.
- `wp-content/themes/blocksy-child/page-792.php` para los estilos específicos de la página.
- `scripts/update-bms-content.php` como patrón de transformación sobre el contenido de la página `ID 792`.
- `scripts/add-bms-page-image.php` como procedimiento focalizado a crear.
- `tests/test-add-bms-page-image.php` como prueba de transformación a crear.
- Página de WordPress `/energia/bms/`, `ID 792`.

## Criterios de aceptación

1. [Solicitud] El DOM de `/energia/bms/` contiene una única imagen asociada al archivo `BMS-page-2.webp`, con el texto alternativo `BMS para monitoreo de baterías de montacargas`.
2. [Solicitud] La imagen aparece entre la navegación interna y la introducción, sin eliminar ni reordenar ninguna sección existente.
3. [Solicitud] La imagen se muestra completa, proporcionada y contenida dentro del diseño BMS en escritorio y móvil.
4. [Regla: docs/domain/NAVIGATION.md] La página conserva sus enlaces internos, navegación por teclado y ausencia de overflow horizontal.
5. [Regla: AGENTS.md] La transformación modifica únicamente el contenido administrado de la página `ID 792` y los archivos propios estrictamente necesarios; no modifica el snapshot, inventario ni multimedia original.
6. [Evidencia: scripts/update-bms-content.php:8-31,88-139] La prueba focalizada demuestra estado inicial, resultado, idempotencia, adjunto ausente, adjunto ambiguo y precondición de contenido inválida, sin escritura parcial.

## Validación

- Pruebas unitarias: transformación focalizada sobre una copia del `post_content`; verificar inserción, `alt`, URL resuelta, idempotencia y bloqueo de precondiciones.
- Pruebas de integración: validar el script, su selección exclusiva de la página `ID 792`, la resolución exacta del adjunto y que el modo de simulación no escriba.
- Validación manual: revisar `/energia/bms/` en escritorio y móvil; confirmar posición, proporción, carga de la imagen, navegación interna, teclado, consola y overflow.
- Validación productiva: pendiente de autorización explícita; requiere backup de la base de datos/contenido, ejecución controlada del procedimiento, purga de caché aplicable y verificación HTTP/navegador según los runbooks.

## Riesgos

- [Inferencia técnica] La proporción real de la imagen podría requerir ajustar sus límites visuales después de verla renderizada en escritorio y móvil.
- [Inferencia técnica] Una URL o adjunto ambiguo podría insertar una imagen distinta de la solicitada si no se valida por nombre exacto antes de escribir.
- [Inferencia técnica] La caché de página de LiteSpeed podría ocultar temporalmente el cambio después de una eventual escritura productiva.
- [Inferencia técnica] Una edición concurrente del `post_content` podría hacer inválida la precondición inmediatamente antes de escribir.

## Decisiones pendientes

- No aplica. La solicitud autoriza elegir la ubicación; se define la posición entre la navegación interna y la introducción porque es el primer punto de contenido y permite presentar la imagen sin alterar el hero ni las secciones existentes.

## Registro de aprobación

- [Aprobación, 2026-09-01] El usuario aprobó la implementación local de este SPEC.
