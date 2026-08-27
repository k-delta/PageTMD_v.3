<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'tm-work-sans',
        'https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'tmd-tabler-icons',
        'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css',
        [],
        null
    );

    $mega_css = get_stylesheet_directory() . '/assets/css/tmd-mega-menu.css';
    wp_enqueue_style(
        'tmd-mega-menu',
        get_stylesheet_directory_uri() . '/assets/css/tmd-mega-menu.css',
        ['tmd-tabler-icons'],
        file_exists($mega_css) ? filemtime($mega_css) : '1.0.0'
    );

    $mega_js = get_stylesheet_directory() . '/assets/js/tmd-mega-menu.js';
    wp_enqueue_script(
        'tmd-mega-menu',
        get_stylesheet_directory_uri() . '/assets/js/tmd-mega-menu.js',
        [],
        file_exists($mega_js) ? filemtime($mega_js) : '1.0.0',
        true
    );
}, 40);

add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_style(
        'tm-work-sans-editor',
        'https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&display=swap',
        [],
        null
    );
});

add_filter('body_class', function ($classes) {
    $classes[] = 'tmd-custom-shell';
    return $classes;
});

add_action('wp_body_open', function () {
    get_template_part('template-parts/tmd-header');
}, 1);

add_filter('blocksy:header:render', '__return_false', 20);

/* TMD_CONTACT_RAIL_START */
add_action('wp_enqueue_scripts', function () {
    $contact_rail_css = get_stylesheet_directory() . '/assets/css/tmd-contact-rail.css';
    wp_enqueue_style(
        'tmd-contact-rail',
        get_stylesheet_directory_uri() . '/assets/css/tmd-contact-rail.css',
        ['tmd-tabler-icons'],
        file_exists($contact_rail_css) ? filemtime($contact_rail_css) : '1.0.0'
    );
}, 42);

add_action('wp_body_open', function () {
    get_template_part('template-parts/tmd-contact-rail');
}, 5);
/* TMD_CONTACT_RAIL_END */

/* TMD_FOOTER_START */
add_action('wp_enqueue_scripts', function () {
    $footer_css = get_stylesheet_directory() . '/assets/css/tmd-footer.css';
    wp_enqueue_style(
        'tmd-footer',
        get_stylesheet_directory_uri() . '/assets/css/tmd-footer.css',
        [],
        file_exists($footer_css) ? filemtime($footer_css) : '1.0.0'
    );
}, 45);

// El footer corporativo sustituye estructuralmente al footer del tema padre.
add_filter('blocksy:builder:footer:enabled', '__return_false', 20);

add_action('blocksy:footer:before', function () {
    get_template_part('template-parts/tmd-footer');
}, 5);
/* TMD_FOOTER_END */

/* TMD_HOME_START */
add_action('wp_enqueue_scripts', function () {
    $home_css = get_stylesheet_directory() . '/assets/css/tmd-home.css';
    wp_enqueue_style(
        'tmd-home',
        get_stylesheet_directory_uri() . '/assets/css/tmd-home.css',
        [],
        file_exists($home_css) ? filemtime($home_css) : '1.0.0'
    );
}, 50);
/* TMD_HOME_END */

/* TMD_HOME_EDITOR_START */
add_action('wp_enqueue_scripts', function () {
    $home_css = get_stylesheet_directory() . '/assets/css/tmd-home-blocks.css';
    wp_enqueue_style(
        'tmd-home-blocks',
        get_stylesheet_directory_uri() . '/assets/css/tmd-home-blocks.css',
        [],
        file_exists($home_css) ? filemtime($home_css) : '1.0.0'
    );
}, 55);

add_action('enqueue_block_editor_assets', function () {
    $home_css = get_stylesheet_directory() . '/assets/css/tmd-home-blocks.css';
    wp_enqueue_style(
        'tmd-home-blocks-editor',
        get_stylesheet_directory_uri() . '/assets/css/tmd-home-blocks.css',
        [],
        file_exists($home_css) ? filemtime($home_css) : '1.0.0'
    );
});
/* TMD_HOME_EDITOR_END */

/* TMD_EQUIPMENT_CATALOG_START */
add_action('wp_enqueue_scripts', function () {
    $catalog_css = get_stylesheet_directory() . '/assets/css/tmd-catalog.css';
    wp_enqueue_style(
        'tmd-catalog',
        get_stylesheet_directory_uri() . '/assets/css/tmd-catalog.css',
        [],
        file_exists($catalog_css) ? filemtime($catalog_css) : '1.0.0'
    );
}, 60);

if (! function_exists('tmd_get_first_term_name')) {
    function tmd_get_first_term_name($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}

if (! function_exists('tmd_meta_or_term')) {
    function tmd_meta_or_term($post_id, $meta_key, $taxonomy = '') {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        if ($taxonomy) {
            return tmd_get_first_term_name($post_id, $taxonomy);
        }

        return '';
    }
}

if (! shortcode_exists('tmd_equipment_grid')) {
    add_shortcode('tmd_equipment_grid', function ($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
        ], $atts, 'tmd_equipment_grid');

        $query = new WP_Query([
            'post_type' => 'tmd_equipo',
            'post_status' => 'publish',
            'posts_per_page' => max(1, (int) $atts['per_page']),
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        ob_start();

        if (! $query->have_posts()) {
            echo '<div class="tmd-equipment-empty">Todavía no hay equipos publicados en el catálogo.</div>';
            return ob_get_clean();
        }

        echo '<div class="tmd-equipment-grid">';

        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $permalink = get_permalink($post_id);
            $title = get_the_title($post_id);

            $tipo = tmd_get_first_term_name($post_id, 'tmd_tipo_equipo');
            $marca = tmd_meta_or_term($post_id, 'tmd_marca', 'tmd_marca_equipo');
            $energia = tmd_meta_or_term($post_id, 'tmd_energia', 'tmd_energia_equipo');
            $condicion = tmd_meta_or_term($post_id, 'tmd_condicion', 'tmd_condicion_equipo');
            $capacidad = trim((string) get_post_meta($post_id, 'tmd_capacidad', true));
            $altura = trim((string) get_post_meta($post_id, 'tmd_altura', true));
            $modelo = trim((string) get_post_meta($post_id, 'tmd_modelo', true));
            $imagen_url = trim((string) get_post_meta($post_id, 'tmd_imagen_url', true));

            $excerpt = get_the_excerpt($post_id);
            if (! $excerpt) {
                $excerpt = 'Equipo disponible para operación logística e industrial.';
            }

            echo '<article class="tmd-equipment-card">';

            echo '<a class="tmd-equipment-image" href="' . esc_url($permalink) . '" aria-label="' . esc_attr($title) . '">';
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, 'medium_large');
            } elseif ($imagen_url) {
                echo '<img src="' . esc_url($imagen_url) . '" alt="' . esc_attr($title) . '">';
            }
            echo '</a>';

            echo '<div class="tmd-equipment-body">';

            echo '<div class="tmd-equipment-tags">';
            foreach ([$tipo, $condicion] as $tag) {
                if ($tag !== '') {
                    echo '<span class="tmd-equipment-tag">' . esc_html($tag) . '</span>';
                }
            }
            echo '</div>';

            echo '<h3 class="tmd-equipment-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';

            echo '<p class="tmd-equipment-excerpt">' . esc_html(wp_trim_words($excerpt, 18)) . '</p>';

            echo '<div class="tmd-equipment-specs">';

            $specs = [
                'Marca' => $marca,
                'Modelo' => $modelo,
                'Energía' => $energia,
                'Capacidad' => $capacidad,
                'Altura' => $altura,
                'Condición' => $condicion,
            ];

            foreach ($specs as $label => $value) {
                if ($value === '') {
                    continue;
                }

                echo '<div class="tmd-equipment-spec">';
                echo '<span>' . esc_html($label) . '</span>';
                echo '<strong>' . esc_html($value) . '</strong>';
                echo '</div>';
            }

            echo '</div>';

            echo '<div class="tmd-equipment-actions">';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-primary" href="' . esc_url($permalink) . '">Ver ficha</a>';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-secondary" href="' . esc_url(home_url('/nosotros/contacto/?equipo=' . rawurlencode($title))) . '">Cotizar</a>';
            echo '</div>';

            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();

        return ob_get_clean();
    });
}
/* TMD_EQUIPMENT_CATALOG_END */

/* TMD_CATALOG_EDITOR_START */
add_action('enqueue_block_editor_assets', function () {
    $catalog_css = get_stylesheet_directory() . '/assets/css/tmd-catalog.css';

    wp_enqueue_style(
        'tmd-catalog-editor',
        get_stylesheet_directory_uri() . '/assets/css/tmd-catalog.css',
        [],
        file_exists($catalog_css) ? filemtime($catalog_css) : '1.0.0'
    );
});
/* TMD_CATALOG_EDITOR_END */

