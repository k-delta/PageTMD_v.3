<?php
/**
 * Ajustes de presentación específicos para /energia/baterias/plomo/.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-lead-battery-section-layout-fix">
        html body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split {
            box-sizing: border-box !important;
            width: min(1180px, calc(100% - 32px)) !important;
            max-width: 1180px !important;
            margin: 0 auto !important;
            padding: clamp(42px, 5vw, 60px) clamp(28px, 4vw, 48px) !important;
            border-radius: 20px !important;
            background: #f5f6f8 !important;
            box-shadow: none !important;
            clip-path: none !important;
        }

        html body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split::after {
            content: none !important;
            display: none !important;
        }

        @media (max-width: 640px) {
            html body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split {
                width: calc(100% - 24px) !important;
                padding: 32px 20px 36px !important;
                border-radius: 18px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
