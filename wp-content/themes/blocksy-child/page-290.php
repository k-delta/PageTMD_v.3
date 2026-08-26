<?php
/**
 * Ajustes de presentación específicos para Mantenimiento correctivo (ID 290).
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-corrective-review-adjustments">
        body.page-id-290 .tmd-maintenance-page { --tmd-maint-yellow: #ffc33c; }

        body.page-id-290 .tmd-maint-hero__lead + .tmd-maint-hero__lead { margin-top: 10px; }

        body.page-id-290 .tmd-corrective-signals,
        body.page-id-290 .tmd-corrective-criteria {
            padding: clamp(28px, 4vw, 46px);
            border: 1px solid rgba(38, 46, 79, .10);
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(18, 140, 235, .09), rgba(94, 116, 139, .06)), #f8fbfe;
            box-shadow: 0 16px 38px rgba(38, 46, 79, .07);
        }

        body.page-id-290 .tmd-corrective-signals .tmd-maint-section__header,
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-section__header { max-width: 880px; }

        body.page-id-290 .tmd-corrective-signals .tmd-maint-grid,
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-grid { gap: 16px; }

        body.page-id-290 .tmd-corrective-signals .tmd-maint-card,
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card {
            border-radius: 12px;
            border-color: rgba(38, 46, 79, .12);
            box-shadow: 0 10px 24px rgba(38, 46, 79, .08);
        }

        body.page-id-290 .tmd-corrective-signals .tmd-maint-card { min-height: 220px; padding: 28px 24px 24px; }
        body.page-id-290 .tmd-corrective-signals .tmd-maint-card:nth-child(1) { border-top: 4px solid #128ceb; background: #fff; }
        body.page-id-290 .tmd-corrective-signals .tmd-maint-card:nth-child(2) { border-top: 4px solid #ffc33c; background: #fffaf0; }
        body.page-id-290 .tmd-corrective-signals .tmd-maint-card:nth-child(3) { border-top: 4px solid #262e4f; background: #eef4f9; }

        body.page-id-290 .tmd-corrective-signals .tmd-maint-card::before,
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card::before { content: none; display: none; }

        body.page-id-290 .tmd-corrective-signals .tmd-maint-card__label { margin-bottom: 18px; border-radius: 8px; }
        body.page-id-290 .tmd-corrective-signals .tmd-maint-card:nth-child(2) .tmd-maint-card__label { color: #6c5200; background: rgba(255, 195, 60, .20); }
        body.page-id-290 .tmd-corrective-signals .tmd-maint-card:nth-child(3) .tmd-maint-card__label { color: #262e4f; background: rgba(38, 46, 79, .08); }

        body.page-id-290 .tmd-corrective-signals > .tmd-maint-note {
            margin-top: 26px;
            padding: 21px 22px;
            border-left: 6px solid #ffc33c;
            border-radius: 0 10px 10px 0;
            color: #fff;
            background: #262e4f;
            box-shadow: 0 12px 28px rgba(38, 46, 79, .16);
        }
        body.page-id-290 .tmd-corrective-signals > .tmd-maint-note strong { color: #ffc33c; }

        body.page-id-290 .tmd-corrective-systems { border-color: rgba(38, 46, 79, .10); background: #fff; }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system { border: 1px solid rgba(38, 46, 79, .08); border-radius: 12px; box-shadow: 0 7px 18px rgba(38, 46, 79, .05); }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system:nth-child(1) { border-left: 4px solid #128ceb; background: #edf7ff; }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system:nth-child(2) { border-left: 4px solid #ffc33c; background: #fff8e7; }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system:nth-child(3) { border-left: 4px solid #5e748b; background: #f0f3f6; }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system:nth-child(4) { border-left: 4px solid #ffc33c; background: #262e4f; }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system:nth-child(4) strong { color: #fff; }
        body.page-id-290 .tmd-corrective-systems .tmd-maint-system:nth-child(4) span { color: rgba(255, 255, 255, .80); }

        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card { min-height: 205px; padding: 32px 22px 24px; }
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card:nth-child(1) { border-top: 4px solid #128ceb; background: #fff; }
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card:nth-child(2) { border-top: 4px solid #ffc33c; background: #fffaf0; }
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card:nth-child(3) { border-top: 4px solid #5e748b; background: #eef4f9; }
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card:nth-child(4) { border-top: 4px solid #262e4f; background: #fff; }
        body.page-id-290 .tmd-corrective-criteria .tmd-maint-card h3 { margin-top: 4px; }

        @media (max-width: 980px) {
            body.page-id-290 .tmd-corrective-signals .tmd-maint-card,
            body.page-id-290 .tmd-corrective-criteria .tmd-maint-card { min-height: 0; }
        }

        @media (max-width: 640px) {
            body.page-id-290 .tmd-corrective-signals,
            body.page-id-290 .tmd-corrective-criteria { padding: 24px 18px; border-radius: 14px; }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