/* TMD_EQUIPMENT_GRID_FILTER_OVERRIDE_START */
add_action('init', function () {
    remove_shortcode('tmd_equipment_grid');

    add_shortcode('tmd_equipment_grid', function ($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
        ], $atts, 'tmd_equipment_grid');

        $tax_map = [
            'tipo-equipo' => 'tmd_tipo_equipo',
            'marca'       => 'tmd_marca_equipo',
            'energia'     => 'tmd_energia_equipo',
            'condicion'   => 'tmd_condicion_equipo',
            'uso'         => 'tmd_uso_equipo',
        ];

        $tax_query = ['relation' => 'AND'];

        foreach ($tax_map as $url_var => $taxonomy) {
            $terms = tmd_get_filter_values_from_request($url_var);

            if (! empty($terms)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => 'IN',
                ];
            }
        }

        $query_args = [
            'post_type'      => 'tmd_equipo',
            'post_status'    => 'publish',
            'posts_per_page' => max(1, (int) $atts['per_page']),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (count($tax_query) > 1) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($query_args);

        ob_start();

        if (! $query->have_posts()) {
            echo '<div class="tmd-equipment-empty">No encontramos equipos con los filtros seleccionados.</div>';
            return ob_get_clean();
        }

        echo '<div class="tmd-equipment-grid">';

        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $permalink = get_permalink($post_id);
            $title = get_the_title($post_id);

            $tipo = tmd_filter_get_first_term_name($post_id, 'tmd_tipo_equipo');
            $marca = tmd_filter_meta_or_term($post_id, 'tmd_marca', 'tmd_marca_equipo');
            $energia = tmd_filter_meta_or_term($post_id, 'tmd_energia', 'tmd_energia_equipo');
            $condicion = tmd_filter_meta_or_term($post_id, 'tmd_condicion', 'tmd_condicion_equipo');
            $capacidad = trim((string) get_post_meta($post_id, 'tmd_capacidad', true));
            $altura = trim((string) get_post_meta($post_id, 'tmd_altura', true));
            $modelo = trim((string) get_post_meta($post_id, 'tmd_modelo', true));
            $imagen_url = trim((string) get_post_meta($post_id, 'tmd_imagen_url', true));

            $excerpt = get_the_excerpt($post_id);
            if (! $excerpt) {
                $excerpt = 'Equipo disponible para validación técnica, compra o alquiler según disponibilidad.';
            }

            echo '<article class="tmd-equipment-card">';

            echo '<a class="tmd-equipment-image" href="' . esc_url($permalink) . '" aria-label="' . esc_attr($title) . '">';
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, 'medium_large');
            } elseif ($imagen_url) {
                echo '<img src="' . esc_url($imagen_url) . '" alt="' . esc_attr($title) . '">';
            }
            echo '</a>';

            echo '<div class="tmd-equipment-body">';

            echo '<div class="tmd-equipment-tags">';
            foreach ([$tipo, $condicion] as $tag) {
                if ($tag !== '') {
                    echo '<span class="tmd-equipment-tag">' . esc_html($tag) . '</span>';
                }
            }
            echo '</div>';

            echo '<h3 class="tmd-equipment-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
            echo '<p class="tmd-equipment-excerpt">' . esc_html(wp_trim_words($excerpt, 20)) . '</p>';

            echo '<div class="tmd-equipment-specs">';

            $specs = [
                'Marca'     => $marca,
                'Modelo'    => $modelo,
                'Energía'   => $energia,
                'Capacidad' => $capacidad,
                'Altura'    => $altura,
                'Condición' => $condicion,
            ];

            foreach ($specs as $label => $value) {
                if ($value === '') {
                    continue;
                }

                echo '<div class="tmd-equipment-spec">';
                echo '<span>' . esc_html($label) . '</span>';
                echo '<strong>' . esc_html($value) . '</strong>';
                echo '</div>';
            }

            echo '</div>';

            echo '<div class="tmd-equipment-actions">';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-primary" href="' . esc_url($permalink) . '">Ver ficha</a>';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-secondary" href="' . esc_url(home_url('/nosotros/contacto/?equipo=' . rawurlencode($title))) . '">Cotizar</a>';
            echo '</div>';

            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();

        return ob_get_clean();
    });
}, 99);

if (! function_exists('tmd_get_filter_values_from_request')) {
    function tmd_get_filter_values_from_request($key) {
        $values = [];

        $possible_keys = [
            $key,
            str_replace('-', '_', $key),
            'filter_' . $key,
            'filter_' . str_replace('-', '_', $key),
            'wpc_' . $key,
            'wpc_' . str_replace('-', '_', $key),
        ];

        foreach ($possible_keys as $possible_key) {
            if (isset($_GET[$possible_key])) {
                $raw = wp_unslash($_GET[$possible_key]);

                if (is_array($raw)) {
                    $values = array_merge($values, $raw);
                } else {
                    $values = array_merge($values, explode(',', (string) $raw));
                }
            }
        }

        $path = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $path = rawurldecode((string) parse_url($path, PHP_URL_PATH));

        if ($path) {
            $quoted = preg_quote($key, '~');

            if (preg_match_all('~(?:^|/)' . $quoted . '/([^/]+)~', $path, $matches)) {
                foreach ($matches[1] as $match) {
                    $values = array_merge($values, explode(',', $match));
                }
            }

            if (preg_match_all('~(?:^|/)' . $quoted . '-([^/]+)~', $path, $matches)) {
                foreach ($matches[1] as $match) {
                    $values = array_merge($values, explode(',', $match));
                }
            }
        }

        $values = array_map('sanitize_title', $values);
        $values = array_filter(array_unique($values));

        return array_values($values);
    }
}

if (! function_exists('tmd_filter_get_first_term_name')) {
    function tmd_filter_get_first_term_name($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}

if (! function_exists('tmd_filter_meta_or_term')) {
    function tmd_filter_meta_or_term($post_id, $meta_key, $taxonomy = '') {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        if ($taxonomy) {
            return tmd_filter_get_first_term_name($post_id, $taxonomy);
        }

        return '';
    }
}
/* TMD_EQUIPMENT_GRID_FILTER_OVERRIDE_END */

/* TMD_CUSTOM_EQUIPMENT_FILTERS_START */
add_action('init', function () {
    remove_shortcode('tmd_equipment_grid');

    add_shortcode('tmd_equipment_grid', function ($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
        ], $atts, 'tmd_equipment_grid');

        $tax_map = [
            'tipo-equipo' => 'tmd_tipo_equipo',
            'marca'       => 'tmd_marca_equipo',
            'energia'     => 'tmd_energia_equipo',
            'condicion'   => 'tmd_condicion_equipo',
            'uso'         => 'tmd_uso_equipo',
        ];

        $tax_query = ['relation' => 'AND'];

        foreach ($tax_map as $url_var => $taxonomy) {
            $selected_terms = tmd_catalog_get_request_values($url_var);

            if (! empty($selected_terms)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $selected_terms,
                    'operator' => 'IN',
                ];
            }
        }

        $query_args = [
            'post_type'      => 'tmd_equipo',
            'post_status'    => 'publish',
            'posts_per_page' => max(1, (int) $atts['per_page']),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (count($tax_query) > 1) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($query_args);

        ob_start();

        if (! $query->have_posts()) {
            echo '<div class="tmd-equipment-empty">No encontramos equipos con los filtros seleccionados.</div>';
            return ob_get_clean();
        }

        echo '<div class="tmd-equipment-grid">';

        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $permalink = get_permalink($post_id);
            $title = get_the_title($post_id);

            $tipo = tmd_catalog_get_first_term_name($post_id, 'tmd_tipo_equipo');
            $marca = tmd_catalog_meta_or_term($post_id, 'tmd_marca', 'tmd_marca_equipo');
            $energia = tmd_catalog_meta_or_term($post_id, 'tmd_energia', 'tmd_energia_equipo');
            $condicion = tmd_catalog_meta_or_term($post_id, 'tmd_condicion', 'tmd_condicion_equipo');
            $capacidad = trim((string) get_post_meta($post_id, 'tmd_capacidad', true));
            $altura = trim((string) get_post_meta($post_id, 'tmd_altura', true));
            $modelo = trim((string) get_post_meta($post_id, 'tmd_modelo', true));
            $imagen_url = trim((string) get_post_meta($post_id, 'tmd_imagen_url', true));

            $excerpt = get_the_excerpt($post_id);
            if (! $excerpt) {
                $excerpt = 'Equipo disponible para validación técnica, compra o alquiler según disponibilidad.';
            }

            echo '<article class="tmd-equipment-card">';

            echo '<a class="tmd-equipment-image" href="' . esc_url($permalink) . '" aria-label="' . esc_attr($title) . '">';
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, 'medium_large');
            } elseif ($imagen_url) {
                echo '<img src="' . esc_url($imagen_url) . '" alt="' . esc_attr($title) . '">';
            }
            echo '</a>';

            echo '<div class="tmd-equipment-body">';

            echo '<div class="tmd-equipment-tags">';
            foreach ([$tipo, $condicion] as $tag) {
                if ($tag !== '') {
                    echo '<span class="tmd-equipment-tag">' . esc_html($tag) . '</span>';
                }
            }
            echo '</div>';

            echo '<h3 class="tmd-equipment-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
            echo '<p class="tmd-equipment-excerpt">' . esc_html(wp_trim_words($excerpt, 20)) . '</p>';

            echo '<div class="tmd-equipment-specs">';

            $specs = [
                'Marca'     => $marca,
                'Modelo'    => $modelo,
                'Energía'   => $energia,
                'Capacidad' => $capacidad,
                'Altura'    => $altura,
                'Condición' => $condicion,
            ];

            foreach ($specs as $label => $value) {
                if ($value === '') {
                    continue;
                }

                echo '<div class="tmd-equipment-spec">';
                echo '<span>' . esc_html($label) . '</span>';
                echo '<strong>' . esc_html($value) . '</strong>';
                echo '</div>';
            }

            echo '</div>';

            echo '<div class="tmd-equipment-actions">';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-primary" href="' . esc_url($permalink) . '">Ver ficha</a>';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-secondary" href="' . esc_url(home_url('/nosotros/contacto/?equipo=' . rawurlencode($title))) . '">Cotizar</a>';
            echo '</div>';

            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();

        return ob_get_clean();
    });

    add_shortcode('tmd_equipment_filters', function ($atts) {
        $atts = shortcode_atts([
            'set_id' => 581,
        ], $atts, 'tmd_equipment_filters');

        $set_id = absint($atts['set_id']);

        $fields = get_posts([
            'post_type'      => 'filter-field',
            'post_status'    => 'publish',
            'post_parent'    => $set_id,
            'posts_per_page' => 20,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if (empty($fields)) {
            return '<div class="tmd-equipment-filter-empty">No hay filtros configurados todavía.</div>';
        }

        ob_start();

        echo '<form class="tmd-equipment-filters-form" method="get" action="' . esc_url(get_permalink(49)) . '">';

        echo '<div class="tmd-equipment-active-filters">';

        $has_active = false;

        foreach ($fields as $field) {
            $url_var = $field->post_name;
            $selected = tmd_catalog_get_request_values($url_var);

            if (empty($selected)) {
                continue;
            }

            $taxonomy = tmd_catalog_get_filter_taxonomy($field);

            foreach ($selected as $slug) {
                $term = get_term_by('slug', $slug, $taxonomy);
                $label = $term && ! is_wp_error($term) ? $term->name : $slug;

                echo '<span class="tmd-equipment-active-chip">' . esc_html($field->post_title . ': ' . $label) . '</span>';
                $has_active = true;
            }
        }

        if ($has_active) {
            echo '<a class="tmd-equipment-clear-filters" href="' . esc_url(get_permalink(49)) . '">Limpiar filtros</a>';
        }

        echo '</div>';

        foreach ($fields as $field) {
            $taxonomy = tmd_catalog_get_filter_taxonomy($field);

            if (! $taxonomy || ! taxonomy_exists($taxonomy)) {
                continue;
            }

            $url_var = $field->post_name;
            $selected = tmd_catalog_get_request_values($url_var);

            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            echo '<fieldset class="tmd-equipment-filter-group">';
            echo '<legend>' . esc_html($field->post_title) . '</legend>';

            foreach ($terms as $term) {
                $input_id = 'tmd-filter-' . sanitize_html_class($url_var . '-' . $term->slug);
                $checked = in_array($term->slug, $selected, true);

                echo '<label class="tmd-equipment-filter-option" for="' . esc_attr($input_id) . '">';
                echo '<input id="' . esc_attr($input_id) . '" type="checkbox" name="' . esc_attr($url_var) . '[]" value="' . esc_attr($term->slug) . '"' . checked($checked, true, false) . ' onchange="this.form.submit()">';
                echo '<span>' . esc_html($term->name) . '</span>';
                echo '<em>' . esc_html((string) $term->count) . '</em>';
                echo '</label>';
            }

            echo '</fieldset>';
        }

        echo '<noscript><button class="tmd-equipment-apply-filters" type="submit">Aplicar filtros</button></noscript>';
        echo '</form>';

        return ob_get_clean();
    });
}, 120);

