# Plan: Maqueta v2 de alquiler de montacargas eléctricos

> SPEC: `docs/specs/2026-10-05-landing-alquiler-v2.md`. La solicitud del 2026-10-05 convierte la maqueta adjunta en la fuente vigente para la landing de alquiler; este plan reemplaza solo las tareas de alquiler incompatibles del plan del 2026-10-04.

El SPEC fija los requisitos. Esta hoja registra su implementación. La hoja específica que aplica los estilos de esta versión es `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css`, cargada condicionalmente desde `wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php`; la base compartida vive en `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css`.

## Restricciones

- Implementar en el worktree aislado creado desde `main`, preservando los cambios paralelos de baterías del checkout principal. Producción solo recibe el SHA que llegue a `main` por el flujo de GitHub Actions.
- Página 1558 y formulario CF7 1556 únicamente. No tocar ID 1559 ni CF7 ID 14.
- Datos dinámicos desde Inventario/Firebase y entradas publicadas. Sin disponibilidad ficticia.
- Fotos del catálogo y referencias visuales suministradas, sin capturas de maqueta ni texto editorial incrustado; versionar únicamente los activos optimizados utilizados.
- Reproducir exactamente el contenido de la maqueta vigente, incluido 120 equipos, Yale, alquiler mínimo de 15 días y “por semanas o meses”. El usuario confirmó 120 equipos y Yale y pidió corregir los hallazgos restantes; se ajustó únicamente el CTA de inventario a `#0D70BD` (5.16:1) para resolver el contraste.
- Despliegue explícitamente solicitado: completar manifiesto, revisión, sincronización, backups y verificación productiva antes del primer write.
- Estado al 2026-10-05: el checkout contiene la versión v2 con DEC-13; el CSS que responde en producción aún oculta la navegación y el footer. El cambio local sigue pendiente de despliegue y no se considera aplicado en el sitio público.

## Tareas

1. [x] Leer AGENTS, SPEC previo, plan, mapas, reglas de negocio y runbooks. Registrar estado Git y conservar cambios locales.
2. [x] Extraer alcance y doce bloques de la maqueta. Revisar fotos limpias y recursos de equipos del catálogo.
3. [x] Repetir `sync-production.sh --check` desde este worktree y clasificar la deriva sin `--pull`. La versión runtime de esta entrega contiene cuatro rutas: los dos CSS, el include y `montacargas-construccion.webp`. Los `.DS_Store` y snapshots son auxiliares de auditoría y quedan fuera del manifiesto.
4. [x] Implementar contenido v2 en el generador WP-CLI y modo de actualización con hashes, backup, rollback, transacción/lock y purga de ID 1558. Ajustar solo CF7 1556 a las cinco entradas compuestas del Markdown, su nota de privacidad y el mapeo del correo.
5. [x] Añadir atributos optativos a shortcodes de inventario/blog y marcador v2 en body class; mantener visibles la navegación y el footer globales.
6. [x] Copiar y optimizar en `wp-content/themes/blocksy-child/assets/img/commercial-landings-v2/` solo las cuatro referencias de equipo necesarias desde `EQUIPOS SEGUN REFERENCIA/`: `EFG425/2.png` (contrabalanceado), `CROWN RR/1.png` (reach), el cuadrante superior izquierdo de `CROWN RD 5220/4.png` (doble profundidad) y `CROWN PE 4000-60/2.png` (traslado a nivel de piso). Versionar únicamente los WebP usados; conservar intactos los originales.
7. [x] Construir estilos v2 aislados: hero, bloques claros/oscuros, cinco tarjetas de sectores, inventario, proceso, usos, respaldo y banda cotización/FAQ responsiva.
8. [x] Actualizar la regla de alquiler mínimo a 15 días y respetar el texto exacto “equipos por semanas o meses” de la maqueta, con el mínimo explícito en el FAQ y en la descripción comercial.
9. [x] Capturar manifiesto y revisar el diff con revisores general, base de datos, seguridad y pruebas. La revisión previa no encontró problemas de seguridad, datos o arquitectura; se cerraron los tres hallazgos de pruebas y se corrigió contraste.
10. [x] El usuario confirmó 120 equipos y Yale, y autorizó corregir los pendientes. Se conserva “por semanas o meses” junto al mínimo de 15 días explicado en descripción y FAQ. Se ajustó el CTA a contraste 5.16:1. Pruebas focales cubren los doce bloques, H1/FAQ, campos y correo CF7, honeypot vacío/lleno y actualizador con rechazos, rollback y commit.
11. [x] Aplicar la aclaración DEC-12 del usuario: H1 limitado a “Alquiler de montacargas eléctricos”; texto inicial del H2 de sectores en blanco; cinco tarjetas con las etiquetas y textos de la nueva referencia, conservando el diseño existente; optimizar la fotografía suministrada para la tarjeta Construcción. Añadir pruebas focales de contenido, cantidad de tarjetas y contraste.
12. [x] Revisar los cambios y ejecutar las pruebas focales después de la enmienda. La actualización local quedó validada; la revisión del diff H1 no encontró hallazgos críticos o importantes.
13. [x] Corregir la regla CSS que ocultaba `#header`, `.tmd-mm-header` y `.tmd-site-footer`; conservar las reglas específicas de `.entry-header` y `.tmd-contact-rail`, y actualizar las pruebas y el SPEC según DEC-13. La prueba focal del render y `git diff --check` pasan.
14. [ ] Tras cerrar todos los gates para el manifiesto y la operación de contenido nuevos, desplegar los archivos autorizados vía `main`; escribir el contenido/formulario/meta autorizados con el actualizador y purgar la página 1558.
15. [ ] Verificar código/HTTP/HTML/formulario, hashes del código y sync final. Playwright Chromium ya está disponible fuera del repositorio; capturar y revisar escritorio/móvil después de publicar.

