# SPEC: Iconos en los pasos del proceso de alquiler

## Estado

- Aprobado el 2026-10-06 por aprobación directa del usuario.

## Contexto

- [Solicitud] La captura adjunta señala los números 01, 02, 03 y 04 del bloque “Cómo funciona” en `https://tecnimontacargas.com/alquiler-montacargas-electricos/` y pide colocar iconos encima de ellos.
- [Solicitud: captura adjunta 2026-10-07] Aplicar a la fotografía actual del proceso la silueta angular de punta derecha y una línea roja de la referencia, conservando la fotografía de la landing.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:80-84`] El renderer genera cuatro pasos dentro de una lista ordenada, seguidos por la fotografía del proceso.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css:588-596`] Los números 01–04 se generan con un contador CSS antes del contenido de cada paso.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:21-23,92-96`] La landing ya usa iconos SVG lineales inline con `currentColor` y los marca como decorativos mediante `aria-hidden="true"`.

## Problema

Los cuatro pasos muestran su número, título y descripción, pero no tienen un icono encima del número como pide la referencia. La fotografía del proceso también necesita la silueta angular y el acento rojo de la segunda referencia visual.

## Objetivo

Mostrar un icono encima de cada número del proceso y dar a la fotografía existente una silueta de punta derecha con un acento rojo, sin cambiar su origen, texto alternativo ni el orden actual del bloque.

## Fuera del alcance

- Cambiar los números, títulos, descripciones, subtítulo, encabezado, archivo de imagen o texto alternativo.
- Cambiar las columnas responsive, el orden del contenido o cualquier otro bloque de la landing.
- Añadir interacciones, dependencias o solicitudes de iconos a servicios externos.

## Requisitos funcionales

1. [Solicitud] Mostrar un icono encima de cada número 01, 02, 03 y 04, asociado visualmente al mismo paso.
2. [Evidencia: `scripts/commercial-landing-rental-v2.php:80-84`] Conservar los cuatro pasos, sus textos y su orden actual.
3. [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css:533-625`] Conservar la composición de cuatro columnas desde 1200 px, dos columnas entre 761 y 1199 px y una columna hasta 760 px.
4. [Evidencia: `scripts/commercial-landing-rental-v2.php:21-23,92-96`] Mantener los iconos decorativos e integrados en el lenguaje visual SVG inline ya usado por la landing.
5. [Solicitud: captura adjunta 2026-10-07] Recortar visualmente la fotografía actual como una punta hacia la derecha y mostrar una línea roja junto al borde diagonal superior.
6. [Solicitud: captura adjunta 2026-10-07] Conservar el archivo `process.webp`, su texto alternativo y su disponibilidad responsive.

## Reglas de negocio

- [Evidencia: `docs/specs/2026-10-05-landing-proceso-v2.md:36-43`] El bloque conserva el proceso comercial y no modifica datos ni interacciones.

## Contratos

### Entrada

No aplica; el usuario no envía datos nuevos.

### Salida

No aplica; el bloque no expone datos ni interacciones nuevas.

## Casos límite

- [Solicitud] Los iconos y números deben permanecer claramente separados y legibles en las tres distribuciones responsive.
- [Evidencia: `docs/specs/2026-10-05-landing-proceso-v2.md:45-48`] La composición debe conservar legibilidad y no provocar overflow horizontal.

## Archivos o módulos relacionados

- `scripts/commercial-landing-rental-v2.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css`
- `tests/test-commercial-landing-rental-v2-render.php`
- `docs/specs/2026-10-05-landing-proceso-v2.md`

## Criterios de aceptación

1. [Solicitud] Cada uno de los cuatro pasos presenta un icono encima de su número correspondiente.
2. [Evidencia: `scripts/commercial-landing-rental-v2.php:80-84`] Los números, títulos, descripciones, imagen y texto alternativo actuales permanecen iguales y en el mismo orden.
3. [Evidencia: `docs/specs/2026-10-05-landing-proceso-v2.md:56-62`] Las distribuciones 4/2/1 se conservan en escritorio, tablet y móvil, sin overflow horizontal.
4. [Evidencia: `scripts/commercial-landing-rental-v2.php:21-23,92-96`] Los SVG son inline, decorativos para tecnologías de asistencia y no dependen de una biblioteca externa.
5. [Solicitud: captura adjunta 2026-10-07] La fotografía del proceso conserva su origen y texto alternativo, adopta una punta hacia la derecha y presenta una línea roja sobre el borde diagonal sin desbordamiento horizontal en escritorio, tablet y móvil.

## Validación

- Pruebas unitarias: ampliar las aserciones del renderer para verificar los cuatro iconos, su correspondencia y su ubicación anterior al número de cada paso.
- Pruebas de integración: no aplica; no cambian datos ni interacciones.
- Validación manual: Chromium local a 1440×900, 1024×768 y 390×844; comprobar icono sobre número, legibilidad, recorte en punta derecha, línea roja sobre la diagonal, visibilidad del equipo y ausencia de overflow.
- Validación productiva: después del despliegue autorizado, comprobar el bloque público en escritorio, tablet y móvil y confirmar que los assets servidos reflejan el cambio.

## Riesgos

- El espacio vertical adicional puede cambiar la altura del bloque; verificar separación y alineación en los tres anchos de referencia.
- El recorte puede ocultar parte de la fotografía; revisar que el montacargas y la carga sigan visibles en los tres anchos de referencia.

## Decisiones pendientes

- DEC-01 resuelto el 2026-10-06: usar cuatro SVG lineales inline, decorativos, con metáforas de registro de datos, orientación técnica, cotización y entrega. El usuario aprobó esta propuesta junto con el SPEC.
- DEC-02 solicitado el 2026-10-07: mostrar iconos y números en amarillo de marca `#FFC33C`. Como ajuste de presentación, añadirles una base azul oscuro `#262E4F`, siguiendo el resaltado “cuatro pasos” y manteniendo contraste sobre el fondo claro.
- DEC-03 solicitado el 2026-10-07: aplicar la forma de punta derecha de la captura de referencia y una línea roja en el borde diagonal superior de la fotografía actual. Se conserva `process.webp` y su texto alternativo.
