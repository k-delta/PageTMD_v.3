# SPEC: Postulación general con CV y correo de Recursos Humanos

## Estado

- Aprobado

## Contexto

La página pública `Trabaja con nosotros` (ID 273) contiene un formulario HTML propio con `form_type=trabaja_con_nosotros` y `enctype=multipart/form-data`. La sección `Adjuntar CV` es actualmente un bloque informativo: no contiene un campo de archivo. La inspección del HTML público tampoco encontró un controlador JavaScript, endpoint o nonce asociado a `data-tmd-ajax-form`, por lo que el formulario no dispone de un flujo verificable de envío.

Producción tiene activo `wpo365-msgraphmailer` 5.10 y configurado un buzón de Microsoft 365 como remitente. WPO365 reemplaza el transporte de `wp_mail()` mediante Microsoft Graph; el buzón configurado como remitente no determina el destinatario de cada mensaje. La documentación oficial indica que la edición instalada admite adjuntos menores de 3 MB.

## Problema

Una persona no puede adjuntar su hoja de vida ni enviar desde la página los datos de la postulación a Recursos Humanos. El texto actual le indica usar correo o WhatsApp, aunque el diseño presenta un botón `Enviar Postulación` que no está conectado a un envío verificable.

## Objetivo

Permitir que una postulación general válida adjunte un único archivo PDF, DOC o DOCX y envíe todos los datos del formulario a `rh@tmdual.com`, utilizando el transporte WPO365 ya configurado y mostrando un resultado claro sin abandonar la página.

## Fuera del alcance

- Cambiar el buzón remitente configurado en WPO365 o permitir que el navegador elija remitente o destinatario.
- Configurar destinatarios para PQR, contacto, cotizaciones u otros formularios.
- Guardar hojas de vida en la biblioteca de medios, base de datos, Flamingo o un servicio externo.
- Cambiar vacantes, textos comerciales, imágenes o estructura general de la página.
- Actualizar WordPress, WPO365, Contact Form 7 u otros plugins.
- Escribir en producción, desplegar, crear backups, purgar caché, hacer commit o push sin autorización explícita posterior.

## Requisitos funcionales

1. [Solicitud] La sección `Adjuntar CV` debe contener un campo de archivo obligatorio que acepte exactamente `.pdf`, `.doc` y `.docx`.
2. [Solicitud] Una postulación válida debe enviarse a `rh@tmdual.com` con nombre, email, teléfono, ciudad, área de interés, mensaje y CV adjunto.
3. [Solicitud] El texto `Adjunta tu hoja de vida en PDF desde el correo o WhatsApp indicado.` debe reemplazarse por instrucciones para seleccionar un archivo PDF, DOC o DOCX.
4. [Evidencia: producción página 273] El envío debe usar el `form_type=trabaja_con_nosotros` existente y mantener el diseño responsive del formulario.
5. [Evidencia: documentación oficial WPO365] El mensaje debe usar `wp_mail()` para conservar WPO365 como transporte y limitar el CV a 2 MB, por debajo del límite de adjunto inferior a 3 MB de la edición instalada.
6. [Regla: AGENTS.md] El destinatario debe resolverse en el servidor mediante una lista permitida por tipo de formulario; nunca debe aceptarse una dirección enviada por el navegador.
7. [Regla: AGENTS.md] El servidor debe validar nonce, campos requeridos, email, error de subida, tamaño, extensión y MIME real antes de enviar.
8. [Evidencia: production-snapshot/pages.json:179] La persona debe aceptar el tratamiento de datos mediante una casilla obligatoria enlazada a `/nosotros/legal/politica-de-privacidad/`, siguiendo el patrón ya usado por el formulario PQR.
9. [Solicitud] Después de enviar correctamente, el formulario debe mostrar confirmación, limpiar los campos y permanecer en la misma página; ante error debe conservar los datos y mostrar un mensaje accionable.
10. [Regla: docs/runbooks/DEPLOYMENT.md] `wp_mail()` exitoso no demuestra entrega: la validación productiva debe confirmar recepción real y logs del proveedor.

## Reglas de negocio

- `trabaja_con_nosotros` se enruta únicamente a `rh@tmdual.com`.
- WPO365 conserva `consultor1@tmdual.com` como cuenta remitente configurada; la dirección de la persona se usa como `Reply-To`, no como `From`.
- Se admite un solo CV obligatorio de máximo 2 MB.
- Los archivos temporales no deben persistir en uploads, base de datos ni repositorio después de procesar la solicitud.
- El mensaje y el asunto deben identificar que se trata de una postulación general e incluir el área de interés.
- Debe existir protección básica contra automatización y abuso sin revelar si una solicitud fue descartada por el honeypot.

