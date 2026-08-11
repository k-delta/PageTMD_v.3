# SPEC: Depuración de imágenes no usadas en WordPress

## Estado

- Aprobado

## Contexto

[Solicitud] Se requiere eliminar de WordPress las imágenes que no se están usando.

[Solicitud aprobada: 2026-08-11] `DEC-01` queda resuelta con la definición conservadora: solo es elegible un adjunto de imagen sin ninguna referencia por ID, URL, ruta original ni ruta derivada en las fuentes enumeradas en los requisitos 2 a 5; “Sin adjuntar” por sí solo nunca lo hace elegible.

[Evidencia: docs/architecture/REPO_MAP.md:224] El contenido de WordPress tiene como fuente canónica MariaDB y [Evidencia: docs/architecture/REPO_MAP.md:225] la multimedia tiene como fuente canónica WordPress uploads.

[Evidencia: production-snapshot/pages.json:39] El contenido de Gutenberg puede referenciar imágenes mediante ID y URL dentro de atributos de bloques y HTML, aunque la relación `post_parent` del adjunto no determine ese uso.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-blog.php:37] El tema consume imágenes destacadas mediante la API de WordPress. [Evidencia: wp-content/plugins/tm-popup-bienvenida/tm-popup-bienvenida.php:433] Un plugin propio también consume una URL de imagen guardada en sus opciones. Por tanto, que una imagen aparezca como “Sin adjuntar” en la biblioteca no prueba que no esté usada.

No se ha ejecutado una auditoría vigente de la biblioteca multimedia ni se ha eliminado contenido local o productivo durante la redacción de este SPEC.

## Problema

WordPress puede conservar imágenes que ya no son necesarias, pero eliminarlas basándose solo en su estado “Sin adjuntar” puede romper contenido, estilos, metadatos, opciones o integraciones que las referencian indirectamente.

## Objetivo

Identificar de forma auditable las imágenes sin referencias vigentes, obtener aprobación sobre un manifiesto exacto y eliminar exclusivamente ese conjunto con backup y reversión verificables, sin afectar las imágenes utilizadas por el sitio.

## Fuera del alcance

- Eliminar videos, documentos, fuentes u otros adjuntos cuyo MIME no sea de imagen.
- Eliminar imágenes de Inventario/Firebase o de servicios externos.
- Optimizar, recomprimir, redimensionar, renombrar o sustituir imágenes usadas.
- Borrar archivos huérfanos de uploads que no tengan un adjunto registrado en WordPress, salvo que una ampliación posterior del SPEC los incluya expresamente.
- Actualizar WordPress, temas, plugins o dependencias.
- Modificar contenido para dejar imágenes sin referencias y hacerlas elegibles.

## Requisitos funcionales

1. [Solicitud] La operación debe retirar de la biblioteca y de uploads únicamente imágenes que cumplan la definición aprobada de “no usada” en `DEC-01`.
2. [Evidencia: production-snapshot/pages.json:39] La auditoría debe buscar referencias por ID, URL absoluta, URL relativa y nombre/ruta del archivo original en contenido publicado, borradores, contenido privado y revisiones que se conservarán.
3. [Evidencia: wp-content/themes/blocksy-child/inc/tmd-blog.php:37] La auditoría debe excluir imágenes usadas como imagen destacada, miniatura o metadato de entradas, páginas y tipos de contenido registrados.
4. [Evidencia: wp-content/plugins/tm-popup-bienvenida/tm-popup-bienvenida.php:433] La auditoría debe excluir imágenes referenciadas en opciones, widgets, personalizador, metadatos, términos, menús, configuración de plugins propios o de terceros y CSS administrado por WordPress.
5. [Regla: AGENTS.md] La auditoría debe excluir los archivos versionados del tema y plugins propios, así como las imágenes publicadas por Inventario/Firebase.
6. [Solicitud] Antes de borrar debe generarse un manifiesto de candidatos que incluya, por cada adjunto, ID, nombre, URL, MIME, fecha, tamaño, ruta relativa y derivados que WordPress eliminaría.
7. [Solicitud] Ningún candidato debe eliminarse hasta que el usuario apruebe explícitamente el manifiesto exacto.
8. [Regla: docs/runbooks/BACKUP_RESTORE.md:11] Antes de la eliminación debe existir un backup verificado de la base de datos y [Regla: docs/runbooks/BACKUP_RESTORE.md:42] de los archivos originales y derivados incluidos en el manifiesto.
9. [Solicitud] La eliminación debe usar las operaciones de adjuntos de WordPress para retirar el registro, sus metadatos y los tamaños derivados del conjunto aprobado; no debe borrar rutas mediante patrones globales.
10. [Solicitud] La ejecución debe producir un reporte final con adjuntos eliminados, omitidos y fallidos, junto con el motivo de cada omisión o fallo.
11. [Solicitud] Si un candidato adquiere una referencia entre la auditoría y la eliminación, debe excluirse de la operación y registrarse como omitido.

## Reglas de negocio

