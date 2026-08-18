# Contrato del reporte consolidado

El reporte se guarda como JSON UTF-8:

```json
{
  "target": {"kind": "range", "base": "abc123", "head": "def456"},
  "generic_review": {"status": "reused", "reference": "review.md"},
  "coverage": [
    {
      "reviewer": "security_reviewer",
      "decision": "selected",
      "reason": "Cambió la autorización del endpoint."
    }
  ],
  "findings": [
    {
      "id": "RC-001",
      "severity": "Important",
      "owner": "security_reviewer",
      "path": "wp-content/plugins/example/api.php",
      "line": 42,
      "root_cause": "missing-authorization",
      "evidence": "La acción ejecuta la escritura sin comprobar capability.",
      "impact": "Un usuario sin permiso puede modificar el recurso.",
      "recommendation": "Comprobar la capability antes de la escritura.",
      "confidence": "high"
    }
  ],
  "validation_gaps": ["No se ejecutó la prueba de integración del endpoint."],
  "verdict": "BLOCKED"
}
```

`coverage` contiene exactamente una decisión razonada para cada uno de los cinco
revisores de `.codex/agents/`. `generic_review.status` es `reused` o
`generated`; en ambos casos `reference` apunta a una revisión completada. Un
especialista que sea dueño de un hallazgo debe figurar como `selected`.

Severidades heredadas de la revisión de Superpowers:

- `Critical`: daño grave, explotación o pérdida de datos plausible.
- `Important`: defecto que debe corregirse antes de integrar.
- `Minor`: mejora real que no bloquea la integración.

Un hallazgo necesita archivo, línea, evidencia observable, impacto,
recomendación accionable y confianza `high`, `medium` o `low`. No se aceptan
duplicados con la misma ruta, línea y causa raíz.

El veredicto resume hallazgos de revisión. No convierte validaciones pendientes
en resultados ni autoriza merge o despliegue.
