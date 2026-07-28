# Handoff operativo: TecniMontacargas

Última verificación en servidor y sitio público: 28 de julio de 2026, zona horaria America/Bogota.

Este documento está preparado para entregárselo a otra IA y continuar el trabajo sin reconstruir el contexto. Los datos sensibles no están incluidos: no copiar contraseñas, tokens de Cloudflare, credenciales de MariaDB ni secretos de Firebase dentro de prompts, commits o salidas de terminal.

## 1. Objetivo y forma de trabajo

El sitio productivo es:

- Principal: `https://tecnimontacargas.com`
- Alias: `https://www.tecnimontacargas.com` (redirige al dominio principal)

El sitio anterior y su VPS deben permanecer intactos. No deben tratarse como destino de despliegue ni modificarse salvo autorización explícita.

Forma de trabajo esperada:

1. Inspeccionar el punto exacto solicitado.
2. Hacer backup antes de modificar producción.
3. Aplicar cambios pequeños; no reemplazar archivos completos si basta un parche.
4. Validar PHP/JS, limpiar caché y comprobar HTTP.
5. Validar visualmente en escritorio y móvil con navegador/Playwright.
6. No actualizar plugins, temas o WordPress dentro de una tarea de diseño sin autorización.
7. No imprimir secretos en la terminal ni guardarlos en el repositorio.

## 2. Infraestructura productiva

- Proveedor: Vultr.
- Región: Miami.
- VPS/IP: `149.28.97.249`.
- Sistema: Alpine Linux.
- Recursos aprox.: 1 vCPU, 2 GB RAM, 5.3 GB de swap y disco de 52 GB.
- Uso observado del disco: 11 GB de 52 GB (21%).
- Docker se ejecuta mediante OpenRC y está habilitado.
- Directorio del stack: `/opt/tecnimontacargas`.
- Compose: `/opt/tecnimontacargas/docker-compose.prod.yml`.
- Los volúmenes son externos y persistentes.

Contenedores:

- `tmd_ols_wordpress`: OpenLiteSpeed + WordPress; expone 80/443.
- `tmd_db`: MariaDB 10.11; solo red Docker.
- `tmd_phpmyadmin`: phpMyAdmin; enlazado únicamente a `127.0.0.1:8081`.
- Administración OpenLiteSpeed: únicamente `127.0.0.1:7080`.

Rutas dentro del contenedor WordPress:

```bash
export WP_CONTAINER=tmd_ols_wordpress
export WP_PATH=/var/www/vhosts/localhost/html
```

Tema activo:

```text
wp-content/themes/blocksy-child
```

Archivos productivos especialmente importantes:

```text
wp-content/themes/blocksy-child/functions.php
wp-content/themes/blocksy-child/template-parts/tmd-header.php
wp-content/themes/blocksy-child/template-parts/tmd-footer.php
wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js
wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css
wp-content/themes/blocksy-child/inc/tmd-inventory-api.php
wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js
wp-content/themes/blocksy-child/assets/css/tmd-inventory-api.css
wp-content/themes/blocksy-child/inc/tmd-blog.php
wp-content/themes/blocksy-child/inc/tmd-brand-carousel.php
wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php
wp-content/themes/blocksy-child/inc/tmd-energy-structure.php
wp-content/themes/blocksy-child/inc/tmd-maintenance.php
wp-content/themes/blocksy-child/inc/tmd-partnerships.php
wp-content/themes/blocksy-child/inc/tmd-about.php
wp-content/themes/blocksy-child/single-tmd_equipo.php
wp-content/themes/blocksy-child/single-tmd_energia.php
wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php
wp-content/plugins/tm-popup-bienvenida/tm-popup-bienvenida.php
wp-content/plugins/tm-popup-bienvenida/assets/tm-popup.js
wp-content/plugins/tm-popup-bienvenida/assets/tm-popup.css
```

### DNS y TLS

- Apex A: `tecnimontacargas.com -> 149.28.97.249`.
- `www`: CNAME a `tecnimontacargas.com`.
- Certificado Let's Encrypt para el dominio principal y `www`.
- Certificado observado: válido del 18 de julio de 2026 al 16 de octubre de 2026.
- Renovación diaria: `/etc/periodic/daily/certbot-renew-tmd`.
- Vultr Automatic Backups quedó activado.

## 3. Estado de WordPress

- WordPress: `7.0.2`.
- Tema activo: `blocksy-child 1.0.0`.
- Tema padre: `blocksy 2.1.50`.
- Base de datos: prefijo `SERVMASK_PREFIX_`.
- `home` y `siteurl`: `https://tecnimontacargas.com`.

Contenido publicado observado:

- 44 páginas publicadas y 3 borradores.
- 6 artículos de blog.
- 6 entradas históricas `tmd_equipo`.
- 2 entradas históricas `tmd_energia`.
- 0 productos WooCommerce publicados; existe 1 producto en borrador.
- 107 revisiones conservadas después de la depuración; `WP_POST_REVISIONS` quedó limitado a 10 por contenido.
- 7 usuarios: 1 administrador y 6 clientes.

