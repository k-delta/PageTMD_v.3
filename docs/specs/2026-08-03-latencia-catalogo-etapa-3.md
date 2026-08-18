# SPEC: Reducir peso y fallos de imágenes de catálogo — etapa 3

## Estado

- Cancelado

## Contexto

- [Solicitud] La tercera etapa propuesta busca optimizar las imágenes de `/equipos/` y `/energia/` después de sacar Firebase del arranque y limitar el DOM a 12 tarjetas.
- [Evidencia: docs/specs/2026-08-03-latencia-catalogo-etapa-2.md:26] La etapa 2 excluyó redimensionar, convertir o corregir imágenes de Firebase Storage.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:580] PageTMD utiliza directamente `media.imagenPrincipal` para las tarjetas.
- [Evidencia: /Users/lauracatalinapreciadoballen/Desktop/Inventario/functions/src/functions/listarEquiposWordpress.ts:151] La Cloud Function publica `media` sin un contrato de miniatura separado.
- [Evidencia: consulta HTTP de solo lectura 2026-08-03] El endpoint vigente devolvió 116 registros y únicamente `imagenPrincipal` y `galeria` dentro de `media`.
- [Evidencia: medición HTTP 2026-08-03] Durante el diagnóstico se observó una imagen de Firebase con respuesta `403` y tiempos de carga de imágenes visibles entre 0.66 y 0.96 segundos.

## Problema

- [Solicitud] Las tarjetas descargan la imagen principal original aunque su área visible es una miniatura, y una URL inválida puede terminar en el fallback después de una solicitud fallida.

## Objetivo

- [Solicitud] Entregar a los catálogos miniaturas WebP dimensionadas para tarjeta, conservar la imagen principal para la ficha y evitar que una miniatura ausente o inválida rompa la tarjeta.

## Fuera del alcance

- [Solicitud] Esta etapa no modifica caché, cron, lock, filtros, paginación, datos comerciales ni límite de 12 tarjetas de las etapas 1 y 2.
- [Regla: AGENTS.md] No inventar ni sustituir imágenes de equipos.
- [Regla: docs/domain/INVENTORY.md] Inventario/Firebase continúa siendo la fuente canónica de imágenes.
- [Regla: AGENTS.md] No desplegar Functions, ejecutar backfill, escribir Firestore/Storage ni desplegar PageTMD sin autorización explícita independiente.
- [Solicitud] No convertir galerías ni imágenes ajenas a los catálogos públicos en esta etapa.

## Requisitos funcionales

1. [Solicitud] Inventario debe producir para cada nueva imagen principal de montacargas o batería una miniatura WebP de tarjeta, sin reemplazar la imagen principal original.
2. [Solicitud] La miniatura debe respetar orientación, no ampliarse por encima del original y caber en un máximo de 720 × 540 píxeles.
3. [Solicitud] La calidad WebP objetivo será 78.
4. [Solicitud] El registro de inventario debe conservar la URL principal y añadir una URL pública de miniatura bajo `media.imagenMiniatura`.
5. [Solicitud] `listarEquiposWordpress` debe exponer únicamente ambas URLs públicas dentro del contrato `media`; no debe generar miniaturas durante una solicitud GET.
6. [Solicitud] PageTMD debe usar `media.imagenMiniatura` en tarjetas PHP y dinámicas y usar `media.imagenPrincipal` en la ficha individual.
7. [Solicitud] Si la miniatura falta o falla, la tarjeta debe intentar una sola vez la imagen principal; si ambas fallan, debe mostrar el fallback visual existente.
8. [Solicitud] La primera imagen visible puede cargarse con prioridad alta; las demás imágenes de tarjeta deben conservar carga diferida y decodificación asíncrona.
9. [Solicitud] Un proceso de backfill separado debe poder generar miniaturas para registros públicos existentes, primero en modo simulación y después únicamente con autorización productiva.
10. [Solicitud] Una URL principal que responda `403` debe reportarse como dato inválido durante el backfill y no debe sobrescribirse ni reemplazarse con una imagen inventada.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Inventario/Firebase gobierna imágenes, disponibilidad y registros publicados.
- [Regla: /Users/lauracatalinapreciadoballen/Desktop/Inventario/AGENTS.md] Las escrituras Firebase requieren autorización, proyecto explícito, backup y rollback cuando aplique.
- [Regla: AGENTS.md] WordPress consume imágenes del inventario, pero no corrige la fuente con datos ficticios.

## Contratos

### Entrada

```json
{
  "media": {
    "imagenPrincipal": "https://.../original.jpg",
    "imagenMiniatura": "https://.../thumbnail.webp"
  }
}
```

