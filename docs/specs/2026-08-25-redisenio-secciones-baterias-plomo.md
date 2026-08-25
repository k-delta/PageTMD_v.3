# SPEC: Rediseño visual de secciones en Baterías de plomo

## Estado

- Aprobado

## Contexto

[Solicitud: capturas aportadas el 2026-08-25] La página de Baterías de plomo presenta las secciones “¿Cuándo elegir una batería de plomo-ácido?” y “Criterios para seleccionar la batería” con fondos, tarjetas y listas casi completamente blancos, por lo que tienen poca diferenciación visual.

[Solicitud: Imagen #2 aportada el 2026-08-25] La sección “Compatibilidad antes que velocidad” sirve como guía para incorporar contraste mediante una tarjeta azul oscura, una tarjeta blanca con acento amarillo, una tarjeta azul grisácea y tres iconos semánticos.

[Solicitud: Imagen #3 aportada el 2026-08-25] La sección “Qué revisamos para recomendar un cargador” sirve como guía para presentar “Criterios para seleccionar la batería” sobre un fondo gris claro, con texto a la izquierda y los criterios en tarjetas compactas a la derecha.

[Solicitud: capturas de revisión aportadas el 2026-08-25] Las tres tarjetas resultantes deben ser considerablemente más compactas; el título de “Operaciones programadas” debe mostrarse blanco sobre la tarjeta azul. El fondo gris de “Criterios para seleccionar la batería” debe quedar centrado y limitado al mismo ancho de las demás secciones, sin la línea amarilla inferior de ancho completo.

[Evidencia: production-snapshot/pages.json:283] La página versionada de referencia tiene ID `401`, slug `plomo` y utiliza el contenedor `tmd-energy-inner--plomo`; el contenido persistido no es fuente canónica para implementar estilos, pero confirma el identificador y la estructura histórica disponibles para focalizar el ajuste.

[Evidencia: wp-content/themes/blocksy-child/style.css:1693] Los estilos compartidos actuales de Energía aplican fondo blanco, borde claro y sombra uniforme a todas las tarjetas `.tmd-energy-card`.

[Evidencia: wp-content/themes/blocksy-child/style.css:1735] El bloque compartido `.tmd-energy-split` solo añade un borde superior y su checklist usa tarjetas blancas con un check azul.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-energy-structure.php:119] La página de Cargadores ya contiene una implementación focalizada del lenguaje visual de la Imagen #3: fondo gris, composición dividida, acento amarillo y checklist en dos columnas.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-energy-structure.php:227] La página de Cargadores también contiene una implementación focalizada del lenguaje visual de la Imagen #2 con tres tarjetas de colores e iconos SVG lineales.

## Problema

[Solicitud] Las dos secciones informativas de Baterías de plomo carecen de contraste y jerarquía visual. Deben adoptar el lenguaje gráfico de las referencias aportadas sin modificar títulos, párrafos, criterios ni su orden.

## Objetivo

[Solicitud] Mejorar la diferenciación visual, jerarquía y legibilidad de “¿Cuándo elegir una batería de plomo-ácido?” y “Criterios para seleccionar la batería”, reutilizando el lenguaje azul, amarillo y gris de las referencias y conservando íntegramente el contenido visible actual.

## Fuera del alcance

- [Solicitud] Cambiar, corregir, resumir, reordenar o eliminar títulos, párrafos o elementos de lista.
- [Solicitud] Rediseñar el hero, CTA u otras secciones de Baterías de plomo.
- [Solicitud] Modificar la página de Cargadores o copiar literalmente sus textos.
- [Regla: AGENTS.md] Modificar el contenido persistido de WordPress, el tema padre, WordPress core o plugins de terceros.
- [Regla: AGENTS.md] Desplegar, purgar caché o escribir en producción sin autorización explícita posterior, backup verificado y control de deriva.

## Requisitos funcionales

1. [Solicitud] El título, los tres títulos de tarjeta, los tres párrafos y su orden en “¿Cuándo elegir una batería de plomo-ácido?” deben permanecer exactamente iguales al contenido visible al iniciar la implementación.
2. [Solicitud] La sección “¿Cuándo elegir una batería de plomo-ácido?” debe conservar tres tarjetas alineadas en escritorio y adoptar el lenguaje visual de la Imagen #2: primera tarjeta azul oscura, segunda blanca con acento amarillo y tercera azul grisácea clara.
3. [Solicitud] Las tres tarjetas deben ser considerablemente más compactas que en la primera implementación, conservar borde superior de color, esquinas redondeadas, sombra suave, separación uniforme y altura visual alineada.
4. [Solicitud] La tarjeta “Operaciones programadas” debe mostrar un icono de calendario; la tarjeta de compatibilidad debe mostrar un icono de conector o enlace técnico; la tarjeta “Mantenimiento preventivo” debe mostrar un icono de llave de mantenimiento.
5. [Solicitud] Los tres iconos deben pertenecer a una misma familia vectorial lineal, compartir grosor, terminaciones redondeadas, tamaño y peso visual, y no depender de emojis o glifos tipográficos del sistema.
6. [Solicitud] Los iconos deben usar amarillo de marca `#ffc33c` dentro de cajas visuales coherentes con el color de cada tarjeta, sin solaparse con títulos o párrafos.
7. [Solicitud] El título, el párrafo introductorio y todos los criterios de “Criterios para seleccionar la batería” deben permanecer exactamente iguales al contenido visible al iniciar la implementación.
8. [Solicitud] “Criterios para seleccionar la batería” debe adoptar el lenguaje visual de la Imagen #3: fondo gris claro centrado y limitado al mismo ancho de las demás secciones, texto en la columna izquierda y criterios en tarjetas compactas en la columna derecha.
9. [Solicitud] La columna izquierda debe incorporar una línea corta de acento amarillo bajo el párrafo, y la derecha debe presentar los criterios en dos columnas en escritorio con indicadores de check amarillos.
10. [Solicitud] Cuando la cantidad de criterios sea impar, la última tarjeta debe ocupar el ancho disponible de la cuadrícula, como en la referencia, sin cambiar su posición relativa en la lista.
11. [Solicitud] En móvil, ambas secciones deben pasar a una sola columna, conservar el orden actual y mantener todo el contenido visible sin recortes, solapamientos ni overflow horizontal.
12. [Regla: AGENTS.md] Los estilos y cualquier marcado auxiliar deben limitarse a la página de Baterías de plomo, identificada por `body.page-id-401` o su contenedor específico `tmd-energy-inner--plomo`, sin alterar componentes compartidos en otras páginas.
13. [Regla: AGENTS.md] El cambio debe implementarse en el child theme canónico y sin añadir dependencias externas.
14. [Solicitud] El título de “Operaciones programadas” debe mostrarse blanco sobre la tarjeta azul, incluidos los elementos de texto anidados que WordPress pueda generar dentro del encabezado.
15. [Solicitud] “Criterios para seleccionar la batería” no debe mostrar una línea amarilla inferior extendida de esquina a esquina; solo debe conservar la línea corta de acento bajo el texto de la columna izquierda.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] No deben inventarse ni modificarse equipos, especificaciones, compatibilidades, precios, disponibilidad o hechos comerciales.

