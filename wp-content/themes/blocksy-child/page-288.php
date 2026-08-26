<?php
/**
 * Ajustes de presentación específicos para Mantenimiento preventivo (ID 288).
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-preventive-review-adjustments">
        body.page-id-288 .tmd-maintenance-page {
            --tmd-maint-yellow: #ffc33c;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section {
            margin-top: 42px;
            padding: clamp(28px, 4vw, 46px);
            border: 1px solid rgba(38, 46, 79, .10);
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(18, 140, 235, .09), rgba(94, 116, 139, .07)),
                #f8fbfe;
            box-shadow: 0 16px 38px rgba(38, 46, 79, .08);
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-section__header {
            max-width: 880px;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-grid {
            gap: 16px;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card {
            min-height: 220px;
            padding: 28px 24px 24px;
            border-radius: 12px;
            border-color: rgba(38, 46, 79, .12);
            box-shadow: 0 10px 24px rgba(38, 46, 79, .08);
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(1),
        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(4) {
            border-top: 4px solid #128ceb;
            background: #ffffff;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(2),
        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(5) {
            border-top: 4px solid #ffc33c;
            background: #fffaf0;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(3),
        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(6) {
            border-top: 4px solid #262e4f;
            background: #eef4f9;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card::before {
            content: none;
            display: none;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card__label {
            margin-bottom: 18px;
            border-radius: 8px;
            color: #0d6fb6;
            background: rgba(18, 140, 235, .10);
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(2) .tmd-maint-card__label,
        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(5) .tmd-maint-card__label {
            color: #6c5200;
            background: rgba(255, 195, 60, .20);
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(3) .tmd-maint-card__label,
        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card:nth-child(6) .tmd-maint-card__label {
            color: #262e4f;
            background: rgba(38, 46, 79, .08);
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card h3 {
            font-size: 20px;
            line-height: 1.28;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card p {
            font-size: 15px;
            line-height: 1.62;
        }

        body.page-id-288 .tmd-maint-hero + .tmd-maint-section > .tmd-maint-note {
            margin-top: 26px;
            padding: 20px 22px;
            border-left: 6px solid #ffc33c;
            border-radius: 0 10px 10px 0;
            color: #262e4f;
            background: linear-gradient(90deg, rgba(255, 195, 60, .20), #fffaf0);
            box-shadow: 0 8px 20px rgba(38, 46, 79, .05);
        }

        @media (max-width: 980px) {
            body.page-id-288 .tmd-maint-hero + .tmd-maint-section .tmd-maint-card {
                min-height: 0;
            }
        }

        @media (max-width: 640px) {
            body.page-id-288 .tmd-maint-hero + .tmd-maint-section {
                margin-top: 30px;
                padding: 24px 18px;
                border-radius: 14px;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
