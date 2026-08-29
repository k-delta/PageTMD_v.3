# SPEC: Aumentar ligeramente el logo del menú

## Estado

- Aprobado

## Contexto

[Solicitud] Se solicita aumentar un poco el tamaño del logo ubicado dentro del menú.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:103-119] El logo del menú usa una caja `.tmd-mm-logo` de `height: 40px` y la imagen está limitada a `max-height: 40px` y `max-width: 150px`.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:29-42] La barra principal tiene `min-height: 56px`.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:108-114] El logo se carga desde el marcado canónico del header y no se requiere cambiar su recurso.

## Problema

[Solicitud] El logo se percibe pequeño dentro de la barra principal y debe ganar presencia sin aumentar la altura del menú.

## Objetivo

[Solicitud] Aumentar moderadamente el tamaño visual del logo del menú, conservando la barra, el recurso gráfico y la estructura existentes.

## Fuera del alcance

- [Solicitud] No modificar la altura de la barra ni la caja `.tmd-mm-logo` de `40px`.
- [Solicitud] No cambiar la imagen, URL, texto alternativo, enlaces ni marcado HTML del logo.
- [Inferencia técnica] No modificar los iconos, etiquetas, paneles, imágenes internas ni comportamiento del mega menú.
- [Regla: AGENTS.md] No modificar producción, hacer commit, push ni despliegue como parte de este cambio local.
- [Regla: AGENTS.md] No modificar los archivos de imagen que ya presentan cambios locales ajenos a esta solicitud.

## Requisitos funcionales

1. [Solicitud] La imagen del logo debe mostrarse ligeramente más grande que el límite actual de `150px × 40px`.
2. [Solicitud] La barra principal debe conservar `min-height: 56px`.
3. [Solicitud] La caja `.tmd-mm-logo` debe conservar `height: 40px`.
4. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:108-114] El logo debe seguir usando el mismo recurso y enlace canónicos.
5. [Regla: docs/domain/NAVIGATION.md] El cambio no debe introducir overflow horizontal en escritorio ni móvil.

## Reglas de negocio

- [Regla: AGENTS.md] El header canónico pertenece al child theme `wp-content/themes/blocksy-child/`.
- [Regla: AGENTS.md] El cambio debe ser focalizado y preservar clases y marcado existentes.

## Contratos

### Entrada

```json
{
  "selector": ".tmd-mm-logo img",
  "currentMaxWidth": "150px",
  "currentMaxHeight": "40px",
  "proposedMaxWidth": "166px",
  "proposedMaxHeight": "44px"
}
```

### Salida

```json
{
  "navbarMinHeight": "56px",
  "logoContainerHeight": "40px",
  "logoSource": "unchanged",
  "navigationBehavior": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] El logo puede sobresalir visualmente algunos píxeles de su caja de `40px`, sin alterar la altura de la barra.
- [Regla: docs/domain/NAVIGATION.md] En móvil debe conservarse la ausencia de overflow horizontal.
- [Inferencia técnica] El ancho adicional puede reducir el espacio disponible para la navegación de escritorio.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] El logo se visualiza ligeramente más grande mediante límites superiores de `166px × 44px` o el tamaño aprobado.
2. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:29-42] La barra conserva `min-height: 56px`.
3. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:103-119] La caja `.tmd-mm-logo` conserva `height: 40px`.
4. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:108-114] El recurso, enlace y marcado del logo permanecen sin cambios.
5. [Regla: docs/domain/NAVIGATION.md] No aparece overflow horizontal nuevo en escritorio ni móvil.

## Validación

- Pruebas unitarias: No aplica; el cambio es CSS.
- Pruebas de integración: comprobar que el header conserva el mismo recurso y que solo cambian los límites visuales de la imagen.
- Validación manual: revisar tamaño, alineación, altura de la barra y overflow en escritorio y móvil.
- Validación productiva: No aplica en esta etapa; cualquier despliegue posterior requiere autorización explícita, backup y verificación según los runbooks.

## Riesgos

- [Inferencia técnica] El incremento puede reducir el espacio horizontal disponible entre logo y navegación.
- [Inferencia técnica] Un logo de `44px` de alto dentro de una caja de `40px` requiere comprobar visualmente su alineación.

## Decisiones pendientes

- No aplica.

## Registro de aprobación

- [Aprobación, 2026-08-29] El usuario aprobó aumentar el logo a `166px × 44px`, manteniendo la caja de `40px` y la barra de `56px`.
