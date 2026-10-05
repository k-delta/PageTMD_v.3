# SPEC: Páginas de alquiler de montacargas y baterías

## Estado

- Versión base aprobada — 2026-10-04. La instrucción del usuario «implementa el spec» aprueba las rutas y la dirección visual de la primera versión, con posibilidad de ajustar después textos e imágenes.
- Enmienda DEC-10 aprobada — 2026-10-04. El usuario aprobó la versión escrita del SPEC para los iconos de los tres textos existentes del hero de baterías.
- Enmienda DEC-11 terminada — 2026-10-05. La solicitud «implementa esta maqueta» y la aclaración «los cambios de este markdown son superiores a los anteriores hechos» autorizaron que `/Users/lauracatalinapreciadoballen/Downloads/02-baterias-maqueta-9-secciones-tecnimontacargas.md` reemplazara la dirección de contenido y diseño anterior de `/baterias-para-montacargas/`. Sustituye para baterías el hero, sus tres apoyos DEC-10, todos los bloques y preguntas frecuentes anteriores, y los requisitos 15–19, 25, 26 y 28 que entren en conflicto. La página de alquiler, la navegación global, los destinos existentes de energía y la capa global del sitio quedaron fuera de esta sustitución.

### DEC-11 — Maqueta de nueve secciones para baterías

[Solicitud del usuario: 2026-10-05] Implementar en `/baterias-para-montacargas/` las nueve secciones del Markdown adjunto, en su orden y con sus textos editables: banner sin CTA, meta ni iconos; soluciones de plomo-ácido, cargadores y BMS; diferencial; ventajas con opción A; proceso de tres pasos; galería de cinco imágenes sin leyendas; cotización; seis FAQ cerradas por defecto; y blog con sus dos encabezados y únicamente artículos publicados reales si los hay. No publicar notas de maqueta, placeholders, tarjetas vacías, titulares o enlaces inventados. El contenedor, la paleta y la tipografía siguen las especificaciones del documento. Mantener la cabecera y el pie globales existentes.

[Regla vigente: `scripts/update-privacy-policy-final.php:76-78`] El formulario conserva la autorización requerida por la política actual. La casilla es un control de tratamiento de datos independiente de los seis campos comerciales; el aviso de privacidad del Markdown conserva su texto exacto y el enlace apunta a la política vigente. No cambiar la política ni retirar la autorización como parte de esta maqueta.

[Criterio de implementación] Las seis etiquetas del formulario se mantienen como seis campos visibles y compuestos; selector de compra/alquiler con esas dos opciones. No se añaden placeholders ni valores de ejemplo. La configuración de validación existente se conserva en lo posible. Las afirmaciones comerciales se copian de la maqueta del usuario y no se deducen de imágenes. Los activos publicados deben proceder del catálogo entregado y no pueden llevar texto incrustado de una captura.

[Criterio de publicación resuelto — 2026-10-05] Se revisó el catálogo antes de escribir en producción. Sus fotos representan montacargas, no celdas, baterías ni conectores identificables; la página combina copias optimizadas del catálogo con activos de batería y monitoreo existentes, y la galería usa un encabezado neutral. Las fuentes del catálogo permanecieron intactas. El navegador cargó todas las imágenes publicadas sin errores.

[Resolución DEC-11: 2026-10-05] El catálogo `EQUIPOS SEGUN REFERENCIA/` contiene fotografías de montacargas, no fotografías identificables de celdas, baterías y conectores. Por eso la galería combina dos copias WebP del catálogo para mostrar equipos eléctricos con los activos de batería/monitoreo ya publicados en el tema; su encabezado describe esa mezcla de forma neutral y no atribuye las fotos a Barbillon. Las tarjetas de soluciones conservan las imágenes de producto ya usadas en el sitio.

[Decisión aprobada por el usuario: 2026-10-05] El CTA de esta maqueta usa fondo azul oscuro `#262E4F` y texto blanco. La aprobación resuelve el contraste; no cambia la paleta de los demás botones o enlaces.

[Aclaración del usuario: 2026-10-05] Las fotografías de catálogo que se pueden usar están en `EQUIPOS SEGUN REFERENCIA/` en la raíz del proyecto. Copiar al directorio dedicado `wp-content/themes/blocksy-child/assets/img/commercial-landings/baterias-maqueta/` solo las fotografías de catálogo usadas por la página y servir copias WebP optimizadas; dejar intactas las fuentes del catálogo.

### Ejecución y verificación de DEC-11 — 2026-10-05

