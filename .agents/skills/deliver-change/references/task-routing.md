# Enrutamiento y gates

## Propiedad

| Condición observable | Propietario | Retorno al orquestador |
| --- | --- | --- |
| Cambio nuevo con decisiones de diseño | `superpowers:brainstorming` | SPEC canónico aprobado y plan |
| Bug o comportamiento inesperado | `superpowers:systematic-debugging` | Causa y reproducción |
| Solicitud no creativa sin SPEC aprobado | `ticket-to-spec` | Borrador validado |
| SPEC aprobado | `implement-spec` | Paquete `READY` o `BLOCKED` |
| Planificación creativa | `superpowers:writing-plans` vía Brainstorming | Plan único |
| Planificación restante | `superpowers:writing-plans` vía `implement-spec` | Plan único |
| Ejecución | Skill de Superpowers elegido por `implement-spec` | Diff y pruebas |
| Revisión general y especializada | `review-change` | Veredicto y hallazgos |
| Hallazgos recibidos | `superpowers:receiving-code-review` | Decisiones verificadas |
| Afirmación de cierre | `superpowers:verification-before-completion` | Evidencia reciente |
| Impacto documental | `update-documentation` | `UPDATED`, `NO_UPDATE` o `BLOCKED` |
| Despliegue explícitamente autorizado | `safe-deploy` | Estado productivo verificable |

No repitas el procedimiento interno del propietario. Entrégale solicitud,
artefactos, límites de autorización y evidencia; valida su resultado antes de
continuar.

## Ramas excluyentes de requisitos

- SPEC aprobado vigente: pasa directamente a `implement-spec`.
- Feature o diseño creativo: usa `superpowers:brainstorming` completo. Antes de
  invocarlo, fija como salida su único SPEC canónico en `docs/specs/`, conforme
  a `TEMPLATE.md`, y exige que su aprobación final deje el estado `Aprobado`.
  Su transición obligatoria crea el plan una sola vez; después usa
  `implement-spec` con ese plan existente antes de ejecutar. No uses
  `ticket-to-spec` en esa misma rama.
- Bug: usa `superpowers:systematic-debugging`; con causa y resultado esperado,
  usa `ticket-to-spec`, aprobación e `implement-spec`.
- Solicitud no creativa sin SPEC: usa `ticket-to-spec`, aprobación e
  `implement-spec`.

No uses parcialmente un Skill para extraer preguntas o diseño y omitir su
salida obligatoria. Si Brainstorming exige una acción no autorizada, devuelve
`BLOCKED`; no declares completada esa rama.

La exclusión de `ticket-to-spec` cubre la creación inicial de la rama creativa.
Si el preflight devuelve `BLOCKED` por decisiones abiertas, sigue la
recuperación indicada por `implement-spec` sobre el SPEC existente y exige una
nueva aprobación. No reabras Brainstorming desde ese preflight. Si
`ticket-to-spec` no puede resolver el bloqueo sin rediseño, devuelve `BLOCKED`;
no inventes una recuperación que contradiga al propietario.

Dos objetivos requieren un SPEC separado cuando pueden aprobarse, implementarse
o revertirse independientemente. Pregunta qué track ejecutar primero. Compartir
solicitud, módulo o fecha no justifica combinarlos.

## Gates

| Gate | Requisito para avanzar |
| --- | --- |
| Contexto | Reglas, estado Git, fuente canónica y alcance inspeccionados |
| Requisitos | Resultado observable y objetivos independientes separados |
| SPEC | Cubre todo el alcance, está validado y sin decisiones abiertas |
| Aprobación | Usuario aprobó la versión actual |
| Implementación | Criterios trazados a tareas, pruebas y validaciones |
| Revisión | Diff fijo; sin `Critical` o `Important` sin resolver |
| Verificación | Evidencia reciente para cada afirmación |
| Documentación | `UPDATED` o `NO_UPDATE`; nunca omitida |
| Autoridad | Cada acción externa está nombrada y autorizada |

## Herramientas y agentes

- Context7: documentación vigente de librerías, SDK, API, CLI o cloud.
- MCP de base de datos o Firebase: lectura inicial; las escrituras requieren
  autorización, entorno, backup y reversión.
- Navegador: comportamiento visual o de usuario; no sustituye pruebas.
- Agentes escritores: solo mediante el Skill de ejecución, con propiedad no
  solapada.
- Revisores de `.codex/agents/`: solo mediante `review-change`, en lectura y
  seleccionados por señales del diff.

## Racionalizaciones observadas

| Atajo | Respuesta |
| --- | --- |
| “Los criterios caben en un plan; no hace falta SPEC.” | Una entrega no trivial requiere SPEC validado y aprobado. |
| “Haré una sola pregunta agrupada para ahorrar tiempo.” | Pregunta una decisión cada vez cuando las respuestas dependan entre sí. |
| “Implemento la parte clara y dejo la otra pendiente.” | Primero obtén aceptación explícita del alcance reducido. |
| “El añadido cabe en la aprobación anterior.” | Cambiar alcance invalida cobertura; vuelve al gate de SPEC. |
| “Minor significa corregible automáticamente.” | Verifica evidencia y alcance con `receiving-code-review`. |
| “Listo/publica incluye commit, push y producción.” | Exige acción, destino y entorno explícitos. |
| “Uso solo las preguntas de Brainstorming.” | Completa el Skill o bloquea; no mezcles dos propietarios de SPEC/plan. |

## Señales de detención

- Solución sugerida sin problema o causa comprobados.
- Objetivos incompatibles o independientes aún combinados.
- SPEC desactualizado, no aprobado o contradicho por el código vigente.
- Cambios ajenos solapados.
- Hallazgo que exige una decisión nueva.
- Validación imposible o evidencia temporal no vigente.
- Operación externa o productiva no autorizada.
