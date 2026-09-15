# SPEC: Actualizar vacantes de Trabaja con nosotros

## Estado

- Aprobado

## Contexto

[Solicitud: mensaje y capturas aportados el 2026-09-15] Se solicita actualizar la sección “Trabaja con nosotros” de la página pública para reemplazar la información de dos vacantes y crear una tercera tarjeta para un puesto técnico especializado.

[Evidencia: `production-snapshot/pages.json:123-130`] La página WordPress `ID 273`, titulada “Trabaja con nosotros”, contiene actualmente la sección `tmd-jobs-vacancies` con dos tarjetas: “Técnico en Mantenimiento de Montacargas” y “Auxiliar Administrativo”. El contenido de la página vive en WordPress/MariaDB; el snapshot versionado es únicamente una copia de auditoría.

[Evidencia: `production-snapshot/pages.json:123-130`] Las tarjetas actuales comparten estructura, metadatos de jornada, ubicación, salario y enlace `#postulacion`; la cuadrícula `tmd-jobs-grid` ya tiene comportamiento responsive para múltiples tarjetas.

[Evidencia: `AGENTS.md`] La página WordPress y su contenido administrado son la fuente canónica; los cambios persistentes deben realizarse mediante un procedimiento versionado, con backup y reversión identificada, separado del despliegue de código.

## Problema

[Solicitud] Las dos vacantes visibles muestran cargos y descripciones que ya no corresponden a los perfiles que se desean publicar, y falta la vacante de Técnico Especializado en Montacargas de Combustión.

## Objetivo

[Solicitud] Publicar en la sección “Vacantes Disponibles” de la página `ID 273` tres tarjetas coherentes con los perfiles solicitados, conservando el diseño, los enlaces de postulación y el resto del contenido de “Trabaja con nosotros”.

## Fuera del alcance

- [Solicitud] No modificar el hero, el panorama, la filosofía, el testimonio, el formulario de postulación, sus destinatarios ni sus validaciones.
- [Solicitud] No cambiar los enlaces existentes hacia `#postulacion`, la ubicación de la sección, la jornada o el salario de las tarjetas existentes.
- [Solicitud] No crear ni modificar imágenes, uploads, inventario, SEO, menú, footer ni otras páginas.
- [Regla: `AGENTS.md`] No modificar el tema padre, WordPress core, plugins de terceros ni copias históricas como `production-snapshot/` para sustituir el contenido administrado.
- [Regla: `docs/runbooks/DEPLOYMENT.md`] No mezclar la escritura de contenido persistente con el despliegue del código del child theme.

## Requisitos funcionales

1. [Solicitud] La primera tarjeta debe mostrar como título `Técnico Especializado en Montacargas Eléctricos` y como descripción exacta: `Buscamos Técnico Especializado en Montacargas Eléctricos, con experiencia en diagnóstico, mantenimiento y reparación de sistemas eléctricos, electrónicos, electromecánicos e hidráulicos, para vincularse a nuestra sede principal.`
2. [Solicitud] La segunda tarjeta debe mostrar como título `Auxiliar Técnico en Entrenamiento` y como descripción exacta: `Buscamos Auxiliar Técnico en Entrenamiento, con conocimientos básicos en electromecánica, electricidad o mecánica, disposición para aprender y crecer profesionalmente en el mantenimiento y reparación de montacargas. No se requiere experiencia.`
3. [Solicitud] La segunda tarjeta debe usar la etiqueta de área `Técnico`, porque el puesto solicitado es técnico; la primera tarjeta debe conservar su etiqueta `Técnico`.
4. [Solicitud] Debe existir una tercera tarjeta con el título `Técnico Especializado en Montacargas de Combustión` y la descripción exacta: `Buscamos Técnico Especializado en Montacargas de Combustión, con experiencia en diagnóstico, mantenimiento y reparación de motores, sistemas hidráulicos, transmisiones, frenos y componentes mecánicos. Vinculación para nuestra sede principal.`
5. [Solicitud] La tercera tarjeta debe conservar la estructura visible de las tarjetas existentes, usar la etiqueta `Técnico`, mostrar `Tiempo Completo`, `Bogotá, D.C.` y `Salario a convenir`, y enlazar su botón `Aplicar a esta vacante` a `#postulacion`.
6. [Solicitud] Las tres tarjetas deben permanecer dentro de la sección `tmd-jobs-vacancies`, conservar sus clases y mantener el comportamiento responsive de la cuadrícula actual.
7. [Solicitud] El resto del contenido de la página `ID 273`, incluidos el hero, las secciones institucionales, el testimonio, el formulario, sus campos, enlaces y recursos visuales, debe permanecer sin cambios funcionales ni textuales.
8. [Regla: `AGENTS.md`] La actualización persistente debe ser idempotente, detenerse sin escribir si las precondiciones del contenido no coinciden de forma unívoca y ejecutarse mediante un procedimiento versionado separado del código de presentación.

