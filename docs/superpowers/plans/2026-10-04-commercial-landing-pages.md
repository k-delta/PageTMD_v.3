# Páginas comerciales de alquiler y baterías — Implementation Plan

> **Ejecución:** La implementación se consolidó en el checkout principal de `main`. Los materiales originales DOCX e imágenes de referencia no forman parte del cambio versionado; los recursos optimizados bajo el child theme son assets finales requeridos por las vistas.

**Goal:** Preparar dos páginas editables en WordPress, enlazadas desde Equipos y Energía, con inventario canónico, formularios específicos y la identidad visual acordada.

**Architecture:** El tema aporta estilos de página y schema Service para alquiler. El bloque de montacargas lee Inventario/Firebase y limita a cinco referencias eléctricas clasificadas. Un script WP-CLI idempotente guarda cada sección en un bloque HTML Gutenberg independiente y crea formularios Contact Form 7 a partir del destinatario actual del formulario de contacto. Las páginas se crean como borradores; los enlaces públicos solo aparecen cuando cada página se publica.

**Tech Stack:** WordPress, child theme Blocksy, PHP, Gutenberg, Contact Form 7, Rank Math, CSS.

**Spec:** `docs/specs/2026-10-04-landing-montacargas-baterias.md`

## Global Constraints

- El alquiler mínimo de montacargas es de 1 mes; no se ofrece alquiler por horas, por días ni por periodos inferiores a ese mínimo.
- La cobertura comercial de ambas páginas es Colombia.
- La instalación y el mantenimiento de baterías se realizan en la bodega del cliente.
- Inventario/Firebase es la fuente canónica para los datos de equipos.
- La venta de montacargas se limita a equipos usados cuya disponibilidad esté confirmada; el contenido no debe prometer equipos nuevos ni existencias no verificadas.
- Rank Math controla title, meta description, canonical, robots, Open Graph, schema y sitemap.
- El usuario autorizó el despliegue de código mediante push; crear y verificar el backup antes del primer despliegue. Las escrituras de contenido WordPress requieren autorización específica y un backup reciente.
- Excluir del cambio versionado los documentos e imágenes originales usados como referencias y no modificar `/energia/baterias/`.

## Review Focus

- Si una ruta ya está ocupada, la preparación debe detenerse sin sobrescribir contenido ni redirigirla.
- Si el inventario no tiene elementos publicables, las secciones siguen ofreciendo acceso al catálogo o al formulario sin mostrar disponibilidad ficticia.
- Si Contact Form 7 o el formulario base no está disponible, el procedimiento debe detenerse antes de escribir.
- Mientras una página siga como borrador, la navegación pública no debe enlazar a una URL que devuelva 404.
- En móvil, CTA, formularios, FAQ y carrusel deben caber y seguir siendo operables con teclado o gesto táctil.

## Plan

### Task 1: Navegación y presentación del tema

**Files:**
- Modify: `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- Modify: `wp-content/themes/blocksy-child/functions.php`
- Modify: `wp-content/themes/blocksy-child/inc/tmd-inventory-api.php`
- Modify: `wp-content/themes/blocksy-child/inc/tmd-form-antispam.php`
- Modify: `wp-content/themes/blocksy-child/inc/tmd-seo.php`
- Create: `wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php`
- Create: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`
- Create: `wp-content/themes/blocksy-child/assets/img/commercial-landings/alquiler-hero.jpeg`
- Create: `wp-content/themes/blocksy-child/assets/img/commercial-landings/baterias-hero.jpeg`
- Create: `wp-content/themes/blocksy-child/assets/img/commercial-landings/baterias-galeria.jpg`

**Interfaces:**
- `tmd_commercial_landing_nav_url(string $slug): string` devuelve permalink publicado, preview de editor o cadena vacía.
- Shortcode `tmd_commercial_related_articles` muestra artículos publicados de la categoría técnica que coincidan con la página; sin coincidencias enlaza al blog.
- Rank Math conserva su graph y agrega `Service` solamente para la página de alquiler.
- Los datos de montacargas salen de tmd_inventory_api_items_by_type() y usan tmd_inventory_api_classification() para admitir solo subcategorías eléctricas reconocidas; no se cargan 120 fotos estáticas.
- La página de baterías enlaza las tres rutas de energía existentes y no consulta el grid general de baterías, que no separa la tecnología en su filtro de entrada.

