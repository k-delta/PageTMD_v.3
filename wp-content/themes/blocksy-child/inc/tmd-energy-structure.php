<?php
/**
 * Estructura canónica de Energía.
 */

defined('ABSPATH') || exit;

/*
 * Conserva referencias históricas sin mantener dos URLs indexables para BMS.
 * La página 512 permanece publicada y recuperable desde WordPress.
 */
add_action('template_redirect', static function (): void {
    $path = trailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));

    if ($path !== '/bms/') {
        return;
    }

    wp_safe_redirect(home_url('/energia/bms/'), 301);
    exit;
}, 1);

/*
 * La página de cargadores todavía conserva en la base de datos la imagen
 * histórica cargadores2.jpeg. Mientras esa referencia siga presente,
 * sustituimos únicamente ese <img> por el recurso canónico del tema.
 *
 * El filtro corre después de wp_filter_content_tags para evitar que WordPress
 * vuelva a añadir el srcset del adjunto antiguo. Si el contenido se edita y
 * deja de usar cargadores2.jpeg, esta regla pasa a ser un no-op.
 */
add_filter('the_content', static function (string $content): string {
    if (is_admin() || ! is_page(255) || ! str_contains($content, 'cargadores2.jpeg')) {
        return $content;
    }

    $old_image = 'https://tecnimontacargas.com/wp-content/uploads/2026/07/cargadores2.jpeg';
    $new_image = esc_url(get_stylesheet_directory_uri() . '/assets/img/mega-menu/energy-cargadores.png');
    $replacement = sprintf(
        '<img src="%s" alt="Cargador industrial para batería de montacargas" class="tmd-energy-charger-hero-image" loading="eager" decoding="async" fetchpriority="high">',
        $new_image
    );

    $pattern = '#<img\b(?=[^>]*\bsrc=["\']' . preg_quote($old_image, '#') . '["\'])[^>]*>#i';
    $updated = preg_replace($pattern, $replacement, $content, 1);

    return is_string($updated) ? $updated : $content;
}, 99);

/*
 * Marca únicamente la sección "Compatibilidad antes que velocidad" para poder
 * aplicar el tratamiento visual de referencia sin afectar las demás tarjetas
 * de Energía.
 */
add_filter('the_content', static function (string $content): string {
    if (is_admin() || ! is_page(255) || ! str_contains($content, 'Compatibilidad antes que velocidad')) {
        return $content;
    }

    return str_replace(
        '<h2>Compatibilidad antes que velocidad</h2>',
        '<h2 class="tmd-energy-compatibility-title">Compatibilidad antes que velocidad</h2>',
        $content
    );
}, 98);

/*
 * Ajustes visuales específicos de /energia/cargadores/.
 */