Los CPT ya no representan el inventario completo. Los catálogos visibles de Equipos y Energía se generan con datos en vivo/cacheados de Firebase.

### Plugins activos

Quedaron 18 plugins activos, todos con una función comprobada en producción:

```text
advanced-custom-fields 6.8.6
blocksy-companion 2.1.50
contact-form-7 6.1.6
email-templates 1.5.14
flamingo 2.6.3
duracelltomi-google-tag-manager 1.22.4
kadence-blocks 3.7.8.1
litespeed-cache 7.8.1
seo-by-rank-math 1.0.275
shortpixel-image-optimiser 6.5.5
tm-chatbot-fase1 1.0.0
tm-equipos-destacados-v2 2.2.0
tm-popup-bienvenida 1.2.2
tm-quiz-equipo-ideal 3.0.0
woocommerce 10.9.4
wordfence 8.2.2
wpo365-msgraphmailer 5.10
wps-hide-login 1.9.18
```

No quedaron plugins instalados e inactivos.

Se retiraron el 28 de julio de 2026 porque no participaban en el flujo productivo o duplicaban funciones vigentes:

```text
code-snippets
filter-everything
string-locator
woocommerce-mercadopago
```

No había actualizaciones pendientes reportadas por WP-CLI durante la verificación. Los plugins de mantenimiento del marketplace conservan en su mayoría la autoactualización habilitada; Blocksy Companion, Rank Math, WPO365 y los plugins propios la tienen deshabilitada. No actualizar plugins, temas o WordPress como parte de otra tarea sin backup y prueba de regresión.

### Endurecimiento y cuentas

- `WP_POST_REVISIONS=10`.
- `AUTOSAVE_INTERVAL=120`.
- `FORCE_SSL_ADMIN=true`.
- `DISALLOW_FILE_EDIT=true`: bloquea la edición de PHP desde el administrador, no la edición de páginas/bloques.
- `users_can_register=0`: el registro general de WordPress está cerrado.
- `woocommerce_enable_myaccount_registration=yes`: los visitantes sí pueden crear una cuenta desde `/mi-cuenta/` o desde el popup.
- Un usuario autenticado ve su cuenta; no vuelve a mostrarse un formulario de registro.

## 4. Páginas y rutas principales

Comprobadas con HTTP 200:

```text
/
/equipos/
/energia/
/encuentra-tu-equipo/
/nosotros/
/nosotros/contacto/
/nosotros/blog/
/nosotros/proveedores/
/nosotros/alianzas/
/nosotros/quienes-somos/
/nosotros/trabaja-con-nosotros/
/energia/baterias/
/energia/cargadores/
/energia/bms/
/mantenimiento/
/mantenimiento/mantenimiento-preventivo/
/mantenimiento/mantenimiento-correctivo/
/equipos/tipos/
/equipos/tipos/contrabalanceados/
/tienda/
/carrito/
```

`/repuestos/` devolvió HTTP 404. La tienda y el carrito responden 200, pero no hay productos publicados y MercadoPago ya no está instalado; no considerar terminado el e-commerce.

IDs relevantes:

```text
47   Inicio
49   Equipos
51   Repuestos
53   Encuentra tu equipo
55   Nosotros
57   Contacto
63   Energía
253  Baterías
255  Cargadores
273  Trabaja con nosotros
275  Alianzas
278  Quiénes somos
281  Blog
284  PQR
288  Mantenimiento preventivo
290  Mantenimiento correctivo
357  Legal
358  Política de privacidad
359  Política SG-SST
360  Política de calidad
401  Plomo
506  Mantenimiento
792  BMS bajo Energía
793  Quiero ser proveedor bajo Nosotros
818  Estibadores y Apiladores
820  Estibadores eléctricos
822  Reach / Retráctiles
824  Pantógrafo sencillo
825  Pantógrafo doble profundidad
826  Tomapedidos
827  Tomapedidos de alto nivel
829  Eléctricos de 3 ruedas
830  Eléctricos de 4 ruedas
```

Hay otra página BMS con ID 512 en la raíz. La URL antigua `/bms/` redirige 301 a la canónica `/energia/bms/` (ID 792), y la página 512 está excluida del sitemap de Rank Math. No eliminarla sin verificar referencias y pedir autorización.

La antigua página Repuestos no está publicada y `/repuestos/` devuelve 404. El elemento “Repuestos” fue eliminado deliberadamente del header.

## 5. Header y mega menú

Fuente productiva:

```text
wp-content/themes/blocksy-child/template-parts/tmd-header.php
wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js
wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css
```

Estado actual:

- Logo: `https://tecnimontacargas.com/wp-content/uploads/2026/07/logo-blanco.png`.
- Menú visible: `INICIO`, `EQUIPOS`, `ENERGÍA`, `SERVICIOS`, `NOSOTROS`.
- `REPUESTOS` no aparece.
- En escritorio la navegación está centrada entre logo e iconos.
- Los títulos de las tarjetas del mega menú son enlaces.
- Los enlaces de hijos apuntan a páginas reales.
- El menú se abre con hover/foco/clic.
- No existe temporizador de cierre automático.
- Se cierra al pulsar fuera del menú o con Escape.
- El panel permanece abierto mientras el usuario interactúa dentro.

