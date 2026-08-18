<?php
/**
 * Valida en dry-run la transformación que el tema aplica al renderizar la página 273.
 *
 * wp eval-file scripts/update-job-application-form.php -- dry-run
 * wp eval-file scripts/update-job-application-form.php -- execute
 */

require_once __DIR__ . '/lib/tmd-job-application-content.php';

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];
if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/update-job-application-form.php -- [dry-run|execute]');
}

$page = get_post(273);
if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error('No existe la página Trabaja con nosotros esperada con ID 273.');
}

$result = tmd_transform_job_application_form((string) $page->post_content);
if (! empty($result['errors'])) {
    WP_CLI::error("La transformación se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('El formulario ya cumple el contrato; no hay cambios.');
    return;
}

WP_CLI::line('Cambios validados: ' . implode(', ', $result['changes']));
if (['execute'] !== $command_args) {
    WP_CLI::success('Dry-run correcto. No se escribió contenido.');
    return;
}

$updated_id = wp_update_post([
    'ID'           => 273,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || 273 !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la página Trabaja con nosotros: ' . $message);
}

clean_post_cache(273);
WP_CLI::success('Formulario de postulación actualizado en el contenido canónico.');
