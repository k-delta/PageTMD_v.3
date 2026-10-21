# SPEC: Duración estimada del alquiler en la cotización

## Estado

- Terminado

## Contexto

- El usuario aprobó el selector y la ampliación visual el 2026-10-20.
- En una solicitud posterior del 2026-10-20, el usuario aprobó actualizar la jerarquía del hero de la misma landing con el texto “Refuerza tu operación con alquileres desde 15 días”.
- [Solicitud] En la sección de cotización de `/alquiler-montacargas-electricos/`, agregar una selección opcional para indicar cuánto tiempo se estima alquilar el montacargas.
- [Solicitud] Aplicar al formulario el estilo de tarjeta de la nueva captura: superficie azul diferenciada, borde sutil, esquinas redondeadas, controles oscuros y botón amarillo.
- [Decisión aprobada] Para el formulario, mantener los textos, campos y comportamiento comerciales actuales; la captura se usa como referencia visual. No agregar la promesa de respuesta en menos de 2 horas ni un checkbox de autorización.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:139-158`] El formulario canónico CF7 1556 tiene cinco controles y ninguno recoge la duración.
- [Evidencia: `scripts/update-rental-landing-v2.php:33-43`] El correo de cotización actual incluye los cinco campos existentes y no tiene una etiqueta para la duración.
- [Evidencia: `docs/specs/2026-10-05-landing-alquiler-v2.md:40`] El SPEC anterior terminó con un contrato de cinco controles; este cambio se limita a ampliar ese contrato.

## Problema

El formulario no permite indicar estructuradamente el periodo estimado del alquiler junto con los datos de la operación.

## Alcance adicional aprobado: hero

- [Solicitud aprobada] Agregar bajo el H1 el texto “Refuerza tu operación con alquileres desde 15 días” y darle una jerarquía visual intermedia.
- [Solicitud aprobada] Mantener “Equipos propios con mantenimiento en nuestro taller técnico” como subtítulo de menor tamaño; conservar el párrafo descriptivo, las métricas y la imagen de fondo.
- El H1 conserva el resaltado amarillo de “montacargas eléctricos”. No se agrega otra promesa comercial ni se cambia el contenido fuera del bloque hero.

## Objetivo

Agregar al formulario de cotización una selección opcional del periodo estimado, transmitir la opción elegida al asesor que recibe la solicitud, presentar el formulario con el tratamiento visual de tarjeta de la captura y aplicar la jerarquía del hero aprobada posteriormente.

## Fuera del alcance

- Cambiar el mínimo comercial de 15 días, las condiciones del alquiler o la información de las FAQ.
- Modificar los otros campos, su obligatoriedad, la política de privacidad, el honeypot, la protección anti-spam, el destinatario o las cabeceras del correo.
- Cambiar otros textos comerciales fuera del hero descrito abajo, agregar una promesa de tiempo de respuesta inferior a dos horas, agregar un checkbox de autorización o alterar la estructura de consentimiento.
- Cambiar otros formularios, páginas, estilos globales, SEO o contenido distinto de los campos del formulario y del hero descrito en esta SPEC.

## Requisitos funcionales

1. [Solicitud] El formulario CF7 1556 muestra una lista desplegable opcional con la etiqueta “Duración estimada del alquiler”.
2. [Solicitud] La lista ofrece exactamente estas opciones: “Mínimo 15 días”, “1 mes”, “3 meses”, “6 meses”, “1 año” y “Más de 1 año”.
3. [Solicitud] El usuario puede enviar el formulario sin seleccionar una duración.
4. [Evidencia: `scripts/update-rental-landing-v2.php:33-43`] Si se selecciona una opción, su texto queda incluido en el correo de la solicitud bajo la etiqueta “Duración estimada del alquiler”. Si se deja vacía, el envío no se rechaza.
5. [Evidencia: `docs/specs/2026-10-05-landing-alquiler-v2.md:40`] Se conservan los cinco controles actuales, sus estados de obligatoriedad y las protecciones y nota de privacidad existentes.
6. [Solicitud] El contenedor del formulario se presenta como una tarjeta azul diferenciada, con borde sutil, esquinas redondeadas y espaciado interior; los controles usan una superficie oscura con texto legible y el botón conserva su tratamiento amarillo.
7. [Solicitud aprobada posterior] El hero presenta el H1 con “montacargas eléctricos” resaltado, seguido por la nueva promesa y el subtítulo existente con tamaños visuales diferenciados.
8. [Solicitud aprobada posterior] El párrafo, las métricas y la imagen del hero se conservan.

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
6. [Solicitud aprobada posterior] En escritorio y móvil el texto adicional del hero es legible, conserva su jerarquía y no produce desbordamiento horizontal.

## Validación

- Pruebas unitarias: verificar que el render del formulario incluya el selector opcional, las seis opciones y que el mapeo de correo incluya su etiqueta; verificar que los campos anteriores permanezcan.
- Pruebas de integración: comprobar en Contact Form 7 que el tag sin asterisco es opcional, que `include_blank` conserva la opción vacía y que la etiqueta del campo se encuentra en la plantilla de correo.
- Validación manual: revisar la lista y su estado opcional en escritorio y móvil.
- Revisión visual manual: comparar la tarjeta, los campos oscuros y el botón con la referencia en escritorio y móvil; confirmar legibilidad, foco y ausencia de desbordamientos.
- Validación productiva: antes de escribir, realizar y verificar backup de la base de datos y snapshot privado de la página 1558/formulario 1556; después verificar el markup público, el tag del selector y la plantilla de correo. No enviar un correo real al asesor para probar el campo.
- Resultado local: las pruebas focalizadas de render, destinatario y actualizador pasaron; también pasaron los `php -l` aplicables y `git diff --check`.
- Resultado productivo (2026-10-21): el commit `b4283e4cecb7e197c23f36d6d364a1ffec09ddf2` y el CSS del tema ya estaban presentes en el checkout productivo; el SHA-256 del CSS coincidió con Git. La página 1558 recibió únicamente el reemplazo del bloque hero; el título, Rank Math y el resto de `post_content` se conservaron. CF7 1556 guardó el selector opcional y la etiqueta de correo, preservando las demás propiedades.
- Resultado productivo: WP-CLI confirmó los hashes objetivo de página y CF7; el selector conserva la opción vacía y las seis opciones indicadas, y la plantilla de correo contiene `[duracion_alquiler]`. La lectura pública devolvió HTTP 200 y cargó todos los CSS vinculados con HTTP 200.
- Revisión visual productiva: Chrome comprobó 1440×1000 y 390×844; `documentElement.scrollWidth` coincidió con el ancho de viewport y los estilos computados mostraron texto del hero blanco/amarillo, tarjeta azul, controles oscuros y CTA amarillo.
- Respaldo previo: dump MariaDB completo y snapshot privado de página 1558/CF7 1556 verificados. No se envió una solicitud real para evitar generar un correo al asesor; se verificaron en producción el tag opcional y su mapeo en la plantilla.
- `sync-production.sh --check` sigue reportando `.DS_Store` locales y diferencias del snapshot de auditoría (páginas, posts y metadatos de plugins) frente a producción. Esas diferencias se compararon; solo página 1558/CF7 1556 se modificaron en el alcance solicitado y el snapshot no se sincronizó.

## Riesgos

- El formulario está persistido en WordPress; actualizar solo el código del repositorio no cambia por sí mismo el formulario público.
- Un cambio al markup sin actualizar la plantilla del correo dejaría la duración fuera de la solicitud recibida por el asesor.

## Decisiones pendientes

- Ninguna.
