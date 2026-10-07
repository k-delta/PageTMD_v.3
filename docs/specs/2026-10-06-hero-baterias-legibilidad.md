# SPEC: Legibilidad y apoyos del hero de baterías

## Estado

- Terminado — 2026-10-06. Código y CSS desplegados en producción con el commit `09d90ed8f855000664972a7ec900acb42216800d`.
- La verificación productiva de `update-battery-maqueta` confirmó que las huellas actuales y destino de página 1559 y CF7 1557 ya coincidían; no hizo falta escribir en la base de datos.
- La revisión final de sincronización clasificó una diferencia de snapshot correspondiente al contenido ya persistido de la página 1559; el detalle está registrado en Validación.

## Contexto

- [Solicitud] La captura de `/baterias-para-montacargas/` muestra el hero actual. Se solicita alejar la imagen (sin ampliarla), alinearla a la derecha y dejar el azul bien oscuro en el lado izquierdo.
- [Solicitud] La referencia visual adicional pide colocar, inmediatamente debajo del texto actual del hero, tres elementos con iconos y las frases “Alto rendimiento para jornadas exigentes”, “Equipos confiables y de larga vida útil” y “Asesoría especializada según tu operación”.
- [Evidencia: `docs/specs/2026-10-04-landing-montacargas-baterias.md:7-8`] La página 1559 ya tiene una dirección visual aprobada y una imagen de batería asignada al banner; esta tarea solo complementa la presentación de ese hero.
- [Evidencia: `docs/specs/2026-10-04-landing-montacargas-baterias.md:12,75,189`] La especificación anterior omitía iconos y apoyos en el banner. La solicitud actual reemplaza esa exclusión únicamente para estos tres apoyos del hero de baterías.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1041-1052`] El hero tiene fondo navy `#262E4F` y un degradado navy encima de la fotografía; el encuadre debe mostrar más área de la foto, con la imagen reducida y alineada a la derecha.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1688-1699`] En pantallas de hasta 760 px el hero conserva una composición vertical con degradado inferior y el texto en la zona baja.
- [Evidencia: `scripts/create-commercial-landing-pages.php:345-355`] El generador canónico del hero contiene el H1, el subtítulo y el párrafo, pero todavía no genera los tres apoyos. Para que sus frases sean contenido legible y semántico, el cambio local incluye el HTML del generador además del CSS.

## Problema

- [Solicitud] La composición actual no satisface el encuadre solicitado: la imagen debe alejarse para mostrar más del encuadre original, quedar a la derecha y dejar el lado izquierdo azul navy oscuro detrás del contenido.

## Objetivo

- [Solicitud] Alejar y alinear a la derecha la imagen, mantener el navy oscuro a la izquierda y añadir debajo del texto actual los tres apoyos mostrados en la referencia.

## Fuera del alcance

- [Solicitud] Cambiar el H1, el subtítulo, el párrafo actual, la imagen fuente, su texto alternativo o cualquier otra sección de la página.
- [Solicitud] Añadir un CTA o modificar la oferta comercial.
- [Solicitud] Cambiar la navegación global, colores de otras páginas, contenido de otras secciones o datos del formulario persistidos en WordPress.

## Requisitos funcionales