- [x] Añadir enlaces de alquiler y baterías a los pies de menús de escritorio y móvil. Omitir enlaces públicos hasta que las páginas estén publicadas.
- [x] Encolar los recursos de inventario donde se muestran referencias; la vista de baterías no descarga el JavaScript del catálogo. Añadir CSS aislado para hero, tarjetas, cifras, formularios, FAQ, artículos y carrusel responsivo.
- [x] Ocultar el título duplicado del tema y mantener Work Sans y la paleta aprobada.
- [x] Extender el schema Rank Math existente por slug, sin añadir metadatos SEO manuales al HTML.

**Validation:** `php -l` para PHP tocado; revisión estática de slugs, clases, destinos, estilos responsive y schema. No se añadieron pruebas visuales automatizadas.

### Task 2: Contenido editable y preparación controlada de las páginas

**Files:**
- Create: `scripts/create-commercial-landing-pages.php`

**Interfaces:**
- WP-CLI `dry-run` (por defecto) describe páginas, campos y formularios sin escrituras.
- WP-CLI `execute` exige copia de seguridad verificada y crea dos páginas raíz en estado `draft`.
- Los bloques de página usan secciones HTML editables y shortcodes nativos para CF7, inventario y artículos.
- Los formularios nuevos usan `TMD_COMMERCIAL_LANDINGS_RECIPIENT` cuando se indica, validan ese override y conservan el destinatario de CF7 ID 14 solo como alternativa. El procedimiento no actualiza el formulario existente. Cada formulario lleva honeypot validado server-side y límite de cinco envíos por hora por IP, ligado por metadata al marcador administrado; la IP se almacena solo como clave hash.
- El seed toma un bloqueo exclusivo en el directorio temporal antes de revisar formularios y páginas, y lo conserva hasta completar o revertir la operación.
- La ejecución de escritura exige `TMD_VERIFIED_BACKUP_PATH` con `BACKUP_MANIFEST.json` y `database.sql`: dump MariaDB/MySQL de producción de al menos 64 KiB, hash SHA-256 y tamaño coincidentes con el manifiesto, cabecera SQL y marcador de finalización verificados, permisos privados, ubicación fuera del docroot y antigüedad máxima de dos horas. El manifiesto identifica el método/ruta de restauración. El modo predeterminado solo hace dry-run.

- [x] Construir el contenido de alquiler en el orden acordado: hero/CTA, necesidades, alquilar o comprar, inventario real, proceso, sectores, usos mensuales, respaldo, cotización, seis FAQ y blog. La franja de cifras y la nota de marcas inmediata se omiten por solicitud del usuario del 2026-10-04.
- [x] Construir el contenido de baterías: hero/CTA, soluciones Barbillon, diagnóstico, rendimiento, proceso con servicio en bodega del cliente, cinco imágenes de referencia, cotización, seis FAQ y blog.
- [x] Aplicar mínimo de un mes, Colombia, equipos sin operador, venta usada, privacidad enlazada y campos indicados. Excluir condiciones, inventario, precios y modelos no confirmados.
- [x] Rechazar slugs ocupados y páginas/formularios existentes no reconocidos; no sobreescribir contenido editorial.
- [x] Configurar title y description mediante metadatos Rank Math; dejar canonical y sitemap al plugin cuando las páginas se publiquen.
- [x] Permitir un destinatario explícito para los dos formularios nuevos, validar el override y dejar CF7 ID 14 sin cambios.

**Validation:** `php -l scripts/create-commercial-landing-pages.php`; ejecutar `php tests/test-commercial-landing-recipient.php` para verificar el override, el destinatario de ambos formularios, la validación de correo y que el dry-run no escriba. El dry-run contra WordPress productivo es de solo lectura; la escritura de contenido requiere autorización específica y un backup fresco.

### Task 3: Revisión final del alcance

- [x] Comparar el diff del checkout principal con el SPEC y excluir los documentos e imágenes fuente de `pageTMD/`.
- [x] Revisar que no se mencione una oferta descartada, un periodo inferior a un mes, cobertura local limitada, precio ni disponibilidad no confirmada.
- [x] Confirmar en producción las páginas publicadas 1558 y 1559. La verificación de lectura identificó el hero anterior en la página 1558 y guardó su hash como precondición para el cambio de contenido.
- [ ] Completar la actualización del hero después de resolver el control de deriva, preparar el backup verificado y obtener autorización ligada a los archivos y al contenido exactos.

**Validation:** La prueba focal `php tests/test-commercial-landing-recipient.php`, `php -l` del seed, del archivo de tema y de la prueba pasan en la iteración anterior; `git diff --check` pasa. `./scripts/sync-production.sh --check` detectó diferencias: los snapshots locales no incluyen las páginas 1558/1559 que existen en producción, y los archivos CSS/PHP locales contienen cambios de esta iteración; también encontró `.DS_Store` locales ignorados. No se ejecutó `--pull`. Después, bajo DEC-09, se retiró de la página publicada 1558 el bloque de cifras con backup completo y snapshot verificados, dry-run y comprobación posterior a `wp_update_post()`. La URL canónica respondió HTTP 200 sin las clases de la sección y conservó el mínimo mensual y el bloque siguiente. No se capturó una verificación visual de navegador; el ajuste del hero sigue pendiente y no se desplegó código.

