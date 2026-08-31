# SPEC: Reemplazar imágenes de las guías de equipos

## Estado

- Borrador

## Contexto

[Solicitud] Las imágenes temporales de las guías de equipos deben reemplazarse por los archivos WebP disponibles en el repositorio, incluyendo las imágenes nuevas de retráctiles, pantógrafo, contrabalanceados y tomapedidos.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php:61-124,147-224,248-287] Las guías `estibadores-manuales`, `estibadores-electricos`, `apiladores-electricos`, `retractiles-de-mastil-movil`, `pantografo-sencillo`, `pantografo-doble-profundidad`, `tomapedidos`, `tomapedidos-de-alto-nivel`, `contrabalanceados`, `electricos-de-3-ruedas` y `electricos-de-4-ruedas` contienen los títulos, resúmenes y contenidos de las secciones objetivo.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php:343-360] La visual del hero actualmente imprime una ilustración CSS genérica mediante `.tmd-type-guide__machine`.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/estibador-manual.webp] El recurso solicitado existe en el repositorio y es un WebP de 483 × 517 px que muestra un estibador manual.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp] El recurso existe en el repositorio y es un WebP de 4000 × 6000 px.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp] El recurso existe en el repositorio y es un WebP de 4000 × 4011 px.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/Montacargas-retráctiles-de-mástil-móvil.webp] El recurso nuevo existe en el workspace y su nombre identifica el equipo retráctil de mástil móvil.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/pantografo-doble-reach.webp] El recurso nuevo existe en el workspace y su nombre identifica el pantógrafo de doble profundidad.

[Evidencia: wp-content/themes/blocksy-child/assets/img/equipment-guides/pantografo-sencillo.webp] El recurso existe en el repositorio, es un WebP de 466 × 700 px y su nombre identifica el pantógrafo sencillo.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/contrabalanceado-4-llantas.webp] El recurso existe en el workspace y muestra un contrabalanceado eléctrico de cuatro ruedas.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/contrabalanceado-3-llantas.webp] El recurso nuevo existe en el repositorio y muestra un contrabalanceado eléctrico de tres ruedas.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/toma-pedidos.webp] El recurso nuevo existe en el repositorio y su nombre identifica un equipo tomapedidos.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css:181-285] El panel visual conserva una proporción cuadrada, fondo, etiqueta y reglas de la ilustración actual.

## Problema

[Solicitud] Las guías que ya tienen un asset dedicado muestran ilustraciones CSS temporales/genéricas en lugar de los recursos reales de sus equipos.

## Objetivo

[Solicitud] Mostrar los nueve recursos locales en los heroes correspondientes, conservando la composición, legibilidad y comportamiento responsive de cada sección.

## Fuera del alcance

- [Solicitud] No cambiar el título, resumen, textos, botones, enlaces ni estructura general de la guía.
- [Solicitud] No cambiar la visual de las guías que no tengan un asset dedicado confirmado en este SPEC.
- [Inferencia técnica] No modificar el recurso original ni generar una versión derivada.
- [Regla: AGENTS.md] No modificar producción, hacer commit, push ni despliegue como parte de este cambio local.
- [Regla: AGENTS.md] No modificar archivos de imagen locales ajenos a esta solicitud.

## Requisitos funcionales

