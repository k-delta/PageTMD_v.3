---
name: safe-deploy
description: Use when preparing or executing an authorized deployment to the Tecnimontacargas production environment.
---

# Safe Deploy

## Overview

Controla despliegues PageTMD mediante autorización ligada a un manifiesto
inmutable, gates verificables y rollback focalizado. Complementa los runbooks;
no inventa un mecanismo de despliegue alternativo.

## Entradas

- Modo `prepare` o `execute`, entorno productivo y alcance exacto.
- Autorización actual, SPEC, revisión y validaciones locales.
- Archivos concretos, clasificación de deriva, backups y rollback.
- Validaciones productivas exigidas por el cambio.

“Prepara” no autoriza escrituras. Una solicitud explícita de desplegar
basta para `execute` tras los gates, sin segunda confirmación. Si cambia ruta,
contenido, hash, entorno u operación, la autorización deja de cubrir el
objetivo.

## Preparación

1. Lee `AGENTS.md`, el SPEC y `docs/runbooks/PRODUCTION.md`,
   `DEPLOYMENT.md` y `BACKUP_RESTORE.md`.
2. Ejecuta `git status --short`; fija el objetivo revisado y preserva cambios
   ajenos. Exige una revisión sin hallazgos `Critical` o `Important`.
3. Genera el manifiesto con
   `scripts/build-deploy-manifest.py --repo-root ROOT --paths-file PATHS
   --output MANIFEST --environment production`.
4. Completa el registro de
   [deploy-record-format.md](references/deploy-record-format.md) y valida con
   `scripts/validate-deploy-record.py RECORD --repo-root ROOT`.
5. Aplica los gates de preparación de
   [deployment-gates.md](references/deployment-gates.md).

`READY` termina `prepare` sin escribir; puede diferir el control productivo
hasta `execute`. Antes del primer write, `AUTHORIZED` exige los gates
pre-write, incluida deriva resuelta, y una autorización que referencie
exactamente hash y rutas.

## Control productivo

Ejecuta `./scripts/sync-production.sh --check`. Si devuelve deriva, detente y
compárala. Reanuda solo cuando cada diferencia esté explicada y el conjunto
autorizado siga siendo exacto. Nunca uses `--pull` automáticamente.

Antes de escribir:

- crea y verifica el backup aplicable para cada objetivo;
- define restauración y verificaciones de rollback;
- recalcula hashes y vuelve a validar el registro.

Despliega únicamente el manifiesto usando el procedimiento del runbook. No
copies el child theme completo, core, tema padre, terceros, snapshots,
temporales, secretos o backups. Infraestructura, base de datos, contenido y
uploads requieren autorización específica y sus gates especializados; el
manifiesto de archivos no los autoriza implícitamente.

## Verificación y rollback

Después de escribir, valida sintaxis, permisos, caché, logs, HTTP, navegador,
flujo afectado y controles específicos. Repite `sync-production.sh --check`.
Una copia exitosa o HTTP 200 aislado no demuestra éxito.

Ante un trigger del runbook, preserva evidencia, restaura solo los objetivos
afectados y repite sintaxis, logs, HTTP, flujo y sincronización. Un rollback
copiado pero no verificado sigue siendo `FAILED_UNVERIFIED`. Comprueba que el
archivo restaurado coincida con el hash del backup cuando uses
`restore-backup`; para `remove-created`, comprueba ausencia y referencias. Si
el rollback seguro puede ejecutarse, ejecútalo; si no puede completarse o
verificarse, escala y conserva `FAILED_UNVERIFIED`.

Estados permitidos:

- `BLOCKED`: ningún write; un gate pre-write pendiente o fallido impide avanzar.
- `READY`: preparación completa; ningún write autorizado.
- `AUTHORIZED`: autorización y gates completos; ningún write todavía.
- `DEPLOYED`: ejecución exacta y verificaciones completas.
- `ROLLED_BACK`: reversión ejecutada y verificada.
- `FAILED_UNVERIFIED`: hubo escritura y el estado final es incierto.

**REQUIRED SUB-SKILL:** usa `superpowers:verification-before-completion` antes
de declarar `DEPLOYED` o `ROLLED_BACK`. Después, usa `update-documentation` para
evaluar el estado documental. No hagas commit, push ni cambios adicionales.
