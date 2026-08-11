# SPEC: Bloqueo de envíos automatizados en formularios públicos

## Estado

- Terminado

## Contexto

[Solicitud] PQR, Trabaja con nosotros y Contacto deben continuar funcionando, pero los correos identificados como pruebas automatizadas deben dejar de llegar.

[Evidencia: producción `/usr/local/lsws/logs/localhost.access.log`, 2026-08-07 a 2026-08-11] Contacto recibió ráfagas de solicitudes válidas cada pocos segundos desde IP externas rotativas; después, la misma navegación automatizada alcanzó PQR mediante `admin-ajax.php`.

[Evidencia: producción `/usr/local/lsws/logs/localhost.access.log`, 2026-08-07 a 2026-08-11] Las solicitudes observadas comparten un `User-Agent` malformado que incluye comillas literales alrededor de una cadena fija de Chrome, mientras cambian de IP durante una misma ráfaga.

[Evidencia: producción `/etc/crontabs/root` y WP-CLI `wp cron event list`, 2026-08-11] No existe una tarea programada del sistema ni un evento WP-Cron que envíe PQR, postulaciones o Contacto. El cron del sistema ejecuta tareas periódicas estándar y renovación de certificados.

[Evidencia: `wp-content/themes/blocksy-child/inc/tmd-pqr.php:170`] PQR envía correo únicamente dentro de su endpoint AJAX después de validar nonce, honeypot, campos y límites.

[Evidencia: `wp-content/themes/blocksy-child/inc/tmd-job-application.php:512`] Trabaja con nosotros envía correo únicamente dentro de su endpoint AJAX después de validar nonce, honeypot, campos, límites y archivo adjunto.

## Problema

[Solicitud] Un cliente automatizado externo completa formularios públicos con datos de prueba y provoca correos reales. Los límites por IP existentes no detienen la secuencia porque el origen rota direcciones, y Contact Form 7 continúa aceptando las solicitudes de Contacto.

## Objetivo

[Solicitud] Impedir que la huella automatizada comprobada genere correos desde Contacto, PQR o Trabaja con nosotros, sin cambiar el funcionamiento observable de los envíos legítimos.

## Fuera del alcance

- [Solicitud] Desactivar, ocultar o retirar cualquiera de los tres formularios.
- [Regla: `AGENTS.md`] Desactivar WP-Cron, el cron de certificados u otras tareas legítimas de WordPress o del servidor.
- [Solicitud] Cambiar destinatarios, remitente WPO365, asunto, contenido o archivos adjuntos de solicitudes legítimas.
- [Regla: `AGENTS.md`] Modificar Contact Form 7, WordPress core, el tema padre o plugins de terceros.
- [Solicitud] Bloquear países, navegadores válidos o rangos completos de IP.
- [Solicitud] Añadir CAPTCHA, una dependencia externa o persistencia de datos en esta corrección focalizada.

## Requisitos funcionales

1. [Solicitud] Contacto, PQR y Trabaja con nosotros deben conservar su envío actual para solicitudes que no coincidan con la huella automatizada comprobada.
2. [Evidencia: producción `/usr/local/lsws/logs/localhost.access.log`, 2026-08-07 a 2026-08-11] Una solicitud cuyo `User-Agent` esté envuelto en comillas literales debe clasificarse como automatizada antes de invocar el transporte de correo.
3. [Solicitud] Una solicitud automatizada bloqueada no debe generar `wp_mail()`, correo WPO365 ni adjunto temporal persistente.
4. [Solicitud] PQR y Trabaja con nosotros deben responder de forma genérica al cliente bloqueado, sin revelar la regla antispam.
5. [Solicitud] Contact Form 7 debe usar su ciclo de envío soportado para abortar el correo de Contacto sin modificar el plugin.
6. [Regla: `AGENTS.md`] La corrección debe implementarse en la fuente canónica del child theme y limitarse a los tres formularios indicados.
7. [Solicitud] WP-Cron y las tareas programadas del servidor deben permanecer sin cambios.

## Reglas de negocio

- [Solicitud] Los destinatarios vigentes de PQR, Trabaja con nosotros y Contacto permanecen sin cambios.
- [Regla: `AGENTS.md`] No se almacenan ni registran nombres, correos, mensajes, hojas de vida ni otros datos personales para aplicar el bloqueo.
- [Evidencia: `wp-content/themes/blocksy-child/inc/tmd-pqr.php:126`] Los límites existentes de PQR se conservan como defensa adicional.
- [Evidencia: `wp-content/themes/blocksy-child/inc/tmd-job-application.php:454`] Los límites existentes de postulaciones se conservan como defensa adicional.

## Contratos

### Entrada

```json
{
  "formulario": "contacto | pqr | trabaja_con_nosotros",
  "user_agent": "cadena HTTP"
}
```

### Salida

```json
{
  "solicitud_legitima": "conserva el flujo actual",
  "huella_automatizada": "no envía correo y devuelve una respuesta genérica"
}
```

## Casos límite

