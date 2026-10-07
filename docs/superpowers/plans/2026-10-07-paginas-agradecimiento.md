# Plan de implementación: páginas de agradecimiento

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Crear páginas de agradecimiento distintas para baterías y montacargas y dirigir cada formulario a su destino solo después de un envío exitoso en Contact Form 7.

**Architecture:** Un módulo del child theme define el contenido, el shortcode y la configuración de redirección. Un JavaScript pequeño se carga solo en las dos landings comerciales, escucha el evento wpcf7mailsent y relaciona CF7 1557 con baterías y 1556 con montacargas. Un script WP-CLI con dry-run crea las páginas y guarda los robots de Rank Math; las escrituras requieren un backup reciente y verificado.

**Tech Stack:** WordPress child theme, PHP/WP-CLI, eventos frontend de Contact Form 7, Rank Math, JavaScript, CSS y pruebas focales de PHP y Node.

**Spec:** docs/specs/2026-10-07-gracias-baterias-y-montacargas.md

## Restricciones globales

- La implementación no modifica los campos, consentimiento, controles antispam ni configuración de correo existentes.
- El formulario 1557 conduce al destino de agradecimiento de baterías.
- El formulario 1556 conduce al destino de agradecimiento de montacargas.
- La URL de salida no contiene los valores enviados por el visitante.
- Rank Math conserva la autoridad de canonical, robots y sitemap de las páginas nuevas.
- La validación productiva requiere autorización separada, backup verificado, purga de caché y comprobaciones públicas.
- No hacer commit, push, merge ni despliegue en este trabajo.

## Enfoque de revisión

- Contact Form 7 entrega el ID como número o cadena; el script normaliza ambos y permite únicamente las asociaciones 1557 → /gracias-baterias/ y 1556 → /gracias-montacargas/.
- Los eventos de error wpcf7invalid, wpcf7spam, wpcf7mailfailed y wpcf7aborted no redirigen; la prueba JS simula cada uno.
- Un evento sin detail.contactFormId o con un ID ajeno se ignora; la prueba JS cubre ambos casos.
- El destino no acepta URL externa, parámetros ni hash; la prueba JS verifica que solo navegue al path local configurado.
- Una página previa con el slug esperado pero título o contenido ajeno no se sobrescribe; la prueba WP-CLI debe detenerse ante ese conflicto.

---

### Tarea 1: Renderizado de páginas y robots SEO

**Archivos:**
- Crear: wp-content/themes/blocksy-child/inc/tmd-commercial-landing-thank-you.php
- Crear: wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-thank-you.css
- Modificar: wp-content/themes/blocksy-child/functions.php
- Prueba: tests/test-commercial-landing-thank-you-pages.php

**Interfaces:**
- Crear tmd_commercial_thank_you_page_specs(): array, con entradas battery y forklift; cada entrada define slug, título visible, shortcode y robots.
- Crear el shortcode [tmd_commercial_thank_you type="battery|forklift"] con un único H1 y el mensaje “Hemos recibido tu solicitud. Gracias por contactar a Tecnimontacargas.”
- Usar los slugs gracias-baterias y gracias-montacargas. Los títulos visibles serán “Gracias por tu solicitud de baterías” y “Gracias por tu solicitud de montacargas”.
- Guardar robots Rank Math [noindex, follow]; Rank Math excluye del sitemap las páginas marcadas noindex.
- Conservar el header y footer globales, ocultar el título duplicado de plantilla y no añadir CTA.

- [x] **Paso 1: Escribir la prueba PHP que falla.** Comprobar las dos definiciones, slugs, títulos, shortcodes, H1 distintos, mensaje, clase de página y salida del renderer.
- [x] **Paso 2: Ejecutar la prueba y confirmar el fallo** porque el módulo y las definiciones no existen.
- [x] **Paso 3: Implementar el módulo PHP y el CSS focalizados.** Registrar el renderer, clases del body, ocultamiento del título duplicado y carga del CSS solo en las dos páginas de agradecimiento.
- [x] **Paso 4: Ejecutar la prueba focal y PHP lint.** Resultado esperado: contenido y configuración pasan; sin errores de sintaxis PHP.

### Tarea 2: Redirección tras envío exitoso de Contact Form 7