## Contratos

### Entrada

```json
{
  "pageId": 401,
  "pageContainer": "tmd-energy-inner--plomo",
  "sections": [
    "¿Cuándo elegir una batería de plomo-ácido?",
    "Criterios para seleccionar la batería"
  ],
  "contentPolicy": "preservar contenido visible actual"
}
```

### Salida

```json
{
  "firstSection": "tres tarjetas azul, blanca y azul grisácea con iconos SVG semánticos",
  "secondSection": "bloque gris centrado, sin línea inferior de ancho completo y con checklist técnico amarillo",
  "contentChanged": false,
  "otherSectionsChanged": false,
  "otherPagesChanged": false
}
```

## Casos límite

- [Solicitud] Un título o párrafo que ocupe varias líneas debe mostrarse completo y mantener una separación consistente respecto del icono.
- [Inferencia técnica] El contenido persistido puede incluir clases u otros atributos generados por WordPress; la focalización del diseño no debe depender de que los encabezados carezcan de atributos.
- [Solicitud] El icono de compatibilidad debe ser inequívocamente técnico y no confundirse con el calendario o la herramienta de mantenimiento.
- [Solicitud] Si el checklist visible contiene más o menos elementos que la referencia, todos deben conservarse y la cuadrícula debe adaptarse sin huecos artificiales.
- [Inferencia técnica] Las reglas responsive deben prevalecer sobre las columnas nativas de WordPress en los puntos de quiebre donde estas se apilan.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-energy-structure.php`
- `wp-content/themes/blocksy-child/style.css` (solo referencia de estilos compartidos; no se prevé modificarlo)
- `wp-content/themes/blocksy-child/functions.php` (solo referencia de carga del módulo)
- Prueba focalizada nueva para estructura y selectores de Baterías de plomo.

## Criterios de aceptación

1. [Solicitud] Los títulos, párrafos, elementos de lista y su orden en las dos secciones coinciden exactamente con el contenido visible anterior al cambio.
2. [Solicitud] En escritorio, “¿Cuándo elegir una batería de plomo-ácido?” muestra tres tarjetas alineadas: azul oscura, blanca con acento amarillo y azul grisácea clara.
3. [Solicitud] Las tres tarjetas muestran respectivamente un calendario, un conector o enlace técnico y una llave de mantenimiento, todos como SVG lineales coherentes y en amarillo `#ffc33c`.
4. [Solicitud] Ningún icono invade el título o el párrafo y las tres tarjetas mantienen altura compacta, bordes, sombras y separación consistentes; “Operaciones programadas” se lee en blanco sobre el fondo azul.
5. [Solicitud] “Criterios para seleccionar la batería” muestra un fondo gris claro centrado y del mismo ancho que las demás secciones, contenido introductorio a la izquierda con una línea amarilla corta y checklist a la derecha en tarjetas con checks amarillos.
6. [Solicitud] En escritorio, el checklist usa dos columnas y, si la cantidad es impar, su última tarjeta ocupa el ancho completo disponible.
7. [Solicitud] En `390x844`, las tarjetas y el checklist se apilan en el orden existente sin texto recortado, superposición ni overflow horizontal.
8. [Regla: AGENTS.md] El ajuste solo actúa en la página 401 o dentro de `tmd-energy-inner--plomo`; Cargadores y las demás páginas que usan `.tmd-energy-card`, `.tmd-energy-split` o `.tmd-energy-checklist` no cambian.
9. [Solicitud] El hero, CTA y demás secciones de Baterías de plomo permanecen visual y funcionalmente fuera del cambio.
10. [Solicitud] No existe una línea amarilla inferior ni un fondo gris extendidos de esquina a esquina en “Criterios para seleccionar la batería”.

