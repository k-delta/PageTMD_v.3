# SPEC: Aumentar la tipografía de la barra principal del menú

## Estado

- Aprobado

## Contexto

[Solicitud] Se solicita aumentar el tamaño de la letra del menú sin modificar la altura del menú ni la altura de sus imágenes.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:145-166] Las etiquetas principales usan `.tmd-mm-home` y `.tmd-mm-nav-link`, con `font-size: 12px`, `min-height: 34px`, `padding: 6px 11px` y `line-height: 1`.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:29-42] La barra principal conserva `min-height: 56px` y el panel desplegable inicia en `top: 56px`.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:287-306] Las tarjetas de imagen del mega menú tienen alturas explícitas de `100px` y, para Nosotros y Servicios, `150px`.

## Problema

[Solicitud] Las etiquetas de la barra principal del menú deben tener una letra más grande, pero el cambio no debe hacer crecer la barra, desplazar el panel ni alterar las imágenes.

## Objetivo

[Solicitud] Aumentar la legibilidad de las etiquetas principales del menú conservando exactamente la altura de la barra y las alturas de las imágenes del mega menú.

## Fuera del alcance

- [Solicitud] No modificar la altura de la barra, la posición vertical del panel, sus paddings ni la estructura HTML.
- [Solicitud] No modificar la altura, proporción, fondo o recorte de las imágenes del mega menú.
- [Inferencia técnica] No cambiar los textos, enlaces, rutas, comportamiento de apertura/cierre ni navegación por teclado.
- [Regla: AGENTS.md] No modificar producción, hacer commit, push ni despliegue como parte de este cambio local.
- [Regla: AGENTS.md] No modificar los archivos de imagen que ya presentan cambios locales ajenos a esta solicitud.

## Requisitos funcionales

1. [Solicitud] Las etiquetas `.tmd-mm-home` y `.tmd-mm-nav-link` deben mostrarse con una tipografía mayor que los `12px` actuales.
2. [Solicitud] La barra principal debe conservar `min-height: 56px`, y el panel debe conservar su inicio en `top: 56px`.
3. [Solicitud] Las alturas de `.tmd-mm-img` deben conservarse en `100px` y `150px` según el panel actual.
4. [Regla: docs/domain/NAVIGATION.md] Hover, foco, clic, Escape, clic exterior y navegación por teclado deben conservar su comportamiento.
5. [Regla: docs/domain/NAVIGATION.md] El cambio no debe introducir overflow horizontal en escritorio ni móvil.

## Reglas de negocio

- [Regla: AGENTS.md] El mega menú canónico pertenece al child theme `wp-content/themes/blocksy-child/`.
- [Regla: AGENTS.md] El cambio debe ser focalizado y preservar clases y marcado existentes.

## Contratos

### Entrada

```json
{
  "selector": ".tmd-mm-home, .tmd-mm-nav-link",
  "currentFontSize": "12px",
  "targetFontSize": "14px"
}
```

### Salida

```json
{
  "navbarMinHeight": "56px",
  "panelTop": "56px",
  "imageHeights": ["100px", "150px"],
  "navigationBehavior": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] El aumento puede reducir el espacio horizontal disponible en resoluciones intermedias.
- [Regla: docs/domain/NAVIGATION.md] En móvil debe conservarse la ausencia de overflow horizontal.
- [Inferencia técnica] Un cambio de tipografía puede modificar el ancho visual de una etiqueta, pero no debe alterar la altura fija de la barra ni las dimensiones de las imágenes.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/functions.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] Las etiquetas principales se visualizan a `14px` o al tamaño aprobado superior a `12px`.
2. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:29-42] La barra conserva `min-height: 56px` y el panel conserva `top: 56px`.
3. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:287-306] Las imágenes conservan sus alturas de `100px` y `150px`.
4. [Regla: docs/domain/NAVIGATION.md] El mega menú continúa funcionando por hover, foco, clic, Escape, clic exterior y teclado sin errores de consola.
5. [Regla: docs/domain/NAVIGATION.md] No aparece overflow horizontal nuevo en escritorio ni móvil.

## Validación

- Pruebas unitarias: No aplica; el cambio es CSS.
- Pruebas de integración: comprobar que la hoja canónica se carga y que los selectores conservan clases, enlaces y comportamiento existentes.
- Validación manual: revisar escritorio y móvil, medir la altura de la barra, verificar alturas de imágenes, interacción completa y overflow horizontal.
- Validación productiva: No aplica en esta etapa; cualquier despliegue posterior requiere autorización explícita, backup y verificación según los runbooks.

## Riesgos

- [Inferencia técnica] El aumento puede provocar solapamiento u overflow horizontal en anchos intermedios si el espacio disponible resulta insuficiente.
- [Inferencia técnica] Una caché de CSS podría ocultar el cambio después de un despliegue; no se abordará producción en este alcance.

## Decisiones pendientes

- No aplica.

## Registro de aprobación

- [Aprobación, 2026-08-29] El usuario aprobó aumentar a `14px` únicamente las etiquetas de la barra principal, conservando la altura del menú y de las imágenes.

## Evidencia de implementación

- [Validación local, 2026-08-29] `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css` cambia únicamente el `font-size` de `.tmd-mm-home, .tmd-mm-nav-link` de `12px` a `14px`; `min-height: 56px`, `top: 56px`, las alturas de imagen y el JavaScript no fueron modificados.
- [Validación local, 2026-08-29] `git diff --check` terminó sin errores.
- [Pendiente] Falta validación visual y de interacción en navegador en escritorio y móvil, incluyendo overflow horizontal y consola.
