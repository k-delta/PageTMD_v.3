# Reglas permanentes del repositorio

Estas reglas aplican a todo el repositorio. Una instrucción directa del usuario
para la tarea actual tiene prioridad. Un `AGENTS.md` más profundo puede añadir
reglas para su subárbol, pero no puede debilitar las reglas de seguridad de este
archivo.

## Autoridad y alcance

1. La instrucción directa del usuario tiene prioridad.
2. Este archivo es la fuente permanente del repositorio.
3. El código versionado y la producción verificada determinan el estado real.

No inferir backlog desde planes, requisitos o handoffs históricos eliminados.
Una función ausente del código y de producción está descartada: no es deuda,
fase parcial ni trabajo pendiente. Solo retomarla por solicitud explícita.

## Producción y seguridad

- Producción: `https://tecnimontacargas.com`.
- Alias `www` redirige al dominio principal.
- No guardar ni mostrar credenciales, tokens, `.env`, `wp-config.php`, SQL con
  datos personales, certificados o claves.
- No actualizar WordPress, temas o plugins salvo solicitud explícita.
- Antes de cambiar producción, inspeccionar el punto exacto y ejecutar
  `./scripts/sync-production.sh --check`.
- Si existe deriva, detener el despliegue y comparar. Usar `--pull` solo para
  incorporar una modificación de emergencia hecha en producción y únicamente
  con las rutas protegidas limpias.
- Crear backup de base de datos y del componente afectado antes de toda escritura
  o eliminación material en producción. Informar las rutas de respaldo.
- Aplicar parches mínimos y desplegar solo archivos modificados. Nunca desplegar
  desde `.codex-tmp/` ni reemplazar el child theme completo.
- Después del despliegue, validar sintaxis, caché, HTTP, navegador y ejecutar de
  nuevo `./scripts/sync-production.sh --check`.

### Infraestructura productiva

- VPS Vultr `149.28.97.249`, región Miami, Alpine Linux y Docker/OpenRC.
- Stack: `/opt/tecnimontacargas`.
- Compose: `/opt/tecnimontacargas/docker-compose.prod.yml`.
- WordPress/OpenLiteSpeed: contenedor `tmd_ols_wordpress`, ruta
  `/var/www/vhosts/localhost/html`.
- MariaDB: contenedor `tmd_db`, sin exposición pública.
- phpMyAdmin: `tmd_phpmyadmin`, enlazado solo a `127.0.0.1:8081`.
- Administración OpenLiteSpeed: solo `127.0.0.1:7080`.
- Los volúmenes Docker son externos y persistentes.
- HTTPS es obligatorio. No cambiar DNS, TLS, contenedores, volúmenes o Compose
  fuera de una tarea explícita de infraestructura.

### Sincronización con producción

- `./scripts/sync-production.sh --check` descarga a temporal y compara tema
  hijo, plugins propios, contenido público, inventarios y Compose. Código `0`
  significa coincidencia; código `1`, deriva.
- `./scripts/sync-production.sh --pull` incorpora cambios remotos de emergencia
  y debe abortar si sobrescribiría rutas productivas con cambios locales.
- Flujo normal: `--check` → editar fuente canónica → backup DB/componente →
  desplegar solo archivos tocados → validar → `--check` → commit/push si fueron
  solicitados.
- El snapshot no cubre por sí solo todo el estado de MariaDB.
- Estado conocido al 29 de julio de 2026: el código propio coincidía, pero
  `production-snapshot/pages.json`, `plugins.json` y `SHA256SUMS` tenían deriva.
  El exportador también consultaba la tabla ya eliminada
  `SERVMASK_PREFIX_snippets`. Corregir esa tolerancia y refrescar el snapshot
  antes del próximo despliegue.

## Fuentes canónicas del código

- Tema activo: `wp-content/themes/blocksy-child/`.
- Plugins propios versionados:
  `tm-chatbot-fase1`, `tm-equipos-destacados-v2`,
  `tm-popup-bienvenida` y `tm-quiz-equipo-ideal`.
- `production-snapshot/` sirve para auditoría; no sustituye MariaDB ni backups.
- `tmd-site-kit/` es histórico e inactivo. `.codex-tmp/` es temporal.
- Código de inventario:
  `inc/tmd-inventory-api.php`, `assets/js/tmd-inventory-api.js` y
  `assets/css/tmd-inventory-api.css`.
- SEO y cuenta: `inc/tmd-seo.php` e `inc/tmd-account.php`.
- Header/footer: `template-parts/tmd-header.php` y
  `template-parts/tmd-footer.php`.
- Guías: `inc/tmd-equipment-type-guides.php`.
- Quiz: editar `wp-content/plugins/tm-quiz-equipo-ideal/`; no volver a registrar
  su shortcode desde una copia histórica del tema.
- Preservar cambios locales ajenos. No usar `git add .`; seleccionar rutas.
- No hacer commit, push o despliegue si el usuario no lo pidió.

### Qué nunca se versiona

