# SPEC: Alineación y énfasis del encabezado de soluciones de baterías

## Estado

- Terminado — 2026-10-06. Código y CSS desplegados en producción con el commit `09d90ed8f855000664972a7ec900acb42216800d`.
- La página 1559 y CF7 1557 se verificaron con el dry-run productivo de `update-battery-maqueta`; sus huellas actuales ya coincidían con el destino y no hizo falta escribir en la base de datos.

## Contexto

- [Solicitud] La captura muestra el encabezado de la sección de soluciones de la página `/baterias-para-montacargas/`. Se solicita alinear el título y el texto de compatibilidad hacia la izquierda, y destacar en amarillo “Baterías de tracción”.
- [Evidencia: `scripts/create-commercial-landing-pages.php:358-363`] El H2 actual es “Baterías de tracción, cargadores y monitoreo BMS” y el H3 actual es “Compatibles con retráctiles, apiladores, estibadores, tomapedidos y equipos de pasillo angosto”.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:294-303,1112-1145`] La sección usa fondo blanco y el contenedor del encabezado divide H2 y H3 en dos columnas; ambos heredan la alineación centrada general.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:20-24`] El amarillo de marca actual es `#FFC33C`.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:20-24`] El amarillo de marca `#FFC33C` sobre el fondo blanco actual tiene contraste aproximado de 1.60:1; el azul de marca `#128CEB` tiene 3.51:1.
- [Decisión del usuario: 2026-10-06] Usar el azul de marca cuando el amarillo no tenga buen contraste.

## Problema

- [Solicitud] El título y el texto de compatibilidad se ven centrados, y el título no destaca visualmente la frase indicada.

## Objetivo

- [Solicitud] Alinear a la izquierda los dos textos del encabezado de soluciones y destacar únicamente “Baterías de tracción” con un color de marca legible.

## Fuera del alcance

- [Solicitud] Cambiar los textos del H2 o del H3, las tarjetas de soluciones, otros bloques de la página o el hero.
- [Solicitud] Cambiar la estructura de dos columnas en escritorio o el orden responsive actual.

## Requisitos funcionales

1. [Solicitud] Alinear a la izquierda el H2 de la sección de soluciones, sin centrarlo.
2. [Solicitud] Destacar únicamente las palabras “Baterías de tracción” con el color de marca que conserve buen contraste; como el amarillo no contrasta lo suficiente sobre el fondo blanco actual, usar el azul de marca. La coma posterior y el resto del H2 conservan el color actual.
3. [Solicitud] Alinear a la izquierda el H3 “Compatibles con retráctiles, apiladores, estibadores, tomapedidos y equipos de pasillo angosto”, sin cambiar su redacción.
4. [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1710-1715,1733-1746`] En móvil se mantiene el apilado actual y ambos textos permanecen alineados a la izquierda y legibles.

## Reglas de negocio

- [Evidencia: `docs/specs/2026-10-04-landing-montacargas-baterias.md:12,186-189`] El cambio se limita a presentación y mantiene los textos comerciales vigentes.

## Contratos

### Entrada

No aplica; no cambian datos ni interacciones.

### Salida

No aplica; no cambian datos ni interacciones.

## Casos límite

- [Decisión del usuario: 2026-10-06] Si el título ocupa varias líneas, solo “Baterías de tracción” conserva el azul de marca definido por contraste; la coma y las palabras posteriores no se resaltan.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1710-1715`] En móvil el H2 y el H3 se apilan sin overflow horizontal.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`
- `scripts/create-commercial-landing-pages.php` (referencia del marcado y texto actuales)
- Página WordPress 1559 (`/baterias-para-montacargas/`), solo como referencia del contenido vigente.

## Criterios de aceptación

1. [Solicitud] El H2 y el H3 quedan alineados a la izquierda, sin centrar, en escritorio y móvil.
2. [Decisión del usuario: 2026-10-06] Solo “Baterías de tracción” aparece en azul de marca por contraste; el resto del H2 y el H3 conserva su texto y color actuales.
3. [Solicitud] La frase resaltada permite leerse claramente sobre su fondo y conserva el resto del título sin énfasis.
4. [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1112-1145,1710-1715`] La estructura responsive actual se conserva y no hay overflow horizontal.

## Validación

- Prueba focal del contrato visual: `php tests/test-commercial-landing-battery-visual-specs.php`; confirma el marcado exacto, la alineación limitada a soluciones y el uso del azul de marca.
- Pruebas de integración: No aplica; no cambian datos ni interacciones.
- Validación manual: Revisar en navegador escritorio (1440 px), tablet (1024 px) y móvil (390 px) la alineación, el acento azul de marca, el contraste, la legibilidad y el ajuste de líneas.
- [Verificado localmente — 2026-10-06] `php -l` del generador y ambas pruebas focales pasan; Chromium local revisó escritorio, tablet y móvil.
- [Verificado en producción — 2026-10-06] El checkout productivo corresponde al commit `09d90ed8f855000664972a7ec900acb42216800d`; SHA-256 del CSS: `c7068f07f542309b11c0629e8d4938a185749ce681dc2b40370bb31a2aac2e79`.
- [Verificado en producción — 2026-10-06] `/baterias-para-montacargas/` respondió HTTP 200. Chromium verificó la alineación izquierda, el énfasis azul y ausencia de overflow a 1440×900 y 390×844; la revisión local también cubrió 1024 px.
- [Verificado en producción — 2026-10-06] El dry-run confirmó página 1559 actual/destino `736e568a8a347b23c37c0a7a244db332339406e4c1d623f92fc47577b62b5e98` y CF7 1557 actual/destino `acc47f51da1372fbb1a00a00ae824960b161c1076e6701c6f4ccdc41ea29477a`. No se realizó escritura porque ambos ya contenían los valores destino.

- [Verificado localmente — 2026-10-06] Las pruebas focales `php tests/test-commercial-landing-battery-visual-specs.php` y `php tests/test-commercial-landing-battery-maqueta.php`, los lint PHP focales y `git diff --check` pasan. Chromium revisó el encabezado a 1440×900, 1024×768 y con un layout local limitado a 390 px.
- [Límite de sincronización] El control completo encontró únicamente el snapshot de auditoría de la página 1559 desactualizado frente al contenido productivo ya verificado; no reportó diferencias de código, plugins ni Compose. Un segundo control en el worktree aislado se interrumpió tras 210 segundos sin salida; no se afirma que ese comando terminara con código 0.

## Riesgos

- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:20-24,1112-1135`] El amarillo de marca tiene contraste insuficiente en el fondo blanco actual; según la decisión del usuario, el acento de este título usa azul de marca `#128CEB`.

## Decisiones pendientes

- DEC-01 resuelto — 2026-10-06: `#FFC33C` sobre blanco tiene contraste 1.60:1, por lo que se usa `#128CEB` sobre blanco (3.51:1), conforme a la instrucción del usuario.
- Sin decisiones funcionales abiertas.
