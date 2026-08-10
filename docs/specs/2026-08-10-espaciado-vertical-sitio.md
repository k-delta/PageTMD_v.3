# SPEC: Normalización del espaciado vertical del sitio

## Estado

- Terminado

## Contexto

[Solicitud] El sitio deja demasiado espacio vertical por defecto entre secciones, entre el inicio de algunas páginas y su título, y entre títulos y contenido. La solicitud amplía y reemplaza el ajuste puntual planteado inicialmente para el home.

[Evidencia: https://tecnimontacargas.com/] La auditoría con Playwright del 10 de agosto de 2026 midió en escritorio `86px` arriba y abajo en “Historias de éxito”, `96px` arriba y abajo en “Mantenimiento y soporte especializado” y `72px` arriba y abajo en “Últimos artículos”.

[Evidencia: https://tecnimontacargas.com/equipos/tipos/reach-retractiles/] La plantilla de guías aplica `108px` arriba y abajo a varias secciones en escritorio y `64px` en móvil.

[Evidencia: https://tecnimontacargas.com/mantenimiento/] La plantilla de mantenimiento acumula márgenes superiores de `76–78px` entre secciones en escritorio y `48–50px` en móvil.

[Evidencia: https://tecnimontacargas.com/nosotros/quienes-somos/] El título principal comienza a `152px` del inicio del contenido principal en escritorio y a `96px` en móvil.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css:280] Las guías definen padding de sección mediante `clamp(4rem, 7.5vw, 7rem)`.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home.css:221] Los estilos generales del home definen `78px` de padding vertical por sección.

## Problema

[Solicitud] La escala vertical actual es inconsistente y excesiva en varias plantillas, extiende innecesariamente las páginas y debilita la relación visual entre títulos y su contenido.

## Objetivo

[Solicitud] Crear una escala vertical más compacta, coherente y responsive para las páginas públicas, reduciendo espacios excesivos sin afectar legibilidad, jerarquía, componentes ni funcionalidad.

## Fuera del alcance

- [Solicitud] No rediseñar componentes, tipografías, colores, contenido, navegación ni orden de secciones.
- [Solicitud] No reducir el espacio interno funcional de tarjetas, formularios, carruseles, botones o controles salvo que forme parte directa del encabezado de una sección.
- [Regla: AGENTS.md] No modificar el tema padre, WordPress core ni plugins de terceros.
- [Regla: AGENTS.md] No desplegar ni escribir en producción sin autorización explícita y sin completar los controles productivos aplicables.
- [Solicitud] No imponer un selector global genérico sobre todos los elementos `section`; cada ajuste debe limitarse a una plantilla o componente canónico identificado.

## Requisitos funcionales

1. [Solicitud] Normalizar el espaciado vertical de las páginas públicas mediante tres categorías: inicio de página o héroe, separación entre secciones y relación entre título y contenido.
2. [Solicitud] Los inicios de página o héroes deben usar como referencia máxima `64px` en escritorio y `40px` en móvil, salvo que una composición visual necesite una altura propia documentada.
3. [Solicitud] Las secciones de contenido deben usar como referencia `48px` arriba y abajo en escritorio y `32px` arriba y abajo en móvil.
4. [Solicitud] Cuando dos secciones adyacentes aporten espacio por ambos lados, el espacio visual resultante no debe duplicar la referencia de sección.
5. [Solicitud] La separación entre un título de sección y su contenido asociado debe quedar entre `16px` y `24px`; un eyebrow o subtítulo perteneciente al encabezado puede conservar su separación interna proporcional.
6. [Solicitud] Los ajustes deben aplicarse por plantilla o componente en el home, catálogos de equipos y energía, guías de tipos de equipos, mantenimiento, quiénes somos, blog y contacto.
7. [Solicitud] Otras páginas públicas que reutilicen exactamente las mismas clases corregidas deben heredar la mejora; las que usen una estructura distinta deben permanecer sin cambios hasta ser auditadas.
8. [Solicitud] El contenido, los enlaces, formularios, carruseles y demás comportamientos interactivos deben conservarse.
9. [Regla: AGENTS.md] La presentación debe permanecer usable y sin solapamientos en escritorio y móvil.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] El ajuste no puede inventar ni alterar equipos, marcas, modelos, imágenes, precios, disponibilidad o hechos comerciales.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- [Solicitud] Una sección dinámica sin resultados no debe conservar un contenedor vacío con altura o separación excesiva.
- [Solicitud] Los carruseles deben conservar flechas, indicadores y sombras sin recorte.
- [Solicitud] Los títulos que ocupen dos o más líneas deben conservar separación suficiente respecto del contenido siguiente.
- [Solicitud] Las páginas con barra administrativa, mensajes de estado o breadcrumbs no deben sufrir solapamientos por el ajuste.
- [Regla: AGENTS.md] En móvil no debe aparecer desplazamiento horizontal, contenido recortado ni superposición entre secciones.

## Archivos o módulos relacionados

- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home.css:221] Home alternativo y secciones generales.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:168] Bloques activos del home.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-blog.css:223] Blog, artículos y bloque de últimas noticias.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-catalog.css:36] Catálogo de equipos.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-energy-catalog.css:40] Catálogo de energía.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css:280] Guías de tipos de equipos.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-maintenance.css:172] Páginas de mantenimiento.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-about.css:21] Página “Quiénes somos”.
- [Evidencia: wp-content/themes/blocksy-child/functions.php:1947] Estilos específicos de contacto.
- [Evidencia: wp-content/plugins/tm-equipos-destacados-v2/assets/css/tm-equipos-destacados.css:20] Componente de equipos destacados del home.

