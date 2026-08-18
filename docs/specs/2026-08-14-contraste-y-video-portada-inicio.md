# SPEC: Contraste y video de portada de Inicio

## Estado

- Aprobado

## Contexto

[Solicitud] La portada de Inicio debe tomar como referencia el contraste de un hero industrial: video oscurecido, texto legible, degradado superior para navegación y profundidad inferior, sin copiar identidad ni composición de Hyster.

[Solicitud] Se deben evaluar todos los videos de `img/videos-general` y compararlos con el video vigente para escoger el que mejor funcione en la portada; el video actual debe permanecer si sigue siendo la mejor opción.

[Evidencia: img/videos-general/] La carpeta contiene 55 videos locales candidatos, con tamaños entre 12 MB y 744 MB. No son todavía multimedia publicada ni una fuente canónica de producción.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:42-60] Inicio ya identifica el hero Kadence por `.kb-row-layout-id47_9c201d-d2`, tiene altura de viewport y aplica un overlay sobre su wrapper.

[Evidencia: production-snapshot/pages.json:39] La captura histórica declara que el bloque Kadence de Inicio usa como video local el recurso de julio de 2026 y el overlay `#262E4F`; confirmar su contenido vigente antes de cualquier escritura persistente.

## Problema

El tratamiento actual del video no define por separado el contraste general, la legibilidad superior del header y una profundidad inferior compacta; además, no existe una selección documentada del video más adecuado entre los candidatos disponibles.

## Objetivo

Garantizar una portada de Inicio industrial y profesional, con video seleccionado de forma justificada, texto y navegación legibles, y overlays acotados que dejen el video claramente visible hacia la mitad de la portada.

## Fuera del alcance

- Copiar la marca, composición, recursos o identidad visual de Hyster.
- Cambiar textos, botones, enlaces, estructura, reproducción, controles o accesibilidad funcional de la portada.
- Aplicar estos overlays a otras rutas o bloques Kadence.
- Reemplazar un video por preferencia sin evidencia comparativa.
- Modificar WordPress core, tema padre, plugins de terceros, infraestructura o multimedia ajena a la portada.

## Requisitos funcionales

1. [Solicitud] Deben evaluarse todos los videos de `img/videos-general` y el video vigente según representación industrial/logística/energética, composición para texto, ruido visual, compatibilidad con `object-fit: cover`, movimiento, peso web y tono profesional.
2. [Solicitud] Si un candidato supera al video vigente, debe documentarse brevemente su elección y reemplazar únicamente el video de fondo de la portada; si no lo supera, el video vigente debe conservarse.
3. [Solicitud] El video de la portada debe permanecer al fondo con `object-fit: cover`, sin pseudo-elementos aplicados directamente al elemento `video`.
4. [Solicitud] El wrapper real de la portada debe aplicar, sin interceptar interacción, un overlay azul oscuro semitransparente general, un degradado superior para navegación y un degradado inferior `#262E4F`.
5. [Solicitud] El degradado inferior debe ser intenso solo en su parte baja y terminar antes del centro: máximo 28% a 34% de la altura de hero en escritorio y aproximadamente 24% a 30% en móvil.
6. [Solicitud] Aproximadamente desde la mitad vertical de la portada, el video debe verse sensiblemente más claro que en la franja inferior.
7. [Solicitud] El contenido de portada debe mantenerse sobre video y overlays con el `z-index` necesario; los overlays deben usar `pointer-events: none`.
8. [Solicitud] El texto y controles blancos del header deben conservar legibilidad sobre el video; el `text-shadow` del título debe reducirse solo si el contraste de overlays lo permite.
9. [Regla: docs/domain/NAVIGATION.md] Deben preservarse las interacciones del mega menú por hover, foco, clic, Escape, clic exterior y teclado.
10. [Regla: docs/domain/NAVIGATION.md] El cambio debe ser usable en escritorio y móvil, sin overflow horizontal.

## Reglas de negocio

- [Regla: AGENTS.md] El tema activo y fuente canónica de los estilos propios es `wp-content/themes/blocksy-child/`.
- [Regla: AGENTS.md] No se deben inventar videos, imágenes, marcas ni hechos comerciales; el material no publicado debe verificarse antes de usarlo.
- [Regla: docs/runbooks/DEPLOYMENT.md] Un cambio de archivos o contenido productivo requiere backup verificado, despliegue focalizado y control de deriva antes y después.

