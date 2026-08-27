<?php
/**
 * Consistencia visual de marca para la portada (ID 47).
 */

defined('ABSPATH') || exit;

require_once get_stylesheet_directory() . '/inc/tmd-brand-consistency.php';
tmd_use_brand_consistency();

/**
 * Resuelve el video canónico del hero desde la librería de medios.
 */
function tmd_home_hero_video_attachment(): array {
    static $resolved = false;
    static $video = [];

    if ($resolved) {
        return $video;
    }

    $resolved = true;

    $attachment_ids = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'video/mp4',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'DESC',
        'meta_query'     => [[
            'key'     => '_wp_attached_file',
            'value'   => 'Hero-Video-Montacargas',
            'compare' => 'LIKE',
        ]],
    ]);

    if (empty($attachment_ids)) {
        return $video;
    }

    $attachment_id = (int) $attachment_ids[0];
    $attachment_url = wp_get_attachment_url($attachment_id);

    if (! $attachment_url) {
        return $video;
    }

    $video = [
        'id'  => $attachment_id,
        'url' => $attachment_url,
    ];

    return $video;
}

/**
 * Kadence conserva la URL y opciones del video en el JSON serializado del bloque.
 * Reescribimos esos atributos antes de do_blocks para que el hero use el adjunto
 * canónico y se reproduzca en bucle.
 */
add_filter('the_content', static function (string $content): string {
    $hero_video = tmd_home_hero_video_attachment();

    if (empty($hero_video['url']) || empty($hero_video['id'])) {
        return $content;
    }

    $old_url = 'https://tecnimontacargas.com/wp-content/uploads/2026/07/WhatsApp-Video-2026-07-08-at-16.24.56.mp4';
    $updated = $content;

    if (str_contains($updated, $old_url)) {
        $pattern = '/("local"\s*:\s*")' . preg_quote($old_url, '/') . '("\s*,\s*"localID"\s*:\s*)\d+/';
        $replaced = preg_replace_callback(
            $pattern,
            static function (array $matches) use ($hero_video): string {
                return $matches[1]
                    . esc_url_raw($hero_video['url'])
                    . $matches[2]
                    . (int) $hero_video['id'];
            },
            $updated,
            1
        );

        if (is_string($replaced)) {
            $updated = $replaced;
        }
    }

    $looped = preg_replace('/"loop"\s*:\s*false/', '"loop":true', $updated, 1);

    return is_string($looped) ? $looped : $updated;
}, 8);

/**
 * Fallback adicional sobre los atributos ya parseados del bloque.
 */
add_filter('render_block_data', static function (array $parsed_block): array {
    static $hero_replaced = false;

    if ($hero_replaced || ($parsed_block['blockName'] ?? '') !== 'kadence/rowlayout') {
        return $parsed_block;
    }

    $attrs = $parsed_block['attrs'] ?? [];
    $videos = $attrs['backgroundVideo'] ?? [];

    if (($attrs['backgroundSettingTab'] ?? '') !== 'video' || empty($videos) || ! is_array($videos)) {
        return $parsed_block;
    }

    $hero_video = tmd_home_hero_video_attachment();

    if (empty($hero_video['url']) || empty($hero_video['id'])) {
        return $parsed_block;
    }

    foreach ($videos as $index => $video) {
        if (! is_array($video)) {
            continue;
        }

        $videos[$index]['local'] = esc_url_raw($hero_video['url']);
        $videos[$index]['localID'] = (int) $hero_video['id'];
        $videos[$index]['loop'] = true;
        $videos[$index]['mute'] = true;
        $hero_replaced = true;
        break;
    }

    $parsed_block['attrs']['backgroundVideo'] = $videos;

    return $parsed_block;
}, 20);

/**
 * El hero ocupa el viewport también en móvil. El video se recorta con cover en
 * lugar de deformarse, manteniendo una única fuente multimedia para escritorio
 * y teléfono.
 */
add_action('wp_head', static function (): void {
    ?>
    <style id="tmd-home-hero-video-responsive">
        body.page-id-47 .kb-row-layout-id47_9c201d-d2 {
            min-height: 100vh;
            min-height: 100svh;
        }

        body.page-id-47 .kb-row-layout-id47_9c201d-d2 > .kt-row-column-wrap {
            min-height: inherit;
        }

        body.page-id-47 .kb-row-layout-id47_9c201d-d2 video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: 50% 50% !important;
        }

        @media (max-width: 767px) {
            body.page-id-47 .kb-row-layout-id47_9c201d-d2 {
                min-height: 100svh;
            }

            body.page-id-47 .kb-row-layout-id47_9c201d-d2 > .kt-row-column-wrap {
                min-height: 100svh;
                align-content: center;
            }

            body.page-id-47 .kb-row-layout-id47_9c201d-d2 video {
                object-position: 50% 50% !important;
            }
        }
    </style>
    <?php
}, 100);

/**
 * Refuerza los atributos requeridos por autoplay en iOS/Android. Kadence ya
 * controla el elemento, pero este ajuste evita diferencias entre navegadores.
 */
add_action('wp_footer', static function (): void {
    ?>
    <script id="tmd-home-hero-video-runtime">
        (() => {
            const video = document.querySelector('body.page-id-47 .kb-row-layout-id47_9c201d-d2 video');
            if (!video) return;

            video.loop = true;
            video.muted = true;
            video.autoplay = true;
            video.playsInline = true;
            video.setAttribute('loop', '');
            video.setAttribute('muted', '');
            video.setAttribute('autoplay', '');
            video.setAttribute('playsinline', '');

            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                const playback = video.play();
                if (playback && typeof playback.catch === 'function') {
                    playback.catch(() => {});
                }
            }
        })();
    </script>
    <?php
}, 100);

require get_template_directory() . '/page.php';
