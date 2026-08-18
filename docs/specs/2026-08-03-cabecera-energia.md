# SPEC: Contenido visible antes del catálogo de baterías en Energía

## Estado

- Terminado

## Contexto

- [Solicitud] En `https://tecnimontacargas.com/energia/` se percibe una cabecera doble y se solicita retirarla para dejar el catálogo de baterías.
- [Evidencia: https://tecnimontacargas.com/energia/] La respuesta HTML vigente muestra primero el título de página `Energia` y luego el encabezado editorial `Baterías, cargadores y monitoreo para montacargas eléctricos`, antes de los filtros y tarjetas del catálogo.
- [Regla: AGENTS.md] El inventario/Firebase sigue siendo la fuente canónica de los equipos y WordPress solo presenta sus datos.

## Problema

- [Solicitud] Dos encabezados consecutivos ocupan el inicio de `/energia/` y deterioran la presentación visual esperada.

## Objetivo

- [Solicitud] Hacer que `/energia/` deje de mostrar ambas cabeceras y conserve el catálogo de baterías funcional.

## Fuera del alcance

- [Solicitud] No modificar datos, disponibilidad, filtros, tarjetas ni fichas provenientes del inventario.
- [Regla: AGENTS.md] No actualizar WordPress, Blocksy, plugins ni dependencias.
- [Solicitud] No cambiar otras rutas de Energía.
- [Regla: AGENTS.md] No desplegar, escribir en producción, hacer commit ni push sin autorización explícita para esa acción.

## Requisitos funcionales

1. [Solicitud] `/energia/` no debe mostrar el título de página `Energia` que actualmente genera la cabecera del tema.
2. [Solicitud] `/energia/` no debe mostrar el bloque editorial encabezado por `Baterías, cargadores y monitoreo para montacargas eléctricos`.
3. [Solicitud] El catálogo de baterías debe conservar filtros, resultados, paginación y enlaces existentes sin cambios funcionales.
4. [Solicitud] Retirar también los bloques promocionales BMS y CTA final para que el contenido administrado conserve únicamente el catálogo.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Las baterías publicadas, su disponibilidad y sus datos deben seguir procediendo de Inventario/Firebase.
- [Regla: AGENTS.md] No inventar equipos, marcas, modelos, imágenes, precios, disponibilidad ni hechos comerciales.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- [Solicitud] La eliminación de cabeceras debe mantenerse cuando `/energia/` se visita con filtros o con paginación.
- [Solicitud] La vista de ficha mediante parámetros del catálogo debe conservar su funcionamiento actual.
- [Regla: AGENTS.md] Escritorio y móvil deben evitar espacios vacíos residuales donde estaban las cabeceras.

## Archivos o módulos relacionados

- Contenido administrado de la página WordPress `/energia/` (ID observado en HTML vigente: `63`).
- Configuración de título de página de Blocksy para `/energia/`.
- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php` como consumidor del catálogo; no se prevén cambios en su contrato.

## Criterios de aceptación

1. [Solicitud] En escritorio y móvil, `/energia/` no presenta ninguno de los dos encabezados identificados.
2. [Solicitud] El catálogo de baterías continúa visible y permite filtrar, paginar, abrir fichas y acceder a cotización.
3. [Solicitud] No queda un espacio vacío atribuible a las cabeceras retiradas.
4. [Regla: docs/domain/INVENTORY.md] El cambio no altera registros ni respuestas de Inventario/Firebase.
5. [Solicitud] No permanecen bloques promocionales BMS ni CTA final fuera del catálogo.

## Validación

- Pruebas unitarias: No aplica para contenido administrado; si la implementación requiere código, añadir una prueba focalizada que demuestre la condición exclusiva de `/energia/`.
- Pruebas de integración: Verificar que filtros, paginación, ficha y cotización del catálogo mantengan sus URLs y comportamiento.
- Validación manual: Revisar `/energia/` en escritorio y móvil, incluyendo estado sin filtros y con filtros activos; confirmar ausencia de ambos encabezados y de huecos visuales.
- Validación productiva: Solo con autorización explícita, backup verificado, purga de caché y comprobación HTTP/navegador posterior.

## Riesgos

- Retirar ambos H1 puede dejar la página sin un encabezado principal semántico.
- Una regla global del tema podría ocultar títulos de páginas distintas si no se limita a `/energia/`.
- Editar contenido administrado sin backup puede dificultar restaurar la composición anterior.

## Decisiones

- `DEC-01` — Resuelta: “solo deja el catálogo de baterías y ya” significa retirar el título del tema, el hero editorial, la promoción BMS y el CTA final; permanecen exclusivamente filtros, resultados, paginación y enlaces propios del catálogo.

## Evidencia de cierre

- Producción, 2026-08-03: `/energia/` respondió HTTP 200 y el contenido administrado de la página 63 quedó reducido al bloque `tmde-section` que conserva `[tmd_energy_filters]` y `[tmd_energy_grid]`.
- Chrome en escritorio y móvil: `.hero-section` quedó con `display:none` y altura `0`; no aparecen `tmde-hero`, `tmde-bms-promo` ni `tmde-cta`, y no existe overflow horizontal a 390 px.
- Flujo del catálogo: 84 baterías, 7 páginas, máximo 12 tarjetas visibles; página 2, filtro BARBILLON, limpieza, ficha y cotización verificados.
- Validaciones: pruebas PHP y JavaScript completas, revisión de base de datos/rendimiento/pruebas sin hallazgos `Critical` o `Important`, logs sin errores y `sync-production.sh --check` exitoso.
- Rollback: backup verificado de base de datos, contenido original de la página 63 y CSS anterior en `/opt/tecnimontacargas/backups/20260803-145153-energy-catalog-only/`.
