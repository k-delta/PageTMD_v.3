# Versionado de producción

Este repositorio conserva el código propio que se ejecuta en
`https://tecnimontacargas.com` y un snapshot legible del contenido público.
El objetivo es detectar cambios hechos directamente en el servidor y poder
recuperar el código sin depender únicamente del volumen Docker.

## Fuente versionada

```text
wp-content/themes/blocksy-child/
wp-content/plugins/tm-chatbot-fase1/
wp-content/plugins/tm-equipos-destacados-v2/
wp-content/plugins/tm-popup-bienvenida/
wp-content/plugins/tm-quiz-equipo-ideal/
production-snapshot/
docker-compose.prod.yml
```

`tmd-site-kit/` es código histórico y no está activo en producción.
`.codex-tmp/` contiene copias de trabajo y tampoco es fuente de despliegue.

## Comprobar diferencias

```bash
./scripts/sync-production.sh --check
```

El comando descarga a un directorio temporal el child theme, los plugins
propios, páginas, artículos, snippets, inventario de plugins/temas y Compose.
Finaliza con código `0` si todo coincide y con código `1` si existe deriva.

## Incorporar un cambio realizado de emergencia en producción

1. Confirmar que no haya trabajo local sin guardar en las rutas productivas.
2. Ejecutar:

   ```bash
   ./scripts/sync-production.sh --pull
   ```

3. Revisar `git diff`, validar sintaxis y comportamiento.
4. Hacer un commit descriptivo y subirlo al remoto Git.

`--pull` se detiene si encuentra modificaciones locales en las rutas que
sobrescribiría.

## Flujo normal recomendado

1. Ejecutar `--check`.
2. Hacer el cambio en las rutas canónicas del repositorio.
3. Crear backup de base de datos y del child theme en el VPS.
4. Desplegar solamente los archivos modificados.
5. Validar PHP/JS, caché, HTTP y navegador.
6. Ejecutar nuevamente `--check`.
7. Hacer commit y push.

No se debe volver a editar `.codex-tmp/production-theme` ni desplegar el
child theme completo desde una copia temporal.

## Qué no se guarda en Git

- Credenciales, `.env`, `wp-config.php` o tokens.
- WordPress core y plugins de terceros.
- Uploads y archivos de caché.
- Backups SQL completos, porque pueden contener usuarios, formularios y datos
  personales.
- Certificados TLS y claves privadas.

`production-snapshot/pages.json`, `posts.json` y `snippets.json` sirven para
auditoría, comparación y reconstrucción asistida. No sustituyen el backup de
MariaDB. La recuperación completa requiere combinar Git, los uploads y el
backup de base de datos almacenado fuera del repositorio.