1. [Solicitud] La guía `estibadores-manuales` debe mostrar `assets/img/mega-menu/mega-menu-out/estibador-manual.webp` en lugar de la ilustración CSS temporal.
2. [Solicitud] La guía `estibadores-electricos` debe mostrar `assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp` en lugar de la ilustración CSS temporal.
3. [Solicitud] La guía `apiladores-electricos` debe mostrar `assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp` en lugar de la ilustración CSS temporal.
4. [Solicitud] La guía `retractiles-de-mastil-movil` debe mostrar `assets/img/mega-menu/mega-menu-out/Montacargas-retráctiles-de-mástil-móvil.webp` en lugar de la ilustración CSS temporal.
5. [Solicitud] La guía `pantografo-doble-profundidad` debe mostrar `assets/img/mega-menu/mega-menu-out/pantografo-doble-reach.webp` en lugar de la ilustración CSS temporal.
6. [Solicitud] La guía `pantografo-sencillo` debe mostrar `assets/img/equipment-guides/pantografo-sencillo.webp` en lugar de la ilustración CSS temporal.
7. [Solicitud] La guía `electricos-de-4-ruedas` debe mostrar `assets/img/mega-menu/mega-menu-out/contrabalanceado-4-llantas.webp` en lugar de la ilustración CSS temporal.
8. [Solicitud] La guía `electricos-de-3-ruedas` debe mostrar `assets/img/mega-menu/contrabalanceado-3-llantas.webp` en lugar de la ilustración CSS temporal.
9. [Solicitud] La guía `tomapedidos-de-alto-nivel` debe mostrar `assets/img/mega-menu/mega-menu-out/toma-pedidos.webp` en lugar de la ilustración CSS temporal.
10. [Solicitud] Las demás guías deben conservar su ilustración y comportamiento actuales mientras no exista un asset dedicado confirmado.
11. [Solicitud] El panel visual debe conservar su proporción, fondo, etiqueta y ubicación dentro del hero.
12. [Solicitud] Las imágenes deben conservar proporción y mostrarse sin distorsión ni recortes que oculten el equipo principal.
13. [Regla: docs/domain/NAVIGATION.md] Las páginas no deben introducir overflow horizontal en escritorio ni móvil.

## Reglas de negocio

- [Regla: AGENTS.md] La guía canónica pertenece al child theme `wp-content/themes/blocksy-child/`.
- [Regla: AGENTS.md] No se deben inventar equipos, imágenes ni hechos comerciales.
- [Regla: AGENTS.md] El cambio debe ser focalizado y preservar el comportamiento existente.

## Contratos

### Entrada

```json
{
  "guides": {
    "estibadores-manuales": "assets/img/mega-menu/mega-menu-out/estibador-manual.webp",
    "estibadores-electricos": "assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp",
    "apiladores-electricos": "assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp",
    "retractiles-de-mastil-movil": "assets/img/mega-menu/mega-menu-out/Montacargas-retráctiles-de-mástil-móvil.webp",
    "pantografo-doble-profundidad": "assets/img/mega-menu/mega-menu-out/pantografo-doble-reach.webp",
    "pantografo-sencillo": "assets/img/equipment-guides/pantografo-sencillo.webp",
    "electricos-de-4-ruedas": "assets/img/mega-menu/mega-menu-out/contrabalanceado-4-llantas.webp",
    "electricos-de-3-ruedas": "assets/img/mega-menu/contrabalanceado-3-llantas.webp",
    "tomapedidos-de-alto-nivel": "assets/img/mega-menu/mega-menu-out/toma-pedidos.webp"
  }
}
```

### Salida