## Reglas de negocio

- [Regla: `AGENTS.md`] La marca pública es Tecnimontacargas y no deben inventarse hechos comerciales, cargos, ubicaciones, salarios o disponibilidad distintos de los proporcionados o ya presentes.
- [Solicitud] Las tres vacantes publicadas corresponden a perfiles técnicos y sus descripciones deben conservar literalmente la redacción suministrada.
- [Evidencia: `production-snapshot/pages.json:123-130`] La ubicación, jornada, salario y acción de postulación forman parte del patrón visual actual de las tarjetas.
- [Regla: `docs/runbooks/BACKUP_RESTORE.md`] Toda escritura sobre contenido persistido requiere un backup verificable y una estrategia de reversión identificada.

## Contratos

### Entrada

No aplica: la tarea actualiza contenido administrado de WordPress y no modifica un endpoint ni un payload público.

### Salida

No aplica: el resultado observable es el HTML renderizado de la sección `tmd-jobs-vacancies` en la página `ID 273`.

## Casos límite

- [Inferencia técnica] Si la página `ID 273` no existe, no es una página o no contiene exactamente una sección `tmd-jobs-vacancies`, la operación debe bloquearse sin escritura.
- [Inferencia técnica] Si alguna tarjeta esperada no contiene exactamente una única versión del título, descripción o estructura anterior, la operación debe bloquearse sin transformación parcial.
- [Inferencia técnica] Si la tercera tarjeta ya existe completa con el contenido final, la ejecución debe ser idempotente y no crear una cuarta tarjeta.
- [Inferencia técnica] Si el contenido se encuentra en un estado mixto entre versiones antiguas y finales, el procedimiento debe informar el conflicto y no escribir.
- [Inferencia técnica] Una edición concurrente de la página entre el dry-run y la ejecución puede invalidar las precondiciones y debe detener la escritura.

## Archivos o módulos relacionados

- `docs/specs/2026-09-15-actualizar-vacantes-trabaja-nosotros.md`
- `scripts/update-jobs-vacancies.php` como procedimiento versionado e idempotente para el contenido de la página `ID 273`.
- `tests/test-update-jobs-vacancies.php` como prueba focalizada de transformación, preservación e idempotencia.
- `production-snapshot/pages.json` como evidencia de auditoría, no como fuente editable.
- Página WordPress `Trabaja con nosotros`, `ID 273`, ruta `/nosotros/trabaja-con-nosotros/`.
- `wp-content/themes/blocksy-child/page-273.php` y el estilo existente como fuentes de presentación; no se prevé modificar CSS porque la cuadrícula actual admite la tercera tarjeta.

## Criterios de aceptación

