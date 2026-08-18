# SPEC: Logo institucional en header y footer

## Estado

- Terminado

## Contexto

[Solicitud] El logo institucional vigente está publicado en `https://tecnimontacargas.com/wp-content/uploads/2026/08/logo-blanco.webp` y debe aparecer únicamente en el header y el footer.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-header.php:109] El header canónico ya referencia ese WebP.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:24] El footer canónico todavía representa la marca como texto plano `Tecnimontacargas`.

## Problema

La representación de marca del footer no usa el mismo recurso gráfico vigente del header, por lo que ambos componentes muestran identidades visuales diferentes.

## Objetivo

Mostrar el WebP institucional vigente en el header y el footer, sustituyendo en este último el texto plano de marca sin alterar su información, enlaces o estructura restante.

## Fuera del alcance

- Cambiar el archivo multimedia suministrado, su URL o sus metadatos.
- Usar el logo en componentes distintos del header y el footer.
- Rediseñar la navegación, columnas, enlaces, redes sociales, contacto o franja legal del footer.
- Modificar WordPress, el tema padre, plugins o contenido administrado.

## Requisitos funcionales

1. [Solicitud] El header debe continuar cargando `https://tecnimontacargas.com/wp-content/uploads/2026/08/logo-blanco.webp` como logo institucional.
2. [Solicitud] El texto plano `Tecnimontacargas` de la columna de marca del footer debe sustituirse por el mismo WebP.
3. [Solicitud] El logo debe mostrarse únicamente en el header y el footer dentro del alcance del tema propio.
4. [Regla: AGENTS.md] El recurso debe conservar su proporción y no debe inventarse, transformarse ni sustituirse por otra imagen.
5. [Regla: docs/runbooks/DEPLOYMENT.md] El cambio visual debe mantener un renderizado usable en escritorio y móvil, sin desbordamiento horizontal ni imágenes rotas.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública es `Tecnimontacargas`.
- [Regla: AGENTS.md] Los cambios funcionales deben realizarse en el child theme canónico.
- [Solicitud] Header y footer deben compartir exactamente la URL WebP proporcionada.

## Contratos

### Entrada

```json
{
  "logoUrl": "https://tecnimontacargas.com/wp-content/uploads/2026/08/logo-blanco.webp"
}
```

### Salida

```json
{
  "header": "logo WebP visible",
  "footer": "logo WebP visible en lugar del texto de marca"
}
```

## Casos límite

- El ancho disponible del footer se reduce en móvil.
- La imagen conserva dimensiones intrínsecas mayores que el espacio visual disponible.
- La caché de página conserva temporalmente el HTML anterior.
- El recurso WebP no responde o devuelve un tipo MIME incorrecto.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-header.php`
- `wp-content/themes/blocksy-child/template-parts/tmd-footer.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-footer.css`
- `docs/runbooks/DEPLOYMENT.md`
- `docs/runbooks/BACKUP_RESTORE.md`

## Criterios de aceptación

1. [Solicitud] El header carga la URL WebP indicada y conserva el logo visible.
2. [Solicitud] El footer ya no muestra `Tecnimontacargas` como texto de marca y muestra en su lugar la misma imagen.
3. [Solicitud] La imagen institucional no se agrega a ningún componente distinto del header y el footer.
4. [Regla: docs/runbooks/DEPLOYMENT.md] En escritorio y móvil, ambos logos cargan sin distorsión, imágenes rotas ni desbordamiento horizontal nuevo.
5. [Regla: docs/runbooks/DEPLOYMENT.md] La versión productiva pasa validación PHP, purga de caché, comprobación HTTP, navegador y revisión de errores de consola.

## Validación

- Pruebas unitarias: No aplica; el cambio es de marcado y estilos sin lógica funcional nueva.
- Pruebas de integración: comprobación focalizada de que el HTML del header y footer contiene la URL exacta y que el texto de marca fue retirado del bloque visual del footer.
- Validación manual: revisar proporción, tamaño, alineación y ausencia de overflow en escritorio y móvil.
- Validación productiva: con autorización, respaldar los archivos afectados, desplegar únicamente esos archivos, purgar LiteSpeed/WordPress, comprobar HTTP 200, imágenes cargadas, consola y sincronización.

## Evidencia de cierre

- [Revisión, 2026-08-11] El diff focalizado de `tmd-footer.php`, `tmd-footer.css` y esta SPEC terminó sin hallazgos.
- [Validación local, 2026-08-11] Header y footer superaron `php -l`; la URL WebP aparece exactamente una vez en cada componente y `git diff --check` no reportó errores.
- [Producción, 2026-08-11] Se respaldaron ambos archivos en `/opt/tecnimontacargas/backups/footer-logo-20260811-211413` y sus tamaños y SHA-256 fueron verificados antes del despliegue.
- [Producción, 2026-08-11] Se desplegaron únicamente `assets/css/tmd-footer.css` y `template-parts/tmd-footer.php`; sus hashes productivos coinciden con el manifiesto `ed178b57cfd85dbff2db01436c82c1c971e34ce0331fb51773ee91a621147108`.
- [Producción, 2026-08-11] LiteSpeed y la object cache de WordPress fueron purgados.
- [Navegador, 2026-08-11] En escritorio `1440x900` y móvil `390x844`, Home respondió HTTP 200; header y footer cargaron el mismo WebP `971x257`, el footer no conservó texto visual de marca, el logo se renderizó a `260x69`, y no hubo overflow, imágenes rotas, respuestas HTTP de error ni errores de consola.
- [Producción, 2026-08-11] No se detectaron errores PHP fatales nuevos, el modo de mantenimiento permaneció inactivo y `./scripts/sync-production.sh --check` confirmó coincidencia total.

## Riesgos

- Un tamaño CSS excesivo puede desalinear la primera columna del footer.
- La caché puede mostrar temporalmente el texto anterior.
- Una referencia duplicada o antigua puede hacer que header y footer diverjan nuevamente.

## Decisiones pendientes

- No aplica. `DEC-01` fue aprobada el 2026-08-11 e incluye implementación y despliegue productivo.
