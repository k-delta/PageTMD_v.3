# Despliegue

## Propósito

Define el procedimiento seguro para desplegar cambios en producción.

Principio: desplegar únicamente el cambio necesario, después de validarlo y crear los respaldos correspondientes.

## No desplegar

- WordPress core.
- Tema padre.
- Plugins de terceros.
- Todo el child theme por un cambio puntual.
- Uploads no relacionados.
- Archivos temporales, cachés o backups.
- `.env`, `wp-config.php`, certificados o secretos.
- `.codex-tmp/`.
- Código histórico.
- Cambios no relacionados con la tarea.

## Preparación

1. Confirmar alcance y fuente canónica.
2. Ejecutar:

```bash
git status --short
```

3. Preservar cambios locales ajenos.
4. Revisar el diff relevante.
5. Ejecutar validaciones focalizadas.
6. Confirmar que no se incluyan secretos.
7. Comprobar sincronización:

```bash
./scripts/sync-production.sh --check
```

## Deriva

- Código `0`: coincidencia en rutas comprobadas.
- Código `1`: existe deriva.

Si existe deriva:

1. Detener el despliegue.
2. Identificar rutas diferentes.
3. Determinar el origen del cambio productivo.
4. No sobrescribirlo automáticamente.
5. Usar `--pull` solo para incorporar una modificación de emergencia autorizada.

```bash
./scripts/sync-production.sh --pull
```

No ejecutar `--pull` si sobrescribe cambios locales válidos.

## Backups

Antes de una escritura material:

- Crear backup de base de datos si puede cambiar contenido persistente.
- Crear backup del componente afectado.
- Respaldar uploads si se modifica multimedia.
- Registrar y verificar las rutas.

Consultar `BACKUP_RESTORE.md`.

## Validaciones previas

### PHP

```bash
php -l ruta/al/archivo.php
```

### JavaScript y CSS

- Ejecutar linter o validación disponible.
- Revisar consola.
- Confirmar carga de assets.
- Validar responsive cuando aplique.

### Cambios visuales

- Escritorio y móvil.
- Chrome y Safari cuando aplique.
- Navegación por teclado.
- Ausencia de overflow horizontal.

### SEO

- Title.
- Description.
- Canonical.
- Robots.
- Open Graph.
- JSON-LD.
- Sitemap.

## Ejecución

- Copiar únicamente archivos modificados.
- Preservar permisos y propietarios.
- No reemplazar el child theme completo.
- No eliminar archivos sin comprobar referencias.
- No modificar secretos.
- No ejecutar cambios destructivos sin plan de reversión.

Registrar archivos, fecha, componente, backup y mecanismo utilizado.

## Validación posterior

1. Validar sintaxis en producción.
2. Revisar logs.
3. Purgar únicamente las cachés necesarias.
4. Verificar HTTP y navegador.
5. Revisar consola.
6. Probar el flujo modificado.
7. Comprobar flujos críticos relacionados.
8. Ejecutar nuevamente:

```bash
./scripts/sync-production.sh --check
```

No declarar éxito únicamente porque los archivos fueron copiados.

## Validaciones específicas

### Redirecciones

- Código HTTP.
- Destino exacto.
- Parámetros cuando corresponda.
- Ausencia de ciclos.

### Inventario

- Respuesta de la fuente real.
- Estructura y estado válidos.
- Renderizado, filtros y paginación.
- Ausencia de elementos inválidos.

### Correo

- Envío desde el flujo real.
- Recepción en una bandeja válida.
- Logs del proveedor.
- Errores de autorización.

### Base de datos

- Contenido final.
- Renderizado público.
- Snapshot de auditoría cuando corresponda.
- Hashes o diferencias.

## Rollback

Realizar rollback cuando:

- Aparezcan errores 5xx.
- Se rompa una función crítica.
- Exista riesgo de pérdida de datos.
- Se afecte autenticación o cuenta.
- Se produzca una regresión visual grave.
- El sitio quede inaccesible.
- Se expongan datos sensibles.
- No sea posible corregir de forma inmediata y segura.

### Procedimiento

1. Preservar logs y evidencia.
2. Identificar el componente afectado.
3. Restaurar únicamente ese componente.
4. Restaurar la base de datos solo si se alteraron datos persistentes.
5. Validar sintaxis, caché, HTTP y flujo afectado.
6. Ejecutar control de sincronización.
7. Documentar causa y resultado.

## Cierre

Informar:

- Qué se desplegó.
- Archivos modificados.
- Backups creados.
- Pruebas ejecutadas.
- Validaciones productivas.
- Resultado de sincronización.
- Riesgos pendientes.
- Si se realizó rollback.
