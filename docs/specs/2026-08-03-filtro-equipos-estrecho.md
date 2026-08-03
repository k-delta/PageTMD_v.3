# SPEC: Igualar el ancho del filtro de Equipos al de Energía

## Estado

- Aprobado

## Contexto

- [Solicitud] Las capturas de `/equipos/` y `/energia/` muestran que el filtro de equipos ocupa más ancho que el filtro de baterías.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-catalog.css:68] El catálogo de equipos define una columna de filtros de `280px` y cambia a una columna a partir de `1060px`.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-energy-catalog.css:563] El catálogo de Energía fuerza una columna de filtros de `220px` y cambia a una columna a partir de `1180px`.

## Problema

- [Solicitud] El filtro ancho de `/equipos/` reduce el espacio disponible para las tarjetas y no mantiene coherencia visual ni adaptable con `/energia/`.

## Objetivo

- [Solicitud] Hacer que el filtro de `/equipos/` tenga el mismo ancho y comportamiento adaptable que el filtro de baterías en `/energia/`.

## Fuera del alcance

- [Solicitud] No cambiar campos, opciones, textos, botones ni funcionamiento de filtros.
- [Solicitud] No modificar tarjetas, datos, orden, paginación ni fichas del catálogo.
- [Solicitud] No cambiar la composición visual de `/energia/`.
- [Regla: AGENTS.md] No actualizar WordPress, Blocksy, plugins ni dependencias.
- [Regla: AGENTS.md] No desplegar, hacer commit ni push sin autorización explícita para esa acción.

## Requisitos funcionales

1. [Solicitud] En escritorio, la columna y la caja del filtro de `/equipos/` deben ocupar el mismo ancho visual que sus equivalentes en `/energia/`.
2. [Solicitud] El espacio liberado debe quedar disponible para el listado de equipos.
3. [Solicitud] El filtro de equipos debe cambiar a disposición de ancho completo en el mismo punto adaptable utilizado por el catálogo de baterías.
4. [Solicitud] En tableta y móvil, filtro y tarjetas deben conservar una disposición legible, sin desbordamiento ni solapamiento.
5. [Solicitud] Filtros, tarjetas y paginación deben mantener su comportamiento actual.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Los registros, estados y disponibilidad deben seguir procediendo de Inventario/Firebase.
- [Regla: AGENTS.md] El cambio debe limitarse a la presentación y no alterar datos reales del catálogo.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- [Solicitud] Etiquetas y valores largos de los filtros no deben salirse de la caja estrecha.
- [Solicitud] El filtro debe conservar su comportamiento fijo actual cuando exista espacio de escritorio suficiente.
- [Solicitud] Con filtros activos, sin resultados y durante la paginación, la cuadrícula no debe cambiar de ancho inesperadamente.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-catalog.css`
- `wp-content/themes/blocksy-child/assets/css/tmd-energy-catalog.css` como referencia visual; no se prevé modificarlo.
- `wp-content/themes/blocksy-child/assets/css/tmd-inventory-api.css` como estilo compartido de controles y resultados; no se prevé modificarlo.

## Criterios de aceptación

1. [Solicitud] En un mismo ancho de escritorio, los filtros de `/equipos/` y `/energia/` miden visualmente lo mismo.
2. [Solicitud] `/equipos/` aprovecha el ancho restante para sus tarjetas sin recortar contenido ni producir desbordamiento horizontal.
3. [Solicitud] Ambos catálogos pasan de filtro lateral a filtro de ancho completo bajo el mismo comportamiento adaptable.
4. [Solicitud] Marca, categoría, subcategoría, alturas, condición, operario y reach siguen filtrando como antes.
5. [Solicitud] Las vistas de escritorio, tableta y móvil no presentan solapamientos, huecos anómalos ni controles truncados.

## Validación

- Pruebas unitarias: No aplica para este ajuste CSS aislado.
- Pruebas de integración: Comprobar filtros, estado sin resultados, paginación y apertura de ficha antes y después del ajuste.
- Validación manual: Comparar `/equipos/` y `/energia/` en el mismo viewport; medir ambas cajas y revisar escritorio, tableta y móvil.
- Validación productiva: Solo con autorización explícita, después de backup, despliegue mínimo, purga de caché y comprobación en navegador.

## Riesgos

- Textos largos pueden requerir salto de línea dentro de una columna más estrecha.
- Igualar el punto de cambio adaptable puede reducir temporalmente la cantidad de columnas visibles en anchos intermedios.
- Reglas editoriales duplicadas en la hoja de estilos pueden sobrescribir el ajuste si el selector no queda limitado al frontend de `/equipos/`.

## Decisiones pendientes

- Ninguna.
