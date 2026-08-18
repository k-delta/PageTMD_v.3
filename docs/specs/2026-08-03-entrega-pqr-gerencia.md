# SPEC: Entrega de solicitudes PQR a gerencia

## Estado

- Aprobado

## Contexto

La página pública `https://tecnimontacargas.com/nosotros/legal/pqr/` contiene un formulario PQR administrado como HTML desde el editor de WordPress.

[Evidencia: `content/pqr-page.html:4`] El formulario declara `data-tmd-ajax-form`, pero la fuente canónica activa no registra un manejador específico para PQR.

[Evidencia: `content/pqr-page.html:5`] El formulario identifica su tipo como `pqr`.

[Evidencia: `docs/specs/2026-08-03-postulacion-cv-correo.md:11`] Producción usa WPO365 Microsoft Graph Mailer como transporte de `wp_mail()`.

[Evidencia: `docs/specs/2026-08-03-postulacion-cv-correo.md:46`] `consultor1@tmdual.com` es la cuenta remitente configurada en WPO365; no determina por sí sola el destinatario del mensaje.

La documentación oficial vigente de WPO365 diferencia el buzón `From` autorizado del destinatario `To`. También advierte que “Send to BCC” puede reemplazar globalmente destinatarios por un `Default To`; esta tarea no modificará esa configuración global.

## Problema

No existe en el tema hijo un flujo PQR verificable que procese el formulario y entregue la solicitud al destinatario de gerencia solicitado. Confundir la cuenta remitente de WPO365 con el destinatario podría desviar otros correos del sitio.

## Objetivo

Procesar solicitudes PQR válidas desde la página existente y enviarlas exclusivamente a `gerencia@tmdual.com`, conservando `consultor1@tmdual.com` como remitente gestionado por WPO365 y manteniendo el formulario editable desde WordPress.

## Fuera del alcance

- Cambiar la cuenta remitente, `Default To`, “Send to BCC”, credenciales o permisos de WPO365.
- Cambiar destinatarios de otros formularios o correos transaccionales.
- Convertir el contenido PQR en shortcode, template rígido o formulario de un plugin.
- Persistir solicitudes PQR en base de datos o logs.
- Actualizar WordPress, WPO365 u otros plugins.
- Desplegar o escribir en producción.

## Requisitos funcionales

1. [Solicitud] Una solicitud PQR válida debe enviarse exclusivamente a `gerencia@tmdual.com`.
2. [Solicitud] WPO365 debe continuar transportando el correo desde la cuenta autorizada `consultor1@tmdual.com`; el formulario no debe intentar reemplazar el `From`.
3. [Evidencia: `content/pqr-page.html:10`] El servidor debe aceptar únicamente Petición, Queja, Reclamo o Reembolso como tipo de solicitud.
4. [Evidencia: `content/pqr-page.html:18`] El mensaje debe incluir tipo, asunto, nombre, email, teléfono, número de pedido cuando exista y descripción detallada.
5. [Solicitud] El correo debe definir `Reply-To` con el email validado de la persona remitente.
6. [Solicitud] El HTML del formulario debe permanecer administrable desde el editor de WordPress.
7. [Solicitud] El envío debe ocurrir sin abandonar la página y debe comunicar estados de procesamiento, éxito o error.
8. [Solicitud] El botón debe impedir envíos duplicados mientras exista una solicitud en curso; solo el éxito debe limpiar campos y restablecer Petición como opción inicial.
9. [Regla: `AGENTS.md:97`] Datos personales no deben guardarse en logs, archivos versionados ni mensajes de diagnóstico.
10. [Regla: `AGENTS.md:229`] Un resultado positivo de `wp_mail()` solo debe comunicarse como solicitud procesada; no debe presentarse como recepción confirmada en la bandeja de gerencia.

## Reglas de negocio

- [Solicitud] Destinatario `To`: `gerencia@tmdual.com`.
- [Solicitud] Remitente administrado por WPO365: `consultor1@tmdual.com`.
- [Evidencia: `content/pqr-page.html:23`] Número de pedido es opcional; campos marcados con asterisco son obligatorios.
- [Evidencia: `content/pqr-page.html:25`] Aceptación de términos es obligatoria.
- [Evidencia: `content/pqr-page.html:29`] Nota de reembolsos y horario permanece sin cambios.

## Contratos

### Entrada

