<?php
/**
 * Template single para Equipos TMD.
 */

get_header();

if (! function_exists('tmd_single_first_term_name')) {
    function tmd_single_first_term_name($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}

if (! function_exists('tmd_single_meta_or_term')) {
    function tmd_single_meta_or_term($post_id, $meta_key, $taxonomy = '') {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        if ($taxonomy) {
            return tmd_single_first_term_name($post_id, $taxonomy);
        }

        return '';
    }
}

if (! function_exists('tmd_single_badge_class')) {
    function tmd_single_badge_class($value) {
        $slug = sanitize_title($value);

        if (strpos($slug, 'electrico') !== false || strpos($slug, 'eléctrico') !== false) {
            return 'is-electric';
        }

        if (strpos($slug, 'glp') !== false || strpos($slug, 'gas') !== false) {
            return 'is-gas';
        }

        if (strpos($slug, 'diesel') !== false || strpos($slug, 'diésel') !== false) {
            return 'is-diesel';
        }

        return '';
    }
}

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();

    $title = get_the_title($post_id);
    $permalink = get_permalink($post_id);

    $tipo = tmd_single_first_term_name($post_id, 'tmd_tipo_equipo');
    $marca = tmd_single_meta_or_term($post_id, 'tmd_marca', 'tmd_marca_equipo');
    $modelo = trim((string) get_post_meta($post_id, 'tmd_modelo', true));
    $anio = trim((string) get_post_meta($post_id, 'tmd_anio', true));
    $capacidad = trim((string) get_post_meta($post_id, 'tmd_capacidad', true));
    $altura = trim((string) get_post_meta($post_id, 'tmd_altura', true));
    $energia = tmd_single_meta_or_term($post_id, 'tmd_energia', 'tmd_energia_equipo');
    $condicion = tmd_single_meta_or_term($post_id, 'tmd_condicion', 'tmd_condicion_equipo');
    $uso = tmd_single_meta_or_term($post_id, 'tmd_uso', 'tmd_uso_equipo');
    $modalidad = trim((string) get_post_meta($post_id, 'tmd_modalidad', true));
    $precio = trim((string) get_post_meta($post_id, 'tmd_precio', true));
    $mostrar_precio = trim((string) get_post_meta($post_id, 'tmd_mostrar_precio', true));
    $imagen_url = trim((string) get_post_meta($post_id, 'tmd_imagen_url', true));

    $price_label = 'Precio a consultar';

    if ($mostrar_precio && strtolower($mostrar_precio) !== 'no' && $precio !== '') {
        $price_label = $precio;
    }

    $whatsapp_text = rawurlencode('Hola, quiero cotizar el equipo ' . $title . '.');
    $whatsapp_url = 'https://wa.me/573244298326?text=' . $whatsapp_text;

    $contact_page = get_page_by_path('nosotros/contacto', OBJECT, 'page');
$contact_base_url = $contact_page ? get_permalink($contact_page->ID) : home_url('/nosotros/contacto/');
$quote_url = add_query_arg('equipo', $title, $contact_base_url);

    $general_rows = [
        'Marca' => $marca,
        'Modelo' => $modelo,
        'Condición' => $condicion,
        'Modalidad' => $modalidad,
        'Uso' => $uso,
    ];

    $technical_rows = [
        'Capacidad' => $capacidad,
        'Altura máxima' => $altura,
        'Tipo de energía' => $energia,
        'Año' => $anio,
    ];

    $config_rows = [
        'Tipo de equipo' => $tipo,
        'Estado físico' => $condicion,
        'Aplicación' => $uso,
    ];

    $dimension_rows = [
        'Capacidad' => $capacidad,
        'Altura' => $altura,
        'Energía' => $energia,
        'Condición' => $condicion,
    ];
?>

<main class="tmd-single-equipo">

    <section class="tmd-single-hero">
        <div class="tmd-single-container">

            <div class="tmd-single-breadcrumb">
                <a href="<?php echo esc_url(home_url('/equipos/')); ?>">Catálogo</a>
                <span>/</span>
                <?php if ($tipo) : ?>
                    <span><?php echo esc_html($tipo); ?></span>
                <?php endif; ?>
            </div>

            <div class="tmd-single-top-grid">

                <div class="tmd-single-gallery">
                    <div class="tmd-single-main-image">
                        <?php if (has_post_thumbnail($post_id)) : ?>
                            <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
                        <?php elseif ($imagen_url) : ?>
                            <img src="<?php echo esc_url($imagen_url); ?>" alt="<?php echo esc_attr($title); ?>">
                        <?php else : ?>
                            <div class="tmd-single-image-placeholder">
                                <span><?php echo esc_html($marca ?: 'TM-Dual'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tmd-single-thumbs">
                        <div class="tmd-single-thumb is-active">
                            <?php if (has_post_thumbnail($post_id)) : ?>
                                <?php echo get_the_post_thumbnail($post_id, 'thumbnail'); ?>
                            <?php elseif ($imagen_url) : ?>
                                <img src="<?php echo esc_url($imagen_url); ?>" alt="<?php echo esc_attr($title); ?>">
                            <?php else : ?>
                                <span>1</span>
                            <?php endif; ?>
                        </div>
                        <div class="tmd-single-thumb"><span>Ficha</span></div>
                        <div class="tmd-single-thumb"><span>Specs</span></div>
                        <div class="tmd-single-thumb"><span>+</span></div>
                    </div>
                </div>

                <aside class="tmd-single-side">
                    <div class="tmd-single-badges">
                        <?php if ($energia) : ?>
                            <span class="tmd-single-badge <?php echo esc_attr(tmd_single_badge_class($energia)); ?>">
                                <?php echo esc_html($energia); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($tipo) : ?>
                            <span class="tmd-single-badge is-soft">
                                <?php echo esc_html($tipo); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1><?php echo esc_html($title); ?></h1>

                    <?php if ($marca || $modelo) : ?>
                        <p class="tmd-single-subtitle">
                            <?php echo esc_html(trim($marca . ' ' . $modelo)); ?>
                        </p>
                    <?php endif; ?>

                    <div class="tmd-single-quote-card">
                        <div class="tmd-single-price"><?php echo esc_html($price_label); ?></div>

                        <div class="tmd-single-actions">
                            <a class="tmd-single-btn tmd-single-btn-yellow" href="<?php echo esc_url($quote_url); ?>">
                                Solicitar cotización
                            </a>

                            <a class="tmd-single-btn tmd-single-btn-whatsapp" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
                                WhatsApp
                            </a>

                            <a class="tmd-single-btn tmd-single-btn-outline" href="<?php echo esc_url(home_url('/equipos/')); ?>">
                                Volver al catálogo
                            </a>
                        </div>

                        <?php if ($tipo) : ?>
                            <a class="tmd-single-help" href="<?php echo esc_url(home_url('/tipo-equipo/' . sanitize_title($tipo) . '/')); ?>">
                                ¿Qué es <?php echo esc_html($tipo); ?>?
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="tmd-single-highlights">
                        <?php if ($capacidad) : ?>
                            <div>
                                <span>Capacidad</span>
                                <strong><?php echo esc_html($capacidad); ?></strong>
                            </div>
                        <?php endif; ?>

                        <?php if ($altura) : ?>
                            <div>
                                <span>Altura máx.</span>
                                <strong><?php echo esc_html($altura); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <section class="tmd-single-content-section">
        <div class="tmd-single-container">

            <?php if (get_the_content()) : ?>
                <div class="tmd-single-description">
                    <h2>Descripción</h2>
                    <div class="tmd-single-description-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tmd-single-info-grid">

                <section class="tmd-single-info-block">
                    <div class="tmd-single-info-title">
                        <span>i</span>
                        <h2>Información general</h2>
                    </div>

                    <div class="tmd-single-row-list">
                        <?php foreach ($general_rows as $label => $value) : ?>
                            <?php if ($value !== '') : ?>
                                <div class="tmd-single-row">
                                    <span><?php echo esc_html($label); ?></span>
                                    <strong><?php echo esc_html($value); ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="tmd-single-info-block">
                    <div class="tmd-single-info-title">
                        <span>⚙</span>
                        <h2>Especificaciones técnicas</h2>
                    </div>

                    <div class="tmd-single-spec-grid">
                        <?php foreach ($technical_rows as $label => $value) : ?>
                            <?php if ($value !== '') : ?>
                                <div>
                                    <span><?php echo esc_html($label); ?></span>
                                    <strong><?php echo esc_html($value); ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="tmd-single-info-block">
                    <div class="tmd-single-info-title">
                        <span>☰</span>
                        <h2>Configuración</h2>
                    </div>

                    <div class="tmd-single-row-list">
                        <?php foreach ($config_rows as $label => $value) : ?>
                            <?php if ($value !== '') : ?>
                                <div class="tmd-single-row">
                                    <span><?php echo esc_html($label); ?></span>
                                    <strong><?php echo esc_html($value); ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="tmd-single-info-block">
                    <div class="tmd-single-info-title">
                        <span>↔</span>
                        <h2>Resumen operativo</h2>
                    </div>

                    <div class="tmd-single-dimensions">
                        <?php foreach ($dimension_rows as $label => $value) : ?>
                            <?php if ($value !== '') : ?>
                                <div>
                                    <span><?php echo esc_html($label); ?></span>
                                    <strong><?php echo esc_html($value); ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>
        </div>
    </section>

    <?php
    $related_args = [
        'post_type'      => 'tmd_equipo',
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'post__not_in'   => [$post_id],
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    $related_tax = [];

    $tipo_terms = wp_get_post_terms($post_id, 'tmd_tipo_equipo', ['fields' => 'ids']);
    if (! empty($tipo_terms) && ! is_wp_error($tipo_terms)) {
        $related_tax[] = [
            'taxonomy' => 'tmd_tipo_equipo',
            'field'    => 'term_id',
            'terms'    => $tipo_terms,
        ];
    }

    if (! empty($related_tax)) {
        $related_args['tax_query'] = $related_tax;
    }

    $related = new WP_Query($related_args);
    ?>

    <?php if ($related->have_posts()) : ?>
        <section class="tmd-single-related">
            <div class="tmd-single-container">
                <div class="tmd-single-related-head">
                    <div>
                        <h2>Equipos relacionados</h2>
                        <span></span>
                    </div>

                    <a href="<?php echo esc_url(home_url('/equipos/')); ?>">Ver todo el catálogo →</a>
                </div>

                <div class="tmd-single-related-grid">
                    <?php while ($related->have_posts()) : $related->the_post(); ?>
                        <?php
                        $rel_id = get_the_ID();
                        $rel_title = get_the_title($rel_id);
                        $rel_permalink = get_permalink($rel_id);
                        $rel_marca = tmd_single_meta_or_term($rel_id, 'tmd_marca', 'tmd_marca_equipo');
                        $rel_capacidad = trim((string) get_post_meta($rel_id, 'tmd_capacidad', true));
                        $rel_energia = tmd_single_meta_or_term($rel_id, 'tmd_energia', 'tmd_energia_equipo');
                        $rel_img = trim((string) get_post_meta($rel_id, 'tmd_imagen_url', true));
                        ?>
                        <article class="tmd-single-related-card">
                            <a class="tmd-single-related-image" href="<?php echo esc_url($rel_permalink); ?>">
                                <?php if (has_post_thumbnail($rel_id)) : ?>
                                    <?php echo get_the_post_thumbnail($rel_id, 'medium_large'); ?>
                                <?php elseif ($rel_img) : ?>
                                    <img src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($rel_title); ?>">
                                <?php endif; ?>

                                <?php if ($rel_energia) : ?>
                                    <span><?php echo esc_html($rel_energia); ?></span>
                                <?php endif; ?>
                            </a>

                            <div class="tmd-single-related-body">
                                <?php if ($rel_marca) : ?>
                                    <p><?php echo esc_html($rel_marca); ?></p>
                                <?php endif; ?>

                                <h3>
                                    <a href="<?php echo esc_url($rel_permalink); ?>">
                                        <?php echo esc_html($rel_title); ?>
                                    </a>
                                </h3>

                                <div>
                                    <?php if ($rel_capacidad) : ?>
                                        <strong><?php echo esc_html($rel_capacidad); ?></strong>
                                    <?php endif; ?>

                                    <a href="<?php echo esc_url($rel_permalink); ?>">Ver</a>
                                </div>
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
endwhile;

get_footer();
