# SPEC: Normalización de proporciones de la portada

## Estado

- Terminado

## Contexto

[Solicitud] Se pide mejorar las proporciones de la portada directamente en producción, considerando separación, alto y ancho de las secciones, y revisar el resultado con Playwright.

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] En `1440x900`, el wrapper central aporta `48px` de padding y su primera sección hija aporta otros `48px`; el contenido comienza `96px` después del borde superior compuesto.

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] En `390x900`, el mismo anidamiento aporta `32px + 32px`; el contenido comienza `64px` después del borde superior compuesto. No se detectó overflow horizontal.

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] Equipos destacados, historias, servicios y blog usan un ancho de contenido de `1180px` en escritorio. Los bloques de energía y beneficios usan `1290px`, por lo que sus bordes no se alinean con el resto de la portada.

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] El bloque de beneficios aporta `80px` arriba y abajo tanto en escritorio como en móvil. Energía conserva `48px` arriba y abajo en móvil; ambos exceden la referencia móvil aprobada de `32px`.

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] El héroe mide `648px` en escritorio y `486px` en móvil, contiene texto y controles sin recorte y funciona como composición audiovisual; no presenta evidencia de una altura fija defectuosa.

## Problema

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] La portada mezcla anchos de `1180px` y `1290px`, duplica padding en contenedores anidados y conserva bloques con `80px` de padding vertical. El resultado pierde alineación horizontal y extiende innecesariamente varias secciones.

## Objetivo

[Solicitud] Crear una proporción visual coherente en la portada, alineando los anchos de contenido y compactando las alturas introducidas por padding, sin forzar alturas que puedan recortar contenido dinámico.

## Fuera del alcance

- [Solicitud] No rediseñar componentes, tipografías, colores, contenido, navegación ni orden de secciones.
- [Solicitud] No imponer alturas fijas a secciones cuyo contenido sea dinámico.
- [Solicitud] No modificar la composición ni altura del héroe mientras no exista evidencia de recorte, solapamiento o desproporción en los viewports auditados.
- [Solicitud] No cambiar las proporciones de páginas internas.
- [Regla: AGENTS.md] No modificar el tema padre, WordPress core ni plugins de terceros.
- [Regla: AGENTS.md] No desplegar archivos distintos de los modificados para esta corrección.

## Requisitos funcionales

1. [Solicitud] Los contenidos principales de equipos destacados, historias, servicios, energía, beneficios y blog deben compartir una referencia máxima de ancho de `1180px` en escritorio.
2. [Solicitud] En móvil, esos contenidos deben conservar un gutter lateral mínimo de `16px` y no generar overflow horizontal.
3. [Solicitud] El bloque central debe aportar una sola capa efectiva de separación vertical antes de “Equipos destacados” y después de “Mantenimiento y soporte especializado”.
4. [Evidencia: docs/specs/2026-08-10-espaciado-vertical-sitio.md] Las secciones deben usar como referencia `48px` arriba y abajo en escritorio y `32px` en móvil, sin duplicar la referencia mediante wrappers anidados.
5. [Solicitud] La altura de energía y beneficios debe derivarse del contenido y del padding responsive; no debe fijarse una altura que corte tarjetas, texto, imágenes o controles.
6. [Solicitud] Equipos destacados, historias, servicios, energía, beneficios y blog deben conservar contenido, jerarquía, enlaces y controles.
7. [Solicitud] El ajuste debe limitarse a la portada y no alterar páginas que reutilicen el plugin de equipos destacados fuera de ella.
8. [Regla: AGENTS.md] La portada debe permanecer usable, sin solapamientos, recortes ni desplazamiento horizontal en escritorio y móvil.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] No se deben alterar equipos, marcas, modelos, imágenes, precios, disponibilidad ni hechos comerciales.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- [Solicitud] Los carruseles deben conservar flechas, indicadores y sombras sin recorte.
- [Solicitud] El contenido dinámico puede cambiar la altura natural de una sección sin romper la alineación horizontal.
- [Solicitud] Los textos de varias líneas deben permanecer dentro de sus tarjetas y columnas.
- [Regla: AGENTS.md] En `390px` no debe aparecer overflow horizontal ni superposición entre secciones.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:406] Los selectores corregidos deben mantener el alcance de `body.page-id-47`.

## Archivos o módulos relacionados

- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:35] Proporciones del héroe y estilos focalizados de la portada.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:158] Contenedor canónico de `1180px`.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:405] Escala vertical y wrapper de la portada.
- [Evidencia: wp-content/plugins/tm-equipos-destacados-v2/assets/css/tm-equipos-destacados.css:20] Espaciado propio del componente de equipos destacados, que debe conservarse fuera de la portada.
- [Evidencia: docs/specs/2026-08-10-espaciado-vertical-sitio.md] Escala vertical aprobada y desplegada previamente.

## Criterios de aceptación

1. [Solicitud] En `1440x900`, energía y beneficios se alinean con el ancho máximo de `1180px` usado por los demás contenidos principales.
2. [Solicitud] En `390x900`, todos los bloques auditados conservan al menos `16px` de gutter lateral útil y no producen overflow horizontal.
3. [Solicitud] En escritorio, el bloque central, energía y beneficios aplican una sola referencia vertical efectiva de `48px`, sin suma adicional del wrapper.
4. [Solicitud] En móvil, el bloque central, energía y beneficios aplican una sola referencia vertical efectiva de `32px`, sin suma adicional del wrapper.
5. [Solicitud] Ninguna sección corregida usa una altura fija nueva; su alto se adapta al contenido sin recorte.
6. [Solicitud] Equipos destacados, historias, servicios, energía, beneficios y blog conservan contenido y controles; el carrusel de historias puede avanzar al siguiente elemento.
7. [Regla: AGENTS.md] En `1440x900` y `390x900` no existen solapamientos, recortes ni overflow horizontal introducidos por el cambio.
8. [Solicitud] El catálogo y las páginas internas no reciben cambios visuales por esta corrección focalizada.

## Validación

- Pruebas unitarias: No aplica; el alcance es CSS de presentación sin lógica de negocio.
- Pruebas de integración: Confirmar carga del asset CSS corregido y alcance de sus selectores a `body.page-id-47`.
- Validación manual: Usar Playwright en `1440x900` y `390x900` sobre la portada; medir anchos, padding, altura natural, gutters, consola, overflow, recorte y controles de carruseles. Comprobar una página interna que reutilice equipos destacados.
- Validación productiva: Con autorización vigente, ejecutar backup focalizado, control de deriva, despliegue del manifiesto exacto, purga de caché, HTTP, Playwright, revisión de logs y `./scripts/sync-production.sh --check` posterior.

## Evidencia de cierre

- [Evidencia: producción, 2026-08-11] Se desplegó únicamente `wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css`; el archivo remoto y la URL pública coinciden con el hash `a65f6150890137853f11451ce2922a0601c62c711c0b11b087b6322da60a6350` del manifiesto aprobado.
- [Evidencia: producción, 2026-08-11] El backup focalizado quedó verificado fuera del directorio público con hash `c29ffe15c4920c918117a832a6d5be2af864d6f31b8ee14e329134a56b92899a`, permisos `600` y estrategia de restauración definida.
- [Evidencia: Playwright producción, 2026-08-11] En `1440x900`, Energía y beneficios resolvieron a `1180px`, padding vertical de `48px` y el contenido inicial quedó a `48px` del wrapper; no hubo overflow horizontal.
- [Evidencia: Playwright producción, 2026-08-11] En `390x900`, Energía y beneficios conservaron gutters de `16px`, padding vertical de `32px` y el contenido inicial quedó a `32px` del wrapper; no hubo overflow horizontal.
- [Evidencia: Playwright producción, 2026-08-11] El carrusel avanzó de “Historia 1” a “Historia 2” y la consola no registró errores.
- [Evidencia: producción, 2026-08-11] LiteSpeed confirmó la purga total, la portada respondió HTTP `200`, el contenedor permaneció `running` y no hubo coincidencias de errores PHP fatales, parseos, excepciones no capturadas o respuestas `500` en los logs revisados.
- [Evidencia: sincronización, 2026-08-11] `./scripts/sync-production.sh --check` contra `root@149.28.97.249` confirmó coincidencia completa entre producción y repositorio.

## Riesgos

- [Inferencia técnica] Los bloques de energía y beneficios son contenido Kadence identificado por clases generadas; los selectores deben permanecer ligados a `page-id-47` y a los identificadores vigentes.
- [Inferencia técnica] Compactar padding sin conservar gutters puede acercar contenido al borde en móvil.
- [Inferencia técnica] LiteSpeed puede servir CSS cacheado después del despliegue hasta completar la purga.
- [Inferencia técnica] Un selector sin `body.page-id-47` podría modificar el componente de equipos destacados en otras páginas.

## Decisiones pendientes

- Ninguna. Versión ampliada aprobada por el usuario el 11 de agosto de 2026.
