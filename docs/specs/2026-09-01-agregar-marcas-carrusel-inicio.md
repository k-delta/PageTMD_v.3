# SPEC: Agregar Coéxito y Duncan al carrusel de marcas del inicio

## Estado

- Aprobado

## Contexto

[Solicitud] Se solicita agregar al carrusel de marcas del inicio las imágenes `coexito.webp` y `duncan.webp` que ya están subidas en los Medios de WordPress.

[Evidencia: production-snapshot/pages.json:39] El carrusel de marcas está incluido en el `post_content` de la página de inicio, identificada como página 47, y actualmente contiene las marcas Jungheinrich, Crown, Zowell, Toyota, Hyster, Yale y Barbillon.

[Evidencia: wp-content/themes/blocksy-child/inc/tmd-brand-carousel.php:5-31] El tema solo registra el CSS y JavaScript del carrusel; las diapositivas no se generan desde ese módulo.

[Evidencia: Medios WordPress, consulta de solo lectura 2026-09-01] Existen los adjuntos `duncan` y `coexito` con las URLs públicas `/wp-content/uploads/2026/09/duncan.webp` y `/wp-content/uploads/2026/09/coexito.webp`.

## Problema

[Solicitud] Las marcas Coéxito y Duncan ya disponen de imágenes en WordPress, pero todavía no aparecen como diapositivas del carrusel de marcas del inicio.

## Objetivo

[Solicitud] Mostrar las marcas Coéxito y Duncan dentro del carrusel de marcas del inicio usando sus imágenes existentes en Medios de WordPress, conservando el diseño y controles actuales.

## Fuera del alcance

- [Solicitud] No cambiar el diseño, tamaños, flechas, autoplay, transición ni comportamiento responsive del carrusel.
- [Solicitud] No eliminar ni reordenar las marcas existentes.
- [Regla: AGENTS.md] No modificar equipos, inventario, precios, disponibilidad ni datos de Firebase.
- [Regla: AGENTS.md] No escribir en producción, desplegar, purgar caché ni modificar uploads sin autorización operativa, backup y reversión identificada.
- [Regla: AGENTS.md] No editar `production-snapshot/pages.json` como sustituto del contenido administrado.

## Requisitos funcionales

1. [Solicitud] El carrusel del inicio debe incluir una diapositiva con la imagen `https://tecnimontacargas.com/wp-content/uploads/2026/09/coexito.webp` y texto alternativo `Coéxito`.
2. [Solicitud] El carrusel del inicio debe incluir una diapositiva con la imagen `https://tecnimontacargas.com/wp-content/uploads/2026/09/duncan.webp` y texto alternativo `Duncan`.
3. [Solicitud] Las diapositivas nuevas deben conservar las clases y atributos de carga compatibles con las diapositivas existentes.
4. [Solicitud] Las marcas existentes deben conservarse sin cambios de contenido, orden ni destino.
5. [Regla: docs/domain/NAVIGATION.md] El carrusel debe continuar funcionando con sus flechas, autoplay, teclado y comportamiento responsive sin overflow horizontal.
6. [Regla: AGENTS.md] La actualización debe aplicarse al contenido administrado de la página de inicio, no al snapshot de auditoría.

## Reglas de negocio

- [Regla: AGENTS.md] No inventar marcas ni imágenes; se usarán únicamente los adjuntos verificados en WordPress.
- [Regla: docs/runbooks/BACKUP_RESTORE.md] Toda modificación de `post_content` productivo requiere backup verificado y mecanismo de reversión.
- [Inferencia técnica] La transformación debe ser idempotente y detenerse si el contenido actual contradice las precondiciones esperadas.

## Contratos

### Entrada

```json
{
  "pageId": 47,
  "carousel": "tmd-brand-carousel",
  "newBrands": [
    {
      "name": "Coéxito",
      "url": "https://tecnimontacargas.com/wp-content/uploads/2026/09/coexito.webp"
    },
    {
      "name": "Duncan",
      "url": "https://tecnimontacargas.com/wp-content/uploads/2026/09/duncan.webp"
    }
  ]
}
```

### Salida

```json
{
  "pageId": 47,
  "carousel": "existing slides plus Coéxito and Duncan",
  "existingSlides": "unchanged",
  "duplicateApplication": "no additional duplicate slides"
}
```

## Casos límite

- [Inferencia técnica] Si una de las imágenes ya existe en el contenido, la aplicación debe aceptarla como estado idempotente y no duplicarla.
- [Inferencia técnica] Si el carrusel o alguna diapositiva existente no coincide con la estructura esperada, debe detenerse sin escritura parcial.
- [Inferencia técnica] Si el contenido de la página fue editado después de la evidencia disponible, debe compararse nuevamente antes de escribir.

## Archivos o módulos relacionados

- `production-snapshot/pages.json` como evidencia, no como destino de edición.
- `wp-content/themes/blocksy-child/inc/tmd-brand-carousel.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-brand-carousel.css`
- Página de inicio de WordPress, ID 47.
- `scripts/add-home-brand-barbillon.php` como patrón de transformación idempotente.

## Criterios de aceptación

1. [Solicitud] El DOM del inicio contiene una diapositiva de Coéxito con su URL de Medios y `alt="Coéxito"`.
2. [Solicitud] El DOM del inicio contiene una diapositiva de Duncan con su URL de Medios y `alt="Duncan"`.
3. [Solicitud] Las siete diapositivas existentes permanecen presentes, con su orden y URLs actuales.
4. [Solicitud] Las nuevas imágenes se muestran dentro del mismo diseño y límites visuales del carrusel.
5. [Regla: docs/domain/NAVIGATION.md] Las flechas, autoplay, teclado, responsive y ausencia de overflow horizontal se conservan.
6. [Regla: docs/runbooks/BACKUP_RESTORE.md] Antes de una eventual escritura productiva existe backup verificado y reversión identificada.

## Validación

- Pruebas unitarias: transformación focalizada sobre una copia del `post_content`; comprobar estado inicial, resultado, idempotencia y precondición contradictoria.
- Pruebas de integración: validar el script de actualización y comprobar que solo opera sobre la página 47 y el bloque `tmd-brand-carousel`.
- Validación manual: revisar el carrusel en escritorio y móvil, avanzar con flechas, observar autoplay, verificar carga de ambas imágenes, teclado y overflow.
- Validación productiva: Pendiente de autorización explícita; requiere backup, escritura controlada en WordPress, purga de caché y verificación HTTP/navegador según los runbooks.

## Riesgos

- [Inferencia técnica] Agregar diapositivas puede cambiar la frecuencia con que cada marca aparece en pantalla, aunque no modifica el diseño del carrusel.
- [Inferencia técnica] Las proporciones de los logos pueden producir tamaños visuales distintos dentro del mismo límite CSS.
- [Inferencia técnica] Una edición concurrente del contenido de Inicio podría sobrescribirse si no se compara inmediatamente antes de aplicar.

## Decisiones pendientes

- [Decisión resuelta, 2026-09-01] El usuario aprobó la incorporación de las dos diapositivas usando las URLs y textos alternativos definidos en esta SPEC.
- [Decisión resuelta, 2026-09-01] Se añadirá primero Coéxito y después Duncan, al final de las marcas existentes.