- La página publicada ID 1559 (`/baterias-para-montacargas/`) y el formulario CF7 ID 1557 se actualizaron dentro de una transacción después de verificar los hashes de origen y destino. El backup completo MariaDB y el snapshot previo acotado a esos IDs se validaron antes de escribir; el snapshot conserva los hashes de origen `470724e8b08cd126cbac75c5f69d361a64e9521ab3b441eeda27442fd630b201` y `85c662a8bd75e463b15f960b5ba746e62dcac3d25aec9465fd0b84cd5063b9fc` (página y formulario, respectivamente).
- Los hashes persistidos verificados son `9659b90817435d63573562befc15d340cd3f91133d85410b5d3e34bae26b9475` para la página y `acc47f51da1372fbb1a00a00ae824960b161c1076e6701c6f4ccdc41ea29477a` para el formulario. CF7 normaliza las propiedades de correo antes de comparar el destino; el guardado por WP-CLI conserva la integración Sendinblue existente sin añadirle propiedades predeterminadas. El callback vuelve a quedar registrado al terminar.
- La URL canónica respondió HTTP 200 y aparece en `page-sitemap.xml`. El title, la descripción y el canonical de Rank Math se verificaron en el HTML público. Playwright confirmó nueve secciones, seis preguntas cerradas por defecto, los seis campos comerciales, consentimiento y honeypot, dos artículos publicados, CTA de envío `#262E4F` con texto blanco, carga de imágenes, ausencia de overflow horizontal y cero errores de consola en escritorio (1440 px) y móvil (390 px). No se envió el formulario.
- Se purgó la caché de la página 1559; la primera respuesta fue `MISS` y la siguiente `HIT`. El runtime del tema y sus assets se desplegó en el commit `3921c7d17192bff29b9a81552f3492043326b3ab` (run `37362919071`, éxito). El ejecutor WP-CLI del commit `8fefbb53c6a9d66f51a0327ce2eca7c8fcad9a9a` se transmitió desde el checkout de trabajo mediante `wp eval-file -`; no se copió al directorio público de WordPress. Aunque el run de Actions de ese commit quedó cancelado mientras estaba en cola, el push posterior sincronizó el checkout productivo limpio con `fcde7ff1dad8b5a21139c9c78b0ff0a9f55c9837` (run `37370063056`, éxito). Los commits posteriores al runtime incluyen solo la herramienta, sus pruebas y esta evidencia documental.

## Contexto

[Solicitud] Se solicitan dos páginas comerciales nuevas, construidas en WordPress con el texto y la estructura de los documentos entregados. Las guías visuales entregadas definen las imágenes y el lenguaje gráfico que debe usarse.

[Solicitud] La página de montacargas debe aparecer en la navegación bajo **Equipos** y la página de baterías bajo **Energía**. Como propuesta SEO, ambas usarán slugs descriptivos desde la raíz: `/alquiler-montacargas-electricos/` y `/baterias-para-montacargas/`. La ubicación en el menú no obliga a repetir el nombre de la categoría en la URL.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:201-203] El pie del mega menú de Equipos ya tiene dos enlaces: catálogo y encuentra tu equipo. El usuario señaló el espacio siguiente para la nueva página.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:228] El pie del mega menú de Energía ya enlaza al catálogo de energía. El usuario señaló el espacio siguiente para la nueva página.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:323-324] El menú móvil tiene acciones de Equipos separadas del mega menú de escritorio.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:356] El menú móvil tiene una acción de Energía que también debe considerarse.

[Verificación pública: 2026-10-04] La ruta `/energia/baterias/` responde HTTP 200. En la revisión con navegador muestra el título “Baterías” y una letra “a” como cuerpo antes del pie global; no presenta una página comercial desarrollada. Este hallazgo actualiza la recomendación de DEC-01 sobre su convivencia con la nueva ruta.

[Solicitud] Los textos fuente son `pageTMD/01 - Alquiler o venta  de montacargas eléctricos.docx` y `pageTMD/02 - Baterías para montacargas eléctricos.docx`. Las guías visuales son `pageTMD/EQUIPOS VISUALES.docx` y `pageTMD/Sugerencias para referencias visuales.docx`. Las fotografías de referencia están en `pageTMD/EQUIPOS SEGUN REFERENCIA/`.

[Decisión del usuario: 2026-10-04] Omitir de la página de alquiler la franja de tres cifras (120 equipos, servicio técnico desde 2000 y periodo mínimo de 1 mes) y la nota de marcas inmediatamente posterior. El mínimo de alquiler se conserva en el banner y los demás bloques aprobados; la retirada no elimina las secciones de respaldo técnico o preguntas frecuentes.

[Solicitud y diseño aprobados en conversación: 2026-10-04] En `/baterias-para-montacargas/`, añadir iconos SVG lineales azules de 18 px a los tres textos existentes del hero: marcador para “Cobertura nacional en Colombia”, bodega para “Instalación en la bodega del cliente” y calendario para “Alquiler desde 1 mes”. Mantener los textos sin cambios, tratar los SVG como decorativos para lectores de pantalla y conservar el ajuste de la fila en móvil.

Mapa de navegación propuesto (jerarquía del menú; las rutas de las páginas nuevas son independientes de esa jerarquía):

```text
INICIO /
├── EQUIPOS /equipos/
│   ├── Catálogo de equipos /equipos/
│   └── Alquiler y venta de montacargas eléctricos
│       /alquiler-montacargas-electricos/ [nueva]
└── ENERGÍA /energia/
    ├── Baterías /energia/baterias/ [ruta existente]
    ├── Baterías para montacargas eléctricos
    │   /baterias-para-montacargas/ [nueva]
    ├── Baterías de plomo /energia/baterias/plomo/
    ├── BMS /energia/bms/
    └── Cargadores /energia/cargadores/
```

### Análisis de los estilos existentes

[Solicitud] Analizar las otras páginas del sitio y los cuatro Word para definir el diseño de estas dos vistas.

[Verificación pública: 2026-10-04] Se consultaron con navegador Inicio, Equipos, Energía, Baterías, Baterías de plomo, BMS, Cargadores y la guía de contrabalanceados. Las ocho rutas respondieron HTTP 200. Se revisaron capturas de escritorio a 1440 px, muestras de Inicio/Plomo/Guías a 390 px y estilos calculados. La revisión es una referencia visual; no certifica todos los estados del sitio. Algunas capturas iniciales mostraron imágenes diferidas y el vídeo de Inicio no quedó evaluado.

