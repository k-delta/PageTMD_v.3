# SPEC: Actualizar el aviso de marca registrada del footer

## Estado

- Aprobado

## Contexto

[Solicitud: captura aportada el 2026-09-09] Se solicita cambiar el aviso legal
visible del footer de la página para que use una marca registrada con el
símbolo `®` en lugar del aviso `Copyright ©`.

[Evidencia: wp-content/themes/blocksy-child/template-parts/tmd-footer.php:129-145]
El footer canónico renderiza la sección legal con el aviso dinámico de año,
la marca Tecnimontacargas y los enlaces legales.

## Problema

[Solicitud] El aviso visible del footer inicia actualmente con `Copyright ©`,
pero debe comunicar la marca registrada mediante el símbolo `®`.

## Objetivo

[Solicitud] Mostrar en el footer el aviso `Marca Registrada ®` seguido del año
dinámico, `Tecnimontacargas` y el texto `Todos los derechos reservados.` sin
alterar el resto del footer.

## Fuera del alcance

- [Solicitud] No modificar el año dinámico, el nombre Tecnimontacargas, el texto
  de reserva de derechos ni los enlaces legales existentes.
- [Solicitud] No cambiar estilos, estructura HTML, responsive, navegación,
  contenido administrado de WordPress ni otras secciones del sitio.
- [Regla: AGENTS.md] No modificar el tema padre, WordPress core, plugins de
  terceros, snapshots ni archivos temporales.
- [Regla: AGENTS.md] No desplegar archivos distintos del objetivo ni escribir
  en producción sin backup verificable, control de deriva, reversión y
  validaciones productivas.

## Requisitos funcionales

1. [Solicitud] El aviso legal del footer debe mostrar exactamente el prefijo
   `Marca Registrada ®` en lugar de `Copyright ©`.
2. [Solicitud] El aviso debe conservar el año generado dinámicamente, el texto
   `Tecnimontacargas. Todos los derechos reservados.` y su posición dentro de
   la sección legal.
3. [Solicitud] Los enlaces `Devoluciones`, `Garantías`, `SG-SST`, `Privacidad`
   y `Términos` deben conservar sus destinos y orden actuales.
4. [Regla: AGENTS.md] La implementación debe realizarse en la fuente canónica
   `wp-content/themes/blocksy-child/template-parts/tmd-footer.php`.

## Reglas de negocio

- [Regla: AGENTS.md] La marca pública del sitio es Tecnimontacargas.
- [Regla: AGENTS.md] Los cambios funcionales deben realizarse en el child theme
  activo y mantenerse limitados al alcance solicitado.

## Contratos

### Entrada

```json
{
  "template": "wp-content/themes/blocksy-child/template-parts/tmd-footer.php",
  "currentPrefix": "Copyright ©",
  "targetPrefix": "Marca Registrada ®",
  "year": "dynamic",
  "brand": "Tecnimontacargas"
}
```

### Salida

```json
{
  "legalNotice": "Marca Registrada ® {year} Tecnimontacargas. Todos los derechos reservados.",
  "legalLinks": "unchanged",
  "footerLayout": "unchanged",
  "responsiveBehavior": "unchanged"
}
```

## Casos límite

- [Inferencia técnica] El símbolo `®` debe conservarse como carácter Unicode
  visible en el HTML renderizado y no convertirse en una entidad ausente o en
  texto ilegible.
- [Inferencia técnica] El año debe continuar actualizándose dinámicamente sin
  hardcodear 2026.
- [Inferencia técnica] El cambio de longitud del aviso no debe producir
  overflow horizontal en los breakpoints existentes del footer.

## Archivos o módulos relacionados

- `wp-content/themes/blocksy-child/template-parts/tmd-footer.php`
- `wp-content/themes/blocksy-child/assets/css/tmd-footer.css`
- `docs/runbooks/PRODUCTION.md`
- `docs/runbooks/DEPLOYMENT.md`
- `docs/runbooks/BACKUP_RESTORE.md`
- `scripts/sync-production.sh`
- `scripts/deploy-production.sh`

## Criterios de aceptación

1. [Solicitud] El HTML del footer muestra `Marca Registrada ®` y no muestra
   `Copyright ©` en el aviso legal.
2. [Solicitud] El aviso conserva el año dinámico, `Tecnimontacargas`,
   `Todos los derechos reservados.` y la posición visual actual.
3. [Solicitud] Los cinco enlaces legales conservan exactamente sus destinos,
   etiquetas y orden.
4. [Regla: AGENTS.md] Solo se modifica el archivo canónico del footer para la
   implementación funcional.
5. [Regla: AGENTS.md] La publicación productiva, si se ejecuta, usa backup
   verificable, control de deriva, despliegue focalizado, purga de caché
   aplicable y verificación posterior.

## Validación

- Pruebas unitarias: No aplica; el cambio es una sustitución literal en una
  plantilla PHP.
- Pruebas de integración: Ejecutar `php -l` sobre el template y comprobar con
  búsqueda focalizada la presencia única del prefijo objetivo y la ausencia del
  prefijo anterior.
- Validación manual: Revisar el footer en escritorio y móvil, confirmando el
  símbolo `®`, el año, el texto restante, el layout y la ausencia de overflow.
- Validación productiva: Después de la aprobación e implementación, requiere
  backup del archivo, `./scripts/sync-production.sh --check` antes y después,
  despliegue del alcance exacto, purga de caché aplicable y verificación HTTP y
  visual en `https://tecnimontacargas.com`.

## Riesgos

- [Inferencia técnica] Una caché de página o de objeto podría mantener el
  aviso anterior después del despliegue y requerir purga antes de la
  verificación visual.
- [Inferencia técnica] La diferencia de longitud entre ambos prefijos podría
  afectar el ajuste en anchos móviles estrechos.

## Decisiones pendientes

- [Decisión resuelta, 2026-09-09] El usuario aprobó este SPEC y el literal
  `Marca Registrada ® 2026 Tecnimontacargas. Todos los derechos reservados.`;
  el año seguirá siendo dinámico en la implementación.
