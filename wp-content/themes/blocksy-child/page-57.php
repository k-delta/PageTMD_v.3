<?php
/**
 * Ajustes de presentación específicos para /nosotros/contacto/.
 */

defined('ABSPATH') || exit;

require_once get_stylesheet_directory() . '/inc/tmd-brand-consistency.php';
tmd_use_brand_consistency();

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-contact-form-title-size">
        body.page-id-57 .tmd-form-card h2,
        body.page-id-57 .wpcf7 form.tmd-form-card h2,
        body.page-id-57 form.wpcf7-form.tmd-form-card h2 {
            font-size: clamp(34px, 3vw, 40px) !important;
            line-height: 1.08 !important;
            letter-spacing: -.035em !important;
        }

        body.page-id-57 .tmd-form-card .tmd-field {
            row-gap: 6px !important;
            gap: 6px !important;
        }

        body.page-id-57 .tmd-form-card .tmd-field > label,
        body.page-id-57 .wpcf7 form.tmd-form-card .tmd-field > label,
        body.page-id-57 form.wpcf7-form.tmd-form-card .tmd-field > label {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.25 !important;
        }

        body.page-id-57 .tmd-form-card .tmd-field > input,
        body.page-id-57 .tmd-form-card .tmd-field > select,
        body.page-id-57 .tmd-form-card .tmd-field > textarea {
            margin-top: 0 !important;
        }

        body.page-id-57 .wpcf7 form.tmd-form-card label .wpcf7-form-control-wrap,
        body.page-id-57 form.wpcf7-form.tmd-form-card label .wpcf7-form-control-wrap {
            display: block !important;
            margin-top: 6px !important;
        }

        @media (max-width: 767px) {
            body.page-id-57 .tmd-form-card h2,
            body.page-id-57 .wpcf7 form.tmd-form-card h2,
            body.page-id-57 form.wpcf7-form.tmd-form-card h2 {
                font-size: 30px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
