# SPEC: Maqueta v2 de alquiler y venta de montacargas eléctricos

## Estado

- Aprobado para implementación y despliegue por la instrucción del usuario del 2026-10-05 de implementar la maqueta adjunta y priorizarla sobre las decisiones anteriores de la landing.
- Fuente de diseño y contenido: `/Users/lauracatalinapreciadoballen/Downloads/maqueta-completa-tecnimontacargas-referencias-integradas.md` (12 bloques).
- Se conserva la referencia a servicio técnico desde 2000, ya publicada actualmente. La cifra de 120 equipos queda oculta hasta que una fuente vigente confirme el tamaño de la flota.
- Yale no se anuncia como parte de la flota disponible: no aparece en el inventario activo consultado. El H3 de marcas se limita a Crown, Clark, Jungheinrich y Hyster, presentes en el inventario activo.
- Este SPEC reemplaza las decisiones anteriores de diseño y mínimo de alquiler únicamente para la página `/alquiler-montacargas-electricos/` (ID 1558). No modifica la página de baterías.

## Objetivo

Implementar en la página publicada 1558 la estructura, textos, composición visual, paleta y comportamiento adaptable descritos en la maqueta adjunta. Reutilizar las fotografías limpias y las imágenes de equipos suministradas en `Downloads/pageTMD`; no publicar capturas con texto/logos incrustados ni presentar imágenes de referencia como disponibilidad de inventario.

## Contrato funcional y visual

1. La página tiene doce bloques en el orden de la maqueta. El hero usa el H1 “Venta o alquiler de montacargas eléctricos”, sin header global, footer global, logo duplicado ni CTA. La palabra “eléctricos” usa el acento amarillo; el resto del hero sigue la variante oscura.
2. El contenido editorial usa Work Sans y la paleta `#128CEB`, `#262E4F`, `#FFC33C`, `#5E748B`, `#3C3C3C`, `#E6E6E6` y blanco. El diseño fluye según contenido, respeta móvil y no genera overflow horizontal.
3. El bloque descriptivo usa el título de la maqueta. Las marcas y cifras que describen la flota se publican solo cuando una fuente vigente confirma su validez; por ahora se omiten 120 equipos y Yale.
4. Necesidades, comparación entre alquiler y compra, proceso, sectores, usos y respaldo técnico conservan los textos de la opción A y la jerarquía definidos en la maqueta. No se agregan sectores, modelos ni condiciones comerciales inventados.
5. El bloque de inventario consume la fuente canónica Inventario/Firebase y muestra como máximo cinco referencias publicables. Los datos estáticos e imágenes de la maqueta nunca se presentan como stock disponible. El enlace usa el catálogo real `/equipos/`.
6. La sección compartida de cotización y FAQ conserva los seis textos de la maqueta, es una sola franja visual oscura en escritorio y se apila en móvil. El formulario CF7 1556 tiene cinco controles agrupados: nombre/cargo; empresa/ciudad; correo o celular; tipo de equipo o necesidad; y requerimientos de carga, altura y pasillo. Conserva consentimiento, privacidad, honeypot y protección anti-spam existentes.
7. El blog muestra únicamente publicaciones reales. Título y subtítulo siguen la maqueta.
8. El periodo mínimo para esta página pasa a 15 días, desde los cuales la cotización fija el periodo por meses. El equipo se ofrece sin operador. La página no ofrece horas ni periodos inferiores a 15 días.
9. El H1 y el contenido quedan editables en bloques de WordPress. Los shortcodes dinámicos de inventario y blog siguen siendo la fuente de sus datos.
10. Rank Math permanece como autoridad de title, description, canonical, schema y sitemap.

## Alcance

- Código: `scripts/create-commercial-landing-pages.php`, `wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php`, `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css` y activos visuales seleccionados del catálogo.
- Contenido: página publicada ID 1558, slug `alquiler-montacargas-electricos` y formulario Contact Form 7 ID 1556.
- Documentación: reglas de negocio y este SPEC/plan.
- Se excluyen el formulario ID 14, la página de baterías ID 1559, menús globales, plugins de terceros, inventario almacenado y cualquier otro contenido.

## Aceptación y evidencia

- El DOM público contiene doce bloques en el orden aprobado, H1 único, seis FAQ y los textos íntegros.
- El header/footer global solo se ocultan cuando la página contiene el marcador v2; las otras landings conservan su presentación.
- Imágenes cargan desde rutas versionadas del child theme y corresponden a las referencias visuales descritas. Las fotos de catálogo de modelos solo ilustran tipo de equipo.
- Inventario y publicaciones salen de sus fuentes reales; no hay afirmaciones de stock derivadas de imágenes.
- El formulario se presenta con cinco campos solicitados y conserva sus controles de privacidad y anti-spam. No se alteran destinatario ni cabeceras de correo fuera del mapeo necesario para los nuevos campos.
- El mínimo publicado es 15 días y no quedan condiciones anteriores de un mes en el contenido de la página o en sus metadatos.
- Verificación focal de PHP, CSS, diff, hashes y estado de sincronización; luego backup verificado, escritura del ID 1558/CF7 1556, purga puntual, HTTP/HTML público y comparación final de código.
- La revisión visual de escritorio y móvil se reporta solo con captura/render de navegador real. Si esta herramienta no está disponible, se deja explícitamente pendiente.

## Límites y rollback

- Antes de escribir contenido: backup completo MariaDB reciente y verificado, snapshot privado del contenido/metadatos del ID 1558 y del formulario 1556, hashes de origen y resultado aprobados.
- El actualizador acepta solo IDs, slugs, estado y hashes esperados; adquiere lock, actualiza contenido/metadatos/formulario como una operación controlada, comprueba el estado final y restaura las capturas ante fallo.
- Código se despliega solo por el flujo GitHub Actions de `main`, limitado al manifiesto. Contenido y multimedia tienen gates separados; los assets se despliegan como archivos versionados del child theme.
- Producción no se usa para investigar mediante escritura. No se ejecutan migraciones ni modificaciones de infraestructura.