| Aspecto | Evidencia actual | Aplicación a las nuevas páginas |
|---|---|---|
| Paleta | `theme.json:7-13` y `style.css:7-17` del child theme: azul `#128CEB`, navy `#262E4F`, amarillo `#FFC33C`, secundario `#5E748B`, texto `#3C3C3C`, gris `#E6E6E6` y blanco. | Mantener los colores de marca; los rojos de las capturas de los Word de contenido son referencias de estructura, no la paleta final. |
| Tipografía | El tema declara Work Sans (`theme.json:19-29,45,61,75`). El navegador muestra Work Sans en cuerpo y varios títulos de Equipos/Plomo/Cargadores, pero Syne en títulos de BMS/Guías y algunos de Inicio. | No asumir una fuente global uniforme. La propuesta de familia y escala común para las dos nuevas páginas figura en DEC-03. |
| Tarjetas y botones | `assets/css/tmd-brand-consistency-global.css:50-69,108-161`: tarjetas de 12 px, botones de 8 px, cuerpo/CTA de 16 px y sombras suaves. La capa excluye Baterías de plomo (ID401), BMS (ID792) y Cargadores (ID255), según `:1-4`. | Tomar esta familia como base propuesta; las variantes de Energía no deben mezclarse arbitrariamente con ella. |
| Páginas de energía | Plomo y Cargadores presentan texto a la izquierda, producto a la derecha y fondo claro. BMS combina hero navy, módulos claros y acentos azules/amarillos. | Aprovechar la alternancia claro/oscuro y las fotografías de producto. Los radios de 20–28 px y H1 de 64–72 px de algunas páginas son variantes locales. |
| Guías e inventario | Las guías tienen hero en dos columnas y CTA; el inventario presenta imágenes con datos y acciones. `assets/css/tmd-equipment-type-guides.css:58-86,150-178,533-611`. | Usar las guías como referencia de composición y el inventario como fuente de tarjetas reales. Los filtros del catálogo no son la estructura de estas páginas comerciales. |
| Contenido y formulario | Contenedores de 1180 px en guías y páginas internas de Energía. Contacto usa campos amplios y tarjeta de formulario (`style.css:1818-1863`). | Proponer un ancho y espaciado comunes, conservando los campos específicos de los Word. |

Las rutas de la tabla son relativas a `wp-content/themes/blocksy-child/`. Los estilos calculados públicos prevalecen para describir lo que se ve; las declaraciones locales explican la intención del tema, pero no prueban por sí solas su apariencia final.

### Cómo se aplican los documentos entregados

1. **Decisiones actuales del usuario:** mínimo 1 mes, Colombia, CTA en el banner, atención de baterías en la bodega del cliente y oferta sin litio. Sustituyen los textos anteriores que entren en conflicto.
2. **Word 01 y Word 02:** texto, orden de secciones, jerarquía H1/H2/H3, formularios y distribución de cada bloque. Sus capturas rojas sirven como referencia de maquetación.
3. **Sugerencias para referencias visuales:** dirección de imagen, encuadres, versiones claras/oscuras y uso visual de azul/amarillo. Sus 23 JPEG incluyen imágenes sin texto y ejemplos ya compuestos; no declaran una fuente web ni códigos HEX.
4. **EQUIPOS VISUALES y EQUIPOS SEGUN REFERENCIA:** candidatos para seleccionar los equipos y sus fotografías. El Word mezcla fotografías y representaciones de producto; no demuestra disponibilidad ni obliga a publicar todos sus modelos.

En las tablas siguientes, `imageN` identifica un archivo dentro de `word/media/` del DOCX indicado; no es una ruta pública ni un archivo nuevo del sitio.

| Bloques de montacargas | Estructura del Word 01 | Referencia de “Sugerencias para referencias visuales” |
|---|---|---|
| Banner | Título, apoyo y montacargas en bodega; añadir el CTA ya confirmado. | `image1.jpeg`–`image4.jpeg`: versiones clara/oscura, con y sin texto. |
| Descripción y cifras | Omitir la franja de tres tarjetas y la nota de marcas que seguía debajo, según la decisión del usuario del 2026-10-04. El mínimo mensual permanece en el banner y demás bloques aprobados. | `image9.jpeg` no se usa para crear una sección alternativa de cifras. |
| Necesidades de operación | Cuatro tarjetas enlazadas: muelle, pasillo angosto, doble profundidad y traslado a nivel de piso. | El bloque 3 está pendiente en la guía visual; usar la composición de `image7.jpg` del Word 01 con la paleta del sitio. |
| Alquilar o comprar | Foto y tres argumentos del texto aprobado. | `image10.jpeg`–`image11.jpeg`. |
| Selección de equipos | Carrusel limitado, datos reales y enlace al catálogo. | El bloque 5 está pendiente en la guía visual; `image3.jpg` del Word 01 define la composición. |
| Proceso | Cuatro pasos numerados. | `image12.jpeg`. |
| Sectores | Cuatro tarjetas: logística, retail, manufactura y distribución. | `image13.jpeg`–`image19.jpeg` orientan la composición. Las imágenes de alimentos/construcción no autorizan ampliar los sectores del Word 01. |
| Usos del alquiler | Cuatro argumentos y dato destacado, conservando mínimo mensual incluso en proyectos temporales. | `image20.jpeg`–`image21.jpeg`; su texto “por semanas” queda sustituido. |
| Respaldo técnico | Imagen y tres diferenciales del taller definidos en el Word 01. | `image22.jpeg`–`image23.jpeg`; no añadir un cuarto argumento solo por aparecer en el ejemplo visual. |
| Cotización, FAQ y relacionados | Formulario de cinco campos, seis preguntas y enlaces a artículos pertinentes. | `image1.jpg` del Word 01: proximidad visual formulario/FAQ, con adaptación a una columna en móvil. |

