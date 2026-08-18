---
name: review-change
description: Use when reviewing a branch, pull request, commit, or local diff for repository-specific risks before merge or deployment.
---

# Review Change

## Overview

Coordina una capa de revisión especializada de PageTMD. Complementa la revisión
general de Superpowers; no la reemplaza, no corrige código y no despliega.

## Entradas

- Objetivo exacto: working tree, staged, commit, rango `base..head` o PR.
- `SPEC` o plan aprobado y criterios de aceptación, si existen.
- Revisión general previa, solo si cubre exactamente el mismo objetivo.
- Evidencia de validación ya disponible.

Si no se puede fijar el objetivo, detenerse y pedirlo. Leer los `AGENTS.md`
aplicables, el `SPEC` y solo la documentación especializada relevante. Ejecutar
`git status --short` y preservar cambios ajenos.

## Flujo

1. Crea un paquete reproducible con
   `scripts/collect-diff.sh --output PACKAGE --range BASE HEAD`, `--staged` o
   `--working`. No mezcles objetivos.
2. Si existe una revisión general reciente del mismo objetivo, reutilízala y
   registra su referencia. En otro caso, **REQUIRED SUB-SKILL:** usa
   `superpowers:requesting-code-review`. `generated` significa que esa revisión
   terminó y tiene referencia; no emitas un reporte final mientras esté
   pendiente.
3. Obtén candidatos con
   `scripts/detect-change-areas.py --paths-file PATHS --diff-file DIFF`.
   Confirma cada selección inspeccionando el cambio y aplica
   [reviewer-routing.md](references/reviewer-routing.md). El detector es una
   ayuda, no autoridad.
4. Registra para los cinco revisores una decisión `selected` o `skipped` y una
   razón concreta. No selecciones todos por defecto ni por “más cobertura”.
5. Ejecuta únicamente los revisores seleccionados de `.codex/agents/`, en modo
   de solo lectura y en paralelo cuando sean independientes. Entrégales solo el
   paquete, requisitos y documentación aplicable. Exige el contrato de
   [finding-format.md](references/finding-format.md).
6. `test_runner` no es revisor. Úsalo únicamente para obtener evidencia de una
   validación focalizada explícitamente ausente; no modifica producción.
7. Consolida por causa raíz y ubicación. Asigna un único dueño por dominio,
   conserva la severidad más alta que la evidencia justifique y documenta
   desacuerdos. Rechaza preferencias personales sin impacto demostrable.
8. Valida el reporte con
   `scripts/validate-findings.py REPORT.json`.

## Límites de propiedad

La revisión general de Superpowers cubre requisitos, corrección, legibilidad,
mantenibilidad y calidad transversal. Los especialistas cubren solo sus riesgos
exclusivos del repositorio. SQL, índices, transacciones y N+1 pertenecen a
`database_reviewer`; `performance_reviewer` cubre rendimiento no SQL. No
selecciones un especialista para repetir observaciones generales de calidad.

## Salida y entrega

Entrega objetivo, revisión general reutilizada/generada, cobertura razonada,
hallazgos deduplicados, brechas de validación y veredicto:

- `BLOCKED`: al menos un hallazgo `Critical` o `Important`.
- `READY_WITH_MINORS`: solo hallazgos `Minor`.
- `READY`: sin hallazgos.

El veredicto describe hallazgos de revisión, no autoriza merge ni despliegue.
Registra validaciones ausentes; complétalas mediante el flujo de verificación
aplicable antes de afirmar que el cambio está listo.

Antes de implementar cualquier hallazgo, **REQUIRED SUB-SKILL:** usa
`superpowers:receiving-code-review`. Producción pertenece a `safe-deploy`.
