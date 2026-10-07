# SPEC: Iconos en los pasos del proceso de alquiler

## Estado

- Aprobado el 2026-10-06 por aprobación directa del usuario.
- Alcance original terminado el 2026-10-06 tras validar el renderer, desplegar el cambio y comprobar la página pública.
- Corrección visual solicitada el 2026-10-06: renderer, CSS y prueba focal validados localmente; pendiente el deploy y la comprobación visual final.

## Contexto

- [Solicitud] La captura adjunta señala los números 01, 02, 03 y 04 del bloque “Cómo funciona” en `https://tecnimontacargas.com/alquiler-montacargas-electricos/` y pide colocar iconos encima de ellos.
- [Solicitud: captura adjunta 2026-10-06] Aplicar a la fotografía actual del proceso la silueta angular de punta derecha y una línea roja de la referencia, conservando la fotografía de la landing.
- [Solicitud: captura adjunta 2026-10-06] Cambiar la línea roja a azul de marca y corregir el recorte asimétrico de la fotografía para que sus vértices sigan el contorno de la referencia.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:80-84`] El renderer genera cuatro pasos dentro de una lista ordenada, seguidos por la fotografía del proceso.
- [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css:588-596`] Los números 01–04 se generan con un contador CSS antes del contenido de cada paso.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:21-23,92-96`] La landing ya usa iconos SVG lineales inline con `currentColor` y los marca como decorativos mediante `aria-hidden="true"`.
- [Evidencia productiva: página 1558 consultada el 2026-10-06] El bloque guardado conserva un SVG antiguo con `d="M70 0 L100 50"` y `stroke="#e9272e"`; un despliegue de archivos por sí solo debe corregir también ese marcado persistido.

## Problema

Los cuatro pasos muestran su número, título y descripción, pero no tienen un icono encima del número como pide la referencia. La fotografía del proceso también necesita una silueta angular. Tras la primera implementación, el usuario pidió que el acento fuera azul y que el contorno se ajustara mejor a la referencia asimétrica.

## Objetivo

Mostrar un icono encima de cada número del proceso y dar a la fotografía existente una silueta de punta derecha con un acento azul de marca, sin cambiar su origen, texto alternativo ni el orden actual del bloque.

## Fuera del alcance

- Cambiar los números, títulos, descripciones, subtítulo, encabezado, archivo de imagen o texto alternativo.
- Cambiar las columnas responsive, el orden del contenido o cualquier otro bloque de la landing.
- Añadir interacciones, dependencias o solicitudes de iconos a servicios externos.

## Requisitos funcionales

1. [Solicitud] Mostrar un icono encima de cada número 01, 02, 03 y 04, asociado visualmente al mismo paso.
2. [Evidencia: `scripts/commercial-landing-rental-v2.php:80-84`] Conservar los cuatro pasos, sus textos y su orden actual.
3. [Evidencia: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css:533-625`] Conservar la composición de cuatro columnas desde 1200 px, dos columnas entre 761 y 1199 px y una columna hasta 760 px.
4. [Evidencia: `scripts/commercial-landing-rental-v2.php:21-23,92-96`] Mantener los iconos decorativos e integrados en el lenguaje visual SVG inline ya usado por la landing.
5. [Solicitud: captura adjunta 2026-10-06] Recortar visualmente la fotografía actual como una punta asimétrica hacia la derecha y mostrar una línea azul de marca junto al borde diagonal superior, sin invadir los vértices. El renderer usa el segmento actualizado; el CSS debe también aplicar color y geometría equivalentes al SVG antiguo ya persistido en la página.
6. [Solicitud: captura adjunta 2026-10-06] Conservar el archivo `process.webp`, su texto alternativo y su disponibilidad responsive.

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
5. [Solicitud: captura adjunta 2026-10-06] La fotografía del proceso conserva su origen y texto alternativo, usa el recorte `polygon(0 0, 51.5% 0, 100% 57.5%, 56.5% 100%, 0 100%)` y presenta el trazo azul `#128CEB` entre `(66, 17.2)` y `(96, 52.8)`, con esquinas limpias y sin desbordamiento horizontal. Esto aplica tanto al SVG del renderer como al SVG antiguo de la página ya persistida.

## Validación

Las validaciones siguientes corresponden al alcance original DEC-01 a DEC-03, cuya línea era roja. No verifican la corrección DEC-04.