Contenido de Equipos:

- Estibadores y Apiladores.
- Reach / Retráctiles.
- Tomapedidos.
- Contrabalanceados.
- La sección Manlift fue retirada.

Subcategorías visibles:

- Estibadores manuales, Estibadores eléctricos y Apiladores eléctricos.
- Retráctiles de mástil móvil, Pantógrafo sencillo y Pantógrafo doble profundidad.
- Tomapedidos de alto nivel.
- Eléctricos de 3 ruedas y Eléctricos de 4 ruedas.

Los nombres largos anteriores se conservan únicamente como explicación editorial dentro de las páginas de cada familia, no como título del menú o de los enlaces principales.

Contenido de Energía:

- Baterías de Plomo.
- BMS.
- Cargadores.
- Baterías de Litio fue retirada.
- El hijo `Para LiFePO4` fue retirado de Cargadores.

Contenido Nosotros:

- Compañía.
- Socios & Atención.
- Legal.
- `Quiero ser proveedor` enlaza a `/nosotros/proveedores/`.

No volver a introducir Repuestos, Manlift, Baterías de Litio ni Para LiFePO4 salvo petición explícita.

## 6. Inicio

### Hero

- Fondo de video local:
  `https://tecnimontacargas.com/wp-content/uploads/2026/07/WhatsApp-Video-2026-07-08-at-16.24.56.mp4`
- El bloque de Kadence tiene `loop: false` y video silenciado.
- La intención aprobada es reproducir una vez y permanecer en el último cuadro/imagen estática.
- El texto y los botones se redujeron y compactaron para no ocupar todo el ancho.
- Botón principal azul con flecha; botón secundario transparente con borde.
- Se eliminaron franjas blancas innecesarias alrededor del hero.

Antes de cambiar este bloque, validar el comportamiento real en Chrome/Safari y móvil, porque gran parte de sus atributos vive serializada en `post_content` de la página 47.

### Carrusel de marcas

- Archivo: `inc/tmd-brand-carousel.php`.
- Assets: `assets/js/tmd-brand-carousel.js` y `assets/css/tmd-brand-carousel.css`.
- Muestra exactamente 3 logos a la vez en escritorio.
- Se desplaza de 3 en 3 mediante flechas.
- Marcas cargadas: Jungheinrich, Crown, Zowell, Toyota, Hyster y Yale.

### Equipos destacados

- Shortcode en Inicio: `[tm_equipos_destacados_v2]`.
- Plugin activo: `tm-equipos-destacados-v2 2.2.0`.
- Fuente local mantenida:
  `.codex-tmp/production-plugins/tm-equipos-destacados-v2/tm-equipos-destacados.php`.
- Endpoint:
  `https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposDestacadosWordpress`.
- Caché WordPress: 24 horas (`DAY_IN_SECONDS`).
- Transient: `tm_eqd_featured_api_v1`.
- Última respuesta válida: opción `tm_eqd_featured_api_last_good_v1`.
- Muestra hasta 5 equipos.
- Estado verificado: 5 de 5 con `estado.codigo === 1` y etiqueta `Disponible`.
- No debe mostrar `Alquilado`.
- Si no hay historial suficiente, el ranking completa espacios con equipos disponibles.
- En la consulta del 28 de julio hubo registros destacados con marca o modelo `N/A`/vacío; corregir esos datos en Inventario/Firebase, no en WordPress.

### Grilla y guías de tipos

- El Home ya apunta a las páginas canónicas bajo `/equipos/tipos/`; en la última comprobación no aparecieron enlaces `/equipos/que-es/...` en su HTML.
- La estructura editorial vigente está en `/equipos/tipos/` y contiene una portada más 13 páginas hijas (14 páginas en total).
- Implementación: `inc/tmd-equipment-type-guides.php` y `assets/css/tmd-equipment-type-guides.css`.
- Las familias principales visibles son Estibadores y Apiladores, Reach / Retráctiles, Tomapedidos y Contrabalanceados.
- Tres slugs anteriores redirigen 301 a las familias canónicas desde `inc/tmd-equipment-section-redirects.php`.
- No eliminar todavía la biblioteca histórica `/equipos/que-es/...` sin auditar SEO, sitemap y referencias externas.

## 7. Catálogos reales de Equipos y Energía

Endpoint general:

```text
https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposWordpress
```

Implementación WordPress:

```text
wp-content/themes/blocksy-child/inc/tmd-inventory-api.php
wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js
wp-content/themes/blocksy-child/assets/css/tmd-inventory-api.css
```

Estado live verificado:

- Total: 123 registros.
- Montacargas: 37.
- Baterías: 86.
- Tipos aceptados: `montacargas`, `bateria`.
- Todos tienen `estado.codigo === 1` (`Disponible`).
- Registros inválidos encontrados: 0.

Comportamiento de carga:

