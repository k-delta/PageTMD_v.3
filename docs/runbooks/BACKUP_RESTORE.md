# Backup y restauración

## Propósito

Define cuándo crear backups, cómo validarlos y cómo realizar una restauración controlada.

Un backup no es válido únicamente porque el comando terminó sin errores.

## Cuándo crear backup

Antes de:

- Cambios en contenido persistido.
- Escrituras masivas o eliminaciones.
- Migraciones o modificaciones de tablas.
- Cambios de configuración productiva.
- Actualizaciones autorizadas.
- Modificaciones críticas de plugins o tema.
- Cambios en multimedia o uploads.
- Operaciones con riesgo de pérdida de información.

## Tipos

### Base de datos

Registrar:

- Fecha y hora.
- Entorno.
- Base de datos.
- Método.
- Archivo generado.
- Tamaño.
- Hash cuando corresponda.

No versionar archivos SQL ni ubicarlos dentro del directorio público.

### Componente

Crear copia del archivo, plugin, tema o configuración afectada, preservando estructura y permisos relevantes.

### Uploads

Respaldar los archivos afectados cuando se reemplace, elimine o migre multimedia.

### Configuración

Respaldar antes de cambiar Docker Compose, OpenLiteSpeed, redirecciones, integraciones o variables de entorno.

Los backups con secretos no deben almacenarse en Git.

## `production-snapshot`

Es una herramienta de auditoría. No sustituye:

- Backup completo de MariaDB.
- Backup de uploads.
- Configuración externa.
- Volúmenes Docker.
- Backup del sistema.

## Ubicación

Los backups deben almacenarse fuera del directorio público de WordPress, con permisos restringidos y espacio suficiente.

Nomenclatura sugerida:

```text
YYYYMMDD-HHMM-entorno-componente.ext
```

Para el bootstrap del modelo Git -> producción se usa una carpeta restringida bajo:

```text
/opt/tecnimontacargas/backups/pre-git-deploy-YYYYMMDD-HHMMSS
```

## Procedimiento validado antes del bootstrap de despliegue

Este procedimiento cubre el cambio de Compose y la activación de bind mounts de los cinco componentes propios. Incluye base de datos, código propio y configuración productiva implicada.

No sustituye un backup de `uploads` cuando la operación pueda modificar multimedia, ni un backup completo de los volúmenes Docker cuando el alcance lo requiera.

### 1. Crear la carpeta restringida

```bash
STAMP="$(date -u +%Y%m%d-%H%M%S)"
BACKUP="/opt/tecnimontacargas/backups/pre-git-deploy-$STAMP"

mkdir -p \
  "$BACKUP/wp-content/themes" \
  "$BACKUP/wp-content/plugins"

chmod 700 "$BACKUP"
```

### 2. Preservar configuración

```bash
cp -p \
  /opt/tecnimontacargas/docker-compose.prod.yml \
  "$BACKUP/docker-compose.prod.yml"

cp -p \
  /opt/tecnimontacargas/.env.prod \
  "$BACKUP/.env.prod"
```

`.env.prod` contiene secretos. Esta copia debe permanecer únicamente en el servidor, con permisos restringidos, y nunca debe copiarse al repositorio, tickets, logs o conversaciones.

### 3. Preservar los cinco componentes propios

```bash
docker cp \
  tmd_ols_wordpress:/var/www/vhosts/localhost/html/wp-content/themes/blocksy-child \
  "$BACKUP/wp-content/themes/"

for plugin in \
  tm-chatbot-fase1 \
  tm-equipos-destacados-v2 \
  tm-popup-bienvenida \
  tm-quiz-equipo-ideal
do
  docker cp \
    "tmd_ols_wordpress:/var/www/vhosts/localhost/html/wp-content/plugins/$plugin" \
    "$BACKUP/wp-content/plugins/"
done
```

### 4. Crear dump consistente de MariaDB

El dump se ejecuta dentro de `tmd_db` para reutilizar el cliente y las variables de entorno ya presentes en el contenedor. La contraseña se expande dentro del contenedor y no debe imprimirse.