```json
{
  "form_type": "pqr",
  "request_type": "Peticion | Queja | Reclamo | Reembolso",
  "subject": "string requerido",
  "name": "string requerido",
  "email": "email requerido",
  "phone": "string requerido",
  "order_number": "string opcional",
  "message": "string requerido",
  "terms": "Acepto",
  "nonce": "nonce válido"
}
```

### Salida

```json
{
  "success": true,
  "data": {
    "message": "Solicitud PQR procesada correctamente."
  }
}
```

Para entrada inválida, sesión vencida, límite excedido o fallo de `wp_mail()`, la respuesta tendrá `success: false`, estado HTTP acorde y mensaje público sin datos personales ni detalles internos.

## Casos límite

- Tipo de solicitud alterado fuera de la lista permitida: rechazar sin enviar correo.
- Campo obligatorio vacío, email inválido o términos no aceptados: rechazar sin enviar correo.
- Campos mayores a límites del servidor: rechazar con mensaje genérico.
- Envíos repetidos desde mismo origen/email: aplicar límite temporal.
- `wp_mail()` devuelve `false` o lanza excepción: mostrar error recuperable y conservar campos.
- Respuesta no JSON o fallo de red: restaurar botón y permitir reintento.
- JavaScript o configuración AJAX ausente: no producir error de consola.
- WPO365 en modo global “Send to BCC”: la validación productiva debe comprobar que no sustituya el destinatario PQR por otro `Default To`.

## Archivos o módulos relacionados

- `content/pqr-page.html` — contenido editable, no requiere convertirse en código.
- `wp-content/themes/blocksy-child/functions.php`
- `wp-content/themes/blocksy-child/inc/`
- `wp-content/themes/blocksy-child/assets/js/`
- `tests/`
- WPO365 Microsoft Graph Mailer como transporte de `wp_mail()`.

## Criterios de aceptación

1. [Solicitud] Un envío válido construye un correo cuyo único `To` es `gerencia@tmdual.com`.
2. [Solicitud] El correo no fuerza un `From`; WPO365 conserva `consultor1@tmdual.com` como remitente autorizado.
3. [Solicitud] El correo contiene todos los campos PQR sanitizados y `Reply-To` coincide con el email validado.
4. [Solicitud] Tipo inválido, campos inválidos, nonce incorrecto y exceso de intentos no invocan `wp_mail()`.
5. [Solicitud] Éxito limpia el formulario y restablece Petición; error conserva datos y permite reintentar.
6. [Solicitud] El formulario continúa siendo contenido editable en WordPress y no se reemplaza por shortcode o template rígido.
7. [Regla: `AGENTS.md:229`] Pruebas locales distinguen `wp_mail()` aceptado de recepción real; recepción en `gerencia@tmdual.com` solo se confirma mediante prueba productiva autorizada.
8. [Solicitud] Ningún correo de contacto, postulaciones, cotizaciones u otro flujo cambia de destinatario.

## Validación

- Pruebas unitarias: sanitización, campos obligatorios, tipos permitidos, límites, destinatario único, cuerpo, asunto y `Reply-To`.
- Pruebas de integración: endpoint AJAX público/autenticado; nonce válido e inválido; éxito, fallo y excepción simulada de `wp_mail()`; rate limit; carga de JavaScript limitada a la página PQR.
- Validación manual: selección de tipo, validación nativa, estados de procesamiento/éxito/error, reset solo exitoso y consola sin errores.
- Validación productiva: fuera del alcance local. Con autorización y backup: verificar `./scripts/sync-production.sh --check`, configuración WPO365 sin reemplazo global de `To`, envío controlado, recepción real en `gerencia@tmdual.com`, `From` consultor1, `Reply-To` del remitente y registro Graph sin errores.

## Riesgos

- WPO365 puede aceptar el mensaje sin garantizar entrega final; recepción requiere comprobación de bandeja.
- Si “Send to BCC” está activo, WPO365 puede sustituir el `To` definido por el formulario por el `Default To` global.
- `functions.php` contiene cambios locales ajenos; la inclusión del módulo deberá ser mínima y preservarlos.
- El formulario comparte el atributo genérico `data-tmd-ajax-form` con otros contenidos; JavaScript debe validar `form_type=pqr` para no interceptarlos.

## Decisiones pendientes

- Ninguna. El usuario aprobó esta versión el 3 de agosto de 2026.
