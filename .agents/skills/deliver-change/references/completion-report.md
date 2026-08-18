# Contrato del informe final

Usa esta forma y omite únicamente campos realmente no aplicables:

```markdown
Estado: NEEDS_INPUT | AWAITING_SPEC_APPROVAL | READY_FOR_IMPLEMENTATION |
        IN_PROGRESS | BLOCKED | DELIVERED

Gate actual
- Gate superado o causa exacta de detención.

Solicitud
- Interpretación y resultado acordado.

SPEC
- Ruta, versión/estado y decisiones aprobadas.

Alcance
- Incluido y fuera de alcance.

Archivos
- Ruta y cambio realizado.

Pruebas
- Comando o comprobación, resultado y momento.

Revisión
- Objetivo, revisión general, revisores seleccionados/omitidos y veredicto.

Correcciones
- Hallazgo verificado, decisión y nueva evidencia.

Verificación
- Criterio de aceptación → evidencia reciente.

Documentación
- UPDATED, NO_UPDATE o BLOCKED; documentos, razones y estado del SPEC.

Autorización
- Edición: autorizada o pendiente.
- Commit: autorizado o pendiente.
- Push: autorizado o pendiente.
- PR: autorizado o pendiente.
- Merge: autorizado o pendiente.
- Producción: autorizada o pendiente.

Producción
- Verificado, no verificado o no aplicable; nunca inferido desde local.

Pendiente
- Bloqueos, riesgos, limitaciones y siguiente decisión necesaria.
```

## Reglas de evidencia

- Relaciona cada afirmación con un archivo, diff, comando, prueba, navegador o
  fuente externa identificable.
- Distingue `local`, `automatizado`, `navegador` y `producción`.
- “No ejecutado”, “no verificado” y “fuera del alcance” son resultados válidos.
- No conviertas el reporte en autorización para acciones pendientes.
- `DELIVERED` exige todo el alcance acordado, criterios comprobados, revisión
  resuelta y documentación no bloqueada. El SPEC relacionado debe quedar en
  estado `Terminado`.
- Si integración o producción pertenecían al alcance, espera el resultado de
  su Skill propietario. Si estaban explícitamente fuera, informa
  `DELIVERED` local y mantenlas pendientes sin presentarlas como defecto.
- `BLOCKED` identifica el gate, evidencia disponible y decisión exacta
  necesaria; no presenta trabajo parcial como cierre.
