# SPEC: Simplificación de contenido redundante en la portada

## Estado

- Terminado

## Contexto

[Solicitud] Se pide limpiar de la portada actual los elementos innecesarios o sobrantes, conservando el resultado visual ya aprobado.

[Evidencia: production-snapshot/pages.json, página 47] Después del héroe existe una columna Kadence vacía identificada como `47_50cecc-16`; el CSS actual la oculta con `display: none`.

[Evidencia: production-snapshot/pages.json, página 47] El bloque de Energía contiene tres párrafos vacíos que no aportan contenido.

[Evidencia: production-snapshot/pages.json, página 47] Después de Energía existe una franja de tres beneficios: “Calidad garantizada”, “soporte técnico” y “Cobertura Nacional”. El mensaje de soporte repite la sección anterior de mantenimiento y los otros dos mensajes no ofrecen una acción o detalle adicional.

[Evidencia: Playwright sobre https://tecnimontacargas.com/, 2026-08-11] La franja de beneficios ocupa `313px` en `1440x900` y `642px` en `390x900`.

[Evidencia: Playwright con preview en memoria, 2026-08-11] Al retirar la franja y los nodos vacíos, Energía conecta directamente con Últimos artículos, la portada reduce `313px` en escritorio y `641px` en móvil, y no aparece overflow horizontal.

## Problema

[Solicitud] La portada conserva bloques vacíos y una franja informativa redundante que alarga el recorrido, añade ruido visual y repite mensajes sin ampliar la navegación ni la capacidad de conversión.

## Objetivo

[Solicitud] Simplificar la portada eliminando únicamente contenido vacío o redundante, de forma que cada sección restante tenga una función diferenciada y verificable.

## Fuera del alcance

- [Solicitud] No eliminar ni rediseñar el héroe, carrusel de marcas, equipos destacados, historias de éxito, mantenimiento, energía, últimos artículos, CTA final, footer, contacto lateral o chatbot.
- [Solicitud] No cambiar textos, enlaces, imágenes, colores, tipografías ni orden de las secciones conservadas.
- [Solicitud] No eliminar contenido de páginas internas.
- [Regla: AGENTS.md] No modificar WordPress core, tema padre ni plugins de terceros.
- [Regla: AGENTS.md] No escribir en producción sin autorización explícita, backup verificado y reversión definida.

## Requisitos funcionales

1. [Solicitud] La columna vacía `47_50cecc-16` debe dejar de formar parte del contenido de la portada.
2. [Solicitud] Los tres párrafos vacíos del bloque de Energía deben dejar de formar parte del contenido de la portada.
3. [Solicitud] La franja completa que contiene “Calidad garantizada”, “soporte técnico” y “Cobertura Nacional” debe retirarse de la portada.
4. [Solicitud] La eliminación debe ser estructural: los bloques retirados no deben permanecer ocultos en el DOM mediante CSS.
5. [Solicitud] Las reglas CSS que solo existan para bloques eliminados deben retirarse cuando no conserven referencias activas.
6. [Solicitud] Energía debe quedar seguida por Últimos artículos, manteniendo la escala vertical aprobada de `48px` en escritorio y `32px` en móvil.
7. [Solicitud] Todas las secciones conservadas deben mantener contenido, enlaces, controles y comportamiento.
8. [Regla: AGENTS.md] La portada debe permanecer sin solapamientos, recortes ni overflow horizontal en escritorio y móvil.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] No se deben inventar ni modificar equipos, marcas, modelos, imágenes, precios, disponibilidad o hechos comerciales.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- [Solicitud] El retiro de la franja no debe dejar un wrapper, fondo, margen o espacio vacío entre Energía y Últimos artículos.
- [Solicitud] Los bloques Gutenberg/Kadence conservados deben mantener estructura válida y editable.
- [Solicitud] La eliminación de selectores obsoletos no debe afectar elementos con clases similares en otras páginas.
- [Regla: AGENTS.md] En móvil no debe aparecer desplazamiento horizontal ni contenido recortado.

## Archivos o módulos relacionados

