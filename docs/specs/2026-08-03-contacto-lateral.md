# SPEC: Contacto lateral global

## Estado

- Terminado

## Contexto

La solicitud vigente indica que dejó de aparecer el sticky lateral izquierdo que mostraba accesos de contacto como correo y ubicación. La inspección del tema activo y del snapshot versionado no encontró el componente renderizado ni estilos asociados. El tema sí mantiene los destinos actuales de ubicación, WhatsApp, correo y LinkedIn en la página de contacto y el footer global.

El historial retirado documenta un componente denominado `SID-001`, pero se usa solo como evidencia de que existió el diseño, no como autoridad para ampliar el alcance actual.

## Problema

Las páginas públicas no muestran el acceso lateral global de contacto que el usuario espera recuperar, por lo que ubicación, WhatsApp, correo y LinkedIn solo quedan disponibles en secciones específicas como el footer o la página de contacto.

## Objetivo

Restablecer un único componente de contacto fijo en el lado izquierdo de las páginas públicas, usando los destinos vigentes ya presentes en el tema y sin interferir con la navegación ni con el contenido.

## Fuera del alcance

- Modificar el footer, el header, la página de contacto o el chatbot.
- Cambiar números, dirección, correo, redes sociales o textos comerciales existentes.
- Crear una pantalla administrativa para configurar los destinos.
- Modificar WordPress core, el tema padre o plugins de terceros.
- Desplegar el cambio a producción, purgar caché productiva o escribir datos productivos.

## Requisitos funcionales

1. [Solicitud] Mostrar una barra de contacto fija en el lado izquierdo de las páginas públicas del sitio.
2. [Solicitud] Incluir accesos identificables para ubicación, WhatsApp, correo electrónico y LinkedIn.
3. [Evidencia: content/contact-page.html:35] El acceso de ubicación debe abrir en una pestaña nueva el mapa de la sede actualmente enlazado por la página de contacto.
4. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:55] El acceso de WhatsApp debe abrir en una pestaña nueva el número actualmente publicado en el footer.
5. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:59] El acceso de correo debe usar el correo actualmente publicado en el footer mediante un enlace `mailto:`.
6. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:27] El acceso de LinkedIn debe abrir en una pestaña nueva la página de empresa actualmente enlazada en el footer.
7. [Solicitud] El componente debe permanecer disponible durante el desplazamiento vertical.
8. [Regla: AGENTS.md] El componente debe implementarse en el child theme activo y reutilizar los datos comerciales comprobados, sin inventar información.
9. [Solicitud] El componente debe mostrarse en escritorio y tablet y permanecer oculto en pantallas móviles menores de 768 px.
10. [Solicitud] Cada acceso debe comunicar su propósito mediante icono, nombre accesible y una etiqueta visible al pasar el puntero o enfocar con teclado.

## Reglas de negocio

- La marca pública debe presentarse como `Tecnimontacargas`.
- Los destinos de contacto deben coincidir con los valores vigentes del tema activo.
- Los enlaces externos deben usar `target="_blank"` y `rel="noopener noreferrer"`.
- El componente no debe tapar controles esenciales ni competir con widgets flotantes en móvil.

## Contratos

### Entrada

No aplica. El componente no recibe datos del visitante ni parámetros de URL.

### Salida

HTML semántico global con cuatro enlaces de contacto y estilos responsivos cargados desde el child theme.

## Casos límite

- En anchos menores de 768 px, la barra no debe ocupar espacio ni quedar disponible fuera del viewport.
- Con navegación por teclado, cada enlace debe recibir foco visible y revelar una etiqueta comprensible.
- Si JavaScript está deshabilitado, todos los enlaces deben seguir funcionando porque el componente no dependerá de JavaScript.
- La barra debe conservar una capa visual suficiente para ser utilizable sin superar modales o diálogos del sitio.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/functions.php`
- `wp-content/themes/blocksy-child/template-parts/`
- `wp-content/themes/blocksy-child/assets/css/`
- `wp-content/themes/blocksy-child/template-parts/tmd-footer.php`
- `content/contact-page.html`

## Criterios de aceptación

1. [Solicitud] En una página pública a 1440 px de ancho se observan cuatro accesos apilados y fijados al borde izquierdo mientras se hace scroll.
2. [Solicitud] En una página pública a 768 px de ancho la barra permanece visible y utilizable sin cubrir la navegación principal.
3. [Solicitud] En una página pública a 767 px de ancho la barra no se muestra ni ocupa espacio.
4. [Evidencia: content/contact-page.html:35] Al activar ubicación se abre el destino vigente de Google Maps en una pestaña nueva.
5. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:55] Al activar WhatsApp se abre el destino vigente del footer en una pestaña nueva.
6. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:59] Al activar correo se inicia un mensaje dirigido al correo vigente del footer.
7. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:27] Al activar LinkedIn se abre la página vigente de la empresa en una pestaña nueva.
8. [Solicitud] Los cuatro enlaces tienen nombre accesible, foco visible y etiqueta legible en interacción con puntero o teclado.
9. [Regla: AGENTS.md] El HTML generado es válido, el PHP modificado supera `php -l` y no se alteran fuentes no canónicas.
10. [Solicitud] La barra no impide usar header, contenido, footer, chatbot ni otros controles flotantes en las vistas verificadas.

## Validación

- Pruebas unitarias: No aplica; el componente no contiene lógica de transformación o estado.
- Pruebas de integración: harness PHP focalizado superado para los hooks `wp_enqueue_scripts` y `wp_body_open`, el asset con dependencia de Tabler y la plantilla con cuatro enlaces.
- Validación manual: Playwright superado a 1440 px, 768 px y 767 px para scroll, foco, etiquetas y responsive; comprobado con los assets exactos en una vista aislada y mediante inyección temporal read-only sobre home, catálogo y contacto, todas con HTTP 200.
- Validación productiva: fuera del alcance sin autorización explícita de despliegue; si posteriormente se autoriza, verificar HTTP, navegador, caché y `./scripts/sync-production.sh --check` según runbooks.

## Riesgos

- Una capa visual incorrecta puede dejar la barra detrás del contenido o por encima de modales.
- El borde izquierdo puede coincidir con controles propios del navegador o con contenido de ancho completo.
- Los destinos están repetidos en distintos archivos; una actualización futura del footer podría no propagarse automáticamente al componente si no se centralizan en otra tarea.

## Decisiones pendientes

- No hay decisiones funcionales pendientes para la implementación local. La aprobación de este SPEC no autoriza commit, push ni despliegue a producción.
