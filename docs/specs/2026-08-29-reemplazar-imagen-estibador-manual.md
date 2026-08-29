# SPEC: Reemplazar ilustraciones de equipos de traslado y elevación ligera

## Estado

- Aprobado

## Contexto

[Solicitud] Las imágenes temporales de las secciones “Estibadores manuales”, “Estibadores eléctricos” y “Apiladores eléctricos” deben reemplazarse por los archivos WebP disponibles en el repositorio.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php:61-124] Los guías `estibadores-manuales`, `estibadores-electricos` y `apiladores-electricos` contienen los títulos, resúmenes y contenidos de las secciones objetivo.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php:343-360] La visual del hero actualmente imprime una ilustración CSS genérica mediante `.tmd-type-guide__machine`.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/estibador-manual.webp] El recurso solicitado existe en el repositorio y es un WebP de 483 × 517 px que muestra un estibador manual.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp] El recurso existe en el repositorio y es un WebP de 4000 × 6000 px.

[Evidencia: wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp] El recurso existe en el repositorio y es un WebP de 4000 × 4011 px.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css:181-285] El panel visual conserva una proporción cuadrada, fondo, etiqueta y reglas de la ilustración actual.

## Problema

[Solicitud] Las secciones objetivo muestran ilustraciones CSS temporales/genéricas en lugar de los recursos reales de sus equipos.

## Objetivo

[Solicitud] Mostrar los tres recursos locales en los heroes correspondientes, conservando la composición, legibilidad y comportamiento responsive de cada sección.

## Fuera del alcance

- [Solicitud] No cambiar el título, resumen, textos, botones, enlaces ni estructura general de la guía.
- [Solicitud] No cambiar la visual de las guías que no estén incluidas en este SPEC.
- [Inferencia técnica] No modificar el recurso original ni generar una versión derivada.
- [Regla: AGENTS.md] No modificar producción, hacer commit, push ni despliegue como parte de este cambio local.
- [Regla: AGENTS.md] No modificar archivos de imagen locales ajenos a esta solicitud.

## Requisitos funcionales

1. [Solicitud] La guía `estibadores-manuales` debe mostrar `assets/img/mega-menu/mega-menu-out/estibador-manual.webp` en lugar de la ilustración CSS temporal.
2. [Solicitud] La guía `estibadores-electricos` debe mostrar `assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp` en lugar de la ilustración CSS temporal.
3. [Solicitud] La guía `apiladores-electricos` debe mostrar `assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp` en lugar de la ilustración CSS temporal.
4. [Solicitud] Las demás guías deben conservar su ilustración y comportamiento actuales.
5. [Solicitud] El panel visual debe conservar su proporción, fondo, etiqueta y ubicación dentro del hero.
6. [Solicitud] Las imágenes deben conservar proporción y mostrarse sin distorsión ni recortes que oculten el equipo principal.
7. [Regla: docs/domain/NAVIGATION.md] Las páginas no deben introducir overflow horizontal en escritorio ni móvil.

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
    "apiladores-electricos": "assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp"
  }
}
```

### Salida

```json
{
  "heroVisuals": "three local WebP assets mapped to their guides",
  "otherGuides": "unchanged",
  "contentAndLinks": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] Los recursos tienen orientaciones y proporciones distintas y pueden requerir `object-fit: contain` para evitar distorsión o recorte.
- [Regla: docs/domain/NAVIGATION.md] En móvil debe conservarse la ausencia de overflow horizontal.
- [Inferencia técnica] La imagen tiene fondo blanco y puede contrastar de forma distinta con el panel translúcido actual.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/estibador-manual.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp`
- `wp-content/themes/blocksy-child/assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En `estibadores-manuales` se muestra `estibador-manual.webp` y deja de renderizarse la ilustración CSS temporal.
2. [Solicitud] En `estibadores-electricos` se muestra `portaestiba-electrico.webp` y deja de renderizarse la ilustración CSS temporal.
3. [Solicitud] En `apiladores-electricos` se muestra `apiladores-electricos.webp` y deja de renderizarse la ilustración CSS temporal.
4. [Solicitud] Las demás guías continúan usando su visual actual sin cambios.
5. [Solicitud] Cada imagen se muestra completa, proporcionada y legible dentro de su panel.
6. [Solicitud] Los títulos, resúmenes, botones y enlaces de las guías conservan sus valores actuales.
7. [Regla: docs/domain/NAVIGATION.md] No aparece overflow horizontal nuevo en escritorio ni móvil.

## Validación

- Pruebas unitarias: No aplica; el cambio es de marcado y CSS visual.
- Pruebas de integración: comprobar que la guía seleccionada resuelve el asset local y que las demás guías no reciben el nuevo recurso.
- Validación manual: revisar encuadre, contraste, nitidez, proporción, escritorio, móvil y ausencia de overflow.
- Validación productiva: No aplica en esta etapa; cualquier despliegue posterior requiere autorización explícita, backup y verificación según los runbooks.

## Riesgos

- [Inferencia técnica] El fondo blanco del WebP puede modificar la apariencia del panel respecto de la ilustración CSS transparente.
- [Inferencia técnica] Los archivos de alta resolución pueden aumentar el peso de descarga si se muestran sin optimización adicional.

## Decisiones pendientes

- No aplica; la solicitud identifica los tres recursos y sus guías objetivo. `portaestiba-electrico.webp` se asigna a `estibadores-electricos` porque no existe una guía separada con slug portaestiba.

## Registro de aprobación

- [Aprobación, 2026-08-29] El usuario aprobó reemplazar las ilustraciones temporales de las tres guías por los assets locales identificados.
