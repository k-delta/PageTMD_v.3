# SPEC: Recepción de propuestas empresariales con adjuntos

## Estado

- En desarrollo

## Contexto

[Solicitud] En el selector `Servicio de interés` de la sección Contáctenos aparece la opción `Alianzas`, aunque las propuestas de alianza y las solicitudes de empresas interesadas en ser proveedor necesitan adjuntar documentos comerciales.

[Evidencia: `production-snapshot/pages.json:85`] La copia de auditoría de la página Contacto, ID 57, inserta el formulario de Contact Form 7 ID 14; el contenido y la configuración activa del formulario viven en MariaDB y no en el HTML versionado del tema.

[Evidencia: `production-snapshot/pages.json:135`] La página Alianzas, ID 275, dirige actualmente sus acciones `Presentar una propuesta` y `Contactar a Tecni Montacargas` hacia `/nosotros/contacto/`.

[Evidencia: `production-snapshot/pages.json:345`] La página Quiero ser proveedor, ID 793, dirige actualmente su acción `Contactar` hacia `/nosotros/contacto/` y no contiene un canal propio para adjuntar documentación.

[Evidencia: `wp-content/themes/blocksy-child/inc/tmd-job-application.php:357`] El child theme ya contiene validación servidor para adjuntos PDF, DOC y DOCX, incluyendo tamaño, extensión, MIME real y estructura del archivo, además de borrado del archivo temporal después del intento de correo.

[Evidencia: `wp-content/themes/blocksy-child/inc/tmd-form-antispam.php:31`] El formulario de Contacto ID 14 dispone de una protección antispam focalizada que no debe perderse ni ampliarse accidentalmente a formularios ajenos.

## Problema

[Solicitud] Las empresas interesadas en proponer una alianza o registrarse como proveedor no tienen un formulario específico donde puedan enviar sus datos y adjuntar brochures u otros documentos; usar el formulario general de Contacto mezcla estas solicitudes con servicios comerciales ordinarios.

## Objetivo

[Solicitud] Separar las solicitudes de alianzas y proveedores del formulario general de Contacto, permitir el envío seguro de documentación empresarial y entregar cada solicitud válida al destinatario fijo `gerencia@gmail.com` mediante el correo ya configurado en WordPress.

## Fuera del alcance

- [Solicitud] Cambiar las demás opciones, el destinatario o el funcionamiento de solicitudes legítimas del formulario general de Contacto.
- [Regla: `AGENTS.md`] Modificar Contact Form 7, WPO365, WordPress core, el tema padre o plugins de terceros.
- [Regla: `AGENTS.md`] Actualizar WordPress, temas, plugins o dependencias.
- [Regla: `AGENTS.md`] Guardar adjuntos o datos personales en Git, logs, la biblioteca de medios, una base de datos o un servicio externo.
- [Regla: `AGENTS.md`] Hacer commit, push, despliegue, escritura productiva, backup o purga de caché sin autorización explícita posterior.
- [Solicitud] Cambiar el contenido comercial general, navegación, SEO o estructura de las páginas Alianzas y Quiero ser proveedor más allá de integrar su canal de envío.

## Requisitos funcionales

