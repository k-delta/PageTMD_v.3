# SPEC: Duración estimada del alquiler en la cotización

## Estado

- Aprobado

## Contexto

- El usuario aprobó el selector y la ampliación visual el 2026-10-20.
- [Solicitud] En la sección de cotización de `/alquiler-montacargas-electricos/`, agregar una selección opcional para indicar cuánto tiempo se estima alquilar el montacargas.
- [Solicitud] Aplicar al formulario el estilo de tarjeta de la nueva captura: superficie azul diferenciada, borde sutil, esquinas redondeadas, controles oscuros y botón amarillo.
- [Decisión aprobada] Mantener los textos, campos y comportamiento comerciales actuales; la captura se usa como referencia visual. No agregar la promesa de respuesta en menos de 2 horas ni un checkbox de autorización.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:139-158`] El formulario canónico CF7 1556 tiene cinco controles y ninguno recoge la duración.
- [Evidencia: `scripts/update-rental-landing-v2.php:33-43`] El correo de cotización actual incluye los cinco campos existentes y no tiene una etiqueta para la duración.
- [Evidencia: `docs/specs/2026-10-05-landing-alquiler-v2.md:40`] El SPEC anterior terminó con un contrato de cinco controles; este cambio se limita a ampliar ese contrato.

## Problema

El formulario no permite indicar estructuradamente el periodo estimado del alquiler junto con los datos de la operación.

## Objetivo

Agregar al formulario de cotización una selección opcional del periodo estimado, transmitir la opción elegida al asesor que recibe la solicitud y presentar la sección del formulario con el tratamiento visual de tarjeta de la captura.

## Fuera del alcance

- Cambiar el mínimo comercial de 15 días, las condiciones del alquiler o la información de las FAQ.
- Modificar los otros campos, su obligatoriedad, la política de privacidad, el honeypot, la protección anti-spam, el destinatario o las cabeceras del correo.
- Cambiar textos comerciales, agregar una promesa de tiempo de respuesta, agregar un checkbox de autorización o alterar la estructura de consentimiento.
- Cambiar otros formularios, páginas, estilos globales, SEO o contenido distinto de esta selección.

## Requisitos funcionales

1. [Solicitud] El formulario CF7 1556 muestra una lista desplegable opcional con la etiqueta “Duración estimada del alquiler”.
2. [Solicitud] La lista ofrece exactamente estas opciones: “Mínimo 15 días”, “1 mes”, “3 meses”, “6 meses”, “1 año” y “Más de 1 año”.
3. [Solicitud] El usuario puede enviar el formulario sin seleccionar una duración.
4. [Evidencia: `scripts/update-rental-landing-v2.php:33-43`] Si se selecciona una opción, su texto queda incluido en el correo de la solicitud bajo la etiqueta “Duración estimada del alquiler”. Si se deja vacía, el envío no se rechaza.
5. [Evidencia: `docs/specs/2026-10-05-landing-alquiler-v2.md:40`] Se conservan los cinco controles actuales, sus estados de obligatoriedad y las protecciones y nota de privacidad existentes.
6. [Solicitud] El contenedor del formulario se presenta como una tarjeta azul diferenciada, con borde sutil, esquinas redondeadas y espaciado interior; los controles usan una superficie oscura con texto legible y el botón conserva su tratamiento amarillo.

## Reglas de negocio

- [Regla: `docs/domain/BUSINESS_RULES.md:12-14`] El alquiler mínimo es de 15 días; no se ofrecen horas ni periodos inferiores a 15 días.

## Contratos

### Entrada

```json
{
  "duracion_alquiler": ""
}
```

Valores permitidos cuando se selecciona una opción: “Mínimo 15 días”, “1 mes”, “3 meses”, “6 meses”, “1 año” o “Más de 1 año”. El valor vacío representa que el campo opcional no se respondió.

### Salida

```text
Duración estimada del alquiler: [duracion_alquiler]
```

## Casos límite

- El envío permanece válido con el selector vacío.
- Solo se puede transmitir una de las seis opciones definidas.

## Archivos o módulos relacionados

- `scripts/commercial-landing-rental-v2.php`
- `scripts/update-rental-landing-v2.php`
- `tests/test-commercial-landing-rental-v2-render.php`
- `tests/test-commercial-landing-rental-v2-update.php`
- Contact Form 7 1556 y la página WordPress 1558.

## Criterios de aceptación

1. [Solicitud] La sección del formulario presenta la etiqueta “Duración estimada del alquiler” y las seis opciones solicitadas.
2. [Solicitud] La duración es opcional: se puede enviar el formulario con el selector vacío.
3. [Evidencia: `scripts/update-rental-landing-v2.php:33-43`] Al enviar una opción, el correo de cotización muestra la duración elegida.
4. [Evidencia: `scripts/commercial-landing-rental-v2.php:151-157`] Los cinco campos existentes, su privacidad, honeypot y botón se conservan; no se cambia la obligatoriedad existente.
5. [Solicitud] En escritorio el formulario queda claramente agrupado en una tarjeta similar a la referencia; en móvil conserva el flujo de una columna sin desbordamiento ni pérdida de legibilidad.

## Validación

- Pruebas unitarias: verificar que el render del formulario incluya el selector opcional, las seis opciones y que el mapeo de correo incluya su etiqueta; verificar que los campos anteriores permanezcan.
- Pruebas de integración: comprobar que Contact Form 7 acepta el envío vacío y expone el valor seleccionado al correo.
- Validación manual: revisar la lista y su estado opcional en escritorio y móvil.
- Revisión visual manual: comparar la tarjeta, los campos oscuros y el botón con la referencia en escritorio y móvil; confirmar legibilidad, foco y ausencia de desbordamientos.
- Validación productiva: antes de escribir, realizar y verificar backup de la base de datos y snapshot privado de la página 1558/formulario 1556; después verificar el formulario público y el contenido del envío de prueba con datos sintéticos.

## Riesgos

- El formulario está persistido en WordPress; actualizar solo el código del repositorio no cambia por sí mismo el formulario público.
- Un cambio al markup sin actualizar la plantilla del correo dejaría la duración fuera de la solicitud recibida por el asesor.

## Decisiones pendientes

- Ninguna.