### Ajuste del hero de alquiler — 2026-10-04

- [x] Reutilizar `alquiler-hero.jpeg` y reconstruir la referencia con texto HTML editable; quitar la línea auxiliar y mantener el header global, el CTA, el mínimo de un mes, la cobertura Colombia y el alquiler sin operador.
- [x] Ajustar jerarquía tipográfica, posición de la imagen, legibilidad y comportamiento móvil en el CSS de la landing.
- [x] Actualizar el contenido que generará el seed y cubrirlo con aserciones de la prueba focal.
- [ ] Aplicar la actualización a la página publicada ID 1558 y verificar escritorio/móvil después de aprobar el manifiesto exacto.

### Retiro de la franja de cifras — 2026-10-04

- [x] Retirar las tres tarjetas de cifras y la nota de marcas inmediata del generador, sus estilos y el contenido publicado de la página 1558; conservar el mínimo de un mes en los demás bloques.
- [x] Crear y verificar un backup privado de MariaDB y una copia del contenido previo de la página antes de la escritura.
- [x] Ejecutar un dry-run que confirmó un solo bloque objetivo, actualizar con `wp_update_post()` y comprobar la URL canónica.

**Validation:** `php -l scripts/create-commercial-landing-pages.php` y `git diff --check` pasan. La lectura pública posterior respondió HTTP 200; no encontró `tmd-commercial-landing__stats` ni `tmd-commercial-landing__brand-note`, y sí encontró el mínimo de un mes y la sección de necesidades siguiente. No se realizó captura visual de escritorio o móvil.

### Iconos del hero de baterías — 2026-10-04 (DEC-10)

- [x] Añadir en el generador tres SVG inline decorativos de 18 px y trazo azul: marcador para cobertura, bodega para instalación y calendario para alquiler desde un mes. Mantener intactos los tres textos y la fila existente.
- [x] Crear un backup privado y verificado, ejecutar dry-run con hash de contenido y actualizar únicamente el bloque HTML de la página publicada `/baterias-para-montacargas/`.
- [x] Validar sintaxis PHP, diff, purga de caché y HTML público: HTTP 200, tres SVG decorativos, etiquetas únicas y en el orden aprobado.
- [ ] Obtener captura visual del navegador en escritorio y móvil; no hay una herramienta de navegador disponible en este entorno.

**Validation:** El backup completo y el snapshot del contenido se verificaron antes de escribir. El dry-run confirmó los tres reemplazos exactos; `wp_update_post()` guardó el contenido esperado y la purga puntual de LiteSpeed se ejecutó. La URL canónica respondió HTTP 200 con los tres SVG y sus textos. `php -l` y `git diff --check` pasan. No se desplegó código del tema. `./scripts/sync-production.sh --check` detectó diferencias en el CSS del tema, snapshots productivos y archivos `.DS_Store`; esas diferencias no se incorporaron ni desplegaron. Falta captura visual de navegador.

### Reemplazo de la maqueta de baterías — 2026-10-05 (DEC-11)

La solicitud vigente sustituye para baterías el hero y el contenido de la versión anterior por el Markdown de nueve secciones. El trabajo de alquiler no forma parte de este ajuste.

