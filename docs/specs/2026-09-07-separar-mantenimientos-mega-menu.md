# SPEC: Separar mantenimientos preventivos y correctivos en Servicios

## Estado

- Aprobado

## Contexto

[Solicitud] Se solicita separar visualmente el área de Servicios mostrada en el
mega menú para que presente por separado los mantenimientos preventivos y
correctivos.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:232-248]
El panel de escritorio `tmd-mm-panel-mant` contiene una única tarjeta con la
imagen de mantenimiento, el título genérico “Mantenimientos” y dos enlaces
subordinados “Preventivo” y “Correctivo”.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:356-380]
La navegación móvil conserva la misma agrupación en un único grupo de
“Mantenimientos”.

## Problema

[Solicitud] Los servicios preventivo y correctivo aparecen agrupados bajo un
único bloque y no se distinguen como dos áreas independientes.

[Solicitud 2026-09-07] La presentación resultante no conserva el estándar
visual del panel Energía: las imágenes de Servicios quedan dentro de una
tarjeta anidada, con anchos menores y recortes distintos.

## Objetivo

[Solicitud] Mostrar dos áreas claramente separadas dentro de Servicios. Cada
área debe tener el título “Mantenimiento” y debajo su opción correspondiente:
“Preventivo” o “Correctivos”, conservando la navegación existente.

## Fuera del alcance

- [Solicitud] No cambiar las rutas actuales de mantenimiento.
- [Solicitud] No modificar otras secciones del mega menú, el footer ni las
  páginas de destino.
- [Regla: AGENTS.md] No inventar imágenes, marcas, servicios, precios ni datos
  comerciales.
- [Regla: docs/domain/NAVIGATION.md] No eliminar el acceso de Servicios ni
  alterar el cierre con Escape, clic exterior o teclado.
- [Regla: AGENTS.md] No modificar el tema padre, WordPress core ni plugins de
  terceros.
- [Regla: AGENTS.md] No escribir en producción, desplegar ni purgar caché sin
  autorización operativa, backup y reversión identificada.

## Requisitos funcionales

1. [Solicitud] El panel de escritorio de Servicios debe conservar la imagen
   actual de mantenimiento encima de “Preventivo” y presentar dos áreas
   diferenciadas. Cada área debe tener el título “Mantenimiento” y debajo su
   opción correspondiente: “Preventivo” o “Correctivos”. Encima de
   “Correctivos” debe mostrarse la imagen de Medios
   `https://tecnimontacargas.com/wp-content/uploads/2026/09/Screenshot-2026-09-07-at-10.44.12-AM.png`.
2. [Solicitud] “Preventivo” debe conservar como destino
   `/mantenimiento/mantenimiento-preventivo/`.
3. [Solicitud] “Correctivos” debe conservar como destino
   `/mantenimiento/mantenimiento-correctivo/`.
4. [Solicitud] La opción de acceso general a `/mantenimiento/` debe conservarse
   desde el área Servicios.
5. [Solicitud] La navegación móvil debe mostrar también los dos grupos
   separados, con sus respectivos títulos y destinos, sin duplicar ni perder
   enlaces.
6. [Regla: docs/domain/NAVIGATION.md] El mega menú debe seguir funcionando con
   hover, foco, clic, Escape, clic exterior y teclado.
7. [Regla: docs/domain/NAVIGATION.md] El cambio debe conservar el comportamiento
   responsive y no producir overflow horizontal.

## Reglas de negocio

- [Regla: AGENTS.md] WordPress mantiene las rutas y contenido de navegación
  administrados por el sitio; no se deben inventar destinos.
- [Regla: docs/domain/NAVIGATION.md] El menú principal debe conservar sus
  secciones y controles existentes.

## Contratos

### Entrada

```json
{
  "panel": "tmd-mm-panel-mant",
  "preventiveUrl": "/mantenimiento/mantenimiento-preventivo/",
  "correctiveUrl": "/mantenimiento/mantenimiento-correctivo/"
}
```

### Salida

```json
{
  "desktop": [
    {"title": "Mantenimiento", "link": "Preventivo"},
    {"title": "Mantenimiento", "link": "Correctivos"}
  ],
  "mobile": [
    {"title": "Mantenimiento", "link": "Preventivo"},
    {"title": "Mantenimiento", "link": "Correctivos"}
  ],
  "correctiveImage": "https://tecnimontacargas.com/wp-content/uploads/2026/09/Screenshot-2026-09-07-at-10.44.12-AM.png",
  "existingRoutes": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] En móvil, los dos grupos deben apilarse sin forzar una
  fila horizontal ni desbordar el viewport.
- [Inferencia técnica] Cada imagen debe permanecer dentro de los límites
  visuales actuales y no producir overflow ni deformación.
- [Inferencia técnica] Si los controles del panel dependen de IDs o clases
  actuales, deben conservarse.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/assets/css/tmd-mobile-menu.css`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En escritorio se visualizan dos áreas; cada una muestra el
   título “Mantenimiento” y debajo “Preventivo” o “Correctivos”.
2. [Solicitud] Cada opción inferior conserva el enlace correcto a su página
   actual.
3. [Solicitud] La imagen actual de mantenimiento aparece encima de
   “Preventivo” y la imagen de Medios indicada aparece encima de
   “Correctivos”.
4. [Solicitud] Las demás secciones del mega menú permanecen sin cambios.
5. [Solicitud] En móvil se visualizan los dos grupos separados y sus enlaces
   funcionan sin overflow horizontal.
6. [Regla: docs/domain/NAVIGATION.md] El panel conserva apertura, cierre,
   navegación por teclado y comportamiento responsive.
7. [Regla: AGENTS.md] La producción solo se modifica después de backup
   verificable, control de deriva y autorización operativa.
8. [Solicitud 2026-09-07] Las dos áreas de escritorio deben usar tarjetas
   hermanas del mismo grid y el mismo patrón de imagen del panel Energía:
   imagen contenida, sin deformación ni recorte `cover`, con dimensiones
   equivalentes y comportamiento responsive.

## Validación

- Pruebas unitarias: No aplica; el cambio es markup/CSS del mega menú.
- Pruebas de integración: Ejecutar `php -l` sobre el template modificado y
  comprobar que las rutas y clases esperadas estén presentes una sola vez.
- Validación manual: Revisar el panel de Servicios en escritorio y móvil,
  verificar ambos títulos, destinos, apertura/cierre, teclado y ausencia de
  overflow horizontal.
- Validación productiva: Pendiente de autorización explícita; requiere
  backup, control de deriva, despliegue focalizado, purga de caché aplicable y
  verificación HTTP/navegador.

## Riesgos

- [Inferencia técnica] Una separación en columnas puede necesitar apilado
  responsive para no comprimir los títulos en anchos intermedios.
- [Inferencia técnica] Plugins o ajustes visuales productivos fuera del checkout
  pueden alterar la apariencia final y deben compararse antes de desplegar.

## Decisiones pendientes

- [Decisión resuelta, 2026-09-07] Se divide el contenido en dos áreas; cada una
  muestra el título “Mantenimiento” y debajo “Preventivo” o “Correctivos”. La
  imagen actual queda encima de “Preventivo” y la imagen de Medios indicada por
  el usuario queda encima de “Correctivos”.
- [Decisión resuelta, 2026-09-07] El usuario aprobó este SPEC para implementar el
  cambio.
