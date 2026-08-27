<?php
/**
 * Ajustes de presentación específicos para Trabaja con nosotros (ID 273).
 */

defined('ABSPATH') || exit;

/**
 * Versiona imágenes reemplazables con su mtime real para evitar que navegador/CDN
 * conserve una copia anterior cuando se mantiene el mismo nombre de archivo.
 */
add_filter('the_content', static function ($content) {
    if (! is_page(273) || ! is_main_query() || ! in_the_loop()) {
        return $content;
    }

    $relative_path = 'assets/img/personal/trabaja-equipo.webp';
    $absolute_path = get_stylesheet_directory() . '/' . $relative_path;
    $asset_url = get_stylesheet_directory_uri() . '/' . $relative_path;

    if (is_file($absolute_path)) {
        $asset_url = add_query_arg('ver', (string) filemtime($absolute_path), $asset_url);
    }

    $pattern = '~(?:https?://[^"\']+)?/wp-content/themes/blocksy-child/assets/img/personal/trabaja-equipo\.webp(?:\?[^"\']*)?~i';
    $updated = preg_replace($pattern, esc_url($asset_url), $content, 1);
    $updated = is_string($updated) ? $updated : $content;

    $testimonial_relative_path = '/wp-content/uploads/2026/08/trabaja-colaborador-20260826.jpeg';
    $testimonial_absolute_path = ABSPATH . ltrim($testimonial_relative_path, '/');

    if (is_file($testimonial_absolute_path)) {
        $testimonial_url = content_url('/uploads/2026/08/trabaja-colaborador-20260826.jpeg');
        $testimonial_mtime = filemtime($testimonial_absolute_path);

        if ($testimonial_mtime !== false) {
            $testimonial_url = add_query_arg('ver', (string) $testimonial_mtime, $testimonial_url);
        }

        $testimonial_pattern = '~(?:https?://[^"\']+)?/wp-content/uploads/2026/08/trabaja-colaborador-20260826\.jpeg(?:\?[^"\']*)?~i';
        $testimonial_updated = preg_replace(
            $testimonial_pattern,
            esc_url($testimonial_url),
            $updated,
            1
        );

        if (is_string($testimonial_updated)) {
            $updated = $testimonial_updated;
        }
    }

    return $updated;
}, 20);

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-jobs-testimonial-portrait-zoom">
        body.page-id-273 .tmd-jobs-testimonial {
            min-height: 0 !important;
            padding: 52px 20px !important;
        }

        /* Nuestro equipo: encuadre alto y anclado arriba para priorizar los rostros. */
        body.page-id-273 img[src*="gerencia"] {
            width: 100% !important;
            height: 560px !important;
            max-height: none !important;
            object-fit: cover !important;
            object-position: 50% 0% !important;
        }

        body.page-id-273 *:has(> img[src*="gerencia"]) {
            height: 560px !important;
            max-height: none !important;
            overflow: hidden !important;
        }

        body.page-id-273 .tmd-jobs-avatar-wrap {
            width: 260px !important;
            height: 260px !important;
            overflow: hidden !important;
            border: 5px solid #ffc33c;
            border-radius: 999px;
            box-shadow: 0 26px 60px rgba(0, 0, 0, .32);
        }

        body.page-id-273 .tmd-jobs-avatar-wrap img {
            width: 100% !important;
            height: 100% !important;
            border: 0 !important;
            border-radius: 999px !important;
            box-shadow: none !important;
            object-fit: cover !important;
            object-position: 50% 50% !important;
            transform: scale(1.45);
            transform-origin: 50% 48%;
            clip-path: none !important;
        }

        @media (max-width: 900px) {
            body.page-id-273 .tmd-jobs-testimonial {
                padding: 46px 20px !important;
            }

            body.page-id-273 img[src*="gerencia"],
            body.page-id-273 *:has(> img[src*="gerencia"]) {
                height: 460px !important;
            }

            body.page-id-273 .tmd-jobs-avatar-wrap {
                width: 240px !important;
                height: 240px !important;
            }
        }

        @media (max-width: 640px) {
            body.page-id-273 .tmd-jobs-testimonial {
                padding: 38px 16px !important;
            }

            body.page-id-273 img[src*="gerencia"],
            body.page-id-273 *:has(> img[src*="gerencia"]) {
                height: 360px !important;
            }

            body.page-id-273 .tmd-jobs-avatar-wrap {
                width: 220px !important;
                height: 220px !important;
            }
        }
    </style>
    <?php
}, 100);

require get_template_directory() . '/page.php';