- Credenciales, `.env`, `wp-config.php`, tokens, TLS o claves privadas.
- WordPress core, plugins de terceros, uploads, cachés o backups SQL completos.
- `production-snapshot/` es auditoría legible; recuperación completa requiere
  Git, uploads y backup MariaDB externo.

## Reglas funcionales vigentes

- Marca pública exacta: `Tecnimontacargas`.
- Experiencia: 26 años.
- Cobertura de alquiler y mantenimiento: toda Colombia.
- Alquiler únicamente mensual o por contratos de años; nunca por horas o días.
- Equipos entregados sin operador.
- La venta es únicamente de equipos usados disponibles en Firebase.
- No inventar equipos, marcas, modelos, imágenes ni disponibilidad en WordPress.
  Corregir datos de inventario en Inventario/Firebase.
- Catálogos WordPress aceptan solo `montacargas` y `bateria` con
  `estado.codigo === 1`.
- Los CPT históricos `tmd_equipo` y `tmd_energia` no son inventario y no deben
  recrearse.

### Identidad visual

- Tipografía principal: Work Sans.
- Colores corporativos:
  - azul empresarial `#128CEB`;
  - azul americano `#262E4F`;
  - amarillo imperial `#FFC33C`;
  - azul metálico `#5E748B`;
  - platino `#E6E6E6`;
  - texto oscuro `#3C3C3C`.
- Mantener diseño mobile-first, navegación por teclado, contraste legible y
  ausencia de overflow horizontal.
- No rediseñar globalmente ni convertir contenido masivamente sin una tarea
  separada y regresión visual.

## URLs y biblioteca de equipos

- `/equipos/tipos/` y sus hijas son la única biblioteca educativa canónica.
- La biblioteca contiene una portada y 13 páginas hijas.
- Mantener `/bms/` como redirección 301 a `/energia/bms/`.
- Mantener `/nosotros/` como redirección 301 a
  `/nosotros/quienes-somos/`.
- `/tienda/` redirige temporalmente 302 a `/equipos/`.
- `/carrito/` y `/finalizar-compra/` redirigen temporalmente 302 a
  `/mi-cuenta/`.
- `/repuestos/` permanece fuera de producción y devuelve 404.

## Navegación y componentes

- Menú principal vigente: `INICIO`, `EQUIPOS`, `ENERGÍA`, `SERVICIOS`,
  `NOSOTROS`.
- Equipos muestra Estibadores y Apiladores, Reach/Retráctiles, Tomapedidos y
  Contrabalanceados.
- Energía muestra Baterías de Plomo, BMS y Cargadores.
- El mega menú abre por hover, foco o clic; cierra con Escape o clic exterior;
  permanece abierto durante interacción interna.
- El hero del Home usa video silenciado sin bucle: reproduce una vez y conserva
  el último cuadro. Sus atributos están serializados en `post_content` de la
  página 47; verificar Chrome, Safari y móvil tras cambios.
- El carrusel de marcas muestra 3 logos y avanza de 3 en 3 en escritorio.
- No reemplazar el footer completo. Corregir bloques/enlaces puntuales y
  conservar markup y clases; ya hubo una regresión por sustitución total.

## Inventario y Firebase

- Fuente real: proyecto Firebase `inventariomaquinas-t` y workspace relacionado
  `/Users/lauracatalinapreciadoballen/Desktop/Inventario`.
- Endpoint general:
  `https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposWordpress`.
- Endpoint destacados:
  `https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposDestacadosWordpress`.
- Ambos aceptan solo `montacargas`/`bateria` disponibles. Destacados devuelve
  máximo 5 y nunca debe mostrar `Alquilado`.
- Functions Gen2 son públicas; los 403 corresponden a la allowlist de la
  aplicación, no a Firebase Auth.
- Caché WordPress de inventario y destacados: 24 horas, con última respuesta
  válida como fallback. No añadir sondeos periódicos ni recargas de página.
- Filtros y paginación funcionan en navegador sobre tarjetas ya renderizadas.
- Para Functions, probar desde `Inventario/functions`, pero desplegar desde la
  raíz `Inventario` con target dirigido. No desplegar todas las Functions por
  accidente.
- Las cifras verificadas el 28 de julio de 2026 fueron 123 registros válidos:
  37 montacargas y 86 baterías; 5 destacados válidos. Volver a consultar antes
  de presentarlas como actuales.

## Formularios, cuenta y correo

- Contacto: página ID 57 y Contact Form 7 ID 14; Flamingo conserva solicitudes.
- Parámetros vigentes de prellenado:
  `?equipo=<nombre>` y `?tmd_cotizacion_energia=<nombre>`.
- No reintroducir el parámetro conflictivo `?energia=`.
- `wp_mail()` aceptado no demuestra entrega. WPO365/Microsoft Graph tenía
  autorización expirada/revocada; reautorizar y comprobar una bandeja real
  antes de declarar correo operativo.
- El chatbot Fase 1 usa todavía un número placeholder; no afirmar transferencia
  real a asesor hasta corregir configuración.

## SEO

