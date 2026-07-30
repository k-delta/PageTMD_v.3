# Contrato del registro

El registro JSON usa este esqueleto:

```json
{
  "version": 1,
  "deployment_id": "deploy-001",
  "mode": "prepare",
  "environment": "production",
  "manifest": {
    "version": 1,
    "environment": "production",
    "files": [
      {
        "path": "wp-content/themes/blocksy-child/assets/css/example.css",
        "size": 120,
        "sha256": "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
        "risk_class": "application"
      }
    ],
    "manifest_sha256": "bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb"
  },
  "authorization": null,
  "preflight": {
    "review_status": "READY",
    "local_validations_passed": true,
    "secrets_scan_passed": true,
    "hashes_reverified": true,
    "drift": {
      "status": "not-run",
      "paths": [],
      "evidence": "Pendiente para ejecución."
    }
  },
  "rollback": [
    {
      "target": "wp-content/themes/blocksy-child/assets/css/example.css",
      "strategy": "restore-backup",
      "artifact": null,
      "restore_steps": ["Restaurar solo el archivo."],
      "verification_steps": ["Validar HTTP y flujo.", "Ejecutar sync check."],
      "performed": false,
      "verified": false
    }
  ],
  "execution": {"performed": false, "completed": false, "files": []},
  "postchecks": [],
  "rollback_checks": [],
  "post_sync": {"status": "not-run", "evidence": ""},
  "blockers": [],
  "result": "READY"
}
```

Para `execute`, `authorization` contiene `authorized: true`,
`current_request: true`, `environment`, `manifest_sha256` y `scope`. `scope`
coincide exactamente con las rutas del manifiesto.

Usa `AUTHORIZED` después de validar autorización, deriva, backups y rehash, pero
antes de la primera escritura. `READY` pertenece solo a `prepare` y permite
`drift.status: "not-run"`. `BLOCKED` exige cero escrituras y al menos un
blocker; puede registrar gates pendientes o fallidos.

Un artifact de backup contiene `path`, `size`, `sha256` y `verified`. No
registres secretos ni contenido del backup. `postchecks` y `rollback_checks`
usan `name`, `required`, `status` (`passed`, `failed` o `pending`) y `evidence`.
Con `restore-backup`, verifica el hash restaurado frente al artifact. Con
`remove-created`, verifica ausencia del objetivo y sus referencias.

Este formato ejecutable cubre archivos versionados. No reutilices
`authorization.scope` para representar tablas, contenido o uploads: esas
operaciones necesitan un manifiesto especializado o permanecen `BLOCKED`.

El validador vuelve a leer los archivos locales y recalcula hashes. Ejecutarlo
inmediatamente antes de la primera escritura y sobre el registro final.
