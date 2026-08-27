<?php
/**
 * Template individual para Baterías y Cargadores TMD.
 */

if (! defined('ABSPATH')) {
    exit;
}

$css_path = get_stylesheet_directory() . '/assets/css/tmd-energy-single.css';

wp_enqueue_style(
    'tmd-energy-single',
    get_stylesheet_directory_uri() . '/assets/css/tmd-energy-single.css',
    [],
    file_exists($css_path) ? filemtime($css_path) : '1.0.0'
);

get_header();

$post_id = get_the_ID();

$tmes_meta = function ($key, $fallback = '') use ($post_id) {
    $value = get_post_meta($post_id, $key, true);
    $value = is_scalar($value) ? trim((string) $value) : '';

    return $value !== '' ? $value : $fallback;
};

$tmes_terms = function ($taxonomy) use ($post_id) {
    $terms = get_the_terms($post_id, $taxonomy);

    if (empty($terms) || is_wp_error($terms)) {
        return [];
    }

    return $terms;
};

$tmes_term_names = function ($taxonomy) use ($tmes_terms) {
    $terms = $tmes_terms($taxonomy);

    if (empty($terms)) {
        return '';
    }

    return implode(', ', wp_list_pluck($terms, 'name'));
};

$tmes_first_term = function ($taxonomy, $field = 'name') use ($tmes_terms) {
    $terms = $tmes_terms($taxonomy);

    if (empty($terms)) {
        return '';
    }

    return isset($terms[0]->{$field}) ? (string) $terms[0]->{$field} : '';
};

$title = get_the_title($post_id);

$category = $tmes_meta('tmd_categoria') ?: $tmes_first_term('tmd_categoria_energia');
$brand = $tmes_meta('tmd_marca') ?: $tmes_first_term('tmd_marca_cargador');
$technology = $tmes_meta('tmd_tecnologia') ?: $tmes_first_term('tmd_tipo_bateria');
$voltage = $tmes_meta('tmd_voltaje') ?: $tmes_term_names('tmd_voltaje');
$amperage = $tmes_meta('tmd_amperaje');
$capacity_ah = $tmes_meta('tmd_capacidad_ah');
$condition = $tmes_meta('tmd_condicion');
$compatibility = $tmes_term_names('tmd_compatibilidad');
$battery_type = $tmes_term_names('tmd_tipo_bateria');
$charger_brand = $tmes_term_names('tmd_marca_cargador');

$price = $tmes_meta('tmd_precio');
$show_price = strtolower($tmes_meta('tmd_mostrar_precio'));
$price_text = ($price !== '' && ! in_array($show_price, ['0', 'false', 'no'], true))
    ? $price
    : 'Precio a consultar';

$image_url = '';

if (has_post_thumbnail($post_id)) {
    $image_url = get_the_post_thumbnail_url($post_id, 'large');
}

if (! $image_url) {
    $image_url = $tmes_meta('tmd_imagen_url');
}

$excerpt = get_the_excerpt($post_id);

if (! $excerpt) {
    $excerpt = 'Solución de energía industrial disponible para validación técnica, compatibilidad y cotización.';
}

$category_slug = $tmes_first_term('tmd_categoria_energia', 'slug');
$back_url = home_url('/energia/');

if ($category_slug) {
    $back_url = home_url('/energia/?tmde_categoria%5B%5D=' . rawurlencode($category_slug));
}

$contact_page = get_page_by_path('nosotros/contacto', OBJECT, 'page');
$contact_base_url = $contact_page ? get_permalink($contact_page->ID) : home_url('/nosotros/contacto/');
$contact_url = tmd_conversion_quote_url('bateria', (string) $post_id, $title, $contact_base_url);
$whatsapp_url = tmd_conversion_whatsapp_url('Hola, quiero cotizar esta solución de energía: ' . $title);

$highlight_items = [
    'Categoría'  => $category,
    'Voltaje'    => $voltage,
    'Tecnología' => $technology,
    'Condición'  => $condition,
];

$general_rows = [
    'Categoría'          => $category,
    'Marca'              => $brand,
    'Tipo de batería'    => $battery_type,
    'Marca de cargador'  => $charger_brand,
    'Tecnología'         => $technology,
    'Condición'          => $condition,
];

$technical_rows = [
    'Voltaje'            => $voltage,
    'Amperaje'           => $amperage,
    'Capacidad Ah'       => $capacity_ah,
    'Compatibilidad'     => $compatibility,
    'Precio'             => $price_text,
];

$category_terms = $tmes_terms('tmd_categoria_energia');

$related_args = [
    'post_type'      => 'tmd_energia',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'post__not_in'   => [$post_id],
];

if (! empty($category_terms)) {
    $related_args['tax_query'] = [
        [
            'taxonomy' => 'tmd_categoria_energia',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck($category_terms, 'term_id'),
        ],
    ];
}

$related = new WP_Query($related_args);

if (! $related->have_posts() && isset($related_args['tax_query'])) {
    wp_reset_postdata();

    unset($related_args['tax_query']);
    $related = new WP_Query($related_args);
}
?>

