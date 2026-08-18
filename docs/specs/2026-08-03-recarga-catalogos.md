# SPEC: Estabilidad de los catálogos sin recarga automática

## Estado

- Terminado

## Contexto

Los catálogos públicos de equipos y baterías consumen inventario desde WordPress,
con una caché de servidor de 24 horas. El JavaScript del inventario filtra y
pagina en el navegador sin solicitar nuevamente los datos. Separadamente, un
autoplay global busca un supuesto carrusel de marcas y hace clic cada tres
segundos en un control inferido.

La observación productiva del 3 de agosto de 2026 registró una navegación inicial
en `/equipos/` y ocho navegaciones en `/energia/` durante 30 segundos. Las
navegaciones adicionales de `/energia/` tuvieron razón `anchorClick` e iniciador
JavaScript en el HTML de esa página, línea 915, correspondiente al autoplay de
logos. El selector amplio encontró un contenedor del catálogo y el fallback tomó
un enlace común como botón siguiente.

## Problema

[Solicitud] Las secciones de catálogo de equipos y baterías recargan la página
sin una acción intencional del usuario, interrumpiendo lectura, filtros y
paginación.

## Objetivo

[Solicitud] Evitar toda recarga o navegación automática causada por componentes
de presentación mientras el usuario permanece en `/equipos/` o `/energia/`.

## Fuera del alcance

- [Solicitud] Cambiar la frecuencia de caché de 24 horas del inventario.
- [Regla: docs/domain/INVENTORY.md] Añadir polling periódico o recargas para actualizar equipos.
- [Regla: AGENTS.md] Alterar equipos, marcas, modelos, imágenes, estados o disponibilidad provenientes de Firebase.
- [Solicitud] Rediseñar filtros, tarjetas, paginación o fichas de los catálogos.
- [Regla: AGENTS.md] Desplegar o purgar caché productiva sin autorización explícita.

## Requisitos funcionales

1. [Solicitud] `/equipos/` debe permanecer en el mismo documento hasta que el usuario active voluntariamente un enlace o navegación.
2. [Solicitud] `/energia/` debe permanecer en el mismo documento hasta que el usuario active voluntariamente un enlace o navegación.
3. [Evidencia: wp-content/themes/blocksy-child/functions.php:2592] El autoplay de marcas no debe ejecutarse fuera de la página que contiene el carrusel de marcas propio.
4. [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-brand-carousel.js:2] El autoplay solo puede accionar el control siguiente declarado dentro de `[data-tmd-brand-carousel]`.
5. [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js:175] Los filtros y la paginación deben continuar funcionando en el navegador sin recarga completa.
6. [Regla: docs/domain/INVENTORY.md] La caché del inventario y la ausencia de polling periódico deben conservarse.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Inventario/Firebase continúa como fuente canónica de equipos y baterías.

## Contratos

### Entrada

- Presencia opcional de un carrusel con `[data-tmd-brand-carousel]` y un control `[data-brand-next]`.
- Interacciones de filtros, paginación, hover y visibilidad del documento.

### Salida

- Los catálogos no generan solicitudes `Document` automáticas.
- Cuando existe el carrusel propio en Home, solo su control siguiente puede recibir el clic de autoplay.

## Casos límite

- [Evidencia: wp-content/themes/blocksy-child/functions.php:2636] Una página contiene nombres o imágenes de marcas, botones y enlaces, pero no el carrusel propio.
- [Evidencia: wp-content/themes/blocksy-child/functions.php:2680] La página no contiene carrusel ni control siguiente.
- [Evidencia: wp-content/themes/blocksy-child/functions.php:2706] El usuario mantiene el puntero sobre el carrusel o la pestaña está oculta.
- [Solicitud] El usuario permanece en cada catálogo durante varios intervalos consecutivos sin interactuar.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/functions.php`
- `wp-content/themes/blocksy-child/inc/tmd-brand-carousel.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-brand-carousel.js`
- `wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js`
- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`

## Criterios de aceptación

1. [Solicitud] Monitorear `/equipos/` por al menos cuatro intervalos de autoplay registra una sola navegación `Document`: la carga inicial.
2. [Solicitud] Monitorear `/energia/` por al menos cuatro intervalos de autoplay registra una sola navegación `Document`: la carga inicial.
3. [Solicitud] Cambiar filtros, limpiar filtros y usar paginación no produce una recarga completa del documento.
4. [Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-brand-carousel.js:30] En Home, si existe el carrusel propio, sus controles manuales continúan funcionando y el autoplay no acciona enlaces externos al carrusel.
5. [Regla: docs/domain/INVENTORY.md] El cambio no añade polling ni modifica `DAY_IN_SECONDS` para la caché del inventario.
6. [Regla: AGENTS.md] El parche queda limitado al código canónico del child theme y preserva cambios locales ajenos.

## Validación

- Pruebas unitarias: Comprobación focalizada del selector de carrusel, ausencia de carrusel y elección exclusiva de `[data-brand-next]`.
- Pruebas de integración: Monitor de navegaciones `Document` durante al menos 12 segundos en `/equipos/` y `/energia/` con el cambio cargado.
- Validación manual: Probar filtros y paginación en escritorio y móvil; revisar consola y funcionamiento del carrusel de Home.
- Validación productiva: Solo tras despliegue autorizado: backup, `./scripts/sync-production.sh --check`, despliegue puntual, purga de caché y monitor de navegación en ambos catálogos.
- Evidencia local de cierre (2026-08-03): `php -l`, comprobación sintáctica del JavaScript inline y `git diff --check` pasaron; los arneses focalizados completaron 17 aserciones para el guard de Home, selectores, ausencia de carrusel, autoplay, hover y pestaña oculta.
- Evidencia de navegador (2026-08-03): Chrome headless cargó HTML público con el parche sustituido solo en memoria. `/equipos/` y `/energia/` conservaron una única navegación `Document` durante 13 segundos y después de cambiar filtros, aplicar, limpiar y paginar; Home produjo dos clics de autoplay, ambos sobre `[data-brand-next]`; no hubo excepciones JavaScript.
- Revisión: `test_reviewer` y `performance_reviewer` terminaron en `NO_FINDINGS` después de completar la evidencia de navegador.
- Producción: no modificada ni validada con el archivo desplegado porque no existe autorización de despliegue en esta tarea.

## Riesgos

- Restringir incorrectamente el autoplay puede detener el avance automático del carrusel de marcas en Home.
- Una versión combinada en caché puede mantener el script anterior después de desplegar el archivo fuente.

## Decisiones pendientes

- No aplica.