| Bloques de baterías | Estructura del Word 02 | Referencias visuales |
|---|---|---|
| Banner | Título, apoyo, batería protagonista y CTA confirmado. No duplicar una franja de beneficios; los tres textos existentes bajo el CTA reciben los iconos especificados en DEC-10. | `image5.jpeg`–`image8.jpeg` de “Sugerencias”: versiones clara/oscura, con y sin texto. |
| Soluciones | Tres tarjetas con flecha: plomo-ácido, cargadores y BMS, enlazadas a las páginas correspondientes. | `image8.jpg` del Word 02 solo define distribución; su tarjeta de litio no forma parte del contenido final. |
| Diferencial | Párrafo sobre oferta y diagnóstico previo a reemplazar; sin duplicar los cuatro iconos del ejemplo. | `image2.jpg` del Word 02. |
| Ventajas | Tres tarjetas: autonomía, celdas y carga entre turnos; conservar las condiciones del texto fuente, sin prometer rendimientos universales. | `image6.jpg` del Word 02 aporta la composición, no la cantidad de tarjetas de su captura. |
| Proceso | Tres pasos: datos del equipo, validación técnica y cotización. Añadir junto al proceso el texto confirmado sobre instalación/mantenimiento en la bodega del cliente y reflejarlo en FAQ. | `image1.jpg` del Word 02; su captura de cuatro pasos no reemplaza los tres pasos redactados. |
| Galería | Cinco fotos reales según el Word, sin texto visible incrustado; conservar alternativas accesibles descriptivas. | `image3.jpg` del Word 02; completar solo con imágenes pertinentes y verificadas. |
| Cotización, FAQ y relacionados | Formulario de seis campos junto a fotografía, seis preguntas en acordeón y artículos relacionados. Omitir los tres apoyos repetidos junto al formulario. | `image7.jpg` y `image4.jpg` del Word 02. |

## Problema

[Solicitud] El menú mostrado en las capturas no ofrece un acceso directo a las páginas de alquiler de montacargas y baterías para montacargas. El contenido comercial y sus referencias visuales ya fueron entregados, pero aún no se han convertido en páginas navegables.

## Objetivo

[Solicitud] Publicar dos páginas de destino editables en WordPress, accesibles desde los espacios señalados en los menús de escritorio y móvil, con contenido comercial verificable, estructura legible, referencias visuales aprobadas y metadatos SEO coherentes.

## Fuera del alcance

- [Solicitud] Publicar 120 fotografías o crear una galería con una imagen por cada equipo de la flota.
- [Solicitud] Mencionar o promocionar baterías de litio.
- [Solicitud] Publicar disponibilidad, precios, equipos, modelos o compatibilidades que no estén confirmados en la fuente canónica.
- [Regla: AGENTS.md] Modificar WordPress core, el tema padre o plugins de terceros.
- [Regla: AGENTS.md] Escribir en producción, desplegar, purgar caché o ejecutar migraciones sin la autorización y los controles productivos requeridos.
- [Solicitud] Cambiar páginas existentes fuera de las dos páginas nuevas y los enlaces de navegación incluidos en este SPEC, salvo decisión explícita posterior sobre `/energia/baterias/`.

## Requisitos funcionales