- WordPress consulta la API al faltar caché.
- Caché de inventario: 24 horas.
- Transient: `tmd_inventory_api_payload_v1`.
- Fallback: opción `tmd_inventory_api_last_good_v1`.
- Si falla la API, usa la última respuesta válida y un transient corto de 2 minutos.
- El JavaScript del catálogo no hace `fetch`, `setInterval`, `setTimeout` ni `location.reload`.
- Los filtros y la paginación actúan en el navegador sobre las tarjetas ya renderizadas.
- No debe recargar la página ni consultar la API cada diez minutos.

Filtros de `/equipos/`:

- Categoría.
- Subcategoría.
- Marca.
- Altura colapsada.
- Altura de levante.
- Condición.
- Operario.
- Reach.

Las tarjetas de montacargas ya no usan una etiqueta visual genérica `Disponible`. En su lugar muestran categoría y subcategoría, manteniendo `estado.codigo === 1` como condición interna obligatoria para publicar el registro.

Filtros de `/energia/`:

- Marca.
- Voltaje.
- Capacidad.
- Condición.

La defensa local de `tmd_inventory_api_items_by_type()` ya exige igualdad estricta con `estado.codigo === 1`, además del filtro aplicado por las Functions.

## 8. Firebase Functions

Proyecto local relacionado:

```text
/Users/lauracatalinapreciadoballen/Desktop/Inventario
```

Proyecto Firebase:

```text
inventariomaquinas-t
```

Archivos:

```text
functions/src/functions/listarEquiposWordpress.ts
functions/src/functions/listarEquiposDestacadosWordpress.ts
functions/src/functions/featuredEquipmentRanking.ts
functions/src/functions/__tests__/featuredEquipmentRanking.test.ts
```

Contrato vigente:

- Solo `tipo` `montacargas` o `bateria`.
- Solo `estado.codigo === 1`.
- `listarEquiposDestacadosWordpress` devuelve máximo 5.
- Los Functions Gen2 tienen invocación pública; no exigen login Firebase.
- Los 403 dependen de la allowlist de la aplicación, no de autenticación Firebase.

Verificación:

```bash
cd /Users/lauracatalinapreciadoballen/Desktop/Inventario/functions
npm run test:featured
```

Despliegue dirigido, siempre desde la raíz de Inventario:

```bash
cd /Users/lauracatalinapreciadoballen/Desktop/Inventario
npx -y firebase-tools@latest deploy \
  --only functions:listarEquiposDestacadosWordpress \
  --project inventariomaquinas-t
```

Para el endpoint general, cambiar el target por:

```text
functions:listarEquiposWordpress
```

No ejecutar el deploy desde `Inventario/functions`: el target se resuelve correctamente desde la raíz.

## 9. Quiz

- Página: `/encuentra-tu-equipo/` (ID 53).
- Contenido: `[tm_quiz_equipo_ideal]`.
- Implementación productiva canónica: `wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php`.
- Plugin activo: `tm-quiz-equipo-ideal 3.0.0`.
- La implementación grande que antes vivía en `inc/tmd-quiz-v3.php` fue consolidada dentro del plugin. El child theme ya no debe quitar y volver a registrar el shortcode.
- El diseño usa panel de pregunta a la izquierda y contexto visual/textual a la derecha.
- Las respuestas se cruzan con los equipos reales y sus categorías/subcategorías.
- El resultado no muestra puntos totales ni una explicación textual del puntaje.
- Muestra las 3 máquinas disponibles que mejor se ajustan a las respuestas.
- Las tres tarjetas tienen dimensiones uniformes, imagen contenida, etiquetas de categoría/subcategoría, datos técnicos y botones `Ver ficha`/`Cotizar`.
- El resultado permite enviar las recomendaciones por correo y repetir el quiz.
- Verificación visual previa: tarjetas de aproximadamente `381×439 px` en escritorio y `311×470 px` en móvil, sin desbordamiento.

Al modificar el quiz:

1. Editar el plugin `tm-quiz-equipo-ideal`, no la copia histórica del child theme.
2. Probar todas las preguntas, anterior/siguiente, resultados, tres fichas recomendadas, cotización, envío y responsive con Playwright.
3. El envío depende de `wp_mail`; validar entrega real después de corregir o reautorizar WPO365.

## 10. Blog

- Página: `/nosotros/blog/` (ID 281).
- 6 artículos publicados.
- Implementación del child theme: `inc/tmd-blog.php`.
- Assets: `assets/css/tmd-blog.css` y `assets/js/tmd-blog.js`.
- Hay templates `page-blog.php` y `single-post.php`.
- La página Inicio puede insertar una sección de novedades desde la lógica del blog.
- Rank Math está activo para SEO.

## 11. Contacto

- Página: `/nosotros/contacto/` (ID 57).
- Dirección:
  `Carrera 108 No.22F-21, Bogotá D.C., Colombia`.
- El mapa usa Google Maps embed con esa dirección y ya se muestra como mapa real.

Asesoras, de izquierda a derecha:

1. Karen Ramirez — `3022734800`.
2. Andrea Liliana Murillo Arguello — `3244298326`.
3. Gloria Andrea Vargas Amaya — `3168770708`.