## Validación

- Pruebas unitarias: No aplica; el cambio es de presentación focalizada.
- Pruebas de integración: Añadir una prueba focalizada que compruebe la restricción a página 401 o `tmd-energy-inner--plomo`, los tres SVG semánticos, los colores requeridos, la cuadrícula del checklist y las reglas responsive; verificar que los selectores no alcanzan `page-id-255` ni estilos globales sin ámbito.
- Validación manual: Comparar ambas secciones con las tres capturas aportadas en escritorio y `390x844`; comprobar contenido, orden, colores, iconos, alturas, saltos de línea, consola, carga de estilos y overflow.
- Validación productiva: Pendiente de autorización explícita. Después de aprobar un despliegue: consultar runbooks, crear backup focalizado, ejecutar `./scripts/sync-production.sh --check`, desplegar únicamente los archivos modificados, purgar LiteSpeed y verificar HTTP, navegador, consola, responsive y sincronización final.

## Riesgos

- [Inferencia técnica] Reutilizar selectores globales de Energía sin el ámbito de página puede cambiar Cargadores u otras páginas que comparten las mismas clases.
- [Inferencia técnica] Usar el orden de las tarjetas como único identificador vincula cada icono a la estructura persistida; la validación debe detectar un reordenamiento futuro para evitar iconos semánticamente incorrectos.
- [Inferencia técnica] El padding interno del fondo gris centrado debe incluirse en su ancho para no superar el contenedor de WordPress.
- [Inferencia técnica] LiteSpeed puede conservar temporalmente la presentación anterior tras un eventual despliegue hasta completar la purga de caché.

## Decisiones pendientes

- Ninguna.
