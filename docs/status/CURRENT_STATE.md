# Estado operativo actual

> Última verificación general: pendiente. Verificaciones focalizadas de producción: runtime PHP/LSAPI 2026-10-22T21:51:17Z; página 273 2026-09-16T16:32:17Z.

Este documento contiene observaciones temporales. No debe tratarse como fuente actual sin volver a verificar.

## Producción

- Sitio accesible: Sí en la comprobación focalizada.
- Código HTTP: 200 en la portada y `/wp-json/`.
- HTTPS: Sí.
- Estado de contenedores: `tmd_ols_wordpress` `running`, sin reinicios, con PHP 8.2.35 (LiteSpeed SAPI) y OpenLiteSpeed 1.9.3; `tmd_db` y `tmd_phpmyadmin` continuaron `running` sin recrearse.
- Deriva contra repositorio: `sync-production.sh --check` encontró diferencias previas en `.DS_Store` locales y en snapshots de páginas, entradas y plugins; `docker-compose.prod.yml` coincide entre el checkout y la configuración activa.

## WordPress

- Versión:
- Tema activo:
- Versión tema hijo:
- Versión tema padre:

## Plugins propios

| Plugin | Versión | Estado |
|---|---:|---|
| `tm-chatbot-fase1` | | |
| `tm-equipos-destacados-v2` | | |
| `tm-popup-bienvenida` | | |
| `tm-quiz-equipo-ideal` | | |

## Inventario

- Registros válidos:
- Montacargas:
- Baterías:
- Destacados:
- Registros inválidos:
- Fecha de consulta:

## SEO

- Páginas indexables:
- Artículos:
- URLs en sitemap:
- Errores HTTP:
- Search Console configurado:

## Correo

- Proveedor:
- Autorización:
- Última prueba real:
- Recepción confirmada:

## Derivas o incidentes

### Página 273: contención de saturación LSAPI (2026-09-16)

- Página observada: [Trabaja con nosotros](https://tecnimontacargas.com/nosotros/trabaja-con-nosotros/), WordPress `ID 273`.
- Durante la lentitud se verificaron avisos de saturación de los 20 procesos LSAPI y solicitudes automatizadas a rutas sensibles como `/.env` y `/.git/config`; esas rutas estaban llegando al flujo de WordPress antes de la contención.
- Contención vigente: bloqueo temprano con HTTP 403 para `.env`, `.git`, `.htaccess`, `.htpasswd` y `wp-config.php` en el `.htaccess` del volumen persistente de WordPress. El cambio no está en el child theme ni debe eliminarse al intervenir reglas del servidor.
- La regla requiere un reload gracioso de LiteSpeed para quedar activa. Backup verificado en `/opt/tecnimontacargas/backups/tmd-containment-20260916-161735/.htaccess`.
- Verificación productiva a las `2026-09-16T16:32:17Z`: las rutas sensibles devolvieron `403`, la página devolvió `200`, OLS permaneció `running` sin reinicio/OOM y no hubo avisos LSAPI posteriores a `15:32:07Z`.
- El deploy del carrusel no se confirmó como causa directa: la revisión del cambio mostró presentación/interacción del navegador sin nuevas consultas AJAX; la coincidencia temporal no prueba causalidad.
- Decisión operativa: no escalar todavía a `2 vCPU`; reevaluar si reaparece saturación LSAPI con tráfico legítimo después de conservar esta contención. No se modificaron firewall, Cloudflare ni un rate limit global.

## Pendientes observados

Los pendientes deben enlazar a un ticket o `SPEC`.

| Pendiente | Ticket/SPEC | Prioridad |
|---|---|---|
| | | |
