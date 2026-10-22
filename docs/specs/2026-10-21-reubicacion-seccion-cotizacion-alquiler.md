# SPEC: Reubicación de la sección de cotización de alquiler

## Estado

- Borrador

## Contexto

- [Solicitud] En la vista `/alquiler-montacargas-electricos/`, subir dos secciones el bloque que contiene el formulario de cotización.
- [Decisión del usuario] Las preguntas frecuentes deben moverse junto con el formulario para conservar el bloque visual compartido.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:101-120`] El orden actual al final de la landing es sectores, alquiler por temporada o proyecto, servicio técnico, cotización con FAQ y guías técnicas.
- [Evidencia: `scripts/commercial-landing-rental-v2.php:120-130`] Cotización y FAQ se emiten dentro de un único bloque `.tmd-rental-v2__quote-faq`.

## Problema

La cotización aparece después de dos secciones informativas; el usuario pidió adelantarla dos posiciones en la secuencia de la página.

## Objetivo

Presentar el bloque de cotización y preguntas frecuentes dos posiciones antes, inmediatamente después de “Sectores que atendemos” y antes de “Alquiler de montacargas por temporada o por proyecto”.

## Fuera del alcance

- Cambiar textos, campos, obligatoriedad, protecciones, destinatario, cabeceras o contenido de las preguntas frecuentes.
- Separar el formulario de las preguntas frecuentes o alterar su composición visual.
- Cambiar el orden o contenido de las demás secciones, estilos globales, SEO u otras páginas.

## Requisitos funcionales

1. [Solicitud] El bloque que contiene “Cotiza el alquiler de montacargas en Bogotá”, el formulario y las preguntas frecuentes aparece después de “Sectores que atendemos”.
2. [Solicitud] El bloque de cotización y FAQ aparece antes de “Alquiler de montacargas por temporada o por proyecto”.
3. [Solicitud] El formulario y las preguntas frecuentes permanecen juntos en el mismo bloque visual, según la aclaración del usuario.
4. [Evidencia: `scripts/commercial-landing-rental-v2.php:101-135`] Las otras secciones conservan su contenido y orden relativo.

## Reglas de negocio

- No aplica; el cambio solo modifica el orden visual de secciones.

## Contratos

### Entrada

No aplica.

### Salida

No aplica.

## Casos límite

- La sección se mantiene intacta en escritorio y móvil; no se divide la cotización y las FAQ.

## Archivos o módulos relacionados

- `scripts/commercial-landing-rental-v2.php`
- `tests/test-commercial-landing-rental-v2-render.php`
- Página WordPress 1558: `/alquiler-montacargas-electricos/`

## Criterios de aceptación

1. [Solicitud] El orden local renderizado es: sectores → cotización y FAQ → alquiler por temporada o proyecto → servicio técnico → guías técnicas.
2. [Solicitud] El formulario y las FAQ aparecen juntos, con el contenido y composición actuales, según la aclaración del usuario.
3. [Evidencia: `scripts/commercial-landing-rental-v2.php:76-135`] Las secciones no involucradas conservan su contenido y orden relativo.
4. [Solicitud] La página pública refleja el mismo orden en escritorio y móvil tras la actualización autorizada.

## Validación

- Pruebas unitarias: ampliar la prueba del renderer para comprobar el orden entre los bloques involucrados.
- Pruebas de integración: verificar que la página 1558 persista el orden nuevo sin cambios en el resto del contenido.
- Validación manual: revisar el orden y la composición del bloque en escritorio y móvil.
- Validación productiva: desplegar únicamente los archivos necesarios; antes de actualizar contenido persistido, verificar backup y rollback. Después confirmar el orden en la página pública y comprobar que las demás secciones mantienen su secuencia.

## Riesgos

- El renderer del repositorio define la fuente canónica, pero la página publicada conserva contenido en WordPress; actualizar solo el PHP no cambia por sí mismo el orden visible en producción.

## Decisiones pendientes

- Ninguna.