- [Regla: AGENTS.md] No se deben inventar ni sustituir imágenes comerciales o de inventario.
- [Regla: docs/architecture/REPO_MAP.md:223] Inventario/Firebase sigue siendo la fuente canónica de las imágenes de equipos.
- [Regla: docs/runbooks/BACKUP_RESTORE.md:54] `production-snapshot` no sustituye el backup de MariaDB ni el backup de uploads.
- [Regla: AGENTS.md] Una escritura productiva requiere autorización, backup verificado y validación posterior.

## Contratos

### Entrada

```json
{
  "environment": "production",
  "attachmentIds": [123],
  "manifestHash": "sha256:...",
  "mode": "dry-run|execute"
}
```

### Salida

```json
{
  "manifestHash": "sha256:...",
  "audited": 0,
  "candidates": 0,
  "deleted": [],
  "skipped": [{"attachmentId": 123, "reason": "reference-found"}],
  "failed": []
}
```

## Casos límite

- Una imagen “Sin adjuntar” aparece por URL dentro de un bloque Gutenberg, HTML, CSS o shortcode.
- Una imagen está referenciada solo por una opción serializada, un widget, un término, un menú o metadatos de un plugin.
- Una imagen es destacada de contenido no publicado que debe conservarse.
- Una URL usa un dominio anterior, una ruta relativa, codificación distinta o uno de los tamaños derivados.
- El original no existe, pero sí existe el adjunto o alguno de sus derivados.
- El archivo existe en uploads, pero no hay un adjunto correspondiente en la base de datos.
- Dos adjuntos o contenidos apuntan al mismo archivo.
- Una referencia aparece después de generar el manifiesto.
- Un borrado falla parcialmente y debe poder recuperarse desde el backup.

## Archivos o módulos relacionados

- Base de datos productiva de WordPress: adjuntos, contenido, metadatos, términos y opciones.
- Uploads productivos de WordPress.
- `production-snapshot/pages.json`
- `production-snapshot/posts.json`
- `wp-content/themes/blocksy-child/`
- `wp-content/plugins/`
- `docs/runbooks/PRODUCTION.md`
- `docs/runbooks/BACKUP_RESTORE.md`

## Criterios de aceptación

1. [Solicitud] Un dry-run vigente entrega el manifiesto completo sin modificar la base de datos ni uploads.
2. [Solicitud] Cada imagen marcada como usada por cualquiera de las fuentes incluidas en los requisitos 2 a 5 queda fuera del manifiesto.
3. [Solicitud] El conjunto ejecutado coincide exactamente con los IDs y el hash del manifiesto aprobado; cualquier cambio posterior obliga a generar y aprobar otro manifiesto.
4. [Regla: docs/runbooks/BACKUP_RESTORE.md:72] Los backups de base de datos y multimedia existen, no están vacíos, tienen integridad comprobada y una ruta de restauración identificada antes de borrar.
5. [Solicitud] Después de ejecutar, los adjuntos aprobados y sus derivados dejan de existir en WordPress y uploads, salvo los reportados como omitidos o fallidos.
6. [Solicitud] Las URLs públicas, páginas, entradas, menús, cabecera, pie, popup, catálogos y flujos que usan multimedia continúan cargando sin imágenes rotas nuevas.
7. [Solicitud] El reporte final permite reconciliar el manifiesto aprobado con cada elemento eliminado, omitido o fallido.
8. [Regla: AGENTS.md] La verificación productiva incluye caché, HTTP, navegador, logs y control de sincronización, y distingue claramente qué evidencia se obtuvo.

## Validación

- Pruebas unitarias: probar normalización de IDs, URLs, rutas originales y derivados; referencias serializadas; exclusiones; cambio de referencia posterior al manifiesto; y reporte de fallos parciales.
- Pruebas de integración: ejecutar dry-run sobre una copia anonimizada o un entorno desechable con adjuntos usados directa e indirectamente, candidatos sin referencias y archivos faltantes; confirmar que solo los candidatos aprobados se eliminan.
- Validación manual: revisar el manifiesto por ID, nombre, URL, fecha, tamaño y derivados; muestrear candidatos desde la biblioteca y buscar sus IDs, URLs y rutas en las fuentes auditadas.
- Validación productiva: con autorización vigente, verificar backups y espacio, ejecutar el manifiesto aprobado, purgar caché, revisar HTTP y navegador en rutas representativas, buscar respuestas 404 de uploads y errores nuevos en logs, y ejecutar `./scripts/sync-production.sh --check`.

## Riesgos

- Referencias almacenadas en formatos o tablas no identificadas pueden producir falsos positivos y romper imágenes visibles.
- Un backup incompleto o no restaurable puede convertir una eliminación incorrecta en pérdida permanente.
- Cambios editoriales concurrentes pueden invalidar el manifiesto entre el dry-run y la ejecución.
- Plugins de caché, CDN o CSS generado pueden ocultar temporalmente referencias o fallos.
- El tamaño liberado puede ser menor al esperado si la mayor parte del espacio corresponde a archivos no registrados, videos u otros adjuntos fuera de alcance.

## Decisiones pendientes

- No aplica. `DEC-01` fue resuelta y aprobada el 2026-08-11.
