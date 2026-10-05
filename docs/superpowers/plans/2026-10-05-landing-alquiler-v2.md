# Plan: Maqueta v2 de alquiler y venta de montacargas eléctricos

> SPEC: `docs/specs/2026-10-05-landing-alquiler-v2.md`. La solicitud del 2026-10-05 convierte la maqueta adjunta en la fuente vigente para la landing de alquiler; este plan reemplaza solo las tareas de alquiler incompatibles del plan del 2026-10-04.

## Restricciones

- Trabajar en `main` preservando los dos cambios de documentación preexistentes.
- Página 1558 y formulario CF7 1556 únicamente. No tocar ID 1559 ni CF7 ID 14.
- Datos dinámicos desde Inventario/Firebase y entradas publicadas. Sin disponibilidad ficticia.
- Fotos del catálogo sin texto incrustado; conservar únicamente los activos optimizados que utiliza la página.
- Mínimo de 15 días para esta landing. Conservar servicio técnico desde 2000, ya publicado; excluir 120 equipos y Yale hasta contar con respaldo vigente.
- Despliegue explícitamente solicitado: completar manifest, revisión, sincronización, backups y verificación productiva antes del primer write.

## Tareas

1. [x] Leer AGENTS, SPEC previo, plan, mapas, reglas de negocio y runbooks. Registrar estado Git y conservar cambios locales.
2. [x] Extraer alcance y doce bloques de la maqueta. Revisar fotos limpias y recursos de equipos del catálogo.
3. [x] Ejecutar `sync-production.sh --check` y clasificar la diferencia de código como archivos `.DS_Store` locales; snapshots quedan fuera del manifiesto de código y reflejan contenido productivo administrado.
4. [ ] Implementar contenido v2 en el generador WP-CLI y modo de actualización con hashes, backup, rollback, transacción/lock y purga de ID 1558. Ajustar solo CF7 1556 a cinco campos agrupados.
5. [ ] Añadir atributos optativos a shortcodes de inventario/blog y marcador v2 en body class para ocultar header/footer solo en la landing actualizada.
6. [ ] Optimizar y versionar las fotografías y cuatro imágenes ilustrativas de tipos de equipo seleccionadas del catálogo.
7. [ ] Construir estilos v2 aislados: hero, bloques claros/oscuros, cuatro tarjetas, inventario, proceso, sectores, usos, respaldo y banda cotización/FAQ responsiva.
8. [ ] Actualizar regla de alquiler a mínimo de 15 días y documentar la precedencia de este SPEC sobre los acuerdos anteriores de la landing.
9. [ ] Ejecutar validaciones focales estáticas, capturar manifiesto exacto y revisar diff. Completar revisión general y especialistas que resulten aplicables.
10. [ ] Antes de producción, verificar deriva, tomar backup completo y snapshot de página/formulario/metadatos, calcular hashes y ejecutar dry-run del actualizador. La cifra de 120 equipos y Yale no aparecen en el contenido.
11. [ ] Desplegar archivos del manifiesto vía flujo aprobado de `main`; escribir el contenido/formulario/meta autorizados con el actualizador y purgar la página 1558.
12. [ ] Verificar código/HTTP/HTML/formulario, hashes del código y sync final. Capturar escritorio/móvil si se habilita un navegador; documentar limitaciones.

## Evidencia ya obtenida

- La página pública 1558 responde HTTP 200; antes del cambio dice mínimo de 1 mes y ya muestra servicio técnico desde 2000 y alquiler de montacargas desde 2013.
- El endpoint canónico de inventario respondió `ok=true`, con 109 registros totales y 23 montacargas activos. Ese endpoint no acredita que la flota total propia sea de 120 equipos.
- `sync-production.sh --check` detectó únicamente `.DS_Store` locales bajo el child theme y diferencias en `production-snapshot`; no detectó cambios de código fuente productivo.
- Los snapshots son de auditoría. No se ejecutó `--pull`.
