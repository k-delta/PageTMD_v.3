# SPEC: Estilo colorido de compatibilidad en Cargadores

## Estado

- Aprobado

## Contexto

[Solicitud] En `https://tecnimontacargas.com/energia/cargadores/`, la sección “Compatibilidad antes que velocidad” debe cambiar del estilo claro uniforme mostrado en la Imagen #2 al tratamiento con más colores mostrado en la Imagen #1.

[Evidencia: producción https://tecnimontacargas.com/energia/cargadores/, 2026-08-25] La respuesta HTML incluye el bloque `tmd-energy-charger-page-adjustments` con los fondos, bordes superiores, sombras e iconos del diseño solicitado, pero el título se entrega como `<h2 class="wp-block-heading">Compatibilidad antes que velocidad</h2>` y no recibe la clase `tmd-energy-compatibility-title` necesaria para activar esos selectores.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-energy-structure.php:55] El filtro actual solo reconoce la cadena exacta `<h2>Compatibilidad antes que velocidad</h2>`, que no coincide con el marcado producido por WordPress en la página.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-energy-structure.php:93] El código canónico ya define el diseño de referencia de la Imagen #1 y limita sus selectores a `body.page-id-255` y a las tarjetas inmediatamente posteriores al título marcado.

## Problema

[Solicitud] La sección continúa mostrando tres tarjetas claras y visualmente uniformes porque el estilo colorido ya disponible no se activa sobre el marcado real de WordPress.

## Objetivo

[Solicitud] Hacer que la sección “Compatibilidad antes que velocidad” de la página de Cargadores adopte el tratamiento visual de la Imagen #1, conservando intactos sus textos y el resto de la página.

## Fuera del alcance

- [Solicitud] Cambiar los textos “Voltaje correcto”, “Capacidad adecuada”, “Instalación segura” o sus descripciones.
- [Solicitud] Rediseñar otras secciones de `/energia/cargadores/` u otras páginas de Energía.
- [Regla: AGENTS.md] Modificar el contenido persistido de WordPress, el tema padre, WordPress core o plugins de terceros.
- [Regla: AGENTS.md] Desplegar, purgar caché o escribir en producción sin autorización explícita posterior, backup verificado y control de deriva.

## Requisitos funcionales

1. [Solicitud] El título “Compatibilidad antes que velocidad” debe conservar su texto y mostrarse centrado sobre las tres tarjetas en escritorio.
2. [Solicitud] La tarjeta “Voltaje correcto” debe usar fondo azul oscuro, borde superior azul, título blanco, texto blanco atenuado e icono de rayo amarillo dentro de un recuadro azulado.
3. [Solicitud] La tarjeta “Capacidad adecuada” debe usar fondo blanco, borde superior naranja e icono de batería naranja dentro de un recuadro crema.
4. [Solicitud] La tarjeta “Instalación segura” debe usar fondo azul grisáceo claro, borde superior azul oscuro e icono de engranaje naranja dentro de un recuadro blanco.
5. [Solicitud] Las tres tarjetas deben conservar esquinas redondeadas, sombra suave, alturas alineadas y separación uniforme, de acuerdo con la Imagen #1.
6. [Regla: AGENTS.md] El cambio debe limitarse a la página de Cargadores, identificada por `body.page-id-255`, y no debe alterar tarjetas similares en otras páginas.
7. [Solicitud] En móvil, las tarjetas deben conservar el orden actual, el tratamiento de color y todo su contenido visible sin solapamientos ni overflow horizontal.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] No se deben inventar ni modificar hechos comerciales, equipos, precios o disponibilidad.

## Contratos

### Entrada

```json
{
  "pageId": 255,
  "sectionTitle": "Compatibilidad antes que velocidad",
  "headingMarkup": "encabezado de WordPress con atributos permitidos"
}
```

### Salida

```json
{
  "sectionStyle": "tratamiento colorido de la Imagen #1",
  "contentChanged": false,
  "otherSectionsChanged": false
}
```

## Casos límite

- [Evidencia: producción https://tecnimontacargas.com/energia/cargadores/, 2026-08-25] El encabezado puede incluir la clase `wp-block-heading`; la activación visual no debe depender de que el `<h2>` carezca de atributos.
- [Inferencia técnica] Si WordPress conserva otros atributos válidos en el encabezado, el contenido visible y esos atributos deben preservarse.
- [Solicitud] Los textos de varias líneas deben permanecer completamente visibles en escritorio y móvil.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-energy-structure.php`
- `wp-content/themes/blocksy-child/functions.php` (solo referencia de carga del módulo)
- `tests/test-energy-compatibility-title.php`

## Criterios de aceptación

1. [Solicitud] En escritorio, la sección coincide visualmente con la Imagen #1: primera tarjeta azul oscura, segunda blanca con acento naranja y tercera azul grisácea clara.
2. [Solicitud] Cada tarjeta muestra su icono y borde superior con los colores correspondientes de la referencia.
3. [Solicitud] El título y los seis textos existentes permanecen sin cambios.
4. [Evidencia: producción https://tecnimontacargas.com/energia/cargadores/, 2026-08-25] El marcado real con `class="wp-block-heading"` activa el estilo focalizado de compatibilidad.
5. [Regla: AGENTS.md] Ninguna otra sección de Cargadores ni otra página recibe cambios visuales por este ajuste.
6. [Solicitud] En `390x844`, no hay texto recortado, superposición ni overflow horizontal introducido por el cambio.

## Validación

- Pruebas unitarias: No aplica; el cambio es un ajuste focalizado del marcado y su presentación.
- Pruebas de integración: Probar el filtro con un encabezado sin atributos y con `class="wp-block-heading"`; verificar que conserva atributos, contenido y estructura, y que no actúa fuera de la página 255.
- Validación manual: Revisar la sección en escritorio y `390x844`; comparar colores, iconos, bordes, sombras, alineación, textos, consola y overflow con la Imagen #1.
- Validación productiva: Pendiente de autorización explícita. Después de aprobar un despliegue: consultar runbooks, crear backup focalizado, ejecutar `./scripts/sync-production.sh --check`, desplegar solo el archivo modificado, purgar LiteSpeed y verificar HTTP, navegador, consola, responsive y sincronización final.

## Riesgos

- [Inferencia técnica] Una sustitución demasiado amplia del encabezado podría afectar otro contenido o duplicar la clase al procesar el filtro más de una vez.
- [Inferencia técnica] LiteSpeed puede seguir mostrando el HTML anterior después de un eventual despliegue hasta completar la purga de caché.

## Decisiones pendientes

- Ninguna.