if (! function_exists('tmd_catalog_get_filter_taxonomy')) {
    function tmd_catalog_get_filter_taxonomy($field) {
        $settings = maybe_unserialize($field->post_content);

        if (is_array($settings) && ! empty($settings['e_name'])) {
            return sanitize_key($settings['e_name']);
        }

        if (! empty($field->post_excerpt) && strpos($field->post_excerpt, 'taxonomy_') === 0) {
            return sanitize_key(substr($field->post_excerpt, 9));
        }

        return '';
    }
}

if (! function_exists('tmd_catalog_get_request_values')) {
    function tmd_catalog_get_request_values($key) {
        $values = [];

        $possible_keys = [
            $key,
            str_replace('-', '_', $key),
        ];

        foreach ($possible_keys as $possible_key) {
            if (! isset($_GET[$possible_key])) {
                continue;
            }

            $raw = wp_unslash($_GET[$possible_key]);

            if (is_array($raw)) {
                $values = array_merge($values, $raw);
            } else {
                $values = array_merge($values, explode(',', (string) $raw));
            }
        }

        $values = array_map('sanitize_title', $values);
        $values = array_filter(array_unique($values));

        return array_values($values);
    }
}

if (! function_exists('tmd_catalog_get_first_term_name')) {
    function tmd_catalog_get_first_term_name($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}

if (! function_exists('tmd_catalog_meta_or_term')) {
    function tmd_catalog_meta_or_term($post_id, $meta_key, $taxonomy = '') {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        if ($taxonomy) {
            return tmd_catalog_get_first_term_name($post_id, $taxonomy);
        }

        return '';
    }
}
/* TMD_CUSTOM_EQUIPMENT_FILTERS_END */

/* TMD_SAFE_FILTER_QUERY_START */
add_action('init', function () {
    remove_shortcode('tmd_equipment_grid');
    remove_shortcode('tmd_equipment_filters');

    add_shortcode('tmd_equipment_grid', function ($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
        ], $atts, 'tmd_equipment_grid');

        $tax_map = [
            'tipo-equipo' => 'tmd_tipo_equipo',
            'marca'       => 'tmd_marca_equipo',
            'energia'     => 'tmd_energia_equipo',
            'condicion'   => 'tmd_condicion_equipo',
            'uso'         => 'tmd_uso_equipo',
        ];

        $tax_query = ['relation' => 'AND'];

        foreach ($tax_map as $url_var => $taxonomy) {
            $selected_terms = tmd_safe_catalog_get_request_values($url_var);

            if (! empty($selected_terms)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $selected_terms,
                    'operator' => 'IN',
                ];
            }
        }

        $query_args = [
            'post_type'      => 'tmd_equipo',
            'post_status'    => 'publish',
            'posts_per_page' => max(1, (int) $atts['per_page']),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (count($tax_query) > 1) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($query_args);

        ob_start();

        if (! $query->have_posts()) {
            echo '<div class="tmd-equipment-empty">No encontramos equipos con los filtros seleccionados.</div>';
            return ob_get_clean();
        }

        echo '<div class="tmd-equipment-grid">';

        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $permalink = get_permalink($post_id);
            $title = get_the_title($post_id);

            $tipo = tmd_safe_catalog_get_first_term_name($post_id, 'tmd_tipo_equipo');
            $marca = tmd_safe_catalog_meta_or_term($post_id, 'tmd_marca', 'tmd_marca_equipo');
            $energia = tmd_safe_catalog_meta_or_term($post_id, 'tmd_energia', 'tmd_energia_equipo');
            $condicion = tmd_safe_catalog_meta_or_term($post_id, 'tmd_condicion', 'tmd_condicion_equipo');
            $capacidad = trim((string) get_post_meta($post_id, 'tmd_capacidad', true));
            $altura = trim((string) get_post_meta($post_id, 'tmd_altura', true));
            $modelo = trim((string) get_post_meta($post_id, 'tmd_modelo', true));
            $imagen_url = trim((string) get_post_meta($post_id, 'tmd_imagen_url', true));

            $excerpt = get_the_excerpt($post_id);
            if (! $excerpt) {
                $excerpt = 'Equipo disponible para validación técnica, compra o alquiler según disponibilidad.';
            }

            echo '<article class="tmd-equipment-card">';

            echo '<a class="tmd-equipment-image" href="' . esc_url($permalink) . '" aria-label="' . esc_attr($title) . '">';
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, 'medium_large');
            } elseif ($imagen_url) {
                echo '<img src="' . esc_url($imagen_url) . '" alt="' . esc_attr($title) . '">';
            }
            echo '</a>';

            echo '<div class="tmd-equipment-body">';

            echo '<div class="tmd-equipment-tags">';
            foreach ([$tipo, $condicion] as $tag) {
                if ($tag !== '') {
                    echo '<span class="tmd-equipment-tag">' . esc_html($tag) . '</span>';
                }
            }
            echo '</div>';

            echo '<h3 class="tmd-equipment-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
            echo '<p class="tmd-equipment-excerpt">' . esc_html(wp_trim_words($excerpt, 20)) . '</p>';

            echo '<div class="tmd-equipment-specs">';

            $specs = [
                'Marca'     => $marca,
                'Modelo'    => $modelo,
                'Energía'   => $energia,
                'Capacidad' => $capacidad,
                'Altura'    => $altura,
                'Condición' => $condicion,
            ];

            foreach ($specs as $label => $value) {
                if ($value === '') {
                    continue;
                }

                echo '<div class="tmd-equipment-spec">';
                echo '<span>' . esc_html($label) . '</span>';
                echo '<strong>' . esc_html($value) . '</strong>';
                echo '</div>';
            }

            echo '</div>';

            echo '<div class="tmd-equipment-actions">';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-primary" href="' . esc_url($permalink) . '">Ver ficha</a>';
            echo '<a class="tmd-equipment-btn tmd-equipment-btn-secondary" href="' . esc_url(home_url('/nosotros/contacto/?equipo=' . rawurlencode($title))) . '">Cotizar</a>';
            echo '</div>';

            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();

        return ob_get_clean();
    });

    add_shortcode('tmd_equipment_filters', function ($atts) {
        $atts = shortcode_atts([
            'set_id' => 581,
        ], $atts, 'tmd_equipment_filters');

        $set_id = absint($atts['set_id']);

        $fields = get_posts([
            'post_type'      => 'filter-field',
            'post_status'    => 'publish',
            'post_parent'    => $set_id,
            'posts_per_page' => 20,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if (empty($fields)) {
            return '<div class="tmd-equipment-filter-empty">No hay filtros configurados todavía.</div>';
        }

        ob_start();

        echo '<form class="tmd-equipment-filters-form" method="get" action="' . esc_url(home_url('/equipos/')) . '">';

        echo '<div class="tmd-equipment-active-filters">';

        $has_active = false;

        foreach ($fields as $field) {
            $url_var = $field->post_name;
            $param_key = tmd_safe_catalog_param_key($url_var);
            $selected = tmd_safe_catalog_get_request_values($url_var);
            $taxonomy = tmd_safe_catalog_get_filter_taxonomy($field);

            if (empty($selected) || ! $taxonomy) {
                continue;
            }

            foreach ($selected as $slug) {
                $term = get_term_by('slug', $slug, $taxonomy);
                $label = $term && ! is_wp_error($term) ? $term->name : $slug;

                echo '<span class="tmd-equipment-active-chip">' . esc_html($field->post_title . ': ' . $label) . '</span>';
                $has_active = true;
            }
        }

        if ($has_active) {
            echo '<a class="tmd-equipment-clear-filters" href="' . esc_url(home_url('/equipos/')) . '">Limpiar filtros</a>';
        }

        echo '</div>';

        foreach ($fields as $field) {
            $taxonomy = tmd_safe_catalog_get_filter_taxonomy($field);

            if (! $taxonomy || ! taxonomy_exists($taxonomy)) {
                continue;
            }

            $url_var = $field->post_name;
            $param_key = tmd_safe_catalog_param_key($url_var);
            $selected = tmd_safe_catalog_get_request_values($url_var);

            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            echo '<fieldset class="tmd-equipment-filter-group">';
            echo '<legend>' . esc_html($field->post_title) . '</legend>';

            foreach ($terms as $term) {
                $input_id = 'tmd-filter-' . sanitize_html_class($param_key . '-' . $term->slug);
                $checked = in_array($term->slug, $selected, true);

                echo '<label class="tmd-equipment-filter-option" for="' . esc_attr($input_id) . '">';
                echo '<input id="' . esc_attr($input_id) . '" type="checkbox" name="' . esc_attr($param_key) . '[]" value="' . esc_attr($term->slug) . '"' . checked($checked, true, false) . ' onchange="this.form.submit()">';
                echo '<span>' . esc_html($term->name) . '</span>';
                echo '<em>' . esc_html((string) $term->count) . '</em>';
                echo '</label>';
            }

            echo '</fieldset>';
        }

        echo '<noscript><button class="tmd-equipment-apply-filters" type="submit">Aplicar filtros</button></noscript>';
        echo '</form>';

        return ob_get_clean();
    });
}, 999);