**Archivos:**
- Crear: wp-content/themes/blocksy-child/assets/js/tmd-commercial-landing-thank-you-redirect.js
- Modificar: wp-content/themes/blocksy-child/inc/tmd-commercial-landing-thank-you.php
- Prueba: tests/test-commercial-landing-thank-you-redirect.js

**Interfaces:**
- PHP publica configuración únicamente en la landing 1558 (formulario 1556) y la landing 1559 (formulario 1557).
- JavaScript lee window.tmdCommercialThankYouRedirects, escucha solo wpcf7mailsent, normaliza detail.contactFormId y navega mediante location.assign a un path del mismo origen.
- No se agregan entradas del formulario ni event.detail a la URL de destino.

- [x] **Paso 1: Escribir la prueba Node que falla.** Ejecutar el asset en un VM con document y window simulados; comprobar destinos correctos para 1557/1556 y que errores, IDs desconocidos, detail ausente y destinos externos no naveguen.
- [x] **Paso 2: Ejecutar la prueba y confirmar el fallo** porque no existe el asset ni la configuración por landing.
- [x] **Paso 3: Implementar el listener de éxito y la carga condicional.** Cargarlo solo en las landings de alquiler y baterías; configurar el ID de CF7 y destino permitido para cada landing.
- [x] **Paso 4: Ejecutar la prueba Node y node --check.** Resultado esperado: solo el evento wpcf7mailsent correcto navega y la URL no contiene datos del formulario.

### Tarea 3: Creación protegida de las dos páginas WordPress

**Archivos:**
- Crear: scripts/create-commercial-landing-thank-you-pages.php
- Prueba: tests/test-commercial-landing-thank-you-pages-update.php
- Reutilizar: tmd_commercial_thank_you_page_specs() del nuevo módulo del tema

**Interfaces:**
- WP-CLI usa dry-run por defecto y requiere TMD_COMMERCIAL_THANK_YOU_PAGES_EXECUTE=1 y TMD_VERIFIED_BACKUP_PATH para escribir.
- Crear/publicar solo las dos páginas definidas en el módulo, guardar rank_math_robots como [noindex, follow] y verificar slug, estado, hash del contenido y metadatos después de escribir.
- Las páginas coincidentes son idempotentes. Un conflicto de slug o contenido/metadatos inesperados detiene la operación sin sobrescribir.
- Una escritura exige el contrato de backup completo, reciente y verificado del repositorio, además de un snapshot privado de rollback.

- [x] **Paso 1: Escribir las pruebas fallidas del actualizador.** Cubrir dry-run, requisito de backup, creación, idempotencia, colisiones de slug, hash de contenido, metadata noindex y rollback ante fallo parcial.
- [x] **Paso 2: Ejecutar las pruebas y confirmar el fallo** porque no existe el creador protegido.
- [x] **Paso 3: Implementar el creador WP-CLI con dry-run inicial.** Reutilizar patrones de backup, lock, transacción, snapshot y conciliación de estado de los actualizadores de alquiler/baterías; no alterar las landings ni su configuración CF7.
- [x] **Paso 4: Ejecutar la prueba focal del actualizador.** Resultado esperado: dry-run no escribe; ejecución verificada crea páginas/metadatos; los guards impiden sobrescrituras.

### Tarea 4: Verificación local y cierre del SPEC

**Archivos:**
- Modificar: docs/specs/2026-10-07-gracias-baterias-y-montacargas.md
- Validar: PHP, JS, CSS y pruebas focales modificadas

- [x] **Paso 1: Tras aprobar el plan, resolver DEC-01 a DEC-04** con los valores aquí indicados y registrar en el SPEC la instrucción del usuario de implementar.
- [x] **Paso 2: Ejecutar pruebas PHP/Node, PHP lint, node --check, comprobaciones CSS disponibles y git diff --check.**
- [x] **Paso 3: Revisar el diff** y comprobar que los campos, consentimiento, antispam, destinatarios y cuerpos de correo no cambiaron.
- [x] **Paso 4: Reportar evidencia por capa.** La verificación local no afirma que las páginas estén publicadas en WordPress ni que se hayan comprobado HTTP, sitemap o envíos de navegador en producción.