- [Solicitud] Un `User-Agent` vacío no debe bloquearse únicamente por estar vacío.
- [Solicitud] Las comillas internas normales dentro de un `User-Agent` no deben activar la regla; solo una cadena completa envuelta en comillas literales.
- [Solicitud] Un navegador legítimo con la misma versión de Chrome, pero con encabezado correctamente formado, debe continuar funcionando.
- [Solicitud] Si Contact Form 7 no está activo, el child theme no debe producir un error fatal por el filtro antispam.
- [Solicitud] Una postulación bloqueada antes del procesamiento del CV no debe crear ni conservar archivos temporales.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/functions.php`
- `wp-content/themes/blocksy-child/inc/tmd-pqr.php`
- `wp-content/themes/blocksy-child/inc/tmd-job-application.php`
- `tests/test-tmd-pqr-endpoint.php`
- `tests/test-job-application-endpoint.php`
- Pruebas focalizadas nuevas para la integración antispam de Contact Form 7.

## Criterios de aceptación

1. [Solicitud] Una solicitud normal simulada sigue invocando una sola vez el envío correspondiente en Contacto, PQR y Trabaja con nosotros.
2. [Evidencia: producción `/usr/local/lsws/logs/localhost.access.log`, 2026-08-07 a 2026-08-11] La cadena automatizada observada, con comillas literales al inicio y al final, no invoca el transporte de correo en ninguno de los tres formularios.
3. [Solicitud] La versión de Chrome incluida en la huella no se bloquea cuando el encabezado está correctamente formado.
4. [Solicitud] Los endpoints bloqueados devuelven respuesta genérica y no exponen detalles de la detección.
5. [Solicitud] Una postulación bloqueada no procesa ni deja un archivo de CV temporal.
6. [Solicitud] Destinatarios, remitente, contenido, validaciones, rate limits y adjuntos de solicitudes legítimas permanecen iguales.
7. [Regla: `AGENTS.md`] Los PHP modificados superan `php -l`, las pruebas focalizadas pasan y el diff no contiene cambios ajenos.
8. [Solicitud] En producción, después de backup y despliegue autorizados, los formularios cargan con HTTP 200 y solicitudes legítimas controladas continúan disponibles.
9. [Solicitud] La validación productiva no genera correos de prueba adicionales; se comprueba mediante logs y una solicitud bloqueada controlada que no invoque entrega.
10. [Regla: `AGENTS.md`] `./scripts/sync-production.sh --check` confirma coincidencia después del despliegue.

## Validación

- Pruebas unitarias: detector de encabezado envuelto, encabezado normal, vacío y comillas internas.
- Pruebas de integración: PQR y Trabaja con nosotros bloquean antes de `wp_mail()`; Contact Form 7 aborta el correo mediante su hook soportado; los flujos normales conservan una invocación.
- Validación manual: revisar respuestas genéricas, ausencia de errores PHP y ausencia de archivos temporales al bloquear una postulación.
- Validación productiva: con autorización explícita, backup verificado, control de sincronización previo, despliegue mínimo, purga de caché, HTTP, logs sin datos personales, prueba bloqueada sin correo y control de sincronización posterior. No se enviarán formularios legítimos de prueba a bandejas reales para esta validación.

## Evidencia de cierre

- [Pruebas focalizadas, 2026-08-11] Pasaron el detector antispam, la integración soportada de Contact Form 7 y los endpoints de PQR y Trabaja con nosotros; los flujos normales simulados conservan una invocación y los bloqueados no invocan correo ni procesan el CV.
- [Validación local, 2026-08-11] Los PHP relacionados superaron `php -l` y `git diff --check` no reportó errores.
- [Producción, 2026-08-11] Se verificó el backup en `/opt/tecnimontacargas/backups/20260811-2055-form-antispam`, se desplegaron únicamente los archivos autorizados del child theme y se purgó LiteSpeed.
- [Producción, 2026-08-11] Contacto, PQR, Trabaja con nosotros y la página de baterías respondieron HTTP 200; las configuraciones públicas de PQR y postulaciones continuaron cargando.
- [Producción, 2026-08-11] Solicitudes controladas con la huella malformada recibieron respuestas genéricas antes del transporte; los logs recientes no mostraron errores fatales, excepciones no capturadas ni respuestas 500 asociadas.
- [Producción, 2026-08-11] `./scripts/sync-production.sh --check` confirmó que producción y repositorio coinciden en todas las rutas versionadas.
- [Límite deliberado] No se envió una solicitud legítima a una bandeja real ni se verificó recepción en buzón, porque el criterio 9 exige no generar correos de prueba adicionales; la conservación del flujo legítimo se comprobó mediante pruebas focalizadas y disponibilidad pública de los formularios.

## Riesgos

- La automatización puede cambiar su `User-Agent`; esta corrección elimina la campaña observada, pero no sustituye una estrategia antispam adaptativa.
- Una regla demasiado amplia podría rechazar clientes legítimos; por eso solo se acepta la huella malformada exacta y se prueban encabezados normales.
- Abortar Contact Form 7 en un hook tardío incorrecto podría mostrar un resultado engañoso; la implementación debe usar el ciclo soportado por la versión productiva verificada.

## Decisiones pendientes

- No aplica.
