# SPEC: Permanencia del megamenú en escritorio

## Estado

- Terminado

## Contexto

El megamenú abre sus paneles desde los botones principales mediante `mouseenter`,
`focus` y `click`. Cuando el panel activo y el botón señalado no corresponden a
la misma sección, la implementación cierra todos los paneles antes de abrir el
nuevo. El cierre exterior y el cierre con `Escape` también están registrados en
el documento.

## Problema

[Solicitud] Después de abrir una sección del megamenú en escritorio, el panel
puede desaparecer o cambiar sin que el usuario haya hecho clic fuera del menú.

## Objetivo

[Solicitud] Mantener estable el panel abierto durante la interacción con el
megamenú y cerrarlo por interacción de puntero únicamente cuando el usuario haga
clic fuera de toda el área del menú.

## Fuera del alcance

- [Solicitud] Cambiar contenido, enlaces, imágenes o presentación visual del megamenú.
- [Regla: AGENTS.md] Modificar el tema padre, WordPress core o plugins de terceros.
- [Regla: AGENTS.md] Desplegar, purgar caché o escribir en producción sin autorización explícita.
- [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js:37] Cambiar el comportamiento del menú móvil.

## Requisitos funcionales

1. [Solicitud] En escritorio, una sección abierta debe permanecer visible al mover el puntero dentro del encabezado, del panel o entre los controles del megamenú.
2. [Solicitud] En escritorio, pasar el puntero o mover el foco hacia el botón de otra sección no debe reemplazar ni cerrar el panel ya abierto.
3. [Solicitud] Hacer clic en otro botón de sección debe mostrar esa sección y mantener el megamenú abierto.
4. [Solicitud] Hacer clic fuera de `#tmdMegaMenu` debe cerrar el panel abierto.
5. [Regla: docs/domain/NAVIGATION.md] La tecla `Escape` debe conservar el cierre explícito para usuarios de teclado.
6. [Regla: docs/domain/NAVIGATION.md] El panel activo y los atributos `aria-expanded` deben representar el estado visible real.

## Reglas de negocio

- No aplica.

## Contratos

### Entrada

- Eventos de puntero, foco, clic y teclado recibidos por `#tmdMegaMenu` y el documento.

### Salida

- Un único panel visible, su botón activo y `aria-expanded="true"`; o ningún panel visible tras clic exterior o `Escape`.

## Casos límite

- [Solicitud] Mover rápidamente el puntero desde una sección activa hacia otro botón del encabezado.
- [Solicitud] Hacer clic repetido en el botón de la sección ya abierta.
- [Regla: docs/domain/NAVIGATION.md] Navegar entre botones y contenido interno mediante teclado.
- [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js:16] No existe `#tmdMegaMenu` en la página; el script debe terminar sin errores.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`

## Criterios de aceptación

1. [Solicitud] Abierto `EQUIPOS` en un viewport de escritorio, recorrer con el puntero `ENERGÍA`, `SERVICIOS`, `NOSOTROS` y el panel no oculta ni reemplaza `EQUIPOS` sin clic.
2. [Solicitud] Con `EQUIPOS` abierto, hacer clic en `ENERGÍA` reemplaza el contenido por `ENERGÍA` sin cerrar el contenedor del megamenú.
3. [Solicitud] Con cualquier panel abierto, hacer clic dentro de `#tmdMegaMenu` no lo cierra y hacer clic fuera sí lo cierra.
4. [Regla: docs/domain/NAVIGATION.md] Con teclado, foco y `aria-expanded` permanecen coherentes; `Escape` cierra el panel.
5. [Regla: AGENTS.md] El cambio se limita a la fuente canónica del child theme y no modifica los cambios locales ajenos.

## Validación

- Pruebas unitarias: No aplica; el repositorio no contiene un arnés unitario para este script de navegador.
- Pruebas de integración: Comprobación focalizada de sintaxis JavaScript y de la matriz `mouseenter`/`focus`/`click`/clic exterior/`Escape`.
- Validación manual: Verificar escritorio y móvil, ausencia de errores de consola, carga del asset, estado visual y atributos ARIA.
- Validación productiva: Solo después de un despliegue autorizado: backup, `./scripts/sync-production.sh --check`, despliegue del archivo puntual, purga de caché y prueba HTTP/navegador.
- Evidencia local de cierre (2026-08-03): `node --check` pasó; fixture en Chrome headless devolvió `PASS` para hover y foco en escritorio, cambio por clic, clic interior, clic exterior, `Escape`, ARIA y foco con `(hover: none)`; revisión focalizada terminó en `NO_FINDINGS`; `git diff --check` pasó.
- Producción: no modificada ni validada porque no existe autorización de despliegue en esta tarea.

## Riesgos

- Bloquear cambios por `focus` cuando ya existe un panel abierto puede alterar la expectativa de navegación por teclado; la validación debe confirmar que el clic y `Enter` sigan seleccionando otra sección.
- Caché o combinación de JavaScript en producción puede servir una versión anterior aunque el archivo fuente haya sido actualizado.

## Decisiones pendientes

- No aplica.
