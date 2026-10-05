# SPEC: Maqueta v2 de alquiler de montacargas eléctricos

## Estado

- Aprobado para implementar y desplegar la versión más reciente de la maqueta por las instrucciones explícitas del usuario del 2026-10-05.
- Fuente de diseño y contenido: `/Users/lauracatalinapreciadoballen/Downloads/maqueta-completa-tecnimontacargas-referencias-integradas.md` (12 bloques).
- El usuario confirmó que la cifra «120 equipos en flota propia» es válida y que Yale está disponible para anunciarse. La consulta al inventario del 2026-10-05 no corrobora estas afirmaciones (110 equipos en total, 23 montacargas activos y Yale ausente); se conserva la decisión comercial explícita del usuario y el bloque de inventario continúa mostrando exclusivamente datos dinámicos de la fuente canónica.
- Para resolver la advertencia de contraste de la maqueta, solo el fondo del CTA de inventario usa `#0D70BD` con texto blanco (5.16:1); el resto mantiene `#128CEB` y la paleta indicada. Esta variante implementa la instrucción del usuario de corregir los hallazgos pendientes.
- Este SPEC reemplaza las decisiones anteriores de diseño y mínimo de alquiler únicamente para la página `/alquiler-montacargas-electricos/` (ID 1558). No modifica la página de baterías.
- La fuente específica que aplica los estilos v2 es `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css`. El tema la carga desde `inc/tmd-commercial-landing-pages.php` cuando la página tiene el marcador v2; `assets/css/tmd-commercial-landings.css` aporta los estilos compartidos.
- Enmienda DEC-12 aprobada — 2026-10-05. La instrucción directa del usuario sustituye el H1 anterior por “Alquiler de montacargas eléctricos”. En el bloque 07 se mantiene el diseño actual y se ajusta el color del texto inicial del H2 a blanco, con el acento amarillo existente; las cinco tarjetas usan los títulos y descripciones de la captura de referencia: Logística — “Recepción, almacenamiento y despacho para varios clientes.”; Alimentos y bebidas — “Operaciones continuas y entornos exigentes.”; Manufactura — “Materia prima y producto terminado entre planta y bodega.”; Retail — “Reposición y distribución desde centros propios.”; Construcción — “Movimiento de materiales para obras y proyectos.” La tarjeta de Construcción utiliza el activo suministrado `montacargas-construccion.webp`. La captura orienta texto e imagen; no sustituye el diseño ni los estilos actuales.
- Enmienda DEC-13 aprobada — 2026-10-05. La instrucción más reciente del usuario corrige el criterio anterior de ocultar elementos globales: la navegación/header del sitio y el footer deben mostrarse también en esta landing, igual que en las demás páginas. El encabezado duplicado de la entrada (`.entry-header`) y el rail de contacto conservan sus ocultamientos específicos de esta landing.

## Objetivo

Implementar en la página publicada 1558 la estructura, textos, composición visual, paleta y comportamiento adaptable descritos en la maqueta adjunta. Reutilizar las fotografías limpias del material suministrado y las imágenes de equipos que están en `EQUIPOS SEGUN REFERENCIA/`; copiar y optimizar únicamente las referencias usadas en `wp-content/themes/blocksy-child/assets/img/commercial-landings-v2/`. No publicar capturas con texto/logos incrustados ni presentar imágenes de referencia como disponibilidad de inventario.

## Contrato funcional y visual

1. La página tiene doce bloques en el orden de la maqueta. El hero usa el H1 “Alquiler de montacargas eléctricos”, debajo de la navegación global visible y sin logo duplicado ni CTA dentro del hero. El footer global también permanece visible después del contenido. La palabra “eléctricos” usa el acento amarillo; el resto del hero sigue la variante oscura.
2. El contenido editorial usa Work Sans y la paleta `#128CEB`, `#262E4F`, `#FFC33C`, `#5E748B`, `#3C3C3C`, `#E6E6E6` y blanco. El diseño fluye según contenido, respeta móvil y no genera overflow horizontal.
3. El bloque descriptivo reproduce el título, la lista de marcas y los tres indicadores exactos de la maqueta, también repetidos en el hero. El usuario confirmó 120 equipos y Yale antes del write productivo.
4. Necesidades, comparación entre alquiler y compra, proceso, sectores, usos y respaldo técnico conservan los textos de la opción A y la jerarquía definidos en la maqueta, con los textos de las cinco tarjetas del bloque de sectores fijados en DEC-12. No se agregan sectores, modelos ni condiciones comerciales inventados.
5. El bloque de inventario consume la fuente canónica Inventario/Firebase y muestra como máximo cinco referencias publicables. Los datos estáticos e imágenes de la maqueta nunca se presentan como stock disponible. El enlace usa el catálogo real `/equipos/`.
6. La sección compartida de cotización y FAQ conserva los seis textos de la maqueta, es una sola franja visual oscura en escritorio y se apila en móvil. El formulario CF7 1556 tiene cinco controles: nombre y cargo; empresa y ciudad; correo o celular; tipo de equipo o necesidad; y requerimientos de carga, altura y pasillo. Conserva la nota/enlace de privacidad, el honeypot y la protección anti-spam; no añade el checkbox de aceptación que la maqueta no especifica. Retiene los estados de obligatoriedad observados en el formulario público actual y no añade textos de ejemplo.
7. El blog muestra únicamente publicaciones reales. Título y subtítulo siguen la maqueta.
8. El periodo mínimo para esta página pasa a 15 días, desde los cuales la cotización fija el periodo por meses. El equipo se ofrece sin operador. La página no ofrece horas ni periodos inferiores a 15 días.
9. El H1 y el contenido quedan editables en bloques de WordPress. Los shortcodes dinámicos de inventario y blog siguen siendo la fuente de sus datos.
10. Rank Math permanece como autoridad de title, description, canonical, schema y sitemap.

