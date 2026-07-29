# AGENTS.md

## Propósito

Este archivo contiene las reglas permanentes para trabajar en este repositorio.

Antes de modificar código, configuración, contenido o infraestructura:

1. Leer este archivo.
2. Leer el `AGENTS.md` más cercano al archivo que se modificará, si existe.
3. Consultar el `SPEC` relacionado, si existe.
4. Consultar únicamente la documentación especializada aplicable.
5. Inspeccionar el código y la configuración actuales antes de proponer cambios.

No inferir trabajo pendiente a partir de código eliminado, ramas antiguas, documentos históricos o handoffs desactualizados. Una funcionalidad ausente solo debe tratarse como pendiente cuando exista una solicitud explícita, un ticket vigente o un `SPEC` activo.

## Autoridad y alcance

Aplicar las instrucciones en este orden:

1. Requisitos explícitos de la tarea actual.
2. `AGENTS.md` más cercano al archivo modificado.
3. Este `AGENTS.md`.
4. Documentación vigente del repositorio.
5. Convenciones comprobadas en el código.

Las instrucciones de una tarea pueden cambiar decisiones funcionales o de implementación, pero no deben omitir silenciosamente controles de seguridad, protección de datos, backups, validaciones productivas o restricciones de infraestructura.

Un `AGENTS.md` de un subdirectorio puede añadir reglas para su subárbol, pero no puede debilitar las reglas de seguridad o producción de este archivo.

El código versionado, la configuración activa y el entorno verificado determinan el estado real del sistema.

## Fuentes canónicas

### Código propio

- Tema activo: `wp-content/themes/blocksy-child/`.
- Plugins propios:
  - `wp-content/plugins/tm-chatbot-fase1/`
  - `wp-content/plugins/tm-equipos-destacados-v2/`
  - `wp-content/plugins/tm-popup-bienvenida/`
  - `wp-content/plugins/tm-quiz-equipo-ideal/`

Archivos principales del tema:

- Inventario:
  - `inc/tmd-inventory-api.php`
  - `assets/js/tmd-inventory-api.js`
  - `assets/css/tmd-inventory-api.css`
- SEO: `inc/tmd-seo.php`
- Cuenta: `inc/tmd-account.php`
- Header: `template-parts/tmd-header.php`
- Footer: `template-parts/tmd-footer.php`
- Guías: `inc/tmd-equipment-type-guides.php`

El quiz debe modificarse en `wp-content/plugins/tm-quiz-equipo-ideal/`. No registrar nuevamente su shortcode desde copias históricas del tema.

### Elementos no canónicos

- `production-snapshot/` sirve para auditoría y comparación; no sustituye backups completos.
- `tmd-site-kit/` es histórico e inactivo.
- `.codex-tmp/` contiene archivos temporales.
- No modificar una copia histórica cuando exista una fuente canónica activa.

## Documentación especializada

Consultar solo lo relacionado con la tarea:

- Arquitectura: `docs/architecture/REPO_MAP.md`
- Operación productiva: `docs/runbooks/PRODUCTION.md`
- Despliegue: `docs/runbooks/DEPLOYMENT.md`
- Backup y restauración: `docs/runbooks/BACKUP_RESTORE.md`
- Reglas comerciales: `docs/domain/BUSINESS_RULES.md`
- Inventario: `docs/domain/INVENTORY.md`
- SEO: `docs/domain/SEO.md`
- Navegación: `docs/domain/NAVIGATION.md`
- WooCommerce: `docs/domain/COMMERCE.md`
- Estado temporal: `docs/status/CURRENT_STATE.md`
- Tareas concretas: `docs/specs/`

Los documentos de estado y handoff son referencias históricas. Verificar nuevamente cualquier dato temporal antes de presentarlo como actual.

Si un documento todavía no existe o está incompleto, inspeccionar el código y la configuración. No inventar su contenido.

## Seguridad y producción

Producción: `https://tecnimontacargas.com`.

Nunca guardar, mostrar, registrar ni versionar:

- Credenciales, tokens o claves privadas.
- `.env` o `wp-config.php`.
- Certificados privados.
- Secretos de Firebase o MariaDB.
- Cadenas de conexión.
- Backups SQL con datos reales.
- Datos personales.

Reglas obligatorias:

- No actualizar WordPress, temas, plugins o dependencias salvo solicitud explícita.
- No cambiar DNS, TLS, Docker Compose, contenedores, volúmenes, servicios o puertos fuera de una tarea explícita de infraestructura.
- No ejecutar escrituras contra producción para investigar un problema.
- No usar datos productivos en pruebas locales sin anonimización.
- Antes de intervenir producción, consultar los runbooks correspondientes.
- Toda escritura material requiere un backup verificado.
- Si `./scripts/sync-production.sh --check` detecta deriva, detener el despliegue y comparar.
- Desplegar únicamente los archivos modificados.
- No desplegar desde `.codex-tmp/`.
- No reemplazar el child theme completo por un cambio puntual.
- No ejecutar migraciones destructivas sin un plan de reversión.

## Reglas de dominio esenciales

- La marca pública es `Tecnimontacargas`.
- No inventar equipos, marcas, modelos, imágenes, precios, disponibilidad ni hechos comerciales.
- Inventario/Firebase es la fuente canónica de los equipos.
- WordPress consume el inventario, pero no debe corregirlo con datos ficticios.
- Rank Math es la autoridad principal para metadatos SEO y sitemap.
- El tema padre, WordPress core y plugins de terceros no deben modificarse.
- Los cambios funcionales deben realizarse en el child theme, plugins propios o contenido administrado correspondiente.

Consultar los documentos de dominio para las reglas completas.

