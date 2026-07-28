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
wp-content/themes/blocksy-child/inc/tmd-quiz-v3.php
wp-content/themes/blocksy-child/inc/tmd-blog.php
wp-content/themes/blocksy-child/inc/tmd-brand-carousel.php
wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php
wp-content/themes/blocksy-child/inc/tmd-energy-structure.php
wp-content/themes/blocksy-child/inc/tmd-maintenance.php
wp-content/themes/blocksy-child/inc/tmd-partnerships.php
wp-content/themes/blocksy-child/inc/tmd-about.php
wp-content/themes/blocksy-child/single-tmd_equipo.php
wp-content/themes/blocksy-child/single-tmd_energia.php
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

Los CPT ya no representan el inventario completo. Los catálogos visibles de Equipos y Energía se generan con datos en vivo/cacheados de Firebase.

### Plugins activos

```text
advanced-custom-fields 6.8.6
blocksy-companion 2.1.50
code-snippets 3.9.6
contact-form-7 6.1.6
email-templates 1.5.14
filter-everything 1.9.4
flamingo 2.6.3
duracelltomi-google-tag-manager 1.22.4
kadence-blocks 3.7.8.1
litespeed-cache 7.8.1
seo-by-rank-math 1.0.275
shortpixel-image-optimiser 6.5.5
string-locator 2.6.7
tm-chatbot-fase1 1.0.0
tm-equipos-destacados-v2 2.2.0
tm-popup-bienvenida 1.1.0
tm-quiz-equipo-ideal 1.0.0
woocommerce 10.9.4
wordfence 8.2.2
wpo365-msgraphmailer 5.10
wps-hide-login 1.9.18
```

Plugin instalado pero inactivo:

```text
woocommerce-mercadopago 8.9.0
```

No había actualizaciones pendientes reportadas por WP-CLI durante la verificación. Varios plugins tienen autoactualización habilitada, por lo que sus versiones pueden cambiar sin un despliegue manual. No actualizar plugins, temas o WordPress como parte de otra tarea sin backup y prueba de regresión.

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

`/repuestos/` devolvió HTTP 404. La tienda y el carrito responden 200, pero no hay productos publicados y MercadoPago está inactivo; no considerar terminado el e-commerce.

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

- Traslado y elevación ligera.
- Pasillo angosto.
- Preparación de pedidos.
- Contrabalanceados.
- La sección Manlift fue retirada.

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

- El Home todavía conserva enlaces de la biblioteca histórica `/equipos/que-es/...`.
- La estructura editorial vigente está en `/equipos/tipos/` y contiene una portada más 13 páginas hijas (14 páginas en total).
- Implementación: `inc/tmd-equipment-type-guides.php` y `assets/css/tmd-equipment-type-guides.css`.
- Las familias principales son traslado/elevación ligera, pasillo angosto, preparación de pedidos y contrabalanceados.
- Tres slugs anteriores redirigen 301 a las familias canónicas desde `inc/tmd-equipment-section-redirects.php`.
- No eliminar todavía la biblioteca histórica `/equipos/que-es/...`: primero actualizar enlaces del Home, SEO y referencias externas.

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

- Marca.
- Altura colapsada.
- Altura de levante.
- Condición.
- Operario.
- Reach.

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
- El plugin `tm-quiz-equipo-ideal` registra originalmente ese shortcode.
- El child theme, en `inc/tmd-quiz-v3.php`, elimina el shortcode del plugin y registra `tmd_quiz_equipo_ideal_v3_shortcode`.
- Por tanto, la versión productiva efectiva es la del child theme, no la plantilla original del plugin.
- El diseño usa panel de pregunta a la izquierda y contexto visual/textual a la derecha.
- Se trabajaron preguntas de aplicación, altura y otras variables del equipo.
- Las imágenes definitivas se dejaron como etapa posterior; priorizar estilos, legibilidad y responsive.

Al modificar el quiz:

1. Editar `inc/tmd-quiz-v3.php`.
2. No asumir que editar únicamente el plugin cambiará lo visible.
3. Probar todas las preguntas, anterior/siguiente, resultados y responsive con Playwright.

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

- `tm-popup-bienvenida 1.1.0` está activo: aparece solo en Inicio a visitantes nuevos, usa cookie, permite registro/login y genera cupones WooCommerce.
- La política enlazada por el popup apunta a `/nosotros/legal/politica-de-privacidad/`.
- El descuento de repuestos puede operar con WooCommerce, pero el uso comercial para alquiler todavía no tiene un flujo transaccional completo.
- `tm-chatbot-fase1 1.0.0` está activo, guarda conversaciones/leads y ofrece salida a WhatsApp. Sigue siendo una fase inicial y el número por defecto dentro del plugin es un placeholder; corregir la configuración antes de considerarlo una transferencia real a asesor.

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
- El child theme productivo completo en `wp-content/themes/blocksy-child/`.
- Los cuatro plugins propios activos en `wp-content/plugins/`.
- Un snapshot versionable de páginas, artículos, snippets y versiones en `production-snapshot/`.
- El verificador `scripts/sync-production.sh`.

Advertencias:

