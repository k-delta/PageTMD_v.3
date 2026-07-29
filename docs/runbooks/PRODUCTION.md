# Producción

## Propósito

Describe el entorno productivo, rutas operativas y comandos básicos de diagnóstico.

Para desplegar, consultar `DEPLOYMENT.md`. Para backups y restauración, consultar `BACKUP_RESTORE.md`.

## Sitio

- Dominio canónico: `https://tecnimontacargas.com`
- HTTPS obligatorio.
- `www` debe redirigir al dominio canónico.

## Infraestructura

```text
VPS
└── Docker Compose
    ├── tmd_ols_wordpress
    │   └── OpenLiteSpeed + WordPress
    ├── tmd_db
    │   └── MariaDB
    └── tmd_phpmyadmin
        └── Administración restringida
```

### Sistema

- Proveedor: Vultr.
- Región: Miami.
- Sistema operativo: Alpine Linux.
- Administrador de servicios: OpenRC.
- Los volúmenes Docker son externos y persistentes.

## Rutas

| Elemento | Ruta |
|---|---|
| Stack | `/opt/tecnimontacargas` |
| Compose | `/opt/tecnimontacargas/docker-compose.prod.yml` |
| WordPress en contenedor | `/var/www/vhosts/localhost/html` |

Verificar estas rutas antes de una operación destructiva.

## Servicios restringidos

- MariaDB no debe exponerse públicamente.
- phpMyAdmin debe permanecer enlazado a `127.0.0.1:8081`.
- OpenLiteSpeed Admin debe permanecer enlazado a `127.0.0.1:7080`.
- No abrir puertos administrativos por conveniencia.

## Secretos

No almacenar en este documento ni en Git:

- Contraseñas.
- Claves SSH.
- Tokens.
- Certificados privados.
- `.env` o `wp-config.php`.
- Secretos de Firebase o MariaDB.
- Backups con datos reales.

## Inspección inicial

Antes de reiniciar o modificar:

1. Capturar el estado actual.
2. Revisar contenedores y logs.
3. Verificar espacio en disco.
4. Verificar respuestas HTTP.
5. Preservar evidencia relevante.

### Estado de contenedores

```bash
docker compose \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  ps
```

### Servicios definidos

```bash
docker compose \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  config --services
```

### Logs de WordPress

```bash
docker compose \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  logs --tail=100 tmd_ols_wordpress
```

### Logs de MariaDB

```bash
docker compose \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  logs --tail=100 tmd_db
```

### Espacio

```bash
df -h
docker system df
```

No ejecutar limpiezas automáticas después de consultar el uso de Docker.

## Verificación HTTP

```bash
curl -I https://tecnimontacargas.com
curl -I https://www.tecnimontacargas.com
```

Verificar código HTTP, redirección, certificado y ausencia de ciclos.

## Inspección WordPress

```bash
docker exec tmd_ols_wordpress \
  wp core version \
  --allow-root \
  --path=/var/www/vhosts/localhost/html
```

```bash
docker exec tmd_ols_wordpress \
  wp theme list \
  --status=active \
  --allow-root \
  --path=/var/www/vhosts/localhost/html
```

```bash
docker exec tmd_ols_wordpress \
  wp plugin list \
  --allow-root \
  --path=/var/www/vhosts/localhost/html
```

Son comandos de inspección. No actualizar componentes sin solicitud explícita.

## Acciones que requieren autorización

- Reiniciar o detener contenedores.
- Reconstruir imágenes.
- Modificar Docker Compose.
- Modificar DNS, TLS o puertos.
- Eliminar volúmenes.
- Limpiar recursos Docker.
- Actualizar WordPress, plugins o temas.
- Ejecutar escrituras masivas en MariaDB.
- Restaurar backups.
- Desplegar archivos.

Un reinicio no debe ser la primera acción de diagnóstico.