if (! function_exists('tmd_safe_catalog_param_key')) {
    function tmd_safe_catalog_param_key($key) {
        return 'tmdf_' . str_replace('-', '_', sanitize_key($key));
    }
}

if (! function_exists('tmd_safe_catalog_get_request_values')) {
    function tmd_safe_catalog_get_request_values($key) {
        $values = [];

        $safe_key = tmd_safe_catalog_param_key($key);

        $possible_keys = [
            $safe_key,
            str_replace('-', '_', $key),
            $key,
        ];

        foreach ($possible_keys as $possible_key) {
            if (! isset($_GET[$possible_key])) {
                continue;
            }

            $raw = wp_unslash($_GET[$possible_key]);

            if (is_array($raw)) {
                $values = array_merge($values, $raw);
            } else {
                $values = array_merge($values, explode(',', (string) $raw));
            }
        }

        $values = array_map('sanitize_title', $values);
        $values = array_filter(array_unique($values));

        return array_values($values);
    }
}

if (! function_exists('tmd_safe_catalog_get_filter_taxonomy')) {
    function tmd_safe_catalog_get_filter_taxonomy($field) {
        $settings = maybe_unserialize($field->post_content);

        if (is_array($settings) && ! empty($settings['e_name'])) {
            return sanitize_key($settings['e_name']);
        }

        if (! empty($field->post_excerpt) && strpos($field->post_excerpt, 'taxonomy_') === 0) {
            return sanitize_key(substr($field->post_excerpt, 9));
        }

        return '';
    }
}

if (! function_exists('tmd_safe_catalog_get_first_term_name')) {
    function tmd_safe_catalog_get_first_term_name($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}

if (! function_exists('tmd_safe_catalog_meta_or_term')) {
    function tmd_safe_catalog_meta_or_term($post_id, $meta_key, $taxonomy = '') {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        if ($taxonomy) {
            return tmd_safe_catalog_get_first_term_name($post_id, $taxonomy);
        }

        return '';
    }
}
/* TMD_SAFE_FILTER_QUERY_END */

/* TMD_SINGLE_EQUIPO_START */
add_action('wp_enqueue_scripts', function () {
    if (! is_singular('tmd_equipo')) {
        return;
    }

    $single_css = get_stylesheet_directory() . '/assets/css/tmd-single-equipo.css';

    wp_enqueue_style(
        'tmd-single-equipo',
        get_stylesheet_directory_uri() . '/assets/css/tmd-single-equipo.css',
        [],
        file_exists($single_css) ? filemtime($single_css) : '1.0.0'
    );
}, 70);
/* TMD_SINGLE_EQUIPO_END */

/* TMD_ADMIN_EQUIPO_COLUMNS_START */
add_filter('manage_tmd_equipo_posts_columns', function ($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ($key === 'title') {
            $new_columns['tmd_marca_admin'] = 'Marca';
            $new_columns['tmd_modelo_admin'] = 'Modelo';
            $new_columns['tmd_capacidad_admin'] = 'Capacidad';
            $new_columns['tmd_altura_admin'] = 'Altura';
            $new_columns['tmd_energia_admin'] = 'Energía';
            $new_columns['tmd_condicion_admin'] = 'Condición';
            $new_columns['tmd_tipo_admin'] = 'Tipo';
        }
    }

    return $new_columns;
});

add_action('manage_tmd_equipo_posts_custom_column', function ($column, $post_id) {
    if ($column === 'tmd_marca_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_marca', true) ?: tmd_admin_first_term($post_id, 'tmd_marca_equipo'));
    }

    if ($column === 'tmd_modelo_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_modelo', true));
    }

    if ($column === 'tmd_capacidad_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_capacidad', true));
    }

    if ($column === 'tmd_altura_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_altura', true));
    }

    if ($column === 'tmd_energia_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_energia', true) ?: tmd_admin_first_term($post_id, 'tmd_energia_equipo'));
    }

    if ($column === 'tmd_condicion_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_condicion', true) ?: tmd_admin_first_term($post_id, 'tmd_condicion_equipo'));
    }

    if ($column === 'tmd_tipo_admin') {
        echo esc_html(tmd_admin_first_term($post_id, 'tmd_tipo_equipo'));
    }
}, 10, 2);

