# SPEC: Carrusel de vacantes de Trabaja con nosotros

## Estado

- Aprobado

## Contexto

[Solicitud: mensaje del usuario del 2026-09-16] La sección “Vacantes disponibles” debe mostrar las tarjetas de las tres vacantes como un carrusel.

[Evidencia: `production-snapshot/pages.json:123-130`] La página WordPress `ID 273`, ruta `/nosotros/trabaja-con-nosotros/`, contiene la sección `tmd-jobs-vacancies` y las tarjetas usan actualmente el contenedor `tmd-jobs-grid`.

[Evidencia: `wp-content/themes/blocksy-child/inc/tmd-job-application.php:569-598`] La página `ID 273` ya carga los assets `tmd-job-application.css` y `tmd-job-application.js` del child theme, por lo que existe un punto canónico para el comportamiento de postulación y la presentación específica de la página.

[Evidencia: `scripts/update-jobs-vacancies.php`] La actualización anterior dejó versionados en WordPress los textos de tres vacantes; este cambio debe modificar únicamente la presentación e interacción del contenedor, no esos textos persistidos.

## Problema

[Solicitud] Las tres vacantes se presentan como tarjetas de una cuadrícula estática y no como una secuencia navegable de carrusel.

## Objetivo

[Solicitud] Convertir la sección “Vacantes disponibles” de la página `ID 273` en un carrusel accesible y responsive para recorrer las tres tarjetas de vacantes, conservando el contenido, los botones de postulación y el formulario existente.

## Fuera del alcance

- [Solicitud] No modificar los títulos, descripciones, etiquetas, metadatos, enlaces ni el orden de las tres vacantes ya publicados.
- [Solicitud] No modificar el formulario de postulación, sus campos, destinatarios, validaciones o endpoint.
- [Solicitud] No modificar el hero, panorama, filosofía, testimonio, imagen de equipo, SEO, menú, footer ni otras páginas.
- [Regla: `AGENTS.md`] No modificar el tema padre, WordPress core, plugins de terceros, inventario, uploads ni `production-snapshot/` como fuente de contenido.
- [Regla: `docs/runbooks/DEPLOYMENT.md`] No mezclar cambios de código del child theme con una nueva escritura innecesaria de `post_content`; el carrusel debe resolverse en la presentación versionada.
- [Solicitud] No incorporar dependencias externas nuevas para el carrusel.

## Requisitos funcionales

1. [Solicitud] La sección `tmd-jobs-vacancies` debe renderizar sus tres tarjetas como un único carrusel navegable.
2. [Solicitud] El carrusel debe permitir avanzar y retroceder una tarjeta mediante controles visibles de anterior y siguiente.
3. [Solicitud] El carrusel debe mostrar indicadores de posición para las tres tarjetas y señalar cuál está activa.
4. [Solicitud] El carrusel debe admitir interacción táctil y desplazamiento horizontal en dispositivos móviles, además de los controles visibles.
5. [Solicitud] Los controles deben ser utilizables con teclado, tener nombres accesibles y no enviar accidentalmente el formulario de postulación.
6. [Solicitud] Cada tarjeta debe conservar sus clases, título, descripción, etiqueta, jornada, ubicación, salario y botón `#postulacion`; el carrusel no debe cambiar el contenido administrado.
7. [Solicitud] En escritorio y móvil debe mostrarse una tarjeta por vista del carrusel, sin overflow horizontal del documento ni recortes de texto o botones.
8. [Solicitud] La navegación debe respetar `prefers-reduced-motion` y no debe avanzar automáticamente sin una acción de la persona usuaria.
9. [Regla: `AGENTS.md`] El cambio debe permanecer localizado en los assets del child theme ya cargados para la página `ID 273`, sin introducir una dependencia nueva ni alterar el contrato del formulario.

## Reglas de negocio

- [Solicitud] Las tres vacantes continúan siendo las publicadas para Técnico Especializado en Montacargas Eléctricos, Auxiliar Técnico en Entrenamiento y Técnico Especializado en Montacargas de Combustión.
- [Regla: `AGENTS.md`] WordPress/MariaDB sigue siendo la fuente canónica del contenido de las tarjetas; el carrusel no debe crear ni duplicar contenido persistente.
- [Regla: `AGENTS.md`] La interacción debe conservar una presentación usable en escritorio y móvil, sin ocultar acciones de postulación.

## Contratos

### Entrada

```json
{
  "pageId": 273,
  "section": "tmd-jobs-vacancies",
  "cards": 3,
  "contentSource": "WordPress/MariaDB"
}
```

### Salida

