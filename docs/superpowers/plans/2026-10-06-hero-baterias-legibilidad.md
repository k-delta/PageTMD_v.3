# Legibilidad y apoyos del hero de baterías — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Alejar y alinear a la derecha la imagen del hero de baterías, mantener el navy oscuro a la izquierda e incluir tres apoyos visibles y accesibles debajo de su párrafo.

**Architecture:** Añadir las tres unidades de texto e iconos decorativos al HTML generado por `tmd_commercial_landing_script_battery_content()`. Estilizar el encuadre y los apoyos en el CSS compartido, bajo el selector exclusivo de la maqueta de baterías. La solicitud actual autoriza desplegar código/CSS y persistir el contenido generado de la página 1559 y el formulario CF7 1557 mediante el modo existente.

**Tech Stack:** PHP, CSS, prueba PHP focal existente y navegador Chromium.

**Spec:** `docs/specs/2026-10-06-hero-baterias-legibilidad.md`

## Global Constraints

- No cambiar el H1, el subtítulo, el párrafo actual, la imagen fuente, su texto alternativo o cualquier otra sección de la página.
- Los iconos son decorativos para lectores de pantalla; el texto transmite cada mensaje.
- La solicitud autoriza el despliegue de código/CSS y la actualización de página 1559 y CF7 1557 tras sus gates de contenido.
- Los apoyos aparecen en el orden fijado por el SPEC: rayo y “Alto rendimiento para jornadas exigentes”; engranaje y “Equipos confiables y de larga vida útil”; reloj y “Asesoría especializada según tu operación”.
- El área izquierda usa `#262E4F`; los iconos circulares tienen trazo rojo/anaranjado.

## Review Focus

- El encuadre alejado debe mostrar la fotografía completa sin ampliarla; comprobar su alineación a la derecha y el navy izquierdo a 1440 px, 1024 px y 390 px.
- La fila de tres apoyos debe caber dentro del ancho de texto en escritorio sin reducir las frases a líneas demasiado estrechas.
- Los apoyos deben apilarse en móvil y permanecer dentro del ancho visible.
- Cada SVG debe quedar oculto a tecnologías de asistencia mientras su frase siga expuesta como texto HTML.
- Todos los selectores nuevos deben limitarse a `.tmd-commercial-landing__hero--battery-maqueta` para no cambiar el hero anterior ni otras páginas.

---

### Task 1: Generar el marcado accesible de los apoyos

**Files:**
- Modify: `scripts/create-commercial-landing-pages.php`, función `tmd_commercial_landing_script_battery_content()`.
- Test: `tests/test-commercial-landing-battery-maqueta.php`.

**Interfaces:**
- Consumes: `tmd_commercial_landing_script_page_content('battery', 1557)`.
- Produces: un hero con una lista de tres elementos después del párrafo, cada uno con SVG decorativo y la frase HTML correspondiente.

- [x] **Step 1: Añadir aserciones focales del hero.** Comprobar cantidad y orden de frases, relación rayo/engranaje/reloj, `aria-hidden="true"` de los SVG, selectores específicos de la variante de baterías y grilla de tres columnas en escritorio/una en móvil; conservar H1, subtítulo, párrafo, fuente y alternativa de imagen.
- [x] **Step 2: Ejecutar la prueba para confirmar que falla por los apoyos y estilos aún ausentes.** La prueba mostró primero que faltaba la lista semántica de apoyos; la prueba visual dedicada confirmó después el faltante del acento y del CSS antes de implementarlos.

```bash
php tests/test-commercial-landing-battery-visual-specs.php
```

Expected: RED ante el marcado o los estilos aún ausentes.

- [x] **Step 3: Agregar a `tmd_commercial_landing_script_battery_content()` una lista semántica de tres elementos con iconos lineales SVG decorativos y las frases exactas del SPEC.**
- [x] **Step 4: Repetir `php tests/test-commercial-landing-battery-visual-specs.php`.** Resultado: `OK`.

### Task 2: Estilizar encuadre, contraste y apoyos responsivos

**Files:**
- Modify: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`.

**Interfaces:**
- Consumes: las clases del marcado incorporado en Task 1.
- Produces: imagen alejada, completa y alineada a la derecha; zona izquierda navy legible; tres apoyos alineados en escritorio y apilados en móvil.

- [x] **Step 1: Ajustar el encuadre del selector `.tmd-commercial-landing__hero--battery-maqueta` para alejar la imagen, mostrarla completa y alinearla a la derecha (`width: 90%`, `object-fit: contain`, `object-position: right center`, sin transformación); en móvil ocupar el ancho disponible y conservar el encuadre completo.**
- [x] **Step 2: Estilizar `.tmd-commercial-landing__hero-supports` como una cuadrícula de tres columnas en escritorio, con cada icono circular a la izquierda de su texto, trazo `#F04D2D` y contraste legible sobre navy.**
- [x] **Step 3: En `max-width: 760px`, convertir `.tmd-commercial-landing__hero-supports` en una columna, conservar icono junto a su frase y limitar el texto al ancho disponible.**
- [x] **Step 4: Ejecutar los lint de PHP, `php tests/test-commercial-landing-battery-visual-specs.php` y `git diff --check`.** Resultado: los lint, la prueba visual dedicada, la prueba integral y `git diff --check` pasan.
- [x] **Step 5: Revisar el hero generado en Chromium a 1440, 1024 y 390 px de ancho.** Resultado: la batería sigue reconocible hacia la derecha, el navy cubre el lado izquierdo, los tres apoyos se leen y el texto e imagen originales se conservan. Para móvil se limitó el layout del fixture a 390 px porque Chromium headless impone un viewport CSS mínimo de 500 px.

## Límite de ejecución

El usuario autorizó el despliegue y la actualización del contenido de página 1559 y CF7 1557. Completar backup/recibo, dry-run, hashes, revisión, manifiestos y verificación productiva según `safe-deploy` y los runbooks.

**Ruling de validación:** los contratos visuales se comprueban con `tests/test-commercial-landing-battery-visual-specs.php`; la regresión integral DEC-11 también pasa tras ajustar su aserción de galería a los activos Barbillon confirmados en DEC-12. No se modificó contenido visible de la galería.