1. [Solicitud] El selector `Servicio de interés` del formulario general de Contacto no debe ofrecer `Alianzas`.
2. [Solicitud] La página `/nosotros/alianzas/` debe ofrecer un formulario propio para presentar una propuesta de alianza sin redirigir a la persona al formulario general de Contacto.
3. [Solicitud] La página `/nosotros/proveedores/` debe ofrecer un formulario propio para presentar una empresa como posible proveedor sin redirigir a la persona al formulario general de Contacto.
4. [Solicitud] Cada formulario debe identificar en el servidor su propósito inmutable como `alianza` o `proveedor`; el navegador no debe poder elegir destinatarios.
5. [Solicitud] Una solicitud válida debe enviar los campos definidos en `DEC-01` y los adjuntos permitidos por `DEC-02`, `DEC-03` y `DEC-04` al único destinatario `gerencia@gmail.com`.
6. [Solicitud] Los formularios deben permitir adjuntar brochures, documentos PDF y los formatos de imagen que se aprueben en `DEC-03`, con el tamaño máximo aprobado en `DEC-04` comunicado junto al selector de archivos.
7. [Regla: `AGENTS.md`] El servidor debe validar nonce, propósito permitido, campos requeridos, email, aceptación de tratamiento de datos, error de subida, cantidad, tamaño, extensión y MIME real antes de invocar el correo.
8. [Evidencia: `wp-content/themes/blocksy-child/inc/tmd-job-application.php:546`] Los adjuntos deben existir solo de forma temporal durante el procesamiento y eliminarse tanto si el correo se acepta como si falla o lanza una excepción.
9. [Evidencia: `wp-content/themes/blocksy-child/inc/tmd-form-antispam.php:31`] Los nuevos formularios deben aplicar protección focalizada contra automatización y abuso sin modificar el comportamiento legítimo del formulario de Contacto.
10. [Solicitud] Después de un envío aceptado, el formulario debe mostrar confirmación accesible, limpiar sus campos y permanecer en la misma página; ante un error debe conservar los datos no sensibles y mostrar un mensaje accionable.
11. [Regla: `AGENTS.md`] Un resultado positivo de `wp_mail()` debe comunicarse como solicitud procesada, no como recepción confirmada en la bandeja de gerencia.
12. [Regla: `AGENTS.md`] Ambos formularios deben conservar una presentación usable, sin recortes ni desplazamiento horizontal, en escritorio y móvil.

## Reglas de negocio

- [Solicitud] `alianza` y `proveedor` se enrutan únicamente a `gerencia@gmail.com`; esta dirección se resuelve en el servidor y nunca se recibe desde el navegador.
- [Solicitud] Una propuesta de alianza y una solicitud de proveedor deben llegar identificadas de forma diferente en el asunto y en el contenido del correo.
- [Evidencia: `docs/specs/2026-08-03-postulacion-cv-correo.md:46`] El correo de la persona se usa como `Reply-To`, no como `From`; el transporte conserva la cuenta remitente ya configurada en WPO365.
- [Regla: `AGENTS.md`] Los adjuntos y datos de las empresas no deben exponerse en respuestas públicas, rutas, logs o mensajes de error.
- [Solicitud] Presentar la información no garantiza aceptación, registro como proveedor, reunión, alianza, exclusividad ni relación comercial.

## Contratos

### Entrada

```json
{
  "form_type": "alianza | proveedor",
  "campos": "DEC-01",
  "terms": "aceptación obligatoria",
  "attachments": "DEC-02 archivos en formatos DEC-03 y máximo DEC-04"
}
```

### Salida

```json
{
  "success": true,
  "data": {
    "message": "Solicitud procesada correctamente."
  }
}
```

[Regla: `AGENTS.md`] Las respuestas de error deben usar `success=false`, un código HTTP adecuado y un mensaje público que no revele rutas internas, datos personales, destinatarios, secretos ni detalles del proveedor de correo.

## Casos límite

