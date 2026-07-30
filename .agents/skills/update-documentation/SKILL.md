---
name: update-documentation
description: Use when a completed repository change needs its documentation impact evaluated before delivery, even when the expected result may be no documentation update.
---

# Update Documentation

## Overview

Evalúa un cambio terminado contra la verdad documental existente y modifica
solo los documentos cuya información dejó de ser correcta. Documentar actividad
no es el objetivo: documentar el estado resultante sí.

## Entradas

- Objetivo exacto: working tree, staged, commit, rango o PR.
- Diff, archivos afectados y revisión final.
- `SPEC` relacionado, criterios de aceptación y evidencia de validación.
- Evidencia vigente para cualquier afirmación temporal o externa.

No usar durante la definición del cambio ni para compensar una implementación
incompleta. Leer el `AGENTS.md` aplicable, `docs/architecture/REPO_MAP.md`, el
`SPEC` y únicamente la documentación especializada candidata.

Si no puedes fijar objetivo o diff, produce `BLOCKED`. Busca un `SPEC`
relacionado: si confirmas que no existe, omite el área `spec` y registra la
búsqueda; si no puedes resolverlo, bloquea. No afirmes que ejecutaste los
scripts sobre un escenario abstracto.

## Decisión

1. Ejecuta `scripts/detect-doc-impact.py --paths-file PATHS --diff-file DIFF`.
   Sus resultados son candidatos, nunca órdenes de edición.
2. Aplica [documentation-routing.md](references/documentation-routing.md).
   Pregunta por cada candidato: “¿El documento actual describe incorrectamente
   el estado resultante?”. Si no, omítelo con razón explícita.
3. Cubre exactamente las áreas `architecture`, `domain`, `runbooks`, `status`,
   `spec` y `agents`, cada una como `selected` o `skipped`.
4. Si una afirmación depende de una librería, SDK, API, CLI o servicio cloud,
   consulta documentación vigente mediante Context7. Usa MCP de base de datos o
   Firebase en modo de lectura para verificar el entorno cuando aplique. La
   documentación oficial no prueba la configuración real y la evidencia del
   entorno no sustituye el contrato oficial.
5. Construye el plan definido en
   [update-plan-format.md](references/update-plan-format.md) y valídalo con
   `scripts/validate-doc-update.py PLAN.json`.

## Gate de evidencia

El proceso es atómico. Si falta evidencia obligatoria, usa `BLOCKED`, deja
`changed_documents` vacío y no edites nada.

- `CURRENT_STATE.md` requiere evidencia obtenida para el mismo entorno y durante
  la revisión actual, con fuente y fecha de comprobación.
- Si existe un `SPEC` relacionado y todos sus criterios de aceptación y
  validaciones requeridas están comprobados, sin decisiones abiertas,
  selecciónalo, registra la evidencia y cambia su estado a `Terminado`.
- No inventes hechos, conviertas expectativas en estado actual ni uses
  “cambio terminado” como evidencia.

## Edición

Con un plan válido `UPDATED`, modifica exclusivamente `changed_documents`:

- conserva estructura y contenido no afectado;
- aplica el parche mínimo que describa el resultado, no una crónica;
- registra evidencia y decisiones finales en el `SPEC`;
- no cambies código, configuración, producción ni fuentes no canónicas.

Revisa el diff documental completo, enlaces, placeholders, contradicciones y
fechas. Ejecuta nuevamente el validador sobre el plan final.

## Resultado

- `UPDATED`: se modificó al menos un documento y no hay bloqueos.
- `NO_UPDATE`: la documentación vigente ya es correcta.
- `BLOCKED`: falta evidencia; no se modificó ningún documento.

Reporta documentos seleccionados y omitidos con razones, transición del `SPEC`,
evidencia usada, hechos no verificados y validaciones ejecutadas.
