# SPEC: Reducir latencia inicial de catálogos — etapa 1

## Estado

- Aprobado

## Contexto

- [Solicitud] Se solicita aplicar por etapas las mejoras propuestas para reducir la demora inicial de `/equipos/` y `/energia/`.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:14] Cuando el transient no contiene inventario, la generación de página ejecuta una solicitud HTTP síncrona a la Cloud Function.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-inventory-api.php:21] La solicitud remota puede esperar hasta 18 segundos antes de fallar.
- [Evidencia: medición HTTP 2026-08-03] Con LiteSpeed en `hit`, el TTFB observado fue cercano a 0.4 segundos; con `miss`, llegó a 4.07 segundos en `/equipos/` y 4.75 segundos en `/energia/`.

## Problema

- [Solicitud] Un visitante puede quedar esperando la consulta remota y la generación completa del catálogo cuando falta la caché de página o de inventario.

## Objetivo

- [Solicitud] Desacoplar la consulta a Inventario/Firebase de la solicitud pública para que `/equipos/` y `/energia/` puedan arrancar usando una copia local actualizada en segundo plano.

## Fuera del alcance

- [Solicitud] Esta etapa no reduce todavía el número de tarjetas renderizadas.
- [Solicitud] Esta etapa no cambia imágenes, miniaturas ni URLs de Firebase Storage.
- [Solicitud] Esta etapa no cambia campos, filtros, paginación, fichas, orden ni contenido comercial.
- [Regla: AGENTS.md] No modificar la Cloud Function, WordPress core, tema padre ni plugins de terceros.
- [Regla: AGENTS.md] No desplegar, hacer commit, push ni escribir en producción sin autorización explícita para esa acción.

## Requisitos funcionales

1. [Solicitud] Una solicitud pública a `/equipos/` o `/energia/` no debe ejecutar una llamada HTTP síncrona a Inventario/Firebase.
2. [Solicitud] WordPress debe actualizar la copia local del inventario mediante una tarea de fondo programada cada hora.
3. [Solicitud] Una actualización exitosa debe reemplazar tanto la copia temporal como la última copia válida persistente, conservando el contrato actual de `items`, `generatedAt`, `fetchedAt` y `source`.
4. [Solicitud] Si la actualización remota falla o devuelve datos inválidos, debe mantenerse la última copia válida sin reemplazarla por información vacía.
5. [Solicitud] Si todavía no existe ninguna copia local, los catálogos deben mostrar el mensaje de indisponibilidad existente y programar una actualización en segundo plano, sin bloquear la respuesta pública.
6. [Solicitud] La programación debe ser idempotente y evitar tareas duplicadas o actualizaciones remotas concurrentes.
7. [Solicitud] Filtros, tarjetas, paginación, fichas y cotización deben conservar el comportamiento actual cuando exista inventario local.

## Reglas de negocio

- [Regla: docs/domain/INVENTORY.md] Inventario/Firebase sigue siendo la fuente canónica de equipos, estados, disponibilidad, marcas, modelos e imágenes.
- [Regla: docs/domain/INVENTORY.md] WordPress puede conservar una caché temporal y la última respuesta válida como fallback.
- [Regla: AGENTS.md] WordPress no debe completar ni corregir registros con datos inventados.

## Contratos

### Entrada

```json
{
  "trigger": "tarea programada de WordPress",
  "source": "listarEquiposWordpress"
}
```

### Salida

```json
{
  "items": [],
  "generatedAt": "cadena recibida de Inventario",
  "fetchedAt": 0,
  "source": "live|fallback|error"
}
```

## Casos límite

- [Solicitud] Dos procesos que detecten la tarea pendiente al mismo tiempo no deben duplicar la consulta remota.
- [Solicitud] Una respuesta HTTP distinta de 200, JSON inválido o arreglo vacío no debe borrar la última copia válida.
- [Solicitud] La expiración del transient no debe obligar al visitante a esperar Firebase cuando exista fallback persistente.
- [Solicitud] En una instalación sin copia previa, el catálogo debe degradar al mensaje existente y recuperarse después de una actualización exitosa.
- [Solicitud] Desactivar y volver a activar el tema no debe acumular eventos programados duplicados.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- `tests/` para pruebas focalizadas del refresco y lectura local.

## Criterios de aceptación

1. [Solicitud] Una prueba focalizada demuestra que renderizar con transient válido, fallback válido o sin datos no invoca `wp_remote_get()`.
2. [Solicitud] Una prueba demuestra que la tarea de fondo acepta una respuesta válida y actualiza transient y fallback con el contrato existente.
3. [Solicitud] Una prueba demuestra que error HTTP, error de WordPress o payload inválido conservan la última copia válida.
4. [Solicitud] Una prueba demuestra que la programación horaria es idempotente y que un bloqueo activo evita refrescos concurrentes.
5. [Solicitud] Los catálogos siguen filtrando, paginando y abriendo fichas con la copia local.
6. [Solicitud] En una comprobación sin caché de página, el TTFB ya no incluye el tiempo de respuesta de la Cloud Function.

## Validación

- Pruebas unitarias: Ejecutar pruebas PHP focalizadas para lectura local, refresco exitoso, fallos, bloqueo y programación idempotente.
- Pruebas de integración: Renderizar ambos shortcodes con datos locales y confirmar filtros, resultados, paginación y ficha sin llamada remota.
- Validación manual: Cargar `/equipos/` y `/energia/`, comprobar contenido y ejecutar de forma controlada el hook de actualización en entorno local.
- Validación productiva: Solo con autorización explícita, backup verificado, despliegue mínimo, ejecución controlada del cron, purga de caché y nuevas mediciones `hit`/`miss`.

## Riesgos

- WP-Cron depende de tráfico o de un cron del sistema; si no se ejecuta, la copia local puede quedar desactualizada.
- Una frecuencia horaria introduce hasta una hora de desfase entre Inventario y WordPress.
- Si no existe copia local durante una instalación nueva, el catálogo estará temporalmente indisponible hasta el primer refresco exitoso.
- Un bloqueo mal liberado podría retrasar actualizaciones posteriores; debe tener expiración limitada.

## Decisiones pendientes

- Ninguna.
