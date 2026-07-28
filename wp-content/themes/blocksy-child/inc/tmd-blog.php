<?php
/**
 * Blog editorial de Tecni Montacargas.
 */

if (! defined('ABSPATH')) {
    exit;
}

function tmd_blog_url() {
    return home_url('/nosotros/blog/');
}

function tmd_blog_category_map() {
    return [
        'noticias' => 'Noticias',
        'eventos' => 'Eventos',
        'consejos-tecnicos' => 'Consejos técnicos',
        'casos-de-exito' => 'Casos de éxito',
        'novedades-del-sector' => 'Novedades del sector',
        'lanzamientos' => 'Lanzamientos',
    ];
}

function tmd_blog_post_categories($post_id) {
    $terms = get_the_category($post_id);
    if (! $terms) {
        return ['slugs' => ['sin-categoria'], 'name' => 'Novedades'];
    }

    return [
        'slugs' => array_values(array_map(static fn($term) => $term->slug, $terms)),
        'name' => $terms[0]->name,
    ];
}

function tmd_blog_image($post_id, $size = 'large') {
    $image = get_the_post_thumbnail_url($post_id, $size);
    return $image ?: '';
}

function tmd_blog_excerpt($post_id, $words = 22) {
    $excerpt = get_the_excerpt($post_id);
    if (! $excerpt) {
        $excerpt = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
    }
    return wp_trim_words($excerpt, $words, '…');
}

function tmd_blog_reading_time($post_id) {
    $words = str_word_count(wp_strip_all_tags((string) get_post_field('post_content', $post_id)));
    return max(1, (int) ceil($words / 220));
}

add_action('wp_enqueue_scripts', function () {
    if (! (is_page('blog') || is_singular('post') || is_front_page())) {
        return;
    }

    $css = get_stylesheet_directory() . '/assets/css/tmd-blog.css';
    $js = get_stylesheet_directory() . '/assets/js/tmd-blog.js';
    wp_enqueue_style('tmd-blog', get_stylesheet_directory_uri() . '/assets/css/tmd-blog.css', [], file_exists($css) ? filemtime($css) : '1.0.0');
    wp_enqueue_script('tmd-blog', get_stylesheet_directory_uri() . '/assets/js/tmd-blog.js', [], file_exists($js) ? filemtime($js) : '1.0.0', true);
}, 80);

add_action('init', function () {
    add_rewrite_rule('^nosotros/blog/([^/]+)/?$', 'index.php?name=$matches[1]', 'top');
}, 2);

add_filter('post_link', function ($url, $post) {
    if ($post->post_status === 'auto-draft') {
        return $url;
    }
    return home_url('/nosotros/blog/' . $post->post_name . '/');
}, 10, 2);

add_filter('rank_math/frontend/canonical', function ($canonical) {
    return is_singular('post') ? get_permalink(get_queried_object_id()) : $canonical;
});

add_filter('rank_math/opengraph/url', function ($url) {
    return is_singular('post') ? get_permalink(get_queried_object_id()) : $url;
});

add_action('acf/init', function () {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_tmd_blog_editorial',
        'title' => 'Configuración editorial del blog',
        'fields' => [
            [
                'key' => 'field_tmd_blog_featured',
                'label' => 'Destacar en blog',
                'name' => 'tmd_blog_featured',
                'type' => 'true_false',
                'instructions' => 'Muestra este artículo en el hero principal.',
                'ui' => 1,
            ],
            [
                'key' => 'field_tmd_blog_secondary',
                'label' => 'Usar en tarjetas principales',
                'name' => 'tmd_blog_secondary',
                'type' => 'true_false',
                'instructions' => 'Prioriza este artículo en una de las dos tarjetas grandes.',
                'ui' => 1,
            ],
        ],
        'location' => [[[
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'post',
        ]]],
        'position' => 'side',
        'style' => 'default',
    ]);
});

function tmd_blog_featured_post($posts) {
    foreach ($posts as $post) {
        if ((bool) get_post_meta($post->ID, 'tmd_blog_featured', true)) {
            return $post;
        }
    }
    return $posts[0] ?? null;
}

