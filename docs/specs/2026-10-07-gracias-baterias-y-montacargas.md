# SPEC: Páginas de agradecimiento para baterías y montacargas

## Estado

- Aprobado

## Contexto

- [Evidencia: docs/specs/2026-10-05-landing-alquiler-v2.md:48] La página de alquiler publicada es la 1558 y usa el formulario Contact Form 7 1556.
- [Evidencia: docs/specs/2026-10-04-landing-montacargas-baterias.md:28] La página de baterías publicada es la 1559 y usa el formulario Contact Form 7 1557.
- [Evidencia: wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php:272-274] El renderer de las landings inserta el shortcode Contact Form 7 dentro de la tarjeta de cotización.
- [Solicitud] El usuario pide una página de agradecimiento para cada flujo y da como ejemplos “gracias baterías” y “graciasmontacargas”.

## Problema

- [Solicitud] Los dos formularios comerciales deben terminar en destinos de agradecimiento distintos para que cada visitante vea una confirmación relacionada con su solicitud.

## Objetivo

- [Solicitud] Definir dos páginas de agradecimiento distintas y relacionar cada formulario con el destino de su categoría: baterías o montacargas.

## Aprobación de implementación

- [Solicitud: aprobada por el usuario el 2026-10-07] El usuario indicó “pues implementa el spec para las paginas de agradecimiento” y después confirmó “si continua” al revisar el plan. Esto autorizó inicialmente la implementación local descrita aquí.
- [Solicitud de producción: 2026-10-07] El usuario pidió “haz el deploy”. Esta instrucción posterior autoriza publicar este cambio y crear las dos páginas persistidas, una vez satisfechos los gates de producción, backup, revisión, deriva y rollback de los runbooks.

## Fuera del alcance

- [Evidencia: docs/specs/2026-10-05-landing-alquiler-v2.md:62] Cambiar campos, consentimiento, antispam, destinatarios o contenido del correo de los formularios.
- [Evidencia: docs/specs/2026-10-04-landing-montacargas-baterias.md:14-16] Cambiar la política de privacidad o los campos comerciales del formulario de baterías.
- [Regla: docs/domain/SEO.md] Sustituir Rank Math como autoridad para canonical, robots, metadatos o sitemap.
- Modificar páginas, formularios o redirecciones comerciales fuera de estos dos flujos.

## Requisitos funcionales

1. [Solicitud] Crear dos destinos de agradecimiento diferentes: uno para el formulario de baterías 1557 y otro para el formulario de montacargas 1556.
2. [Solicitud] Después de un envío exitoso del formulario 1557, mostrar la página de agradecimiento de baterías; después de un envío exitoso del formulario 1556, mostrar la página de agradecimiento de montacargas.
3. [Solicitud] Cada destino debe tener una identificación y URL propias. Los nombres de ejemplo recibidos son “gracias baterías” y “graciasmontacargas”; su asignación exacta a título visible y slug se registra en DEC-01 y DEC-02.
4. [Regla: docs/domain/SEO.md] Rank Math conserva la autoridad de canonical, robots y sitemap de las páginas nuevas.
5. [Evidencia: docs/specs/2026-10-05-landing-alquiler-v2.md:62] [Evidencia: docs/specs/2026-10-04-landing-montacargas-baterias.md:14-16] La implementación no modifica los campos, consentimiento, controles antispam ni configuración de correo existentes.

## Reglas de negocio

- [Regla: docs/domain/SEO.md] El contenido sin valor de búsqueda no se agrega al sitemap indexable; la indexación y los robots de las páginas de agradecimiento se deben resolver antes de publicar.
- [Evidencia: docs/architecture/REPO_MAP.md:174-185] Contact Form 7 conserva el flujo existente de captura y correo; la confirmación no reemplaza ni altera ese proceso.

## Contratos

### Entrada

- Envío exitoso asociado al formulario Contact Form 7 1557 o 1556.
- Los errores de validación, antispam o envío deben permanecer asociados al formulario que los produjo.

### Salida

- El formulario 1557 conduce al destino de agradecimiento de baterías.
- El formulario 1556 conduce al destino de agradecimiento de montacargas.
- La URL de salida no contiene los valores enviados por el visitante.

## Casos límite

- Un envío rechazado por validación o antispam no muestra una confirmación de éxito ni conduce a una página de agradecimiento.
- Un error de transporte o del servidor conserva el aviso de error del formulario y no conduce a un destino de éxito.
- Un envío nunca cruza las páginas de agradecimiento de baterías y montacargas.

## Archivos o módulos relacionados

- wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php
- scripts/create-commercial-landing-pages.php
- Formulario Contact Form 7 1556 y página 1558.
- Formulario Contact Form 7 1557 y página 1559.
- Rank Math para robots, canonical y sitemap.

## Criterios de aceptación

1. [Solicitud] Existen dos páginas de agradecimiento con nombres y URL distintos, según los slugs y textos definidos en DEC-01 y DEC-02.
2. [Solicitud] Un envío exitoso de CF7 1557 muestra la página de baterías; un envío exitoso de CF7 1556 muestra la página de montacargas.
3. [Solicitud] Los errores de validación, antispam o envío permanecen en el flujo del formulario y no generan una confirmación falsa.
4. [Evidencia: docs/specs/2026-10-05-landing-alquiler-v2.md:62] [Evidencia: docs/specs/2026-10-04-landing-montacargas-baterias.md:14-16] Los campos, consentimiento, antispam, destinatarios y contenido de correo se conservan.
5. [Regla: docs/domain/SEO.md] Las páginas tienen canonical y robots coherentes con la decisión SEO y las páginas no indexables no aparecen en el sitemap.
6. [Regla: AGENTS.md] La publicación de páginas o cambios persistentes en producción requiere backup verificado y validación pública posterior.

## Validación

- Pruebas unitarias: cobertura focal para asignación formulario-destino y casos de error; ejecutar si se modifica código.
- Pruebas de integración: enviar ambos formularios en un entorno de prueba y confirmar destinos correctos, manejo de errores y ausencia de datos personales en la URL.
- Validación manual: revisar cada página en escritorio y móvil, además de estados exitosos y fallidos de los formularios.
- Validación productiva: Pendiente de autorización separada; si se autoriza, realizar backup, purga de caché, HTTP/HTML, robots, canonical y sitemap.

## Riesgos

- Una asociación equivocada llevaría solicitudes a una página de agradecimiento de otra categoría.
- La indexación de páginas de confirmación puede generar URLs de bajo valor en resultados de búsqueda.
- Un redirect prematuro podría ocultar errores de validación o antispam y comunicar falsamente que la solicitud fue recibida.

## Decisiones

- DEC-01: Usar los slugs `/gracias-baterias/` y `/gracias-montacargas/`.
- DEC-02: Mostrar los títulos “Gracias por tu solicitud de baterías” y “Gracias por tu solicitud de montacargas”, con el mensaje compartido “Hemos recibido tu solicitud. Gracias por contactar a Tecnimontacargas.”
- DEC-03: Guardar robots Rank Math `noindex, follow`; las páginas no deben aparecer en el sitemap.
- DEC-04: No añadir CTA. Conservar el header y footer globales y ocultar únicamente el título duplicado de la plantilla.

## Decisiones pendientes

- Ninguna. Decisiones resueltas según el plan aprobado por el usuario el 2026-10-07.
