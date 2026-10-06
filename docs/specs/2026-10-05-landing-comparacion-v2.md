# SPEC: Presentación del bloque de alquiler frente a compra

## Estado

- DEC-23 aprobado para implementación local el 2026-10-05. La solicitud directa posterior del usuario autoriza commit, push y despliegue de este alcance una vez superados los gates de revisión y producción.
- Complementa únicamente el bloque 04 del SPEC aprobado `2026-10-05-landing-alquiler-v2.md`.

## Contexto

El bloque de comparación ya muestra un H2, un subtítulo y una fotografía a la derecha. La solicitud actual aporta una referencia visual para destacar una frase del título, atenuar el subtítulo y cambiar el contorno de la imagen.

## Problema

El título y subtítulo tienen el mismo tratamiento blanco, el H2 usa el tamaño general de sección y la imagen tiene esquinas redondeadas. La referencia solicita una jerarquía de color y tamaño más marcada y una silueta poligonal.

## Objetivo

Aplicar esos tres ajustes solo al bloque 04, conservando textos, contenido de las tarjetas, fotografía, posición derecha y proporción actuales.

## Fuera del alcance

- Cambiar el texto, la fotografía, su disponibilidad o sus datos comerciales.
- Cambiar otros bloques fuera del alcance del SPEC base o la página de baterías.
- Cambiar datos comerciales, inventario o formulario fuera de los objetivos definidos por el SPEC base.
- Cambios fuera de la página 1558 y del alcance del SPEC base.

## Requisitos funcionales

1. Mantener el H2 completo “Alquiler mensual frente a compra de maquinaria” y envolver únicamente “compra de maquinaria” para darle el amarillo de marca `#FFC33C`.
2. Aumentar el tamaño del H2 con `clamp(38px, 4vw, 50px)` en escritorio y `clamp(30px, 7vw, 36px)` en móvil.
3. Cambiar el subtítulo “Capacidad adicional en tu bodega sin sumar un activo a tu balance” de blanco a gris `#E6E6E6`.
4. Reemplazar las esquinas redondeadas de la fotografía por `clip-path: polygon(26% 0, 100% 0, 100% 100%, 26% 100%, 0 83%, 0 34%)`, manteniendo imagen, posición y relación de aspecto.

## Reglas de negocio

- El cambio es exclusivamente visual y conserva íntegros los textos aprobados.
- Se mantienen la paleta y el activo fotográfico del bloque.

## Contratos

No aplica; no cambian datos, interacciones ni interfaces. El bloque conserva el orden y contenido del SPEC base.

## Casos límite

- El título y la imagen deben adaptarse a móvil sin recorte horizontal ni desbordamiento.
- El recorte poligonal no debe ocultar al operador o al montacargas en la imagen actual.

## Archivos o módulos relacionados

- `scripts/commercial-landing-rental-v2.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css`
- Prueba focal del renderer de la landing de alquiler.

## Criterios de aceptación

1. El texto del H2 no cambia; solo “compra de maquinaria” aparece en `#FFC33C`.
2. El H2 usa los tamaños responsive definidos en este SPEC.
3. El subtítulo usa `#E6E6E6`.
4. La fotografía conserva fuente, ubicación y proporción, y muestra el contorno poligonal indicado en escritorio y móvil.
5. La sección no genera overflow y el contenido de las tarjetas permanece intacto.

## Validación

- Pruebas unitarias: aserciones focales del renderer y del contenido del H2.
- Pruebas de integración: no aplica; no cambian interacciones ni datos.
- Validación manual: Chromium local en escritorio (1440×900) y móvil (390×844), sin overflow; proporción 4:3, `object-fit: cover` y recorte poligonal computados.
- Validación productiva: revisar este bloque en la página publicada 1558, en escritorio y móvil, después del despliegue.

## Resultado local

- Implementación y prueba focal completadas. `php -l` pasó para el renderer y la prueba; `php tests/test-commercial-landing-rental-v2-render.php` pasó; `git diff --check` no reportó errores.
- La revisión local con Chromium confirmó ausencia de overflow en escritorio (1440×900) y móvil (390×844); la imagen conserva proporción 4:3, `object-fit: cover` y el recorte indicado. Se usó un fixture aislado, no la página completa.
- La validación productiva queda pendiente hasta ejecutar el despliegue.

## Riesgos

- El recorte puede ocultar parte de la fotografía; revisar la composición resultante en escritorio y móvil.

## Decisiones pendientes

- No hay decisiones de diseño abiertas. La publicación sigue sujeta a los gates del runbook.
