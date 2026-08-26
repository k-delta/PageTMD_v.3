<?php
/**
 * Plugin Name: TMD Mobile Menu Assets
 * Description: Carga de forma determinista los estilos del menu movil de Tecnimontacargas.
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', static function (): void {
    $css_path = get_stylesheet_directory() . '/assets/css/tmd-mobile-menu.css';

    if (! is_file($css_path)) {
        return;
    }

    wp_enqueue_style(
        'tmd-mobile-menu',
        get_stylesheet_directory_uri() . '/assets/css/tmd-mobile-menu.css',
        ['tmd-mega-menu'],
        (string) filemtime($css_path),
        '(max-width: 1024px)'
    );
}, 41);
