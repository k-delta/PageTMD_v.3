# Enrutamiento de revisores

La selección se basa en señales reales del diff, no en el nombre del rol ni en
el deseo de obtener más cobertura.

| Revisor | Seleccionar cuando | No seleccionar solo porque |
| --- | --- | --- |
| `architecture_reviewer` | Cambian límites entre tema, plugins, Firebase o servicios; fuente canónica; responsabilidades; estructura transversal | Hay un cambio rutinario en un único archivo |
| `security_reviewer` | Cambian autorización, autenticación, entradas, formularios, endpoints, nonces, secretos o consultas con entrada no parametrizada | El archivo es PHP |
| `database_reviewer` | Hay SQL/WPDB, esquema, migraciones, índices, transacciones, integridad o N+1 SQL | Existe una operación lenta no SQL |
| `performance_reviewer` | Cambian caché, bucles o I/O no SQL, memoria, serialización, llamadas externas o paginación | El único riesgo es una consulta SQL/N+1 |
| `test_reviewer` | Cambia comportamiento, se corrige un bug o deben comprobarse criterios de aceptación | Solo cambian documentación o estilo CSS sin lógica |

La revisión general de Superpowers conserva corrección, legibilidad,
mantenibilidad, complejidad y calidad transversal. No existe un especialista
de clean code porque duplicaría esa responsabilidad.

## Reglas de solapamiento

- Una consulta dentro de un bucle pertenece a `database_reviewer`, aunque tenga
  impacto de rendimiento. `performance_reviewer` solo se añade si existe una
  causa no SQL independiente.
- SQL construido con entrada no parametrizada requiere
  `database_reviewer` y `security_reviewer`.
- Un cambio de autorización rutinario requiere `security_reviewer` y
  normalmente `test_reviewer`, no `architecture_reviewer`.
- Si dos revisores informan la misma causa y ubicación, conservar un hallazgo,
  asignar el dueño anterior y anotar la corroboración.
- `test_runner` ejecuta validaciones; nunca aparece como autor de hallazgos ni
  sustituye a `test_reviewer`.

El detector automático solo produce candidatos. El coordinador debe justificar
tanto cada selección como cada omisión en la cobertura final.
