---
name: deliver-change
description: Use when a brief, informal, or incomplete PageTMD repository change request requires end-to-end delivery across multiple specialized workflows.
---

# Deliver Change

## Overview

Orquesta la entrega sin asumir el trabajo de los Skills especializados. Mantén
un único alcance trazable y no avances mientras falle el gate actual.

```text
analizar → inspeccionar → preguntar → requisitos → SPEC → aprobar → planificar
→ implementar → probar → revisar → corregir → verificar → documentar → evidenciar
```

## Entradas

- Solicitud vigente, resultado esperado y evidencia aportada.
- Ticket, SPEC o plan existentes.
- Límites explícitos para editar, integrar y operar producción.
- Contexto descubierto en reglas, código y configuración canónicos.

“Listo”, “publica” o “haz lo que falte” no identifican por sí solos una acción
externa autorizada.

## Flujo

1. Lee los `AGENTS.md` aplicables, ejecuta `git status --short` y consulta
   `docs/architecture/REPO_MAP.md`, código y documentación relacionada. Preserva
   cambios ajenos. Aplica
   [task-routing.md](references/task-routing.md).
2. Confirma que se pide una entrega completa. Para diagnóstico, revisión,
   documentación o despliegue aislados, entrega el control al Skill propietario
   y termina. Separa objetivos que puedan aprobarse o entregarse por separado;
   no implementes una parte hasta que el usuario acepte el alcance reducido.
3. Investiga antes de preguntar y formula una decisión no descubrible cada vez.
   Elige una de las ramas excluyentes de requisitos en `task-routing.md`. No
   uses parcialmente un Skill ni combines propietarios de SPEC o plan.
4. Exige un único SPEC canónico que cubra el alcance y aprobación explícita de
   su versión actual; el silencio no aprueba. Un plan no sustituye el SPEC y
   todo añadido vuelve a este gate.
5. Usa `implement-spec` antes de ejecutar. Si la rama de Brainstorming ya creó
   el plan obligatorio, entrégalo como plan existente; en las demás ramas,
   `implement-spec` decide planificación y ejecución. No copies procedimientos
   ni inventes modos alternativos. Asigna a escritores propiedad no solapada.
6. Sobre un diff fijo, usa `review-change`. Procesa cada hallazgo con
   `superpowers:receiving-code-review`; corrige solo evidencia válida dentro del
   alcance y repite pruebas y revisión afectadas.
7. Usa `superpowers:verification-before-completion`, luego
   `update-documentation`. Si cambia documentación, valida ese diff. Entrega el
   informe de
   [completion-report.md](references/completion-report.md).

## Estados

- `NEEDS_INPUT`: falta una decisión no investigable.
- `AWAITING_SPEC_APPROVAL`: borrador válido, sin autorización de implementación.
- `READY_FOR_IMPLEMENTATION`: SPEC aprobado y preflight preparado.
- `IN_PROGRESS`: ejecución o gates posteriores aún no terminan.
- `BLOCKED`: un gate, contradicción o autorización impide continuar.
- `DELIVERED`: todo el alcance acordado quedó resuelto con evidencia reciente.

La edición local no autoriza commit, push, merge, PR ni producción. Una
integración solicitada usa el flujo de Superpowers aplicable. Un despliegue
explícitamente autorizado ocurre después mediante `safe-deploy`.
