# Contrato del plan documental

El plan es JSON UTF-8:

```json
{
  "target": {
    "kind": "range",
    "reference": "abc123..def456",
    "environment": "production"
  },
  "scope": [
    {
      "area": "architecture",
      "decision": "skipped",
      "documents": [],
      "reason": "No cambian límites ni responsabilidades.",
      "evidence": ["diff revisado"]
    },
    {
      "area": "domain",
      "decision": "selected",
      "documents": ["docs/domain/INVENTORY.md"],
      "reason": "Cambió el contrato de inventario.",
      "evidence": ["diff: función consumidora y respuesta"]
    },
    {
      "area": "runbooks",
      "decision": "skipped",
      "documents": [],
      "reason": "No cambia un procedimiento operativo.",
      "evidence": ["diff revisado"]
    },
    {
      "area": "status",
      "decision": "skipped",
      "documents": [],
      "reason": "No cambia un hecho temporal.",
      "evidence": ["validaciones revisadas"]
    },
    {
      "area": "spec",
      "decision": "selected",
      "documents": ["docs/specs/2026-07-29-contract.md"],
      "reason": "La tarea terminó con evidencia completa.",
      "evidence": ["criterios y validaciones comprobados"]
    },
    {
      "area": "agents",
      "decision": "skipped",
      "documents": [],
      "reason": "No cambia una regla permanente.",
      "evidence": ["AGENTS.md comparado con el cambio"]
    }
  ],
  "changed_documents": [
    "docs/domain/INVENTORY.md",
    "docs/specs/2026-07-29-contract.md"
  ],
  "spec_transition": {
    "path": "docs/specs/2026-07-29-contract.md",
    "previous_status": "En desarrollo",
    "new_status": "Terminado",
    "acceptance_verified": true,
    "validations_verified": true,
    "open_decisions": false
  },
  "temporal_evidence": [],
  "unverified_facts": [],
  "blockers": [],
  "result": "UPDATED"
}
```

`scope` contiene una entrada para cada área: `architecture`, `domain`,
`runbooks`, `status`, `spec` y `agents`. Un área `selected` enumera documentos
exactos; un área `skipped` usa una lista vacía. Toda decisión lleva razón y
evidencia.

Para `UPDATED`, `changed_documents` coincide exactamente con los documentos
seleccionados. Para `BLOCKED`, permanece vacío aunque las áreas candidatas estén
`selected`, y existe al menos un bloqueo. Para `NO_UPDATE`, todas las áreas
están omitidas.

`temporal_evidence` usa objetos con `source`, `environment`, `checked_at` y
`target_reference`; este último coincide con `target.reference`. Para
`UPDATED`, la comprobación debe pertenecer a las últimas 24 horas. Un plan
`BLOCKED` puede dejarla vacía si declara la evidencia ausente en
`unverified_facts` y `blockers`.
`target.environment` es obligatorio cuando se selecciona estado temporal y debe
coincidir con la evidencia. Cada elemento de `unverified_facts` contiene `fact`,
`area` y `document`; el documento debe estar seleccionado en esa área. Cualquier
entrada obliga a bloquear. No incluyas incertidumbres irrelevantes.

El validador comprueba estructura, no verdad semántica. Antes de aceptar un
bloqueo, contrasta cada hecho y razón con el diff, la evidencia y el documento
seleccionado; una etiqueta JSON no demuestra relevancia.
