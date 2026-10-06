# Plan: Presentación del bloque de alquiler frente a compra

> SPEC: `docs/specs/2026-10-05-landing-comparacion-v2.md`.

## Tareas

1. [x] Aprobación de implementación local registrada para DEC-23 el 2026-10-05.
2. [x] Envolver “compra de maquinaria” en el H2 del bloque 04 sin cambiar el texto.
3. [x] Aplicar tamaños responsive, color amarillo del fragmento, color gris del subtítulo y recorte poligonal de la imagen.
4. [x] Actualizar la prueba focal del renderer, incluidos los textos de las tres tarjetas.
5. [x] Ejecutar `php -l` en renderer y prueba, prueba focal y `git diff --check`.
6. [x] Confirmar en Chromium local los estados escritorio (1440×900) y móvil (390×844), con proporción 4:3, recorte poligonal y sin overflow.
7. [ ] Desplegar tras revisión, backup y sincronización; verificar el bloque en la página pública.

## Límites

- Solo bloque 04 y sus pruebas focales.
- No ampliar el cambio a otros bloques fuera del SPEC base, la página de baterías, otros formularios o datos de inventario.
- El usuario autorizó directamente el despliegue de estos cambios en producción, sujeto a los gates aplicables y limitado a la página 1558.
