<?php
/**
 * Fuerza la imagen de la card "Compañía" del mega menú a usar el asset
 * versionado del repositorio.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    $relative_path = 'assets/images/mega-menu/menu-company.webp';
    $absolute_path = get_stylesheet_directory() . '/' . $relative_path;
    $asset_url = get_stylesheet_directory_uri() . '/' . $relative_path;

    if (is_file($absolute_path)) {
        $asset_url = add_query_arg('ver', (string) filemtime($absolute_path), $asset_url);
    }
    ?>
    <style id="tmd-menu-company-image-fix">
        .tmd-mm-img--compania {
            background-image: url('<?php echo esc_url($asset_url); ?>') !important;
        }
    </style>
    <?php
}, 120);
