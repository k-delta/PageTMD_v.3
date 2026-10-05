<?php
/**
 * Presentación compartida para las nuevas páginas comerciales.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * No expone enlaces a páginas ausentes o borradores para visitantes públicos.
 */
function tmd_commercial_landing_nav_url(string $slug): string
{
    $page = get_page_by_path(sanitize_title($slug), OBJECT, 'page');
    if (! $page instanceof WP_Post) {
        return '';
    }

    if ($page->post_status === 'publish') {
        return (string) get_permalink($page);
    }

    if (is_user_logged_in() && current_user_can('edit_post', $page->ID)) {
        return (string) get_preview_post_link($page->ID);
    }

    return '';
}

/**
 * Artículos reales del blog, priorizados por relación temática.
 */
function tmd_commercial_landing_related_articles($attributes): string
{
    $attributes = shortcode_atts([
        'topic' => 'montacargas',
        'fallback' => 'blog',
    ], (array) $attributes, 'tmd_commercial_related_articles');

    $topic = sanitize_key((string) $attributes['topic']);
    $fallback = sanitize_key((string) $attributes['fallback']);
    $category = get_term_by('slug', 'consejos-tecnicos', 'category');
    if (! $category || is_wp_error($category)) {
        return 'none' === $fallback ? '' : tmd_commercial_landing_blog_fallback();
    }

    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 18,
        'category' => (int) $category->term_id,
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
    ]);

    $terms = $topic === 'baterias'
        ? ['bateria', 'carga', 'electrolito', 'bms', 'montacargas']
        : ['montacargas', 'bodega', 'mantenimiento', 'operacion', 'bateria'];
    $ranked = [];

    foreach ($posts as $position => $post) {
        $text = strtolower(remove_accents(
            get_the_title($post) . ' ' .
            get_the_excerpt($post) . ' ' .
            wp_strip_all_tags((string) $post->post_content)
        ));
        $score = 0;
        foreach ($terms as $term) {
            if (str_contains($text, $term)) {
                $score++;
            }
        }
        if ($score > 0) {
            $ranked[] = ['post' => $post, 'score' => $score, 'position' => $position];
        }
    }

    if (! $ranked) {
        return 'none' === $fallback ? '' : tmd_commercial_landing_blog_fallback();
    }

    usort($ranked, static function (array $left, array $right): int {
        $by_score = $right['score'] <=> $left['score'];
        return $by_score !== 0 ? $by_score : ($left['position'] <=> $right['position']);
    });

    ob_start();
    echo '<div class="tmd-commercial-landing__article-grid">';
    foreach (array_slice($ranked, 0, 3) as $entry) {
        $post = $entry['post'];
        $image = get_the_post_thumbnail_url($post, 'medium_large');
        echo '<article class="tmd-commercial-landing__article">';
        echo '<a class="tmd-commercial-landing__article-link" href="' . esc_url(get_permalink($post)) . '">';
        if ($image) {
            echo '<img src="' . esc_url($image) . '" alt="" loading="lazy">';
        }
        echo '<span class="tmd-commercial-landing__article-copy">';
        echo '<strong>' . esc_html(get_the_title($post)) . '</strong>';
        echo '<span>' . esc_html(wp_trim_words(get_the_excerpt($post), 24, '…')) . '</span>';
        echo '<span class="tmd-commercial-landing__article-action">Leer artículo <span aria-hidden="true">→</span></span>';
        echo '</span></a></article>';
    }
    echo '</div>';

    return (string) ob_get_clean();
}

function tmd_commercial_landing_blog_fallback(): string
{
    return '<p class="tmd-commercial-landing__blog-fallback">'
        . 'Explora las publicaciones técnicas de Tecnimontacargas. '
        . '<a href="' . esc_url(home_url('/nosotros/blog/')) . '">Ver artículos del blog</a>'
        . '</p>';
}

add_shortcode('tmd_commercial_related_articles', 'tmd_commercial_landing_related_articles');

/**
 * Usa el inventario canónico y muestra hasta cinco referencias eléctricas clasificadas.
 */
