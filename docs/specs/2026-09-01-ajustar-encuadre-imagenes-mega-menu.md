# SPEC: Mejorar el encuadre de imágenes de Nosotros y Socios y atención

## Estado

- Aprobado

## Contexto

[Solicitud] En el mega menú, las imágenes de las secciones “Nosotros” y “Socios y atención” deben verse un poco más alejadas para que se aprecie mejor su contenido.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:317-323] La imagen de “Compañía” usa el recurso `assets/img/personal/trabaja-equipo.webp` y la de “Socios y atención” usa `assets/images/mega-menu/menu-partners-support.webp`.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:333-341] Ambas imágenes comparten `background-repeat: no-repeat` y `background-size: cover`; este modo llena la tarjeta, pero puede recortar parte de la imagen.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:329-331] Las tarjetas de “Nosotros” y “Servicios” conservan una altura de `150px`.

## Problema

[Solicitud] El encuadre actual muestra las imágenes demasiado cercanas y no permite apreciar suficientemente su contenido dentro del panel del mega menú.

## Objetivo

[Solicitud] Mostrar un encuadre más abierto de las imágenes de “Nosotros” y “Socios y atención”, conservando la composición general, la legibilidad y el comportamiento responsive del mega menú.

## Fuera del alcance

- [Solicitud] No cambiar las imágenes, sus URLs, textos, enlaces ni la estructura HTML del menú.
- [Solicitud] No modificar la altura ni el ancho de las tarjetas del mega menú.
- [Regla: AGENTS.md] No modificar producción, hacer commit, push ni despliegue como parte de este cambio local.
- [Regla: docs/domain/NAVIGATION.md] No alterar hover, foco, clic, Escape, clic exterior ni navegación por teclado.

## Requisitos funcionales

1. [Solicitud] La imagen de “Nosotros” debe mostrar un encuadre visualmente más alejado que el actual y permitir apreciar mejor su contenido principal.
2. [Solicitud] La imagen de “Socios y atención” debe mostrar un encuadre visualmente más alejado que el actual y permitir apreciar mejor su contenido principal.
3. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:329-331] Las tarjetas deben conservar la altura actual de `150px` en escritorio.
4. [Solicitud] Los recursos gráficos, sus rutas, el contraste y la ausencia de tintas superpuestas deben conservarse.
5. [Regla: docs/domain/NAVIGATION.md] El ajuste debe conservar el comportamiento responsive y no introducir overflow horizontal en escritorio ni móvil.

## Reglas de negocio

- [Regla: AGENTS.md] El mega menú canónico pertenece al child theme `wp-content/themes/blocksy-child/`.
- [Regla: AGENTS.md] No se deben inventar ni reemplazar imágenes o hechos comerciales.

## Contratos

### Entrada

```json
{
  "sections": ["nosotros", "socios-atencion"],
  "assets": {
    "nosotros": "assets/img/personal/trabaja-equipo.webp",
    "socios-atencion": "assets/images/mega-menu/menu-partners-support.webp"
  },
  "currentCardHeightDesktop": "150px"
}
```

### Salida

```json
{
  "sections": ["nosotros", "socios-atencion"],
  "framing": "more open than current production/local baseline",
  "assets": "unchanged",
  "cardDimensions": "unchanged",
  "horizontalOverflow": false
}
```

## Casos límite

- [Inferencia técnica] Un encuadre más abierto puede dejar espacios del fondo visibles si la proporción del recurso no coincide con la tarjeta.
- [Inferencia técnica] Escritorio y móvil usan alturas distintas para estas tarjetas, por lo que el encuadre debe revisarse en ambos tamaños.
- [Decisión resuelta, 2026-09-01] Se mostrará el recurso completo sin distorsión mediante un encuadre contenido y centrado, conservando el fondo de la tarjeta.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En “Nosotros” se percibe un encuadre más alejado y se aprecia mejor el contenido principal de la imagen.
2. [Solicitud] En “Socios y atención” se percibe un encuadre más alejado y se aprecia mejor el contenido principal de la imagen.
3. [Solicitud] En “Legal” se muestra el recurso actualizado correspondiente, con un encuadre que permita apreciar mejor su contenido principal.
4. [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:329-331] La altura de las tarjetas permanece en `150px` en escritorio.
5. [Solicitud] Las imágenes, URLs, textos, enlaces y estructura del menú permanecen sin cambios, salvo la corrección del intercambio accidental de los recursos Legal y Socios & Atención.
6. [Regla: docs/domain/NAVIGATION.md] No aparece overflow horizontal nuevo en escritorio ni móvil y la interacción del mega menú permanece operativa.

## Validación

- Pruebas unitarias: No aplica; el cambio previsto es visual/CSS.
- Pruebas de integración: comprobar que las dos clases de imagen conservan sus recursos y que la hoja canónica se carga correctamente.
- Validación manual: revisar el encuadre, contraste, nitidez, alineación y altura de las dos tarjetas en escritorio y móvil; abrir y cerrar el mega menú y comprobar teclado y overflow.
- Validación productiva: Pendiente de autorización explícita; cualquier publicación requiere backup focalizado, control de sincronización, despliegue únicamente del archivo afectado, purga de LiteSpeed y verificación HTTP/navegador según los runbooks.

## Riesgos

- [Inferencia técnica] Un ajuste excesivo puede hacer que el contenido principal se vea demasiado pequeño o que aparezcan áreas vacías.
- [Inferencia técnica] LiteSpeed o la caché del navegador pueden ocultar el ajuste después de un eventual despliegue.

## Decisiones pendientes

- [Decisión resuelta, 2026-09-01] El usuario autorizó actualizar las tres partes: tipografía, logo y encuadre/corrección de imágenes.
- [Decisión resuelta, 2026-09-01] La corrección de recursos intercambiados aplica a `menu-legal.webp` y `menu-partners-support.webp`; cada archivo recupera el contenido que le corresponde.
