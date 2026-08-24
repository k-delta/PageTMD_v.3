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
 * Alinea el CTA final de /energia/cargadores/ como un único bloque visual:
 * título, texto y botón comparten el mismo ancho y borde izquierdo.
 */
add_action('wp_head', static function (): void {
    if (! is_page(255)) {
        return;
    }
    ?>
    <style id="tmd-energy-charger-cta-alignment">
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