Todas tienen el cargo `Asesora comercial`. Los botones de llamada y WhatsApp usan el mismo número de cada asesora.

Detalle conocido: las URLs de las fotos conservan nombres de archivo históricos (`ricardo-mendoza.jpg`, `elena-rodriguez.jpg`, `carlos-duarte.jpg`), aunque los nombres, alt, teléfonos y cargos visibles son los actuales. No renombrar archivos sin actualizar todas las referencias.

El formulario admite prellenado desde catálogos:

- Equipo: `?equipo=<nombre>`.
- Energía: `?tmd_cotizacion_energia=<nombre>`.

El parámetro antiguo `?energia=` causaba conflicto; no reintroducirlo.

### Formulario productivo

- El formulario manual anterior fue sustituido por Contact Form 7, formulario ID 14.
- La página 57 contiene un bloque shortcode independiente:
  `[contact-form-7 id="14" title="Formulario de contacto 1" html_class="tmd-form-card tmd-form-grid"]`.
- Contact Form 7 gestiona validación y envío; Flamingo conserva las solicitudes.
- El correo administrativo usa una plantilla HTML con los colores corporativos.
- Campos: nombre, correo, servicio, mensaje, consentimiento, origen/cotización ocultos y código de descuento opcional.
- El código del popup se autocompleta para usuarios autenticados.
- La validación comprueba que el código exista, esté disponible y corresponda al correo de la solicitud. Los códigos inexistentes, ajenos o usados se rechazan antes del envío.
- El prellenado de Equipo/Energía se realiza también del lado del servidor mediante `wpcf7_form_tag`, para que sobreviva a la inicialización de Contact Form 7.

## 12. Secciones corporativas y de servicio incorporadas

### Equipos y Energía

- Se publicó una biblioteca editorial de tipos de equipo bajo `/equipos/tipos/`.
- Energía quedó organizada bajo `/energia/`, con Baterías, Cargadores y BMS.
- `/bms/` redirige a `/energia/bms/`; la URL canónica es la anidada.
- El catálogo dinámico de Energía sigue alimentado por los 86 registros Firebase de tipo `bateria`; no existe todavía un inventario independiente y completo de cargadores.

### Mantenimiento

- `/mantenimiento/`, `/mantenimiento/mantenimiento-preventivo/` y `/mantenimiento/mantenimiento-correctivo/` responden HTTP 200.
- Assets productivos: `inc/tmd-maintenance.php` y `assets/css/tmd-maintenance.css`.
- El mega menú de Servicios enlaza las tres páginas.

### Nosotros

- `/nosotros/quienes-somos/` tiene estilos propios en `inc/tmd-about.php` y `assets/css/tmd-about.css`.
- `/nosotros/alianzas/` tiene estilos propios en `inc/tmd-partnerships.php` y `assets/css/tmd-partnerships.css`.
- `/nosotros/trabaja-con-nosotros/` está publicada, pero las vacantes y el flujo completo de postulación siguen pendientes.

### Popup y chatbot

- `tm-popup-bienvenida 1.2.2` está activo: aparece solo en Inicio a visitantes nuevos, usa cookie y permite registro/login.
- La política enlazada por el popup apunta a `/nosotros/legal/politica-de-privacidad/`.
- Genera un código único por cliente de `$100.000 COP`, válido una sola vez para el primer alquiler de un montacargas o una batería.
- Los códigos nuevos usan el prefijo `TM-ALQUILER-`; los seis códigos ya existentes fueron migrados sin regenerarlos.
- El código se muestra y puede copiarse inmediatamente, queda guardado en `/mi-cuenta/` y se autocompleta en el formulario de contacto.
- El beneficio no se aplica automáticamente al carrito: el flujo comercial real es cotización/contacto, no checkout.
- En `TM Popup Bienvenida > Códigos emitidos`, un administrador puede marcar el código como usado cuando se formalice el alquiler o restaurarlo si hubo un error.
- Un código usado o perteneciente a otro correo es rechazado por Contact Form 7.
- La obtención del código no depende exclusivamente del correo: siempre queda visible en el popup y en Mi cuenta.
- `tm-chatbot-fase1 1.0.0` está activo, guarda conversaciones/leads y ofrece salida a WhatsApp. Sigue siendo una fase inicial y el número por defecto dentro del plugin es un placeholder; corregir la configuración antes de considerarlo una transferencia real a asesor.

### Editabilidad desde WordPress

- Inicio usa bloques Core/Kadence.
- Catálogos, destacados, quiz y formulario se insertan como bloques de shortcode y se administran desde sus plugins/datos.
- Las páginas de tipos de equipo usan bloques Core.
- Varias páginas informativas grandes (Contacto, Mantenimiento, BMS, Quiénes somos y Alianzas, entre otras) siguen almacenadas como bloques `Custom HTML` dentro de `post_content`.
- Esas páginas sí se pueden editar desde WordPress y previsualizar, pero todavía no están descompuestas párrafo por párrafo en bloques visuales nativos.
- No convertirlas masivamente sin una tarea separada de migración y regresión visual.

### Correo saliente

