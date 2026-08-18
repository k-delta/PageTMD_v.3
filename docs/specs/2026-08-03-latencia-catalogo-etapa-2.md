# SPEC: Reducir DOM inicial de catálogos — etapa 2

## Estado

- Aprobado

## Contexto

- [Solicitud] Después de la etapa 1, se solicita aplicar la segunda etapa propuesta para acelerar `/equipos/` y `/energia/`.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:674] El servidor imprime actualmente una tarjeta HTML por cada registro disponible.
- [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js:21] JavaScript recopila todas las tarjetas existentes en el DOM después de `DOMContentLoaded`.
- [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js:152] Solo después de cargar el documento, JavaScript oculta las tarjetas que no pertenecen a la página visible.
- [Evidencia: medición HTTP 2026-08-03] `/energia/` entregó 286776 bytes de HTML sin compresión y 173 etiquetas de imagen para 84 baterías, aunque la interfaz muestra 12 resultados por página.

## Problema

- [Solicitud] El navegador debe analizar, construir y recorrer todas las tarjetas e imágenes del catálogo antes de dejar visibles únicamente los primeros resultados.

## Objetivo

- [Solicitud] Limitar el HTML inicial a un máximo de 12 tarjetas y mantener los resultados restantes como datos compactos, creando en el DOM solo la página visible durante filtros y paginación.

## Fuera del alcance

- [Solicitud] Esta etapa no cambia la actualización horaria, fallback ni lock implementados en la etapa 1.
- [Solicitud] Esta etapa no redimensiona, convierte ni corrige imágenes de Firebase Storage.
- [Solicitud] Esta etapa no cambia datos, disponibilidad, textos comerciales, orden, campos de filtro ni cantidad de resultados por página.
- [Solicitud] Esta etapa no cambia URLs de fichas o cotización.
- [Regla: AGENTS.md] No modificar Inventario/Firebase, WordPress core, tema padre ni plugins de terceros.
- [Regla: AGENTS.md] No desplegar, hacer commit, push ni escribir en producción sin autorización explícita para esa acción.

## Requisitos funcionales

1. [Solicitud] El HTML inicial de cada cuadrícula debe contener como máximo el valor `per_page`, actualmente 12 tarjetas.
2. [Solicitud] El servidor debe entregar todos los resultados disponibles mediante un bloque JSON estructurado y seguro, sin duplicar el HTML completo de cada tarjeta.
3. [Solicitud] El JSON debe contener únicamente datos públicos necesarios para filtros, tarjeta, ficha y cotización; no debe incluir secretos ni campos internos ajenos a la presentación.
4. [Solicitud] JavaScript debe construir y mantener en el DOM únicamente las tarjetas de la página visible.
5. [Solicitud] Marca, categoría, subcategoría, alturas, condición, operario, reach, voltaje y capacidad deben conservar coincidencias y conteos actuales.
6. [Solicitud] Aplicar, limpiar, navegar entre páginas y responder a historial del navegador debe mantener el comportamiento actual sin recargar la página.
7. [Solicitud] Cada tarjeta creada dinámicamente debe conservar clases, etiquetas, especificaciones, imagen lazy, fallback visual, ficha y parámetros de cotización actuales.
8. [Solicitud] Una URL abierta con filtros debe renderizar inicialmente hasta 12 resultados que coincidan con esos filtros antes de que JavaScript se ejecute.
9. [Solicitud] Si el JSON falta o es inválido, las tarjetas iniciales renderizadas por PHP deben permanecer utilizables y no desaparecer.
10. [Solicitud] Las fichas individuales deben conservar su renderizado PHP actual y no incluir el payload completo del catálogo.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Inventario/Firebase sigue siendo la fuente canónica de equipos, estados, disponibilidad, marcas, modelos e imágenes.
- [Regla: docs/domain/INVENTORY.md] WordPress publica únicamente tipos aceptados y registros cuyo estado permite publicación.
- [Regla: AGENTS.md] No inventar equipos ni valores para completar registros inválidos.

## Contratos

### Entrada

