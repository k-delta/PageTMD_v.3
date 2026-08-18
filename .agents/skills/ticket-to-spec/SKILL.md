---
name: ticket-to-spec
description: Use when a ticket, issue, bug report, or informal request needs a repository-specific SPEC before implementation, especially when scope, evidence, acceptance criteria, or business decisions are incomplete.
---

# Ticket to Spec

## Overview

Convierte una solicitud vigente en un contrato verificable. Cada afirmación procede de la solicitud, una regla vigente o evidencia inspeccionada; lo demás queda pendiente.

## Cuándo usarlo

Úsalo para cambios no triviales sin SPEC aprobado. No lo uses para implementar, revisar, documentar después, desplegar ni para una corrección literal. Si la premisa es histórica, duplicada o satisfecha, informa la evidencia y no crees un SPEC.

## Contrato de entrada

Reúne solicitud e identificador, resultado y evidencia, restricciones, criterios expresos y decisiones autorizadas.

No conviertas una solución propuesta en requisito sin verificar el problema que pretende resolver.

## Inspección obligatoria

1. Lee `AGENTS.md`, `docs/architecture/REPO_MAP.md`, `docs/specs/README.md` y `docs/specs/TEMPLATE.md`.
2. Ejecuta `git status --short` y preserva cambios ajenos.
3. Inspecciona código o configuración canónicos y casos similares.
4. Lee solo `docs/domain/` aplicable; lee `docs/runbooks/` si hay producción, despliegue, backup o restauración.
5. Trata `docs/status/` y handoffs como temporales hasta reverificarlos. No consultes producción solo para redactar.

Antes de redactar, clasifica cada dato como requisito recibido, hecho verificado, inferencia técnica o decisión pendiente.

## Contrato de salida

Produce exactamente uno de estos resultados:

1. Ningún archivo y una explicación cuando no aplica, la solicitud no está vigente o el comportamiento ya existe.
2. Un único `docs/specs/YYYY-MM-DD-nombre-corto.md`, con estado `Borrador`, siguiendo todos los encabezados y su orden en `docs/specs/TEMPLATE.md`.

El borrador debe:

- describir el delta entre comportamiento actual y esperado;
- nombrar el síntoma o resultado, no la solución propuesta: para “inventario tarda” usa `latencia-inventario`, nunca `polling-30s`;
- separar alcance y fuera del alcance;
- prefijar cada requisito y criterio con `[Solicitud]`, `[Regla: ruta]` o `[Evidencia: ruta:línea]`; el Skill no es fuente de reglas;
- escribir `No aplica` cuando un contrato o validación realmente no corresponda;
- usar requisitos y criterios solo para comportamiento del producto, compatible y observable; referencia valores no definidos como `DEC-##` y descríbelos solo en `Decisiones pendientes`;
- distinguir validación local de validación productiva autorizada;
- terminar sin modificar código, producción ni documentación de dominio.

El agente no aprueba su propio SPEC. Los bloqueos viven en `Decisiones pendientes`, no en estados inventados ni en archivos alternativos de `docs/status/`.

Una implementación impuesta sin evidencia o una solicitud que contradice reglas queda en `Contexto` y `Decisiones pendientes`, nunca como requisito condicional. Escribe contratos objetivo no definidos como `Pendiente`; describe contratos actuales solo con evidencia.

Fechas, aprobación, backups y despliegue pertenecen a contexto, validación o decisiones; no sustituyen requisitos ni criterios funcionales.

## Validación

Ejecuta:

```bash
python3 .agents/skills/ticket-to-spec/scripts/validate-spec.py \
  docs/specs/YYYY-MM-DD-nombre-corto.md
```

Revisa el diff y confirma que el único cambio previsto sea el SPEC.

## Errores observados

- Handoffs históricos no sustituyen evidencia vigente.
- Título, requisitos y criterios describen el problema y el producto, no una solución o el proceso.
- La salida es un SPEC `Borrador` o ningún archivo; producción no completa vacíos.
