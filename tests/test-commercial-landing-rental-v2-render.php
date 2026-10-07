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
function render_css_rule_bodies(string $css, string $selector): array {
    $without_comments = preg_replace('~/\*.*?\*/~s', '', $css);
    if (is_string($without_comments)) { $css = $without_comments; }

    $normalize = static function (string $value): string {
        $normalized = preg_replace('/\s+/', ' ', trim($value));
        return is_string($normalized) ? $normalized : trim($value);
    };
    $requested = $normalize($selector);
    $matches = [];
    $rule_start = 0;
    $css_length = strlen($css);

    for ($open_brace = 0; $open_brace < $css_length; $open_brace++) {
        if (';' === $css[$open_brace] || '}' === $css[$open_brace]) {
            $rule_start = $open_brace + 1;
            continue;
        }
        if ('{' !== $css[$open_brace]) { continue; }

        $prelude = trim(substr($css, $rule_start, $open_brace - $rule_start));
        $normalized_prelude = $normalize($prelude);
        $is_match = false;
        if ('' !== $normalized_prelude && '@' === $normalized_prelude[0]) {
            $is_match = $normalized_prelude === $requested;
        } elseif ('' !== $normalized_prelude) {
            foreach (explode(',', $prelude) as $candidate) {
                if ($normalize($candidate) === $requested) {
                    $is_match = true;
                    break;
                }
            }
        }

        if ($is_match) {
            $depth = 1;
            for ($close_brace = $open_brace + 1; $close_brace < $css_length; $close_brace++) {
                if ('{' === $css[$close_brace]) { $depth++; }
                if ('}' === $css[$close_brace]) { $depth--; }
                if (0 === $depth) {
                    $matches[] = substr($css, $open_brace + 1, $close_brace - $open_brace - 1);
                    break;
                }
            }
        }
        $rule_start = $open_brace + 1;
    }

    return $matches;
}
function render_css_rule_body(string $css, string $selector, bool $last = false): string {
    $matches = render_css_rule_bodies($css, $selector);
    if ([] === $matches) { return ''; }
    return $last ? $matches[count($matches) - 1] : $matches[0];
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
render_assert(
    false !== strpos(
        render_css_rule_body(
            '/* body.test .target { color: red; } */ body.test .target-extra { color: red; } body.test .target { color: blue; }',
            'body.test .target'
        ),
        'color: blue;'
    ),
    'La extracción CSS ignora reglas comentadas y exige una coincidencia completa del selector.'
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
$hero_h1_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero h1') : '';
$hero_h1_span_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero h1 span') : '';
$hero_overlay_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero::after') : '';
$hero_eyebrow_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2-section .tmd-rental-v2__eyebrow') : '';
$hero_eyebrow_rule_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2-section .tmd-rental-v2__eyebrow::before') : '';
$metrics_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__metrics') : '';
$hero_metrics_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero .tmd-rental-v2__metrics') : '';
$mobile_css = is_string($rental_css) ? render_css_rule_body($rental_css, '@media (max-width: 720px)') : '';
$description_badge_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__description-badge') : '';
$description_badge_accent_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__description-badge-accent') : '';
$mobile_description_badge_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__description-badge');
$mobile_hero_overlay_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero::after');
$mobile_hero_h1_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero h1');
$mobile_eyebrow_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2-section .tmd-rental-v2__eyebrow');
$mobile_eyebrow_rule_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2-section .tmd-rental-v2__eyebrow::before');
$mobile_hero_metrics_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__hero .tmd-rental-v2__metrics');
$buy_heading_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy h2#tmd-rental-v2-buy-heading') : '';
$buy_heading_accent_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy-heading-accent') : '';
$buy_subtitle_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy .tmd-rental-v2__buy-subtitle') : '';
$buy_image_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy-layout > img') : '';
$process_layout_rules = is_string($rental_css) ? render_css_rule_bodies($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-layout') : [];
$process_layout_css = implode("\n", $process_layout_rules);
$process_copy_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-copy') : '';
$process_image_frame_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-image-frame') : '';
$process_image_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-image-frame > img') : '';
$process_image_accent_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-image-accent') : '';
$process_steps_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process ol') : '';
$process_step_icon_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-icon') : '';
$process_step_icon_svg_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-icon svg') : '';
$process_step_number_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-number::before') : '';
$process_step_heading_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process li > strong') : '';
$process_step_description_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process li > span') : '';
$process_heading_accent_css = is_string($rental_css) ? render_css_rule_body($rental_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-heading-accent') : '';
$tablet_process_css = is_string($rental_css) ? render_css_rule_body($rental_css, '@media (min-width: 761px) and (max-width: 1199px)') : '';
$tablet_process_steps_css = render_css_rule_body($tablet_process_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process ol');
$mobile_process_css = is_string($rental_css) ? render_css_rule_body($rental_css, '@media (max-width: 760px)') : '';
$mobile_process_layout_css = render_css_rule_body($mobile_process_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-layout');
$mobile_process_copy_css = render_css_rule_body($mobile_process_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-copy');
$mobile_process_image_css = render_css_rule_body($mobile_process_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process-image-frame');
$mobile_process_steps_css = render_css_rule_body($mobile_process_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__process ol');
$mobile_buy_heading_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy h2#tmd-rental-v2-buy-heading');
$mobile_buy_image_rules = render_css_rule_bodies($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy-layout > img');
$mobile_buy_image_css = render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__buy-layout > img', true);
$mobile_buy_image_rules_css = implode("\n", $mobile_buy_image_rules);
$mobile_buy_grid_columns = [];
$mobile_buy_grid_rows = [];
preg_match_all('/(?:^|;)\s*grid-column\s*:\s*([^;]+);/i', $mobile_buy_image_rules_css, $mobile_buy_grid_columns);
preg_match_all('/(?:^|;)\s*grid-row\s*:\s*([^;]+);/i', $mobile_buy_image_rules_css, $mobile_buy_grid_rows);
$buy_section_start = strpos($content, '<section class="tmd-rental-v2-section tmd-rental-v2__buy"');
$buy_section_end = false === $buy_section_start ? false : strpos($content, '</section>', $buy_section_start);
$buy_section_html = false === $buy_section_end ? '' : substr($content, $buy_section_start, $buy_section_end - $buy_section_start);
preg_match_all('/<li\b[^>]*>(.*?)<\/li>/s', $buy_section_html, $buy_card_matches);
$process_section_start = strpos($content, '<section class="tmd-rental-v2-section tmd-rental-v2__process"');
$process_section_end = false === $process_section_start ? false : strpos($content, '</section>', $process_section_start);
$process_section_html = false === $process_section_end ? '' : substr($content, $process_section_start, $process_section_end - $process_section_start);
$process_copy_position = strpos($process_section_html, '<div class="tmd-rental-v2__process-copy">');
$process_eyebrow_position = strpos($process_section_html, '<p class="tmd-rental-v2__eyebrow">CÓMO FUNCIONA</p>');
$process_heading_position = strpos($process_section_html, '<h2 id="tmd-rental-v2-process-heading">');
$process_image_position = strpos($process_section_html, 'src="https://example.test/wp-content/themes/blocksy-child/assets/img/commercial-landings-v2/process.webp"');
preg_match_all('/<li\b[^>]*>(.*?)<\/li>/s', $process_section_html, $process_step_matches);
$process_step_texts = array_map(
    static function (string $step): string {
        $text = preg_replace('/\s+/', ' ', trim(strip_tags($step)));
        return is_string($text) ? $text : '';
    },
    $process_step_matches[1] ?? []
);
$process_step_icon_names = [];
$process_step_icons_are_decorative = [];
$process_step_icons_precede_numbers = [];
foreach ($process_step_matches[1] ?? [] as $process_step_match) {
    $process_icon_match = [];
    $has_process_icon = 1 === preg_match(
        '/<span class="tmd-rental-v2__process-icon tmd-rental-v2__process-icon--(operation|recommendation|quote|delivery)" aria-hidden="true"><svg\b(?=[^>]*focusable="false")[^>]*>.*?<\/svg><\/span>/s',
        $process_step_match,
        $process_icon_match
    );
    $process_step_icon_names[] = $has_process_icon ? $process_icon_match[1] : '';
    $process_step_icons_are_decorative[] = $has_process_icon;

    $process_icon_position = strpos($process_step_match, 'class="tmd-rental-v2__process-icon ');
    $process_number_position = strpos($process_step_match, '<span class="tmd-rental-v2__process-number" aria-hidden="true"></span>');
    $process_step_icons_precede_numbers[] = false !== $process_icon_position
        && false !== $process_number_position
        && $process_icon_position < $process_number_position;
}
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
render_assert(
    false !== strpos($content, '<p class="tmd-rental-v2__eyebrow">SOLUCIÓN PARA TU OPERACIÓN</p>')
        && false !== strpos($content, '<h1 id="tmd-rental-v2-heading">Alquiler de <span>montacargas eléctricos</span></h1>')
        && 1 === preg_match(
            '/linear-gradient\(90deg,\s*rgba\(38, 46, 79, \.98\) 0%,\s*rgba\(38, 46, 79, \.96\) 32%,\s*rgba\(38, 46, 79, \.82\) 44%,\s*rgba\(38, 46, 79, \.58\) 56%,\s*rgba\(38, 46, 79, \.2\) 72%,\s*rgba\(38, 46, 79, \.05\) 100%\)/',
            $hero_overlay_css
        )
        && 1 === preg_match(
            '/linear-gradient\(90deg,\s*rgba\(38, 46, 79, \.96\),\s*rgba\(38, 46, 79, \.68\)\)/',
            $mobile_hero_overlay_css
        )
        && false !== strpos($hero_h1_css, 'font-size: clamp(48px, 4.8vw, 66px);')
        && false !== strpos($mobile_hero_h1_css, 'font-size: clamp(40px, 10vw, 46px);')
        && false !== strpos($hero_h1_span_css, 'color: #ffc33c;')
        && false !== strpos($hero_eyebrow_css, 'color: #128ceb;')
        && false !== strpos($hero_eyebrow_rule_css, 'flex: 0 0 64px;')
        && false !== strpos($hero_eyebrow_rule_css, 'height: 3px;')
        && false !== strpos($hero_eyebrow_rule_css, 'background: currentColor;')
        && false !== strpos($hero_eyebrow_rule_css, "content: '';" )
        && false !== strpos($mobile_eyebrow_rule_css, 'flex-basis: 34px;')
        && 1 === preg_match(
            '/body\.tmd-rental-layout-v2 \.tmd-rental-v2__metric-icon svg\s*\{\s*display: block;\s*width: 40px;\s*height: 40px;\s*\}/',
            $rental_css
        ),
    'El hero mantiene el H1 solo de alquiler, el rótulo azul, el acento amarillo y los iconos métricos de 40 px.'
);
render_assert(
    false !== strpos($metrics_css, 'grid-template-columns: repeat(3, minmax(0, 1fr));')
        && false !== strpos($hero_metrics_css, 'width: min(100%, 680px);')
        && false !== strpos($hero_metrics_css, 'margin-left: 0;')
        && false !== strpos($hero_metrics_css, 'margin-right: auto;')
        && false !== strpos($mobile_hero_metrics_css, 'grid-template-columns: minmax(0, 1fr);'),
    'Las métricas del hero se alinean a la izquierda en tres columnas de máximo 680 px y se apilan en móvil.'
);
render_assert(
    false !== strpos($content, '<div class="tmd-rental-v2__description-badge">')
        && false !== strpos($content, '<span class="tmd-rental-v2__description-badge-accent" aria-hidden="true">')
        && false !== strpos($content, 'class="tmd-rental-v2__description-badge-icon"')
        && false !== strpos($content, '<span>FLOTA DISPONIBLE</span><strong>Equipos listos para tu operación</strong>')
        && false !== strpos($description_badge_css, 'position: absolute;')
        && false !== strpos($description_badge_css, 'width: min(420px, calc(100% - 32px));')
        && false !== strpos($description_badge_css, 'background: #fff;')
        && false !== strpos($description_badge_accent_css, 'background: #128ceb;')
        && false !== strpos($mobile_description_badge_css, 'right: 12px;')
        && false !== strpos($mobile_description_badge_css, 'bottom: 12px;')
        && false !== strpos($mobile_description_badge_css, 'width: calc(100% - 24px);')
        && false !== strpos($mobile_description_badge_css, 'grid-template-columns: 3px 44px minmax(0, 1fr);'),
    'La tarjeta inferior de flota conserva icono, textos, acento azul y ancho adaptable en móvil.'
);
render_assert(
    false !== strpos(
        $buy_section_html,
        '<h2 id="tmd-rental-v2-buy-heading">Alquiler mensual frente a <span class="tmd-rental-v2__buy-heading-accent">compra de maquinaria</span></h2>'
    )
        && 1 === substr_count($content, 'compra de maquinaria')
        && false !== strpos(
            $buy_section_html,
            '<h3 class="tmd-rental-v2__buy-subtitle">Capacidad adicional en tu bodega sin sumar un activo a tu balance</h3>'
        )
        && false !== strpos($buy_section_html, 'src="https://example.test/wp-content/themes/blocksy-child/assets/img/commercial-landings-v2/rental-vs-buy.webp"')
        && 3 === count($buy_card_matches[1])
        && false !== strpos($buy_card_matches[1][0], '<strong>Capital disponible:</strong>')
        && false !== strpos($buy_card_matches[1][0], 'la tarifa mensual reemplaza la inversión en un equipo nuevo o usado.')
        && false !== strpos($buy_card_matches[1][1], '<strong>Tarifa por periodo:</strong>')
        && false !== strpos($buy_card_matches[1][1], 'la cotización fija el valor del equipo según el tiempo de alquiler.')
        && false !== strpos($buy_card_matches[1][2], '<strong>Sin reventa ni bodegaje:</strong>')
        && false !== strpos($buy_card_matches[1][2], 'al terminar, el equipo vuelve a nuestra flota y no ocupa espacio en tu bodega.'),
    'El bloque 04 conserva el texto completo, destaca solo la frase solicitada y mantiene foto y tarjetas.'
);
render_assert(
    false !== strpos($buy_heading_css, 'font-size: clamp(38px, 4vw, 50px);')
        && false !== strpos($buy_heading_accent_css, 'color: #ffc33c;')
        && false !== strpos($buy_subtitle_css, 'color: #e6e6e6;')
        && false !== strpos($mobile_buy_heading_css, 'font-size: clamp(30px, 7vw, 36px);')
        && false !== strpos($buy_image_css, 'grid-column: 2;')
        && false !== strpos($buy_image_css, 'grid-row: 1;')
        && false !== strpos($buy_image_css, 'aspect-ratio: 4 / 3;')
        && false !== strpos($buy_image_css, 'object-fit: cover;')
        && false !== strpos($buy_image_css, 'clip-path: polygon(26% 0, 100% 0, 100% 100%, 26% 100%, 0 83%, 0 34%);')
        && false === strpos($buy_image_css, 'border-radius:')
        && [] !== $mobile_buy_image_rules
        && ['1'] === array_values(array_unique(array_map('trim', $mobile_buy_grid_columns[1] ?? [])))
        && ['auto'] === array_values(array_unique(array_map('trim', $mobile_buy_grid_rows[1] ?? [])))
        && false !== strpos($mobile_buy_image_css, 'min-height: 0;')
        && 0 === preg_match('/(?:^|;)\s*(?:aspect-ratio|object-fit|border-radius)\s*:/i', $mobile_buy_image_rules_css)
        && false !== strpos($mobile_buy_image_rules_css, 'clip-path: polygon(26% 0, 100% 0, 100% 100%, 26% 100%, 0 83%, 0 34%);')
        && false !== strpos(
            render_css_rule_body($mobile_css, 'body.tmd-rental-layout-v2 .tmd-rental-v2__support-layout > img', true),
            'border-radius: 10px;'
        ),
    'El bloque 04 mantiene la imagen a la derecha y el recorte poligonal con proporción intacta en escritorio y móvil.'
);
render_assert(
    false !== $process_eyebrow_position
        && false !== $process_heading_position
        && $process_eyebrow_position < $process_heading_position
        && false !== strpos($process_section_html, '<p class="tmd-rental-v2__eyebrow">CÓMO FUNCIONA</p>')
        && false !== strpos(
            $process_section_html,
            '<h2 id="tmd-rental-v2-process-heading">Alquiler de montacargas en <span class="tmd-rental-v2__process-heading-accent">cuatro pasos</span></h2>'
        )
        && false !== strpos($process_section_html, '<h3>La recomendación técnica del equipo llega antes que la tarifa del alquiler</h3>')
        && 4 === count($process_step_texts)
        && [
            'Datos de tu operación: peso de la carga, altura de las estanterías, ancho de pasillo y ciudad.',
            'Recomendación técnica: un asesor propone el tipo de equipo y la capacidad que requiere tu bodega.',
            'Cotización: recibes la tarifa, el periodo de alquiler y las condiciones de cada equipo.',
            'Entrega en tu sede: coordinamos el traslado del equipo hasta tu bodega en la fecha acordada.',
        ] === $process_step_texts
        && ['operation', 'recommendation', 'quote', 'delivery'] === $process_step_icon_names
        && 4 === count(array_filter($process_step_icons_are_decorative))
        && 4 === count(array_filter($process_step_icons_precede_numbers))
        && false !== strpos($process_section_html, 'src="https://example.test/wp-content/themes/blocksy-child/assets/img/commercial-landings-v2/process.webp"')
        && false !== strpos($process_section_html, 'alt="Montacargas reach operando dentro de una bodega"')
        && false !== strpos($process_section_html, '<div class="tmd-rental-v2__process-image-frame"><img')
        && false !== strpos($process_section_html, '<svg class="tmd-rental-v2__process-image-accent"')
        && false !== strpos($process_section_html, 'aria-hidden="true" focusable="false"><path d="M70 0 L100 50"')
        && false !== strpos($process_section_html, 'stroke="#e9272e"')
        && false !== strpos($process_section_html, 'stroke-width="7"')
        && false !== $process_copy_position
        && false !== $process_image_position
        && $process_copy_position < $process_image_position,
    'El bloque 06 conserva rótulo, acento, textos y foto, y muestra los cuatro SVG decorativos antes de sus números.'
);
render_assert(
    false !== strpos($hero_eyebrow_css, 'color: #128ceb;')
        && false !== strpos($hero_eyebrow_css, 'font-size: 19px;')
        && false !== strpos($hero_eyebrow_rule_css, 'background: currentColor;')
        && false !== strpos($mobile_eyebrow_css, 'font-size: 19px;')
        && false !== strpos($process_heading_accent_css, 'color: #ffc33c;')
        && false !== strpos($process_heading_accent_css, 'background-color: #262e4f;')
        && false !== strpos($process_heading_accent_css, 'white-space: nowrap;')
        && false !== strpos($process_layout_css, 'grid-template-columns: minmax(0, .82fr) minmax(0, 1.18fr);')
        && false !== strpos($process_copy_css, 'grid-column: 2;')
        && false !== strpos($process_copy_css, 'grid-row: 1;')
        && false !== strpos($process_image_frame_css, 'grid-column: 1;')
        && false !== strpos($process_image_frame_css, 'grid-row: 1;')
        && false !== strpos($process_image_frame_css, 'aspect-ratio: 4 / 3;')
        && false !== strpos($process_image_css, 'object-fit: cover;')
        && false !== strpos($process_image_css, 'clip-path: polygon(0 0, 70% 0, 100% 50%, 70% 100%, 0 100%);')
        && false !== strpos($process_image_accent_css, 'position: absolute;')
        && false !== strpos($process_image_accent_css, 'pointer-events: none;')
        && false !== strpos($process_steps_css, 'grid-template-columns: repeat(4, minmax(0, 1fr));')
        && false !== strpos($process_step_icon_css, 'width: 36px;')
        && false !== strpos($process_step_icon_css, 'height: 36px;')
        && false !== strpos($process_step_icon_css, 'background: #262e4f;')
        && false !== strpos($process_step_icon_css, 'color: #ffc33c;')
        && false !== strpos($process_step_icon_svg_css, 'width: 100%;')
        && false !== strpos($process_step_icon_svg_css, 'height: 100%;')
        && false !== strpos($process_step_number_css, 'counter(rental-step, decimal-leading-zero)')
        && false !== strpos($process_step_number_css, 'background: #262e4f;')
        && false !== strpos($process_step_number_css, 'color: #ffc33c;')
        && false !== strpos($process_step_number_css, 'display: inline-block;')
        && false !== strpos($process_step_heading_css, 'display: block;')
        && false !== strpos($process_step_description_css, 'display: block;')
        && false !== strpos($tablet_process_steps_css, 'grid-template-columns: repeat(2, minmax(0, 1fr));')
        && false !== strpos($mobile_process_layout_css, 'grid-template-columns: minmax(0, 1fr);')
        && false !== strpos($mobile_process_copy_css, 'grid-column: 1;')
        && false !== strpos($mobile_process_copy_css, 'grid-row: auto;')
        && false !== strpos($mobile_process_image_css, 'grid-column: 1;')
        && false !== strpos($mobile_process_image_css, 'grid-row: auto;')
        && false !== strpos($mobile_process_steps_css, 'grid-template-columns: minmax(0, 1fr);'),
    'El bloque 06 conserva imagen a la izquierda en escritorio, pasos en 4/2/1 columnas por breakpoint y orden textual en móvil.'
);
fwrite(STDOUT, "OK: DOM rental-v2 con doce secciones, shortcodes reales y máximo de inventario.\n");