## Contratos

### Entrada

```json
{
  "route": "/",
  "heroSelector": ".kb-row-layout-id47_9c201d-d2",
  "candidateDirectory": "img/videos-general",
  "bottomGradient": {
    "color": "#262E4F",
    "desktopMaxHeight": "34%",
    "mobileMaxHeight": "30%"
  }
}
```

### Salida

```json
{
  "selectedVideo": "actual o candidato justificado",
  "heroVideo": "object-fit: cover",
  "overlays": ["azul oscuro general", "degradado superior", "degradado inferior compacto #262E4F"],
  "scope": "solo portada de Inicio"
}
```

## Casos límite

- Un candidato tiene buena composición pero un tamaño que no es adecuado para publicación web.
- El encuadre con `object-fit: cover` recorta el motivo importante en viewport móvil.
- El video falla, carga lentamente o no puede reproducirse: contenido y navegación permanecen legibles.
- El hero se muestra con barra de administración, scroll, menú abierto, foco por teclado y cambio de orientación.
- Los degradados compiten con botones, logo o texto y reducen su contraste.

## Archivos o módulos relacionados

- `img/videos-general/` (evaluación local; no desplegar directamente)
- Contenido administrado de la página Inicio (confirmar antes de modificarlo)
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `docs/domain/NAVIGATION.md`
- `docs/runbooks/BACKUP_RESTORE.md`
- `docs/runbooks/DEPLOYMENT.md`

## Criterios de aceptación

1. [Solicitud] Existe una comparación explícita entre el video vigente y los 55 candidatos, con motivo de conservarlo o de elegir un único reemplazo.
2. [Solicitud] El video elegido muestra un motivo industrial/logístico/energético claro y permite leer título, descripción, botones, logo y navegación en escritorio y móvil.
3. [Solicitud] En escritorio, el degradado inferior `#262E4F` no supera el 34% de la altura del hero y se desvanece antes de la mitad; en móvil no supera el 30%.
4. [Solicitud] El overlay general y el degradado superior mejoran el contraste sin ocultar el video ni bloquear clics, foco o interacción.
5. [Solicitud] El video usa `object-fit: cover` y los overlays se aplican al wrapper, no directamente al elemento `video`.
6. [Regla: docs/domain/NAVIGATION.md] No aparecen overflow horizontal, errores de consola ni regresiones de navegación o mega menú en Chrome, Safari y móvil.
7. [Regla: docs/runbooks/DEPLOYMENT.md] Si se publica un video nuevo, se respaldan contenido y multimedia afectados, se verifica tamaño/formato/URL, se purga la caché necesaria y el control de sincronización finaliza sin deriva de archivos versionados.

## Validación

- Pruebas unitarias: No aplica; se trata de selección multimedia, CSS y comportamiento visual.
- Pruebas de integración: verificar selector exclusivo de Inicio, capas separadas, `pointer-events`, `z-index`, `object-fit` y los estados de scroll del header.
- Validación manual: comparar los 55 candidatos con el video vigente mediante miniaturas y reproducción de finalistas; comprobar encuadre, peso, contraste, desktop y móvil.
- Validación productiva: solo con autorización explícita, confirmar el bloque Kadence actual, respaldar los archivos y el contenido/multimedia si cambia el video, desplegar el alcance exacto, purgar caché y validar HTTP, Chrome, Safari, móvil, consola, navegación y sincronización.

## Riesgos

- Un candidato grande puede aumentar de forma inaceptable la transferencia inicial si se publica sin optimización.
- Cambiar el video desde contenido persistente puede alterar bloques Kadence ajenos si no se preserva el contenido completo.
- Un selector no encapsulado puede cambiar overlays de otras páginas.
- Reducir demasiado las capas puede comprometer la legibilidad sobre secuencias claras del video.

## Decisiones pendientes

- No aplica. El usuario aprobó conservar el video vigente tras la evaluación de los 55 candidatos; no se publica multimedia nueva.

## Registro de aprobación

- [Aprobación, 2026-08-14] El usuario aprobó implementar el tratamiento visual y conservar el video vigente.
