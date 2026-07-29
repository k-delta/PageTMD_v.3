# Inventario

## Fuente canónica

La fuente de verdad es el proyecto Inventario/Firebase.

WordPress no debe utilizarse para corregir:

- Marca.
- Modelo.
- Estado.
- Disponibilidad.
- Imágenes.
- Tipo de equipo.
- Condición de destacado.

## Integraciones

### Inventario general

- Cloud Function: `listarEquiposWordpress`
- Consumidor: `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`

### Equipos destacados

- Cloud Function: `listarEquiposDestacadosWordpress`
- Consumidor: `wp-content/plugins/tm-equipos-destacados-v2/`

## Tipos aceptados

- `montacargas`
- `bateria`

No introducir nuevos tipos sin actualizar primero el contrato entre Inventario y WordPress.

## Publicación

Un registro puede mostrarse cuando:

- Su tipo es aceptado.
- Su estructura es válida.
- Su estado permite publicación.
- Contiene la información mínima requerida.

No inventar valores para completar registros inválidos.

## Destacados

- Máximo cinco elementos.
- Un equipo alquilado no debe presentarse como disponible.
- La selección debe originarse en Inventario, no en un arreglo manual de WordPress.

## Caché

WordPress puede conservar:

- Caché temporal de respuestas.
- Última respuesta válida como fallback.

No añadir polling periódico o recargas automáticas sin un requisito explícito.

## Cloud Functions

- Probar desde el workspace correcto.
- Desplegar desde la raíz correcta del proyecto.
- Utilizar un target específico.
- No desplegar todas las Functions por accidente.
- Verificar logs después del despliegue.

## Validación

Antes de afirmar cantidades:

1. Consultar nuevamente el endpoint.
2. Validar registros inválidos.
3. Verificar estados.
4. Confirmar que WordPress renderice los resultados.