add_action('wp_head', static function (): void {
    if (! is_page(255)) {
        return;
    }
    ?>
    <style id="tmd-energy-charger-page-adjustments">
        body.page-id-255 .tmd-energy-cta > h2,
        body.page-id-255 .tmd-energy-cta > p,
        body.page-id-255 .tmd-energy-cta > .tmd-energy-actions {
            width: min(760px, 100%);
            margin-left: auto !important;
            margin-right: auto !important;
        }

        body.page-id-255 .tmd-energy-cta > p {
            margin-bottom: 0;
        }

        body.page-id-255 .tmd-energy-cta > .tmd-energy-actions {
            justify-content: flex-start;
            margin-top: 26px !important;
        }

        /*
         * "Qué revisamos para recomendar un cargador".
         * Replica el lenguaje visual del protocolo de referencia: fondo gris,
         * acento amarillo y tarjetas técnicas compactas en dos columnas.
         */
        body.page-id-255 .tmd-energy-split {
            position: relative;
            display: grid !important;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.6fr) !important;
            gap: clamp(36px, 5vw, 72px) !important;
            align-items: center !important;
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto !important;
            padding: clamp(48px, 6vw, 72px) 0 clamp(46px, 5vw, 64px) !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            background: #f5f6f8;
            box-shadow: 0 0 0 100vmax #f5f6f8;
            clip-path: inset(0 -100vmax);
        }

        body.page-id-255 .tmd-energy-split::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 100vw;
            height: 3px;
            transform: translateX(-50%);
            background: #ffc33c;
            pointer-events: none;
        }

        body.page-id-255 .tmd-energy-split > div:first-child {
            max-width: 440px;
        }

        body.page-id-255 .tmd-energy-split > div:first-child::after {
            content: '';
            display: block;
            width: 62px;
            height: 4px;
            margin-top: 28px;
            border-radius: 99px;
            background: #ffb52b;
        }

        body.page-id-255 .tmd-energy-split h2 {
            max-width: 430px !important;
            margin: 0 0 22px !important;
            color: #262e4f !important;
            font-size: clamp(36px, 3.8vw, 50px) !important;
            font-weight: 700 !important;
            line-height: 1.04 !important;
            letter-spacing: -.035em !important;
        }

        body.page-id-255 .tmd-energy-split > div:first-child > p {
            max-width: 430px;
            margin: 0 !important;
            color: #5e748b !important;
            font-size: 17px !important;
            line-height: 1.55 !important;
        }

        body.page-id-255 .tmd-energy-checklist {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 16px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.page-id-255 .tmd-energy-checklist li {
            position: relative;
            display: flex;
            min-height: 94px;
            align-items: center;
            margin: 0 !important;
            padding: 18px 22px 18px 78px !important;
            border: 1px solid rgba(38, 46, 79, .28) !important;
            border-radius: 8px !important;
            color: #262e4f !important;
            background: #fff !important;
            box-shadow: 0 4px 12px rgba(38, 46, 79, .05) !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            line-height: 1.45 !important;
        }

        body.page-id-255 .tmd-energy-checklist li::before {
            content: '✓';
            position: absolute;
            top: 50% !important;
            left: 18px !important;
            display: grid;
            width: 40px;
            height: 44px;
            place-items: center;
            transform: translateY(-50%);
            border-radius: 12px;
            color: #fff !important;
            background: radial-gradient(circle at center, #ffb52b 0 10px, rgba(255, 195, 60, .24) 11px 100%);
            font-family: Arial, sans-serif;
            font-size: 14px !important;
            font-weight: 800;
            line-height: 1;
        }

        body.page-id-255 .tmd-energy-checklist li:nth-child(5) {
            grid-column: 1 / -1;
        }

        body.page-id-255 .tmd-energy-compatibility-title {
            max-width: none !important;
            margin: 0 auto 30px !important;
            text-align: center;
            font-size: clamp(28px, 3vw, 36px) !important;
            line-height: 1.1 !important;
            letter-spacing: -.025em !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards {
            gap: 22px;
            align-items: stretch;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card {
            position: relative;
            min-height: 190px;
            padding: 62px 22px 22px !important;
            overflow: hidden;
            border: 1px solid rgba(38, 46, 79, .10) !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 26px rgba(38, 46, 79, .10) !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card::before {
            position: absolute;
            top: 18px;
            left: 22px;
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 7px;
            font-family: Arial, sans-serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1) {
            border-top: 4px solid #128ceb !important;
            background: #262e4f !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1)::before {
            content: '⚡';
            color: #ffc33c;
            background: rgba(255, 255, 255, .08);
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1) h3 {
            color: #fff !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1) p {
            color: rgba(255, 255, 255, .76) !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(2) {
            border-top: 4px solid #ff9f1a !important;
            background: #fff !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(2)::before {
            content: '▭';
            color: #ff8f00;
            background: #fff4e7;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(3) {
            border-top: 4px solid #262e4f !important;
            background: #eef4f9 !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(3)::before {
            content: '⚙';
            color: #ff8f00;
            background: #fff;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card h3 {
            margin: 0 0 10px !important;
            font-size: 18px !important;
            line-height: 1.2 !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card p {
            margin: 0 !important;
            font-size: 13px !important;
            line-height: 1.55 !important;
        }

        @media (max-width: 900px) {
            body.page-id-255 .tmd-energy-split {
                grid-template-columns: 1fr !important;
                gap: 30px !important;
            }

            body.page-id-255 .tmd-energy-split > div:first-child {
                max-width: 620px;
            }

            body.page-id-255 .tmd-energy-split h2,
            body.page-id-255 .tmd-energy-split > div:first-child > p {
                max-width: 620px !important;
            }
        }

        @media (max-width: 640px) {
            body.page-id-255 .tmd-energy-split {
                width: min(100% - 28px, 1180px);
                padding: 38px 0 42px !important;
            }

            body.page-id-255 .tmd-energy-split h2 {
                font-size: 32px !important;
            }

            body.page-id-255 .tmd-energy-split > div:first-child > p {
                font-size: 15px !important;
            }

            body.page-id-255 .tmd-energy-checklist {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }

            body.page-id-255 .tmd-energy-checklist li,
            body.page-id-255 .tmd-energy-checklist li:nth-child(5) {
                grid-column: auto;
                min-height: 82px;
                padding: 16px 16px 16px 72px !important;
            }

            body.page-id-255 .tmd-energy-checklist li::before {
                left: 16px !important;
                width: 38px;
                height: 40px;
            }
        }

        @media (max-width: 781px) {
            body.page-id-255 .tmd-energy-compatibility-title {
                margin-bottom: 22px !important;
                font-size: 28px !important;
            }

            body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card {
                min-height: 0;
            }
        }
    </style>
    <?php
}, 99);

/*
 * Rank Math debe publicar únicamente la URL canónica /energia/bms/.
 */
add_filter('rank_math/sitemap/posts_to_exclude', static function (array $post_ids): array {
    $post_ids[] = 512;
    return array_values(array_unique(array_map('intval', $post_ids)));
});
