<?php
/**
 * Ajusta el encuadre de la imagen de Mantenimiento en el megamenu de escritorio
 * mediante el CSS personalizado del tema activo.
 */

defined('ABSPATH') || exit;

function tmd_maintenance_menu_image_css_block(): string
{
    return <<<'CSS'
/* TMD_MAINTENANCE_MENU_IMAGE_START */
@media (min-width: 1025px) {
    #tmd-mm-panel-mant .tmd-mm-img--maintenance {
        background-size: 84% auto !important;
        background-position: center center !important;
        background-repeat: no-repeat !important;
        background-color: #f4f7fb !important;
    }
}
/* TMD_MAINTENANCE_MENU_IMAGE_END */
CSS;
}

function tmd_transform_maintenance_menu_custom_css(string $css): array
{
    $start = '/* TMD_MAINTENANCE_MENU_IMAGE_START */';
    $end = '/* TMD_MAINTENANCE_MENU_IMAGE_END */';
    $block = tmd_maintenance_menu_image_css_block();

    $has_start = str_contains($css, $start);
    $has_end = str_contains($css, $end);

    if ($has_start !== $has_end) {
        return [
            'changed' => false,
            'content' => $css,
            'errors' => ['Los marcadores del ajuste de Mantenimiento estan incompletos.'],
        ];
    }

    if (! $has_start) {
        $content = rtrim($css);
        $content .= ($content === '' ? '' : "\n\n") . $block . "\n";

        return [
            'changed' => true,
            'content' => $content,
            'errors' => [],
        ];
    }

    $pattern = '/\/\* TMD_MAINTENANCE_MENU_IMAGE_START \*\/.*?\/\* TMD_MAINTENANCE_MENU_IMAGE_END \*\//s';
    $content = preg_replace($pattern, $block, $css, 1, $count);

    if ($content === null || $count !== 1) {
        return [
            'changed' => false,
            'content' => $css,
            'errors' => ['No fue posible actualizar de forma univoca el bloque existente.'],
        ];
    }

    return [
        'changed' => $content !== $css,
        'content' => $content,
        'errors' => [],
    ];
}

if (defined('WP_CLI') && WP_CLI) {
    if (! function_exists('wp_get_custom_css') || ! function_exists('wp_update_custom_css_post')) {
        WP_CLI::error('WordPress no expone las funciones de CSS personalizado requeridas.');
    }

    $stylesheet = get_stylesheet();
    $current_css = (string) wp_get_custom_css($stylesheet);
    $result = tmd_transform_maintenance_menu_custom_css($current_css);

    if (! empty($result['errors'])) {
        WP_CLI::error(implode(' ', $result['errors']));
    }

    if (! $result['changed']) {
        WP_CLI::success('El encuadre de Mantenimiento ya estaba actualizado.');
        return;
    }

    $updated = wp_update_custom_css_post(
        $result['content'],
        ['stylesheet' => $stylesheet]
    );

    if (is_wp_error($updated)) {
        WP_CLI::error($updated->get_error_message());
    }

    WP_CLI::success('Encuadre de imagen de Mantenimiento actualizado al 84%.');
}
