<?php

/** Focused coverage for the DEC-11 related-post shortcode and empty states. */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . DIRECTORY_SEPARATOR);
}

$GLOBALS['tmd_battery_blog_category'] = (object) ['term_id' => 7];
$GLOBALS['tmd_battery_blog_posts'] = [];
$GLOBALS['tmd_battery_blog_query'] = [];

function add_shortcode(string $tag, string $callback): void {}
function add_filter(string $tag, $callback): void {}
function add_action(string $tag, $callback): void {}
function shortcode_atts(array $defaults, array $attributes, string $shortcode = ''): array { return array_merge($defaults, $attributes); }
function sanitize_key(string $value): string { return strtolower(preg_replace('/[^a-z0-9_-]/', '', $value)); }
function get_term_by(string $field, string $value, string $taxonomy) { return $GLOBALS['tmd_battery_blog_category']; }
function is_wp_error($value): bool { return false; }
function get_posts(array $args): array
{
    $GLOBALS['tmd_battery_blog_query'][] = $args;
    return array_values(array_filter(
        $GLOBALS['tmd_battery_blog_posts'],
        static fn ($post): bool => 'publish' === $args['post_status'] && 'publish' === $post->post_status
    ));
}
function remove_accents(string $value): string { return strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']); }
function get_the_title($post): string { return $post->post_title; }
function get_the_excerpt($post): string { return $post->post_excerpt; }
function wp_strip_all_tags(string $value): string { return strip_tags($value); }
function get_the_post_thumbnail_url($post, string $size = '') { return $post->thumbnail; }
function get_permalink($post): string { return $post->permalink; }
function esc_url(string $value): string { return $value; }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function wp_trim_words(string $value, int $count, string $more = ''): string
{
    $words = preg_split('/\s+/', trim($value));
    return count($words) > $count ? implode(' ', array_slice($words, 0, $count)) . $more : $value;
}
function home_url(string $path = ''): string { return 'https://example.test' . $path; }

require dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php';

function tmd_battery_blog_assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
}

$GLOBALS['tmd_battery_blog_category'] = false;
tmd_battery_blog_assert(
    '' === tmd_commercial_landing_related_articles(['topic' => 'baterias', 'fallback' => 'none']),
    'sin categoría temática no se deben inventar ni mostrar artículos'
);
tmd_battery_blog_assert(
    false !== strpos(tmd_commercial_landing_related_articles(['topic' => 'baterias', 'fallback' => 'blog']), '/nosotros/blog/'),
    'el fallback general existente debe seguir disponible para otros consumidores'
);

$GLOBALS['tmd_battery_blog_category'] = (object) ['term_id' => 7];
$GLOBALS['tmd_battery_blog_posts'] = [];
tmd_battery_blog_assert(
    '' === tmd_commercial_landing_related_articles(['topic' => 'baterias', 'fallback' => 'none']),
    'sin artículos relacionados el shortcode DEC-11 debe quedar vacío'
);
$last_query = end($GLOBALS['tmd_battery_blog_query']);
tmd_battery_blog_assert(
    'publish' === $last_query['post_status'],
    'la consulta debe excluir borradores y solicitar solo artículos publicados'
);

$draft = (object) [
    'post_status' => 'draft',
    'post_title' => 'Bateria de traccion en borrador',
    'post_excerpt' => 'Carga y electrolito',
    'post_content' => 'BMS y montacargas',
    'permalink' => '/borrador/',
    'thumbnail' => '',
];
$irrelevant = (object) [
    'post_status' => 'publish',
    'post_title' => 'Consejos de organizacion',
    'post_excerpt' => 'Ideas de oficina',
    'post_content' => 'Buenas practicas administrativas',
    'permalink' => '/organizacion/',
    'thumbnail' => '',
];
$related = (object) [
    'post_status' => 'publish',
    'post_title' => 'Batería de tracción y carga completa',
    'post_excerpt' => 'Cuidado del electrolito y registro BMS para montacargas',
    'post_content' => 'Revisa las conexiones y las horas de carga.',
    'permalink' => '/bateria-traccion-carga/',
    'thumbnail' => 'https://example.test/bateria.webp',
];
$GLOBALS['tmd_battery_blog_posts'] = [$draft, $irrelevant, $related];
$html = tmd_commercial_landing_related_articles(['topic' => 'baterias', 'fallback' => 'none']);
tmd_battery_blog_assert(false !== strpos($html, '/bateria-traccion-carga/'), 'debe mostrarse una publicación relacionada y publicada');
tmd_battery_blog_assert(false !== strpos($html, 'Batería de tracción y carga completa'), 'la tarjeta debe mostrar el título publicado');
tmd_battery_blog_assert(false === strpos($html, '/borrador/') && false === strpos($html, 'Consejos de organizacion'), 'no se deben mostrar borradores ni publicaciones no relacionadas');
tmd_battery_blog_assert(1 === substr_count($html, '<article '), 'la salida debe contener solo la tarjeta real relacionada');

echo "OK: artículos relacionados, solo publicados y estado vacío de DEC-11.\n";