## Contratos

### Entrada

```json
{
  "form_type": "trabaja_con_nosotros",
  "name": "string requerido",
  "email": "email requerido",
  "phone": "string opcional",
  "city": "string opcional",
  "service": "string requerido",
  "message": "string requerido",
  "terms": "Acepto",
  "cv": "un archivo PDF, DOC o DOCX de máximo 2 MB"
}
```

### Salida

```json
{
  "success": true,
  "data": {
    "message": "Postulación enviada correctamente."
  }
}
```

Los errores usan `success=false`, código HTTP adecuado y un mensaje público sin rutas internas, secretos ni detalles del proveedor.

## Casos límite

- Archivo ausente, vacío, superior a 2 MB o con error de subida.
- Extensión permitida con MIME incompatible o archivo renombrado maliciosamente.
- Más de un archivo o nombre de archivo con caracteres no seguros.
- Nonce ausente o vencido.
- Email inválido, campos requeridos vacíos o aceptación de datos ausente.
- `form_type` desconocido o alterado para intentar cambiar el destinatario.
- Fallo de `wp_mail()` o rechazo de Microsoft Graph.
- Doble clic o reenvíos automatizados repetidos.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/functions.php`
- Nuevo módulo propio del child theme para el formulario y su JavaScript.
- Página de WordPress `Trabaja con nosotros`, ID 273.
- `production-snapshot/pages.json:139` como evidencia, no como fuente editable.
- WPO365 Microsoft Graph Mailer como transporte de `wp_mail()`.

## Criterios de aceptación

1. [Solicitud] El formulario muestra un selector obligatorio que permite elegir `.pdf`, `.doc` o `.docx` y comunica el máximo de 2 MB.
2. [Solicitud] Un PDF válido y archivos DOC/DOCX válidos pueden enviarse con todos los demás datos a `rh@tmdual.com`.
3. [Regla: AGENTS.md] Un ejecutable, imagen, archivo con MIME contradictorio, archivo mayor de 2 MB o solicitud manipulada se rechaza sin enviar correo.
4. [Solicitud] El correo recibido contiene todos los campos, el CV adjunto y `Reply-To` con el email válido de la persona.
5. [Evidencia: documentación oficial WPO365] El remitente continúa siendo la cuenta autorizada en WPO365 y el destinatario se determina por el tipo de formulario en el servidor.
6. [Solicitud] Éxito y error se anuncian de forma visible y accesible; durante el envío se evita el doble clic.
7. [Evidencia: production-snapshot/pages.json:139] La presentación actual se conserva en escritorio y móvil sin overflow horizontal.
8. [Regla: docs/runbooks/DEPLOYMENT.md] La validación productiva confirma recepción real en `rh@tmdual.com`, presencia y apertura del adjunto, Reply-To, registro WPO365 y ausencia de errores de autorización.

## Validación

- Pruebas unitarias: mapa servidor de `form_type` a destinatario, saneamiento, validación de extensión/MIME/tamaño y construcción del mensaje.
- Pruebas de integración: endpoint sin nonce, archivo ausente, archivo inválido, archivo válido, `form_type` manipulado, rate limit y fallo de `wp_mail()` sin realizar envíos externos.
- Validación manual: selector, nombre del archivo, estados de carga/éxito/error, teclado, lector de estado, escritorio y móvil.
- Validación productiva: solo con autorización y backup; enviar archivos de prueba PDF, DOC y DOCX, confirmar recepción real en `rh@tmdual.com`, adjuntos, Reply-To, logs WPO365, caché, consola y `./scripts/sync-production.sh --check`.

## Riesgos

- Los CV contienen datos personales y no deben almacenarse o registrarse fuera del correo autorizado.
- Con WPO365 gratuito, acercarse al límite de 3 MB puede fallar por la codificación del mensaje; por eso el límite funcional queda en 2 MB.
- Confiar únicamente en `accept` del navegador permitiría archivos maliciosos; la validación servidor es obligatoria.
- Usar una dirección recibida desde el formulario como destinatario convertiría el endpoint en un relay abusivo.
- Un `wp_mail()` exitoso no garantiza recepción; se requieren bandeja y logs del proveedor.

## Decisiones pendientes

- No hay decisiones funcionales pendientes para Postulación General. Los destinatarios de PQR y otros formularios se definirán en tareas separadas cuando sean solicitados.