function tmd_commercial_landing_electric_inventory_grid(): string
{
    if (! function_exists('tmd_inventory_api_items_by_type')
        || ! function_exists('tmd_inventory_api_classification')
        || ! function_exists('tmd_inventory_api_card')) {
        return '<p class="tmd-commercial-landing__inventory-empty">Consulta con nuestro equipo las referencias eléctricas vigentes.</p>';
    }

    $electric_subcategories = [
        'Tomapedidos de alto nivel',
        'Eléctricos de 3 ruedas',
        'Eléctricos de 4 ruedas',
        'Pantógrafo doble profundidad',
        'Pantógrafo sencillo',
        'Estibadores eléctricos',
        'Apiladores eléctricos',
        'Retráctiles de mástil móvil',
    ];
    $items = tmd_inventory_api_items_by_type('montacargas');
    $electric_items = array_values(array_filter($items, static function ($item) use ($electric_subcategories): bool {
        $classification = tmd_inventory_api_classification($item);
        return in_array($classification['subcategory'] ?? '', $electric_subcategories, true);
    }));
    $electric_items = array_slice($electric_items, 0, 5);
    if (! $electric_items) {
        return '<p class="tmd-commercial-landing__inventory-empty">Consulta con nuestro equipo las referencias eléctricas vigentes y confirma su disponibilidad.</p>';
    }

    ob_start();
    echo '<div class="tmd-api-grid">';
    foreach ($electric_items as $item) {
        tmd_inventory_api_card($item, 'montacargas');
    }
    echo '</div>';
    return (string) ob_get_clean();
}

function tmd_commercial_landing_inventory($attributes): string
{
    $attributes = shortcode_atts([
        'type' => 'equipment',
        'eyebrow' => 'Referencias de inventario',
        'heading' => 'Equipos eléctricos para tu operación',
        'intro' => 'Mostramos hasta cinco referencias clasificadas en Inventario. Su disponibilidad se confirma al cotizar.',
        'link_text' => 'Ver catálogo de equipos',
        'link_url' => '/equipos/',
        'variant' => '',
    ], (array) $attributes, 'tmd_commercial_landing_inventory');
    if ('equipment' !== sanitize_key((string) $attributes['type'])) {
        return '';
    }

    $variant = sanitize_key((string) $attributes['variant']);
    $section_class = 'tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__inventory';
    if ('rental-v2' === $variant) {
        $section_class .= ' tmd-rental-v2-section tmd-rental-v2__inventory';
    }
    $link_url = (string) $attributes['link_url'];
    $link_url = str_starts_with($link_url, '/') ? home_url($link_url) : $link_url;

    ob_start();
    ?>
    <section class="<?php echo esc_attr($section_class); ?>" aria-labelledby="tmd-commercial-inventory-heading">
      <div class="tmd-commercial-landing__container">
        <div class="tmd-commercial-landing__section-heading">
          <span class="tmd-commercial-landing__eyebrow"><?php echo esc_html((string) $attributes['eyebrow']); ?></span>
          <h2 id="tmd-commercial-inventory-heading"><?php echo esc_html((string) $attributes['heading']); ?></h2>
          <p><?php echo esc_html((string) $attributes['intro']); ?></p>
        </div>
        <?php echo tmd_commercial_landing_electric_inventory_grid(); ?>
        <p class="tmd-commercial-landing__inventory-link">
          <a class="tmd-commercial-landing__text-link" href="<?php echo esc_url($link_url); ?>">
            <?php echo esc_html((string) $attributes['link_text']); ?> <span aria-hidden="true">→</span>
          </a>
        </p>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}
add_shortcode('tmd_commercial_landing_inventory', 'tmd_commercial_landing_inventory');

/**
 * Envuelve el formulario Contact Form 7 con el diseño y el ancla de cotización.
 */
