# SPEC: Restauración visual de Equipos y Energía en el mega menú

## Estado

- Borrador

## Contexto

[Solicitud] Las tarjetas de Equipos y Energía deben volver a mostrar las imágenes que ya tenían antes de los ajustes de las tarjetas de Nosotros.

[Solicitud] La tarjeta Socios & Atención debe usar `img/personal/DSC03460.webp`.

[Evidencia: captura del usuario, 2026-08-14] Equipos y Energía actualmente presentan únicamente el fondo genérico de `.tmd-mm-img`.

[Evidencia: /opt/tecnimontacargas/backups/mega-menu-images-20260814-163000/tmd-mega-menu.css] El respaldo anterior contiene reglas específicas para las ilustraciones originales de Equipos y Energía, que fueron eliminadas del CSS desplegado.

[Evidencia: producción, assets/img/] Los siete recursos originales siguen disponibles: cuatro de Equipos (`menu-estibadores-apiladores.webp`, `menu-reach-retractiles.webp`, `menu-tomapedidos.webp`, `menu-contrabalanceados.webp`) y tres de Energía (`energy-baterias-plomo.webp`, `energy-bms.webp`, `energy-cargadores.png`).

[Evidencia: img/personal/DSC03460.webp] El recurso solicitado para Socios & Atención existe localmente (1920 × 1280).

## Problema

Las reglas y el marcado que hacían visibles las imágenes de Equipos y Energía no están presentes en las fuentes canónicas actuales; las tarjetas se muestran con un fondo genérico. Socios & Atención no usa la imagen concreta solicitada.

## Objetivo

Restaurar las imágenes originales de las tarjetas de Equipos y Energía con su presentación contenida, y sustituir exclusivamente la imagen de Socios & Atención por `DSC03460.webp`, sin alterar textos, enlaces, interacción ni las demás tarjetas de Nosotros y Servicios.

## Fuera del alcance

- Cambiar textos, enlaces, estructura funcional, comportamiento de apertura, teclado o responsive del mega menú.
- Reemplazar las imágenes de Compañía, Legal o Mantenimientos.
- Cambiar las imágenes originales de Equipos o Energía por otras nuevas.
- Modificar video, hero, header general, WordPress core, tema padre o plugins de terceros.

## Requisitos funcionales

1. [Solicitud] Las cuatro tarjetas de Equipos deben volver a mostrar sus respectivas imágenes originales.
2. [Solicitud] Las tres tarjetas de Energía deben volver a mostrar sus respectivas imágenes originales.
3. [Solicitud] Las imágenes de Equipos y Energía deben conservar proporción y mostrarse completas, sin el fondo genérico como contenido visual principal.
4. [Solicitud] Socios & Atención debe mostrar una versión web optimizada de `img/personal/DSC03460.webp`.
5. [Regla: docs/domain/NAVIGATION.md] El mega menú debe conservar apertura por hover, foco o clic, cierre por Escape o clic exterior y navegación por teclado.
6. [Regla: AGENTS.md] El cambio debe limitarse al child theme y a sus recursos; no se editarán el tema padre ni plugins de terceros.

## Reglas de negocio

- [Regla: AGENTS.md] La fuente canónica del header es `wp-content/themes/blocksy-child/template-parts/tmd-header.php`.
- [Regla: AGENTS.md] No se deben inventar imágenes ni hechos comerciales.
- [Regla: AGENTS.md] Producción requiere backup, despliegue solo de archivos afectados, purga de caché y validación posterior con autorización actual.

## Contratos

### Entrada

```json
{
  "panels": ["equipos", "energia", "nosotros"],
  "existingAssets": ["4 imágenes de Equipos", "3 imágenes de Energía"],
  "partnersSource": "img/personal/DSC03460.webp"
}
```

### Salida

```json
{
  "equipos": "4 imágenes originales visibles",
  "energia": "3 imágenes originales visibles",
  "sociosAtencion": "DSC03460.webp optimizada",
  "behavior": "sin cambios"
}
```

## Casos límite

- Los recursos originales existen en producción pero no están versionados localmente; deben incorporarse al child theme sin modificar sus referencias funcionales.
- La imagen de Socios & Atención debe recortarse con `object-fit: cover` sin exponer información sensible visible.
- En móvil, las imágenes no deben causar overflow horizontal ni impedir el foco o clic de su tarjeta.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-mega-menu.css`
- `wp-content/themes/blocksy-child/assets/img/`
- `wp-content/themes/blocksy-child/assets/images/mega-menu/menu-partners-support.webp`
- `img/personal/DSC03460.webp`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En escritorio, las capturas de Equipos y Energía muestran las siete imágenes originales, una por tarjeta correspondiente.
2. [Solicitud] Socios & Atención muestra `DSC03460.webp` procesada, mientras Compañía, Legal y Mantenimientos no cambian.
3. [Solicitud] Las imágenes de Equipos y Energía se ven completas, nítidas, proporcionadas y sin el fondo genérico dominante.
4. [Regla: docs/domain/NAVIGATION.md] Escritorio y móvil no presentan overflow horizontal y el menú conserva hover, foco, clic, Escape y clic exterior.
5. [Regla: AGENTS.md] Si se despliega, los recursos y archivos modificados quedan respaldados, su hash coincide en producción y se purga la caché aplicable.

## Validación

- Pruebas unitarias: No aplica; marcado, recursos y estilos visuales.
- Pruebas de integración: confirmar la correspondencia de las siete imágenes con sus tarjetas y que las URLs existentes se preservan.
- Validación manual: inspeccionar Equipos, Energía y Nosotros en escritorio y móvil; comprobar el encuadre y la interacción por teclado.
- Validación productiva: con autorización vigente, respaldar solo los archivos afectados, verificar sincronización, desplegar el manifiesto exacto, purgar LiteSpeed y comprobar HTTP, navegador, consola y hash.

## Riesgos

- Restaurar solo el CSS sin restaurar el marcado o los recursos mantendría las tarjetas vacías.
- Un recurso original no versionado puede volver a perderse en un despliegue posterior si no se incorpora al child theme canónico.
- Un encuadre demasiado cerrado de `DSC03460.webp` podría perder el contexto de atención.

## Decisiones pendientes

- No aplica. Se restaurarán las siete imágenes existentes verificadas en producción y se procesará `img/personal/DSC03460.webp` para Socios & Atención.