```bash
docker exec tmd_db sh -c '
  exec mariadb-dump \
    --single-transaction \
    --quick \
    --routines \
    --events \
    --triggers \
    --hex-blob \
    -uroot \
    -p"$MYSQL_ROOT_PASSWORD" \
    --databases "$MYSQL_DATABASE"
' > "$BACKUP/database.sql"
```

Restringir el árbol completo:

```bash
chmod -R go-rwx "$BACKUP"
```

## Verificación

Comprobar siempre:

- existencia;
- tamaño razonable;
- archivo no vacío;
- permisos;
- lectura e integridad;
- formato correcto;
- hash cuando aplique;
- ruta de restauración identificada.

No continuar con una operación destructiva si el backup no fue validado.

Para el procedimiento de bootstrap anterior:

```bash
echo "=== BACKUP PATH ==="
printf '%s\n' "$BACKUP"

echo "=== DATABASE ==="
ls -lh "$BACKUP/database.sql"
test -s "$BACKUP/database.sql" \
  && echo "database=NON_EMPTY" \
  || echo "database=ERROR"

echo "=== SQL HEADER ==="
head -n 5 "$BACKUP/database.sql"

echo "=== FILES ==="
du -sh "$BACKUP"
find "$BACKUP/wp-content" -type f | wc -l

echo "=== HASHES ==="
sha256sum \
  "$BACKUP/database.sql" \
  "$BACKUP/docker-compose.prod.yml" \
  "$BACKUP/.env.prod"
```

La cabecera debe corresponder a un dump MariaDB y `database.sql` no debe estar vacío. No compartir contenido SQL, contraseñas ni el contenido de `.env.prod` durante la validación.

## Restauración

Antes de restaurar:

1. Confirmar el componente.
2. Identificar y verificar el backup correcto.
3. Preservar el estado fallido para análisis.
4. Detener escrituras cuando sea necesario.
5. Evaluar pérdida de información posterior al backup.
6. Informar el alcance.

### Componente

1. Crear copia del estado actual.
2. Restaurar únicamente archivos afectados.
3. Preservar permisos.
4. Validar sintaxis.
5. Purgar caché.
6. Revisar logs, HTTP y flujo afectado.
7. Ejecutar control de sincronización.

### Rollback del bootstrap de bind mounts

Si falla únicamente la adopción del Compose nuevo o los bind mounts y MariaDB no fue modificada, no restaurar la base de datos. Restaurar el Compose anterior y recrear solo WordPress:

```bash
BACKUP=/opt/tecnimontacargas/backups/<backup-validado>

cp \
  "$BACKUP/docker-compose.prod.yml" \
  /opt/tecnimontacargas/docker-compose.prod.yml

docker compose \
  -p pagetmd_v3 \
  --env-file /opt/tecnimontacargas/.env.prod \
  -f /opt/tecnimontacargas/docker-compose.prod.yml \
  up -d --no-deps --force-recreate wordpress
```

Usar `-p pagetmd_v3` durante este rollback porque el Compose anterior puede no contener el `name:` del proyecto.

### Base de datos

Antes:

1. Confirmar autorización.
2. Identificar tablas afectadas.
3. Crear backup del estado actual.
4. Evaluar restauración parcial.
5. Detener escrituras cuando corresponda.
6. Confirmar compatibilidad entre esquema y código.

Después:

1. Verificar integridad.
2. Confirmar acceso de WordPress.
3. Revisar errores y caché.
4. Probar autenticación, formularios y contenido cuando aplique.
5. Registrar el resultado.

No restaurar `database.sql` únicamente porque existe. El dump es una red de seguridad; su restauración requiere confirmar que datos persistentes fueron afectados y evaluar la pérdida de información posterior al backup.

### Uploads

1. Identificar archivos afectados.
2. Preservar el estado actual.
3. Restaurar únicamente lo necesario.
4. Confirmar permisos, URLs, tamaños y formatos.
5. Regenerar metadatos solo cuando sea necesario.

## Registro

Cada backup o restauración material debe registrar:

- Fecha y hora.
- Motivo.
- Entorno.
- Componente.
- Archivo utilizado.
- Validación realizada.
- Resultado.
- Riesgos.
- Responsable.

No registrar secretos ni datos personales.
