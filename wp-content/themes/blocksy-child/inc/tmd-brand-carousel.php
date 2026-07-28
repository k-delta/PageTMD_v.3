<?php
/** Assets for manufacturer logo carousel on Home. */

if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    if (! is_front_page()) {
        return;
    }

    $css = get_stylesheet_directory() . '/assets/css/tmd-brand-carousel.css';
    $js = get_stylesheet_directory() . '/assets/js/tmd-brand-carousel.js';

    wp_enqueue_style(
        'tmd-brand-carousel',
        get_stylesheet_directory_uri() . '/assets/css/tmd-brand-carousel.css',
        [],
        file_exists($css) ? filemtime($css) : '1.0.0'
    );
    wp_enqueue_script(
        'tmd-brand-carousel',
        get_stylesheet_directory_uri() . '/assets/js/tmd-brand-carousel.js',
        [],
        file_exists($js) ? filemtime($js) : '1.0.0',
        true
    );
}, 85);
