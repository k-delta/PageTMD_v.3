# SPEC: Proporción vertical de servicios en Inicio

## Estado

- Aprobado

## Contexto

[Solicitud] En la sección de Inicio “Mantenimiento y soporte especializado”, las tarjetas se perciben demasiado altas: en la captura aportada no se alcanza a ver su final en una primera vista de escritorio.

[Evidencia: production-snapshot/pages.json:39] El bloque corresponde a la página 47 y contiene el encabezado “Mantenimiento y soporte especializado” más tres tarjetas de servicios, sin que su HTML defina alturas propias.

[Evidencia: wp-content/themes/blocksy-child/style.css:1280-1480] La presentación actual combina separaciones del encabezado, tipografías responsivas, `min-height: 320px`, relleno interno y distribución vertical de las tarjetas.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:414-444] El padding externo de la sección ya se limita a `48px` en escritorio y `32px` en móvil mediante reglas específicas de Inicio.

## Problema

[Solicitud] La sección de servicios ocupa una altura excesiva en escritorio y obliga a desplazarse para ver el cierre de las tarjetas, aun cuando el usuario la visita por primera vez.

## Objetivo

[Solicitud] Reducir de forma proporcionada la altura visual de la sección de servicios de Inicio para que el conjunto sea más compacto, conservando legibilidad y la jerarquía actual de las tarjetas.

## Fuera del alcance

- [Solicitud] No cambiar textos, enlaces, orden, cantidad de tarjetas, colores ni funcionalidades de la sección.
- [Solicitud] No cambiar otras secciones de Inicio ni páginas internas.
- [Regla: AGENTS.md] No modificar el tema padre, WordPress core ni plugins de terceros.
- [Regla: AGENTS.md] No desplegar ni modificar contenido productivo sin autorización explícita posterior.

## Requisitos funcionales

1. [Solicitud] La sección “Mantenimiento y soporte especializado” debe ocupar menos altura vertical en escritorio que su presentación actual.
2. [Solicitud] Las tres tarjetas deben conservar todos sus textos, iconos, llamadas a la acción y enlaces actuales.
3. [Solicitud] La compactación debe reducir espacios internos y externos antes de disminuir la legibilidad tipográfica.
4. [Solicitud] Las tarjetas deben conservar una altura derivada de su contenido, sin introducir una altura fija que pueda recortar texto al cambiar el viewport.
5. [Regla: AGENTS.md] El ajuste debe quedar limitado a la portada mediante selectores de `body.page-id-47`.
6. [Regla: AGENTS.md] La versión móvil debe conservar contenido legible, sin desbordamiento horizontal, recortes ni solapamientos.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública continúa siendo `Tecnimontacargas`.
- [Regla: AGENTS.md] No se deben alterar hechos comerciales, servicios ofrecidos, enlaces ni contenido administrado.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- [Solicitud] Los títulos de tarjeta que ocupen varias líneas deben seguir completos y legibles.
- [Regla: AGENTS.md] En móvil no debe aparecer desplazamiento horizontal ni superposición entre el texto, icono y llamada a la acción.
- [Evidencia: production-snapshot/pages.json:39] Las tres tarjetas tienen longitudes de texto distintas; la cuadrícula debe conservar una composición coherente sin ocultar contenido.

## Archivos o módulos relacionados

- [Evidencia: wp-content/themes/blocksy-child/style.css:1251-1513] Estilos visuales y responsive específicos de la sección de servicios de la página 47.
- [Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-home-blocks.css:414-444] Escala vertical complementaria específica de Inicio, que debe conservarse al ajustar la sección.
- [Evidencia: production-snapshot/pages.json:39] HTML administrado de la página de Inicio que define el contenido y los enlaces a preservar.

## Criterios de aceptación

1. [Solicitud] En una vista de escritorio de `1440x900`, la altura de la sección se reduce de forma apreciable respecto al estado actual y el final visual de las tarjetas queda más próximo a la primera vista, sin necesidad de incrementar su alto para equilibrarlas.
2. [Solicitud] Las tres tarjetas conservan íntegros título, descripción, icono y llamada a la acción.
3. [Solicitud] La sección conserva su jerarquía visual: eyebrow, encabezado, texto descriptivo y tarjetas siguen siendo claramente distinguibles.
4. [Regla: AGENTS.md] En `1440x900` y `390x900` no se introducen recortes, solapamientos, errores de consola ni desplazamiento horizontal.
5. [Regla: AGENTS.md] El CSS modificado continúa encapsulado en `body.page-id-47`, sin efectos en páginas internas.

## Validación

- Pruebas unitarias: No aplica; el cambio previsto es CSS de presentación sin lógica ejecutable.
- Pruebas de integración: Comprobar que el asset CSS de Inicio carga y que los selectores aplican únicamente en la página 47.
- Validación manual: Comparar la sección en `1440x900` y `390x900`; revisar la altura total, final de tarjetas, legibilidad, enlaces, hover/focus, consola y overflow horizontal.
- Validación productiva: Con autorización explícita, crear backup focalizado, comprobar deriva, desplegar únicamente el archivo modificado, purgar caché, validar HTTP y navegador en ambos viewports y ejecutar el control de sincronización posterior.

## Riesgos

- [Inferencia técnica] Reducir relleno o márgenes en exceso puede disminuir la separación entre elementos o volver demasiado densas las tarjetas.
- [Inferencia técnica] Una reducción mediante altura fija produciría recortes cuando cambien los textos o el viewport; debe evitarse.
- [Inferencia técnica] La caché de LiteSpeed puede mostrar temporalmente el CSS previo tras un despliegue.

## Decisiones pendientes

- [Solicitud, 2026-08-14] El usuario aprobó la implementación local. Un despliegue a producción continúa requiriendo autorización explícita posterior.