```json
{
  "type": "montacargas|bateria",
  "perPage": 12,
  "filters": {}
}
```

### Salida

```json
{
  "items": [
    {
      "id": "identificador público",
      "title": "título público",
      "image": "URL pública",
      "detailUrl": "URL pública",
      "contactUrl": "URL pública",
      "classes": {},
      "tags": [],
      "filters": {},
      "specs": []
    }
  ]
}
```

## Casos límite

- [Solicitud] Cero resultados debe conservar el mensaje vacío y ocultar paginación.
- [Solicitud] Menos de 12 resultados debe mostrar únicamente los existentes y ocultar paginación.
- [Solicitud] Más de 12 resultados debe crear botones suficientes y nunca mantener más de 12 tarjetas en el DOM.
- [Solicitud] Cambiar un filtro desde una página posterior debe volver a la primera página válida.
- [Solicitud] Una imagen ausente o fallida debe conservar el fallback visual actual también en tarjetas dinámicas.
- [Solicitud] Texto y URLs incluidos en JSON deben codificarse sin permitir cierre del bloque `<script>` ni inyección HTML.
- [Solicitud] Valores Unicode, tildes y espacios deben conservar comparación, títulos y parámetros de cotización.
- [Solicitud] Si la página solicitada deja de existir después de filtrar, debe ajustarse a la última página válida.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js`
- `wp-content/themes/blocksy-child/assets/css/tmd-inventory-api.css` solo si una adaptación visual resulta necesaria.
- `tests/test-tmd-inventory-background-refresh.php`
- Prueba JavaScript focalizada nueva si existe infraestructura ejecutable sin añadir dependencias.

## Criterios de aceptación

1. [Solicitud] Con más de 12 registros, una prueba demuestra que el HTML inicial contiene exactamente 12 `<article class="...tmd-api-card">` y el JSON contiene todos los resultados.
2. [Solicitud] Una prueba demuestra que el JSON contiene el contrato público esperado y escapa de forma segura texto capaz de cerrar un `<script>`.
3. [Solicitud] En navegador, carga inicial, filtros, limpiar, paginación y `popstate` mantienen como máximo 12 tarjetas en el DOM.
4. [Solicitud] Tarjetas PHP y dinámicas conservan título, etiquetas, especificaciones, imagen, ficha y cotización equivalentes.
5. [Solicitud] Una URL con filtros entrega inicialmente hasta 12 tarjetas coincidentes.
6. [Solicitud] Un payload ausente o inválido conserva las tarjetas PHP iniciales.
7. [Solicitud] Las fichas individuales no incluyen el JSON del catálogo y mantienen navegación y cotización.
8. [Solicitud] El HTML inicial de ambos catálogos reduce cantidad de tarjetas y etiquetas de imagen respecto a la evidencia previa.

## Validación

- Pruebas unitarias: Probar construcción del modelo público, límite inicial, JSON completo/seguro, filtros iniciales y fichas sin payload.
- Pruebas de integración: Renderizar ambos catálogos con más de 12 registros y verificar tarjetas PHP, contrato JSON, filtros, URLs y ausencia de llamadas remotas públicas.
- Validación manual: En navegador servido, comprobar escritorio y móvil, filtros, limpiar, todas las páginas, historial, imágenes válidas/fallidas y límite de 12 tarjetas DOM.
- Validación productiva: Solo con autorización explícita, backup, despliegue mínimo, purga de caché, comparación HTML/DOM/imágenes y nuevas mediciones de TTFB y carga visual.

## Riesgos

- Duplicar reglas de presentación entre PHP y JavaScript puede causar diferencias entre tarjeta inicial y dinámica; debe existir un único modelo público compartido.
- Un JSON demasiado amplio reduciría poco el peso transferido aunque mejore el DOM.
- Un error de hidratación podría dejar filtros o paginación sin acceso a resultados posteriores.
- La optimización de LiteSpeed podría modificar carga de imágenes; debe verificarse en navegador después de despliegue.

## Decisiones pendientes

- Ninguna.