if (! function_exists('tmd_admin_first_term')) {
    function tmd_admin_first_term($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}
/* TMD_ADMIN_EQUIPO_COLUMNS_END */

/* TMD_ADMIN_EQUIPO_COLUMNS_CLEAN_START */
add_filter('manage_tmd_equipo_posts_columns', function ($columns) {
    $remove_columns = [
        'taxonomy-tmd_tipo_equipo',
        'taxonomy-tmd_marca_equipo',
        'taxonomy-tmd_energia_equipo',
        'taxonomy-tmd_condicion_equipo',
        'taxonomy-tmd_uso_equipo',
        'taxonomy-tmd_compatibilidad',
    ];

    foreach ($remove_columns as $column_key) {
        if (isset($columns[$column_key])) {
            unset($columns[$column_key]);
        }
    }

    return $columns;
}, 99);

add_action('admin_head-edit.php', function () {
    $screen = get_current_screen();

    if (! $screen || $screen->post_type !== 'tmd_equipo') {
        return;
    }
    ?>
    <style>
        .post-type-tmd_equipo .wp-list-table {
            table-layout: auto;
        }

        .post-type-tmd_equipo .column-title {
            width: 210px;
        }

        .post-type-tmd_equipo .column-tmd_marca_admin,
        .post-type-tmd_equipo .column-tmd_modelo_admin,
        .post-type-tmd_equipo .column-tmd_capacidad_admin,
        .post-type-tmd_equipo .column-tmd_altura_admin,
        .post-type-tmd_equipo .column-tmd_energia_admin,
        .post-type-tmd_equipo .column-tmd_condicion_admin,
        .post-type-tmd_equipo .column-tmd_tipo_admin {
            width: 110px;
        }

        .post-type-tmd_equipo .column-date {
            width: 135px;
        }
    </style>
    <?php
});
/* TMD_ADMIN_EQUIPO_COLUMNS_CLEAN_END */

/* TMD_ENERGY_CATALOG_START */
add_action('wp_enqueue_scripts', function () {
    $css = get_stylesheet_directory() . '/assets/css/tmd-energy-catalog.css';

    wp_enqueue_style(
        'tmd-energy-catalog',
        get_stylesheet_directory_uri() . '/assets/css/tmd-energy-catalog.css',
        [],
        file_exists($css) ? filemtime($css) : '1.0.0'
    );
}, 75);

add_action('enqueue_block_editor_assets', function () {
    $css = get_stylesheet_directory() . '/assets/css/tmd-energy-catalog.css';

    wp_enqueue_style(
        'tmd-energy-catalog-editor',
        get_stylesheet_directory_uri() . '/assets/css/tmd-energy-catalog.css',
        [],
        file_exists($css) ? filemtime($css) : '1.0.0'
    );
});

add_action('init', function () {
    remove_shortcode('tmd_energy_grid');
    remove_shortcode('tmd_energy_filters');

    add_shortcode('tmd_energy_grid', function ($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
        ], $atts, 'tmd_energy_grid');

        $tax_map = [
            'categoria'       => 'tmd_categoria_energia',
            'tipo-bateria'    => 'tmd_tipo_bateria',
            'marca-cargador'  => 'tmd_marca_cargador',
            'voltaje'         => 'tmd_voltaje',
            'compatibilidad'  => 'tmd_compatibilidad',
        ];

        $tax_query = ['relation' => 'AND'];

        foreach ($tax_map as $url_var => $taxonomy) {
            $selected_terms = tmde_get_request_values($url_var);

            if (! empty($selected_terms)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $selected_terms,
                    'operator' => 'IN',
                ];
            }
        }

        $query_args = [
            'post_type'      => 'tmd_energia',
            'post_status'    => 'publish',
            'posts_per_page' => max(1, (int) $atts['per_page']),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (count($tax_query) > 1) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($query_args);

        ob_start();

        if (! $query->have_posts()) {
            echo '<div class="tmde-empty">No encontramos soluciones de energía con los filtros seleccionados.</div>';
            return ob_get_clean();
        }

        echo '<div class="tmde-grid">';

        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $title = get_the_title($post_id);
            $permalink = get_permalink($post_id);

            $categoria = tmde_meta_or_terms($post_id, 'tmd_categoria', 'tmd_categoria_energia');
            $marca = tmde_meta_or_terms($post_id, 'tmd_marca', 'tmd_marca_cargador');
            $voltaje = tmde_meta_or_terms($post_id, 'tmd_voltaje', 'tmd_voltaje');
            $tipo_bateria = tmde_terms_names($post_id, 'tmd_tipo_bateria');
            $tecnologia = trim((string) get_post_meta($post_id, 'tmd_tecnologia', true));
            $capacidad_ah = trim((string) get_post_meta($post_id, 'tmd_capacidad_ah', true));
            $amperaje = trim((string) get_post_meta($post_id, 'tmd_amperaje', true));
            $condicion = trim((string) get_post_meta($post_id, 'tmd_condicion', true));
            $precio = trim((string) get_post_meta($post_id, 'tmd_precio', true));
            $imagen_url = trim((string) get_post_meta($post_id, 'tmd_imagen_url', true));

            $excerpt = get_the_excerpt($post_id);
            if (! $excerpt) {
                $excerpt = 'Solución de energía industrial disponible para validación técnica y cotización.';
            }

            echo '<article class="tmde-card">';

            echo '<a class="tmde-image" href="' . esc_url($permalink) . '" aria-label="' . esc_attr($title) . '">';
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, 'medium_large');
            } elseif ($imagen_url) {
                echo '<img src="' . esc_url($imagen_url) . '" alt="' . esc_attr($title) . '">';
            }
            echo '</a>';

            echo '<div class="tmde-card-body">';

            echo '<div class="tmde-tags">';
            foreach ([$categoria, $voltaje] as $tag) {
                if ($tag !== '') {
                    echo '<span>' . esc_html($tag) . '</span>';
                }
            }
            echo '</div>';

            echo '<h3><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
            echo '<p class="tmde-excerpt">' . esc_html(wp_trim_words($excerpt, 18)) . '</p>';

            echo '<div class="tmde-specs">';

            $specs = [
                'Marca'       => $marca,
                'Tipo'        => $tipo_bateria ?: $categoria,
                'Tecnología'  => $tecnologia,
                'Voltaje'     => $voltaje,
                'Capacidad'   => $capacidad_ah,
                'Amperaje'    => $amperaje,
                'Condición'   => $condicion,
                'Precio'      => $precio,
            ];

            foreach ($specs as $label => $value) {
                if ($value === '') {
                    continue;
                }

                echo '<div>';
                echo '<span>' . esc_html($label) . '</span>';
                echo '<strong>' . esc_html($value) . '</strong>';
                echo '</div>';
            }

            echo '</div>';

            echo '<div class="tmde-actions">';
            echo '<a class="tmde-btn tmde-btn-primary" href="' . esc_url($permalink) . '">Ver ficha</a>';
            echo '<a class="tmde-btn tmde-btn-secondary" href="' . esc_url(home_url('/nosotros/contacto/?tmd_cotizacion_energia=' . rawurlencode($title))) . '">Cotizar</a>';
            echo '</div>';

            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();

        return ob_get_clean();
    });

    add_shortcode('tmd_energy_filters', function ($atts) {
        $atts = shortcode_atts([
            'set_id' => 0,
        ], $atts, 'tmd_energy_filters');

        $set_id = absint($atts['set_id']);

        if (! $set_id) {
            $set_id = tmde_get_filter_set_id();
        }

        $fields = get_posts([
            'post_type'      => 'filter-field',
            'post_status'    => 'publish',
            'post_parent'    => $set_id,
            'posts_per_page' => 20,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if (empty($fields)) {
            return '<div class="tmde-filter-empty">No hay filtros de energía configurados todavía.</div>';
        }

        ob_start();

        echo '<form class="tmde-filters-form" method="get" action="' . esc_url(home_url('/energia/')) . '">';

        echo '<div class="tmde-active-filters">';

        $has_active = false;

        foreach ($fields as $field) {
            $url_var = $field->post_name;
            $selected = tmde_get_request_values($url_var);
            $taxonomy = tmde_filter_taxonomy($field);

            if (empty($selected) || ! $taxonomy) {
                continue;
            }

            foreach ($selected as $slug) {
                $term = get_term_by('slug', $slug, $taxonomy);
                $label = $term && ! is_wp_error($term) ? $term->name : $slug;

                echo '<span class="tmde-active-chip">' . esc_html($field->post_title . ': ' . $label) . '</span>';
                $has_active = true;
            }
        }

        if ($has_active) {
            echo '<a class="tmde-clear-filters" href="' . esc_url(home_url('/energia/')) . '">Limpiar filtros</a>';
        }

        echo '</div>';

        foreach ($fields as $field) {
            $taxonomy = tmde_filter_taxonomy($field);

            if (! $taxonomy || ! taxonomy_exists($taxonomy)) {
                continue;
            }

            $url_var = $field->post_name;
            $param_key = tmde_param_key($url_var);
            $selected = tmde_get_request_values($url_var);

            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            echo '<fieldset class="tmde-filter-group">';
            echo '<legend>' . esc_html($field->post_title) . '</legend>';

            foreach ($terms as $term) {
                $input_id = 'tmde-filter-' . sanitize_html_class($param_key . '-' . $term->slug);
                $checked = in_array($term->slug, $selected, true);

                echo '<label class="tmde-filter-option" for="' . esc_attr($input_id) . '">';
                echo '<input id="' . esc_attr($input_id) . '" type="checkbox" name="' . esc_attr($param_key) . '[]" value="' . esc_attr($term->slug) . '"' . checked($checked, true, false) . ' onchange="this.form.submit()">';
                echo '<span>' . esc_html($term->name) . '</span>';
                echo '<em>' . esc_html((string) $term->count) . '</em>';
                echo '</label>';
            }

            echo '</fieldset>';
        }

        echo '<noscript><button class="tmde-apply-filters" type="submit">Aplicar filtros</button></noscript>';
        echo '</form>';

        return ob_get_clean();
    });
}, 999);

if (! function_exists('tmde_get_filter_set_id')) {
    function tmde_get_filter_set_id() {
        $sets = get_posts([
            'post_type'      => 'filter-set',
            'post_status'    => 'publish',
            'title'          => 'Catálogo Energía',
            'posts_per_page' => 1,
        ]);

        return ! empty($sets) ? (int) $sets[0]->ID : 0;
    }
}

if (! function_exists('tmde_param_key')) {
    function tmde_param_key($key) {
        return 'tmde_' . str_replace('-', '_', sanitize_key($key));
    }
}

if (! function_exists('tmde_get_request_values')) {
    function tmde_get_request_values($key) {
        $values = [];
        $safe_key = tmde_param_key($key);

        $possible_keys = [
            $safe_key,
            str_replace('-', '_', $key),
            $key,
        ];

        foreach ($possible_keys as $possible_key) {
            if (! isset($_GET[$possible_key])) {
                continue;
            }

            $raw = wp_unslash($_GET[$possible_key]);

            if (is_array($raw)) {
                $values = array_merge($values, $raw);
            } else {
                $values = array_merge($values, explode(',', (string) $raw));
            }
        }

        $values = array_map('sanitize_title', $values);
        $values = array_filter(array_unique($values));

        return array_values($values);
    }
}

if (! function_exists('tmde_filter_taxonomy')) {
    function tmde_filter_taxonomy($field) {
        $settings = maybe_unserialize($field->post_content);

        if (is_array($settings) && ! empty($settings['e_name'])) {
            return sanitize_key($settings['e_name']);
        }

        if (! empty($field->post_excerpt) && strpos($field->post_excerpt, 'taxonomy_') === 0) {
            return sanitize_key(substr($field->post_excerpt, 9));
        }

        return '';
    }
}

if (! function_exists('tmde_terms_names')) {
    function tmde_terms_names($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return implode(', ', wp_list_pluck($terms, 'name'));
    }
}

if (! function_exists('tmde_meta_or_terms')) {
    function tmde_meta_or_terms($post_id, $meta_key, $taxonomy = '') {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        if ($taxonomy) {
            return tmde_terms_names($post_id, $taxonomy);
        }

        return '';
    }
}
/* TMD_ENERGY_CATALOG_END */

/* TMD_ADMIN_ENERGIA_COLUMNS_START */
add_filter('manage_tmd_energia_posts_columns', function ($columns) {
    $clean_columns = [];

    foreach ($columns as $key => $label) {
        if (in_array($key, [
            'taxonomy-tmd_categoria_energia',
            'taxonomy-tmd_tipo_bateria',
            'taxonomy-tmd_marca_cargador',
            'taxonomy-tmd_voltaje',
            'taxonomy-tmd_compatibilidad',
            'rank_math_seo_details',
            'rank_math_title',
            'rank_math_description',
            'rank_math_focus_keyword',
            'rank_math_schema',
            'rank_math_links',
        ], true)) {
            continue;
        }

        $clean_columns[$key] = $label;

        if ($key === 'title') {
            $clean_columns['tmd_energia_categoria_admin'] = 'Categoría';
            $clean_columns['tmd_energia_marca_admin'] = 'Marca';
            $clean_columns['tmd_energia_voltaje_admin'] = 'Voltaje';
            $clean_columns['tmd_energia_tecnologia_admin'] = 'Tecnología';
            $clean_columns['tmd_energia_capacidad_admin'] = 'Capacidad / detalle';
            $clean_columns['tmd_energia_amperaje_admin'] = 'Amperaje';
            $clean_columns['tmd_energia_condicion_admin'] = 'Condición';
        }
    }

    return $clean_columns;
}, 100);

