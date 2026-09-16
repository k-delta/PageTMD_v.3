# SPEC: Mostrar hasta tres tarjetas en el carrusel de Vacantes

## Estado

- Aprobado

## Contexto

[Solicitud: captura aportada el 2026-09-16] La sección “Vacantes disponibles” ya funciona como carrusel, pero la tarjeta visible ocupa todo el ancho disponible y solo se observa una tarjeta por vista.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-job-application.css:82-108] El estilo actual convierte `tmd-jobs-grid` en un track horizontal y fija cada `.tmd-job-card` en `100%` del viewport.

[Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-job-application.js:79-179] La navegación actual calcula el desplazamiento de cada tarjeta individual y conserva controles, indicadores, teclado, gesto táctil y movimiento reducido.

[Evidencia: docs/specs/2026-09-16-carrusel-vacantes-trabaja-nosotros.md:68-75,169-180] El SPEC aprobado previamente estableció una tarjeta por vista; esta solicitud modifica explícitamente esa decisión de presentación.

## Problema

[Solicitud] Las tarjetas de vacantes se muestran demasiado anchas y la persona usuaria no puede comparar simultáneamente las vacantes disponibles en una vista amplia.

## Objetivo

[Solicitud] Hacer más estrechas las tarjetas de “Vacantes disponibles” para mostrar hasta tres tarjetas simultáneamente en el carrusel, manteniendo el acceso al contenido, la postulación y la navegación responsive.

## Fuera del alcance

- [Solicitud] No modificar los títulos, descripciones, etiquetas, jornada, ubicación, salario, orden ni enlaces de las vacantes.
- [Solicitud] No modificar el formulario de postulación, sus campos, validaciones, destinatarios, endpoint ni payload.
- [Solicitud] No modificar el hero, panorama, filosofía, testimonio, imagen de equipo, SEO, menú, footer ni otras páginas.
- [Regla: AGENTS.md] No modificar WordPress core, el tema padre, plugins de terceros, uploads ni `production-snapshot/` como fuente de contenido.
- [Regla: AGENTS.md] La solicitud actual no autoriza commit, push, escritura de contenido administrado ni despliegue productivo.
- [Solicitud] No incorporar dependencias externas nuevas para el carrusel.

## Requisitos funcionales

1. [Solicitud] En un viewport de escritorio, la sección `tmd-jobs-vacancies` debe mostrar simultáneamente hasta tres tarjetas, cada una con un ancho menor que el viewport completo.
2. [Solicitud] En viewports estrechos, el número de tarjetas visibles debe adaptarse al espacio disponible sin recortar textos, metadatos, controles ni botones de postulación.
3. [Regla: AGENTS.md] Las tres tarjetas deben conservar sus clases, contenido, orden, enlaces `#postulacion` y comportamiento de postulación existentes.
4. [Regla: AGENTS.md] El carrusel debe permanecer localizado en los assets del child theme ya cargados para la página `ID 273`, sin afectar otras páginas.
5. [Solicitud] La navegación manual, los indicadores, el teclado, el desplazamiento táctil, el foco visible y `prefers-reduced-motion` deben continuar funcionando con la nueva cantidad de tarjetas visibles.
6. [Regla: AGENTS.md] La modificación debe ser exclusivamente de presentación e interacción local; no debe crear ni modificar contenido persistido de WordPress.

## Reglas de negocio

- [Regla: AGENTS.md] WordPress/MariaDB continúa siendo la fuente canónica del contenido de las vacantes.
- [Solicitud] La comparación visual de vacantes no debe ocultar una vacante ni su acción “Aplicar a esta vacante”.
- [Regla: AGENTS.md] La presentación responsive debe evitar overflow horizontal del documento y conservar una experiencia usable.

## Contratos

### Entrada

```json
{
  "pageId": 273,
  "section": "tmd-jobs-vacancies",
  "cards": 3,
  "contentSource": "WordPress/MariaDB",
  "desktopVisibleCards": 3
}
```

### Salida

```json
{
  "component": "responsive accessible vacancy carousel",
  "maximumVisibleCards": 3,
  "cardContent": "unchanged",
  "formAndApplicationLinks": "unchanged",
  "autoplay": false
}
```

## Casos límite

