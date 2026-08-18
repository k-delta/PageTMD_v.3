# SPEC: Portada de video y header transparente en inicio

## Estado

- Aprobado

## Contexto

[Solicitud] En Inicio, la capa azul actual que cubre el video debe pasar de opaca en la parte inferior a transparente en la parte superior, conservando el azul actual.

[Solicitud] El video debe ocupar toda la altura visible en la primera vista del usuario.

[Solicitud] Mientras la primera vista muestre el video, el fondo azul fuerte del header debe ser transparente, conservando blancos los textos e iconos; al hacer scroll hacia abajo debe volver el fondo azul actual.

[Evidencia: screenshot proporcionado, 2026-08-13] La portada muestra el video bajo una capa azul uniforme y el header canónico muestra el fondo azul fuerte.

[Evidencia: production-snapshot/pages.json:39] La captura histórica de la página Inicio declara un bloque Kadence con video local, `overlay` `#262E4F` y opacidad `72`. Es evidencia de referencia, no sustituto de la comprobación productiva antes de escribir contenido.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:12-38] El header propio es sticky y su barra navega con fondo `#262e4f`.

## Problema

La portada de Inicio no aprovecha toda la primera vista, su cobertura azul tiene intensidad uniforme y el header opaco corta visualmente la continuidad del video.

## Objetivo

Presentar una portada de Inicio a pantalla completa, con la cobertura azul existente degradada de transparente arriba a opaca abajo, y un header inicialmente transparente que recupere su fondo actual al abandonar visualmente la portada.

## Fuera del alcance

- Cambiar el archivo de video, su URL, reproducción, audio, controles o atribuciones.
- Cambiar los textos, llamadas a la acción, logo, menús, enlaces, mega menú, buscador o cuenta.
- Aplicar el header transparente a rutas distintas de Inicio.
- Modificar WordPress core, el tema padre, plugins de terceros, infraestructura o configuraciones globales de Kadence.
- Rediseñar las secciones posteriores de Inicio.

## Requisitos funcionales

1. [Solicitud] La primera sección de Inicio que contiene el video debe ocupar como mínimo toda la altura visible del viewport, incluyendo el área que queda bajo el header.
2. [Solicitud] La capa de cobertura del video debe conservar el azul actual y cambiar de transparencia total en su borde superior a opacidad total en el borde inferior mediante un degradado vertical continuo.
3. [Solicitud] En Inicio, al cargar y mientras el video cubra el área del header, el fondo de la barra de navegación debe ser transparente y no proyectar el fondo azul fuerte actual.
4. [Solicitud] En el estado transparente de Inicio, los textos, logo e iconos del header deben conservar su presentación blanca y los controles deben seguir siendo visibles y utilizables.
5. [Solicitud] En Inicio, al desplazarse de manera que el video deje de cubrir el header, la barra debe recuperar el fondo `#262e4f` actualmente usado y conservarlo durante el resto de la página.
6. [Regla: docs/domain/NAVIGATION.md] El comportamiento existente de apertura por hover, foco y clic; cierre por Escape o clic exterior; y navegación por teclado del mega menú debe conservarse.
7. [Regla: docs/domain/NAVIGATION.md] El resultado debe ser usable en escritorio y móvil, sin overflow horizontal.

## Reglas de negocio

- [Regla: AGENTS.md] El header canónico pertenece a `wp-content/themes/blocksy-child/template-parts/tmd-header.php` y sus estilos/scripts propios deben modificarse con un parche focalizado.
- [Regla: AGENTS.md] No se debe inventar ni sustituir contenido comercial o multimedia.
- [Regla: docs/runbooks/DEPLOYMENT.md] Toda escritura material en producción requiere backup verificado y el despliegue debe detenerse si el control de sincronización detecta deriva.

## Contratos

### Entrada

```json
{
  "route": "/",
  "hero": "bloque Kadence con video de fondo vigente",
  "header": ".tmd-mm-header > .tmd-mm-wrap > .tmd-mm-navbar"
}
```

### Salida

