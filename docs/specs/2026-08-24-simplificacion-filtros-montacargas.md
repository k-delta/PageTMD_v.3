# SPEC: Simplificación de filtros del catálogo de montacargas

## Estado

- Aprobado

## Contexto

[Solicitud] El formulario público de `/equipos/` muestra Marca, Categoría, Subcategoría, Altura colapsada, Altura de levante, Condición, Operario y Reach; varias opciones dinámicas agregan entre paréntesis la cantidad de equipos asociada.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:487] Los ocho controles se renderizan desde el formulario del catálogo de montacargas.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:561] La clasificación interna de categoría y subcategoría forma parte del modelo público de cada equipo, independientemente de los controles visibles.

[Evidencia: wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php:36] “Tu equipo ideal” consume directamente la clasificación del inventario y no depende del formulario público de filtros.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:528] La capacidad de los montacargas ya se obtiene de `especificaciones.capacidad_ton` y se presenta con unidad `ton`.

## Problema

[Solicitud] El formulario contiene controles que no se desean mostrar y no ofrece un filtro público por capacidad. Además, los controles dinámicos visibles no deben presentar conteos de equipos.

## Objetivo

[Solicitud] Simplificar el formulario de `/equipos/` para mostrar únicamente Altura colapsada, Altura de levante y el nuevo filtro Capacidad, sin conteos visibles y sin retirar la clasificación o el filtrado interno que consumen otras secciones.

## Fuera del alcance

- [Solicitud] No eliminar ni desactivar la clasificación interna de Marca, Categoría, Subcategoría, Condición, Operario o Reach.
- [Solicitud] No modificar el funcionamiento ni los datos consumidos por “Tu equipo ideal”.
- [Regla: docs/domain/INVENTORY.md:5] No modificar datos ni contratos en la fuente canónica Inventario/Firebase.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:508] No cambiar los filtros ni la presentación del catálogo de energía `/energia/`.
- [Solicitud] No rediseñar visualmente el formulario fuera de retirar y agregar los controles indicados.
- [Regla: AGENTS.md:99] No desplegar ni escribir en producción como parte de esta implementación local.

## Requisitos funcionales

1. [Solicitud] El formulario público de `/equipos/` no debe renderizar los controles Marca, Categoría, Subcategoría, Condición, Operario ni Reach.
2. [Solicitud] El formulario público de `/equipos/` debe conservar los controles Altura colapsada y Altura de levante.
3. [Solicitud] El formulario público de `/equipos/` debe agregar un control Capacidad construido con los valores disponibles de `especificaciones.capacidad_ton`, presentados con unidad `ton`.
4. [Solicitud] Las opciones del control Capacidad deben mostrar únicamente su valor y unidad, sin añadir entre paréntesis la cantidad de equipos.
5. [Solicitud] El filtro Capacidad debe aplicar coincidencia exacta sobre `especificaciones.capacidad_ton` tanto en el renderizado inicial del servidor como en la interacción del navegador.
6. [Solicitud] Marca, Categoría, Subcategoría, Condición, Operario y Reach deben conservarse en la clasificación, el modelo público y el filtrado interno existente, aunque sus controles no sean visibles en el formulario.
7. [Solicitud] “Tu equipo ideal” debe seguir recibiendo categoría, subcategoría y capacidad sin cambios de contrato.
8. [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:508] Los filtros del catálogo de energía deben conservar su comportamiento y presentación actuales.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md:5] Inventario/Firebase continúa siendo la fuente de verdad de los equipos.
- [Regla: AGENTS.md:190] El cambio debe ser el mínimo suficiente y no incluir refactors no relacionados.

## Contratos

### Entrada

Para `/equipos/`, se incorpora el parámetro opcional ya existente en la infraestructura de filtros:

```json
{
  "api_capacidad": "valor numérico disponible expresado como texto con sufijo ton"
}
```

Los parámetros existentes de Marca, Categoría, Subcategoría, Condición, Operario y Reach conservan compatibilidad para URLs o consumidores existentes, aunque ya no tengan controles visibles.

### Salida

El modelo público de cada montacargas conserva sus campos actuales y completa `filters.capacity` con el valor normalizado de `especificaciones.capacidad_ton` y el sufijo ` ton`.

## Casos límite

- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:528] Los equipos sin una capacidad numérica válida no deben crear una opción vacía ni coincidir cuando se aplique una capacidad concreta.
- [Solicitud] Capacidades equivalentes deben compartir una única opción; las opciones deben conservar un orden natural.
- [Solicitud] Los números propios de Altura colapsada y Altura de levante deben conservarse porque son valores funcionales, no conteos.
- [Solicitud] Una URL existente con parámetros ocultos como `api_categoria` o `api_subcategoria` debe continuar filtrando los resultados.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:561] Ocultar controles no debe eliminar etiquetas de categoría/subcategoría de tarjetas ni datos usados por el recomendador.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- `wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js`
- `tests/test-tmd-inventory-background-refresh.php`
- `tests/test-tmd-inventory-api-js.js`
- `wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php` (solo consumidor que debe permanecer sin cambios)

## Criterios de aceptación

1. [Solicitud] En `/equipos/`, el formulario muestra exactamente Altura colapsada, Altura de levante y Capacidad, además de Aplicar filtros y Limpiar filtros.
2. [Solicitud] En `/equipos/`, no se renderizan controles visibles para Marca, Categoría, Subcategoría, Condición, Operario ni Reach.
3. [Solicitud] Las opciones de Capacidad corresponden a capacidades reales disponibles, usan la unidad `ton` y no muestran conteos entre paréntesis.
4. [Solicitud] Aplicar una capacidad muestra únicamente equipos con ese valor en el renderizado inicial y en el filtrado dinámico del navegador.
5. [Solicitud] Los filtros conservados de altura producen las mismas coincidencias que antes del cambio.
6. [Solicitud] Una URL con `api_categoria` o `api_subcategoria` continúa filtrando correctamente aunque esos controles estén ocultos.
7. [Solicitud] Las tarjetas y “Tu equipo ideal” conservan la clasificación y capacidad actuales.
8. [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:508] `/energia/` conserva sus controles y conteos actuales.
9. [Regla: AGENTS.md:197] Existen comprobaciones automatizadas focalizadas para estructura visible, capacidad en servidor y navegador, compatibilidad de filtros ocultos y ausencia de regresión en energía.

## Validación

- Pruebas unitarias: Ejecutar las comprobaciones PHP y JavaScript focalizadas para opciones de capacidad, coincidencia exacta, ausencia de controles ocultos y conservación de datos internos.
- Pruebas de integración: Ejecutar `tests/test-tmd-inventory-background-refresh.php` y `tests/test-tmd-inventory-api-js.js`.
- Validación manual: Inspeccionar `/equipos/` en escritorio y móvil; confirmar los tres controles visibles y aplicar Altura colapsada, Altura de levante y Capacidad.
- Validación productiva: Pendiente de autorización explícita de despliegue; después de desplegar, purgar caché y verificar `/equipos/`, “Tu equipo ideal” y `/energia/` en navegador de escritorio y móvil.

## Riesgos

- Retirar la lógica en lugar de solo los controles podría afectar enlaces existentes y el recomendador; los datos y coincidencias internas deben preservarse.
- Reutilizar `api_capacidad` sin distinguir el tipo podría confundir toneladas de montacargas con amperios-hora de baterías; cada catálogo debe normalizar contra su campo y unidad correspondientes.
- Capacidades ausentes o con formatos inconsistentes podrían producir opciones inválidas; solo deben incluirse valores numéricos válidos.

## Decisiones pendientes

- No aplica.