## Evidencia ya obtenida

- Los originales de referencia viven en `EQUIPOS SEGUN REFERENCIA/` en el checkout principal. No se añade el catálogo completo al cambio; solo se copian/optimizan los cuatro recursos usados en el child theme.
- El 2026-10-05 la página pública 1558 respondió HTTP 200; su copia anterior declara mínimo de un mes, servicio técnico desde 2000, alquiler desde 2013 y Yale. La respuesta del sitio es evidencia de publicación previa, no verificación actual de vigencia.
- El usuario confirmó “120 equipos en flota propia” y que Yale está disponible para anunciarse. La consulta del endpoint canónico del 2026-10-05 registró 110 equipos en total, 23 montacargas activos y no incluyó Yale; la nueva landing mantiene el inventario de equipos dinámico y sin inventar modelos ni stock.
- Contraste calculado: `#128CEB` con blanco = 3.51:1; el CTA de inventario usa ahora `#0D70BD` con blanco = 5.16:1.
- Las cuatro imágenes de equipo copiadas al child theme corresponden a EFG425, Crown RR, Crown RD 5220 y Crown PE 4000-60 de `EQUIPOS SEGUN REFERENCIA/`. Para el bloque 7, Retail usa la foto limpia del centro de distribución de `Sugerencias para referencias visuales.docx` (imagen 17); Distribuidoras usa la foto de montacargas rojo en zona de despacho de `EQUIPOS VISUALES.docx` (imagen 41), sin reutilizar la imagen de alimentos.
- El `sync-production.sh --check` completo desde este worktree (2026-10-05, solo lectura) detectó el include compartido previsto y los CSS/assets v2 ausentes. El diff del include se limita a atributos/variantes de shortcodes de inventario/blog y al marcador/enqueue v2.
- La descarga HTTP de la hoja CSS pública de la landing (206, por lectura parcial) confirmó los selectores que ocultaban `#header`, `.tmd-mm-header` y `.tmd-site-footer`. La corrección ya está en el worktree; falta desplegarla para verificar el resultado en producción.
- La deriva de snapshots se clasificó sin traer contenido a Git: páginas 47, 57, 255, 273, 278, 288, 290, 401 y 792 cambiaron; páginas 1558 y 1559 son nuevas respecto al snapshot; cambiaron tres entradas publicadas (798–800). Diez plugins de terceros tienen versiones nuevas. Producción reporta dos entradas activas bajo `tm-chatbot-fase1`; el árbol de código de los cuatro plugins propios comparó igual. También reporta tres MU plugins ausentes del snapshot; sus hooks se limitan a guías de tipos, las páginas 278 y 358 y correos con asunto PQR, sin cambiar la página 1558 ni el destino CF7 1556. `snippets.json` y `themes.json` coinciden. Todo queda fuera del manifiesto de runtime y no se usó `--pull`.
- La vista pública previa tenía como obligatorios nombre, cargo, empresa, ciudad, contacto y necesidad; requerimientos y checkbox de privacidad no eran obligatorios. El objetivo conserva la obligatoriedad sobre los campos compuestos y elimina el checkbox de aceptación que la maqueta no incluye. No añade placeholders.
- La revisión de pruebas reportó ausencia de cobertura focal de CF7 v2 y del actualizador/rollback. Se migraron las aserciones obsoletas al contenido v2 y se añadieron pruebas de renderer/CF7, honeypot vacío/lleno y updater con gates y rollback.
- La captura visual de navegador de escritorio/móvil queda pendiente hasta que producción tenga el nuevo contenido y assets.
- El primer intento del control quedó detenido en SSH, pero una conexión posterior respondió y el control completo comparó producción. La clasificación anterior se volvió a verificar; no hubo `--pull` ni escritura remota.
- Los snapshots productivos son de auditoría; no se ejecutó `--pull`.
