<?php
/**
 * Conserva únicamente el catálogo administrado de la página Energía (ID 63).
 *
 * wp eval-file scripts/update-energy-catalog-content.php dry-run
 * wp eval-file scripts/update-energy-catalog-content.php execute
 */

require_once __DIR__ . '/lib/tmd-energy-catalog-content.php';

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];
if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/update-energy-catalog-content.php [dry-run|execute]');
}

$page = get_post(63);
if (! $page || 'page' !== $page->post_type || 'energia' !== $page->post_name) {
    WP_CLI::error('No existe la página Energía esperada con ID 63 y slug energia.');
}

$result = tmd_transform_energy_catalog_content((string) $page->post_content);
if (! empty($result['errors'])) {
    WP_CLI::error("La transformación se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('La página ya contiene únicamente el catálogo; no hay cambios.');
    return;
}

WP_CLI::line('Cambios validados: ' . implode(', ', $result['changes']));
if (['execute'] !== $command_args) {
    WP_CLI::success('Dry-run correcto. No se escribió contenido.');
    return;
}

$updated = wp_update_post([
    'ID'           => 63,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated)) {
    WP_CLI::error('No se pudo actualizar la página Energía: ' . $updated->get_error_message());
}

clean_post_cache(63);
WP_CLI::success('Página Energía actualizada para mostrar únicamente el catálogo.');
