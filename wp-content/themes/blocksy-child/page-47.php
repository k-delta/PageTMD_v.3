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

    $attached_file = get_attached_file($attachment_id);

    if (is_string($attached_file) && $attached_file !== '' && is_file($attached_file)) {
        $mtime = filemtime($attached_file);

        if ($mtime !== false) {
            $attachment_url = add_query_arg('ver', (string) $mtime, $attachment_url);
        }
    }

    $video = [
        'id'  => $attachment_id,
        'url' => $attachment_url,
    ];

    return $video;
}

/**
 * Resuelve la imagen móvil del hero desde Medios sin depender de una ruta fija.
 */
function tmd_home_hero_mobile_image_attachment(): array {
    static $resolved = false;
    static $image = [];

    if ($resolved) {
        return $image;
    }

    $resolved = true;

    $attachment_ids = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image/png',
        'posts_per_page' => 10,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'DESC',
        'meta_query'     => [[
            'key'     => '_wp_attached_file',
            'value'   => 'celu-hero',
            'compare' => 'LIKE',
        ]],
    ]);

    foreach ($attachment_ids as $attachment_id) {
        $attachment_id = (int) $attachment_id;
        $relative_file = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
        $title = strtolower(trim((string) get_the_title($attachment_id)));
        $slug = strtolower(trim((string) get_post_field('post_name', $attachment_id)));
        $basename = strtolower(basename($relative_file));

        if ('celu-hero.png' !== $basename && 'celu-hero' !== $title && 'celu-hero' !== $slug) {
            continue;
        }

        $attachment_url = wp_get_attachment_url($attachment_id);

        if (! $attachment_url) {
            continue;
        }

        $attached_file = get_attached_file($attachment_id);

        if (is_string($attached_file) && $attached_file !== '' && is_file($attached_file)) {
            $mtime = filemtime($attached_file);

            if ($mtime !== false) {
                $attachment_url = add_query_arg('ver', (string) $mtime, $attachment_url);
            }
        }

        $image = [
            'id'  => $attachment_id,
            'url' => $attachment_url,
        ];
        break;
    }

    return $image;
}

/**
 * Kadence conserva la URL y opciones del video en el JSON serializado del bloque.
 * Reescribimos esos atributos antes de do_blocks para que el hero use el adjunto
 * canónico y se reproduzca en bucle en escritorio.
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
 * Inserta la imagen específica de móvil dentro del hero. El video de Kadence
 * permanece disponible para escritorio, pero no se muestra en la vista móvil.
 */
add_filter('render_block', static function (string $block_content, array $block): string {
    static $mobile_image_injected = false;

    if ($mobile_image_injected || ! is_page(47) || ($block['blockName'] ?? '') !== 'kadence/rowlayout') {
        return $block_content;
    }

    $attrs = $block['attrs'] ?? [];
    $videos = $attrs['backgroundVideo'] ?? [];

    if (($attrs['backgroundSettingTab'] ?? '') !== 'video' || empty($videos) || ! is_array($videos)) {
        return $block_content;
    }

    $mobile_image = tmd_home_hero_mobile_image_attachment();

    if (empty($mobile_image['url'])) {
        return $block_content;
    }

    $image_html = sprintf(
        '<img class="tmd-mobile-hero-image" src="%s" alt="" aria-hidden="true" decoding="async" fetchpriority="high">',
        esc_url($mobile_image['url'])
    );

    $updated = preg_replace(
        '/^(\s*<div\b[^>]*>)/i',
        '$1' . $image_html,
        $block_content,
        1
    );

    if (is_string($updated) && $updated !== $block_content) {
        $mobile_image_injected = true;
        return $updated;
    }

    return $block_content;
}, 30, 2);

/**
 * El hero ocupa el viewport. En móvil se usa celu-hero.png; en escritorio se
 * mantiene el video de fondo generado por Kadence.
 */
add_action('wp_head', static function (): void {
    $mobile_image = tmd_home_hero_mobile_image_attachment();
    ?>
    <style id="tmd-home-hero-video-responsive">
        body.page-id-47 .kb-row-layout-id47_9c201d-d2 {
            position: relative !important;
            min-height: 100vh;
            min-height: 100svh;
            overflow: hidden;
        }

        body.page-id-47 .kb-row-layout-id47_9c201d-d2 > .kt-row-column-wrap {
            position: relative;
            z-index: 2;
            min-height: inherit;
        }

        body.page-id-47 .kb-row-layout-id47_9c201d-d2 video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: 50% 50% !important;
        }

        body.page-id-47 .tmd-mobile-hero-image {
            display: none;
        }

        @media (max-width: 767px) {
            body.page-id-47 .kb-row-layout-id47_9c201d-d2 {
                min-height: 100svh;
                background-color: #262e4f;
            }

            body.page-id-47 .kb-row-layout-id47_9c201d-d2 > .kt-row-column-wrap {
                min-height: 100svh;
                align-content: center;
            }

            body.page-id-47 .kb-row-layout-id47_9c201d-d2 > .kt-row-layout-overlay {
                position: absolute;
                z-index: 1;
            }

            <?php if (! empty($mobile_image['url'])) : ?>
            body.page-id-47 .kb-row-layout-id47_9c201d-d2 video {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
            }

            body.page-id-47 .kb-row-layout-id47_9c201d-d2 .tmd-mobile-hero-image {
                display: block !important;
                position: absolute !important;
                inset: 0 !important;
                z-index: 0 !important;
                width: 100% !important;
                height: 100% !important;
                max-width: none !important;
                object-fit: cover !important;
                object-position: 50% 50% !important;
                pointer-events: none;
            }
            <?php endif; ?>
        }
    </style>
    <?php
}, 100);

/**
 * El video se reproduce únicamente en escritorio. En móvil se pausa para que el
 * hero visual dependa de celu-hero.png.
 */
add_action('wp_footer', static function (): void {
    ?>
    <script id="tmd-home-hero-video-runtime">
        (() => {
            const hero = document.querySelector('body.page-id-47 .kb-row-layout-id47_9c201d-d2');
            if (!hero) return;

            const mobile = window.matchMedia('(max-width: 767px)').matches;

            const configureVideo = (video) => {
                if (mobile) {
                    video.pause();
                    video.autoplay = false;
                    video.removeAttribute('autoplay');
                    video.preload = 'none';
                    return;
                }

                video.loop = true;
                video.muted = true;
                video.defaultMuted = true;
                video.autoplay = true;
                video.playsInline = true;
                video.controls = false;
                video.setAttribute('loop', '');
                video.setAttribute('muted', '');
                video.setAttribute('autoplay', '');
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline', '');
                video.setAttribute('aria-hidden', 'true');
                video.load();

                const playback = video.play();
                if (playback && typeof playback.catch === 'function') {
                    playback.catch(() => {});
                }
            };

            hero.querySelectorAll('video').forEach(configureVideo);
        })();
    </script>
    <?php
}, 100);

require get_template_directory() . '/page.php';
