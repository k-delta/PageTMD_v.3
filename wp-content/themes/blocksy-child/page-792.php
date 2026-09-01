<?php
/**
 * Ajustes de presentación específicos para /energia/bms/.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-bms-page-adjustments">
        html body.page-id-792 .tmd-bms-compatible {
            border-color: rgba(18, 140, 235, .28) !important;
            background: linear-gradient(145deg, #eef6fd 0%, #fff 58%, #fff8e7 100%) !important;
            box-shadow: 0 18px 45px rgba(38, 46, 79, .10) !important;
        }

        html body.page-id-792 .tmd-bms-chip:nth-child(3n + 1) {
            border-color: rgba(18, 140, 235, .24) !important;
            background: #eef6fd !important;
        }

        html body.page-id-792 .tmd-bms-chip:nth-child(3n + 2) {
            border-color: rgba(255, 195, 60, .42) !important;
            background: #fff8e7 !important;
        }

        html body.page-id-792 .tmd-bms-chip:nth-child(3n) {
            border-color: rgba(94, 116, 139, .22) !important;
            background: #f4f7fb !important;
        }

        html body.page-id-792 .tmd-bms-metric {
            min-height: 118px !important;
            border-top: 4px solid #128ceb !important;
            box-shadow: 0 9px 26px rgba(38, 46, 79, .07) !important;
        }

        html body.page-id-792 .tmd-bms-metric:nth-child(3n + 1) {
            border-top-color: #128ceb !important;
            background: #eef6fd !important;
        }

        html body.page-id-792 .tmd-bms-metric:nth-child(3n + 2) {
            border-top-color: #ffc33c !important;
            background: #fff8e7 !important;
        }

        html body.page-id-792 .tmd-bms-metric:nth-child(3n) {
            border-top-color: #262e4f !important;
            background: #fff !important;
        }

        html body.page-id-792 .tmd-bms-metric span {
            font-size: 13px !important;
        }

        html body.page-id-792 .tmd-bms-metric strong {
            font-size: 17px !important;
            line-height: 1.4 !important;
        }

        html body.page-id-792 .tmd-bms-note {
            margin-top: 24px !important;
            padding: 20px 22px !important;
            border: 1px solid rgba(255, 195, 60, .55) !important;
            border-left: 6px solid #ffc33c !important;
            border-radius: 12px !important;
            color: #262e4f !important;
            background: linear-gradient(90deg, #fff3bf, #fff9e9) !important;
            box-shadow: 0 10px 24px rgba(38, 46, 79, .06) !important;
            font-size: 15px !important;
            line-height: 1.65 !important;
        }

        html body.page-id-792 .tmd-bms-note strong {
            color: #262e4f !important;
            font-size: 16px !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(1) {
            border-color: #262e4f !important;
            background: #262e4f !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(1) h3 {
            color: #fff !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(1) p {
            color: rgba(255, 255, 255, .78) !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(1) span {
            color: #ffc33c !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(2) {
            border-top: 4px solid #128ceb !important;
            background: #eef6fd !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(3) {
            border-top: 4px solid #ffc33c !important;
            background: #fff8e7 !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(4) {
            border-top: 4px solid #5e748b !important;
            background: #f4f7fb !important;
        }

        html body.page-id-792 .tmd-bms-benefit:nth-child(5) {
            border-top: 4px solid #128ceb !important;
            background: #eaf4fd !important;
        }

        html body.page-id-792 .tmd-bms-app span {
            font-size: 16px !important;
            letter-spacing: .06em;
        }

        html body.page-id-792 .tmd-bms-media-section {
            padding: clamp(32px, 5vw, 58px) 0 0;
        }

        html body.page-id-792 .tmd-bms-media {
            display: flex;
            min-height: 220px;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            border: 1px solid rgba(18, 140, 235, .22);
            border-radius: 22px;
            background: linear-gradient(145deg, #eef6fd 0%, #fff 58%, #fff8e7 100%);
            box-shadow: 0 18px 45px rgba(38, 46, 79, .10);
        }

        html body.page-id-792 .tmd-bms-media img {
            display: block;
            width: 100%;
            max-height: 650px;
            object-fit: contain;
            object-position: center;
        }

        @media (max-width: 720px) {
            html body.page-id-792 .tmd-bms-metric {
                min-height: 0 !important;
            }

            html body.page-id-792 .tmd-bms-media-section {
                padding-top: 28px;
            }

            html body.page-id-792 .tmd-bms-media {
                min-height: 0;
                border-radius: 16px;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
