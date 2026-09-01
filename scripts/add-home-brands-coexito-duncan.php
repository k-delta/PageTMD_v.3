<?php
/**
 * Añade Coéxito y Duncan al carrusel de marcas de Inicio de forma idempotente.
 *
 * Validación sin escritura:
 * wp eval-file scripts/add-home-brands-coexito-duncan.php -- dry-run
 *
 * Ejecución autorizada, después de backup:
 * wp eval-file scripts/add-home-brands-coexito-duncan.php -- execute
 */

if (! function_exists('tmd_transform_home_brands_coexito_duncan')) {
    function tmd_transform_home_brands_coexito_duncan($content) {
        $original = (string) $content;
        $marker   = '<section class="tmd-brand-carousel" data-tmd-brand-carousel=""';
        $coexito  = '<figure class="tmd-brand-carousel__slide"><img src="https://tecnimontacargas.com/wp-content/uploads/2026/09/coexito.webp" alt="Coéxito" loading="lazy"></figure>';
        $duncan   = '<figure class="tmd-brand-carousel__slide"><img src="https://tecnimontacargas.com/wp-content/uploads/2026/09/duncan.webp" alt="Duncan" loading="lazy"></figure>';
        $end      = "\n    </div>\n  </div>\n  <button class=\"tmd-brand-carousel__arrow tmd-brand-carousel__arrow--next\"";

        $marker_count = substr_count($original, $marker);
        $coexito_count = substr_count($original, $coexito);
        $duncan_count = substr_count($original, $duncan);

        if (1 !== $marker_count) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => [sprintf('Se esperaba un único carrusel de marcas; encontrados: %d.', $marker_count)],
            ];
        }

        if (1 === $coexito_count && 1 === $duncan_count) {
            return ['content' => $original, 'changes' => [], 'errors' => []];
        }

        if (0 !== $coexito_count || 0 !== $duncan_count) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => [sprintf('Precondición inválida (Coéxito=%d, Duncan=%d).', $coexito_count, $duncan_count)],
            ];
        }

        $carousel_end = strpos($original, $end);
        if (false === $carousel_end) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => ['No se encontró el cierre esperado del carrusel de marcas.'],
            ];
        }

        $updated = substr_replace($original, "      {$coexito}\n      {$duncan}\n", $carousel_end, 0);

        return [
            'content' => $updated,
            'changes' => ['marca:Coéxito', 'marca:Duncan'],
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
    WP_CLI::error('Uso: wp eval-file scripts/add-home-brands-coexito-duncan.php -- [dry-run|execute]');
}

$home_id = 47;
$home    = get_post($home_id);

if (! $home || 'page' !== $home->post_type) {
    WP_CLI::error("No existe la página de inicio esperada con ID {$home_id}.");
}

$result = tmd_transform_home_brands_coexito_duncan((string) $home->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('El carrusel ya contiene Coéxito y Duncan; no hay cambios.');
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
WP_CLI::success('Página de inicio actualizada con Coéxito y Duncan.');