```json
{
  "heroVisuals": "nine local WebP assets mapped to their guides",
  "otherGuides": "unchanged until dedicated assets are confirmed",
  "contentAndLinks": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] Los recursos tienen orientaciones y proporciones distintas y pueden requerir `object-fit: contain` para evitar distorsión o recorte.
- [Regla: docs/domain/NAVIGATION.md] En móvil debe conservarse la ausencia de overflow horizontal.
- [Inferencia técnica] La imagen tiene fondo blanco y puede contrastar de forma distinta con el panel translúcido actual.
- [Inferencia técnica] Los nombres con caracteres acentuados deben conservarse exactamente en el mapping para resolver el archivo real.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/estibador-manual.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/Montacargas-retráctiles-de-mástil-móvil.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/pantografo-doble-reach.webp`
- `wp-content/themes/blocksy-child/assets/img/equipment-guides/pantografo-sencillo.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/contrabalanceado-4-llantas.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/contrabalanceado-3-llantas.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/toma-pedidos.webp`
- `tests/test-equipment-type-guides.php`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En `estibadores-manuales` se muestra `estibador-manual.webp` y deja de renderizarse la ilustración CSS temporal.
2. [Solicitud] En `estibadores-electricos` se muestra `portaestiba-electrico.webp` y deja de renderizarse la ilustración CSS temporal.
3. [Solicitud] En `apiladores-electricos` se muestra `apiladores-electricos.webp` y deja de renderizarse la ilustración CSS temporal.
4. [Solicitud] En `retractiles-de-mastil-movil` se muestra `Montacargas-retráctiles-de-mástil-móvil.webp` y deja de renderizarse la ilustración CSS temporal.
5. [Solicitud] En `pantografo-doble-profundidad` se muestra `pantografo-doble-reach.webp` y deja de renderizarse la ilustración CSS temporal.
6. [Solicitud] En `pantografo-sencillo` se muestra `pantografo-sencillo.webp` y deja de renderizarse la ilustración CSS temporal.
7. [Solicitud] En `electricos-de-4-ruedas` se muestra `contrabalanceado-4-llantas.webp` y deja de renderizarse la ilustración CSS temporal.
8. [Solicitud] En `electricos-de-3-ruedas` se muestra `contrabalanceado-3-llantas.webp` y deja de renderizarse la ilustración CSS temporal.
9. [Solicitud] En `tomapedidos-de-alto-nivel` se muestra `toma-pedidos.webp` y deja de renderizarse la ilustración CSS temporal.
10. [Solicitud] Las demás guías continúan usando su visual actual sin cambios hasta confirmar un asset dedicado.
11. [Solicitud] Cada imagen se muestra completa, proporcionada y legible dentro de su panel.
12. [Solicitud] Los títulos, resúmenes, botones y enlaces de las guías conservan sus valores actuales.
13. [Regla: docs/domain/NAVIGATION.md] No aparece overflow horizontal nuevo en escritorio ni móvil.

## Validación

- Pruebas unitarias: `php tests/test-equipment-type-guides.php` debe comprobar los nueve mappings, el render del `<img>`, el fallback de las dos guías no mapeadas (`tomapedidos` y `contrabalanceados`) y las reglas visuales esenciales.
- Pruebas de integración: comprobar que la guía seleccionada resuelve el asset local y que las demás guías no reciben el nuevo recurso.
- Validación manual: revisar encuadre, contraste, nitidez, proporción, escritorio, móvil y ausencia de overflow.
- Validación productiva: No aplica en esta etapa; cualquier despliegue posterior de código o purga de caché requiere autorización explícita, backup y verificación según los runbooks.

## Riesgos

- [Inferencia técnica] El fondo blanco del WebP puede modificar la apariencia del panel respecto de la ilustración CSS transparente.
- [Inferencia técnica] Los archivos de alta resolución pueden aumentar el peso de descarga si se muestran sin optimización adicional.

## Decisiones pendientes

- [Decisión resuelta, 2026-08-31] Las dos guías sin un asset dedicado confirmado (`tomapedidos` y `contrabalanceados`) mantienen la ilustración CSS, conforme al requisito de conservar las demás guías sin cambios.
- [Decisión resuelta, 2026-08-31] `assets/img/equipment-guides/pantografo-sencillo.webp` se asigna a `pantografo-sencillo` porque existe como recurso dedicado identificado para esa guía.
- [DEC-02] `portaestiba-electrico.webp` se asigna a `estibadores-electricos` porque no existe una guía separada con slug portaestiba.
- [Regla: AGENTS.md] La aprobación registrada el 2026-08-29 cubría solo tres guías y la del 2026-08-31 cubría ocho mappings; esta ampliación a nueve mappings requiere aprobación explícita de este borrador.

## Registro de aprobación

- [Aprobación histórica, 2026-08-29] El usuario aprobó reemplazar las ilustraciones temporales de las tres guías por los assets locales identificados. Esta aprobación no cubre la ampliación del alcance a ocho guías.
- [Aprobación, 2026-08-31] El usuario aprobó la ampliación a ocho mappings, incluyendo `contrabalanceado-3-llantas.webp` para `electricos-de-3-ruedas` y `toma-pedidos.webp` para `tomapedidos-de-alto-nivel`.
- [Solicitud recibida, 2026-08-31] El usuario solicitó conservar `electricos-de-4-ruedas` e incorporar `pantografo-sencillo` con su asset dedicado; la versión de nueve mappings queda pendiente de aprobación explícita antes de implementar o desplegar.
