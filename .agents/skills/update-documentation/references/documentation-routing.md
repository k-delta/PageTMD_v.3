# Enrutamiento documental

| Impacto comprobado | Documento canónico |
| --- | --- |
| Límites, responsabilidades, módulos, flujos o fuentes canónicas | `docs/architecture/REPO_MAP.md` |
| Identidad, alquiler, venta o mantenimiento | `docs/domain/BUSINESS_RULES.md` |
| Contrato o integración de equipos/Firebase | `docs/domain/INVENTORY.md` |
| Menú, rutas, header, footer o responsive permanente | `docs/domain/NAVIGATION.md` |
| Autoridad SEO, schema, canonical, robots o sitemap | `docs/domain/SEO.md` |
| Cuenta o funciones comerciales de WooCommerce | `docs/domain/COMMERCE.md` |
| Operación productiva | `docs/runbooks/PRODUCTION.md` |
| Procedimiento de despliegue | `docs/runbooks/DEPLOYMENT.md` |
| Backup o restauración | `docs/runbooks/BACKUP_RESTORE.md` |
| Hecho temporal verificado | `docs/status/CURRENT_STATE.md` |
| Decisiones, evidencia y estado de una tarea | `docs/specs/<spec>.md` |
| Regla permanente para agentes | `AGENTS.md` |

Una ruta afectada solo genera un candidato. Selecciona el documento únicamente
si el cambio modifica la verdad que contiene. Un bug que restaura el
comportamiento ya documentado y un refactor privado normalmente son
`NO_UPDATE`.

## Límites

- `CURRENT_STATE.md` no es changelog, backlog ni prueba de despliegue.
- El `SPEC` registra el cierre de su tarea; no sustituye reglas permanentes.
- `AGENTS.md` no recibe métricas, resultados puntuales, pendientes ni decisiones
  de un ticket.
- Los runbooks cambian cuando cambia el procedimiento, no cada vez que se
  ejecuta.
- No documentar copias históricas, snapshots ni `.codex-tmp/` como fuentes
  canónicas.

## Racionalizaciones: detenerse

| Excusa | Respuesta |
| --- | --- |
| “Actualiza todo para estar seguros.” | Más archivos no producen más exactitud. |
| “Marcar el SPEC terminado es solo documentación.” | El estado afirma que criterios y validaciones se cumplieron. |
| “Producción debería estar bien.” | Una expectativa no es evidencia temporal. |
| “Firebase es conocido.” | Consultar contrato vigente y configuración real. |
| “El archivo cambió, por tanto su doc cambia.” | Las rutas indican candidatos, no impacto semántico. |
