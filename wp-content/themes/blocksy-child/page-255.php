<?php
/**
 * Ajustes de presentación específicos para /energia/cargadores/.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-charger-review-layout-fix">
        html body.page-id-255 .tmd-energy-split {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 52px 0 56px !important;
            background: transparent !important;
            box-shadow: none !important;
            clip-path: none !important;
        }

        html body.page-id-255 .tmd-energy-split::after {
            content: none !important;
            display: none !important;
        }

        html body.page-id-255 .tmd-energy-split > .wp-block-columns {
            display: grid !important;
            grid-template-columns: minmax(260px, .78fr) minmax(0, 1.65fr) !important;
            gap: clamp(30px, 4vw, 56px) !important;
            align-items: center !important;
            width: min(1180px, calc(100% - 64px)) !important;
            max-width: 1180px !important;
            margin: 0 auto !important;
            padding: clamp(30px, 3.2vw, 46px) !important;
            box-sizing: border-box !important;
            border-radius: 26px !important;
            background: #f4f6f8 !important;
        }

        html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column {
            min-width: 0 !important;
            flex-basis: auto !important;
            margin: 0 !important;
        }

        html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child {
            max-width: 360px !important;
        }

        html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child::after {
            content: '';
            display: block;
            width: 58px;
            height: 3px;
            margin-top: 22px;
            border-radius: 99px;
            background: #ffb52b;
        }

        html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child .wp-block-separator,
        html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child hr {
            display: none !important;
        }

        html body.page-id-255 .tmd-energy-split h2 {
            max-width: 350px !important;
            margin: 0 0 16px !important;
            color: #262e4f !important;
            font-size: clamp(28px, 2.4vw, 36px) !important;
            line-height: 1.08 !important;
            letter-spacing: -.03em !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            hyphens: none !important;
        }

        html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child > p {
            max-width: 340px !important;
            margin: 0 !important;
            color: #5e748b !important;
            font-size: 14px !important;
            line-height: 1.6 !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            hyphens: none !important;
        }

        html body.page-id-255 .tmd-energy-checklist {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px 14px !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        html body.page-id-255 .tmd-energy-checklist li {
            min-width: 0;
            min-height: 78px !important;
            margin: 0 !important;
            padding: 14px 18px 14px 64px !important;
            border: 1px solid rgba(38, 46, 79, .24) !important;
            border-radius: 6px !important;
            background: #fff !important;
            box-shadow: 0 4px 12px rgba(38, 46, 79, .05) !important;
            color: #262e4f !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1.4 !important;
            word-break: normal !important;
            overflow-wrap: break-word !important;
            hyphens: none !important;
        }

        html body.page-id-255 .tmd-energy-checklist li::before {
            left: 14px !important;
            width: 36px !important;
            height: 40px !important;
            border-radius: 10px !important;
            font-size: 12px !important;
            background: radial-gradient(circle at center, #ffb52b 0 9px, rgba(255, 195, 60, .22) 10px 100%) !important;
        }

        html body.page-id-255 .tmd-energy-checklist li:nth-child(5) {
            grid-column: 1 / -1 !important;
        }

        @media (max-width: 820px) {
            html body.page-id-255 .tmd-energy-split > .wp-block-columns {
                grid-template-columns: 1fr !important;
                gap: 28px !important;
                width: min(calc(100% - 32px), 720px) !important;
                padding: 26px !important;
            }

            html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child,
            html body.page-id-255 .tmd-energy-split h2,
            html body.page-id-255 .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child > p {
                max-width: 620px !important;
            }
        }

        @media (max-width: 560px) {
            html body.page-id-255 .tmd-energy-split {
                padding: 38px 0 42px !important;
            }

            html body.page-id-255 .tmd-energy-split > .wp-block-columns {
                width: calc(100% - 24px) !important;
                padding: 22px 18px !important;
                border-radius: 20px !important;
            }

            html body.page-id-255 .tmd-energy-split h2 {
                font-size: 28px !important;
            }

            html body.page-id-255 .tmd-energy-checklist {
                grid-template-columns: 1fr !important;
            }

            html body.page-id-255 .tmd-energy-checklist li,
            html body.page-id-255 .tmd-energy-checklist li:nth-child(5) {
                grid-column: auto !important;
                min-height: 72px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