## Criterios de aceptación

1. [Solicitud] En las plantillas incluidas, ningún inicio de página o héroe supera la referencia de `64px` en escritorio o `40px` en móvil sin una excepción visual documentada en el diff.
2. [Solicitud] En las plantillas incluidas, las secciones de contenido usan como referencia `48px` verticales en escritorio y `32px` en móvil, sin espacios duplicados entre bloques adyacentes.
3. [Solicitud] Los títulos de sección quedan separados de su contenido asociado entre `16px` y `24px` en los casos auditados.
4. [Solicitud] En el home, “Equipos destacados”, “Historias de éxito”, “Mantenimiento y soporte especializado” y “Últimos artículos” se perciben más próximas y mantienen una escala coherente.
5. [Solicitud] En escritorio de `1440px` y móvil de `390px`, no existen solapamientos, recortes ni desplazamiento horizontal introducidos por el cambio.
6. [Solicitud] Carruseles, enlaces, botones y formularios de las plantillas incluidas conservan su funcionamiento y controles visibles.
7. [Solicitud] Las páginas internas fuera de las plantillas o clases auditadas no reciben cambios visuales accidentales.

## Validación

- Pruebas unitarias: No aplica; el alcance es CSS de presentación sin lógica de negocio.
- Pruebas de integración: Comprobar carga de cada asset CSS modificado y confirmar que los selectores se limitan a las plantillas o componentes previstos.
- Validación manual: Usar Playwright en `1440x900` y `390x900` sobre home, catálogo de equipos, una guía de tipo, catálogo de energía, mantenimiento, quiénes somos, blog y contacto. Medir padding/margin computados, comparar capturas antes/después y revisar consola, recorte, solapamiento y desplazamiento horizontal.
- Validación productiva: Pendiente de autorización explícita. Si se autoriza, seguir los runbooks, crear backup verificable cuando corresponda, desplegar solo archivos modificados, purgar caché, repetir la matriz Playwright sobre producción y ejecutar `./scripts/sync-production.sh --check`.

## Riesgos

- [Inferencia técnica] El home combina bloques administrados, estilos del tema y plugins propios; un selector amplio puede afectar páginas internas.
- [Inferencia técnica] Reducir simultáneamente padding y margin adyacentes puede compactar más de lo esperado si no se mide el espacio resultante.
- [Inferencia técnica] Algunas páginas ocultan títulos nativos y construyen su propio héroe; no todas admiten la misma regla de inicio de página.
- [Inferencia técnica] Los selectores generados por Kadence contienen identificadores de bloque y requieren conservar compatibilidad con el contenido activo.

## Decisiones pendientes

- Ninguna. La escala fue aprobada por el usuario el 10 de agosto de 2026.

## Evidencia de cierre

- [Evidencia: Playwright, 2026-08-10] Se probaron los CSS locales sobre home, equipos, guía de tipo, energía, mantenimiento, quiénes somos, blog y contacto en `1440x900` y `390x900`; no se detectó desplazamiento horizontal.
- [Evidencia: Playwright, 2026-08-10] La cascada del home se reprodujo interceptando las respuestas CSS cacheadas de LiteSpeed en su posición real de carga. Equipos destacados, Historias de éxito, Mantenimiento y soporte y Últimos artículos resolvieron a `48px` arriba/abajo en escritorio y `32px` en móvil.
- [Evidencia: Playwright, 2026-08-10] Guías resolvió el héroe a `64px` en escritorio y `40px` en móvil, y las secciones a `48px` y `32px`, respectivamente.
- [Evidencia: Playwright, 2026-08-10] Mantenimiento resolvió márgenes de sección a `48px` en escritorio y `32px` en móvil; quiénes somos eliminó el margen duplicado del héroe y dejó el contenido en `48px`/`32px`; blog quedó en `48px`/`32px`.
- [Evidencia: Playwright, 2026-08-10] El carrusel de historias avanzó de “Historia 1” a “Historia 2”, sus controles permanecieron visibles y el enlace de mantenimiento conservó `/mantenimiento/mantenimiento-preventivo/`.
- [Evidencia: php -l, 2026-08-10] `wp-content/themes/blocksy-child/functions.php` no presenta errores de sintaxis.
- [Evidencia: git diff --check, 2026-08-10] El diff no presenta errores de whitespace.
- [Evidencia: revisión focalizada, 2026-08-10] Los hallazgos sobre mediciones y cascada se resolvieron con pruebas Playwright adicionales; no quedaron hallazgos Critical o Important abiertos.