- [Solicitud] La opción `Alianzas` difiere solo en mayúsculas, minúsculas, espacios o acentos dentro de la configuración activa de Contacto.
- [Solicitud] Un CTA histórico de Alianzas o Proveedores todavía dirige a `/nosotros/contacto/` después de incorporar el formulario propio.
- [Regla: `AGENTS.md`] Propósito ausente, desconocido o manipulado para intentar cambiar el destinatario.
- [Regla: `AGENTS.md`] Nonce ausente o vencido, email inválido, campo requerido vacío o aceptación de datos ausente.
- [Regla: `AGENTS.md`] Archivo ausente cuando resulte obligatorio según `DEC-02`, vacío, truncado, con error de subida, con extensión permitida pero MIME incompatible o renombrado maliciosamente.
- [Regla: `AGENTS.md`] Archivo de imagen con contenido activo o estructura no permitida.
- [Regla: `AGENTS.md`] Cantidad de archivos o suma de tamaños superior a lo aprobado.
- [Evidencia: `wp-content/themes/blocksy-child/inc/tmd-job-application.php:546`] Fallo o excepción de `wp_mail()` después de preparar uno o más adjuntos temporales.
- [Regla: `AGENTS.md`] Doble clic, reenvío repetido, automatización o imposibilidad de adquirir el control de rate limit.
- [Regla: `AGENTS.md`] Contact Form 7 o WPO365 no están disponibles; el child theme no debe producir un error fatal.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/functions.php`
- `wp-content/themes/blocksy-child/inc/tmd-partnerships.php`
- `wp-content/themes/blocksy-child/inc/tmd-form-antispam.php`
- `wp-content/themes/blocksy-child/inc/tmd-job-application.php` como patrón de seguridad reutilizable, no como flujo que deba cambiarse.
- Nuevo módulo, JavaScript y estilos focalizados del child theme para solicitudes empresariales.
- Página de WordPress Contacto, ID 57, y formulario Contact Form 7 ID 14.
- Página de WordPress Alianzas, ID 275.
- Página de WordPress Quiero ser proveedor, ID 793.
- `production-snapshot/pages.json` como evidencia de auditoría, no como fuente editable.
- Pruebas focalizadas nuevas para validación, endpoint, adjuntos y comportamiento del navegador.

## Criterios de aceptación

1. [Solicitud] El formulario general de Contacto conserva sus opciones y comportamiento actuales salvo que `Alianzas` deja de aparecer y no puede enviarse como servicio permitido.
2. [Solicitud] Alianzas y Quiero ser proveedor muestran cada uno su formulario en la página correspondiente y sus CTA ya no desembocan en el formulario general de Contacto.
3. [Solicitud] Una solicitud válida de cada propósito invoca una sola vez el transporte de correo con `To: gerencia@gmail.com`, `Reply-To` de la persona y asunto que distingue alianza de proveedor.
4. [Solicitud] El correo construido contiene los campos aprobados y todos los adjuntos válidos admitidos por `DEC-02`, `DEC-03` y `DEC-04`.
5. [Regla: `AGENTS.md`] Propósito, destinatario, nonce, campos, aceptación, cantidad, tamaño, extensión o MIME inválidos se rechazan antes de invocar el transporte de correo.
6. [Regla: `AGENTS.md`] Ningún adjunto temporal persiste después de éxito, fallo, excepción o bloqueo antispam.
7. [Solicitud] El estado de envío es accesible, evita dobles envíos y conserva los datos no sensibles ante un error recuperable.
8. [Solicitud] El formulario general de Contacto, PQR y Trabaja con nosotros conservan destinatarios, remitente, contenido, adjuntos, validaciones y protección antispam vigentes.
9. [Regla: `AGENTS.md`] Los PHP modificados superan `php -l`; las pruebas focalizadas y comprobaciones de JavaScript/CSS pasan; `git diff --check` no reporta errores ni cambios ajenos.
10. [Regla: `AGENTS.md`] La validación manual comprueba teclado, foco, mensajes, selección y eliminación de archivos, escritorio y móvil sin errores de consola, recortes ni desplazamiento horizontal.
11. [Regla: `docs/runbooks/DEPLOYMENT.md:9`] La actualización de contenido persistente se realiza en MariaDB mediante un procedimiento versionado, idempotente y con backup verificado, separado del despliegue de código.
12. [Regla: `AGENTS.md`] La validación productiva autorizada confirma recepción real en `gerencia@gmail.com`, presencia y apertura de cada tipo de adjunto aprobado, `Reply-To`, registro WPO365, ausencia de errores y sincronización final.

## Validación

- Pruebas unitarias: mapa fijo de propósitos y destinatario; saneamiento de campos; límites de longitud; validación de cantidad, tamaño, extensión, MIME y estructura de cada formato aprobado; construcción diferenciada de mensajes; borrado temporal.
- Pruebas de integración: endpoint público y autenticado; nonce válido e inválido; propósito manipulado; adjuntos ausentes, inválidos y válidos; múltiples adjuntos según `DEC-02`; antispam; rate limit; éxito, fallo y excepción simulada de `wp_mail()` sin efectuar entregas externas; regresión de Contacto, PQR y Trabaja con nosotros.
- Validación manual: ambos formularios y CTA en las páginas 275 y 793; ausencia de `Alianzas` en Contacto; ayuda sobre formatos y tamaño; teclado, foco, estados de carga/éxito/error y responsive en `1440x900` y `390x844`.
- Validación productiva: solo con aprobación explícita adicional y backup verificado; ejecutar control de sincronización previo, desplegar únicamente código aprobado, aplicar el cambio idempotente de contenido/configuración, purgar caché y probar HTTP/navegador. Enviar archivos inocuos de cada formato aprobado, confirmar recepción y apertura real en `gerencia@gmail.com`, `Reply-To`, logs WPO365 sin datos personales ni errores, ausencia de temporales y `./scripts/sync-production.sh --check` final.

## Riesgos

- [Evidencia: `docs/runbooks/DEPLOYMENT.md:9`] La configuración activa del formulario y el contenido de las páginas viven en MariaDB; desplegar solo archivos del tema no aplica esos cambios de contenido.
- [Evidencia: `docs/specs/2026-08-03-postulacion-cv-correo.md:123`] Los adjuntos aumentan el tamaño efectivo del mensaje y pueden superar el límite del transporte aun cuando el archivo original cumpla el límite visible.
- [Regla: `AGENTS.md`] Confiar solo en `accept`, extensión o MIME declarado por el navegador permitiría contenido malicioso.
- [Regla: `AGENTS.md`] Permitir un destinatario enviado por el navegador convertiría el endpoint en una superficie de abuso.
- [Regla: `AGENTS.md`] Brochures y documentos empresariales pueden contener información personal o confidencial y no deben persistirse fuera del correo autorizado.
- [Regla: `AGENTS.md`] `wp_mail()` exitoso no garantiza recepción ni apertura del adjunto; la bandeja y los logs del proveedor son evidencia separada.
- [Evidencia: `wp-content/themes/blocksy-child/inc/tmd-form-antispam.php:31`] Un formulario público nuevo sin protección equivalente puede reabrir la automatización ya observada en Contacto.

## Evidencia local

- [Validación local: 2026-08-27] Pasaron las pruebas focalizadas de campos, límites, destinatario fijo, acciones separadas por propósito, formatos reales, archivos múltiples, MIME/estructura, PDF/DOCX activos, imágenes con contenido añadido, rate limit, antispam, correo, excepciones, staging parcial, limpieza temporal, Contact Form 7, CTAs, JavaScript y comando persistente idempotente.
- [Regresión local: 2026-08-27] Pasaron las suites existentes de PQR y Trabaja con nosotros, incluyendo PHP y JavaScript.
- [Validación estática: 2026-08-27] Los PHP modificados y añadidos superaron `php -l`; los JavaScript superaron `node --check`; `git diff --check` no reportó errores.
- [Context7, Microsoft Graph: 2026-08-27] Los adjuntos directos deben permanecer por debajo de 3 MB y las escrituras Graph tienen un límite de 4 MB; por ello el contrato usa 2.5 MB por archivo y 2.5 MB acumulados.
- [Context7, Contact Form 7: 2026-08-27] El procedimiento persistente usa `wpcf7_save_contact_form()` y vuelve a cargar el formulario con `wpcf7_contact_form()` para verificar el postestado.
- [Pendiente] No se ejecutó el dry-run ni la escritura en MariaDB productiva, no se desplegó código, no se purgó caché y no se comprobó recepción real en `gerencia@gmail.com`; el SPEC no puede pasar a `Terminado` hasta completar esas validaciones con autorización y backup verificado.

## Decisiones pendientes

- No aplica.

## Decisiones aprobadas

- [Solicitud aprobada: 2026-08-27] `DEC-01`: empresa, nombre de contacto, email, teléfono, ciudad, cobertura, descripción de la propuesta o portafolio y aceptación de privacidad son obligatorios; NIT, cargo y sitio web son opcionales.
- [Solicitud aprobada: 2026-08-27] `DEC-02`: cada formulario exige entre uno y tres archivos.
- [Solicitud aprobada: 2026-08-27] `DEC-03`: se admiten únicamente `.pdf`, `.docx`, `.jpg`, `.jpeg`, `.png` y `.webp`.
- [Solicitud aprobada: 2026-08-27] `DEC-04`: cada archivo puede pesar máximo 2.5 MB y la suma de todos los adjuntos también puede pesar máximo 2.5 MB. La decisión conserva margen frente al límite documentado de Microsoft Graph para adjuntos directos menores de 3 MB y solicitudes de escritura de 4 MB, considerando la codificación del mensaje.
- [Solicitud aprobada: 2026-08-27] `DEC-05`: el destinatario literal es `gerencia@gmail.com`; el flujo PQR existente hacia `gerencia@tmdual.com` permanece sin cambios.