- Rank Math es la autoridad para title, description, canonical, robots, Open
  Graph, schema y sitemap.
- Conservar metadatos únicos y la prioridad comercial del alquiler.
- Mantener `Service` para alquiler y mantenimiento; `BlogPosting` solo en
  artículos. Evitar `Article` o perfiles de autor en páginas corporativas.
- Tras cambios SEO, invalidar sitemap de Rank Math, purgar WordPress/LiteSpeed y comprobar HTML real, canonical, robots, schema y sitemap.
- Las URLs indexables deben responder 200 y figurar solo una vez en sitemap.
  Redirecciones, cuenta y utilidades comerciales no se indexan.
- No alterar hechos de negocio para perseguir palabras clave.
- Estado verificado el 29 de julio de 2026: metadatos propios para 37 páginas y
  6 artículos; 33 páginas indexables y 6 artículos sin faltantes; sitemap de 39
  URLs, todas HTTP 200. Volver a medir antes de presentar estas cifras como
  actuales.
- Sitemap permitido: páginas y artículos. No publicar sitemaps de categorías,
  CPT históricos o utilidades WooCommerce.
- Schema vigente: `Organization`/`LocalBusiness` + `WebPage`; `Service` en
  alquiler/mantenimiento; `BlogPosting` solo en artículos. No añadir `Article`
  ni perfiles de autor a páginas corporativas.
- Google Search Console aún no estaba configurado. Cuando se autorice, registrar
  dominio y enviar `https://tecnimontacargas.com/sitemap_index.xml`.

## WooCommerce y comercio pausado

- WooCommerce permanece activo únicamente para registro, inicio de sesión y
  gestión básica de usuario en `/mi-cuenta/`.
- `tm-popup-bienvenida` permanece inactivo.
- Tienda, carrito, checkout, pagos, cupones, repuestos y navegación comercial
  permanecen desactivados. Sus redirecciones/noindex deben conservarse.
- No instalar pasarelas ni publicar productos/repuestos sin autorización y una fuente de datos real aprobada.
- Eliminar recursos WooCommerce fuera de `/mi-cuenta/`, pero conservar los
  assets necesarios para login y registro.

## Estado WordPress conocido

- Última referencia verificada: WordPress `7.0.2`, tema hijo
  `blocksy-child 1.0.0`, tema padre Blocksy `2.1.50`.
- Prefijo MariaDB: `SERVMASK_PREFIX_`.
- `DISALLOW_FILE_EDIT=true`, `FORCE_SSL_ADMIN=true`,
  `WP_POST_REVISIONS=10`, `AUTOSAVE_INTERVAL=120`.
- El registro general de WordPress está cerrado; el registro WooCommerce en
  `/mi-cuenta/` está habilitado.
- Los plugins propios y versiones observadas fueron:
  `tm-chatbot-fase1 1.0.0`, `tm-equipos-destacados-v2 2.2.0`,
  `tm-popup-bienvenida 1.2.2` inactivo y
  `tm-quiz-equipo-ideal 3.0.0`.
- No asumir que estas versiones siguen vigentes: comprobar WP-CLI antes de
  reportarlas o intervenir.

## Validación mínima

- Ejecutar `php -l` en cada PHP modificado y la comprobación focalizada
  correspondiente para JS/CSS.
- Verificar códigos HTTP y destinos exactos de redirección.
- Si cambia SEO: revisar title, description, canonical, robots, Open Graph,
  JSON-LD y `sitemap_index.xml`.
- Si cambia header, cuenta o WooCommerce: probar escritorio y móvil, sin
  overflow, sin iconos de carrito y con registro/login funcional.
- Purgar caché antes de afirmar el estado público.
- Distinguir con claridad lo verificado localmente de lo verificado en
  producción.
- Para inventario, comprobar `invalid: 0` y que todo
  `estado.codigo === 1`.
- Para correo, confirmar recepción real; no basta el retorno de `wp_mail()`.
- Para cambios de contenido almacenado en DB, actualizar
  `production-snapshot/` y revisar hashes.

## Pendientes reales conocidos

- Hacer tolerante el exportador a la ausencia de
  `SERVMASK_PREFIX_snippets`, refrescar `production-snapshot/` y obtener
  `sync-production.sh --check` en código `0`.
- Reautorizar WPO365/Microsoft Graph y validar recepción real de Contact Form 7 y quiz.
- Corregir marcas/modelos/imágenes `N/A` desde Inventario/Firebase.
- Sustituir el número placeholder del chatbot antes de ofrecer transferencia a asesor.
- Vacantes/postulación completa solo si el usuario decide desarrollar ese flujo.

## Comunicación y mantenimiento

- Tras cambios materiales, actualizar este `AGENTS.md` si cambió una regla
  permanente o estado operativo esencial.
- Escribir handoffs y reportes en español, con rutas, comprobaciones, riesgos y
  pendientes reales. Nunca copiar secretos.
- Consultar documentación vigente con Context7 cuando una tarea dependa de una librería, framework, SDK, API, CLI o servicio cloud.