1. [Solicitud] La primera tarjeta muestra el título y la descripción exactos del perfil de Técnico Especializado en Montacargas Eléctricos.
2. [Solicitud] La segunda tarjeta muestra el título y la descripción exactos del perfil de Auxiliar Técnico en Entrenamiento y su etiqueta visible es `Técnico`.
3. [Solicitud] La sección contiene exactamente tres tarjetas, y la tercera muestra el título y la descripción exactos del perfil de Técnico Especializado en Montacargas de Combustión.
4. [Solicitud] La tercera tarjeta muestra `Tiempo Completo`, `Bogotá, D.C.`, `Salario a convenir` y un botón que dirige a `#postulacion`.
5. [Solicitud] Las tres tarjetas conservan las clases, el orden, la estructura de metadatos y el comportamiento responsive de la sección actual, sin overflow horizontal en escritorio ni móvil.
6. [Solicitud] El hero, panorama, filosofía, testimonio, formulario, enlaces y recursos visuales fuera de las vacantes permanecen sin cambios.
7. [Regla: `AGENTS.md`] La prueba focalizada confirma precondiciones, preservación del contenido ajeno, transformación completa, bloqueo ante estados ambiguos e idempotencia.
8. [Regla: `AGENTS.md`] El PHP modificado supera `php -l`, `git diff --check` no reporta errores y no se incluyen archivos ajenos, temporales, secretos ni snapshots modificados como fuente.
9. [Regla: `docs/runbooks/DEPLOYMENT.md`] La publicación productiva, si se ejecuta con autorización, usa backup verificable, control de deriva, dry-run, escritura controlada, purga de caché aplicable, validación HTTP/navegador/logs y `./scripts/sync-production.sh --check` posterior.

## Validación

- Pruebas unitarias: transformar el contenido actual de la sección; comprobar las tres tarjetas, los textos exactos, la etiqueta de la segunda, el enlace de la tercera, preservación del contenido fuera de la sección, estados ambiguos e idempotencia.
- Pruebas de integración: ejecutar el procedimiento versionado en dry-run sobre la página `ID 273`, verificar que no escribe en dry-run y ejecutar `php -l` sobre cada PHP modificado.
- Validación manual: revisar `/nosotros/trabaja-con-nosotros/` en escritorio y móvil; confirmar títulos, descripciones, etiquetas, metadatos, botones, salto responsive de la tercera tarjeta, ausencia de recortes y ausencia de errores de consola u overflow horizontal.
- Validación productiva: con el SPEC aprobado y autorización vigente, verificar el backup de MariaDB y el control de deriva previo, desplegar únicamente el código versionado necesario, ejecutar dry-run y luego la escritura persistente controlada, purgar la caché aplicable, comprobar HTTP, HTML visible, logs y sincronización posterior. La verificación productiva no se considerará completa por un HTTP 200 aislado.

## Riesgos

- [Inferencia técnica] Reemplazar por posición o con búsquedas no unívocas podría alterar otra sección o borrar contenido no relacionado.
- [Inferencia técnica] La longitud de las nuevas descripciones puede aumentar la altura de las tarjetas; debe conservarse el flujo responsive existente sin overflow horizontal.
- [Inferencia técnica] La caché de página puede ocultar el contenido actualizado después de la escritura persistente.
- [Inferencia técnica] Una edición concurrente del contenido puede hacer que el resultado del dry-run ya no corresponda al contenido que se va a escribir.
- [Regla: `AGENTS.md`] El backup de contenido persistente debe estar verificado antes de la escritura y no debe copiarse al repositorio ni exponerse en la evidencia.

## Decisiones pendientes

- `DEC-01` — Aprobada por el usuario el 2026-09-15: los nombres de puesto incluidos en los textos suministrados se reflejan también en los títulos de las tarjetas; por coherencia, la segunda etiqueta cambia de `Administrativo` a `Técnico`.
- `DEC-02` — Aprobada por el usuario el 2026-09-15: la tercera tarjeta hereda los metadatos visibles del patrón actual (`Tiempo Completo`, `Bogotá, D.C.`, `Salario a convenir`) y el enlace `#postulacion`.

## Registro de aprobación

- [Aprobación, 2026-09-15] El usuario aprobó este SPEC para la implementación local y la publicación productiva solicitada, sujetas a los gates de revisión, backup, deriva y despliegue.
