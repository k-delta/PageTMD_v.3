<?php
/**
 * Capa visual compartida para páginas auditadas por la guía global de marca.
 */

defined('ABSPATH') || exit;

if (! function_exists('tmd_enqueue_brand_consistency_asset')) {
    function tmd_enqueue_brand_consistency_asset(): void
    {
        $relative_path = '/assets/css/tmd-brand-consistency-global.css';
        $absolute_path = get_stylesheet_directory() . $relative_path;
        $version = file_exists($absolute_path) ? (string) filemtime($absolute_path) : null;

        wp_enqueue_style(
            'tmd-brand-consistency-global',
            get_stylesheet_directory_uri() . $relative_path,
            array(),
            $version
        );
    }
}

if (! function_exists('tmd_use_brand_consistency')) {
    function tmd_use_brand_consistency(): void
    {
        add_action('wp_enqueue_scripts', 'tmd_enqueue_brand_consistency_asset', 100);
    }
}
