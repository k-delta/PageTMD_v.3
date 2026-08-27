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
 * Kadence conserva la URL del video en el JSON serializado del bloque.
 * Reescribimos ese atributo antes de do_blocks (prioridad 9) para que el
 * render use realmente el nuevo adjunto de la librería de medios.
 */
add_filter('the_content', static function (string $content): string {
    $hero_video = tmd_home_hero_video_attachment();

    if (empty($hero_video['url']) || empty($hero_video['id'])) {
        return $content;
    }

    $old_url = 'https://tecnimontacargas.com/wp-content/uploads/2026/07/WhatsApp-Video-2026-07-08-at-16.24.56.mp4';

    if (! str_contains($content, $old_url)) {
        return $content;
    }

    $pattern = '/("local"\s*:\s*")' . preg_quote($old_url, '/') . '("\s*,\s*"localID"\s*:\s*)\d+/';
    $updated = preg_replace_callback(
        $pattern,
        static function (array $matches) use ($hero_video): string {
            return $matches[1]
                . esc_url_raw($hero_video['url'])
                . $matches[2]
                . (int) $hero_video['id'];
        },
        $content,
        1
    );

    return is_string($updated) ? $updated : $content;
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
        $hero_replaced = true;
        break;
    }

    $parsed_block['attrs']['backgroundVideo'] = $videos;

    return $parsed_block;
}, 20);

require get_template_directory() . '/page.php';
