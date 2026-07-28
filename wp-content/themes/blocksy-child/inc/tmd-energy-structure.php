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
 * Rank Math debe publicar únicamente la URL canónica /energia/bms/.
 */
add_filter('rank_math/sitemap/posts_to_exclude', static function (array $post_ids): array {
    $post_ids[] = 512;
    return array_values(array_unique(array_map('intval', $post_ids)));
});