add_action('manage_tmd_energia_posts_custom_column', function ($column, $post_id) {
    if ($column === 'tmd_energia_categoria_admin') {
        echo esc_html(tmd_admin_energia_meta_or_term($post_id, 'tmd_categoria', 'tmd_categoria_energia'));
    }

    if ($column === 'tmd_energia_marca_admin') {
        echo esc_html(tmd_admin_energia_meta_or_term($post_id, 'tmd_marca', 'tmd_marca_cargador'));
    }

    if ($column === 'tmd_energia_voltaje_admin') {
        echo esc_html(tmd_admin_energia_meta_or_term($post_id, 'tmd_voltaje', 'tmd_voltaje'));
    }

    if ($column === 'tmd_energia_tecnologia_admin') {
        echo esc_html(tmd_admin_energia_meta_or_term($post_id, 'tmd_tecnologia', 'tmd_tipo_bateria'));
    }

    if ($column === 'tmd_energia_capacidad_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_capacidad_ah', true));
    }

    if ($column === 'tmd_energia_amperaje_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_amperaje', true));
    }

    if ($column === 'tmd_energia_condicion_admin') {
        echo esc_html(get_post_meta($post_id, 'tmd_condicion', true));
    }
}, 10, 2);

if (! function_exists('tmd_admin_energia_first_term')) {
    function tmd_admin_energia_first_term($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}

if (! function_exists('tmd_admin_energia_meta_or_term')) {
    function tmd_admin_energia_meta_or_term($post_id, $meta_key, $taxonomy) {
        $value = trim((string) get_post_meta($post_id, $meta_key, true));

        if ($value !== '') {
            return $value;
        }

        return tmd_admin_energia_first_term($post_id, $taxonomy);
    }
}

add_action('admin_head-edit.php', function () {
    $screen = get_current_screen();

    if (! $screen || $screen->post_type !== 'tmd_energia') {
        return;
    }
    ?>
    <style>
        .post-type-tmd_energia .wp-list-table {
            table-layout: auto;
        }

        .post-type-tmd_energia .column-title {
            width: 230px;
        }

        .post-type-tmd_energia .column-tmd_energia_categoria_admin,
        .post-type-tmd_energia .column-tmd_energia_marca_admin,
        .post-type-tmd_energia .column-tmd_energia_voltaje_admin,
        .post-type-tmd_energia .column-tmd_energia_tecnologia_admin,
        .post-type-tmd_energia .column-tmd_energia_capacidad_admin,
        .post-type-tmd_energia .column-tmd_energia_amperaje_admin,
        .post-type-tmd_energia .column-tmd_energia_condicion_admin {
            width: 120px;
        }

        .post-type-tmd_energia .column-date {
            width: 135px;
        }

        .post-type-tmd_energia .column-rank_math_seo_details,
        .post-type-tmd_energia .column-rank_math_title,
        .post-type-tmd_energia .column-rank_math_description,
        .post-type-tmd_energia .column-rank_math_focus_keyword,
        .post-type-tmd_energia .column-rank_math_schema,
        .post-type-tmd_energia .column-rank_math_links {
            display: none !important;
        }
    </style>
    <?php
});
/* TMD_ADMIN_ENERGIA_COLUMNS_END */

/* TMD_ENERGY_PRETTY_URLS_START */
add_action('init', function () {
    add_rewrite_rule(
        '^energia/([^/]+)/?$',
        'index.php?tmd_energia=$matches[1]',
        'top'
    );
}, 20);

add_filter('post_type_link', function ($post_link, $post) {
    if (! $post || $post->post_type !== 'tmd_energia') {
        return $post_link;
    }

    return home_url('/energia/' . $post->post_name . '/');
}, 10, 2);

add_action('template_redirect', function () {
    if (! is_singular('tmd_energia')) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    if (strpos($request_uri, '/tmd_energia/') !== 0) {
        return;
    }

    wp_safe_redirect(get_permalink(), 301);
    exit;
});
/* TMD_ENERGY_PRETTY_URLS_END */

/* TMD_EQUIPO_PRETTY_URLS_START */
add_action('init', function () {
    add_rewrite_rule(
        '^equipos/([^/]+)/?$',
        'index.php?tmd_equipo=$matches[1]',
        'top'
    );
}, 20);

add_filter('post_type_link', function ($post_link, $post) {
    if (! $post || $post->post_type !== 'tmd_equipo') {
        return $post_link;
    }

    return home_url('/equipos/' . $post->post_name . '/');
}, 10, 2);

add_action('template_redirect', function () {
    if (! is_singular('tmd_equipo')) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    if (strpos($request_uri, '/tmd_equipo/') !== 0) {
        return;
    }

    wp_safe_redirect(get_permalink(), 301);
    exit;
});
/* TMD_EQUIPO_PRETTY_URLS_END */

/* TMD_CONTACT_EQUIPO_QUERY_FIX_START */
add_filter('request', function ($query_vars) {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    if (
        strpos($request_uri, '/nosotros/contacto/') !== false
        && isset($_GET['equipo'])
        && isset($query_vars['equipo'])
    ) {
        unset($query_vars['equipo']);
    }

    return $query_vars;
}, 1);
/* TMD_CONTACT_EQUIPO_QUERY_FIX_END */





/* TMD_CONTACT_ENERGIA_REDIRECT_START */
add_action('init', function () {
    if (empty($_GET['energia'])) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = trim((string) parse_url($request_uri, PHP_URL_PATH), '/');

    if ($path !== 'nosotros/contacto') {
        return;
    }

    $energia = sanitize_text_field(wp_unslash($_GET['energia']));

    $target = add_query_arg(
        'tmd_cotizacion_energia',
        $energia,
        home_url('/nosotros/contacto/')
    );

    wp_safe_redirect($target, 302);
    exit;
}, 0);
/* TMD_CONTACT_ENERGIA_REDIRECT_END */

/* TMD_CONTACT_CF7_PREFILL_START */
add_filter('wpcf7_form_tag', function ($tag) {
    if (! is_page(57) || ! $tag instanceof WPCF7_FormTag) {
        return $tag;
    }

    $equipment = isset($_GET['equipo'])
        ? sanitize_text_field(wp_unslash($_GET['equipo']))
        : '';
    $energy = isset($_GET['tmd_cotizacion_energia'])
        ? sanitize_text_field(wp_unslash($_GET['tmd_cotizacion_energia']))
        : '';

    if ($equipment === '' && $energy === '') {
        return $tag;
    }

    $type = $equipment !== '' ? 'Equipo' : 'Energía';
    $product = $equipment !== '' ? $equipment : $energy;
    $values = [
        'tmd_tipo_cotizacion' => $type,
        'tmd_cotizacion' => $product,
        'tmd_url_origen' => esc_url_raw(home_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/nosotros/contacto/'))),
    ];

    if (isset($values[$tag->name])) {
        $tag->values = [$values[$tag->name]];
        $tag->raw_values = [$values[$tag->name]];
    }

    if ($tag->name === 'service') {
        $tag->options = array_values(array_filter(
            $tag->options,
            static fn($option) => ! str_starts_with($option, 'default:')
        ));
        $tag->options[] = $type === 'Energía' ? 'default:5' : 'default:3';
    }

    if ($tag->name === 'message') {
        $message = 'Hola, quiero recibir información sobre: ' . $product;
        $tag->options = array_values(array_filter(
            $tag->options,
            static fn($option) => ! in_array($option, ['placeholder', 'watermark'], true)
        ));
        $tag->values = [$message];
        $tag->raw_values = [$message];
        $tag->content = $message;
    }

    return $tag;
}, 20);
/* TMD_CONTACT_CF7_PREFILL_END */



/* TMD_CONTACT_PAGE_POLISH_START */
add_action('wp_enqueue_scripts', function () {
    if (! is_page(57)) {
        return;
    }

    wp_enqueue_script('jquery');

    $css = <<<'CSS'
body.page-id-57 .tmd-page {
  color: #1f3046;
  background: #f7f9fc;
}

body.page-id-57 .tmd-wrap {
  max-width: 1180px;
  margin: 0 auto;
  padding-left: 24px;
  padding-right: 24px;
}

body.page-id-57 .tmd-page-hero {
  padding: 56px 0 34px;
  background: linear-gradient(180deg, #f7f9fc 0%, #ffffff 100%);
}

body.page-id-57 .tmd-page-hero h1 {
  margin: 0 0 16px;
  font-size: clamp(38px, 4vw, 58px);
  line-height: 1.05;
  color: #1d2d44;
}

body.page-id-57 .tmd-page-hero p {
  max-width: 820px;
  margin: 0;
  color: #5e748b;
  font-size: 18px;
  line-height: 1.7;
}

body.page-id-57 .tmd-dark-band {
  margin: 18px 0 0;
  padding: 54px 0;
  background: #262e4f;
}

body.page-id-57 .tmd-band-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 28px;
}

body.page-id-57 .tmd-band-head h2 {
  margin: 0;
  color: #ffffff;
  font-size: clamp(30px, 3vw, 44px);
  line-height: 1.1;
}

body.page-id-57 .tmd-round-btn {
  width: 42px;
  height: 42px;
  border: 0;
  border-radius: 999px;
  background: #ffffff;
  color: #262e4f;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  box-shadow: 0 12px 30px rgba(0, 0, 0, .18);
}

body.page-id-57 .tmd-round-btn:hover {
  background: #ffc33c;
}

body.page-id-57 .tmd-advisors-carousel {
  width: 100%;
}

body.page-id-57 .tmd-advisors-viewport {
  overflow: hidden;
}

body.page-id-57 .tmd-advisors-track {
  display: flex;
  gap: 22px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding: 4px 2px 10px;
  scrollbar-width: none;
}

body.page-id-57 .tmd-advisors-track::-webkit-scrollbar {
  display: none;
}

body.page-id-57 .tmd-advisor-card {
  flex: 0 0 min(360px, 86vw);
  scroll-snap-align: start;
  padding: 28px;
  border-radius: 22px;
  background: #ffffff;
  color: #1d2d44;
  box-shadow: 0 20px 45px rgba(0, 0, 0, .16);
}

body.page-id-57 .tmd-avatar {
  width: 58px;
  height: 58px;
  display: grid;
  place-items: center;
  margin-bottom: 18px;
  border-radius: 18px;
  background: rgba(18, 140, 235, .12);
  color: #128ceb;
  font-weight: 900;
  letter-spacing: .04em;
}

body.page-id-57 .tmd-advisor-card h3 {
  margin: 0 0 12px;
  color: #1d2d44;
  font-size: 26px;
  line-height: 1.15;
}

body.page-id-57 .tmd-advisor-role,
body.page-id-57 .tmd-advisor-phone {
  display: block;
  color: #5e748b;
  font-size: 16px;
  line-height: 1.55;
}

body.page-id-57 .tmd-advisor-phone {
  margin-top: 6px;
  font-weight: 800;
  color: #262e4f;
}

body.page-id-57 .tmd-advisor-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 20px;
}

body.page-id-57 .tmd-call,
body.page-id-57 .tmd-whatsapp {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  padding: 10px 16px;
  border-radius: 999px;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

body.page-id-57 .tmd-call {
  background: #128ceb;
  color: #ffffff;
}

body.page-id-57 .tmd-whatsapp {
  background: #e9f8ef;
  color: #15803d;
}

body.page-id-57 .tmd-contact-grid {
  display: grid;
  grid-template-columns: minmax(0, .95fr) minmax(360px, 1.05fr);
  gap: 34px;
  padding-top: 58px;
  padding-bottom: 76px;
}

body.page-id-57 .tmd-contact-grid h2 {
  margin: 0 0 14px;
  color: #1d2d44;
  font-size: clamp(28px, 3vw, 40px);
  line-height: 1.1;
}

body.page-id-57 .tmd-contact-grid p {
  color: #5e748b;
  font-size: 17px;
  line-height: 1.65;
}

body.page-id-57 .tmd-contact-list {
  display: grid;
  gap: 14px;
  margin-top: 24px;
}

body.page-id-57 .tmd-contact-item {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 18px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(38, 46, 79, .09);
  box-shadow: 0 12px 30px rgba(38, 46, 79, .06);
}

body.page-id-57 .tmd-contact-icon {
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: rgba(255, 195, 60, .22);
  color: #262e4f;
  font-weight: 900;
}

body.page-id-57 .tmd-map-box {
  display: grid;
  place-items: center;
  min-height: 190px;
  margin-top: 18px;
  border-radius: 22px;
  background: linear-gradient(135deg, rgba(18, 140, 235, .14), rgba(255, 195, 60, .28));
  border: 1px solid rgba(18, 140, 235, .18);
  color: #128ceb;
  font-size: 42px;
  text-decoration: none;
}

body.page-id-57 .tmd-form-card {
  padding: 30px;
  border-radius: 24px;
  background: #ffffff;
  border: 1px solid rgba(38, 46, 79, .09);
  box-shadow: 0 22px 55px rgba(38, 46, 79, .12);
}

body.page-id-57 .tmd-contact-grid > .wpcf7 {
  min-width: 0;
}

body.page-id-57 .tmd-contact-grid .wpcf7-form {
  margin: 0;
}

body.page-id-57 .tmd-contact-grid .wpcf7-response-output {
  grid-column: 1 / -1;
  margin: 0;
  border-radius: 12px;
}

body.page-id-57 .tmd-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

body.page-id-57 .tmd-field-wide {
  grid-column: 1 / -1;
}

body.page-id-57 .tmd-field label {
  display: block;
  margin-bottom: 7px;
  color: #262e4f;
  font-size: 14px;
  font-weight: 800;
}

body.page-id-57 .tmd-field input,
body.page-id-57 .tmd-field select,
body.page-id-57 .tmd-field textarea {
  width: 100%;
  min-height: 48px;
  padding: 12px 14px;
  border: 1px solid rgba(94, 116, 139, .28);
  border-radius: 14px;
  background: #f9fbfd;
  color: #1d2d44;
  font: inherit;
}

body.page-id-57 .tmd-field textarea {
  min-height: 132px;
  resize: vertical;
}

body.page-id-57 .tmd-submit {
  min-height: 52px;
  border: 0;
  border-radius: 999px;
  background: #128ceb;
  color: #ffffff;
  font-weight: 900;
  cursor: pointer;
}

body.page-id-57 .tmd-submit:hover {
  background: #0f72bf;
}

body.page-id-57 .tmd-form-status {
  color: #5e748b;
  font-size: 14px;
}

@media (min-width: 1100px) {
  body.page-id-57 .tmd-advisors-track {
    overflow: visible;
  }

  body.page-id-57 .tmd-advisor-card {
    flex: 1 1 0;
  }
}

@media (max-width: 860px) {
  body.page-id-57 .tmd-band-head {
    align-items: flex-start;
    flex-direction: column;
  }

  body.page-id-57 .tmd-contact-grid,
  body.page-id-57 .tmd-form-grid {
    grid-template-columns: 1fr;
  }

  body.page-id-57 .tmd-form-card {
    padding: 22px;
  }
}
CSS;

    wp_register_style('tmd-contact-page-polish-style', false);
    wp_enqueue_style('tmd-contact-page-polish-style');
    wp_add_inline_style('tmd-contact-page-polish-style', $css);

    $js = <<<'JS'
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
      return;
    }

    document.addEventListener('DOMContentLoaded', fn);
  }

  function ensureHiddenField(form, name) {
    var field = form.querySelector('[name="' + name + '"]');

    if (field) {
      return field;
    }

    field = document.createElement('input');
    field.type = 'hidden';
    field.name = name;
    form.appendChild(field);

    return field;
  }

  ready(function () {
    var params = new URLSearchParams(window.location.search);
    var equipo = params.get('equipo') || '';
    var energia = params.get('tmd_cotizacion_energia') || params.get('energia') || '';

    var tipo = '';
    var producto = '';

    if (equipo) {
      tipo = 'Equipo';
      producto = equipo;
    } else if (energia) {
      tipo = 'Energía';
      producto = energia;
    }

    var form = document.querySelector('.tmd-contact-grid .wpcf7-form');

    if (form && producto) {
      var applyQuotation = function () {
        ensureHiddenField(form, 'tmd_tipo_cotizacion').value = tipo;
        ensureHiddenField(form, 'tmd_cotizacion').value = producto;
        ensureHiddenField(form, 'tmd_url_origen').value = window.location.href;

        var service = form.querySelector('[name="service"]');

        if (service) {
          service.value = tipo === 'Energía' ? 'Baterías y cargadores' : 'Venta de equipo';
        }

        var message = form.querySelector('[name="message"]');

        if (message && !message.value) {
          message.value = 'Hola, quiero recibir información sobre: ' + producto;
        }
      };

      applyQuotation();
      document.addEventListener('wpcf7init', applyQuotation);
      document.addEventListener('wpcf7reset', applyQuotation);
      form.addEventListener('reset', function () {
        window.setTimeout(applyQuotation, 0);
      });
      window.addEventListener('load', applyQuotation);
      window.setTimeout(applyQuotation, 1200);

      form.addEventListener('submit', function () {
        applyQuotation();
      });
    }

    var track = document.querySelector('[data-tmd-advisors-track]');
    var prev = document.querySelector('[data-tmd-advisors-prev]');
    var next = document.querySelector('[data-tmd-advisors-next]');

    function stepSize() {
      var card = track ? track.querySelector('[data-tmd-advisors-slide]') : null;

      if (!card) {
        return 340;
      }

      return card.getBoundingClientRect().width + 22;
    }

    if (track && prev && next) {
      prev.addEventListener('click', function () {
        track.scrollBy({ left: -stepSize(), behavior: 'smooth' });
      });

      next.addEventListener('click', function () {
        track.scrollBy({ left: stepSize(), behavior: 'smooth' });
      });
    }
  });
})();
JS;

    wp_add_inline_script('jquery', $js);
}, 100);
/* TMD_CONTACT_PAGE_POLISH_END */

