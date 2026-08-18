---
name: implement-spec
description: Use when an approved PageTMD specification is ready for implementation or an existing implementation plan must be checked against repository constraints before execution.
---

# Implement Spec

## Overview

Es el adaptador entre un SPEC de PageTMD y Superpowers. Valida preparación, aporta restricciones del repositorio y entrega el control; no replica planificación, worktrees, TDD, ejecución, revisiones ni finalización.

## Cuándo usarlo

Úsalo con una ruta concreta de `docs/specs/`, opcionalmente acompañada por un plan existente y límites de autorización.

No lo uses para redactar o aprobar el SPEC, revisar un diff, actualizar documentación posterior ni desplegar.

## Preflight PageTMD

1. Lee el `AGENTS.md` aplicable, el SPEC, `docs/architecture/REPO_MAP.md` y solo la documentación de dominio o runbooks relacionada.
2. Ejecuta `git status --short`; preserva cambios ajenos.
3. Comprueba que el SPEC existe, está `Aprobado`, sigue vigente, no conserva decisiones funcionales abiertas y tiene criterios observables.
4. Inspecciona código y configuración canónicos. Si contradicen el SPEC, detente y presenta ambas evidencias; no elijas silenciosamente.
5. Si existe plan, comprueba cobertura del SPEC, rutas canónicas, validaciones y conflictos con las reglas del repositorio. No lo reescribas ni generes otro.

## Herramientas conectadas

Úsalas solo cuando reduzcan incertidumbre necesaria para ejecutar

Registra herramienta, alcance y evidencia obtenida. La lectura es el modo inicial. Una escritura requiere autorización explícita, entorno exacto, backup y reversión aplicables. Nunca uses escrituras productivas para investigar, ni expongas secretos o datos personales.

## Paquete de entrega

Antes de delegar produce:

- veredicto `READY` o `BLOCKED`;
- rutas de SPEC y plan;
- fuentes canónicas y archivos afectados;
- restricciones globales copiadas literalmente;
- matriz `criterio → tarea/prueba/validación`;
- límites actuales: editar, commit, integración y producción;
- evidencia consultada y hechos aún no verificados.

## Enrutamiento sin duplicación

- `BLOCKED` por decisiones del SPEC: vuelve a `ticket-to-spec`; no abras otro diseño de Superpowers.
- `READY` sin plan: **REQUIRED SUB-SKILL:** Use `superpowers:writing-plans`.
- `READY` con plan: no vuelvas a brainstorming ni writing-plans. Usa la opción de ejecución definida por Superpowers: `superpowers:subagent-driven-development` o `superpowers:executing-plans`.

Esos Skills son dueños de aislamiento, tareas, TDD, revisiones y cierre. Pásales el paquete PageTMD como restricciones; no copies sus pasos aquí. Expón sus prerrequisitos y deja que el Skill elegido los valide. No uses `executing-plans` para eludir worktrees o commits requeridos, ni inventes modos como “SDD sin commits”. Si el usuario niega una acción requerida, devuelve `BLOCKED`.

Implementación local no autoriza commit, push, merge ni despliegue. Si el plan llega a producción sin autorización actual, termina con “local verificado; producción pendiente”. Un despliegue autorizado usa `safe-deploy`; no lo ejecutes desde este Skill. `review-change` permanece independiente y no duplica automáticamente las revisiones de Superpowers.