- Pruebas unitarias: las aserciones del renderer para los cuatro iconos, su correspondencia y su ubicación sobre los números se ejecutaron y aprobaron.
- Pruebas de integración: no aplica; no cambian datos ni interacciones.
- Validación manual: Chromium revisó icono sobre número, legibilidad, recorte en punta derecha, línea roja sobre la diagonal, visibilidad del equipo y ausencia de overflow a 1440×900, 1024×768 y 390×844.
- Validación productiva: después del despliegue autorizado, se comprobaron el bloque público y los assets servidos en escritorio, tablet y móvil.

### Evidencia de cierre

- `php -l scripts/commercial-landing-rental-v2.php` y `php -l tests/test-commercial-landing-rental-v2-render.php`: sin errores.
- `php tests/test-commercial-landing-rental-v2-render.php`: aprobado; `git diff --check`: sin errores.
- El contenido publicado de la página 1558 quedó con SHA-256 `6bf122ddae2c90623eb72d206426b69cfa66809d0f2ee2378c8e14a1064e3341`. La huella del contenido fuera del bloque “Cómo funciona” se mantuvo en `1061d4bda5c8fbf999e3003b7eebab652deb1fe401721cf83a6883c325530711`.
- La aplicación productiva ejecuta `58b8a86`, que contiene el commit `2d10871`. La página pública y su bundle CSS optimizado respondieron HTTP 200; el bundle verificado tuvo SHA-256 `dae8ff8fd4a2355c92b8f21fca96444d9cf4c83040999806e8afc07b69982cfe`.
- Chromium comprobó 4/2/1 columnas, iconos de 36 px en `#FFC33C`, la imagen cargada de 1187×668 px, el recorte de punta derecha, la línea roja y ausencia de overflow horizontal en 1440×900, 1024×768 y 390×844.
- Se purgaron las cachés de LiteSpeed después de actualizar el bloque de contenido.

### Estado de la corrección DEC-04

- El lint PHP del renderer y de la prueba focal pasa, la prueba focal pasa, y `git diff --check` no reporta errores de whitespace.
- En producción, el HTML guardado de la página 1558 aún tiene el SVG rojo antiguo (`M70 0 L100 50`, `#e9272e`); el CSS añade un selector explícito que le aplica el azul y lo transforma a los vértices requeridos sin escribir en la base de datos.
- La prueba focal sí se ejecutó y pasó después de añadir la adaptación CSS; no se hizo una captura de navegador local con la geometría nueva.
- La corrección DEC-04 no está desplegada ni verificada en producción.

## Riesgos revisados

### Evidencia de la geometría anterior (DEC-01 a DEC-03)

- El espacio vertical adicional de los iconos no produjo overflow y su separación respecto al texto se mantuvo legible en los tres anchos revisados.
- El recorte anterior mantuvo visibles el montacargas y la carga en escritorio, tablet y móvil; la fotografía cargó a 1187×668 px. Esto no verifica la visibilidad con la geometría DEC-04.

### Riesgo abierto de DEC-04

- Falta confirmar en navegador que el nuevo recorte mantenga visibles el montacargas y la carga en escritorio, tablet y móvil.

## Decisiones

- DEC-01 resuelto el 2026-10-06: usar cuatro SVG lineales inline, decorativos, con metáforas de registro de datos, orientación técnica, cotización y entrega. El usuario aprobó esta propuesta junto con el SPEC.
- DEC-02 solicitado y resuelto el 2026-10-06: mostrar iconos y números en amarillo de marca `#FFC33C` con una base azul oscuro `#262E4F`, siguiendo el resaltado “cuatro pasos”.
- DEC-03 solicitado y resuelto el 2026-10-06: aplicar la forma de punta derecha y la línea roja sobre el borde diagonal superior de la fotografía, conservando `process.webp` y su texto alternativo.
- DEC-04 solicitado el 2026-10-06: reemplazar la línea roja por el azul empresarial `#128CEB`; usar el recorte `polygon(0 0, 51.5% 0, 100% 57.5%, 56.5% 100%, 0 100%)` y el trazo `M66 17.2 L96 52.8` para seguir la diagonal sin invadir sus vértices. DEC-04 sustituye el color y la geometría de DEC-03; la captura y validación productiva anteriores documentan el estado previo, no esta corrección.