/* TMD_CONTACT_FINAL_VISUAL_TWEAKS_START */
add_action('wp_enqueue_scripts', function () {
    if (! is_page(57)) {
        return;
    }

    wp_enqueue_script('jquery');

    $css = <<<'CSS'
/* Oculta el título automático de Blocksy para no duplicar "Contacto" */
body.page-id-57 .entry-header,
body.page-id-57 .ct-page-title,
body.page-id-57 .ct-hero-section,
body.page-id-57 .page-title {
  display: none !important;
}

/* Ajuste del hero real de la página */
body.page-id-57 .tmd-page-hero {
  padding-top: 64px !important;
  padding-bottom: 40px !important;
}

body.page-id-57 .tmd-page-hero h1 {
  margin-bottom: 18px !important;
}

/* El bloque de producto queda dentro del hero */
body.page-id-57 .tmd-page-hero .tmd-contact-source-box-server {
  max-width: 100%;
  margin: 30px 0 0 !important;
}

/* Ajuste de separación entre hero y asesores */
body.page-id-57 .tmd-dark-band {
  margin-top: 0 !important;
  padding-top: 48px !important;
  padding-bottom: 48px !important;
}

/* Ajuste suave del formulario para que no quede tan pegado al final */
body.page-id-57 .tmd-contact-grid {
  padding-top: 48px !important;
  padding-bottom: 48px !important;
}

@media (max-width: 640px) {
  body.page-id-57 .tmd-page-hero {
    padding-top: 40px !important;
    padding-bottom: 32px !important;
  }

  body.page-id-57 .tmd-dark-band {
    padding-top: 32px !important;
    padding-bottom: 32px !important;
  }

  body.page-id-57 .tmd-contact-grid {
    padding-top: 32px !important;
    padding-bottom: 32px !important;
  }
}
CSS;

    wp_register_style('tmd-contact-final-visual-tweaks-style', false);
    wp_enqueue_style('tmd-contact-final-visual-tweaks-style');
    wp_add_inline_style('tmd-contact-final-visual-tweaks-style', $css);

    $js = <<<'JS'
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
      return;
    }

    document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var sourceBox = document.querySelector('.tmd-contact-source-box-server');
    var heroWrap = document.querySelector('.tmd-page-hero .tmd-wrap');

    if (sourceBox && heroWrap && !heroWrap.contains(sourceBox)) {
      heroWrap.appendChild(sourceBox);
    }
  });
})();
JS;

    wp_add_inline_script('jquery', $js);
}, 120);
/* TMD_CONTACT_FINAL_VISUAL_TWEAKS_END */

