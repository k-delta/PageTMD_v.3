# SPEC: Imágenes del mega menú de Nosotros y Servicios

## Estado

- Aprobado

## Contexto

[Solicitud] Las tarjetas vacías del mega menú Nosotros para Compañía, Socios & Atención y Legal deben mostrar imágenes relacionadas con cada concepto.

[Solicitud] La tarjeta Mantenimientos del menú Servicios debe usar la mejor imagen disponible y conservar la relación conceptual como las tarjetas de Equipos.

[Solicitud] Deben preferirse imágenes de `img/`; solo se podrán usar fuentes externas cuando no exista un recurso local útil.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:202-212] Mantenimientos ya contiene un enlace visual `.tmd-mm-img` vacío.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:215-227] Las tres columnas de Nosotros usan `<span class="tmd-mm-img">` sin imagen.

[Evidencia: wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css:287-306] `.tmd-mm-img` representa una tarjeta de 100 px de alto con fondo genérico y overlay; el mismo patrón se usa para los Equipos ilustrados en la captura aportada.

[Evidencia: img/mantenimientos/] Los recursos locales incluyen técnicos, herramientas, mantenimiento de montacargas y operación industrial.

[Evidencia: img/personal/] Los recursos locales incluyen equipo técnico y atención de oficina.

## Problema

Las tarjetas de Compañía, Socios & Atención, Legal y Mantenimientos muestran fondos genéricos, por lo que no tienen la referencia visual de las categorías de Equipos ni comunican su propósito.

## Objetivo

Mostrar una imagen local pertinente en cada una de las cuatro tarjetas del mega menú, manteniendo la estructura, enlaces e interacción existentes.

## Fuera del alcance

- Rediseñar el header, la grilla, textos, enlaces, rutas, menú móvil o comportamiento del mega menú.
- Modificar las tarjetas de Equipos o Energía que ya tienen su tratamiento visual.
- Generar, comprar o publicar multimedia externa sin selección documentada y autorización específica.
- Reemplazar imágenes locales por una externa si una local cumple el concepto y calidad necesaria.

## Requisitos funcionales

1. [Solicitud] Compañía debe mostrar una imagen que represente al equipo o la organización de Tecnimontacargas.
2. [Solicitud] Socios & Atención debe mostrar una imagen que represente atención, relación comercial o servicio de contacto.
3. [Solicitud] Legal debe mostrar una imagen asociada a documentación, cumplimiento o gestión corporativa, sin datos personales ni documentos reales legibles.
4. [Solicitud] Mantenimientos debe mostrar una imagen de intervención técnica o mantenimiento de montacargas.
5. [Solicitud] Las cuatro imágenes deben seleccionarse primero de `img/`; una fuente externa solo se considera si ninguna imagen local cumple el concepto y el recurso externo dispone de uso autorizado.
6. [Solicitud] Las imágenes deben cubrir la tarjeta sin distorsión y conservar contraste suficiente con el overlay existente.
7. [Regla: docs/domain/NAVIGATION.md] El mega menú debe conservar apertura por hover, foco o clic; cierre por Escape o clic exterior; e interacción por teclado.
8. [Regla: docs/domain/NAVIGATION.md] En escritorio y móvil no debe aparecer overflow horizontal.

## Reglas de negocio

- [Regla: AGENTS.md] El header canónico pertenece a `wp-content/themes/blocksy-child/template-parts/tmd-header.php` y solo admite parches focalizados.
- [Regla: AGENTS.md] No se deben inventar imágenes ni hechos comerciales.
- [Regla: AGENTS.md] No se deben versionar recursos nuevos ni modificar uploads sin autorización de publicación correspondiente.

## Contratos

### Entrada

```json
{
  "panels": ["nosotros", "mant"],
  "cards": ["Compañía", "Socios & Atención", "Legal", "Mantenimientos"],
  "sourcePriority": ["img/", "fuente externa autorizada"]
}
```

### Salida

```json
{
  "cards": {
    "Compañía": "imagen organizacional",
    "Socios & Atención": "imagen de atención",
    "Legal": "imagen corporativa sin datos personales",
    "Mantenimientos": "imagen de mantenimiento técnico"
  },
  "behavior": "enlaces y menú sin cambios"
}
```

## Casos límite

- La imagen elegida tiene orientación vertical o deja un motivo importante fuera de `object-fit: cover`.
- Una imagen de oficina muestra información sensible en monitores, documentos o credenciales.
- La imagen local no tiene proporción, nitidez o licencia adecuadas para el uso público.
- El menú se muestra en móvil, donde las tarjetas reducen su altura.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `img/mantenimientos/`
- `img/personal/`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] Cada una de las cuatro tarjetas muestra una imagen relacionada con su concepto y no mantiene el fondo genérico como contenido visual principal.
2. [Solicitud] Mantenimientos representa una actividad técnica de mantenimiento; Compañía, Socios & Atención y Legal representan sus conceptos sin exponer datos personales o documentos reales.
3. [Solicitud] Las imágenes conservan proporción, se ven nítidas, no se distorsionan y no introducen overflow en escritorio o móvil.
4. [Regla: docs/domain/NAVIGATION.md] Hover, foco, clic, Escape, clic exterior y teclado continúan funcionando sin errores de consola.
5. [Regla: AGENTS.md] Si se publica multimedia nueva, su origen y autorización quedan verificados antes de cambiar producción.

## Validación

- Pruebas unitarias: No aplica; marcado y estilos visuales.
- Pruebas de integración: comprobar que cada tarjeta contiene el recurso seleccionado y que las URLs de sus enlaces no cambian.
- Validación manual: revisar encuadre, contraste, recorte, escritorio, móvil y menú por teclado.
- Validación productiva: con autorización, respaldar archivos y uploads afectados, desplegar solo los recursos aprobados, purgar caché y verificar navegador, consola y sincronización.

## Riesgos

- Un recurso de oficina puede revelar información de pantalla o documentos.
- Una imagen externa puede no tener licencia de uso o estabilidad de URL.
- Un cambio de marcado puede alterar la semántica de las tarjetas o su foco por teclado.

## Decisiones pendientes

- No aplica. Se usarán y publicarán recursos locales autorizados: equipo técnico para Compañía, atención de oficina para Socios & Atención, gestión corporativa para Legal y técnico de montacargas para Mantenimientos.

## Registro de aprobación

- [Aprobación, 2026-08-14] El usuario autorizó seleccionar, procesar y publicar recursos de `img/` para estas cuatro tarjetas.