### Salida

```json
{
  "card": {
    "image": "https://.../thumbnail.webp",
    "fallbackImage": "https://.../original.jpg"
  },
  "detail": {
    "image": "https://.../original.jpg"
  }
}
```

## Casos límite

- [Solicitud] Sin imagen principal ni miniatura, mostrar directamente “Imagen no disponible”.
- [Solicitud] Con miniatura ausente y principal válida, usar la principal sin romper la tarjeta.
- [Solicitud] Con miniatura inválida y principal válida, reintentar solo la principal y evitar ciclos de error.
- [Solicitud] Con ambas URLs inválidas, eliminar el elemento `img` y conservar el fallback visual.
- [Solicitud] Un PNG/JPEG con transparencia, orientación EXIF o dimensiones menores al máximo debe producir un WebP visualmente equivalente sin ampliación.
- [Solicitud] Una actualización de imagen principal debe invalidar la miniatura anterior y asociar la nueva miniatura al mismo registro.
- [Solicitud] Objetos de Storage fuera de las carpetas de montacargas y baterías no deben procesarse.

## Archivos o módulos relacionados

- `/Users/lauracatalinapreciadoballen/Desktop/Inventario/functions/src/functions/listarEquiposWordpress.ts`
- Productor de miniaturas y prueba focalizada nuevos bajo `/Users/lauracatalinapreciadoballen/Desktop/Inventario/functions/src/`.
- Script de backfill con modo simulación bajo `/Users/lauracatalinapreciadoballen/Desktop/Inventario/functions/src/scripts/`.
- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js`
- `tests/test-tmd-inventory-background-refresh.php`
- `tests/test-tmd-inventory-api-js.js`

## Criterios de aceptación

1. [Solicitud] Una prueba demuestra que una imagen válida produce WebP de máximo 720 × 540, calidad configurada y sin ampliación.
2. [Solicitud] Una prueba demuestra que objetos fuera de las rutas admitidas y miniaturas generadas no causan procesamiento recursivo.
3. [Solicitud] Una prueba demuestra que el endpoint conserva `imagenPrincipal` y publica `imagenMiniatura` sin hacer transformación durante GET.
4. [Solicitud] Una prueba PHP demuestra que tarjetas prefieren la miniatura, fichas conservan la principal y el JSON público incluye fallback explícito.
5. [Solicitud] Una prueba JavaScript demuestra la secuencia miniatura → principal → fallback sin ciclos.
6. [Solicitud] La primera tarjeta PHP usa prioridad alta y las demás tarjetas usan `loading="lazy"` y `decoding="async"`.
7. [Solicitud] El backfill ofrece simulación sin escrituras, conteos por resultado y ejecución limitada a montacargas/baterías públicos.
8. [Solicitud] En navegador, `/equipos/` y `/energia/` conservan recorte, proporción, cambio de página, filtros y fallback visual.
9. [Solicitud] En producción, después de despliegues y backfill autorizados, las tarjetas visibles solicitan WebP dimensionados y ninguna URL pública de catálogo devuelve `403` sin quedar reportada.

## Validación

- Pruebas unitarias: transformación WebP, selección de rutas, contrato público, selección/fallback PHP y secuencia de error JavaScript.
- Pruebas de integración: Functions con emuladores y proyecto explícito; render local de ambos catálogos con miniatura válida, ausente e inválida.
- Validación manual: navegador servido en escritorio y móvil; red, dimensiones, formato, prioridad, filtros, paginación y fichas.
- Validación productiva: solo con autorización separada; backups, despliegues focalizados, backfill en simulación, ejecución aprobada, purga de caché y medición antes/después.

## Riesgos

- Generar miniaturas en una lectura pública reintroduciría latencia; el procesamiento debe ocurrir fuera del GET.
- Actualizar Firestore o Storage sin idempotencia puede duplicar objetos o dejar URLs desincronizadas.
- Un backfill sin límites puede aumentar costo, tiempo o escrituras y requiere operación separada.
- La miniatura puede quedar obsoleta si cambia la principal y no existe una asociación verificable.
- El cambio cruza dos repositorios y no produce mejora real hasta desplegar productor, consumidor y procesar registros existentes.

## Decisiones pendientes

- Ninguna.

## Decisiones aprobadas

- [Solicitud: aprobación 2026-08-03] Alcance cross-repo aprobado: miniatura WebP de máximo 720 × 540 y calidad 78 generada en Inventario, publicada por la API y consumida con fallback por PageTMD.
- [Solicitud: cancelación 2026-08-03] Se decide no realizar el cambio estructural en la API; esta etapa no forma parte del despliegue solicitado.
