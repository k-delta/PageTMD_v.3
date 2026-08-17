# SPEC: Reemplazo de imagen de Mantenimientos en el mega menú

## Estado

- Terminado

## Contexto

[Solicitud] La tarjeta “Mantenimientos” del mega menú Servicios muestra actualmente una fotografía cuyo encuadre se ve mal y debe sustituirse por `img/mantenimientos/antos.png`.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:213-221] La tarjeta pertenece al panel `tmd-mm-panel-mant` y conserva enlaces a Mantenimientos, Preventivo y Correctivo.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:321-332] La imagen visible procede del asset publicado `assets/images/mega-menu/menu-maintenance.webp`, usado como fondo con `background-size: cover`.

[Evidencia: img/mantenimientos/antos.png] El recurso solicitado existe localmente, mide 1087 × 858 px y muestra herramientas de mantenimiento junto a un montacargas.

## Problema

[Solicitud] La fotografía actual de la tarjeta Mantenimientos tiene un encuadre que no representa bien el servicio y debe dejar de mostrarse.

## Objetivo

[Solicitud] Mostrar `antos.png` como nueva imagen de la tarjeta Mantenimientos, con un recorte legible y optimizado para el formato horizontal del mega menú, eliminando del asset publicado la fotografía anterior.

## Fuera del alcance

- [Solicitud] No cambiar textos, enlaces, rutas, estructura ni interacción del mega menú.
- [Regla: docs/domain/NAVIGATION.md] No reemplazar el header completo ni alterar sus clases o marcado dependiente.
- [Inferencia técnica] No eliminar otros originales de `img/mantenimientos/`, porque la solicitud solo identifica el asset visible anterior y esos originales podrían tener otros usos.
- [Regla: AGENTS.md] No desplegar ni modificar producción sin autorización explícita posterior.

## Requisitos funcionales

1. [Solicitud] La tarjeta Mantenimientos debe mostrar una versión derivada de `img/mantenimientos/antos.png`.
2. [Solicitud] La fotografía anterior no debe permanecer en el asset publicado ni mostrarse como fallback de la tarjeta.
3. [Solicitud] El encuadre debe conservar visibles las herramientas de mantenimiento y suficiente contexto del montacargas.
4. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:321-332] La imagen derivada debe cubrir la tarjeta horizontal sin distorsión.
5. [Regla: docs/domain/NAVIGATION.md] Hover, foco, clic, Escape, clic exterior y navegación por teclado deben conservar su comportamiento.
6. [Regla: docs/domain/NAVIGATION.md] El cambio no debe introducir overflow horizontal en escritorio ni móvil.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] El header canónico pertenece al child theme y solo admite parches focalizados.
- [Solicitud] La imagen debe representar mantenimiento real, sin inventar equipos, modelos o hechos comerciales.

## Contratos

### Entrada

```json
{
  "source": "img/mantenimientos/antos.png",
  "card": "Mantenimientos"
}
```

### Salida

```json
{
  "publishedAsset": "wp-content/themes/blocksy-child/assets/images/mega-menu/menu-maintenance.webp",
  "content": "herramientas de mantenimiento y montacargas",
  "previousPhotoVisible": false
}
```

## Casos límite

- [Inferencia técnica] El formato casi cuadrado del original puede perder elementos relevantes al adaptarse a la tarjeta horizontal.
- [Regla: docs/domain/NAVIGATION.md] El recorte debe seguir siendo comprensible cuando la tarjeta reduce su tamaño en móvil.
- [Inferencia técnica] La caché del navegador o LiteSpeed puede conservar temporalmente la versión anterior si la URL no cambia.

## Archivos o módulos relacionados

- `img/mantenimientos/antos.png`
- `wp-content/themes/blocksy-child/assets/images/mega-menu/menu-maintenance.webp`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] La tarjeta Mantenimientos muestra la escena de herramientas y montacargas de `antos.png`, y ya no muestra ningún píxel de la fotografía anterior.
2. [Solicitud] El recorte se ve nítido, horizontal y sin distorsión; las herramientas y el contexto de mantenimiento siguen identificables.
3. [Solicitud] Los enlaces Mantenimientos, Preventivo y Correctivo conservan exactamente sus destinos actuales.
4. [Regla: docs/domain/NAVIGATION.md] El mega menú continúa funcionando por hover, foco, clic, Escape, clic exterior y teclado sin errores de consola.
5. [Regla: docs/domain/NAVIGATION.md] En escritorio y móvil no aparecen recortes del contenedor, solapamientos ni overflow horizontal nuevos.

## Validación

- Pruebas unitarias: No aplica; el cambio es un reemplazo de asset visual.
- Pruebas de integración: comprobar que la tarjeta continúa resolviendo `menu-maintenance.webp`, que el archivo es un WebP válido y que los enlaces no cambian.
- Validación manual: comparar antes/después en escritorio y móvil; revisar encuadre, nitidez, contraste, interacción completa del mega menú, consola y overflow horizontal.
- Validación productiva: con autorización explícita, crear backup focalizado, comprobar deriva, desplegar únicamente el asset aprobado y cualquier cachebuster estrictamente necesario, purgar caché, validar navegador y repetir el control de sincronización.

## Riesgos

- [Inferencia técnica] Un recorte automático centrado puede ocultar parte de las herramientas o del montacargas.
- [Inferencia técnica] Reutilizar la misma URL puede hacer que una caché sirva temporalmente la fotografía anterior.
- [Inferencia técnica] Aumentar innecesariamente el peso del asset puede degradar la apertura del mega menú.

## Decisiones pendientes

- No aplica. La solicitud identifica `antos.png`; el asset publicado anterior se reemplazará en la misma ruta y los originales no referenciados de `img/mantenimientos/` permanecerán fuera del alcance.

## Registro de aprobación

- [Aprobación, 2026-08-16] El usuario aprobó el reemplazo del asset visible por una versión derivada de `img/mantenimientos/antos.png`, junto con la implementación del SPEC aprobado de compactación de servicios en Inicio.

## Evidencia de implementación

- [Validación local, 2026-08-16] `menu-maintenance.webp` fue reemplazado por un WebP válido de 960 × 280 px y 49.166 bytes derivado de `antos.png`; el recorte conserva herramientas y contexto del montacargas.
- [Validación pública en memoria, 2026-08-16] Chrome verificó la tarjeta en escritorio y móvil con `background-size: cover`, sin overflow ni errores de consola; los destinos de Mantenimientos, Preventivo y Correctivo se conservaron.
- [Validación de interacción, 2026-08-16] El panel abrió por foco y cerró con Escape y clic exterior en escritorio y móvil.
- [Evidencia HTTP pública, 2026-08-16] El asset vigente usa caché de siete días; la referencia nueva incluye un cachebuster estable para impedir que siga apareciendo la foto anterior después de un despliegue.
- [Revisión, 2026-08-16] El reporte consolidado terminó `READY`; la revisión de rendimiento no encontró hallazgos.
- Producción no fue modificada ni validada con el nuevo asset; continúa pendiente de una autorización explícita de despliegue.
