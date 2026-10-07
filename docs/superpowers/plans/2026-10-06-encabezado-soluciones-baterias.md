# Alineación y énfasis del encabezado de soluciones de baterías — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Alinear a la izquierda el H2 y el H3 de soluciones de baterías, y destacar solo “Baterías de tracción” con el azul de marca que sí contrasta con el fondo blanco.

**Architecture:** Envolver el fragmento exacto del H2 en un `span` dedicado en el marcado generado por la landing. Aplicar alineación y color mediante selectores limitados a `.tmd-commercial-landing__section--battery-solutions`; mantener la cuadrícula de cards y el orden responsive actual.

**Tech Stack:** PHP, CSS y prueba focal PHP existente.

**Spec:** `docs/specs/2026-10-06-encabezado-soluciones-baterias.md`

## Global Constraints

- Conservar el texto del H2 “Baterías de tracción, cargadores y monitoreo BMS” y del H3 “Compatibles con retráctiles, apiladores, estibadores, tomapedidos y equipos de pasillo angosto”.
- Alinear a la izquierda los dos textos del encabezado de soluciones, sin centrar.
- Destacar únicamente “Baterías de tracción”; la coma posterior y el resto del título mantienen el color navy.
- Usar `#128CEB` porque `#FFC33C` sobre blanco tiene contraste 1.60:1 y el azul sobre blanco 3.51:1.
- No cambiar cards ni otras secciones fuera del alcance visible de la landing.
- La solicitud actual autoriza desplegar el código/CSS y persistir el contenido generado de la página 1559 junto con CF7 1557 mediante el modo existente.

## Review Focus

- Los selectores nuevos deben quedar limitados a la sección de soluciones; la cabecera del blog, proceso y demás `section-heading--split` no deben cambiar.
- La coma que sigue a “Baterías de tracción” no debe quedar dentro del fragmento azul.
- El texto visible H2/H3 debe permanecer literal y en el mismo orden aunque el H2 añada un `span`.
- La alineación debe seguir a la izquierda cuando la cuadrícula se apile en móvil.
- No debe aparecer overflow horizontal ni cambiar el ajuste de las tres tarjetas.

---

### Task 1: Añadir prueba de marcado y estilos del encabezado

**Files:**
- Modify: `tests/test-commercial-landing-battery-visual-specs.php`.
- Modify: `scripts/create-commercial-landing-pages.php`.
- Modify: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`.

**Interfaces:**
- Consumes: `tmd_commercial_landing_script_page_content('battery', 1557)`.
- Produces: H2 con `<span class="tmd-commercial-landing__solutions-title-accent">Baterías de tracción</span>` y H3 actual, ambos alineados a la izquierda por estilos exclusivos de la sección de soluciones.

- [x] **Step 1: Add focal assertions for the generated heading.** Check the exact accent span, the unchanged H3 copy, and CSS rules scoped to `.tmd-commercial-landing__section--battery-solutions` for `text-align: left` and `color: var(--tmd-landing-blue)`.
- [x] **Step 2: Run the test and verify it fails for the missing span, then for the absent image and heading styles.**

```bash
php tests/test-commercial-landing-battery-visual-specs.php
```

Expected: RED for the missing accent markup and scoped style rules.

- [x] **Step 3: Wrap only `Baterías de tracción` in the dedicated span in `scripts/create-commercial-landing-pages.php`; keep the comma outside it and leave the H3 text unchanged.**
- [x] **Step 4: Add section-scoped left alignment and blue accent rules in `tmd-commercial-landings.css`; do not change shared heading selectors.**
- [x] **Step 5: Run `php tests/test-commercial-landing-battery-visual-specs.php` and confirm the focal assertions pass.** Resultado: `OK`.

### Task 2: Validate responsive appearance and preservation

**Files:**
- Inspect: `scripts/create-commercial-landing-pages.php`.
- Inspect: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`.

**Interfaces:**
- Consumes: generated battery solutions heading and its scoped styles from Task 1.
- Produces: confirmed alignment, color contrast, unchanged copy, and no responsive overflow at the SPEC viewports.

- [x] **Step 1: Run PHP lint on the generator and both visual test files.** Resultado: sin errores de sintaxis.
- [x] **Step 2: Run `git diff --check`.** Resultado: sin errores de whitespace.
- [x] **Step 3: Review the rendered section at 1440 px, 1024 px, and 390 px.** Resultado: H2/H3 alineados a la izquierda; solo “Baterías de tracción” azul; resto del copy conserva sus colores; cards y apilado responsive se conservan.

## Límite de ejecución

El usuario autorizó el despliegue y la actualización de la página 1559 y CF7 1557. Ejecutar `update-battery-maqueta` solo después de completar backup, hashes, dry-run, manifiesto y verificaciones productivas.

La prueba integral DEC-11 `tests/test-commercial-landing-battery-maqueta.php` y el contrato visual `tests/test-commercial-landing-battery-visual-specs.php` pasan. La aserción previa de galería se alineó con DEC-12: permite atribuir Barbillon únicamente a los dos activos de batería cuyo archivo confirma esa referencia.