<main class="tmes-shell">
    <section class="tmes-hero">
        <div class="tmes-container">
            <div class="tmes-breadcrumb">
                <a href="<?php echo esc_url(home_url('/energia/')); ?>">Energía</a>
                <?php if ($category) : ?>
                    <span>/</span>
                    <a href="<?php echo esc_url($back_url); ?>"><?php echo esc_html($category); ?></a>
                <?php endif; ?>
            </div>

            <div class="tmes-hero-grid">
                <div class="tmes-gallery">
                    <div class="tmes-main-image">
                        <?php if ($image_url) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                        <?php else : ?>
                            <div class="tmes-image-placeholder">
                                <span><?php echo esc_html($category ?: 'Energía'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tmes-thumbs">
                        <div class="tmes-thumb is-active">
                            <?php if ($image_url) : ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                            <?php else : ?>
                                <span>Imagen</span>
                            <?php endif; ?>
                        </div>
                        <div class="tmes-thumb"><span>Ficha</span></div>
                        <div class="tmes-thumb"><span>Specs</span></div>
                        <div class="tmes-thumb"><span>+</span></div>
                    </div>
                </div>

                <div class="tmes-summary">
                    <div class="tmes-badges">
                        <?php if ($category) : ?>
                            <span><?php echo esc_html($category); ?></span>
                        <?php endif; ?>

                        <?php if ($voltage) : ?>
                            <span><?php echo esc_html($voltage); ?></span>
                        <?php endif; ?>
                    </div>

                    <h1><?php echo esc_html($title); ?></h1>

                    <?php if ($brand) : ?>
                        <p class="tmes-subtitle"><?php echo esc_html($brand); ?></p>
                    <?php endif; ?>

                    <p class="tmes-description"><?php echo esc_html($excerpt); ?></p>

                    <div class="tmes-price-box">
                        <h2><?php echo esc_html($price_text); ?></h2>

                        <a class="tmes-btn tmes-btn-quote" href="<?php echo esc_url($contact_url); ?>">
                            Solicitar cotización
                        </a>

                        <a class="tmes-btn tmes-btn-whatsapp" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
                            WhatsApp
                        </a>

                        <a class="tmes-btn tmes-btn-outline" href="<?php echo esc_url(home_url('/energia/')); ?>">
                            Volver a energía
                        </a>
                    </div>

                    <div class="tmes-highlights">
                        <?php foreach ($highlight_items as $label => $value) : ?>
                            <?php if ($value === '') { continue; } ?>
                            <div>
                                <span><?php echo esc_html($label); ?></span>
                                <strong><?php echo esc_html($value); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tmes-details">
        <div class="tmes-container">
            <div class="tmes-details-grid">
                <article class="tmes-panel">
                    <div class="tmes-panel-title">
                        <span>i</span>
                        <h2>Información general</h2>
                    </div>

                    <div class="tmes-rows">
                        <?php foreach ($general_rows as $label => $value) : ?>
                            <?php if ($value === '') { continue; } ?>
                            <div>
                                <span><?php echo esc_html($label); ?></span>
                                <strong><?php echo esc_html($value); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="tmes-panel">
                    <div class="tmes-panel-title">
                        <span>⚙</span>
                        <h2>Especificaciones técnicas</h2>
                    </div>

                    <div class="tmes-rows">
                        <?php foreach ($technical_rows as $label => $value) : ?>
                            <?php if ($value === '') { continue; } ?>
                            <div>
                                <span><?php echo esc_html($label); ?></span>
                                <strong><?php echo esc_html($value); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </div>

            <article class="tmes-info-band">
                <div>
                    <h2>Validación técnica antes de cotizar</h2>
                    <p>Confirmamos compatibilidad, voltaje, conector, ciclo de uso, autonomía esperada y condiciones de operación antes de recomendar una solución.</p>
                </div>

                <a href="<?php echo esc_url($contact_url); ?>">Solicitar asesoría</a>
            </article>

            <?php if (trim((string) get_post_field('post_content', $post_id)) !== '') : ?>
                <article class="tmes-content">
                    <h2>Descripción</h2>
                    <?php
                    while (have_posts()) :
                        the_post();
                        the_content();
                    endwhile;
                    ?>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($related->have_posts()) : ?>
        <section class="tmes-related">
            <div class="tmes-container">
                <div class="tmes-section-heading">
                    <div>
                        <h2>Soluciones relacionadas</h2>
                        <p>Otras opciones de energía disponibles en el catálogo.</p>
                    </div>

                    <a href="<?php echo esc_url(home_url('/energia/')); ?>">Ver catálogo</a>
                </div>

                <div class="tmes-related-grid">
                    <?php while ($related->have_posts()) : ?>
                        <?php
                        $related->the_post();

                        $related_id = get_the_ID();
                        $related_title = get_the_title($related_id);
                        $related_link = get_permalink($related_id);
                        $related_image = has_post_thumbnail($related_id)
                            ? get_the_post_thumbnail_url($related_id, 'medium_large')
                            : trim((string) get_post_meta($related_id, 'tmd_imagen_url', true));

                        $related_brand = trim((string) get_post_meta($related_id, 'tmd_marca', true));
                        $related_voltage = trim((string) get_post_meta($related_id, 'tmd_voltaje', true));
                        ?>
                        <article class="tmes-related-card">
                            <a class="tmes-related-image" href="<?php echo esc_url($related_link); ?>">
                                <?php if ($related_image) : ?>
                                    <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_title); ?>">
                                <?php endif; ?>
                            </a>

                            <div class="tmes-related-body">
                                <?php if ($related_brand) : ?>
                                    <span><?php echo esc_html($related_brand); ?></span>
                                <?php endif; ?>

                                <h3><a href="<?php echo esc_url($related_link); ?>"><?php echo esc_html($related_title); ?></a></h3>

                                <?php if ($related_voltage) : ?>
                                    <p><?php echo esc_html($related_voltage); ?></p>
                                <?php endif; ?>

                                <a class="tmes-related-link" href="<?php echo esc_url($related_link); ?>">Ver ficha</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <?php wp_reset_postdata(); ?>
    <?php endif; ?>
</main>

<?php
get_footer();
