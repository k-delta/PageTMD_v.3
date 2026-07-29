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

## Verificación

Comprobar:

- Existencia.
- Tamaño razonable.
- Archivo no vacío.
- Permisos.
- Lectura e integridad.
- Formato correcto.
- Hash cuando aplique.
- Ruta de restauración identificada.

No continuar con una operación destructiva si el backup no fue validado.

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
