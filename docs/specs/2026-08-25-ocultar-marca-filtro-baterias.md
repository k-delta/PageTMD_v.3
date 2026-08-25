# SPEC: Ocultar marca en el filtro del catálogo de baterías

## Estado

- Aprobado

## Contexto

[Solicitud: captura aportada el 2026-08-25] El panel lateral del catálogo “Baterías disponibles” muestra los filtros Marca, Voltaje, Capacidad y Condición; la opción Marca debe ocultarse.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:525] El formulario canónico de baterías renderiza actualmente el selector `api_marca` antes de Voltaje, Capacidad y Condición.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:426] El filtrado inicial del servidor aplica `api_marca` a baterías y montacargas sin distinguir el tipo de catálogo.

[Evidencia: wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js:123] El cliente obtiene los filtros desde los controles presentes en el formulario; si Marca no se renderiza, su valor del formulario queda vacío.

[Evidencia: commit b4275b5] El comportamiento vigente restauró deliberadamente el selector Marca en montacargas; esta tarea debe conservarlo y limitarse a baterías.

## Problema

[Solicitud] El catálogo de baterías ofrece una opción de filtrado por Marca que no debe estar visible. Una URL antigua con `api_marca` tampoco debe dejar resultados de baterías filtrados mediante un control que el usuario ya no puede ver ni cambiar.

## Objetivo

[Solicitud] Mostrar en el catálogo de baterías únicamente los filtros Voltaje, Capacidad y Condición, manteniendo el filtro Marca disponible en el catálogo de montacargas y conservando intactos los datos de marca de las tarjetas.

## Fuera del alcance

- [Solicitud] Ocultar o eliminar la marca mostrada dentro de las fichas o especificaciones de las baterías.
- [Solicitud] Cambiar los filtros Voltaje, Capacidad o Condición.
- [Solicitud] Modificar los filtros del catálogo de montacargas.
- [Regla: AGENTS.md] Alterar datos de Inventario/Firebase, marcas, disponibilidad o registros del catálogo.
- [Regla: AGENTS.md] Desplegar, purgar caché o escribir en producción sin autorización explícita posterior, backup verificado y control de deriva.

## Requisitos funcionales

1. [Solicitud] El formulario del catálogo de baterías no debe renderizar la etiqueta ni el selector `api_marca`.
2. [Solicitud] El formulario de baterías debe conservar, en este orden, los selectores Voltaje, Capacidad y Condición.
3. [Solicitud] El catálogo de montacargas debe conservar sus cuatro controles actuales, incluido Marca.
4. [Solicitud] Una URL del catálogo de baterías que contenga el parámetro heredado `api_marca` debe ignorarlo para evitar un filtro activo invisible.
5. [Solicitud] La marca de cada batería debe permanecer disponible en sus datos, tarjetas o fichas cuando exista; el cambio se limita al panel de filtros.
6. [Regla: AGENTS.md] El catálogo debe continuar leyendo la copia local del inventario sin introducir llamadas síncronas a Firebase.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Inventario/Firebase continúa siendo la fuente canónica de las marcas y demás datos del equipo.
- [Regla: AGENTS.md] WordPress no debe inventar ni corregir marcas o registros para satisfacer la presentación.

## Contratos

### Entrada

```json
{
  "catalogType": "bateria",
  "visibleFilters": ["api_voltaje", "api_capacidad", "api_condicion"],
  "legacyQueryParameter": "api_marca"
}
```

### Salida

```json
{
  "visibleFilterCount": 3,
  "brandFilterVisible": false,
  "legacyBrandFilterApplied": false,
  "batteryBrandDataChanged": false,
  "forkliftFiltersChanged": false
}
```

## Casos límite

- [Solicitud] Una batería sin marca debe continuar renderizándose según las reglas existentes.
- [Solicitud] Una URL con `api_marca` y otro filtro válido de baterías debe ignorar solo Marca y conservar Voltaje, Capacidad o Condición.
- [Inferencia técnica] El HTML inicial y el renderizado del navegador deben producir el mismo conjunto de resultados cuando una URL heredada incluya `api_marca`.
- [Solicitud] El panel debe conservar su alineación y espaciado al pasar de cuatro a tres selectores.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js` (solo referencia; no se prevé modificación)
- `tests/test-tmd-inventory-background-refresh.php`

## Criterios de aceptación

1. [Solicitud] El panel de baterías muestra exactamente tres selectores: Voltaje, Capacidad y Condición.
2. [Solicitud] El HTML del formulario de baterías no contiene `name="api_marca"` ni la etiqueta Marca.
3. [Solicitud] El formulario de montacargas conserva exactamente sus cuatro controles actuales: Marca, Altura colapsada, Altura de levante y Capacidad.
4. [Solicitud] Con `api_marca` en la URL de baterías, el resultado inicial no se reduce por marca y coincide con el comportamiento del navegador sin ese control.
5. [Solicitud] Al combinar `api_marca` con Voltaje, Capacidad o Condición, el catálogo ignora Marca y aplica el otro filtro válido.
6. [Solicitud] Las tarjetas y fichas de baterías conservan la marca cuando el dato existe.
7. [Regla: docs/domain/INVENTORY.md] Renderizar el catálogo con la copia local no realiza llamadas remotas a Firebase.

## Validación

- Pruebas unitarias: No aplica; el comportamiento se cubre en la prueba focalizada existente del catálogo.
- Pruebas de integración: Ejecutar `tests/test-tmd-inventory-background-refresh.php`; comprobar tres selectores de baterías, ausencia de `api_marca`, conservación de los filtros de montacargas y que URLs heredadas ignoren Marca sin desactivar otros filtros válidos.
- Validación manual: Revisar `/energia/` en escritorio y móvil; confirmar que Marca no aparece, que los tres controles restantes funcionan y que no hay huecos, desalineación, errores de consola ni cambios en las marcas de las tarjetas.
- Validación productiva: Pendiente de autorización explícita. Después de aprobar un despliegue: consultar runbooks, crear backup focalizado, ejecutar `./scripts/sync-production.sh --check`, desplegar únicamente los archivos modificados, purgar LiteSpeed y verificar HTTP, navegador, filtros, consola, logs y sincronización final.

## Riesgos

- [Inferencia técnica] Ocultar solo el HTML sin ajustar el filtrado inicial permitiría que una URL antigua aplique un filtro invisible y produzca resultados inconsistentes.
- [Inferencia técnica] Modificar la lógica genérica de Marca sin limitarla por tipo podría afectar el catálogo de montacargas.
- [Inferencia técnica] LiteSpeed puede conservar temporalmente el formulario anterior después de un eventual despliegue hasta completar la purga.

## Decisiones pendientes

- Ninguna.
