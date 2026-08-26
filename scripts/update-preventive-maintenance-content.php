<?php
/**
 * Actualiza de forma idempotente el contenido solicitado para
 * /mantenimiento/mantenimiento-preventivo/ (pagina 288).
 *
 * Alcance:
 * - hero: nuevo titulo y descripcion;
 * - elimina la navegacion repetida Preventivo/Correctivo;
 * - actualiza el bloque "Alcance tecnico" y sus seis tarjetas;
 * - actualiza la nota de alcance del mantenimiento preventivo.
 */

function tmd_preventive_replace_once(
    string $content,
    string $label,
    string $old,
    string $new,
    array &$changes,
    array &$errors
): string {
    $old_count = substr_count($content, $old);
    $new_count = substr_count($content, $new);

    if (1 === $old_count) {
        $updated = str_replace($old, $new, $content, $replacements);
        if (1 !== $replacements) {
            $errors[] = sprintf('%s: se esperaba un reemplazo y se obtuvieron %d.', $label, $replacements);
            return $content;
        }

        $changes[] = $label;
        return $updated;
    }

    if (0 === $old_count && $new_count >= 1) {
        return $content;
    }

    $errors[] = sprintf('%s: precondicion invalida (anterior=%d, nuevo=%d).', $label, $old_count, $new_count);
    return $content;
}

function tmd_transform_preventive_maintenance_content(string $content): array
{
    $original = $content;
    $changes = [];
    $errors = [];

    $replacements = [
        [
            'hero-titulo',
            '<h2 id="tmd-preventive-title">Anticípese al desgaste antes de que detenga la operación</h2>',
            '<h2 id="tmd-preventive-title">Prevenga fallas y reduzca paradas no programadas</h2>',
        ],
        [
            'hero-descripcion',
            '<p class="tmd-maint-hero__lead">El mantenimiento preventivo reúne inspecciones y actividades programadas para conocer la condición del equipo, corregir ajustes menores y detectar componentes que requieren seguimiento antes de que generen una falla.</p>',
            '<p class="tmd-maint-hero__lead">El mantenimiento preventivo permite evaluar periódicamente la condición del montacargas mediante inspecciones, ajustes y verificaciones técnicas, con el fin de detectar oportunamente desgastes o anomalías que puedan afectar su funcionamiento.</p>',
        ],
        [
            'nav-repetida',
            "  <nav class=\"tmd-maint-service-nav\" aria-label=\"Páginas de mantenimiento\">\n    <a href=\"/mantenimiento/\">Mantenimiento</a>\n    <a href=\"/mantenimiento/mantenimiento-preventivo/\" aria-current=\"page\">Preventivo</a>\n    <a href=\"/mantenimiento/mantenimiento-correctivo/\">Correctivo</a>\n  </nav>",
            '  <!-- tmd-preventive-service-nav-removed -->',
        ],
        [
            'alcance-titulo',
            '<h2>¿Qué puede incluir una revisión preventiva?</h2>',
            '<h2>¿Qué incluye el mantenimiento preventivo?</h2>',
        ],
        [
            'alcance-descripcion',
            '<p>La lista se adapta al tipo de montacargas, su manual, horas de trabajo y condiciones de operación. La inspección inicial determina las actividades aplicables.</p>',
            '<p>Las actividades se definen según el tipo de montacargas, las recomendaciones del fabricante, las horas de trabajo y las condiciones de operación.</p>',
        ],
        [
            'tarjeta-seguridad',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Seguridad</span><h3>Frenos, dirección y alarmas</h3><p>Verificación funcional de elementos de maniobra, señalización, parada y control disponibles en el equipo.</p></article>',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Seguridad</span><h3>Frenos, dirección y dispositivos de seguridad</h3><p>Verificación del funcionamiento de frenos, dirección, alarmas, señalización y demás elementos de seguridad del equipo.</p></article>',
        ],
        [
            'tarjeta-elevacion',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Elevación</span><h3>Mástil, cadenas y horquillas</h3><p>Inspección visual, puntos de ajuste, lubricación, desgaste aparente y comportamiento durante el movimiento.</p></article>',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Sistema de elevación</span><h3>Mástil, cadenas y horquillas</h3><p>Inspección de desgaste, ajuste, lubricación y funcionamiento de los componentes del sistema de elevación.</p></article>',
        ],
        [
            'tarjeta-hidraulico',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Hidráulica</span><h3>Mangueras, cilindros y conexiones</h3><p>Revisión de niveles, fugas visibles, uniones y respuesta del sistema bajo condiciones seguras de prueba.</p></article>',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Sistema hidráulico</span><h3>Mangueras, cilindros y conexiones</h3><p>Revisión de niveles, fugas, conexiones, mangueras, cilindros y funcionamiento general del sistema hidráulico.</p></article>',
        ],
        [
            'tarjeta-rodaje',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Rodamiento</span><h3>Ruedas y componentes móviles</h3><p>Estado visible, desgaste irregular, fijaciones y condiciones que puedan afectar estabilidad o desplazamiento.</p></article>',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Tren de rodaje</span><h3>Ruedas y componentes móviles</h3><p>Inspección del estado y desgaste de ruedas, fijaciones y componentes asociados al desplazamiento y estabilidad del equipo.</p></article>',
        ],
        [
            'tarjeta-electrico',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Electricidad</span><h3>Conexiones y controles</h3><p>Inspección de cableado accesible, conectores, mandos, contactores, sensores y registros de alarma disponibles.</p></article>',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Sistema eléctrico</span><h3>Conexiones, controles y componentes</h3><p>Revisión de cableado, conectores, mandos, contactores, sensores y códigos de falla disponibles.</p></article>',
        ],
        [
            'tarjeta-energia',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Energía</span><h3>Batería y proceso de carga</h3><p>Condición visible, conexiones y señales de uso o carga inadecuada, según la tecnología instalada.</p></article>',
            '<article class="tmd-maint-card"><span class="tmd-maint-card__label">Sistema de energía</span><h3>Batería y sistema de carga</h3><p>Inspección del estado de la batería, conexiones, conectores y condiciones generales del sistema de carga, según la tecnología instalada.</p></article>',
        ],
        [
            'nota-alcance',
            '<p class="tmd-maint-note"><strong>El mantenimiento preventivo no oculta fallas:</strong> si durante la revisión se identifica una reparación necesaria, debe reportarse y autorizarse como trabajo adicional.</p>',
            '<p class="tmd-maint-note"><strong>Importante:</strong> El mantenimiento preventivo permite identificar anomalías, pero no incluye reparaciones correctivas ni repuestos. Cualquier falla detectada durante la inspección será reportada para su diagnóstico, cotización y autorización.</p>',
        ],
    ];

    foreach ($replacements as [$label, $old, $new]) {
        $content = tmd_preventive_replace_once($content, $label, $old, $new, $changes, $errors);
    }

    return [
        'content' => $content,
        'changes' => $changes,
        'errors' => $errors,
        'changed' => $content !== $original,
    ];
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$page_id = 288;
$page = get_post($page_id);

if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página de Mantenimiento preventivo esperada con ID {$page_id}.");
}

$result = tmd_transform_preventive_maintenance_content((string) $page->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización de Mantenimiento preventivo se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (! $result['changed']) {
    WP_CLI::success('Mantenimiento preventivo ya contiene los cambios solicitados; no hay cambios.');
    return;
}

$updated_id = wp_update_post([
    'ID' => $page_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar Mantenimiento preventivo: ' . $message);
}

clean_post_cache($page_id);
WP_CLI::success('Mantenimiento preventivo actualizado: ' . implode(', ', $result['changes']));