function tmd_commercial_landing_form($attributes): string
{
    $attributes = shortcode_atts([
        'id' => 0,
        'type' => 'rental',
    ], (array) $attributes, 'tmd_commercial_landing_form');

    $form_id = absint($attributes['id']);
    $type = sanitize_key((string) $attributes['type']);
    if (! $form_id || ! function_exists('wpcf7_contact_form')) {
        return tmd_commercial_landing_form_unavailable();
    }

    $form = wpcf7_contact_form($form_id);
    if (! $form) {
        return tmd_commercial_landing_form_unavailable();
    }

    $settings = [
        'rental' => [
            'id' => 'formulario-montacargas',
            'eyebrow' => 'Solicita una cotización',
            'title' => 'Cuéntanos qué necesita tu operación',
            'copy' => 'Con los datos de tu bodega y del equipo podemos orientar la cotización para Colombia. El alquiler tiene un mínimo de un mes.',
        ],
        'battery' => [
            'id' => 'formulario-baterias',
            'eyebrow' => 'Cotiza una solución de energía',
            'title' => 'Revisemos la batería de tu montacargas',
            'copy' => 'Comparte los datos de la batería y del equipo. La instalación y el mantenimiento se coordinan en la bodega del cliente.',
        ],
        'battery-maqueta' => [
            'id' => 'formulario-baterias',
            'title' => 'Cotiza baterías para montacargas',
            'subtitle' => 'Indica el voltaje y los amperios hora de la batería actual',
        ],
    ];
    if (! isset($settings[$type])) {
        return tmd_commercial_landing_form_unavailable();
    }

    $setting = $settings[$type];
    $battery_maqueta_class = 'battery-maqueta' === $type ? ' tmd-commercial-landing__form-layout--battery-maqueta' : '';
    $battery_image = get_stylesheet_directory_uri() . '/assets/img/commercial-landings/baterias-maqueta/formulario-etv214.webp';
    ob_start();
    ?>
    <section id="<?php echo esc_attr($setting['id']); ?>" class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft tmd-commercial-landing__form-section" aria-labelledby="<?php echo esc_attr($setting['id']); ?>-heading">
      <div class="tmd-commercial-landing__container tmd-commercial-landing__form-layout<?php echo esc_attr($battery_maqueta_class); ?>">
        <?php if ('battery-maqueta' === $type) : ?>
          <figure class="tmd-commercial-landing__form-image">
            <img src="<?php echo esc_url($battery_image); ?>" alt="Montacargas eléctrico en una bodega" loading="lazy" decoding="async">
          </figure>
          <div class="tmd-commercial-landing__form-copy">
            <h2 id="<?php echo esc_attr($setting['id']); ?>-heading"><?php echo esc_html($setting['title']); ?></h2>
            <h3><?php echo esc_html($setting['subtitle']); ?></h3>
          </div>
        <?php else : ?>
          <div class="tmd-commercial-landing__form-copy">
            <span class="tmd-commercial-landing__eyebrow"><?php echo esc_html($setting['eyebrow']); ?></span>
            <h2 id="<?php echo esc_attr($setting['id']); ?>-heading"><?php echo esc_html($setting['title']); ?></h2>
            <p><?php echo esc_html($setting['copy']); ?></p>
          </div>
        <?php endif; ?>
        <div class="tmd-commercial-landing__form-card">
          <?php echo do_shortcode('[contact-form-7 id="' . $form_id . '"]'); ?>
        </div>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}
add_shortcode('tmd_commercial_landing_form', 'tmd_commercial_landing_form');

function tmd_commercial_landing_form_unavailable(): string
{
    return '<p class="tmd-commercial-landing__form-unavailable">'
        . 'El formulario de cotización no está disponible en este momento. '
        . '<a href="' . esc_url(home_url('/nosotros/contacto/')) . '">Contacta a Tecnimontacargas</a>.'
        . '</p>';
}

/**
 * Encapsula el shortcode editorial en una sección con título visible.
 */