/* TMD_CONTACT_ADVISOR_PHOTOS_START */
add_action('wp_enqueue_scripts', function () {
    if (! is_page(57)) {
        return;
    }

    $css = <<<'CSS'
/* Ocultar texto "Solicitud desde catálogo", mantener solo Equipo/Energía */
body.page-id-57 .tmd-contact-source-box-server strong {
  display: none !important;
}

body.page-id-57 .tmd-contact-source-box-server {
  padding: 18px 22px !important;
}

body.page-id-57 .tmd-contact-source-box-server span {
  margin: 0 !important;
}

/* Fotos reales de asesores */
body.page-id-57 .tmd-avatar.tmd-avatar-photo {
  width: 82px !important;
  height: 82px !important;
  padding: 0 !important;
  overflow: hidden !important;
  border-radius: 20px !important;
  background: #e7f2ff !important;
  display: block !important;
}

body.page-id-57 .tmd-avatar.tmd-avatar-photo img {
  width: 100% !important;
  height: 100% !important;
  display: block !important;
  object-fit: cover !important;
  object-position: center center !important;
}

/* Un poco más de aire visual en la tarjeta */
body.page-id-57 .tmd-advisor-card {
  padding-top: 30px !important;
}

body.page-id-57 .tmd-advisor-card h3 {
  margin-top: 18px !important;
}
CSS;

    wp_register_style('tmd-contact-advisor-photos-style', false);
    wp_enqueue_style('tmd-contact-advisor-photos-style');
    wp_add_inline_style('tmd-contact-advisor-photos-style', $css);
}, 130);
/* TMD_CONTACT_ADVISOR_PHOTOS_END */

/* TMD_ENERGY_PAGES_REWRITE_FIX_START */
add_action('init', function () {
    /*
     * Estas páginas deben resolver como páginas reales.
     * Evita que la regla /energia/<slug>/ de fichas individuales las capture como tmd_energia.
     */
    add_rewrite_rule(
        '^energia/baterias/?$',
        'index.php?pagename=energia/baterias',
        'top'
    );

    add_rewrite_rule(
        '^energia/cargadores/?$',
        'index.php?pagename=energia/cargadores',
        'top'
    );

    add_rewrite_rule(
        '^energia/bms/?$',
        'index.php?pagename=energia/bms',
        'top'
    );

    add_rewrite_rule(
        '^energia/baterias/litio/?$',
        'index.php?pagename=energia/baterias/litio',
        'top'
    );

    add_rewrite_rule(
        '^energia/baterias/plomo/?$',
        'index.php?pagename=energia/baterias/plomo',
        'top'
    );
}, 1);
/* TMD_ENERGY_PAGES_REWRITE_FIX_END */

/* TMD_HIDE_TRABAJA_TITLE_START */
add_action('wp_enqueue_scripts', function () {
    if (! is_page(273)) {
        return;
    }

    $css = <<<'CSS'
body.page-id-273 .entry-header,
body.page-id-273 .ct-page-title,
body.page-id-273 .ct-hero-section,
body.page-id-273 h1.entry-title,
body.page-id-273 .page-title {
  display: none !important;
}
CSS;

    wp_register_style('tmd-hide-trabaja-title-style', false);
    wp_enqueue_style('tmd-hide-trabaja-title-style');
    wp_add_inline_style('tmd-hide-trabaja-title-style', $css);
}, 150);
/* TMD_HIDE_TRABAJA_TITLE_END */

/* TMD_BLOG_START */
require_once get_stylesheet_directory() . '/inc/tmd-blog.php';
/* TMD_BLOG_END */

/* TMD_BRAND_CAROUSEL_START */
require_once get_stylesheet_directory() . '/inc/tmd-brand-carousel.php';
/* TMD_BRAND_CAROUSEL_END */

/* TMD_INVENTORY_API_START */
require_once get_stylesheet_directory() . '/inc/tmd-inventory-api.php';
/* TMD_INVENTORY_API_END */

/* TMD_LOGO_CAROUSEL_AUTOPLAY_START */
add_action('wp_footer', function () {
    if (is_admin() || ! is_front_page()) {
        return;
    }
    ?>
    <script>
    (function () {
      const INTERVAL_MS = 3000;

      function findCarousel() {
        return document.querySelector('[data-tmd-brand-carousel]');
      }

      function findNextButton(carousel) {
        return carousel.querySelector('[data-brand-next]');
      }

      function initLogoCarouselAutoplay() {
        const carousel = findCarousel();

        if (!carousel || carousel.dataset.tmdAutoplayReady === '1') {
          return;
        }

        const nextButton = findNextButton(carousel);

        if (!nextButton) {
          return;
        }

        carousel.dataset.tmdAutoplayReady = '1';

        let paused = false;

        carousel.addEventListener('mouseenter', function () {
          paused = true;
        });

        carousel.addEventListener('mouseleave', function () {
          paused = false;
        });

        setInterval(function () {
          if (paused || document.hidden) {
            return;
          }

          nextButton.click();
        }, INTERVAL_MS);
      }

      document.addEventListener('DOMContentLoaded', function () {
        setTimeout(initLogoCarouselAutoplay, 700);
        setTimeout(initLogoCarouselAutoplay, 1800);
        setTimeout(initLogoCarouselAutoplay, 3500);
      });
    })();
    </script>
    <?php
}, 100);
/* TMD_LOGO_CAROUSEL_AUTOPLAY_END */


/* TMD_EQUIPMENT_SECTION_REDIRECTS_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-equipment-section-redirects.php';

/* TMD_ACCOUNT_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-account.php';

/* TMD_ABOUT_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-about.php';

/* TMD_EQUIPMENT_TYPE_GUIDES_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-equipment-type-guides.php';

/* TMD_ENERGY_STRUCTURE_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-energy-structure.php';

/* TMD_MAINTENANCE_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-maintenance.php';

/* TMD_PARTNERSHIPS_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-partnerships.php';

/* TMD_SEO_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-seo.php';

/* TMD_FORM_ANTISPAM_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-form-antispam.php';

/* TMD_JOB_APPLICATION_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-job-application.php';

/* TMD_PQR_INCLUDE */
require_once get_stylesheet_directory() . '/inc/tmd-pqr.php';

// Solicitudes empresariales con adjuntos para Alianzas y Proveedores.
require_once get_stylesheet_directory() . '/inc/tmd-business-proposals.php';

/* TMD_ENQUEUE_CHILD_STYLE_START */
add_action('wp_enqueue_scripts', function () {
    $style_path = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style(
        'tmd-blocksy-child-style',
        get_stylesheet_uri(),
        [],
        file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version')
    );
}, 99);
/* TMD_ENQUEUE_CHILD_STYLE_END */

/* TMD_MAINTENANCE_CARDS_FIX_JS_START */
add_action('wp_footer', function () {
    if (! is_page(506)) {
        return;
    }
    ?>
    <script id="tmd-maintenance-cards-fix-js">
      (function () {
        var labels = [
          'DISPONIBILIDAD',
          'SEGURIDAD',
          'TRAZABILIDAD',
          'SERVICIO PROGRAMADO',
          'ATENCION DE FALLAS',
          'ATENCIÓN DE FALLAS'
        ];

        function normalizeText(value) {
          return (value || '')
            .trim()
            .replace(/\s+/g, ' ')
            .toUpperCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        }

        var normalizedLabels = {};
        labels.forEach(function (label) {
          normalizedLabels[normalizeText(label)] = true;
        });

        function findTile(el, labelText) {
          var node = el;

          for (var i = 0; i < 7 && node; i += 1) {
            var nodeText = normalizeText(node.textContent);

            if (nodeText === labelText) {
              var rect = node.getBoundingClientRect();

              if (rect.width >= 40 && rect.height >= 20) {
                return node;
              }
            }

            node = node.parentElement;
          }

          return el;
        }

        function findCard(tile) {
          var node = tile.parentElement;

          for (var i = 0; i < 8 && node; i += 1) {
            var text = normalizeText(node.textContent || '');
            var rect = node.getBoundingClientRect();

            if (
              rect.width > 180 &&
              text.length > 40 &&
              (
                node.matches('article') ||
                /card|tarjeta|service|servicio|benefit|beneficio|maintenance|mantenimiento/i.test(node.className || '')
              )
            ) {
              return node;
            }

            node = node.parentElement;
          }

          return tile.closest('article, .wp-block-column, .wp-block-group, .kt-inside-inner-col, [class*="card"]');
        }

        function applyFix() {
          var nodes = Array.prototype.slice.call(document.querySelectorAll('body.page-id-506 .entry-content *'));

          nodes.forEach(function (el) {
            var text = normalizeText(el.textContent);

            if (!normalizedLabels[text]) {
              return;
            }

            var tile = findTile(el, text);
            var card = findCard(tile);

            tile.classList.add('tmd-maintenance-compact-tile');

            if (card) {
              card.classList.add('tmd-maintenance-compact-card');
            }
          });
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', applyFix);
        } else {
          applyFix();
        }

        window.addEventListener('load', applyFix);
      })();
    </script>
    <?php
}, 100);
/* TMD_MAINTENANCE_CARDS_FIX_JS_END */