- [Inferencia técnica] Con tres tarjetas y un viewport de escritorio, las tres deben caber dentro del track sin desplazamiento horizontal del documento.
- [Inferencia técnica] Con una tarjeta, debe conservarse la estructura de carrusel, su indicador y sus controles deshabilitados; con ninguna tarjeta no deben aparecer controles que no puedan funcionar.
- [Inferencia técnica] Con dos tarjetas, ambas deben conservarse accesibles y no debe reservarse espacio para una tarjeta inexistente.
- [Inferencia técnica] En móvil, cada tarjeta debe conservar una anchura legible y el botón de postulación debe permanecer alcanzable mediante scroll, teclado y foco.
- [Inferencia técnica] Si existen más tarjetas en el futuro, el carrusel debe poder desplazarse por grupos o tarjetas sin perder la correspondencia entre posición, indicador y contenido visible.
- [Inferencia técnica] Si JavaScript no carga, las tarjetas no deben desaparecer ni duplicarse y deben conservar una presentación navegable.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-job-application.css` como estilo focalizado del carrusel.
- `wp-content/themes/blocksy-child/assets/js/tmd-job-application.js` como comportamiento de navegación y formulario.
- `wp-content/themes/blocksy-child/inc/tmd-job-application.php` como punto de carga de los assets de la página `ID 273`.
- `tests/test-jobs-carousel-js.mjs` como prueba focalizada de tarjetas, navegación, accesibilidad y CSS.
- `docs/specs/2026-09-16-carrusel-vacantes-trabaja-nosotros.md` como SPEC aprobado cuya decisión de una tarjeta por vista debe ser sustituida si este cambio se aprueba.

## Criterios de aceptación

1. [Solicitud] En un viewport de escritorio amplio, la sección muestra las tres tarjetas actuales simultáneamente y ninguna ocupa el `100%` del viewport del carrusel.
2. [Solicitud] En viewports estrechos, las tarjetas se adaptan a una o más por vista según el espacio disponible, sin recortes ni overflow horizontal del documento.
3. [Regla: AGENTS.md] Las tres tarjetas conservan exactamente sus textos, metadatos, clases, orden, enlaces y botones de postulación.
4. [Solicitud] Los controles e indicadores deben permanecer dentro de la estructura del carrusel con una, dos o tres tarjetas visibles; deben identificar y alcanzar cada vista disponible y quedar deshabilitados en los extremos sin producir desplazamientos inválidos.
5. [Solicitud] El carrusel conserva navegación táctil, teclado, foco visible, movimiento reducido y ausencia de autoplay.
6. [Regla: AGENTS.md] El formulario de postulación funciona igual y no se modifican contenido persistido, contratos AJAX ni archivos fuera del alcance.
7. [Regla: AGENTS.md] Las validaciones focalizadas pasan y el diff contiene únicamente los assets del carrusel y su prueba, además de la actualización documental correspondiente.

## Validación

- Pruebas unitarias: actualizar `tests/test-jobs-carousel-js.mjs` para comprobar hasta tres tarjetas visibles, navegación por los extremos, indicadores, teclado, reduced-motion y ausencia de regresión del formulario.
- Pruebas de integración: ejecutar `node --check wp-content/themes/blocksy-child/assets/js/tmd-job-application.js` y la prueba focalizada; comprobar que el enqueue existente continúa apuntando a los assets del child theme.
- Validación manual: revisar la página en `1440x900`, `900x900`, `640x844` y `390x844`; confirmar tres tarjetas en escritorio, adaptación responsive, textos completos, botones, controles, foco, gesto y ausencia de overflow.
- Validación productiva: No aplica a este SPEC; la solicitud vigente se limita al repositorio y no autoriza despliegue.

## Riesgos

- [Inferencia técnica] Cambiar el ancho de las tarjetas sin ajustar el cálculo de desplazamiento puede dejar indicadores desalineados o hacer que los controles no cambien de vista.
- [Inferencia técnica] Mostrar tres tarjetas en un ancho insuficiente puede comprimir el texto y volver inaccesibles los botones.
- [Inferencia técnica] Mantener controles pensados para una tarjeta por vista puede producir desplazamientos vacíos cuando las tres tarjetas ya caben.
- [Inferencia técnica] Una regla CSS global podría alterar otras cuadrículas si no conserva el alcance `body.page-id-273 .tmd-jobs-vacancies`.

## Decisiones pendientes

- No hay decisiones funcionales pendientes.

## Decisiones aprobadas

- `CAR-04` — [Aprobación del usuario, 2026-09-16] Mostrar hasta tres tarjetas en escritorio amplio, dos en tablet y una en móvil.
- `CAR-05` — [Aprobación del usuario, 2026-09-16] Mantener siempre la estructura de carrusel —track, snap, controles, indicadores, touch y teclado— aunque una, dos o tres tarjetas quepan en la vista; no convertirla en una cuadrícula estática.
- `CAR-06` — [Aprobación del usuario, 2026-09-16] Conservar controles e indicadores en todos esos casos; cuando no exista desplazamiento adicional, los controles deben permanecer deshabilitados en los extremos.

## Registro de aprobación

- [Aprobación, 2026-09-16] El usuario aprobó el ajuste visual y confirmó que la sección debe seguir siendo un carrusel con 3, 2 o 1 tarjetas visibles.
