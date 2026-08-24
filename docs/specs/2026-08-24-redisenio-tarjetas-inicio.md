# SPEC: Rediseño de las tarjetas visuales de la portada

## Estado

- Aprobado

## Contexto

[Solicitud] La sección mostrada en la captura actual debe recuperar la composición visual de la segunda captura de referencia, manteniendo sus imágenes de fondo, sus textos y la paleta amarilla ya utilizada por el sitio.

[Evidencia: captura actual aportada por el usuario, 2026-08-24] La sección usa una tarjeta principal alta para “Montacargas” y dos tarjetas apiladas para “Baterías” y “Cargadores”; las tres presentan una superposición azul oscura uniforme, títulos amarillos y botones azules.

[Evidencia: captura de referencia aportada por el usuario, 2026-08-24] El estilo objetivo usa tarjetas claras con sombra suave, esquinas redondeadas, contenido oscuro sobre una superficie blanca y las imágenes concentradas hacia el fondo o el costado derecho. Los títulos y el acento visual usan amarillo, mientras los botones usan azul oscuro.

[Evidencia: producción https://tecnimontacargas.com/, 2026-08-24] La sección corresponde al bloque Kadence `47_83d64e-ce`, con una tarjeta principal `47_ba091a-80` y las tarjetas secundarias `47_6f15fb-12` y `47_a1bb23-84`.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-equipment-section-redirects.php:22] En la portada, un filtro activo transforma el contenido persistido de Energía para mostrar “Montacargas”, “Equipos para cada operación”, el texto comercial de compra y alquiler y “Ver Equipos”.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:15] La fuente versionada actual contiene el reemplazo solicitado del fondo de Cargadores por `assets/img/mega-menu/energy-cargadores.png`; este recurso debe conservarse en el rediseño.

## Problema

[Solicitud] La superposición oscura actual reduce el protagonismo de las imágenes y produce una composición pesada. La sección debe adoptar la jerarquía más clara y comercial de la referencia, sin cambiar su contenido ni abandonar la identidad cromática de Tecnimontacargas.

## Objetivo

[Solicitud] Rediseñar exclusivamente la presentación de las tres tarjetas de la portada para obtener una composición clara, limpia y responsive inspirada en la segunda captura: tarjeta principal a la izquierda, dos tarjetas secundarias a la derecha, imágenes visibles y textos legibles, conservando contenido, imágenes y destinos actuales.

## Fuera del alcance

- [Solicitud] Cambiar, resumir, corregir o reordenar los textos visibles de Montacargas, Baterías o Cargadores.
- [Solicitud] Sustituir o generar nuevas imágenes para las tres tarjetas.
- [Solicitud] Cambiar los destinos `/equipos/`, `/energia/baterias/` y `/energia/cargadores/`.
- [Solicitud] Rediseñar otras secciones de la portada o páginas internas.
- [Regla: AGENTS.md] Modificar el contenido persistido de WordPress, el tema padre, WordPress core o plugins de terceros.
- [Regla: AGENTS.md] Desplegar, purgar caché o escribir en producción sin autorización explícita posterior, backup verificado y control de deriva.

## Requisitos funcionales

1. [Solicitud] En escritorio, la sección debe conservar una tarjeta principal a la izquierda y dos tarjetas secundarias apiladas a la derecha.
2. [Solicitud] Las tres tarjetas deben usar una superficie predominantemente clara, esquinas redondeadas y una sombra suave coherente con la captura de referencia.
3. [Solicitud] La imagen existente de cada tarjeta debe continuar como fondo: `montacargas.jpeg` en la principal, `bateria.jpeg` en Baterías y `energy-cargadores.png` en Cargadores.
4. [Solicitud] Las imágenes deben permanecer reconocibles y concentrarse visualmente hacia el fondo o el costado derecho, con un degradado claro que mantenga legible el contenido sin una capa azul oscura uniforme.
5. [Solicitud] La tarjeta principal debe conservar visibles “Montacargas”, “Equipos para cada operación”, “Montacargas para compra y alquiler, seleccionados según capacidad, altura de levante y condiciones de operación.” y “Ver Equipos”.
6. [Solicitud] Las tarjetas secundarias deben conservar sus títulos, párrafos y botones actuales: “Baterías / Ver Baterías” y “Cargadores / Ver Cargadores”.
7. [Solicitud] Los títulos deben usar el amarillo de la paleta actual (`#FFC33C`) y el texto debe usar el azul oscuro de marca (`#262E4F`) o un tono de contraste equivalente ya definido por el tema.
8. [Solicitud] Los botones deben adoptar el azul oscuro de marca como estado normal, con texto blanco, sombra discreta y una interacción hover/focus visible basada en el amarillo existente.
9. [Solicitud] La etiqueta “Equipos para cada operación” debe presentarse como un acento compacto tipo píldora o banda, usando colores de la paleta actual y sin cambiar su texto.
10. [Evidencia: docs/specs/2026-08-11-proporciones-portada.md] La sección debe conservar un ancho máximo de `1180px`, separación vertical de `48px` en escritorio y `32px` en móvil, y gutters laterales mínimos de `16px`.
11. [Solicitud] En móvil, las tres tarjetas deben apilarse en una sola columna, conservar la jerarquía principal-secundarias y ajustar fondos, padding y tipografía sin recortar texto, imágenes ni controles.
12. [Regla: AGENTS.md] Los selectores del cambio deben limitarse a `body.page-id-47` y no modificar bloques Kadence similares en otras páginas.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] No se deben inventar imágenes, equipos, precios, disponibilidad ni hechos comerciales.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-equipment-section-redirects.php:22] El texto público canónico para la tarjeta principal es el resultado del filtro activo de Montacargas y debe preservarse.