- Plugins activos: `email-templates 1.5.14` y `wpo365-msgraphmailer 5.10`.
- El formulario de contacto y el popup ya generan correos HTML con estilo corporativo.
- La última revisión del panel WPO365 mostró que la autorización delegada de Microsoft Graph requería un token nuevo después de un grant expirado/revocado.
- `wp_mail()` puede aceptar la solicitud sin garantizar que haya llegado al buzón. Antes de declarar el correo completamente operativo, reautorizar en `WPO365 > Mail > Authorize` y validar recepción real en una bandeja controlada.

## 13. Footer

Fuente:

```text
wp-content/themes/blocksy-child/template-parts/tmd-footer.php
wp-content/themes/blocksy-child/assets/css/tmd-footer.css
```

Regla crítica: no reemplazar el footer completo. Ya ocurrió una regresión de diseño por sustituir toda su estructura. Corregir únicamente enlaces o bloques puntuales y conservar clases/markup.

Validar especialmente:

- Equipos.
- Baterías.
- Cargadores.
- Mantenimiento preventivo/correctivo.
- Quiénes somos.
- Blog.
- Alianzas.
- Trabaja con nosotros.
- PQR y legales.
- Contacto y WhatsApp.

## 14. Relación entre workspace local y producción

Workspace local principal:

```text
/Users/lauracatalinapreciadoballen/Desktop/PageTMD_v.3
```

El repositorio local contiene:

- `content/*.html` y scripts de despliegue históricos.
- `tmd-site-kit/`.
- Una copia del child theme en `wp-content/themes/blocksy-child/`.
- Copias de los plugins propios en `wp-content/plugins/`.
- Un snapshot versionable de páginas, artículos, snippets y versiones en `production-snapshot/`.
- El verificador `scripts/sync-production.sh`.

Advertencias:

- `tmd-site-kit` no figura entre los plugins activos de producción.
- El commit local observado al actualizar este handoff fue `5cb2ae4`; no representa por sí solo el estado remoto posterior a la depuración.
- La copia canónica local quedó atrasada al menos en dos puntos:
  - `wp-content/plugins/tm-popup-bienvenida/` conserva la versión 1.1.0, mientras producción usa 1.2.2.
  - `wp-content/plugins/tm-quiz-equipo-ideal/` conserva la versión 1.0.0, mientras producción usa 3.0.0.
- Las copias exactas más recientes usadas durante la intervención están temporalmente en:
  - `.codex-tmp/production-plugins/tm-popup-bienvenida/` (1.2.2).
  - `.codex-tmp/production-cleanup/plugins/tm-quiz-equipo-ideal/` (3.0.0).
- `.codex-tmp/` no debe convertirse en fuente canónica permanente. Antes del próximo cambio de estos plugins, copiar primero los archivos exactos desde producción hacia `wp-content/plugins/`, revisar el diff y versionarlos.
- La base de datos contiene cambios que no viven en archivos: formulario CF7 ID 14, plantilla de correo, opciones del popup, códigos por usuario, contenido de páginas y ajustes de registro.
- No asumir que `./scripts/sync-production.sh --check` cubre esos cambios de base de datos ni que todo el repositorio coincide actualmente con producción.
- Para cambios normales, sincronizar primero, editar las rutas canónicas del repositorio y desplegar únicamente los archivos modificados después de crear backup.

## 15. Backups y operación segura

Backups relevantes observados:

- Migración completa: `/root/backups/site-final-migration-20260718-132555.tar.gz` y `/root/backups/db-final-migration-20260718-132555.sql`.
- Restore point completo previo a la depuración:
  - `/root/backups/db-before-production-cleanup-20260728-213630.sql`.
  - `/root/backups/wp-content-before-production-cleanup-20260728-213630.tgz`.
- Estado compacto posterior a la depuración:
  - `/root/backups/db-production-ready-20260728-220506.sql`.
  - `/root/backups/custom-code-production-ready-20260728-220506.tgz`.
  - `/root/backups/wp-config-production-ready-20260728-220506.php`.
- Antes del popup 1.2.x:
  - `/root/backups/db-before-popup-20260728-v12.sql`.
  - `/root/backups/tm-popup-bienvenida-before-20260728-v12.tgz`.

Los archivos comprimidos anteriores fueron comprobados con `gzip -t`. `/root/backups` ocupaba aproximadamente 398 MB después de retirar backups intermedios. Antes de la próxima modificación material se debe crear un backup nuevo del componente exacto y de la base de datos cuando el cambio tenga estado persistente.

### Limpieza realizada el 28 de julio

- Se retiraron cuatro plugins redundantes o sin uso: Code Snippets, Filter Everything, String Locator y WooCommerce MercadoPago.
- Se eliminaron 13 tablas huérfanas de WPForms, WP Mail SMTP, MWAI, Elementor, Code Snippets y Fluent SMTP.
- Se eliminaron opciones huérfanas de Updraft, Popup Maker, Elementor, Jetpack, WPForms, WP Mail SMTP y MWAI.
- Se retiraron tipos de contenido huérfanos de Elementor, Popup Maker, Header/Footer Builder, WPCode y Filter Everything.
- Se podaron 166 revisiones antiguas y se limitó el crecimiento futuro a 10 revisiones por contenido.
- Se eliminaron directorios/cachés huérfanos de Updraft, WPForms, Popup Maker, WPCode, WPvivid y ShortPixel.
- `wp-content` bajó de aproximadamente 475 MB a 330 MB: cerca de 145 MB liberados.
- No se eliminó contenido del sitio anterior ni se modificó `tecnimontacargasdual.com`.

