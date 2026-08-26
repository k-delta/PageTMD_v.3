# SPEC: Navegación móvil dedicada del megamenú

## Estado

- Aprobado por solicitud del usuario el 26 de agosto de 2026.

## Contexto

El header actual reutiliza en móvil el megamenú de escritorio: debajo de `1024px` oculta los controles principales, pero al abrirlos vuelve a mostrar los mismos botones y paneles apilados. Los paneles conservan contenido e imágenes pensados para escritorio y no existe un drawer móvil con altura limitada al viewport.

El comportamiento de permanencia del megamenú de escritorio ya está documentado y debe conservarse. Esta tarea no lo reemplaza.

## Problema

En teléfono la navegación resulta demasiado larga, mezcla el patrón de megamenú con interacción táctil y no ofrece un acordeón claro ni un área de scroll propia. En la portada el header es fijo, por lo que un menú móvil largo puede superar el viewport y dificultar llegar a enlaces inferiores.

## Objetivo

Separar la experiencia móvil de la de escritorio: conservar el megamenú actual a partir de `1025px` y usar hasta `1024px` un drawer táctil, scrollable y accesible con secciones tipo acordeón.

## Requisitos funcionales

1. El header móvil conserva únicamente logo y botón de menú en la barra superior.
2. El botón cambia entre icono de menú y cierre y mantiene `aria-expanded` coherente.
3. El drawer ocupa como máximo el alto visible debajo del header y usa scroll vertical propio.
4. La página de fondo no hace scroll mientras el drawer está abierto.
5. Las secciones principales son `Inicio`, `Equipos`, `Energía`, `Servicios` y `Nosotros`.
6. `Equipos`, `Energía`, `Servicios` y `Nosotros` ofrecen dos acciones separadas: el texto navega a la página principal y el chevron abre/cierra el acordeón.
7. Solo puede existir un acordeón abierto a la vez.
8. Pulsar de nuevo el chevron de una sección abierta la cierra.
9. El drawer móvil no muestra las imágenes del megamenú de escritorio.
10. Buscar y cuenta aparecen como acciones con icono y texto.
11. El drawer se cierra al seguir un enlace, pulsar el fondo exterior, pulsar `Escape` o cambiar de layout móvil a escritorio.
12. Los estados `aria-expanded`, `aria-hidden` e `inert` reflejan el estado visible real.
13. En la portada, abrir el menú fuerza el fondo azul corporativo del navbar para mantener contraste sobre el video.
14. El menú de escritorio conserva hover, foco, clic, permanencia del panel, clic exterior y `Escape` actuales.

## Presentación móvil

- Breakpoint: `max-width: 1024px`.
- Navbar: 60px de referencia.
- Drawer: ancho máximo 440px; 100% en teléfonos pequeños.
- Fondo principal blanco, superficies secundarias gris muy claro.
- Azul oscuro `#262E4F`, azul `#128CEB` y amarillo `#FFC33C`.
- Filas principales de aproximadamente 54px y objetivos táctiles de al menos 44px.
- Tipografía Work Sans, 17px para niveles principales y 15–16px para subniveles.
- Radios de 8px en controles, sin tarjetas visuales ni fotografías dentro del drawer.

## Fuera del alcance

- Cambiar el contenido, enlaces, nombres técnicos o imágenes del megamenú de escritorio.
- Cambiar fotografías del menú.
- Cambiar Cargadores, Baterías o BMS como páginas de contenido.
- Rediseñar header/footer de escritorio.
- Reemplazar el componente completo fuera de la fuente canónica del child theme.

## Archivos

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `wp-content/themes/blocksy-child/assets/css/tmd-mobile-menu.css`

## Criterios de aceptación

1. En 390px y 430px el menú abre sin overflow horizontal y permite llegar a todas las opciones mediante scroll interno.
2. Abrir `Equipos` y luego `Energía` cierra `Equipos` y abre `Energía`.
3. Pulsar nuevamente el chevron de `Energía` la cierra.
4. Tocar el texto `Equipos` navega a `/equipos/` sin depender del acordeón.
5. El drawer no renderiza ninguna imagen del megamenú.
6. Buscar y cuenta son legibles y táctiles.
7. Al cerrar se recupera el scroll de la página.
8. Al redimensionar a escritorio se limpia todo estado móvil.
9. En escritorio se mantienen los criterios del SPEC de permanencia del megamenú.
10. `php -l` del template y `node --check` del script pasan sin errores.

## Validación productiva

Después del despliegue y purga de caché, validar al menos:

- Home en 390x844 y 430x932.
- Una página interna en móvil.
- Home en escritorio 1440x900 para confirmar que el megamenú no cambió.
- Abrir/cerrar cada acordeón, seguir un enlace, fondo exterior, `Escape`, scroll y resize.
- Ausencia de errores de consola y de overflow horizontal.
