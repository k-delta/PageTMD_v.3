# SPEC: Presentación del bloque “Cómo funciona” de alquiler

## Estado

- DEC-24 aprobado para implementación el 2026-10-05. La solicitud directa posterior del usuario autoriza commit, push y despliegue de este alcance una vez superados los gates de revisión y producción.
- Complementa únicamente el bloque 06 del SPEC aprobado `2026-10-05-landing-alquiler-v2.md`.

## Contexto

El bloque 06 presenta el proceso de alquiler en una lista vertical a la izquierda y una fotografía a la derecha. La solicitud aporta una referencia para el rótulo y pide una composición horizontal de los cuatro pasos y la fotografía al lado izquierdo.

## Problema

La composición actual apila los pasos y coloca la fotografía a la derecha; no coincide con la disposición solicitada.

## Objetivo

Destacar el bloque como “CÓMO FUNCIONA”, presentar el proceso en cuatro pasos contiguos en escritorio y mover la fotografía a la columna izquierda, preservando los textos y una lectura responsive.

## Fuera del alcance

- Cambiar el orden, significado o redacción de los cuatro pasos.
- Cambiar la fotografía o los datos comerciales.
- Modificar otros bloques o la página de baterías.

## Requisitos funcionales

1. Colocar el rótulo “CÓMO FUNCIONA” inmediatamente encima del H2, con una línea horizontal corta y el azul de marca `#128CEB`. Mantenerlo en 19 px, negrita, para alcanzar el contraste mínimo de texto grande sobre `#F6F6F6`.
2. Mantener el H2 “Alquiler de montacargas en cuatro pasos” y resaltar únicamente “cuatro pasos” en amarillo de marca `#FFC33C` sobre un fondo `#262E4F`; el contraste de texto es 8.27:1.
3. En escritorio de 1200 px o más, colocar la fotografía en la columna izquierda y el texto del proceso a la derecha.
4. En esa columna, mostrar los pasos 01, 02, 03 y 04 en una sola fila de cuatro columnas. Cada columna muestra el número arriba, el título debajo y su descripción al final.
5. Entre 761 y 1199 px, acomodar los pasos en dos columnas por dos filas; hasta 760 px, apilarlos en una columna. En pantallas angostas se conserva primero el contenido textual en el orden del DOM y luego la fotografía.
6. Mantener la fotografía `process.webp`, su texto alternativo y el contenido actual del subtítulo y de los pasos.

## Reglas de negocio

- El cambio es exclusivamente de presentación y conserva íntegros el proceso comercial y sus cuatro textos.
- La fotografía continúa siendo ilustrativa; no representa disponibilidad de inventario.

## Contratos

No aplica; no cambian datos ni interacciones. Se conservan los textos y el orden del SPEC base.

## Casos límite

- Los títulos y descripciones de cada paso deben seguir siendo legibles en cuatro columnas de escritorio.
- La composición no debe provocar overflow ni reducir la fotografía fuera de su columna.

## Archivos o módulos relacionados

- `scripts/commercial-landing-rental-v2.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css`
- Prueba focal del renderer de la landing de alquiler.

## Criterios de aceptación

1. El rótulo aparece inmediatamente encima del H2 en `#128CEB`, con línea corta y tamaño/negrita que conservan contraste accesible en escritorio y móvil.
2. Solo “cuatro pasos” está resaltado en `#FFC33C` sobre fondo `#262E4F`; los demás términos y el subtítulo no cambian.
3. A partir de 1200 px, la imagen queda a la izquierda y los cuatro pasos se muestran en una fila con número, título y descripción apilados.
4. Entre 761 y 1199 px los pasos forman una cuadrícula 2×2; hasta 760 px forman una columna y conservan el orden textual antes de la imagen.
5. Los textos, el alt y la fuente de imagen permanecen intactos; no aparece overflow horizontal.

## Validación

- Pruebas unitarias: aserciones focales de rótulo, fragmento resaltado, cuatro pasos y textos.
- Pruebas de integración: no aplica; no cambian interacciones ni datos.
- Validación manual: Chromium local en escritorio (1440 px), tablet (1024 px) y móvil (390 px); rótulo azul, grillas 4/2/1, orden responsive de la imagen y sin overflow.
- Validación productiva: revisar este bloque en la página publicada 1558, en escritorio, tablet y móvil, después del despliegue.

## Resultado local

- Implementación local y prueba focal completadas. `php -l` pasó para renderer y prueba; `php tests/test-commercial-landing-rental-v2-render.php` pasó; `git diff --check` no reportó errores.
- La revisión local en Chromium de un fixture aislado confirmó escritorio (1440 px, cuatro columnas), tablet (1024 px, dos columnas) y móvil (390 px, una columna); rótulo azul `#128CEB` de 19 px, acento `#FFC33C` sobre `#262E4F` (8.27:1), imagen a la izquierda en anchos ≥761 px, debajo del texto en móvil y sin overflow horizontal.
- La validación productiva queda pendiente hasta ejecutar el despliegue.

## Riesgos

- Cuatro columnas dentro de la columna de texto pueden alargar el bloque; revisar legibilidad en el ancho mínimo definido para escritorio.

## Decisiones pendientes

- No hay decisiones de diseño abiertas. La publicación sigue sujeta a los gates del runbook.