```json
{
  "component": "accessible vacancy carousel",
  "slides": 3,
  "controls": ["previous", "next", "indicators"],
  "autoplay": false,
  "formAndCardContent": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] Si el script se ejecuta sin formulario o sin la configuración AJAX, el carrusel debe inicializarse sin impedir la navegación ni dejar inoperante el formulario cuando sí esté disponible.
- [Inferencia técnica] Si hay una o ninguna tarjeta, no deben aparecer controles que no puedan funcionar.
- [Inferencia técnica] Si una descripción ocupa varias líneas, la tarjeta debe conservar todo el texto y el botón dentro de su área visible.
- [Inferencia técnica] Si la persona usa teclado, foco visible y controles accesibles, no debe producirse desplazamiento horizontal de la página completa.
- [Inferencia técnica] Si el navegador solicita movimiento reducido, la navegación no debe forzar animaciones suaves.
- [Inferencia técnica] Si JavaScript no carga, el contenido no debe desaparecer ni duplicarse; debe conservar una alternativa navegable acorde con la estructura existente.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/css/tmd-job-application.css` como estilo focalizado de la página `ID 273`.
- `wp-content/themes/blocksy-child/assets/js/tmd-job-application.js` como comportamiento focalizado ya cargado en la página `ID 273`.
- `wp-content/themes/blocksy-child/inc/tmd-job-application.php` como punto de carga existente de ambos assets.
- `tests/test-job-application-js.mjs` como regresión del formulario existente.
- Nueva prueba focalizada del comportamiento del carrusel en `tests/`.
- `docs/specs/2026-09-15-actualizar-vacantes-trabaja-nosotros.md` como SPEC anterior de contenido; no se reabre su escritura persistente.

## Criterios de aceptación

1. [Solicitud] En la página `ID 273`, la sección `tmd-jobs-vacancies` contiene exactamente tres tarjetas y el usuario puede recorrerlas como carrusel.
2. [Solicitud] Los botones anterior y siguiente cambian una tarjeta por acción, respetan los extremos y tienen nombres accesibles.
3. [Solicitud] Los tres indicadores reflejan la tarjeta activa y permiten seleccionarla directamente.
4. [Solicitud] En `1440px` y `390px` cada tarjeta permanece dentro del viewport del carrusel, sin overflow horizontal del documento, recortes ni superposición de controles.
5. [Solicitud] El gesto/desplazamiento táctil y la navegación con teclado permiten acceder a las tres tarjetas y a sus botones `Aplicar a esta vacante`.
6. [Solicitud] Las tres tarjetas conservan exactamente los textos, metadatos, clases y enlaces publicados; el formulario de postulación conserva su comportamiento.
7. [Regla: `AGENTS.md`] Los assets modificados superan sus comprobaciones focalizadas, no se añaden dependencias externas y no se alteran archivos fuera del alcance.
8. [Regla: `docs/runbooks/DEPLOYMENT.md`] La publicación autorizada despliega únicamente los assets del child theme modificados, purga la caché aplicable y verifica HTTP, navegador, consola, logs y sincronización.

## Validación

- Pruebas unitarias: ejecutar una prueba JS focalizada con DOM simulado para comprobar creación de controles, avance/retroceso, indicadores, extremos, teclado, movimiento reducido y ausencia de formulario/configuración.
- Pruebas de integración: ejecutar `node --check` sobre el JS, comprobar la carga del asset mediante el enqueue existente y verificar que el CSS limita el carrusel a `body.page-id-273`/`tmd-jobs-vacancies`.
- Validación manual: revisar la página en `1440x900` y `390x844`; usar botones, indicadores, teclado y gesto táctil; confirmar foco, textos, enlaces, formulario, ausencia de overflow, recortes y errores de consola.
- Validación productiva: después de aprobación y gates, crear backup aplicable del código, controlar deriva, desplegar solo los assets modificados, purgar LiteSpeed, comprobar HTTP/HTML/navegador/logs y ejecutar el control de sincronización posterior.

## Riesgos

- [Inferencia técnica] El CSS inline administrado de la página define actualmente `tmd-jobs-grid` como `display: grid`; el estilo focalizado debe vencerlo sin alterar otras páginas.
- [Inferencia técnica] La inicialización del carrusel no debe cortar la inicialización existente del formulario cuando falta configuración AJAX o cuando el formulario cambia de estado.
- [Inferencia técnica] Un carrusel con controles incompletos puede ocultar tarjetas o hacer inaccesible el botón de postulación en móvil.
- [Inferencia técnica] La caché de LiteSpeed puede servir el JS/CSS anterior después del despliegue si no se purga y verifica.

## Decisiones pendientes

- No hay decisiones funcionales pendientes.

## Decisiones aprobadas

- `CAR-01` — [Aprobación del usuario, 2026-09-16] Mostrar una tarjeta por vista tanto en escritorio como en móvil, con desplazamiento de una tarjeta por acción.
- `CAR-02` — [Aprobación del usuario, 2026-09-16] Usar navegación manual sin autoplay, con controles anterior/siguiente e indicadores siempre visibles.
- `CAR-03` — [Aprobación del usuario, 2026-09-16] Implementar y desplegar el carrusel mediante los gates de producción; no repetir la escritura persistente de los textos.

## Registro de aprobación

- [Aprobación, 2026-09-16] El usuario aprobó este SPEC para implementación y despliegue productivo.