1. [Solicitud] En escritorio, alejar la imagen para mostrar completa la fotografía del hero; mantenerla alineada a la derecha, sin ampliar ni recortar la fuente.
2. [Solicitud] El área izquierda detrás del texto debe verse en azul navy oscuro, usando el navy existente `#262E4F`, y conservar una transición que permita reconocer la fotografía hacia la derecha.
3. [Solicitud] Inmediatamente debajo del párrafo actual, mostrar en el orden de la referencia tres apoyos con icono y texto: rayo con “Alto rendimiento para jornadas exigentes”; engranaje con “Equipos confiables y de larga vida útil”; reloj con “Asesoría especializada según tu operación”.
4. [Solicitud] Presentar los apoyos como tres unidades alineadas en escritorio, cada una con icono circular de trazo rojo/anaranjado y su texto a la derecha. Los iconos son decorativos para lectores de pantalla; el texto transmite cada mensaje.
5. [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1688-1699`] En móvil se conserva la composición vertical existente y se mantienen legibles el texto y los tres apoyos, sin overflow horizontal.
6. [Evidencia: `docs/specs/2026-10-04-landing-montacargas-baterias.md:35-42`; `scripts/create-commercial-landing-pages.php:345-346`] Se conservan la fotografía actual del banner, su alternativa accesible, el H1, el subtítulo y el párrafo actuales.
7. [Solicitud] En móvil, la fotografía se ve completa, usa el ancho disponible y queda alineada arriba a la derecha.

## Reglas de negocio

- [Evidencia: `docs/specs/2026-10-04-landing-montacargas-baterias.md:37-42`] El cambio es de presentación; no altera la oferta ni atribuye a la imagen afirmaciones de disponibilidad o compatibilidad.

## Contratos

### Entrada

No aplica; no cambian datos ni interacciones.

### Salida

No aplica; no cambian datos ni interacciones.

## Casos límite

- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1041-1056`] El desplazamiento no debe cubrir el título, el subtítulo ni el texto del hero.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1688-1699`] En móvil deben seguir visibles la batería y el contenido, sin desbordamiento horizontal.
- [Solicitud] La fila de apoyos debe poder envolver o apilarse en anchos estrechos sin separar un icono de su frase ni hacer ilegibles los textos.

## Archivos o módulos relacionados

- `scripts/create-commercial-landing-pages.php` — HTML fuente del hero de baterías.
- `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`
- `tests/test-commercial-landing-battery-maqueta.php`
- `docs/specs/2026-10-04-landing-montacargas-baterias.md`
- Página WordPress 1559 (`/baterias-para-montacargas/`), solo como referencia de contenido existente.

## Criterios de aceptación

1. [Solicitud] En escritorio, la fotografía aparece alejada y completa, alineada a la derecha; el contenido queda sobre una zona izquierda de navy oscuro `#262E4F`.
2. [Solicitud] Debajo del párrafo se muestran los tres apoyos en el orden y con las frases indicadas; cada icono corresponde a su frase y el conjunto conserva el estilo de la referencia.
3. [Solicitud] El H1, subtítulo y párrafo actuales conservan su legibilidad y no se superponen con el foco de la imagen ni con los apoyos.
4. [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css:1688-1699`] En móvil se mantiene la composición vertical; la batería sigue reconocible, los apoyos se reacomodan sin overflow horizontal y los textos permanecen legibles.
5. [Evidencia: `docs/specs/2026-10-04-landing-montacargas-baterias.md:37-42`] La imagen fuente, su texto alternativo, el H1, el subtítulo y el párrafo no cambian.

## Validación

- Prueba focal visual: `php tests/test-commercial-landing-battery-visual-specs.php`; verificar las frases y su orden, el tipo de cada icono, su carácter decorativo, el encuadre y que se conserve el resto del hero.
- Pruebas de integración: No aplica; no cambian datos ni interacciones.
- Validación manual: Revisar en navegador escritorio (1440 px), tablet (1024 px) y móvil (390 px) el encuadre, el navy detrás del contenido, las tres unidades y sus textos, legibilidad, foco de imagen y ausencia de overflow.
- [Verificado localmente — 2026-10-06] `php -l` del generador y ambas pruebas focales pasan. La prueba integral se ejecutó con `sys_temp_dir` dentro del workspace para respetar el entorno restringido.
- [Verificado en producción — 2026-10-06] El checkout productivo corresponde al commit `09d90ed8f855000664972a7ec900acb42216800d`; SHA-256 del CSS: `c7068f07f542309b11c0629e8d4938a185749ce681dc2b40370bb31a2aac2e79`.
- [Verificado en producción — 2026-10-06] `/baterias-para-montacargas/` respondió HTTP 200. Chromium comprobó 1440×900 y 390×844: sin overflow horizontal; imagen con `object-fit: contain`, alineada a la derecha, con 1166 px de ancho de render en escritorio y 343 px en móvil.
- [Verificado en producción — 2026-10-06] El dry-run de `update-battery-maqueta` pasó: página 1559 actual/destino `736e568a8a347b23c37c0a7a244db332339406e4c1d623f92fc47577b62b5e98`; CF7 1557 actual/destino `acc47f51da1372fbb1a00a00ae824960b161c1076e6701c6f4ccdc41ea29477a`. No se ejecutó escritura porque ambos recursos ya tenían el contenido objetivo.
- [Sincronización — 2026-10-06] `sync-production.sh --check` señaló únicamente `production-snapshot/pages.json` y su suma de control. La comparación de 47 páginas aisló la página 1559 y los campos `post_content`/`post_modified`; su hash actual es el destino autorizado. No hubo diferencias reportadas en código, plugins ni Compose. El snapshot es de auditoría y se dejó intacto en el checkout principal. El reintento aislado tras refrescar solo ese snapshot no terminó dentro de 210 segundos; no se afirma un cierre limpio de ese segundo comando.

## Riesgos

- En los tres anchos revisados la batería sigue reconocible y el texto cabe; otras proporciones de pantalla no se comprobaron.
- En escritorio y móvil revisados, los tres apoyos mantienen icono y frase juntos, sin overflow horizontal.
- El segundo control global posterior a la reconciliación temporal del snapshot no terminó; la verificación específica de commit, CSS, HTTP, navegador y contenido sí está documentada arriba.

## Decisiones pendientes

- Sin decisiones funcionales abiertas.
