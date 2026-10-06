# Plan: Presentación del bloque “Cómo funciona” de alquiler

> SPEC: `docs/specs/2026-10-05-landing-proceso-v2.md`.

## Tareas

1. [x] Aprobación de implementación local registrada para DEC-24 el 2026-10-05.
2. [x] Agregar el rótulo del proceso y resaltar “cuatro pasos” en el H2 sobre fondo azul oscuro para conservar el amarillo con contraste accesible.
3. [x] Mover la fotografía a la izquierda en escritorio y maquetar los pasos 01–04 en cuatro columnas.
4. [x] Definir cuadrícula 2×2 para tablet y una columna para móvil, conservando el orden de lectura estrecho.
5. [x] Añadir aserciones focales del renderer para el orden y contraste del rótulo/acento, textos, fotografía, apilado vertical de cada paso, breakpoints y reinicio de filas en móvil.
6. [x] Ejecutar `php -l` para renderer y test, prueba focal y `git diff --check`.
7. [x] Revisar fixture en Chromium en escritorio (1440 px), tablet (1024 px) y móvil (390 px); rótulo azul legible, grillas 4/2/1, frase destacada en una sola pieza, orden responsive de la imagen y sin overflow.
8. [ ] Desplegar tras revisión, backup y sincronización; verificar el bloque en la página pública.

## Límites

- Solo bloque 06 y sus pruebas focales.
- No cambiar los textos de los pasos ni la fotografía.
- No modificar contenido productivo, otros bloques ni la página de baterías.
- El usuario autorizó directamente el despliegue de estos cambios en producción, sujeto a los gates aplicables y limitado a la página 1558.