function tmd_commercial_landing_related_section($attributes): string
{
    $raw_attributes = (array) $attributes;
    $attributes = shortcode_atts([
        'topic' => 'montacargas',
        'eyebrow' => 'Contenido relacionado',
        'heading' => 'Artículos para tu operación',
        'subtitle' => '',
        'variant' => '',
        'fallback' => 'blog',
    ], (array) $attributes, 'tmd_commercial_landing_related_section');
    $topic = sanitize_key((string) $attributes['topic']);
    $variant = sanitize_key((string) $attributes['variant']);
    $eyebrow = sanitize_text_field((string) $attributes['eyebrow']);
    $heading = sanitize_text_field((string) $attributes['heading']);
    $subtitle = sanitize_text_field((string) $attributes['subtitle']);
    $fallback = sanitize_key((string) $attributes['fallback']);
    $custom_layout = array_key_exists('heading', $raw_attributes)
        || array_key_exists('subtitle', $raw_attributes)
        || array_key_exists('fallback', $raw_attributes)
        || array_key_exists('eyebrow', $raw_attributes);
    $section_class = 'tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft';
    if ('rental-v2' === $variant) {
        $section_class .= ' tmd-rental-v2-section tmd-rental-v2__blog';
    }
    if ($custom_layout && 'baterias' === $topic) {
        $section_class .= ' tmd-commercial-landing__section--battery-blog';
    }

    ob_start();
    ?>
    <section class="<?php echo esc_attr($section_class); ?>" aria-labelledby="tmd-commercial-related-heading">
      <div class="tmd-commercial-landing__container">
        <div class="tmd-commercial-landing__section-heading<?php echo $custom_layout && 'baterias' === $topic ? ' tmd-commercial-landing__section-heading--split' : ''; ?>">
          <?php if ('' !== $eyebrow) : ?><span class="tmd-commercial-landing__eyebrow"><?php echo esc_html($eyebrow); ?></span><?php endif; ?>
          <h2 id="tmd-commercial-related-heading"><?php echo esc_html($heading); ?></h2>
          <?php if ('' !== $subtitle) : ?>
            <h3><?php echo esc_html($subtitle); ?></h3>
          <?php endif; ?>
        </div>
        <?php echo do_shortcode('[tmd_commercial_related_articles topic="' . esc_attr($topic) . '" fallback="' . esc_attr($fallback) . '"]'); ?>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}
add_shortcode('tmd_commercial_landing_related_section', 'tmd_commercial_landing_related_section');

function tmd_commercial_landing_is_rental_v2(): bool
{
    if (! is_page('alquiler-montacargas-electricos')) {
        return false;
    }

    $page = get_queried_object();
    return $page instanceof WP_Post && str_contains((string) $page->post_content, 'tmd-rental-v2-section');
}

add_filter('blocksy:single:has-default-hero', static function ($has_default_hero): bool {
    if (tmd_commercial_landing_is_rental_v2()) {
        return false;
    }

    return (bool) $has_default_hero;
});

add_filter('body_class', static function (array $classes): array {
    if (is_page('baterias-para-montacargas')) {
        $classes[] = 'tmd-commercial-landing-battery';
    }

    if (tmd_commercial_landing_is_rental_v2()) {
        $classes[] = 'tmd-rental-layout-v2';
    }

    return $classes;
});

add_action('wp_enqueue_scripts', static function (): void {
    if (! is_page(['alquiler-montacargas-electricos', 'baterias-para-montacargas'])) {
        return;
    }

    $css = get_stylesheet_directory() . '/assets/css/tmd-commercial-landings.css';
    $dependencies = ['tm-work-sans'];
    if (is_page('alquiler-montacargas-electricos')) {
        $dependencies[] = 'tmd-inventory-api';
    }

    wp_enqueue_style(
        'tmd-commercial-landings',
        get_stylesheet_directory_uri() . '/assets/css/tmd-commercial-landings.css',
        $dependencies,
        file_exists($css) ? filemtime($css) : '1.0.0'
    );

    if (tmd_commercial_landing_is_rental_v2()) {
        $rental_css = get_stylesheet_directory() . '/assets/css/tmd-commercial-landing-rental-v2.css';
        wp_enqueue_style(
            'tmd-commercial-landing-rental-v2',
            get_stylesheet_directory_uri() . '/assets/css/tmd-commercial-landing-rental-v2.css',
            ['tmd-commercial-landings'],
            file_exists($rental_css) ? filemtime($rental_css) : '1.0.0'
        );
    }
}, 96);