1. [Solicitud] Crear una página WordPress editable titulada “Alquiler y venta de montacargas eléctricos”, con la ruta propuesta `/alquiler-montacargas-electricos/` y ubicación de navegación bajo Equipos.
2. [Solicitud] Crear una página WordPress editable titulada “Baterías para montacargas eléctricos”, con la ruta propuesta `/baterias-para-montacargas/` y ubicación de navegación bajo Energía.
3. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:201-203] Añadir en el pie del mega menú de Equipos un enlace visible a la nueva página, junto a las acciones existentes.
4. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:228] Añadir en el pie del mega menú de Energía un enlace visible a la nueva página de baterías.
5. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:323-324] Añadir el acceso de alquiler de montacargas a las acciones del menú móvil de Equipos.
6. [Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:356] Añadir el acceso a baterías para montacargas a las acciones del menú móvil de Energía.
7. [Solicitud] Organizar el contenido de alquiler con las secciones aprobadas del DOCX correspondiente: banner, necesidades de operación, compra frente a alquiler, selección de equipos, proceso, sectores, situaciones de alquiler, respaldo técnico, cotización, preguntas frecuentes y artículos relacionados. Omitir la franja de cifras y la nota de marcas inmediatamente posterior.
8. [Solicitud] Mostrar en la página de alquiler un periodo mínimo de 1 mes de forma destacada y explicar que la cotización considera la operación y el periodo solicitado. Aplicarlo al banner, FAQ, proyectos temporales y textos adaptados de las imágenes; sustituir las menciones de alquiler por semanas o de 15 días presentes en los Word.
9. [Solicitud] Incluir en el banner de cada página un botón de cotización que lleve al formulario correspondiente dentro de esa misma página.
10. [Solicitud] Comunicar que el alquiler y la venta se atienden a nivel nacional en Colombia, y que los equipos de alquiler se entregan sin operador.
11. [Solicitud] No mostrar la franja de cifras (120 equipos, servicio técnico desde 2000 y mínimo de 1 mes) ni la nota de marcas que la seguía. Mantener el mínimo de un mes en el banner, proceso, situaciones de alquiler y FAQ, y conservar los datos de respaldo técnico y marcas en sus bloques actuales.
12. [Regla: docs/domain/BUSINESS_RULES.md:16-20] La venta de montacargas se limita a equipos usados cuya disponibilidad esté confirmada; el contenido no debe prometer equipos nuevos ni existencias no verificadas.
13. [Regla: docs/domain/INVENTORY.md:5-14] Si la página presenta equipos, modelos o disponibilidad, sus datos deben provenir del Inventario/Firebase. Las fotografías entregadas pueden servir como selección visual, sin sustituir los datos canónicos ni implicar que toda la flota está disponible.
14. [Solicitud] Usar una selección pequeña y representativa de fotografías; la página no debe publicar las 120 fotografías ni cargar una imagen por cada unidad.
15. [Solicitud] Organizar el contenido de baterías con las secciones del DOCX correspondiente: banner, soluciones de energía, referencias e inventario, rendimiento, proceso de cambio, galería, cotización, preguntas frecuentes y artículos relacionados.
16. [Solicitud] Limitar la oferta de baterías a las tecnologías realmente disponibles y no mencionar baterías de litio.
17. [Solicitud] Informar que la cobertura de baterías es nacional y que su instalación y mantenimiento se realizan en la bodega del cliente.
18. [Solicitud] Presentar Barbillon como marca representada y enlazar las tarjetas de baterías de plomo, BMS y cargadores a las páginas actuales correspondientes.
19. [Solicitud] Mantener los formularios definidos en los DOCX: datos de contacto y operación para cotizar montacargas; datos de contacto, equipo, voltaje, capacidad y compra o alquiler para cotizar baterías. Incluir el texto de privacidad indicado en los documentos.
20. [Solicitud] Usar una sola versión de cada bloque cuando el DOCX ofrece Opción A y Opción B, sin duplicar ambas redacciones. La opción recomendada es la versión en viñetas cuando mejora el escaneo del contenido.
21. [Regla: docs/domain/SEO.md:6-15] Gestionar title, meta description, canonical, robots, Open Graph y schema con Rank Math, sin duplicar estas etiquetas desde el tema.
22. [Regla: docs/domain/SEO.md:26-49] Incluir las nuevas rutas una sola vez en el sitemap SEO cuando estén aprobadas como páginas indexables; verificar su canonical y estado HTTP.
23. [Solicitud] Aplicar las referencias de composición, color e imagen de los DOCX visuales sin alterar la marca pública Tecnimontacargas.
24. [Solicitud] Mantener títulos, textos, cifras, botones y formularios como contenido web editable. Las imágenes compuestas con texto en los Word sirven de guía; no reemplazan secciones enteras mediante una sola imagen.
25. [Solicitud] Respetar las cantidades y los contenidos de bloques recogidos en las tablas de Contexto; las capturas de ejemplo no añaden ofertas, sectores, marcas ni beneficios ausentes del texto confirmado.
26. [Solicitud] Dar a ambas vistas una familia visual coherente con el sitio y con las guías entregadas. La propuesta concreta de tipografía, escala, variantes de banner y componentes se recoge en DEC-03.
27. [Regla: docs/domain/INVENTORY.md:5-14] Seleccionar imágenes cuya representación del equipo y tecnología corresponda al contenido; una imagen editorial no debe presentarse como fotografía de una unidad disponible. Las tarjetas de inventario usan la imagen asociada al registro canónico.
28. [Enmienda DEC-10 aprobada] En el hero de `/baterias-para-montacargas/`, mostrar antes de cada uno de los tres textos acordados un icono SVG lineal azul de 18 px: marcador, bodega y calendario, respectivamente. El SVG es decorativo (`aria-hidden="true"`); los textos permanecen íntegros y accesibles. La fila debe seguir envolviendo correctamente en móvil sin overflow horizontal.

## Reglas de negocio

- [Solicitud] La cobertura comercial de ambas páginas es Colombia.
- [Solicitud] El alquiler mínimo de montacargas es de 1 mes; no se ofrece alquiler por horas, por días ni por periodos inferiores a ese mínimo.
- [Solicitud] La instalación y el mantenimiento de baterías se realizan en la bodega del cliente.
- [Regla: docs/domain/BUSINESS_RULES.md:5-7] Mantener el nombre público Tecnimontacargas y verificar cifras corporativas antes de publicarlas. La cifra y fechas de este SPEC proceden de la confirmación del usuario en esta solicitud.
- [Regla: docs/domain/BUSINESS_RULES.md:16-20] No inventar precios, marcas, modelos, fotografías, condiciones ni disponibilidad; mostrar solo datos confirmados.
- [Regla: docs/domain/INVENTORY.md:5-14] Inventario/Firebase es la fuente canónica para los datos de equipos.
- [Regla: docs/domain/SEO.md:17-24] Las páginas comerciales usan schema `WebPage`; el schema `Service` corresponde a páginas de alquiler y mantenimiento. No etiquetar estas páginas como artículos.
- [Regla: docs/domain/BUSINESS_RULES.md:9-14] El alquiler se ofrece por periodos mensuales o de mayor duración; no se ofrece por horas ni días.

## Contratos

### Entrada

```json
{
  "rentalPage": "/alquiler-montacargas-electricos/",
  "batteryPage": "/baterias-para-montacargas/",
  "coverage": "Colombia",
  "rentalMinimumMonths": 1,
  "heroQuoteCta": true,
  "batteryServiceLocation": "bodega del cliente",
  "contentSources": [
    "pageTMD/01 - Alquiler o venta  de montacargas eléctricos.docx",
    "pageTMD/02 - Baterías para montacargas eléctricos.docx",
    "pageTMD/EQUIPOS VISUALES.docx",
    "pageTMD/Sugerencias para referencias visuales.docx"
  ]
}
```

Campos recibidos en los documentos, manteniendo sus agrupaciones:

