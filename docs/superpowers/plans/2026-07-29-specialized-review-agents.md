# Specialized Review Agents Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crear cinco revisores especializados y un ejecutor de validaciones con responsabilidades exclusivas, integrados con `review-change` sin duplicar la revisión general de Superpowers.

**Architecture:** `review-change` seguirá siendo el coordinador: reutiliza o solicita la revisión general, selecciona solo los especialistas pertinentes y consolida sus hallazgos. Los revisores serán configuraciones TOML de solo lectura; `test_runner` tendrá escritura de workspace limitada a artefactos de validación y nunca corregirá archivos.

**Tech Stack:** Codex custom agents (`.codex/agents/*.toml`), Markdown, Python 3.9 `unittest`, scripts existentes de `review-change`.

## Global Constraints

- No modificar código funcional, contenido de dominio ni infraestructura.
- No desplegar y no ejecutar commit, push, merge ni cambio de rama.
- Preservar todos los cambios locales ajenos.
- Eliminar `clean_code_reviewer` porque duplica la revisión general de Superpowers.
- Cada revisor debe producir `NO_FINDINGS` o hallazgos con archivo, línea, evidencia, causa raíz, impacto, severidad, recomendación y confianza.
- Cada agente debe tener casos positivo, negativo y de falso positivo.

---

### Task 1: Contratos evaluables de los agentes

**Files:**
- Create: `evals/review-change/test_agent_contracts.py`
- Create: `evals/review-change/agent-cases/architecture-reviewer.json`
- Create: `evals/review-change/agent-cases/security-reviewer.json`
- Create: `evals/review-change/agent-cases/database-reviewer.json`
- Create: `evals/review-change/agent-cases/performance-reviewer.json`
- Create: `evals/review-change/agent-cases/test-reviewer.json`
- Create: `evals/review-change/agent-cases/test-runner.json`

**Interfaces:**
- Consumes: configuraciones TOML de `.codex/agents/`.
- Produces: una suite que valida nombres, sandbox, límites exclusivos, contrato de salida y tres clases de evaluación por agente.

- [x] **Step 1: Escribir evaluaciones que representen el diseño aprobado**

La suite debe cargar el subconjunto TOML usado por los agentes sin depender de paquetes externos, comprobar que `clean_code_reviewer` no existe y verificar los campos/instrucciones obligatorios.

- [x] **Step 2: Ejecutar RED**

Run: `python3 evals/review-change/test_agent_contracts.py -v`

Expected: `FAIL`, porque existe `clean-code-reviewer.toml` y los agentes actuales no contienen todos los límites ni el contrato uniforme.

- [x] **Step 3: Registrar las fallas y racionalizaciones actuales**

La salida RED debe identificar al menos: duplicación de clean code, contratos incompletos, contexto no limitado y falta de casos por agente.

### Task 2: Configuraciones mínimas de agentes

**Files:**
- Modify: `.codex/agents/architecture-reviewer.toml`
- Modify: `.codex/agents/security-reviewer.toml`
- Modify: `.codex/agents/database-reviewer.toml`
- Modify: `.codex/agents/performance-reviewer.toml`
- Modify: `.codex/agents/test-reviewer.toml`
- Modify: `.codex/agents/test-runner.toml`
- Delete: `.codex/agents/clean-code-reviewer.toml`

**Interfaces:**
- Consumes: paquete acotado de diff, requisitos, instrucciones aplicables y evidencia.
- Produces: `NO_FINDINGS` o hallazgos estructurados; `test_runner` produce evidencia de ejecución, no hallazgos.

- [x] **Step 1: Implementar el mínimo que satisface los contratos**

Cada revisor debe declarar propiedad exclusiva, exclusiones explícitas, contexto permitido, solo lectura y forma de salida. `test_runner` debe aceptar únicamente comandos focalizados explícitos, no corregir archivos y separar regresiones de bloqueos ambientales.

- [x] **Step 2: Ejecutar GREEN focalizado**

Run: `python3 evals/review-change/test_agent_contracts.py -v`

Expected: `PASS`.

### Task 3: Integración coherente con review-change