- [Evidencia: production-snapshot/pages.json] Snapshot auditable del contenido actual de la página 47; WordPress es la fuente activa del contenido.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:15] Regla que oculta la columna vacía `47_50cecc-16`.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:427] Reglas de proporción ligadas a la franja `47_145da3-51`.
- [Evidencia: docs/specs/2026-08-11-proporciones-portada.md] Proporciones aprobadas que deben conservarse.

## Criterios de aceptación

1. [Solicitud] La portada no contiene `47_50cecc-16`, los tres párrafos vacíos de Energía ni el bloque `47_145da3-51` en el DOM o en el contenido de la página 47.
2. [Solicitud] No quedan reglas CSS cuya única referencia sea uno de los bloques retirados.
3. [Solicitud] Energía queda seguida por Últimos artículos sin wrapper, fondo o espacio residual de la franja eliminada.
4. [Solicitud] La página reduce aproximadamente `313px` en escritorio y `641px` en móvil respecto de la medición auditada, admitiendo variación por contenido dinámico.
5. [Solicitud] En `1440x900` y `390x900` no existen solapamientos, recortes ni overflow horizontal.
6. [Solicitud] Héroe, marcas, equipos, historias, mantenimiento, energía, blog, CTA, footer, contacto y chatbot permanecen visibles y funcionales.
7. [Solicitud] El carrusel de historias avanza al siguiente elemento y los enlaces principales conservan sus destinos.
8. [Solicitud] Las páginas internas permanecen sin cambios visuales o funcionales por esta limpieza.

## Validación

- Pruebas unitarias: No aplica; el alcance combina contenido Gutenberg y limpieza CSS sin nueva lógica.
- Pruebas de integración: Validar estructura de bloques de la página 47, ausencia de identificadores eliminados, carga del CSS y alcance de selectores conservados.
- Validación manual: Usar Playwright en `1440x900` y `390x900`; revisar orden de secciones, altura, separación, consola, overflow, enlaces, carruseles y elementos fijos.
- Validación productiva: Con autorización específica para contenido, crear backup verificado de la página 47 y del CSS, aplicar únicamente la actualización aprobada, purgar caché, validar HTTP, navegador, logs, contenido final y sincronización.

## Riesgos

- [Inferencia técnica] Una edición directa de `post_content` puede invalidar bloques si no conserva comentarios de apertura y cierre balanceados.
- [Inferencia técnica] La página 47 es contenido persistido; el snapshot del repositorio no sustituye un backup de base de datos ni autoriza la escritura productiva.
- [Inferencia técnica] Eliminar la franja reduce mensajes de confianza; por eso se conservan marcas, historias de éxito y CTA como señales diferenciadas.
- [Inferencia técnica] LiteSpeed puede servir contenido o CSS previo hasta completar la purga.

## Decisiones pendientes

- Ninguna. El usuario aprobó el SPEC y autorizó actualizar la página 47 y el CSS en producción el 11 de agosto de 2026.

## Resultado

- [Producción, 2026-08-11] Se actualizó únicamente el contenido de la página 47 y `assets/css/tmd-home-blocks.css`, después de respaldar la base de datos, el contenido original y el CSS original fuera del directorio público.
- [WordPress, 2026-08-11] `parse_blocks()` y `serialize_blocks()` hicieron roundtrip exacto; quedaron ocho bloques de nivel superior, cero fragmentos libres con contenido y ningún identificador retirado.
- [Playwright, 2026-08-11] En `1440x1000` y `390x844` permanecieron héroe, marcas, equipos, historias, servicios, energía, blog, CTA, footer, contacto y chatbot; no hubo overflow ni errores de consola.
- [Playwright, 2026-08-11] Los carruseles de marcas e historias avanzaron, los enlaces principales conservaron sus destinos y `/equipos/` permaneció aislada del CSS de la portada.
- [Producción, 2026-08-11] LiteSpeed confirmó la purga, la portada respondió HTTP 200 y los logs de los últimos 15 minutos no mostraron errores críticos.
- [Límite] No se abrió la interfaz autenticada del editor Gutenberg; la editabilidad se comprobó mediante el roundtrip exacto del parser de WordPress.
- [Decisión del usuario, 2026-08-11] El control final de `SHA256SUMS` dejó de usarse como criterio de cierre.
