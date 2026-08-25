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
function tmd_mark_energy_compatibility_title(string $content): string {
    if (is_admin() || ! is_page(255) || ! str_contains($content, 'Compatibilidad antes que velocidad')) {
        return $content;
    }

    $processor = new WP_HTML_Tag_Processor($content);

    while ($processor->next_tag('H2')) {
        if (! $processor->set_bookmark('tmd-energy-compatibility-heading')) {
            continue;
        }

        $has_title = $processor->next_token()
            && '#text' === $processor->get_token_name()
            && 'Compatibilidad antes que velocidad' === trim($processor->get_modifiable_text());

        if (! $has_title) {
            $processor->release_bookmark('tmd-energy-compatibility-heading');
            continue;
        }

        if ($processor->seek('tmd-energy-compatibility-heading')) {
            $processor->add_class('tmd-energy-compatibility-title');
        }

        $processor->release_bookmark('tmd-energy-compatibility-heading');
        return $processor->get_updated_html();
    }

    return $content;
}
add_filter('the_content', 'tmd_mark_energy_compatibility_title', 98);

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
