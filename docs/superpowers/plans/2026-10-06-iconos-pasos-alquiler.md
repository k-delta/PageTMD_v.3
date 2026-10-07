# Iconos en los pasos del proceso de alquiler — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Mostrar un icono lineal decorativo encima de cada número del proceso de alquiler, manteniendo la composición y el contenido actuales.

**Architecture:** El renderer añadirá un SVG inline por paso y un contenedor para el número generado por contador CSS. El CSS, limitado a la sección del proceso, ordenará icono, número, título y descripción; la prueba focal verificará el marcado, el contador y las tres distribuciones responsive.

**Tech Stack:** PHP, CSS, SVG inline y prueba focal PHP existente.

**Spec:** `docs/specs/2026-10-06-iconos-pasos-alquiler.md`

## Global Constraints

- Mostrar un icono encima de cada número 01, 02, 03 y 04, asociado visualmente al mismo paso.
- Conservar los cuatro pasos, sus textos y su orden actual.
- Conservar la composición de cuatro columnas desde 1200 px, dos columnas entre 761 y 1199 px y una columna hasta 760 px.
- Mantener iconos decorativos integrados en el lenguaje SVG inline de la landing.
- No cambiar números, títulos, descripciones, encabezado, subtítulo, imagen ni texto alternativo.
- No añadir interacciones, dependencias ni solicitudes externas.

## Review Focus

- El icono de cada paso debe preceder visualmente al número y corresponder al texto de ese paso. La prueba comprueba el orden DOM y la asociación por clase semántica.
- El contador CSS debe seguir produciendo 01–04 después de mover su pseudo-elemento a un span dedicado. La prueba comprueba el selector y la declaración del contador; Chromium confirma el resultado visible.
- Cada SVG debe ser decorativo y no enfocable. La prueba comprueba `aria-hidden="true"` en el wrapper y `focusable="false"` en el SVG.
- Los selectores nuevos deben estar limitados al bloque `tmd-rental-v2__process`. La prueba comprueba las reglas CSS y las grillas actuales.
- Los textos, la fotografía, el orden de lectura móvil y la ausencia de overflow deben conservarse. La prueba focal y la revisión visual comprueban estos casos.

### Task 1: Añadir iconos a los cuatro pasos

**Files:**
- Modify: `scripts/commercial-landing-rental-v2.php:80-84`
- Modify: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css:575-601`
- Test: `tests/test-commercial-landing-rental-v2-render.php`

**Interfaces:**
- Consumes: La lista ordenada actual y sus cuatro títulos/descripciones del SPEC aprobado.
- Produces: En cada `li`, un wrapper `.tmd-rental-v2__process-icon` específico del paso, seguido por `.tmd-rental-v2__process-number`, el `strong` existente y la descripción existente. Los números siguen viniendo de `counter(rental-step, decimal-leading-zero)`.

- [ ] **Step 1: Añadir aserciones de regresión para los iconos y el orden.** En la prueba focal, comprobar cuatro SVG inline en el orden `operation`, `recommendation`, `quote`, `delivery`; cada wrapper debe ser decorativo, preceder al número y mantener el título/descripción existentes. Añadir aserciones CSS para icono y número en amarillo `#ffc33c` sobre fondo `#262e4f`, tamaño de icono 36×36 px y el contador en `.tmd-rental-v2__process-number::before`.
- [ ] **Step 2: Confirmar que la prueba falla antes de implementar.** Ejecutar `php tests/test-commercial-landing-rental-v2-render.php`. Resultado esperado: falla la aserción nueva por ausencia de los cuatro iconos y del selector de contador actualizado.
- [ ] **Step 3: Actualizar el marcado del renderer.** En cada `li`, insertar primero el SVG inline con `aria-hidden="true"` en el wrapper y `focusable="false"` en el SVG; usar las metáforas aprobadas para datos, recomendación técnica, cotización y entrega. Insertar luego un span vacío `.tmd-rental-v2__process-number` para alojar el contador CSS; conservar literalmente `strong` y la descripción.
- [ ] **Step 4: Estilizar la secuencia visual en CSS.** Mantener el reinicio e incremento del contador en la lista y sus `li`; mover únicamente la regla visual `li::before` al pseudo-elemento `.tmd-rental-v2__process-number::before`. Mostrar el SVG a 36×36 px y el número en amarillo `#FFC33C` sobre fondo `#262E4F`, con separación breve y los títulos, descripciones y breakpoints existentes.
- [ ] **Step 5: Ejecutar las validaciones focales.** Correr `php -l scripts/commercial-landing-rental-v2.php`, `php -l tests/test-commercial-landing-rental-v2-render.php`, `php tests/test-commercial-landing-rental-v2-render.php` y `git diff --check`. Resultado esperado: lint sin errores, prueba focal aprobada y sin errores de whitespace.
- [ ] **Step 6: Revisar el resultado en Chromium local.** A 1440×900, 1024×768 y 390×844, comprobar icono sobre número, 4/2/1 columnas, legibilidad, orden textual antes de la fotografía en móvil y ausencia de overflow horizontal.

## Ejecución y entrega

- Usar el método de ejecución que el usuario elija y mantener el aislamiento exigido por `executing-plans`.
- Después de la revisión del cambio, pasar al Skill `safe-deploy` con el manifiesto exacto y los gates productivos. La solicitud original señala la página pública `https://tecnimontacargas.com/alquiler-montacargas-electricos/`.
- Confirmar en producción la respuesta HTTP, CSS servido, iconos y distribución responsive antes de declarar terminado.
