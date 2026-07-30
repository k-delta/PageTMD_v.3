# Gates de despliegue

| Gate | Evidencia mínima | Si falla |
| --- | --- | --- |
| Autoridad | Solicitud actual, producción, modo, rutas y hash del manifiesto | `BLOCKED` |
| Alcance | Rutas exactas, fuente canónica, sin archivos ajenos | `BLOCKED` |
| Revisión | Sin hallazgos `Critical` o `Important` | `BLOCKED` |
| Validación local | Pruebas focalizadas y control de secretos | `BLOCKED` |
| Deriva previa | `clean` o diferencias `intended-only` enumeradas | Comparar; no escribir |
| Reversibilidad | Estrategia por objetivo, backup verificado o eliminación de archivo nuevo | `BLOCKED` |
| Inmutabilidad | Hash local coincide justo antes de escribir | Renovar manifiesto y autorización |
| Ejecución | Archivos copiados coinciden exactamente con el manifiesto | Detener y evaluar rollback |
| Validación posterior | Sintaxis, logs, HTTP, navegador y flujo aplicable | Rollback |
| Sincronización posterior | `sync-production.sh --check` comprobado | Rollback; `FAILED_UNVERIFIED` solo si no puede completarse o verificarse |
| Rollback | Resultado de la estrategia, sintaxis, logs, HTTP, flujo y sync comprobados | `FAILED_UNVERIFIED` |

## Reglas por tipo

- Archivos existentes: backup no vacío, legible, con integridad y ruta de
  restauración; tras `restore-backup`, hash final igual al artifact.
- Archivos nuevos: rollback `remove-created`, objetivo ausente y referencias
  verificadas.
- Base de datos: tablas exactas, dump verificado, compatibilidad y restauración.
- Uploads: objetivos exactos, backup, permisos, URL, tamaño y formato.
- Infraestructura: autorización específica, configuración respaldada y plan de
  reversión; no reiniciar como primer diagnóstico.
- Correo: `wp_mail()` exitoso no basta; confirmar recepción y proveedor.

## Condiciones de detención

- Autorización histórica, ambigua o para otro manifiesto.
- Deriva ajena o no clasificada.
- Archivo cambiado después de revisión/autorización.
- Backup vacío, corrupto o asociado a otro objetivo.
- Operación destructiva sin restauración comprobable.
- Dependencia de secretos no disponibles.
- Estado productivo incierto.

`READY` puede conservar deriva `not-run` porque no autoriza escrituras.
`AUTHORIZED` exige deriva `clean` o `intended-only`, backups verificados y
rehash inmediato. Si un gate posterior activa el rollback y este puede
ejecutarse de forma segura, ejecútalo; usa `FAILED_UNVERIFIED` cuando la
reversión o su verificación no puedan completarse.

El registro y sus scripts incluidos validan despliegues de archivos
versionados. Una operación sobre base de datos, contenido o uploads permanece
`BLOCKED` hasta disponer de un manifiesto especializado que describa objetivo,
operación, backup, autorización y verificación; autorizar el archivo de una
migración no autoriza sus efectos.

Las aprobaciones técnicas solicitadas por la plataforma permiten ejecutar el
comando; no amplían el alcance autorizado por el usuario.
