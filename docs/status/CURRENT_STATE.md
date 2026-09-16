# Estado operativo actual

> Última verificación general: pendiente. Verificación focalizada de producción para la página 273: 2026-09-16T16:32:17Z.

Este documento contiene observaciones temporales. No debe tratarse como fuente actual sin volver a verificar.

## Producción

- Sitio accesible:
- Código HTTP:
- HTTPS:
- Estado de contenedores:
- Deriva contra repositorio:

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
