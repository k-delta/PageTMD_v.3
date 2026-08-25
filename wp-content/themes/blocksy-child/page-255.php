<?php
/**
 * Ajustes de presentación específicos para /energia/cargadores/.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-charger-review-layout-fix">
        html body.page-id-255 .tmd-energy-split {
            grid-template-columns: minmax(220px, .78fr) minmax(0, 1.65fr) !important;
            gap: clamp(24px, 4vw, 54px) !important;
            align-items: center !important;
            padding: 52px 0 56px !important;
        }

        html body.page-id-255 .tmd-energy-split > * {
            min-width: 0;
        }

        html body.page-id-255 .tmd-energy-split > div:first-child {
            max-width: 320px !important;
        }

        html body.page-id-255 .tmd-energy-split h2 {
            max-width: 310px !important;
            margin: 0 0 16px !important;
            font-size: clamp(26px, 2.4vw, 34px) !important;
            line-height: 1.08 !important;
            letter-spacing: -.03em !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            hyphens: none !important;
        }

        html body.page-id-255 .tmd-energy-split > div:first-child > p {
            max-width: 310px !important;
            font-size: 14px !important;
            line-height: 1.55 !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            hyphens: none !important;
        }

        html body.page-id-255 .tmd-energy-split > div:first-child::after {
            width: 58px !important;
            height: 3px !important;
            margin-top: 22px !important;
        }

        html body.page-id-255 .tmd-energy-checklist {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px 14px !important;
            width: 100%;
        }

        html body.page-id-255 .tmd-energy-checklist li {
            min-width: 0;
            min-height: 74px !important;
            padding: 13px 16px 13px 62px !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            line-height: 1.35 !important;
            word-break: normal !important;
            overflow-wrap: break-word !important;
            hyphens: none !important;
        }

        html body.page-id-255 .tmd-energy-checklist li::before {
            left: 14px !important;
            width: 34px !important;
            height: 38px !important;
            border-radius: 10px !important;
            font-size: 12px !important;
            background: radial-gradient(circle at center, #ffb52b 0 9px, rgba(255, 195, 60, .22) 10px 100%) !important;
        }

        html body.page-id-255 .tmd-energy-checklist li:nth-child(5) {
            grid-column: 1 / -1 !important;
        }

        @media (max-width: 760px) {
            html body.page-id-255 .tmd-energy-split {
                grid-template-columns: 1fr !important;
                gap: 28px !important;
                padding: 40px 0 44px !important;
            }

            html body.page-id-255 .tmd-energy-split > div:first-child,
            html body.page-id-255 .tmd-energy-split h2,
            html body.page-id-255 .tmd-energy-split > div:first-child > p {
                max-width: 620px !important;
            }
        }

        @media (max-width: 560px) {
            html body.page-id-255 .tmd-energy-split h2 {
                font-size: 28px !important;
            }

            html body.page-id-255 .tmd-energy-checklist {
                grid-template-columns: 1fr !important;
            }

            html body.page-id-255 .tmd-energy-checklist li,
            html body.page-id-255 .tmd-energy-checklist li:nth-child(5) {
                grid-column: auto !important;
                min-height: 70px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