```json
{
  "heroHeight": "al menos 100vh o 100svh segun soporte del navegador",
  "heroOverlay": "degradado vertical del azul actual: transparente arriba, opaco abajo",
  "headerAtHero": "transparente con contenido blanco",
  "headerAfterHero": "fondo #262e4f"
}
```

## Casos límite

- Navegador móvil con barras del sistema dinámicas: la altura debe evitar un espacio en blanco o recorte de contenido.
- Navegador sin soporte de unidades dinámicas: la portada conserva una altura completa razonable con fallback compatible.
- Carga lenta, fallo o reproducción bloqueada del video: el fondo/degradado y el contenido de portada permanecen legibles.
- Apertura de menú, foco de teclado, hover, Escape y clic exterior tanto con header transparente como con fondo restaurado.
- Cambio de tamaño, recarga en una posición desplazada, regreso con historial y navegación directa a `/#...`: el estado visual del header coincide con la posición visible.
- Barra de administración de WordPress y breakpoints móviles existentes.

## Archivos o módulos relacionados

- Contenido administrado de la página Inicio (confirmar el bloque Kadence vigente antes de editarlo).
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `wp-content/themes/blocksy-child/functions.php`
- `docs/runbooks/BACKUP_RESTORE.md`
- `docs/runbooks/DEPLOYMENT.md`

## Criterios de aceptación

1. [Solicitud] En escritorio y móvil, al abrir Inicio el video llena la primera vista de extremo a extremo en altura, sin una franja vacía entre el header y la portada.
2. [Solicitud] En la portada, la cobertura azul visible es transparente en el borde superior y alcanza opacidad completa en el borde inferior, manteniendo el color azul actual.
3. [Solicitud] En la portada inicial de Inicio, el header no presenta su rectángulo azul fuerte; sus textos, logo e iconos continúan legibles y operables.
4. [Solicitud] Al desplazarse más allá del video, el header muestra nuevamente su fondo `#262e4f`; al volver al video, recupera su estado transparente.
5. [Regla: docs/domain/NAVIGATION.md] Los menús conservan hover, foco, clic, Escape, clic exterior y uso por teclado sin errores de consola.
6. [Regla: docs/runbooks/DEPLOYMENT.md] No hay overflow horizontal, imágenes/recursos rotos, errores HTTP nuevos ni regresiones visuales en Chrome, Safari y móvil.
7. [Regla: docs/runbooks/DEPLOYMENT.md] Tras el despliegue autorizado, los archivos propios desplegados coinciden con el manifiesto y `./scripts/sync-production.sh --check` finaliza sin deriva.

## Validación

- Pruebas unitarias: No aplica; el cambio es visual y de interacción en navegador.
- Pruebas de integración: comprobación focalizada del selector de Inicio, de los dos estados del header y del degradado aplicado al bloque de video vigente.
- Validación manual local: revisar el diff, sintaxis PHP si se modifica, CSS/JS focalizado, consola, carga de assets, flujo de menú y responsive en escritorio y móvil.
- Validación productiva autorizada: comprobar antes de editar el bloque Kadence actual; respaldar el contenido persistente y cada archivo afectado; ejecutar `./scripts/sync-production.sh --check`; desplegar solo archivos propios modificados; purgar únicamente cachés pertinentes; verificar HTTP, Chrome, Safari, móvil, consola, menú, retorno del fondo al scroll y ejecutar de nuevo el control de sincronización.

## Riesgos

- Una edición directa del `post_content` de Inicio puede alterar bloques Kadence ajenos si no se preserva su contenido completo.
- Un selector de portada demasiado amplio puede afectar otros bloques o rutas.
- Un estado de header calculado incorrectamente puede dejarlo transparente sobre contenido claro o impedir la interacción con el mega menú.
- Cachés de WordPress/LiteSpeed pueden retrasar la visibilidad del cambio aun si el despliegue terminó correctamente.

## Decisiones pendientes

- No aplica. La solicitud define el alcance funcional: el degradado usa el azul vigente, la portada ocupa la primera vista y el header solo es transparente sobre ese video en Inicio.

## Registro de aprobación

- [Aprobación, 2026-08-13] El usuario autorizó la implementación y el despliegue productivo de este alcance.
