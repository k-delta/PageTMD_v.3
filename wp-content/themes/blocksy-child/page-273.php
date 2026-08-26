<?php
/**
 * Ajustes de presentación específicos para Trabaja con nosotros (ID 273).
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-jobs-testimonial-portrait-zoom">
        body.page-id-273 .tmd-jobs-avatar-wrap {
            width: 260px !important;
            height: 260px !important;
            border: 5px solid #ffc33c;
            border-radius: 999px;
            box-shadow: 0 26px 60px rgba(0, 0, 0, .32);
        }

        body.page-id-273 .tmd-jobs-avatar-wrap img {
            width: 100% !important;
            height: 100% !important;
            border: 0 !important;
            border-radius: 999px !important;
            box-shadow: none !important;
            object-fit: cover !important;
            object-position: 50% 56% !important;
            transform: scale(1.2);
            transform-origin: center center;
            clip-path: circle(41.667% at 50% 50%);
        }

        @media (max-width: 900px) {
            body.page-id-273 .tmd-jobs-avatar-wrap {
                width: 240px !important;
                height: 240px !important;
            }
        }

        @media (max-width: 640px) {
            body.page-id-273 .tmd-jobs-avatar-wrap {
                width: 220px !important;
                height: 220px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