## Alcance

- Código: `scripts/create-commercial-landing-pages.php`, `scripts/commercial-landing-rental-v2.php`, `wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php`, `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`, `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css` y activos visuales seleccionados del catálogo o aportados explícitamente para esta página.
- Contenido: página publicada ID 1558, slug `alquiler-montacargas-electricos` y formulario Contact Form 7 ID 1556.
- Documentación: reglas de negocio y este SPEC/plan.
- Se excluyen el formulario ID 14, la página de baterías ID 1559, menús globales, plugins de terceros, inventario almacenado y cualquier otro contenido.

## Aceptación y evidencia

- El DOM público contiene doce bloques en el orden aprobado, H1 único, seis FAQ y los textos íntegros.
- El H1 es “Alquiler de montacargas eléctricos”. El bloque 07 conserva su composición actual, muestra en blanco el texto inicial del H2 y contiene cinco tarjetas con las etiquetas y descripciones de DEC-12; Construcción carga `montacargas-construccion.webp`.
- La navegación/header y el footer global se muestran en esta landing y en el resto del sitio; `.entry-header` y `.tmd-contact-rail` conservan sus reglas específicas para la landing v2.
- Imágenes cargan desde rutas versionadas del child theme y corresponden a las referencias visuales descritas. Las cuatro ilustraciones de tipo de equipo se copian y optimizan desde `EQUIPOS SEGUN REFERENCIA/`: `EFG425/2.png` (contrabalanceado), `CROWN RR/1.png` (reach), el cuadrante superior izquierdo de `CROWN RD 5220/4.png` (doble profundidad) y `CROWN PE 4000-60/2.png` (traslado a nivel de piso). Las fotos de catálogo de modelos solo ilustran tipo de equipo.
- Inventario y publicaciones salen de sus fuentes reales; no hay afirmaciones de stock derivadas de imágenes.
- El formulario presenta exactamente cinco entradas de texto/área con las etiquetas compuestas de la maqueta, conserva nota/enlace de privacidad y controles anti-spam, y no cambia destinatario ni cabeceras de correo fuera del mapeo del cuerpo. No añade placeholders ni requisitos nuevos; los campos obligatorios existentes conservan su estado y no se muestra un checkbox de aceptación.
- El mínimo publicado es 15 días y no quedan condiciones anteriores de un mes en el contenido de la página o en sus metadatos.
- Verificación focal de PHP, CSS, estructura de los doce bloques, seis FAQ, formulario, runner, hashes y estado de sincronización; luego backup verificado, confirmación comercial del usuario, contraste accesible, escritura del ID 1558/CF7 1556, purga puntual, HTTP/HTML público y comparación final de código.
- La revisión visual de escritorio y móvil se reporta solo con captura/render de navegador real. Si esta herramienta no está disponible, se deja explícitamente pendiente.

## Límites y rollback

- Antes de escribir contenido: backup completo MariaDB reciente y verificado, snapshot privado del contenido/metadatos del ID 1558 y del formulario 1556, hashes de origen y resultado aprobados.
- El actualizador acepta solo IDs, slugs, estado y hashes esperados; en `execute` también exige `TMD_RENTAL_V2_COMMERCIAL_CLAIMS_CONFIRMED=yes` y `TMD_RENTAL_V2_CONTRAST_APPROVED=yes`. Fija hashes del formulario y metadatos, bloquea y vuelve a comprobar sus filas dentro de la transacción, y reconstruye CF7 después de limpiar caché. Ante error, concilia el estado persistido con los hashes de origen/destino; un estado mixto detiene nuevos writes y conserva el artefacto privado para restauración controlada. El modo `dry-run` informa esos gates y no escribe.
- Código se despliega solo por el flujo GitHub Actions de `main`, limitado al manifiesto. Contenido y multimedia tienen gates separados; los assets se despliegan como archivos versionados del child theme.
- Producción no se usa para investigar mediante escritura. No se ejecutan migraciones ni modificaciones de infraestructura.