## Contratos

### Entrada

```json
{
  "pageId": 47,
  "layout": "una tarjeta principal y dos secundarias",
  "content": "texto y enlaces actuales",
  "images": ["montacargas.jpeg", "bateria.jpeg", "energy-cargadores.png"]
}
```

### Salida

```json
{
  "desktop": "composicion clara de dos columnas",
  "mobile": "tres tarjetas apiladas sin overflow",
  "contentChanged": false,
  "imagesChanged": false,
  "linksChanged": false
}
```

## Casos límite

- [Solicitud] Los párrafos de varias líneas deben permanecer completamente visibles cuando cambie el ancho del viewport o el tamaño del texto.
- [Solicitud] La imagen de Cargadores, de orientación vertical, debe integrarse sin dejar una franja plana dominante ni perder el equipo como sujeto principal.
- [Solicitud] El degradado claro debe sostener contraste suficiente aunque el punto focal de una imagen quede detrás del texto.
- [Regla: AGENTS.md] No debe aparecer overflow horizontal, superposición entre tarjetas ni contenido recortado en `1440x900`, `768x1024` y `390x844`.
- [Solicitud] Hover y focus-visible deben conservar legibilidad y no desplazar el contenido.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/energy-cargadores.png`
- `wp-content/themes/blocksy-child/inc/tmd-equipment-section-redirects.php` (solo referencia; no requiere modificación)
- `docs/specs/2026-08-11-proporciones-portada.md`
- `docs/specs/2026-08-03-ctas-home.md`

## Criterios de aceptación

1. [Solicitud] En `1440x900`, la sección conserva la tarjeta principal a la izquierda y las dos secundarias apiladas a la derecha, alineadas dentro de un ancho máximo de `1180px`.
2. [Solicitud] Las tres tarjetas se perciben predominantemente claras, con bordes redondeados, sombra suave, títulos amarillos, texto oscuro y botones azul oscuro, siguiendo la composición de la referencia sin copiar contenido nuevo.
3. [Solicitud] Las tres imágenes existentes permanecen visibles y reconocibles; Cargadores usa `energy-cargadores.png` y no `cargadores2.jpeg`.
4. [Solicitud] El DOM visible conserva exactamente los textos actuales y los CTA mantienen `/equipos/`, `/energia/baterias/` y `/energia/cargadores/`.
5. [Solicitud] En `390x844`, las tarjetas se apilan, todos los textos y botones quedan visibles y accionables, y las imágenes mantienen un encuadre útil.
6. [Regla: AGENTS.md] En `1440x900`, `768x1024` y `390x844` no existen solapamientos, recortes ni overflow horizontal introducidos por el cambio.
7. [Solicitud] La sección anterior, Últimos artículos y una página interna con bloques Kadence no reciben cambios visuales por estos selectores.
8. [Solicitud] Los estados hover, focus y focus-visible de los tres CTA son perceptibles y conservan contraste suficiente.

## Validación

- Pruebas unitarias: No aplica; el cambio es exclusivamente CSS de presentación.
- Pruebas de integración: Verificar carga de `tmd-home-blocks.css`, alcance `body.page-id-47`, correspondencia de los tres fondos, textos visibles y destinos de CTA.
- Validación manual: Comparar capturas en `1440x900`, `768x1024` y `390x844`; revisar proporciones, encuadre, contraste, sombras, radios, wrapping, hover, focus-visible, consola y overflow horizontal.
- Validación productiva: Pendiente de autorización explícita. Después de aprobar un despliegue: consultar runbooks, crear backup focalizado, ejecutar `./scripts/sync-production.sh --check`, desplegar únicamente el CSS afectado, purgar LiteSpeed y comprobar HTTP, navegador, consola, logs, responsive y sincronización final.

## Riesgos

- [Inferencia técnica] Kadence genera estilos con selectores específicos y pseudo-elementos de overlay; el CSS focalizado necesitará especificidad suficiente para sustituirlos sin alterar el contenido.
- [Inferencia técnica] El fondo vertical de Cargadores requiere un `background-position` específico para equilibrar imagen y texto en distintos anchos.
- [Inferencia técnica] Una superficie demasiado transparente puede reducir el contraste; una demasiado opaca puede ocultar las imágenes que la solicitud exige conservar.
- [Inferencia técnica] LiteSpeed puede mantener temporalmente el estilo anterior después de un eventual despliegue hasta completar la purga.

## Decisiones pendientes

- Ninguna. SPEC aprobado explícitamente por el usuario el 24 de agosto de 2026.