- Montacargas: nombre y cargo; empresa y ciudad; correo o celular; tipo de equipo o necesidad; peso de la carga, altura de levante y ancho de pasillo. Botón: “Solicitar cotización”.
- Baterías: nombre y cargo; empresa y ciudad; correo o celular; marca y modelo del montacargas; voltaje y capacidad de la batería actual; compra o alquiler. Botón: “Cotizar batería”.

### Salida

```json
{
  "pages": [
    "/alquiler-montacargas-electricos/",
    "/baterias-para-montacargas/"
  ],
  "desktopMenuLinks": 2,
  "mobileMenuLinks": 2,
  "pageContentEditableInWordPress": true,
  "canonicalMetadataManagedBy": "Rank Math",
  "equipmentDataSource": "Inventario/Firebase"
}
```

## Casos límite

- Si una de las rutas propuestas ya está ocupada, no sobrescribir ni redirigir esa página sin resolver el destino y su canonical.
- Si no hay unidades con disponibilidad confirmada, no presentar una tarjeta como “Disponible”; dirigir al catálogo o al formulario de asesoría.
- Si una fotografía de referencia no se puede asociar de forma verificable con un equipo o una batería, usarla solo como imagen ilustrativa sin atribuir modelo ni disponibilidad.
- En móvil, ambos enlaces deben seguir accesibles aunque la lista del submenú sea larga y sin causar overflow horizontal.
- Si el formulario no está disponible o falla su envío, no afirmar que la cotización fue recibida.
- Los botones del banner deben enlazar a un ancla estable del formulario correspondiente y funcionar con teclado y en móvil.
- Si un ejemplo visual contiene una oferta descartada, texto por semanas, ubicación limitada a Bogotá, sectores pendientes o una tecnología diferente, recrear el bloque con el contenido vigente. No publicar la captura completa con ese contenido incrustado.
- Si la resolución de una imagen incrustada en Word no alcanza para su tamaño de presentación, seleccionar un original adecuado del material entregado o dejar esa selección pendiente; no ampliarla a ciegas ni sustituirla por otra máquina no verificada.
- El recorte móvil de una imagen debe conservar el equipo o batería reconocible; el texto se redistribuye y no queda incrustado en el recorte. Los títulos largos deben caber sin forzar desplazamiento horizontal.
- No duplicar la información de la nueva página en `/energia/baterias/` hasta resolver DEC-01.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/assets/js/tmd-mega-menu.js`
- `wp-content/themes/blocksy-child/theme.json` y `style.css` (referencia de paleta, fuente y formularios).
- `wp-content/themes/blocksy-child/assets/css/tmd-brand-consistency-global.css` (referencia de escala, tarjetas y botones).
- `wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css` (referencia de composición y adaptación móvil).
- `wp-content/themes/blocksy-child/inc/tmd-energy-structure.php`, `page-401.php`, `page-255.php` y `page-792.php` (referencias de páginas de Energía; no implica modificarlas).
- Páginas administradas en WordPress para las rutas de alquiler y baterías.
- `docs/domain/BUSINESS_RULES.md` (mínimo de alquiler mensual).
- `docs/domain/SEO.md` y Rank Math para metadatos y sitemap.
- Material de entrada bajo `pageTMD/`.

## Criterios de aceptación

1. [Solicitud] Las páginas existen en WordPress con las rutas aprobadas y sus textos pueden editarse desde WordPress.
2. [Solicitud] En escritorio, el nuevo enlace aparece en el espacio señalado del pie del mega menú de Equipos y el de baterías en el espacio señalado del pie del mega menú de Energía.
3. [Solicitud] En móvil, cada enlace aparece en el submenú correspondiente y navega a la página correcta.
4. [Solicitud] El botón de cada banner desplaza al formulario de cotización de esa página.
5. [Solicitud] La página de montacargas muestra el mínimo de 1 mes, la cobertura nacional y las condiciones de alquiler sin operador; no incluye la franja de tres cifras ni la nota de marcas que seguía debajo.
6. [Regla: docs/domain/BUSINESS_RULES.md:16-20] Ningún equipo, modelo, precio o disponibilidad se presenta sin confirmación en la fuente canónica.
7. [Solicitud] El bloque visual de equipos usa una selección representativa de imágenes y no publica una galería de 120 unidades.
8. [Solicitud] La página de baterías presenta las soluciones de plomo-ácido, BMS y cargadores aplicables, la cobertura nacional y la atención de instalación y mantenimiento en la bodega del cliente; no menciona litio.
9. [Solicitud] Los dos formularios contienen los campos definidos para cada cotización y muestran el aviso de privacidad.
10. [Solicitud] Las imágenes y colores corresponden a las referencias aprobadas y se muestran con proporciones adecuadas en escritorio y móvil.
11. [Regla: docs/domain/SEO.md:6-15] Cada página tiene metadatos administrados por Rank Math, una canonical coherente y schema comercial apropiado.
12. [Regla: docs/domain/SEO.md:26-49] Cada página indexable aparece una sola vez en el sitemap de Rank Math y responde HTTP 200.
13. [Solicitud] Las páginas conservan lectura y navegación utilizables en escritorio y móvil, sin recortes, solapamiento ni overflow horizontal.
14. [Solicitud] El texto de las referencias es seleccionable y editable en WordPress; los botones y acordeones son controles web, y los banners no contienen un segundo header o logo copiado del montaje de referencia.
15. [Solicitud] Las dos vistas aplican la dirección visual acordada en DEC-03 y la paleta del sitio. Las capturas rojas de los Word de contenido no introducen un segundo sistema de colores.
16. [Solicitud] La revisión de contenido no encuentra una oferta de alquiler por semanas o 15 días, una cobertura limitada a Bogotá ni tarjetas de litio o sectores no confirmados añadidos desde las imágenes de ejemplo.
17. [Solicitud] Se omite la franja de cifras de montacargas y su nota de marcas inmediata; se conservan las cuatro necesidades de operación, las tres soluciones de baterías y los demás pasos y formularios aprobados.
18. [Enmienda DEC-10 aprobada] Los tres apoyos del hero de baterías muestran sus iconos antes del texto, mantienen exactamente la redacción existente y se leen sin depender del icono; en escritorio y móvil no hay solapamientos ni overflow horizontal.

## Validación

- Pruebas unitarias: No aplica a la redacción del SPEC. Para la implementación, definir pruebas focalizadas para los enlaces de menú y la salida de contenido donde exista código propio.
- Pruebas de integración: Verificar los destinos de enlaces y la integración de cada formulario con el mecanismo existente; validar que los metadatos y schema se generen una sola vez.
- Validación manual: Revisar ambas páginas y los menús de escritorio y móvil; comprobar orden de secciones, imágenes, formularios, navegación, accesibilidad básica, consola y overflow.
- Evidencia de análisis del SPEC: los cuatro DOCX se leyeron mediante su XML y se inspeccionaron visualmente sus imágenes incrustadas. Se compararon fuentes canónicas con capturas y estilos calculados de las ocho páginas públicas indicadas en Contexto. Los recursos de análisis están en `.codex-tmp/landing-style-study/` y no son archivos de publicación.
- [Verificación productiva: 2026-10-04] Las páginas raíz `/alquiler-montacargas-electricos/` (ID 1558) y `/baterias-para-montacargas/` (ID 1559) están publicadas. La primera conserva el hero anterior; esta iteración prepara el nuevo texto editable y sus estilos. La revisión visual en navegador, escritorio y móvil sigue pendiente.
- Antes de aplicar el nuevo hero en producción: crear backup verificado, revisar deriva con `./scripts/sync-production.sh --check`, actualizar únicamente los archivos aprobados y el contenido del ID 1558, purgar caché y comprobar HTTP, sitemap, metadatos y navegador. Un HTTP 200 por sí solo no acredita el render ni el flujo.
- Antes de aplicar DEC-10 en producción: verificar la deriva del tema y preparar backup verificado del contenido de la página de baterías. Actualizar únicamente los tres elementos del hero y desplegar solo los archivos aprobados; purgar caché y comprobar la página en escritorio y móvil.
- [Verificación productiva: 2026-10-05] Se respaldaron y verificaron la base de datos y el contenido previo del ID 1559. El dry-run confirmó el hash de origen y un único bloque objetivo; tras `wp_update_post()`, la página guardó tres SVG decorativos junto a las etiquetas exactas. Se despachó la purga puntual de LiteSpeed. La URL canónica respondió HTTP 200 y su HTML público contiene los tres SVG, sus tres atributos `aria-hidden="true"` y las tres etiquetas en orden. No se obtuvo captura visual de navegador.
- [Control de sincronización: 2026-10-05] `./scripts/sync-production.sh --check` detectó diferencias en `tmd-commercial-landings.css`, snapshots y archivos `.DS_Store` locales. No se desplegó código del tema en esta operación; esas diferencias quedan pendientes de comparación en su alcance correspondiente.

## Riesgos

- [Verificación pública: 2026-10-04] `/energia/baterias/` permanece fuera de este cambio; no se altera su contenido, canonical ni redirección. Su posible consolidación requiere otra decisión.
- [Regla: docs/domain/INVENTORY.md:5-14] Las imágenes aportadas no prueban disponibilidad actual de los equipos representados.
- [Inferencia técnica] Un formulario visualmente correcto no demuestra recepción del correo; se necesita validación de entrega real antes de afirmar que funciona.
- [Inferencia técnica] La edición de contenido en WordPress y el despliegue de código del tema son acciones separadas y pueden dejar la página con estilos o enlaces incompletos si se ejecutan en momentos distintos.
- [Verificación pública: 2026-10-04] El sitio mezcla familias tipográficas y variantes por página. Heredar reglas globales sin comprobar los estilos calculados puede cambiar el aspecto previsto de títulos y botones.
- [Evidencia visual: pageTMD/Sugerencias para referencias visuales.docx, word/media/image4.jpeg] Una variante de hero muestra un cilindro posterior que requiere revisar la correspondencia del equipo con la oferta eléctrica. Es una referencia de composición; la selección final debe verificar la tecnología y el modelo antes de usarse como representación del servicio.

## Decisiones registradas

- DEC-01: Se conserva `/energia/baterias/` tal como está. Esta implementación no cambia páginas existentes ni crea redirecciones; la nueva página de baterías tendrá la ruta raíz acordada.
- DEC-02: Aprobadas las rutas `/alquiler-montacargas-electricos/` y `/baterias-para-montacargas/`. La primera aparece bajo Equipos y la segunda bajo Energía; la jerarquía del menú no se duplica en los slugs.
- DEC-03: Aprobada como base de esta versión la dirección visual descrita abajo. Imágenes y textos pueden ajustarse en WordPress después de revisar las páginas.

  | Elemento | Propuesta |
  |---|---|
  | Paleta y roles | Navy `#262E4F` para títulos y paneles oscuros; azul `#128CEB` para acentos, iconos y acciones; amarillo `#FFC33C` para énfasis y CTA con texto navy. Blanco y fondo suave `#F6F8FB` para lectura; `#5E748B` para apoyos y `#3C3C3C` para texto. Ajustar las combinaciones a un contraste legible, sin copiar amarillo sobre blanco para textos pequeños. |
  | Tipografía | Work Sans explícita en títulos, cuerpo y botones de ambas vistas, alineada con el tema versionado. Pesos 400 para cuerpo, 600 para subtítulos/CTA y 700 para titulares. Evitar depender de la mezcla de herencia Syne/Work Sans observada en las páginas existentes. |
  | Escala y espacio | H1 de 48–52 px en escritorio y 34–36 px en móvil; H2 de 34–40 px y 28–30 px; cuerpo de 16–18 px. Contenido máximo de 1180 px, laterales móviles de 20 px y separación de secciones de 64–80 px en escritorio / 40–48 px en móvil. |
  | Hero montacargas | Variante oscura inspirada en `image2.jpeg`/`image3.jpeg` de Sugerencias: imagen de operación a la derecha, degradado navy detrás del texto, título blanco con énfasis amarillo y CTA “Solicitar cotización”. Usar un archivo sin texto que corresponda a un equipo eléctrico verificado. |
  | Hero baterías | Variante clara inspirada en `image5.jpeg`/`image7.jpeg`: batería protagonista a la derecha, espacio claro para título navy y énfasis azul; CTA amarillo/navy “Cotizar batería”. Comparte ancho, escala, márgenes y forma de los botones con montacargas. |
  | Cuerpo de página | Alternar blanco y gris suave; reservar navy para diferenciales o franjas puntuales. Priorizar texto breve, cifras grandes y fotografías amplias. Simplificar los cortes diagonales de los montajes para que no dificulten el recorte en móvil. |
  | Tarjetas y CTA | Tarjetas con radio de 12 px, borde fino y sombra suave; botones de radio 8 px y texto 16 px/600. Un CTA dominante por bloque, con contorno para acciones secundarias. Iconos lineales coherentes con el sitio. |
  | Imágenes | `contain` para mostrar equipos y baterías completos en tarjetas; `cover` con foco revisado para escenas de bodega. Usar imágenes separadas del texto y evitar repetir el logo del header dentro de cada sección. Los montajes de Word orientan el encuadre, no prueban existencias. |
  | Móvil | Apilar texto, CTA e imagen; tarjetas en una columna cuando su contenido no admita dos; pasos y FAQ en lectura vertical; formulario de una columna. Conservar el orden y las condiciones comerciales aunque cambie la composición. |
  | Formulario y FAQ | Formulario sobre tarjeta clara, etiquetas visibles, campos del Word y aviso de privacidad. En escritorio pueden convivir con fotografía o FAQ según el bloque de referencia; en móvil se apilan. |