Desde el VPS:

```bash
TS=$(date +%Y%m%d-%H%M%S)
mkdir -p /root/backups

docker exec tmd_ols_wordpress \
  wp --allow-root --path=/var/www/vhosts/localhost/html \
  db export "/tmp/db-before-$TS.sql"

docker cp \
  "tmd_ols_wordpress:/tmp/db-before-$TS.sql" \
  "/root/backups/db-before-$TS.sql"

docker exec tmd_ols_wordpress sh -lc \
  "tar -czf /tmp/blocksy-child-before-$TS.tgz \
  -C /var/www/vhosts/localhost/html/wp-content/themes blocksy-child"

docker cp \
  "tmd_ols_wordpress:/tmp/blocksy-child-before-$TS.tgz" \
  "/root/backups/blocksy-child-before-$TS.tgz"
```

Para un archivo puntual:

```bash
TS=$(date +%Y%m%d-%H%M%S)
cp ARCHIVO "/root/backups/$(basename ARCHIVO).before-$TS"
```

Validación PHP:

```bash
docker exec tmd_ols_wordpress php -l \
  /var/www/vhosts/localhost/html/wp-content/themes/blocksy-child/functions.php

docker exec tmd_ols_wordpress php -l \
  /var/www/vhosts/localhost/html/wp-content/themes/blocksy-child/template-parts/tmd-header.php
```

Limpiar cachés:

```bash
docker exec tmd_ols_wordpress sh -lc '
  cd /var/www/vhosts/localhost/html &&
  wp cache flush --allow-root &&
  wp litespeed-purge all --allow-root
'
```

Limpiar únicamente el caché de inventario:

```bash
docker exec tmd_ols_wordpress sh -lc '
  cd /var/www/vhosts/localhost/html &&
  wp transient delete tmd_inventory_api_payload_v1 --allow-root &&
  wp option delete tmd_inventory_api_last_good_v1 --allow-root
'
```

Limpiar únicamente destacados:

```bash
docker exec tmd_ols_wordpress sh -lc '
  cd /var/www/vhosts/localhost/html &&
  wp transient delete tm_eqd_featured_api_v1 --allow-root &&
  wp option delete tm_eqd_featured_api_last_good_v1 --allow-root
'
```

WP-CLI puede tardar cuando carga todos los plugins. Para inspecciones de opciones, DB o posts que no necesitan hooks del sitio, usar:

```bash
wp --allow-root --skip-plugins --skip-themes ...
```

No usar `--skip-plugins` para purgar LiteSpeed ni para acciones que dependan de un plugin.

## 16. Verificación rápida

```bash
for route in \
  / \
  /equipos/ \
  /energia/ \
  /encuentra-tu-equipo/ \
  /nosotros/contacto/ \
  /nosotros/blog/ \
  /nosotros/proveedores/ \
  /nosotros/alianzas/ \
  /nosotros/quienes-somos/ \
  /mi-cuenta/ \
  /energia/baterias/ \
  /energia/cargadores/ \
  /energia/bms/ \
  /mantenimiento/ \
  /mantenimiento/mantenimiento-preventivo/ \
  /mantenimiento/mantenimiento-correctivo/ \
  /equipos/tipos/
do
  curl -sS -L -o /dev/null -w "%{http_code} $route\n" \
    "https://tecnimontacargas.com$route"
done
```

API general:

```bash
curl -sS \
  -H 'Origin: https://tecnimontacargas.com' \
  'https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposWordpress' |
jq '{
  ok,
  count,
  invalid: [.items[] | select(.estado.codigo != 1)] | length
}'
```

Destacados:

```bash
curl -sS \
  -H 'Origin: https://tecnimontacargas.com' \
  'https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposDestacadosWordpress' |
jq '{
  ok,
  count,
  codes: [.items[].estado.codigo],
  invalid: [.items[] | select(.estado.codigo != 1)] | length
}'
```

Esperado: HTTP 200, `invalid: 0` y únicamente código de estado 1.

Comprobar por separado que Repuestos siga fuera de producción:

```bash
curl -sS -L -o /dev/null -w "%{http_code}\n" \
  "https://tecnimontacargas.com/repuestos/"
```

Esperado para `/repuestos/`: HTTP 404.

Resultado puntual del 28 de julio de 2026: las rutas listadas respondieron 200, `/repuestos/` respondió 404, `www` redirigió 301 al apex, la API general devolvió 123 registros válidos (37 montacargas y 86 baterías) y la API de destacados devolvió 5 válidos.

### Verificación específica del popup 1.2.2

Se probó con Playwright usando un usuario temporal que se eliminó al terminar:

