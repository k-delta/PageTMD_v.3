# SPEC: Presentación visual del formulario PQR

## Estado

- Aprobado

## Contexto

La página pública `https://tecnimontacargas.com/nosotros/legal/pqr/` conserva su formulario como contenido HTML administrado desde WordPress.

[Evidencia: `content/pqr-page.html:3`] El contenido versionado usa las clases `tmd-pqr-wrap` y `tmd-pqr-card`.

[Evidencia: `content/pqr-page.html:18`] Los campos ya están organizados dentro de `tmd-form-grid`.

[Evidencia: `AGENTS.md:61`] `tmd-site-kit/`, donde existen estilos históricos para esas clases, está inactivo y no es fuente canónica.

## Problema

El formulario carece de estilos activos propios y presenta controles desalineados, anchos inconsistentes y poca jerarquía visual.

## Objetivo

Presentar el formulario PQR de forma clara, coherente con la identidad de Tecnimontacargas y adaptable a escritorio y móvil, sin retirar su contenido del editor de WordPress.

## Fuera del alcance

- Cambiar HTML, textos, campos u opciones del formulario.
- Cambiar destinatarios, correo, AJAX, JavaScript o cualquier lógica del formulario.
- Modificar `tmd-site-kit/` o reactivarlo.
- Desplegar o escribir en producción.

## Requisitos funcionales

1. [Solicitud] El formulario debe tener jerarquía visual clara, campos uniformes, espaciado consistente y colores de la identidad actual de Tecnimontacargas.
2. [Solicitud] El HTML debe permanecer como contenido editable desde el editor de WordPress.
3. [Solicitud] Petición, Queja, Reclamo y Reembolso deben mostrarse como controles visualmente consistentes, con un estado activo distinguible.
4. [Solicitud] En escritorio los campos deben usar dos columnas cuando el HTML lo permita; en pantallas estrechas deben apilarse sin desbordamiento horizontal.
5. [Solicitud] Inputs, textarea, consentimiento, nota y botón deben conservar estados visibles de foco y lectura suficiente.
6. [Regla: `AGENTS.md:63`] Los estilos deben implementarse en la fuente canónica activa y no en copias históricas.

## Reglas de negocio

- [Evidencia: `content/pqr-page.html:12`] Petición conserva el estado activo inicial definido por el contenido.
- [Evidencia: `content/pqr-page.html:29`] La nota de reembolsos y horario permanece sin cambios.
- [Regla: `AGENTS.md:115`] La identidad pública seguirá usando la marca Tecnimontacargas.

## Contratos

### Entrada

```json
{
  "page_id": 284,
  "markup": "contenido PQR administrado en WordPress"
}
```

### Salida

```json
{
  "presentation": "formulario PQR responsive y encapsulado",
  "content_editable": true,
  "behavior_changed": false
}
```

## Casos límite

- Pantallas pequeñas: campos y opciones se apilan sin recorte.
- Etiquetas o textos más largos: permiten salto de línea sin superponerse.
- Foco por teclado: inputs, textarea, checkbox y botones conservan indicador visible.
- CSS de Blocksy: selectores limitados a la página PQR evitan afectar otros formularios.

## Archivos o módulos relacionados

- `content/pqr-page.html` — referencia del HTML editable; no se modifica.
- `wp-content/themes/blocksy-child/style.css` — fuente canónica de estilos globales del child theme.

## Criterios de aceptación

1. [Solicitud] En escritorio, formulario aparece como tarjeta centrada; inputs no ocupan accidentalmente todo el viewport.
2. [Solicitud] En móvil, opciones, campos, consentimiento, nota y botón caben sin desplazamiento horizontal.
3. [Solicitud] Estado activo de tipo y estados de foco son visibles.
4. [Solicitud] Ninguna regla añadida se aplica fuera de `body.page-id-284`.
5. [Solicitud] `content/pqr-page.html` permanece sin cambios y editable en WordPress.
6. [Regla: `AGENTS.md:222`] Validación visual cubre escritorio y móvil.

## Validación

- Pruebas unitarias: No aplica; no cambia lógica.
- Pruebas de integración: comprobación estática de encapsulado de selectores y ausencia de cambios en HTML/PHP/JS.
- Validación manual: render de fixture HTML en escritorio y móvil; revisión de anchos, espaciado, foco y desbordamiento.
- Validación productiva: fuera de alcance; requiere autorización, backup, control de sincronización, despliegue mínimo, purga de caché y comprobación en navegador.

## Riesgos

- Un cambio futuro del ID de la página impediría aplicar estilos.
- El comportamiento de envío existente permanece intacto y no queda validado por esta tarea.

## Decisiones pendientes

- Ninguna. El usuario redujo y aprobó explícitamente el alcance a estilos, manteniendo el contenido editable desde WordPress.