- DEC-04: El bloque de equipos de alquiler muestra como máximo cinco registros vigentes de Inventario/Firebase. Solo admite las subcategorías eléctricas conocidas por el clasificador: tomapedidos de alto nivel, eléctricos de 3 y 4 ruedas, pantógrafo sencillo y doble profundidad, estibadores eléctricos, apiladores eléctricos y retráctiles de mástil móvil. Excluye estibadores manuales, combustión, categorías vacías y nuevas categorías hasta verificarlas. La página de baterías presenta las líneas confirmadas y las cinco imágenes de referencia del Word; no usa el shortcode general de baterías porque el consumidor actual no separa allí la tecnología por tipo.
- DEC-05: Los dos formularios CF7 incorporan un campo honeypot fuera del área visible y controles server-side aplicados únicamente a formularios con el marcador de estas páginas. El límite es de cinco envíos por hora y dirección `REMOTE_ADDR`; la IP se usa solo en una clave hash de transient. Se conservan los controles de User-Agent existentes y el registro anti-spam no guarda valores enviados por el usuario.
- DEC-06: El seed WP-CLI obtiene un bloqueo exclusivo en el directorio temporal del proceso antes de consultar formularios/páginas y mantiene el bloqueo durante dry-run o ejecución para impedir duplicados por ejecuciones concurrentes.
- DEC-07: Los formularios nuevos de alquiler y baterías usan `info@tmdual.com` como destinatario. El seed lo recibe mediante `TMD_COMMERCIAL_LANDINGS_RECIPIENT`, valida el override y solo usa como alternativa el destinatario del formulario CF7 ID 14 cuando no se indica override. El formulario 14 permanece de solo lectura y no se altera.
- DEC-08: Tras revisar la página publicada, el usuario aprobó reconstruir el banner de alquiler a partir de su nueva referencia como composición HTML editable. Se conserva la fotografía limpia existente `alquiler-hero.jpeg`; el H1 pasa a “Alquiler de montacargas eléctricos”, seguido por “Equipos propios con mantenimiento en nuestro taller técnico” y el párrafo sobre contrabalanceados, reach, pantógrafos y apiladores. Se elimina la línea “Soluciones para Colombia”, se mantiene el CTA y los datos de mínimo mensual, cobertura Colombia y alquiler sin operador, y el logotipo del header global no se duplica dentro del banner.
- DEC-09: Por solicitud explícita del usuario del 2026-10-04, la página de alquiler no incluye la sección HTML de tres cifras ni la nota de marcas inmediatamente posterior. El periodo mínimo de un mes se conserva en el hero, proceso, situaciones de alquiler y FAQ; las demás secciones permanecen.
- DEC-10 (enmienda aprobada — 2026-10-04): En la página de baterías, añadir SVG lineales decorativos de 18 px en azul `#128CEB` a los tres textos existentes del hero: marcador para cobertura nacional, bodega para instalación en sitio y calendario para el alquiler desde un mes. Mantener las etiquetas textuales, no duplicar la franja ni modificar el hero de alquiler.