**Files:**
- Modify: `.agents/skills/review-change/SKILL.md`
- Modify: `.agents/skills/review-change/references/reviewer-routing.md`
- Modify: `.agents/skills/review-change/references/finding-format.md`
- Modify: `.agents/skills/review-change/scripts/validate-findings.py`
- Modify: `evals/review-change/test_review_change_tools.py`

**Interfaces:**
- Consumes: los cinco nombres de revisores especializados.
- Produces: cobertura exactamente una vez por revisor y rechazo de `clean_code_reviewer` como propietario o cobertura.

- [x] **Step 1: Cambiar primero las pruebas del enrutamiento y reporte**

Actualizar la constante esperada a cinco revisores y añadir una prueba que rechace cobertura o propiedad de `clean_code_reviewer`.

- [x] **Step 2: Ejecutar RED de integración**

Run: `python3 evals/review-change/test_review_change_tools.py -v`

Expected: `FAIL` mientras el Skill y el validador sigan aceptando seis revisores.

- [x] **Step 3: Ajustar documentación y validador**

Eliminar el revisor genérico de mantenibilidad, conservar la revisión general de Superpowers como propietaria de calidad transversal y mantener `test_runner` fuera de `coverage`.

- [x] **Step 4: Ejecutar GREEN completo**

Run: `python3 -m unittest discover -s evals/review-change -p 'test_*.py' -v`

Expected: `PASS`.

### Task 4: REFACTOR y validación final

**Files:**
- Modify only if needed: files from Tasks 1-3.

**Interfaces:**
- Consumes: resultados GREEN y diff final.
- Produces: configuraciones sin contradicciones, enlaces válidos y evidencia final.

- [x] **Step 1: Revisar ambigüedades y duplicados**

Comprobar que SQL/N+1 pertenece a base de datos, rendimiento excluye SQL, seguridad conserva explotabilidad, pruebas no ejecuta comandos y el runner no emite hallazgos.

- [x] **Step 2: Validar TOML, JSON y enlaces internos**

Run: `codex --version`

Run: `python3 -m json.tool evals/review-change/agent-cases/architecture-reviewer.json`

Run: `python3 -m unittest discover -s evals/review-change -p 'test_*.py' -v`

Run: `git diff --check`

- [x] **Step 3: Revisar alcance**

Run: `git status --short`

Run: `git diff -- .codex/agents .agents/skills/review-change evals/review-change docs/superpowers/plans/2026-07-29-specialized-review-agents.md`

Confirmar que no hay código funcional, despliegue, commit, push ni merge.

## Evidencia RED, GREEN y REFACTOR

### RED

- Contratos de agentes: 12 fallos iniciales. Existía
  `clean-code-reviewer.toml`; faltaban límites exclusivos, contexto acotado,
  `NO_FINDINGS`, causa raíz y clasificación ambiental del runner.
- Integración: 2 fallos. Un reporte de cinco revisores era rechazado y
  `clean_code_reviewer` todavía era aceptado.
- Los escenarios conductuales descubrieron que los positivos no aportaban
  archivo y línea, y que el runner tenía decisiones implícitas y carecía de
  alcance, motivo y autorización en los casos ejecutables.

### GREEN

- Las configuraciones quedaron limitadas a cinco revisores de solo lectura y
  un `test_runner` con escritura de workspace restringida.
- `review-change` exige cobertura de exactamente cinco especialistas y rechaza
  el rol retirado.
- Los seis agentes clasificaron correctamente sus escenarios positivo,
  negativo y falso positivo: 18 de 18 decisiones.

### REFACTOR

- Se añadieron evidencia, ruta y línea a cada caso que espera un hallazgo.
- `test_runner` declara `RUN`, `REFUSE` y `BLOCKED`, y sus casos ejecutables
  incluyen comando, directorio, alcance, motivo y autorización.
- Se añadieron comprobaciones de estructura TOML restringida y enlaces
  internos de `review-change`.

### Racionalizaciones cerradas

- “Una revisión extra de clean code da más cobertura”: duplica la revisión
  general de Superpowers y favorece preferencias personales.
- “La descripción del cambio basta para un hallazgo”: sin archivo, línea y
  evidencia el revisor tendría que inventar datos.
- “El runner puede inferir autorización y alcance”: debe recibirlos de forma
  explícita antes de ejecutar.
- “Una herramienta ausente demuestra una regresión”: corresponde a
  `BLOCKED`, no a un defecto del cambio.
