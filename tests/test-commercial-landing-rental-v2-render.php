<?php
define('ABSPATH', sys_get_temp_dir() . '/tmd-rental-render-test/');
define('WP_CLI', true);
define('OBJECT', 'OBJECT');

function add_shortcode($tag, $callback) { $GLOBALS['shortcodes'][$tag] = $callback; }
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) { $GLOBALS['filters'][$tag][] = $callback; }
function add_action($tag, $callback, $priority = 10, $accepted_args = 1) { $GLOBALS['actions'][$tag][] = $callback; }
function shortcode_atts($defaults, $attributes, $shortcode = '') { return array_merge($defaults, (array) $attributes); }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_url($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_url_raw($value) { return (string) $value; }
function absint($value) { return abs((int) $value); }
function home_url($path = '') { return 'https://example.test' . $path; }
function untrailingslashit($value) { return rtrim($value, '/'); }
function is_wp_error($value) { return $value instanceof WP_Error; }
class WP_Error {}
class WP_Post { public $post_content = ''; }
function is_page($slug = '') {
    $current_slug = $GLOBALS['test_current_page_slug'] ?? '';
    return is_array($slug) ? in_array($current_slug, $slug, true) : $current_slug === $slug;
}
function get_queried_object() { return $GLOBALS['test_queried_page'] ?? null; }
function tmd_inventory_api_items_by_type($type) {
    return array_map(static fn ($number) => ['id' => 'equipment-' . $number, 'subcategory' => 'Eléctricos de 3 ruedas'], range(1, 7));
}
function tmd_inventory_api_classification($item) { return ['subcategory' => $item['subcategory']]; }
function tmd_inventory_api_card($item, $type) { echo '<article data-equipment="' . esc_attr($item['id']) . '">' . esc_html($item['id']) . '</article>'; }
function get_term_by($field, $slug, $taxonomy) { return (object) ['term_id' => 9]; }
function get_posts($args) { return $GLOBALS['published_posts']; }
function get_the_title($post) { return $post->post_title; }
function get_the_excerpt($post) { return $post->post_excerpt; }
function wp_strip_all_tags($value) { return strip_tags((string) $value); }
function remove_accents($value) { return strtolower((string) $value); }
function get_permalink($post) { return $post->url; }
function get_the_post_thumbnail_url($post, $size) { return $post->thumbnail; }
function wp_trim_words($value, $count, $more = '') { return $value; }
function do_shortcode($shortcode) {
    if (false !== strpos($shortcode, 'tmd_commercial_related_articles')) {
        return tmd_commercial_landing_related_articles(['topic' => 'montacargas', 'fallback' => 'none']);
    }
    if (false !== strpos($shortcode, 'contact-form-7')) { return '<form data-form-id="1556"></form>'; }
    return '';
}

require_once dirname(__DIR__) . '/scripts/commercial-landing-rental-v2.php';
require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php';

function render_assert($condition, $message) {
    if (! $condition) { fwrite(STDERR, 'FAIL: ' . $message . "\n"); exit(1); }
}
$GLOBALS['published_posts'] = [
    (object) ['post_title' => 'Mantenimiento de montacargas', 'post_excerpt' => 'Bodega y operación', 'post_content' => 'Mantenimiento preventivo', 'url' => '/blog/mantenimiento', 'thumbnail' => '/uploads/mantenimiento.webp'],
    (object) ['post_title' => 'Baterías de tracción en bodega', 'post_excerpt' => 'Carga y baterías', 'post_content' => 'Operación eléctrica', 'url' => '/blog/baterias', 'thumbnail' => '/uploads/baterias.webp'],
    (object) ['post_title' => 'Historia de la empresa', 'post_excerpt' => 'Conoce Tecnimontacargas', 'post_content' => 'Historia corporativa', 'url' => '/blog/historia', 'thumbnail' => ''],
    (object) ['post_title' => 'Montacargas para centros de distribución', 'post_excerpt' => 'Equipo y bodega', 'post_content' => 'Mantenimiento de equipos', 'url' => '/blog/centros', 'thumbnail' => '/uploads/centros.webp'],
];
$content = tmd_commercial_landing_script_rental_v2_content(
    1556,
    'https://example.test/wp-content/themes/blocksy-child/assets/img'
);
$inventory_html = tmd_commercial_landing_inventory([
    'type' => 'equipment', 'variant' => 'rental-v2', 'eyebrow' => 'Inventario real',
    'heading' => 'Contrabalanceados, reach y apiladores en inventario',
    'intro' => 'Compara referencias', 'link_text' => 'Ver todos los equipos', 'link_url' => '/equipos/',
]);
$blog_html = tmd_commercial_landing_related_section([
    'topic' => 'montacargas', 'variant' => 'rental-v2', 'fallback' => 'none',
    'eyebrow' => 'Blog', 'heading' => 'Guías técnicas', 'subtitle' => 'Mantenimiento preventivo',
]);
$content = str_replace(
    '[tmd_commercial_landing_inventory type="equipment" variant="rental-v2" eyebrow="Inventario real" heading="Contrabalanceados, reach y apiladores en inventario" intro="Compara capacidad y altura de levante antes de pedir la cotización" link_text="Ver todos los equipos" link_url="/equipos/"]',
    $inventory_html,
    $content
);
$content = str_replace(
    '[tmd_commercial_landing_related_section topic="montacargas" variant="rental-v2" fallback="none" eyebrow="Blog" heading="Guías técnicas de mantenimiento y baterías de tracción" subtitle="Vida útil de la batería de tracción y señales de mantenimiento preventivo"]',
    $blog_html,
    $content
);
$content = str_replace('[contact-form-7 id="1556"]', '<form data-form-id="1556"></form>', $content);

render_assert(12 === preg_match_all('/<section\b/i', $content), 'El DOM renderizado debe tener doce secciones en orden.');
render_assert(1 === preg_match_all('/<h1\b/i', $content), 'El DOM renderizado debe tener un H1.');
$blocksy_hero_filter = $GLOBALS['filters']['blocksy:single:has-default-hero'][0] ?? null;
render_assert(is_callable($blocksy_hero_filter), 'La landing debe registrar el filtro para suprimir el hero automático de Blocksy.');
$GLOBALS['test_current_page_slug'] = 'alquiler-montacargas-electricos';
$GLOBALS['test_queried_page'] = new WP_Post();
$GLOBALS['test_queried_page']->post_content = 'tmd-rental-v2-section';
$theme_hero = $blocksy_hero_filter(true) ? '<h1 class="page-title">Título de WordPress</h1>' : '';
render_assert(
    1 === preg_match_all('/<h1\b/i', $theme_hero . $content),
    'La página completa debe conservar solo el H1 del hero de la maqueta.'
);
render_assert(false === $blocksy_hero_filter(false), 'La landing v2 mantiene desactivado el hero automático de Blocksy.');
$GLOBALS['test_queried_page']->post_content = 'contenido anterior';
render_assert(true === $blocksy_hero_filter(true), 'Las páginas de alquiler sin el marcador v2 conservan el hero de Blocksy.');
render_assert(false === $blocksy_hero_filter(false), 'Las páginas de alquiler sin el marcador v2 conservan el valor false recibido.');
$GLOBALS['test_current_page_slug'] = 'baterias-para-montacargas';
$GLOBALS['test_queried_page']->post_content = 'tmd-rental-v2-section';
render_assert(true === $blocksy_hero_filter(true), 'La página de baterías conserva su comportamiento de hero.');
render_assert(false === $blocksy_hero_filter(false), 'La página de baterías conserva el valor false recibido.');
render_assert(6 === preg_match_all('/<details\b/i', $content), 'El DOM renderizado debe tener las seis FAQ.');
render_assert(
    5 === preg_match_all('/<article data-equipment=/', $inventory_html)
        && false !== strpos($inventory_html, 'tmd-rental-v2__inventory')
        && false !== strpos($inventory_html, 'href="https://example.test/equipos/"')
        && false === strpos($inventory_html, 'equipment-6'),
    'Inventario debe renderizar como máximo cinco referencias reales y el link al catálogo.'
);
render_assert(
    false !== strpos($blog_html, 'tmd-rental-v2__blog')
        && false !== strpos($blog_html, 'Mantenimiento de montacargas')
        && false !== strpos($blog_html, 'Baterías de tracción en bodega')
        && false !== strpos($blog_html, 'Montacargas para centros de distribución')
        && false === strpos($blog_html, 'Historia de la empresa'),
    'Blog debe envolver hasta tres publicaciones reales priorizadas y omitir una entrada irrelevante.'
);
$rental_css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css');
render_assert(
    is_string($rental_css)
        && false === strpos($rental_css, 'body.tmd-rental-layout-v2 #header')
        && false === strpos($rental_css, 'body.tmd-rental-layout-v2 .tmd-mm-header')
        && false === strpos($rental_css, 'body.tmd-rental-layout-v2 .tmd-site-footer')
        && 1 === preg_match(
            '/body\.tmd-rental-layout-v2 \.entry-header,\s*body\.tmd-rental-layout-v2 \.tmd-contact-rail\s*\{\s*display: none !important;\s*\}/',
            $rental_css
        )
        && false !== strpos($rental_css, 'body.tmd-rental-layout-v2 .ct-container-full,')
        && false !== strpos($rental_css, 'padding: 0 !important;'),
    'El layout v2 conserva visibles navegación/footer, oculta el título duplicado y quita el espaciado superior global.'
);
fwrite(STDOUT, "OK: DOM rental-v2 con doce secciones, shortcodes reales y máximo de inventario.\n");
