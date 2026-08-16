<?php
/**
 * Añade Barbillon al carrusel de marcas de Inicio de forma idempotente.
 *
 * Validación sin escritura:
 * wp eval-file scripts/add-home-brand-barbillon.php -- dry-run
 *
 * Ejecución autorizada, después de backup:
 * wp eval-file scripts/add-home-brand-barbillon.php -- execute
 */

if (! function_exists('tmd_transform_home_brand_barbillon')) {
    function tmd_transform_home_brand_barbillon($content) {
        $original = (string) $content;
        $existing = '<figure class="tmd-brand-carousel__slide"><img src="https://tecnimontacargas.com/wp-content/uploads/2026/07/yale-e1784359370520.png" alt="Yale" loading="lazy"></figure>';
        $brand    = '<figure class="tmd-brand-carousel__slide"><img src="https://tecnimontacargas.com/wp-content/themes/blocksy-child/assets/images/brands/barbillon-aliado.webp" alt="Barbillon" loading="lazy"></figure>';

        $existing_count = substr_count($original, $existing);
        $brand_count    = substr_count($original, $brand);

        if (1 === $brand_count && 1 === $existing_count) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => [],
            ];
        }

        if (1 !== $existing_count || 0 !== $brand_count) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => [
                    sprintf(
                        'El carrusel no cumple la precondición esperada (Yale=%d, Barbillon=%d).',
                        $existing_count,
                        $brand_count
                    ),
                ],
            ];
        }

        return [
            'content' => str_replace($existing, $existing . "\n      " . $brand, $original),
            'changes' => ['marca:Barbillon'],
            'errors'  => [],
        ];
    }
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];
$command_args = array_values(array_filter($command_args, static function ($argument) {
    return '--' !== $argument;
}));

if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/add-home-brand-barbillon.php -- [dry-run|execute]');
}

$home_id = 47;
$home    = get_post($home_id);

if (! $home || 'page' !== $home->post_type) {
    WP_CLI::error("No existe la página de inicio esperada con ID {$home_id}.");
}

$result = tmd_transform_home_brand_barbillon((string) $home->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('El carrusel ya contiene Barbillon; no hay cambios.');
    return;
}

WP_CLI::line('Cambios validados: ' . implode(', ', $result['changes']));
if (['execute'] !== $command_args) {
    WP_CLI::success('Dry-run correcto. No se escribió contenido.');
    return;
}

$updated_id = wp_update_post([
    'ID'           => $home_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $home_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la página de inicio: ' . $message);
}

clean_post_cache($home_id);
WP_CLI::success('Página de inicio actualizada con Barbillon.');
