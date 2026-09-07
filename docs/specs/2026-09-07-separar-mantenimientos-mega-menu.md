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

## Objetivo

[Solicitud] Mostrar “Mantenimientos preventivos” y “Mantenimientos correctivos”
como dos opciones claramente separadas dentro del área Servicios, conservando
la navegación existente.

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
   actual de mantenimiento una sola vez y presentar dos áreas diferenciadas:
   “Mantenimientos preventivos” y “Mantenimientos correctivos”.
2. [Solicitud] “Mantenimientos preventivos” debe conservar como destino
   `/mantenimiento/mantenimiento-preventivo/`.
3. [Solicitud] “Mantenimientos correctivos” debe conservar como destino
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
    "Mantenimientos preventivos",
    "Mantenimientos correctivos"
  ],
  "mobile": [
    "Mantenimientos preventivos",
    "Mantenimientos correctivos"
  ],
  "existingRoutes": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] En móvil, los dos grupos deben apilarse sin forzar una
  fila horizontal ni desbordar el viewport.
- [Inferencia técnica] La imagen compartida no debe duplicarse ni deformarse
  como consecuencia de separar los enlaces.
- [Inferencia técnica] Si los controles del panel dependen de IDs o clases
  actuales, deben conservarse.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/assets/css/tmd-mobile-menu.css`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En escritorio se visualizan por separado “Mantenimientos
   preventivos” y “Mantenimientos correctivos”.
2. [Solicitud] Cada título conserva el enlace correcto a su página actual.
3. [Solicitud] La imagen actual de mantenimiento aparece una sola vez y las
   demás secciones del mega menú permanecen sin cambios.
4. [Solicitud] En móvil se visualizan los dos grupos separados y sus enlaces
   funcionan sin overflow horizontal.
5. [Regla: docs/domain/NAVIGATION.md] El panel conserva apertura, cierre,
   navegación por teclado y comportamiento responsive.
6. [Regla: AGENTS.md] La producción solo se modifica después de backup
   verificable, control de deriva y autorización operativa.

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

- [Decisión resuelta, 2026-09-07] Se conserva una sola imagen de mantenimiento
  arriba y se divide debajo el contenido en dos áreas tituladas
  “Mantenimientos preventivos” y “Mantenimientos correctivos”.
- [Decisión resuelta, 2026-09-07] El usuario aprobó este SPEC para implementar el
  cambio.
