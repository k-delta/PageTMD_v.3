<?php
/**
 * Plugin Name: TMD Maintenance Menu Framing
 * Description: Ajusta el encuadre de la imagen de Mantenimiento en el megamenu de escritorio.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', static function (): void {
    if (! wp_style_is('tmd-mega-menu', 'enqueued')) {
        return;
    }

    $css = <<<'CSS'
@media (min-width: 1025px) {
    #tmd-mm-panel-mant .tmd-mm-img--maintenance {
        background-size: 84% auto !important;
        background-position: center center !important;
        background-repeat: no-repeat !important;
        background-color: #f4f7fb !important;
    }
}
CSS;

    wp_add_inline_style('tmd-mega-menu', $css);
}, 100);
