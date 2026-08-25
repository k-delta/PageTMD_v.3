<?php
/**
 * Ajustes de presentación específicos para /nosotros/contacto/.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-contact-form-title-size">
        body.page-id-57 .tmd-form-card h2,
        body.page-id-57 .wpcf7 form.tmd-form-card h2,
        body.page-id-57 form.wpcf7-form.tmd-form-card h2 {
            font-size: clamp(34px, 3.2vw, 48px) !important;
            line-height: 1.02 !important;
            letter-spacing: -.045em !important;
        }

        @media (max-width: 767px) {
            body.page-id-57 .tmd-form-card h2,
            body.page-id-57 .wpcf7 form.tmd-form-card h2,
            body.page-id-57 form.wpcf7-form.tmd-form-card h2 {
                font-size: 34px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
