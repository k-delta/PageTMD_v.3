# SPEC: Consistencia visual global según manual y revisión del Word

## Estado

- Aprobado por solicitud actual del usuario.

## Contexto

El documento `Sugerencias Página web 2 -Catalina.docx` incluye una sección final de recomendaciones globales de estilo y diseño. Esta sección no corresponde a una página puntual: define criterios transversales para la identidad visual del sitio.

La implementación debe respetar decisiones ya aprobadas y no reinterpretar como pendiente aquello que ya fue cerrado manualmente por el usuario.

## Objetivo

Alinear progresivamente las páginas públicas de Tecnimontacargas con la guía global indicada en el Word, priorizando consistencia de marca, legibilidad y una estética industrial ordenada.

## Alcance

1. Tipografía Work Sans en la interfaz pública propia.
2. Escala tipográfica de contenido:
   - H1 escritorio: 48–52 px.
   - H1 móvil: 34–40 px.
   - H2: 34–40 px.
   - H3 de sección: 24–28 px.
   - Título de tarjeta: 19–22 px.
   - Texto introductorio: 18–20 px.
   - Texto normal: 16–18 px.
   - Texto auxiliar: 14–16 px.
   - Botones/CTA: 16–18 px.
3. Jerarquía cromática basada en:
   - Azul empresarial `#128CEB`.
   - Azul americano `#262E4F`.
   - Azul metálico `#5E748B`.
   - Amarillo imperial `#FFC33C`.
   - Complementarios `#3C3C3C`, `#E6E6E6` y blanco.
4. Los dos azules principales deben mantener el mayor peso visual; el amarillo se usa como acento, no como fondo dominante repetido.
5. Las tintas claras derivadas de la paleta pueden usarse para variedad sin introducir familias cromáticas ajenas.
6. Ritmo de fondos: blanco / gris muy claro / blanco / azul oscuro / blanco, evitando convertir cada grupo informativo en un fondo de color diferente.
7. Tarjetas con radios visuales de referencia de 8–12 px y sombras suaves.
8. Sistema de CTA:
   - Principal claro: fondo `#128CEB`, texto blanco.
   - Sobre fondo azul oscuro: fondo `#FFC33C`, texto `#262E4F`.
   - Secundario claro: fondo transparente, borde/texto `#128CEB`.
   - Tamaño 16–18 px, Work Sans SemiBold, radio de 8 px salvo control funcional que requiera otra forma.
9. Alineación:
   - Título grande de sección: preferiblemente a la izquierda.
   - Título inmediatamente encima de un grupo compacto de tarjetas: puede centrarse.
   - Texto de más de tres líneas: alineado a la izquierda.
10. Mantener mensajes cortos y precisos y el tono de confianza técnica + acompañamiento al cliente.

## Decisiones que se conservan

- `docs/specs/2026-08-10-espaciado-vertical-sitio.md` sigue siendo la autoridad para espaciado vertical: 48 px en escritorio y 32 px en móvil para secciones auditadas. La recomendación general de “dar más aire” no debe incrementar esos valores sin una nueva decisión explícita.
- Los SPEC activos de componentes concretos conservan su estructura, contenido, imágenes y comportamiento. Esta normalización solo ajusta atributos visuales cuando no contradicen un requisito específico vigente.

## Fuera del alcance

- Cambiar fotografías o imágenes del menú.
- Modificar imágenes de portada.
- Modificar nuevamente las páginas de Cargadores, Baterías o BMS, que el usuario declaró cerradas manualmente.
- Cambiar contenido comercial, inventario, enlaces o comportamiento funcional.
- Reescribir masivamente textos fuera de requerimientos concretos del Word.
- Modificar tema padre o plugins de terceros.

## Páginas/componentes a normalizar en esta fase

- Home, preservando sus SPEC de portada y tarjetas.
- Catálogo de Equipos.
- Quiénes somos.
- Blog y artículos.
- Mantenimientos.
- Alianzas/socios cuando reutilicen el sistema visual propio.
- Componentes globales propios cuando corresponda: botones estándar y tipografía.

## Exclusiones explícitas por ID

No aplicar overrides visuales específicos de esta fase sobre:

- Página 255: Cargadores.
- Página 401: Baterías.
- Página 792: BMS.

## Criterios de aceptación

1. Work Sans sigue cargándose globalmente y no se introduce otra familia tipográfica.
2. Los H1 de las páginas auditadas no superan 52 px en escritorio y se mantienen entre 34–40 px en móvil, salvo excepción documentada por un SPEC de composición.
3. Botones/CTA de contenido auditados usan 16–18 px y un radio cercano a 8 px.
4. Tarjetas auditadas reducen radios excesivos a 12 px y mantienen sombras discretas.
5. Los amarillos aproximados como `#FFBE31` se sustituyen por `#FFC33C` en componentes propios incluidos en esta fase.
6. No se modifican imágenes, contenido ni enlaces.
7. No se modifican visualmente las páginas 255, 401 o 792 por los nuevos selectores.
8. El espaciado vertical auditado permanece en 48 px escritorio / 32 px móvil.
9. No se introduce overflow horizontal ni se recortan textos o controles en escritorio o móvil.

## Validación

- Revisar el diff completo.
- `php -l` para cada PHP modificado.
- Verificar que los CSS nuevos/cambiados carguen en producción.
- Comprobar Home, Equipos, Quiénes somos, Blog, Mantenimiento preventivo y Mantenimiento correctivo en escritorio y móvil.
- Verificar que Cargadores, Baterías y BMS no reciben los nuevos overrides específicos.
- Purgar caché después del despliegue.
