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
            padding: 82px 22px 22px !important;
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
            width: 46px;
            height: 46px;
            place-items: center;
            border-radius: 10px;
            content: '';
            background-position: center;
            background-repeat: no-repeat;
            background-size: 28px 28px;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1) {
            border-top: 4px solid #128ceb !important;
            background: #262e4f !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1)::before {
            background-color: rgba(255, 255, 255, .08);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc33c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M13 2 4.5 14h7L11 22l8.5-12h-7L13 2Z'/%3E%3C/svg%3E");
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1) h3 {
            color: #fff !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(1) p {
            color: rgba(255, 255, 255, .76) !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(2) {
            border-top: 4px solid #ffc33c !important;
            background: #fff !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(2)::before {
            background-color: rgba(255, 195, 60, .12);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc33c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='6' width='16' height='12' rx='2'/%3E%3Cpath d='M21 10v4'/%3E%3C/svg%3E");
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(3) {
            border-top: 4px solid #262e4f !important;
            background: #eef4f9 !important;
        }

        body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card:nth-child(3)::before {
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc33c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.09a2 2 0 0 1 1 1.74v.5a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z'/%3E%3Ccircle cx='12' cy='12' r='3'/%3E%3C/svg%3E");
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
 * Ajustes visuales específicos de /energia/baterias/plomo/.
 * El ámbito de página y del contenedor evita alterar los componentes
 * compartidos de Energía o la composición equivalente de Cargadores.
 */
add_action('wp_head', static function (): void {
    if (! is_page(401)) {
        return;
    }
    ?>
    <style id="tmd-energy-lead-battery-page-adjustments">
        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > h2 {
            max-width: none !important;
            margin: 0 auto 30px !important;
            text-align: center;
            font-size: clamp(28px, 3vw, 36px) !important;
            line-height: 1.1 !important;
            letter-spacing: -.025em !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 22px !important;
            align-items: stretch;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card {
            position: relative;
            min-width: 0;
            min-height: 218px;
            margin: 0 !important;
            padding: 86px 24px 26px !important;
            overflow: hidden;
            border: 1px solid rgba(38, 46, 79, .10) !important;
            border-radius: 14px !important;
            box-shadow: 0 12px 30px rgba(38, 46, 79, .11) !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card::before {
            position: absolute;
            top: 20px;
            left: 24px;
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            border-radius: 11px;
            content: '';
            background-position: center;
            background-repeat: no-repeat;
            background-size: 28px 28px;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(1) {
            border-top: 4px solid #128ceb !important;
            background: #262e4f !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(1)::before {
            background-color: rgba(255, 255, 255, .09);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc33c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='4' y='5' width='16' height='15' rx='2'/%3E%3Cpath d='M8 3v4M16 3v4M4 10h16M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01'/%3E%3C/svg%3E");
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(1) h3 {
            color: #fff !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(1) p {
            color: rgba(255, 255, 255, .76) !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(2) {
            border-top: 4px solid #ffc33c !important;
            background: #fff !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(2)::before {
            background-color: rgba(255, 195, 60, .14);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc33c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M8 12h8'/%3E%3Cpath d='M6 9v6M18 9v6M3 10v4M21 10v4'/%3E%3C/svg%3E");
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(3) {
            border-top: 4px solid #262e4f !important;
            background: #eef4f9 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card:nth-child(3)::before {
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc33c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m14.7 6.3 3-3a4.5 4.5 0 0 0-5.9 5.9l-7.5 7.5a2.1 2.1 0 0 0 3 3l7.5-7.5a4.5 4.5 0 0 0 5.9-5.9l-3 3-4-1-1-4Z'/%3E%3Cpath d='m5.7 18.3.01.01'/%3E%3C/svg%3E");
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card h3 {
            margin: 0 0 10px !important;
            font-size: 20px !important;
            line-height: 1.25 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card p {
            margin: 0 !important;
            font-size: 15px !important;
            line-height: 1.6 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split {
            position: relative;
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto !important;
            padding: clamp(48px, 6vw, 72px) 0 clamp(46px, 5vw, 64px) !important;
            border-top: 0 !important;
            background: #f5f6f8;
            box-shadow: 0 0 0 100vmax #f5f6f8;
            clip-path: inset(0 -100vmax);
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split::after {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 50%;
            width: 100vw;
            height: 3px;
            transform: translateX(-50%);
            content: '';
            background: #ffc33c;
            pointer-events: none;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns {
            display: grid !important;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.6fr) !important;
            gap: clamp(36px, 5vw, 72px) !important;
            align-items: center !important;
            margin: 0 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child {
            max-width: 440px;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child::after {
            display: block;
            width: 62px;
            height: 4px;
            margin-top: 28px;
            border-radius: 99px;
            content: '';
            background: #ffc33c;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split h2 {
            max-width: 430px !important;
            margin: 0 0 22px !important;
            color: #262e4f !important;
            font-size: clamp(36px, 3.8vw, 50px) !important;
            font-weight: 700 !important;
            line-height: 1.04 !important;
            letter-spacing: -.035em !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child > p {
            max-width: 430px;
            margin: 0 !important;
            color: #5e748b !important;
            font-size: 17px !important;
            line-height: 1.55 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 16px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist li {
            position: relative;
            display: flex;
            min-width: 0;
            min-height: 86px;
            align-items: center;
            margin: 0 !important;
            padding: 17px 20px 17px 72px !important;
            border: 1px solid rgba(38, 46, 79, .24) !important;
            border-radius: 8px !important;
            color: #262e4f !important;
            background: #fff !important;
            box-shadow: 0 4px 12px rgba(38, 46, 79, .05) !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            line-height: 1.45 !important;
        }

        body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist li::before {
            position: absolute;
            top: 50% !important;
            left: 17px !important;
            display: grid;
            width: 40px;
            height: 44px;
            place-items: center;
            transform: translateY(-50%);
            border-radius: 12px;
            content: '✓';
            color: #fff !important;
            background: radial-gradient(circle at center, #ffc33c 0 10px, rgba(255, 195, 60, .24) 11px 100%);
            font-family: Arial, sans-serif;
            font-size: 14px !important;
            font-weight: 800;
            line-height: 1;
        }

        body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist li:last-child:nth-child(odd) {
            grid-column: 1 / -1;
        }

        @media (max-width: 900px) {
            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns {
                grid-template-columns: 1fr !important;
                gap: 30px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child {
                max-width: 620px;
            }
        }

        @media (max-width: 781px) {
            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > h2 {
                margin-bottom: 22px !important;
                font-size: 28px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards,
            body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist {
                grid-template-columns: 1fr !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards > .tmd-energy-card {
                min-height: 0;
            }

            body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist li:last-child:nth-child(odd) {
                grid-column: auto;
            }
        }

        @media (max-width: 640px) {
            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split {
                width: min(100% - 28px, 1180px);
                padding: 38px 0 42px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split h2 {
                font-size: 32px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo > .tmd-energy-split > .wp-block-columns > .wp-block-column:first-child > p {
                font-size: 15px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist {
                gap: 12px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist li {
                min-height: 80px;
                padding: 16px 16px 16px 68px !important;
            }

            body.page-id-401 .tmd-energy-inner--plomo .tmd-energy-checklist li::before {
                left: 14px !important;
                width: 38px;
                height: 40px;
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
