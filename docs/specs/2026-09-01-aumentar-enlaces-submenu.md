# SPEC: Aumentar la legibilidad de los enlaces secundarios del mega menú

## Estado

- Borrador

## Contexto

[Solicitud] Se solicita aumentar el tamaño de la letra de las secciones internas del mega menú, sin aumentar sus títulos; se menciona como referencia que enlaces como `Preventivo` y `Correctivo` deben verse más grandes y que `SERVICIOS` debe conservar su tamaño actual.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:360-375] Los títulos de las columnas usan `.tmd-mm-title` con `font-size: 13px`.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:399-425] Los enlaces secundarios usan `.tmd-mm-items a` con `font-size: 12px`, mientras sus indicadores `›` usan `font-size: 13px`.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:149-165] Las etiquetas superiores, incluido `SERVICIOS`, usan `.tmd-mm-home` y `.tmd-mm-nav-link`, selectores distintos de los enlaces secundarios.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:207-245] Los paneles de Energía y Servicios renderizan sus títulos con `.tmd-mm-title` y sus enlaces internos con `.tmd-mm-items`.

## Problema

[Solicitud] Los enlaces secundarios del mega menú se perciben pequeños y necesitan mayor legibilidad, pero el aumento no debe afectar los títulos de columna ni la etiqueta superior `SERVICIOS`.

## Objetivo

[Solicitud] Aumentar la tipografía de los enlaces secundarios del mega menú, conservando sin cambios los títulos de columna, la barra superior, las imágenes y el comportamiento de navegación.

## Fuera del alcance

- [Solicitud] No modificar `.tmd-mm-title` ni los títulos de las columnas, incluidos `Baterías de plomo`, `BMS`, `Cargadores` y `Mantenimientos`.
- [Solicitud] No modificar `.tmd-mm-home`, `.tmd-mm-nav-link` ni la etiqueta superior `SERVICIOS`.
- [Solicitud] No cambiar textos, enlaces, rutas, imágenes, alturas, paddings, estructura HTML ni comportamiento de apertura/cierre.
- [Regla: AGENTS.md] No modificar producción, hacer commit, push ni despliegue como parte de este cambio local.
- [Regla: AGENTS.md] No modificar archivos de imagen ni el tema padre, WordPress core o plugins de terceros.

## Requisitos funcionales

1. [Solicitud] Los enlaces `.tmd-mm-items a` deben mostrarse con una tipografía mayor que los `12px` actuales; se propone `14px`, igualando la escala de navegación ya aprobada para la barra principal.
2. [Solicitud] Los títulos `.tmd-mm-title` deben conservar su tamaño actual y no deben aumentar como consecuencia del cambio.
3. [Solicitud] La etiqueta superior `SERVICIOS` debe conservar exactamente su tamaño y selector actuales.
4. [Solicitud] El indicador `›` de los enlaces secundarios debe conservar su tamaño actual de `13px`.
5. [Solicitud] La barra superior, la posición del panel y las alturas de imágenes deben conservarse sin cambios.
6. [Regla: docs/domain/NAVIGATION.md] Hover, foco, clic, Escape, clic exterior y navegación por teclado deben conservar su comportamiento.
7. [Regla: docs/domain/NAVIGATION.md] El cambio no debe introducir overflow horizontal en escritorio ni móvil.

## Reglas de negocio

- [Regla: AGENTS.md] El mega menú canónico pertenece al child theme `wp-content/themes/blocksy-child/`.
- [Regla: AGENTS.md] El cambio debe ser focalizado y preservar las clases y el marcado existentes.
- [Regla: AGENTS.md] No se deben inventar ni modificar datos comerciales, imágenes o rutas.

## Contratos

### Entrada

```json
{
  "selector": ".tmd-mm-items a",
  "currentFontSize": "12px",
  "targetFontSize": "14px",
  "unchangedSelectors": [
    ".tmd-mm-title",
    ".tmd-mm-home",
    ".tmd-mm-nav-link",
    ".tmd-mm-items a::before"
  ]
}
```

### Salida

```json
{
  "secondaryLinks": "14px",
  "columnTitles": "unchanged",
  "topServicesLabel": "unchanged",
  "navigationBehavior": "unchanged",
  "imageHeights": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] El aumento puede ampliar el ancho de algunos enlaces en resoluciones intermedias; debe comprobarse que las columnas no desborden.
- [Inferencia técnica] Los enlaces largos deben conservar el ajuste de línea natural sin modificar la estructura ni recortar texto.
- [Regla: docs/domain/NAVIGATION.md] En móvil debe conservarse la ausencia de overflow horizontal y la navegación por teclado.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `docs/specs/2026-08-29-aumentar-tipografia-menu.md` como antecedente de la tipografía de la barra principal, sin ampliar su alcance.
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En los paneles del mega menú, los enlaces secundarios `.tmd-mm-items a` computan `14px` o el tamaño aprobado superior a `12px`.
2. [Solicitud] Los títulos `.tmd-mm-title` conservan su tamaño computado actual y no reciben el aumento.
3. [Solicitud] `SERVICIOS` conserva el mismo tamaño computado que tenía antes del cambio.
4. [Solicitud] Los indicadores `›`, las imágenes, la altura de la barra y la posición del panel conservan sus valores actuales.
5. [Regla: docs/domain/NAVIGATION.md] El mega menú continúa funcionando por hover, foco, clic, Escape, clic exterior y teclado sin errores de consola.
6. [Regla: docs/domain/NAVIGATION.md] No aparece overflow horizontal nuevo en escritorio ni móvil.

## Validación

- Pruebas unitarias: No aplica; el cambio es CSS sin lógica de transformación.
- Pruebas de integración: comprobar que la hoja canónica se carga, que solo `.tmd-mm-items a` recibe el aumento y que los selectores excluidos conservan sus reglas.
- Validación manual: revisar los paneles de Equipos, Energía, Servicios y Nosotros en escritorio y móvil; verificar enlaces, títulos, `SERVICIOS`, imágenes, foco, apertura/cierre y overflow.
- Validación productiva: pendiente de autorización explícita; cualquier despliegue requiere verificación de sincronización, purga de caché y comprobación HTTP/navegador según los runbooks.

## Riesgos

- [Inferencia técnica] Una fuente mayor puede cambiar la altura visual de las filas y aumentar la altura total de algunos paneles.
- [Inferencia técnica] Si el selector se aplica a títulos o a la barra superior por error, incumplirá el alcance y alterará la captura de referencia.
- [Inferencia técnica] Una caché de CSS podría ocultar el cambio después de un eventual despliegue.

## Decisiones pendientes

- [Decisión propuesta, pendiente de aprobación] Se interpreta que `SERVICIOS` se refiere a la etiqueta superior y debe permanecer igual, mientras `Preventivo` y `Correctivo`, como enlaces secundarios, sí deben aumentar.
- [Decisión propuesta, pendiente de aprobación] Se propone aumentar los enlaces secundarios de todos los paneles a `14px`, sin cambiar los títulos de columna ni los indicadores.