- [x] Actualizar el generador, la composición del formulario, el shortcode de relacionados y los estilos para reproducir las nueve secciones y sus encabezados. Preservar el consentimiento exigido por la política de privacidad.
- [x] Copiar solo las siete fotos de catálogo usadas desde `EQUIPOS SEGUN REFERENCIA/` a `assets/img/commercial-landings/baterias-maqueta/` y crear copias WebP optimizadas para banner, diferencial, ventajas, proceso, galería y formulario; conservar sin cambios las fuentes del catálogo. La galería también usa los activos de batería/monitoreo que ya viven en el child theme.
- [x] Revisar visualmente los activos de energía ya existentes y corregir el título de la galería: usa imágenes de batería/monitoreo junto a montacargas del catálogo, con texto neutral y sin atribución visual a Barbillon que no se pueda verificar.
- [x] `php -l` de los archivos PHP modificados, `git diff --check` y las pruebas focalizadas `test-commercial-landing-battery-maqueta.php` y `test-commercial-landing-battery-blog.php` pasan. Cubren orden/estructura, textos de sección y FAQ, los seis valores del correo, consentimiento, CTA, assets, artículos publicados relacionados y estado vacío, dry-run, hashes, snapshot y fallos de rollback/concurrencia con WordPress/CF7 simulados. No hay Stylelint configurado. La revisión de base de datos confirmó locks, hashes, backup y rollback; seguridad, rendimiento y revisión general no reportan hallazgos. El test reviewer está cerrando la revisión de las aserciones añadidas. El CTA `#262E4F`/blanco fue aprobado por el usuario el 2026-10-05.
- [x] Comparar `./scripts/sync-production.sh --check` contra los archivos productivos: el PHP y CSS vivos coinciden con `HEAD`; la diferencia de esos archivos corresponde al cambio local. Las diferencias de `production-snapshot/` son datos de auditoría, no fuente de código, y los `.DS_Store` locales son ruido. No se usó `--pull` ni se desplegó código.
- [ ] Crear y verificar backup completo de base de datos y snapshot de página/formulario. Hacer dry-run con hashes de origen y destino para el ID 1559 y CF7 ID 1557; escribir solo tras pasar los controles pendientes.
- [ ] Desplegar la lista exacta de archivos aprobados, purgar caché de página y comprobar HTML, HTTP, sitemap, metadatos, carga de assets y formulario en producción.
- [ ] Obtener revisión visual de escritorio/móvil con Playwright después del despliegue.

**Validación local y preflight (2026-10-05):** Siete fotos del catálogo quedaron copiadas y optimizadas como WebP dentro de la carpeta de la maqueta (1,1 MB total); las fuentes no se modificaron. Pasaron los tests focalizados de maqueta y blog, destinatario, render/update rental-v2 y antispam; también los lint PHP y `git diff --check`. No hay configuración de Stylelint. La revisión de imágenes confirmó el uso neutral de batería/monitoreo existente y montacargas del catálogo. Producción y `origin/main` estaban en `08cad501`; el CSS y el PHP activos coinciden con ese SHA. El check detectó solo el delta intencional de baterías en CSS/PHP/assets y snapshots de auditoría desactualizados: páginas 1558/1559 ausentes del snapshot local, contenido de nueve páginas y tres posts actualizado en producción, versiones de terceros más nuevas y cinco entradas productivas más una entrada local con nombres distintos en el listado de plugins. Esas rutas no se incluyen en el deploy; no hubo deriva en plugins propios, Compose ni otros archivos de tema. No se usó `--pull`. Todavía no hay backup ni escritura DEC-11. La URL pública responde HTTP 200 y conserva el contenido anterior. El navegador Playwright está disponible para validar escritorio y móvil tras desplegar.

## Evidencia consultada

- Context7, WordPress Functions: `wp_insert_post()` devuelve ID o `WP_Error` y admite `post_type`, `post_name`, `post_content` y `post_status`.
- Context7, Contact Form 7: usar `wpcf7_save_contact_form()` para guardar y volver a cargar con API pública; tags de sitio `[_site_title]` y `[_site_admin_email]` permiten un remitente fijo.
- Context7, Contact Form 7: `WPCF7_Submission::get_contact_form()`, `get_posted_data()` y `add_spam_log()` sustentan el filtro `wpcf7_spam`, restringido por el marcador de los formularios administrados.
- Context7, WordPress Functions: `is_email()` devuelve la dirección válida o `false`; valida el override antes de incluirlo en los formularios nuevos.
- Context7, WP-CLI: `wp eval-file -` acepta el script por STDIN; permite probar el seed desplegable sin copiar archivos a producción.
- Context7, WordPress Functions: `wp_update_post()` devuelve el ID al actualizar y permite solicitar `WP_Error`; el actualizador aplica `wp_slash()` al contenido para conservarlo al guardar y al restaurar.
- Context7, LiteSpeed Cache for WordPress: `litespeed_purge_post` ejecuta la invalidación de caché etiquetada para un post concreto; WP-CLI `eval` permitió invocar el hook para el ID 1559.
- Context7, Rank Math: `rank_math_title` y `rank_math_description` son metadatos reconocidos. El tema ya usa `rank_math/json_ld` y WordPress/Rank Math resuelve canonical y sitemap.
- Código local: `tmd_inventory_api_items_by_type()` filtra por estado publicable; la clasificación de montacargas separa subcategorías eléctricas y combustión. La nueva página limita el resultado a cinco registros.
- Código local: el menú de escritorio/móvil está en `template-parts/tmd-header.php`; navegación móvil puede envolver acciones y CSS actual `tmd-mm-panel-footer` ya permite wrap.
