# SPEC: Filtros de energía sin conteos visibles

## Estado

- Aprobado

## Contexto

[Solicitud] En `/energia/`, los filtros Marca, Voltaje, Capacidad y Condición deben conservarse, pero las opciones dinámicas muestran entre paréntesis la cantidad de baterías asociada, por ejemplo `24 V (8)`.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:397] El selector reutilizable permite mostrar u ocultar los conteos añadidos a las opciones dinámicas.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:523] El formulario de energía utiliza el selector reutilizable para Marca, Voltaje y Capacidad, y mantiene Condición con etiquetas textuales.

## Problema

[Solicitud] Las cantidades disponibles añadidas como sufijo generan información visual que no se desea mostrar en los filtros de baterías.

## Objetivo

[Solicitud] Conservar todos los filtros actuales de `/energia/` y retirar únicamente los conteos de disponibilidad añadidos entre paréntesis a sus opciones.

## Fuera del alcance

- [Solicitud] No ocultar ni eliminar Marca, Voltaje, Capacidad o Condición.
- [Solicitud] No eliminar números que forman parte del valor del filtro, como `24 V` o `625 Ah`.
- [Solicitud] No cambiar valores, orden, selección ni comportamiento de filtrado.
- [Regla: docs/domain/INVENTORY.md:5] No modificar los datos de Inventario/Firebase.
- [Solicitud] No modificar el formulario de `/equipos/` ni “Tu equipo ideal”.
- [Regla: AGENTS.md:99] No desplegar ni escribir en producción como parte de esta implementación local.

## Requisitos funcionales

1. [Solicitud] `/energia/` debe seguir mostrando los controles Marca, Voltaje, Capacidad y Condición.
2. [Solicitud] Las opciones dinámicas de Marca, Voltaje y Capacidad deben mostrar únicamente su etiqueta, sin el conteo añadido entre paréntesis.
3. [Solicitud] Los valores numéricos funcionales y sus unidades deben conservarse, incluidos voltios `V` y amperios-hora `Ah`.
4. [Solicitud] Cada opción debe conservar su valor, orden, disponibilidad, selección y coincidencia actuales.
5. [Solicitud] El control Condición debe conservar las opciones `Nueva` y `Usada` sin cambios.
6. [Solicitud] El comportamiento aprobado del formulario de `/equipos/` debe permanecer sin cambios.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md:5] Inventario/Firebase continúa siendo la fuente de verdad de las baterías.
- [Regla: AGENTS.md:190] El cambio debe ser el mínimo suficiente y no incluir refactors no relacionados.

## Contratos

### Entrada

No aplica. Se conservan los parámetros existentes `api_marca`, `api_voltaje`, `api_capacidad` y `api_condicion`.

### Salida

No aplica. Solo cambia el texto visible de las opciones HTML; los valores enviados y el modelo público permanecen iguales.

## Casos límite

- [Solicitud] `24 V (8)` debe mostrarse como `24 V`, no como `24` ni como una etiqueta vacía.
- [Solicitud] `625 Ah (2)` debe mostrarse como `625 Ah`, conservando el valor y la unidad.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:404] Las opciones dinámicas sin registros asociados deben continuar ausentes.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:527] Condición usa etiquetas textuales y debe conservarse sin alteraciones.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- `tests/test-tmd-inventory-background-refresh.php`

## Criterios de aceptación

1. [Solicitud] En `/energia/` se renderizan exactamente los cuatro controles actuales: Marca, Voltaje, Capacidad y Condición.
2. [Solicitud] Ninguna opción dinámica de esos controles muestra un conteo de baterías entre paréntesis.
3. [Solicitud] Voltaje conserva etiquetas como `24 V` y Capacidad conserva etiquetas como `625 Ah`.
4. [Solicitud] Aplicar Marca, Voltaje, Capacidad o Condición produce las mismas coincidencias que antes del cambio.
5. [Solicitud] `/equipos/` y “Tu equipo ideal” permanecen sin cambios respecto del comportamiento aprobado previamente.
6. [Regla: AGENTS.md:197] Existe una comprobación automatizada focalizada que cubre ausencia de conteos, conservación de controles, valores y filtrado.

## Validación

- Pruebas unitarias: Comprobar el HTML generado de `/energia/` y confirmar que las etiquetas dinámicas no contienen conteos, pero sí conservan valores y unidades.
- Pruebas de integración: Ejecutar `tests/test-tmd-inventory-background-refresh.php` y las pruebas JavaScript vigentes del catálogo.
- Validación manual: Inspeccionar los cuatro selectores en `/energia/`, en escritorio y móvil, y aplicar al menos Voltaje y Capacidad.
- Validación productiva: Pendiente de autorización explícita de despliegue; después de desplegar, purgar caché y verificar `/energia/` en navegador.

## Riesgos

- Eliminar todos los números de la etiqueta también retiraría voltajes y capacidades válidas; solo debe ocultarse el conteo añadido.
- Cambiar globalmente el selector reutilizable podría alterar `/equipos/`; el comportamiento debe configurarse únicamente en las llamadas de energía.

## Decisiones pendientes

- No aplica.