function tmd_blog_secondary_posts($posts, $featured_id, $limit = 2) {
    $selected = [];
    foreach ($posts as $post) {
        if ($post->ID !== $featured_id && get_post_meta($post->ID, 'tmd_blog_secondary', true)) {
            $selected[$post->ID] = $post;
        }
    }
    foreach ($posts as $post) {
        if ($post->ID !== $featured_id && count($selected) < $limit) {
            $selected[$post->ID] = $post;
        }
    }
    return array_slice(array_values($selected), 0, $limit);
}

function tmd_render_blog_card($post, $class = 'tmd-blog-card') {
    $cats = tmd_blog_post_categories($post->ID);
    $image = tmd_blog_image($post->ID, 'medium_large');
    $style = $image ? ' style="background-image:url(' . esc_url($image) . ')"' : '';
    ?>
    <article class="<?php echo esc_attr($class); ?>" data-blog-item data-categories="<?php echo esc_attr(implode(' ', $cats['slugs'])); ?>"<?php echo $style; ?>>
        <a href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="Leer <?php echo esc_attr(get_the_title($post)); ?>">
            <span class="tmd-blog-overlay"></span>
            <span class="tmd-blog-card-content">
                <span class="tmd-blog-category tmd-blog-category--light"><?php echo esc_html($cats['name']); ?></span>
                <span class="tmd-blog-rule"></span>
                <strong><?php echo esc_html(get_the_title($post)); ?></strong>
                <span class="tmd-blog-card-excerpt"><?php echo esc_html(tmd_blog_excerpt($post->ID, 18)); ?></span>
                <span class="tmd-blog-outline-btn">Ver más</span>
            </span>
        </a>
    </article>
    <?php
}

function tmd_render_blog_mini_card($post, $class = 'tmd-blog-mini-card') {
    $cats = tmd_blog_post_categories($post->ID);
    $image = tmd_blog_image($post->ID, 'medium_large');
    ?>
    <article class="<?php echo esc_attr($class); ?>" data-blog-item data-categories="<?php echo esc_attr(implode(' ', $cats['slugs'])); ?>">
        <a href="<?php echo esc_url(get_permalink($post)); ?>">
            <?php if ($image) : ?>
                <span class="tmd-blog-mini-image"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title($post)); ?>" loading="lazy"></span>
            <?php else : ?>
                <span class="tmd-blog-mini-image tmd-blog-image-placeholder"></span>
            <?php endif; ?>
            <span class="tmd-blog-category"><?php echo esc_html($cats['name']); ?></span>
            <strong><?php echo esc_html(get_the_title($post)); ?></strong>
        </a>
    </article>
    <?php
}

function tmd_render_home_blog_preview() {
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 3,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    if (! $posts) {
        return '';
    }

    ob_start();
    ?>
    <section class="tmd-home-blog" aria-labelledby="tmd-home-blog-title">
        <div class="tmd-blog-container">
            <span class="tmd-blog-eyebrow">Novedades</span>
            <h2 id="tmd-home-blog-title">Últimos artículos</h2>
            <div class="tmd-blog-mini-grid">
                <?php foreach ($posts as $post) { tmd_render_blog_mini_card($post); } ?>
            </div>
            <p class="tmd-blog-center"><a class="tmd-blog-all-btn" href="<?php echo esc_url(tmd_blog_url()); ?>">Ver todos los artículos <span aria-hidden="true">→</span></a></p>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

add_filter('the_content', function ($content) {
    if (! is_front_page() || ! is_main_query() || ! in_the_loop()) {
        return $content;
    }
    $preview = tmd_render_home_blog_preview();
    if (! $preview) {
        return $content;
    }

    $marker = '<div class="wp-block-group tmd-container-block tmd-final-cta">';
    if (strpos($content, $marker) !== false) {
        return str_replace($marker, $preview . $marker, $content);
    }
    return $content . $preview;
}, 20);

add_filter('document_title_parts', function ($parts) {
    if (is_page('blog')) {
        $parts['title'] = 'Blog y novedades';
    }
    return $parts;
});