- `tmd-site-kit` no figura entre los plugins activos de producción.
- `.codex-tmp/` contiene artefactos históricos y no es fuente de despliegue.
- El commit local `e781067` registra el estado productivo verificado el 28 de julio de 2026.
- Antes de editar, ejecutar `./scripts/sync-production.sh --check`; debe informar que producción y repositorio coinciden.
- Si hubo un cambio de emergencia en el VPS, incorporarlo con `--pull`, revisar el diff y hacer commit antes de continuar.
- Para cambios normales, editar primero las rutas canónicas del repositorio y desplegar únicamente los archivos modificados después de crear backup.
- El workspace local tenía cambios sin commit en `tmd-site-kit` y artefactos sin seguimiento en `.codex-tmp`.

## 15. Backups y operación segura

Backups relevantes observados:

- Migración completa: `/root/backups/site-final-migration-20260718-132555.tar.gz` y `/root/backups/db-final-migration-20260718-132555.sql`.
- Estado estable reciente: `/root/backups/blocksy-child-stable-current-20260728-193711.tgz` y `/root/backups/db-stable-current-20260728-193711.sql`.
- Hay backups adicionales del 28 de julio previos a guías, Energía, mantenimiento, alianzas y enlaces de botones.

El backup estable de las 19:37 UTC precede cambios posteriores de alianzas y enlaces de botones. Antes de la próxima modificación material se debe crear otro backup de DB y child theme.

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

Resultado puntual del 28 de julio de 2026: las rutas listadas respondieron 200, `www` redirigió 301 al apex, la API general devolvió 123 registros válidos y la API de destacados devolvió 5 válidos.

## 17. Estado breve frente a `Requeriminetos.md` v6.6

### Realizado

- Infraestructura productiva, dominio, TLS, WordPress, tema hijo, header/mega menú, footer y responsive base.
- Home con hero, marcas, destacados, accesos de equipos, servicios, blog y CTA.
- Catálogos Firebase de Equipos y Energía, fichas/cotización, filtros y paginación sin recarga.
- Biblioteca editorial de tipos de equipo, quiz funcional, blog, contacto, Quiénes somos, alianzas, políticas y mantenimiento.
- Popup de bienvenida con cuenta/cupón y configuración básica desde WordPress.
- Rank Math, GTM, seguridad, caché y optimización de imágenes instalados.

### Parcial

- El Home implementa el contenido principal, pero no replica literalmente todos los criterios visuales de HER-001/HOME-001/HOME-002.
- El catálogo no tiene los ocho niveles jerárquicos, comparador ni todo el control de precio/cotización solicitado desde WordPress.
- Energía publica baterías, cargadores y BMS, pero el inventario dinámico solo diferencia `bateria`; falta catálogo independiente de cargadores y compatibilidades completas.
- El chatbot es Fase 1; falta IA entrenada y transferencia con datos definitivos.
- El popup genera cupones WooCommerce, pero la aplicación del descuento al alquiler no está cerrada.
- Trabaja con nosotros tiene landing, pero faltan vacantes y postulación completa.
- El quiz recomienda y envía resultados, pero no tiene dashboard, embudos ni exportación SER-003.
- SEO/GTM están instalados, pero faltan validar metas/schema/Lighthouse y configurar eventos, embudos, Clarity/Hotjar, UTM y perfil progresivo.

### Pendiente

- Barra lateral sticky global SID-001.
- Agendamiento AGE-001 mediante enlace configurable.
- Tienda de repuestos completa: `/repuestos/`, productos, filtros, checkout, MercadoPago/PSE y notificaciones.
- Comparador CAT-004.
- Contenidos finales pendientes: imágenes definitivas de quiz/fichas, datos incompletos del inventario y capacitación/entrega formal.

## 18. Riesgos y próximos pasos recomendados

1. Crear un backup estable nuevo: el backup estable de las 19:37 UTC precede cambios posteriores del 28 de julio.
2. Mantener `./scripts/sync-production.sh --check` como control obligatorio antes y después de cada despliegue.
3. Desactivar o controlar las autoactualizaciones de plugins solo con autorización; actualmente varias están habilitadas.
4. Revisar visualmente los cambios del 28 de julio en escritorio y móvil, especialmente Inicio, guías, Energía, mantenimiento y alianzas.
5. Actualizar los enlaces históricos `/equipos/que-es/...` del Home después de definir si la nueva biblioteca `/equipos/tipos/` los reemplaza.
6. Completar o reemplazar imágenes de fichas/quiz cuando el usuario las entregue.
7. Corregir datos incompletos (`N/A`, modelos o imágenes ausentes) desde Inventario/Firebase, no inventarlos en WordPress.
8. Mantener la redirección BMS y auditar referencias antes de eliminar la página histórica.
9. No publicar la tienda hasta completar productos, pasarela, PSE, pruebas de compra y notificaciones.

## 19. Prompt breve para continuar

> Continúa el sitio WordPress productivo `https://tecnimontacargas.com` siguiendo este documento. Antes de editar, inspecciona el archivo o página exacta, crea backup en `/root/backups`, aplica un parche mínimo, valida sintaxis, limpia LiteSpeed y verifica por HTTP y navegador. No toques `tecnimontacargasdual.com`, no sobrescribas el child theme desde el workspace local y no cambies plugins/infraestructura fuera del alcance. Los catálogos se alimentan de Firebase y solo pueden mostrar `montacargas`/`bateria` con `estado.codigo === 1`.