- Registro y login.
- Generación de código con patrón `TM-ALQUILER-XXXXXX`.
- Persistencia y visualización en Mi cuenta.
- Autocompletado en Contact Form 7.
- Rechazo de código inexistente.
- Marcado como usado, rechazo del código usado y restauración desde administración.
- Cierre con Escape.
- Escritorio y móvil `390×844`, sin overflow ni errores de consola.

## 17. Estado breve frente a `Requeriminetos.md` v6.6

### Realizado

- Infraestructura productiva, dominio, TLS, WordPress, tema hijo, header/mega menú, footer y responsive base.
- Home con hero, marcas, destacados, accesos de equipos, servicios, blog y CTA.
- Catálogos Firebase de Equipos y Energía, fichas/cotización, filtros y paginación sin recarga.
- Biblioteca editorial de tipos de equipo, quiz funcional, blog, contacto, Quiénes somos, alianzas, políticas y mantenimiento.
- Popup de bienvenida con cuenta, código único de primer alquiler, Mi cuenta, validación en Contact Form 7 y control de usado/restaurado.
- Formulario de contacto gestionado con Contact Form 7, persistencia en Flamingo y correo HTML corporativo.
- Depuración de plugins, tablas, opciones, revisiones, cachés y archivos huérfanos.
- Rank Math, GTM, seguridad, caché y optimización de imágenes instalados.

### Parcial

- El Home implementa el contenido principal, pero no replica literalmente todos los criterios visuales de HER-001/HOME-001/HOME-002.
- El catálogo no tiene los ocho niveles jerárquicos, comparador ni todo el control de precio/cotización solicitado desde WordPress.
- Energía publica baterías, cargadores y BMS, pero el inventario dinámico solo diferencia `bateria`; falta catálogo independiente de cargadores y compatibilidades completas.
- El chatbot es Fase 1; falta IA entrenada y transferencia con datos definitivos.
- Trabaja con nosotros tiene landing, pero faltan vacantes y postulación completa.
- El quiz recomienda y envía resultados, pero no tiene dashboard, embudos ni exportación SER-003.
- Las páginas son editables desde WordPress, pero varias secciones informativas siguen como bloques Custom HTML y no como bloques visuales individuales.
- SEO/GTM están instalados, pero faltan validar metas/schema/Lighthouse y configurar eventos, embudos, Clarity/Hotjar, UTM y perfil progresivo.

### Pendiente

- Barra lateral sticky global SID-001.
- Agendamiento AGE-001 mediante enlace configurable.
- Tienda de repuestos completa: `/repuestos/`, productos, filtros, checkout, MercadoPago/PSE y notificaciones.
- Comparador CAT-004.
- Reautorizar Microsoft Graph en WPO365 y confirmar recepción real de Contact Form 7, popup y quiz.
- Contenidos finales pendientes: imágenes definitivas de quiz/fichas, datos incompletos del inventario y capacitación/entrega formal.

## 18. Riesgos y próximos pasos recomendados

1. Sincronizar desde producción las versiones 1.2.2 del popup y 3.0.0 del quiz hacia las rutas canónicas `wp-content/plugins/` antes de volver a desplegar desde el workspace.
2. Reautorizar Microsoft Graph en `WPO365 > Mail > Authorize` y probar entrega real a una bandeja controlada.
3. Crear un backup nuevo antes de la próxima modificación; el restore point completo vigente precede el popup 1.2.2.
4. No asumir que `scripts/sync-production.sh` compara contenido/opciones de la base de datos; mantener además un snapshot explícito de CF7, páginas y opciones propias.
5. Cambiar autoactualizaciones solo con autorización; actualmente varias están habilitadas.
6. Auditar SEO/sitemap y referencias externas antes de retirar la biblioteca histórica `/equipos/que-es/...`; el Home ya usa `/equipos/tipos/`.
7. Completar o reemplazar imágenes de fichas/quiz cuando el usuario las entregue.
8. Corregir datos incompletos (`N/A`, modelos o imágenes ausentes) desde Inventario/Firebase, no inventarlos en WordPress.
9. Mantener la redirección BMS y auditar referencias antes de eliminar la página histórica.
10. No publicar la tienda hasta completar productos, pasarela, PSE, pruebas de compra y notificaciones.

## 19. Prompt breve para continuar

> Continúa el sitio WordPress productivo `https://tecnimontacargas.com` siguiendo este documento. Antes de editar, inspecciona el archivo o página exacta, crea backup en `/root/backups`, aplica un parche mínimo, valida sintaxis, limpia LiteSpeed y verifica por HTTP y navegador. No toques `tecnimontacargasdual.com`, no sobrescribas el child theme ni los plugins desde el workspace sin compararlos primero con producción y no cambies plugins/infraestructura fuera del alcance. Producción usa `tm-popup-bienvenida 1.2.2` y `tm-quiz-equipo-ideal 3.0.0`, versiones todavía no sincronizadas con las rutas canónicas locales al cerrar este handoff. Los catálogos se alimentan de Firebase y solo pueden mostrar `montacargas`/`bateria` con `estado.codigo === 1`.