## Git

Antes de modificar archivos:

```bash
git status --short
```

Reglas:

- Preservar cambios locales ajenos.
- No usar `git add .`.
- Seleccionar explícitamente las rutas que se agregan.
- No descartar cambios ajenos.
- No ejecutar `git reset --hard` ni `git clean -fd`.
- No forzar push ni reescribir historial compartido.
- No cambiar de rama si pone en riesgo cambios locales.
- No hacer commit, push, merge, revert o despliegue salvo solicitud explícita.
- No incluir archivos temporales, secretos, cachés o backups.
- Mantener los cambios limitados al alcance de la tarea.

Cuando se solicite un commit:

- Revisar el diff completo.
- Incluir únicamente archivos relacionados.
- Usar un mensaje que explique el propósito.
- No mezclar refactors no relacionados.

## Uso de especificaciones

Para tareas no triviales, buscar un `SPEC` en `docs/specs/`.

El agente debe implementar el comportamiento definido en el `SPEC`, sin reinterpretarlo silenciosamente.

Si el código actual contradice el `SPEC`:

1. Identificar la contradicción.
2. Verificar si el `SPEC` sigue vigente.
3. No cambiar el comportamiento sin dejar explícita la decisión.

Un `SPEC` describe una tarea concreta; no sustituye reglas permanentes del repositorio.

## Agentes y revisores

- Los agentes revisores trabajan en modo de solo lectura.
- Solo un agente debe modificar un mismo conjunto de archivos.
- El agente principal asigna responsabilidades no solapadas, consolida hallazgos y elimina duplicados.
- La escritura paralela requiere worktrees o entornos completamente separados.
- Los revisores no deben convertir preferencias personales en hallazgos.

## Proceso de trabajo

### Antes de implementar

1. Leer las reglas aplicables.
2. Revisar el `SPEC`.
3. Consultar el mapa del repositorio.
4. Ejecutar `git status --short`.
5. Identificar la fuente canónica.
6. Buscar implementaciones similares.
7. Identificar riesgos.
8. Definir cómo se validará el cambio.

### Durante la implementación

- Aplicar el cambio mínimo suficiente.
- Respetar la arquitectura existente.
- Evitar refactors no relacionados.
- No ocultar errores con fallbacks genéricos.
- No introducir dependencias sin justificarlo.
- No duplicar lógica existente.
- Mantener compatibilidad con datos existentes.
- Añadir o actualizar pruebas cuando cambie el comportamiento.

### Después de implementar

1. Revisar el diff completo.
2. Ejecutar validaciones relevantes.
3. Corregir errores producidos por el cambio.
4. Comprobar los criterios de aceptación.
5. Informar archivos modificados y pruebas ejecutadas.
6. Informar limitaciones, riesgos y validaciones pendientes.
7. No afirmar que algo funciona si no fue verificado.

## Validación mínima

Para cada PHP modificado:

```bash
php -l ruta/al/archivo.php
```

Para JavaScript y CSS:

- Ejecutar el linter o comprobación focalizada disponible.
- Revisar errores de consola.
- Comprobar carga de assets.
- Validar escritorio y móvil cuando el cambio sea visual.

Según el tipo de cambio:

- Redirecciones: verificar código HTTP, destino y ausencia de ciclos.
- Base de datos: crear backup, verificar contenido final y revisar diferencias.
- Inventario: consultar la fuente real y validar estructura y estado.
- Correo: confirmar recepción real; `wp_mail()` exitoso no demuestra entrega.
- Producción: purgar caché, verificar HTTP y navegador, y ejecutar control de sincronización.

No ejecutar toda la suite cuando una validación focalizada sea suficiente, salvo que exista riesgo de regresión transversal.

## Documentación técnica

Cuando una tarea dependa del comportamiento actual de una librería, framework, SDK, API, CLI o servicio cloud:

- Consultar documentación oficial vigente.
- Usar herramientas de documentación conectadas, como Context7, cuando apliquen.
- No confiar únicamente en conocimiento recordado.
- No inventar comandos, opciones o APIs.
- Distinguir comportamiento documentado de inferencias.

## Preguntas y decisiones

Antes de preguntar al usuario:

1. Inspeccionar el repositorio.
2. Leer el `SPEC`.
3. Revisar la documentación.
4. Buscar implementaciones similares.
5. Consultar las herramientas disponibles.

Preguntar únicamente cuando falte una decisión de negocio, un criterio de aceptación, una autorización o una elección con consecuencias distintas.

No preguntar por información que pueda obtenerse inspeccionando el proyecto. No iniciar cambios destructivos mientras exista una ambigüedad relevante.

## Comunicación

Los reportes, handoffs y resúmenes deben escribirse en español.

Al finalizar, informar:

- Qué se modificó.
- Qué archivos fueron afectados.
- Qué validaciones se ejecutaron.
- Qué se verificó localmente y en producción.
- Qué riesgos permanecen.
- Qué no pudo comprobarse.
- Qué quedó fuera del alcance.

No copiar secretos, credenciales, datos personales ni resultados no verificados.

## Mantenimiento de este archivo

Actualizar `AGENTS.md` únicamente cuando cambie una regla permanente, una fuente canónica, una restricción de seguridad o un procedimiento general.

No agregar aquí:

- Versiones o métricas actuales.
- Cantidades de registros.
- Resultados de auditorías puntuales.
- Incidentes temporales.
- Backlog o pendientes.
- Estado de una tarea.
- Hallazgos de una revisión.
- Información de un único ticket.

Ubicar esos datos en `docs/status/`, `docs/handoffs/`, `docs/specs/`, GitHub Issues o reportes de revisión.
