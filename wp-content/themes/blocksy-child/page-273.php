<?php
/**
 * Ajustes de presentación específicos para Trabaja con nosotros (ID 273).
 */

defined('ABSPATH') || exit;

/**
 * Versiona el asset del equipo con su mtime real para evitar que navegador/CDN
 * conserve una copia anterior cuando se reemplaza el archivo manteniendo el nombre.
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

    return is_string($updated) ? $updated : $content;
}, 20);

add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-jobs-testimonial-portrait-zoom">
        body.page-id-273 .tmd-jobs-testimonial {
            min-height: 0 !important;
            padding: 52px 20px !important;
        }

        /* Nuestro equipo: más alto y con un encuadre bastante más abierto. */
        body.page-id-273 img[src*="gerencia"] {
            width: 100% !important;
            height: 380px !important;
            max-height: none !important;
            object-fit: cover !important;
            object-position: 50% 42% !important;
        }

        body.page-id-273 *:has(> img[src*="gerencia"]) {
            height: 380px !important;
            max-height: none !important;
            overflow: hidden !important;
        }

        body.page-id-273 .tmd-jobs-avatar-wrap {
            width: 260px !important;
            height: 260px !important;
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
            object-position: 50% 56% !important;
            transform: scale(1.2);
            transform-origin: center center;
            clip-path: circle(41.667% at 50% 50%);
        }

        @media (max-width: 900px) {
            body.page-id-273 .tmd-jobs-testimonial {
                padding: 46px 20px !important;
            }

            body.page-id-273 img[src*="gerencia"],
            body.page-id-273 *:has(> img[src*="gerencia"]) {
                height: 340px !important;
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
                height: 300px !important;
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
