<?php
/**
 * Plugin Name: TMD Maintenance Menu Image
 * Description: Ajusta el encuadre de la imagen de Mantenimiento en el megamenu de escritorio.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-maintenance-menu-image-framing">
        @media (min-width: 1025px) {
            #tmd-mm-panel-mant .tmd-mm-img--maintenance {
                background-size: 90% auto !important;
                background-position: center center !important;
                background-color: #f4f7fb !important;
            }
        }
    </style>
    <?php
}, 90);
