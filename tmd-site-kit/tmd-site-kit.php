<?php
/**
 * Plugin Name: TMD Site Kit
 * Description: Componentes configurables para Tecni Montacargas Dual sin tocar el tema.
 * Version: 0.3.0
 * Author: TMD
 */

if (!defined('ABSPATH')) {
    exit;
}

const TMD_SITE_KIT_VERSION = '0.3.0';

function tmd_site_kit_defaults(): array
{
    return [
        'phone' => '573015556180',
        'whatsapp_text' => 'Hola, quiero recibir asesoria sobre equipos, repuestos o servicio tecnico de Tecni Montacargas Dual.',
        'email' => 'info@tmdual.com',
        'maps_query' => 'Carrera 108 No.22F-21, Bogota, Colombia',
        'linkedin_url' => 'https://www.linkedin.com/company/108105080/',
    ];
}

function tmd_site_kit_option(string $key): string
{
    $defaults = tmd_site_kit_defaults();
    $value = get_option('tmd_site_kit_' . $key, $defaults[$key] ?? '');

    return is_string($value) && $value !== '' ? $value : ($defaults[$key] ?? '');
}

function tmd_site_kit_enqueue_assets(): void
{
    wp_enqueue_style(
        'tmd-work-sans',
        'https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'tmd-site-kit',
        plugins_url('tmd-site-kit.css', __FILE__),
        [],
        TMD_SITE_KIT_VERSION
    );

}
add_action('wp_enqueue_scripts', 'tmd_site_kit_enqueue_assets');

function tmd_site_kit_footer_script(): void
{
    if (is_admin()) {
        return;
    }

    $config = [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tmd_site_kit_form'),
    ];
    ?>
    <?php tmd_site_kit_render_global_footer(); ?>
    <div class="tmd-modal" data-tmd-quote-modal hidden>
        <div class="tmd-modal-card">
            <button class="tmd-modal-close" type="button" data-tmd-modal-close>×</button>
            <h2>Cotizar</h2>
            <p>Producto: <strong data-tmd-quote-product></strong></p>
            <form class="tmd-form-grid" data-tmd-ajax-form>
                <input type="hidden" name="form_type" value="cotizacion">
                <input type="hidden" name="product" value="">
                <div class="tmd-field"><label>Nombre</label><input name="name" required></div>
                <div class="tmd-field"><label>Telefono</label><input name="phone" required></div>
                <div class="tmd-field tmd-field-wide"><label>Correo</label><input type="email" name="email"></div>
                <div class="tmd-field tmd-field-wide"><label>Solicitud</label><textarea name="message" required>Quiero cotizar este producto.</textarea></div>
                <button class="tmd-submit tmd-field-wide" type="submit">Enviar cotizacion</button>
                <div class="tmd-form-status tmd-field-wide" data-tmd-form-status></div>
            </form>
        </div>
    </div>
    <script>
        window.tmdSiteKit = <?php echo wp_json_encode($config); ?>;
    </script>
    <script src="<?php echo esc_url(plugins_url('tmd-site-kit.js', __FILE__) . '?ver=' . TMD_SITE_KIT_VERSION); ?>" defer></script>
    <?php
}
add_action('wp_footer', 'tmd_site_kit_footer_script', 20);

function tmd_site_kit_register_leads(): void
{
    register_post_type('tmd_equipo', [
        'labels' => [
            'name' => 'Equipos TMD',
            'singular_name' => 'Equipo TMD',
            'add_new_item' => 'Agregar equipo',
            'edit_item' => 'Editar equipo',
        ],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'equipos', 'with_front' => false],
        'show_in_rest' => true,
    ]);

    register_post_type('tmd_energia', [
        'labels' => [
            'name' => 'Baterias y cargadores',
            'singular_name' => 'Producto de energia',
            'add_new_item' => 'Agregar producto de energia',
            'edit_item' => 'Editar producto de energia',
        ],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-battery',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'energia/ficha', 'with_front' => false],
        'show_in_rest' => true,
    ]);

    register_post_type('tmd_lead', [
        'label' => 'TMD Leads',
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'supports' => ['title'],
        'menu_icon' => 'dashicons-email-alt',
    ]);
}
add_action('init', 'tmd_site_kit_register_leads');

function tmd_site_kit_register_acf_fields(): void
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_tmd_equipo',
        'title' => 'Ficha tecnica del equipo',
        'fields' => [
            ['key' => 'field_tmd_tipo', 'label' => 'Tipo', 'name' => 'tmd_tipo', 'type' => 'select', 'choices' => ['contrabalanceada' => 'Contrabalanceada', 'retractil' => 'Retractil', 'apilador' => 'Apilador', 'transpaleta' => 'Transpaleta', 'recogepedidos' => 'Recogepedidos', 'estibador' => 'Estibador manual', 'manlift' => 'Manlift / plataforma']],
            ['key' => 'field_tmd_marca', 'label' => 'Marca', 'name' => 'tmd_marca', 'type' => 'text'],
            ['key' => 'field_tmd_modelo', 'label' => 'Modelo', 'name' => 'tmd_modelo', 'type' => 'text'],
            ['key' => 'field_tmd_condicion', 'label' => 'Condicion', 'name' => 'tmd_condicion', 'type' => 'select', 'choices' => ['nuevo' => 'Nuevo', 'usado' => 'Usado', 'reacondicionado' => 'Reacondicionado']],
            ['key' => 'field_tmd_energia', 'label' => 'Energia', 'name' => 'tmd_energia', 'type' => 'select', 'choices' => ['electrico' => 'Electrico', 'glp' => 'GLP', 'diesel' => 'Diesel', 'manual' => 'Manual']],
            ['key' => 'field_tmd_capacidad', 'label' => 'Capacidad', 'name' => 'tmd_capacidad', 'type' => 'text'],
            ['key' => 'field_tmd_altura', 'label' => 'Altura maxima', 'name' => 'tmd_altura', 'type' => 'text'],
            ['key' => 'field_tmd_modalidad', 'label' => 'Modalidad', 'name' => 'tmd_modalidad', 'type' => 'select', 'choices' => ['venta' => 'Venta', 'alquiler' => 'Alquiler', 'venta-alquiler' => 'Venta y alquiler']],
            ['key' => 'field_tmd_precio', 'label' => 'Precio publico', 'name' => 'tmd_precio', 'type' => 'text'],
            ['key' => 'field_tmd_mostrar_precio', 'label' => 'Mostrar precio', 'name' => 'tmd_mostrar_precio', 'type' => 'true_false', 'ui' => 1],
            ['key' => 'field_tmd_destacado', 'label' => 'Destacado', 'name' => 'tmd_destacado', 'type' => 'true_false', 'ui' => 1],
            ['key' => 'field_tmd_imagen_url', 'label' => 'Imagen URL', 'name' => 'tmd_imagen_url', 'type' => 'url'],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'tmd_equipo']]],
    ]);

    acf_add_local_field_group([
        'key' => 'group_tmd_energia',
        'title' => 'Ficha tecnica de bateria/cargador',
        'fields' => [
            ['key' => 'field_tmd_categoria_energia', 'label' => 'Categoria', 'name' => 'tmd_categoria', 'type' => 'select', 'choices' => ['bateria' => 'Bateria', 'cargador' => 'Cargador']],
            ['key' => 'field_tmd_tecnologia', 'label' => 'Tecnologia', 'name' => 'tmd_tecnologia', 'type' => 'select', 'choices' => ['litio' => 'Litio', 'plomo' => 'Plomo-acido', 'gel-agm' => 'Gel / AGM', 'cargador' => 'Cargador']],
            ['key' => 'field_tmd_marca_energia', 'label' => 'Marca', 'name' => 'tmd_marca', 'type' => 'text'],
            ['key' => 'field_tmd_voltaje', 'label' => 'Voltaje', 'name' => 'tmd_voltaje', 'type' => 'text'],
            ['key' => 'field_tmd_amperaje', 'label' => 'Amperaje', 'name' => 'tmd_amperaje', 'type' => 'text'],
            ['key' => 'field_tmd_capacidad_ah', 'label' => 'Capacidad Ah', 'name' => 'tmd_capacidad_ah', 'type' => 'text'],
            ['key' => 'field_tmd_condicion_energia', 'label' => 'Condicion', 'name' => 'tmd_condicion', 'type' => 'select', 'choices' => ['nuevo' => 'Nuevo', 'usado' => 'Usado', 'reacondicionado' => 'Reacondicionado']],
            ['key' => 'field_tmd_precio_energia', 'label' => 'Precio publico', 'name' => 'tmd_precio', 'type' => 'text'],
            ['key' => 'field_tmd_mostrar_precio_energia', 'label' => 'Mostrar precio', 'name' => 'tmd_mostrar_precio', 'type' => 'true_false', 'ui' => 1],
            ['key' => 'field_tmd_imagen_url_energia', 'label' => 'Imagen URL', 'name' => 'tmd_imagen_url', 'type' => 'url'],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'tmd_energia']]],
    ]);
}
add_action('acf/init', 'tmd_site_kit_register_acf_fields');

function tmd_meta(int $post_id, string $key, string $default = ''): string
{
    $value = get_post_meta($post_id, $key, true);
    return is_scalar($value) && (string) $value !== '' ? (string) $value : $default;
}

function tmd_label(string $value): string
{
    $labels = [
        'contrabalanceada' => 'Contrabalanceada',
        'retractil' => 'Retractil',
        'apilador' => 'Apilador',
        'transpaleta' => 'Transpaleta',
        'recogepedidos' => 'Recogepedidos',
        'estibador' => 'Estibador manual',
        'manlift' => 'Manlift / plataforma',
        'electrico' => 'Electrico',
        'glp' => 'GLP',
        'diesel' => 'Diesel',
        'manual' => 'Manual',
        'venta' => 'Venta',
        'alquiler' => 'Alquiler',
        'venta-alquiler' => 'Venta y alquiler',
        'nuevo' => 'Nuevo',
        'usado' => 'Usado',
        'reacondicionado' => 'Reacondicionado',
        'bateria' => 'Bateria',
        'cargador' => 'Cargador',
        'litio' => 'Litio',
        'plomo' => 'Plomo-acido',
        'gel-agm' => 'Gel / AGM',
    ];

    return $labels[$value] ?? ucwords(str_replace('-', ' ', $value));
}

function tmd_post_image_url(int $post_id): string
{
    $image = tmd_meta($post_id, 'tmd_imagen_url');
    if ($image !== '') {
        return $image;
    }

    $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
    return $thumbnail ?: 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/ChatGPT-Image-25-may-2026-10_32_47-1.png';
}

function tmd_condition_class(string $condition): string
{
    if ($condition === 'nuevo') {
        return ' tmd-condition-new';
    }
    if ($condition === 'reacondicionado') {
        return ' tmd-condition-rebuilt';
    }
    return '';
}

function tmd_render_equipment_catalog(): string
{
    $query = new WP_Query([
        'post_type' => 'tmd_equipo',
        'post_status' => 'publish',
        'posts_per_page' => 60,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ]);

    ob_start();
    ?>
    <section class="tmd-catalog-shell" data-tmd-catalog>
        <div class="tmd-wrap tmd-catalog-layout">
            <aside class="tmd-filter-panel">
                <h2>Filtros</h2>
                <div class="tmd-filter-group"><strong>Subtipo</strong><label><input data-tmd-filter type="checkbox" name="tipo" value="contrabalanceada"> Contrabalanceadas</label><label><input data-tmd-filter type="checkbox" name="tipo" value="retractil"> Retractiles</label><label><input data-tmd-filter type="checkbox" name="tipo" value="apilador"> Apiladores</label><label><input data-tmd-filter type="checkbox" name="tipo" value="transpaleta"> Transpaletas</label><label><input data-tmd-filter type="checkbox" name="tipo" value="recogepedidos"> Recogepedidos</label><label><input data-tmd-filter type="checkbox" name="tipo" value="estibador"> Estibadores</label><label><input data-tmd-filter type="checkbox" name="tipo" value="manlift"> Manlift</label></div>
                <div class="tmd-filter-group"><strong>Energia</strong><label><input data-tmd-filter type="checkbox" name="energia" value="electrico"> Electrico</label><label><input data-tmd-filter type="checkbox" name="energia" value="glp"> GLP</label><label><input data-tmd-filter type="checkbox" name="energia" value="diesel"> Diesel</label><label><input data-tmd-filter type="checkbox" name="energia" value="manual"> Manual</label></div>
                <div class="tmd-filter-group"><strong>Modalidad</strong><label><input data-tmd-filter type="checkbox" name="modalidad" value="venta"> Venta</label><label><input data-tmd-filter type="checkbox" name="modalidad" value="alquiler"> Alquiler</label><label><input data-tmd-filter type="checkbox" name="modalidad" value="venta-alquiler"> Venta y alquiler</label></div>
                <button class="tmd-clear-btn" type="button" data-tmd-clear-filters>Limpiar filtros</button>
            </aside>
            <div class="tmd-catalog-grid">
                <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
                    $post_id = get_the_ID();
                    $tipo = tmd_meta($post_id, 'tmd_tipo', 'contrabalanceada');
                    $energia = tmd_meta($post_id, 'tmd_energia', 'electrico');
                    $modalidad = tmd_meta($post_id, 'tmd_modalidad', 'venta');
                    $condition = tmd_meta($post_id, 'tmd_condicion', 'usado');
                    $price = tmd_meta($post_id, 'tmd_precio');
                    $show_price = tmd_meta($post_id, 'tmd_mostrar_precio') === '1';
                    ?>
                    <article class="tmd-product-card" data-tmd-card data-tipo="<?php echo esc_attr($tipo); ?>" data-energia="<?php echo esc_attr($energia); ?>" data-modalidad="<?php echo esc_attr($modalidad); ?>">
                        <img src="<?php echo esc_url(tmd_post_image_url($post_id)); ?>" alt="<?php the_title_attribute(); ?>">
                        <div class="tmd-product-copy">
                            <span class="tmd-condition<?php echo esc_attr(tmd_condition_class($condition)); ?>"><?php echo esc_html(tmd_label($condition)); ?></span>
                            <h3><?php the_title(); ?></h3>
                            <div class="tmd-product-meta"><?php echo esc_html(tmd_label($energia)); ?> · <?php echo esc_html(tmd_meta($post_id, 'tmd_capacidad', 'Capacidad por validar')); ?> · <?php echo esc_html(tmd_meta($post_id, 'tmd_altura', 'Altura por validar')); ?> · <?php echo esc_html(tmd_label($modalidad)); ?></div>
                            <div class="tmd-chips"><span><?php echo esc_html(tmd_label($tipo)); ?></span><?php if ($show_price && $price !== '') : ?><span><?php echo esc_html($price); ?></span><?php endif; ?><span><?php echo esc_html(tmd_meta($post_id, 'tmd_marca', 'Multimarca')); ?></span></div>
                            <div class="tmd-product-actions"><button class="tmd-quote-btn" type="button" data-tmd-quote="<?php echo esc_attr(get_the_title()); ?>">Cotizar</button><a class="tmd-detail-btn" href="<?php the_permalink(); ?>">Ver ficha</a></div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); else : ?>
                    <p class="tmd-empty-state">Aun no hay equipos publicados. Agrega equipos desde el administrador de WordPress.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('tmd_equipment_catalog', 'tmd_render_equipment_catalog');

function tmd_render_energy_catalog(): string
{
    $query = new WP_Query([
        'post_type' => 'tmd_energia',
        'post_status' => 'publish',
        'posts_per_page' => 60,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ]);

    ob_start();
    ?>
    <section class="tmd-catalog-shell" data-tmd-catalog>
        <div class="tmd-wrap tmd-catalog-layout">
            <aside class="tmd-filter-panel">
                <h2>Filtros</h2>
                <div class="tmd-filter-group"><strong>Categoria</strong><label><input data-tmd-filter type="checkbox" name="tipo" value="bateria"> Baterias</label><label><input data-tmd-filter type="checkbox" name="tipo" value="cargador"> Cargadores</label></div>
                <div class="tmd-filter-group"><strong>Tecnologia</strong><label><input data-tmd-filter type="checkbox" name="energia" value="litio"> Litio</label><label><input data-tmd-filter type="checkbox" name="energia" value="plomo"> Plomo</label><label><input data-tmd-filter type="checkbox" name="energia" value="gel-agm"> Gel / AGM</label><label><input data-tmd-filter type="checkbox" name="energia" value="cargador"> Cargador</label></div>
                <button class="tmd-clear-btn" type="button" data-tmd-clear-filters>Limpiar filtros</button>
            </aside>
            <div class="tmd-catalog-grid">
                <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
                    $post_id = get_the_ID();
                    $categoria = tmd_meta($post_id, 'tmd_categoria', 'bateria');
                    $tecnologia = tmd_meta($post_id, 'tmd_tecnologia', 'litio');
                    $condition = tmd_meta($post_id, 'tmd_condicion', 'nuevo');
                    $price = tmd_meta($post_id, 'tmd_precio');
                    $show_price = tmd_meta($post_id, 'tmd_mostrar_precio') === '1';
                    ?>
                    <article class="tmd-product-card" data-tmd-card data-tipo="<?php echo esc_attr($categoria); ?>" data-energia="<?php echo esc_attr($tecnologia); ?>">
                        <img src="<?php echo esc_url(tmd_post_image_url($post_id)); ?>" alt="<?php the_title_attribute(); ?>">
                        <div class="tmd-product-copy">
                            <span class="tmd-condition<?php echo esc_attr(tmd_condition_class($condition)); ?>"><?php echo esc_html(tmd_label($condition)); ?></span>
                            <h3><?php the_title(); ?></h3>
                            <div class="tmd-product-meta"><?php echo esc_html(tmd_meta($post_id, 'tmd_voltaje', 'Voltaje por validar')); ?> · <?php echo esc_html(tmd_meta($post_id, 'tmd_capacidad_ah', 'Capacidad por validar')); ?> · <?php echo esc_html(tmd_meta($post_id, 'tmd_marca', 'Multimarca')); ?></div>
                            <div class="tmd-chips"><span><?php echo esc_html(tmd_label($categoria)); ?></span><span><?php echo esc_html(tmd_label($tecnologia)); ?></span><?php if ($show_price && $price !== '') : ?><span><?php echo esc_html($price); ?></span><?php endif; ?></div>
                            <div class="tmd-product-actions"><button class="tmd-quote-btn" type="button" data-tmd-quote="<?php echo esc_attr(get_the_title()); ?>">Cotizar</button><a class="tmd-detail-btn" href="<?php the_permalink(); ?>">Ver ficha</a></div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); else : ?>
                    <p class="tmd-empty-state">Aun no hay baterias o cargadores publicados. Agrega productos desde el administrador de WordPress.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('tmd_energy_catalog', 'tmd_render_energy_catalog');

function tmd_site_kit_single_content(string $content): string
{
    if (!is_singular(['tmd_equipo', 'tmd_energia']) || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $post_id = get_the_ID();
    $is_equipo = get_post_type($post_id) === 'tmd_equipo';
    $specs = $is_equipo
        ? [
            'Tipo' => tmd_label(tmd_meta($post_id, 'tmd_tipo', 'contrabalanceada')),
            'Energia' => tmd_label(tmd_meta($post_id, 'tmd_energia', 'electrico')),
            'Capacidad' => tmd_meta($post_id, 'tmd_capacidad', 'Por validar'),
            'Altura maxima' => tmd_meta($post_id, 'tmd_altura', 'Por validar'),
            'Modalidad' => tmd_label(tmd_meta($post_id, 'tmd_modalidad', 'venta')),
            'Condicion' => tmd_label(tmd_meta($post_id, 'tmd_condicion', 'usado')),
        ]
        : [
            'Categoria' => tmd_label(tmd_meta($post_id, 'tmd_categoria', 'bateria')),
            'Tecnologia' => tmd_label(tmd_meta($post_id, 'tmd_tecnologia', 'litio')),
            'Voltaje' => tmd_meta($post_id, 'tmd_voltaje', 'Por validar'),
            'Amperaje' => tmd_meta($post_id, 'tmd_amperaje', 'Por validar'),
            'Capacidad' => tmd_meta($post_id, 'tmd_capacidad_ah', 'Por validar'),
            'Condicion' => tmd_label(tmd_meta($post_id, 'tmd_condicion', 'nuevo')),
        ];

    $price = tmd_meta($post_id, 'tmd_precio');
    $show_price = tmd_meta($post_id, 'tmd_mostrar_precio') === '1' && $price !== '';

    ob_start();
    ?>
    <article class="tmd-single-product">
        <div class="tmd-wrap tmd-single-grid">
            <div class="tmd-single-media"><img src="<?php echo esc_url(tmd_post_image_url($post_id)); ?>" alt="<?php the_title_attribute(); ?>"></div>
            <div class="tmd-single-copy">
                <a class="tmd-back-link" href="<?php echo esc_url($is_equipo ? home_url('/equipos/') : home_url('/energia/')); ?>">← Volver al catalogo</a>
                <span class="tmd-eyebrow"><?php echo esc_html($is_equipo ? 'Ficha tecnica de equipo' : 'Ficha tecnica de energia'); ?></span>
                <h1><?php the_title(); ?></h1>
                <div class="tmd-single-specs">
                    <?php foreach ($specs as $label => $value) : ?>
                        <div><span><?php echo esc_html($label); ?></span><strong><?php echo esc_html($value); ?></strong></div>
                    <?php endforeach; ?>
                </div>
                <?php if ($show_price) : ?><p class="tmd-single-price"><?php echo esc_html($price); ?></p><?php endif; ?>
                <div class="tmd-actions"><button class="tmd-btn tmd-btn-primary" type="button" data-tmd-quote="<?php echo esc_attr(get_the_title()); ?>">Cotizar esta ficha</button><a class="tmd-btn tmd-btn-outline" href="<?php echo esc_url(home_url('/nosotros/contacto/')); ?>">Hablar con asesor</a></div>
            </div>
        </div>
        <div class="tmd-wrap tmd-single-body"><?php echo $content; ?></div>
    </article>
    <?php
    return ob_get_clean();
}
add_filter('the_content', 'tmd_site_kit_single_content');

function tmd_site_kit_render_global_footer(): void
{
    $phone = tmd_site_kit_option('phone');
    $email = tmd_site_kit_option('email');
    $wa = 'https://wa.me/' . rawurlencode($phone);
    ?>
    <footer class="tmd-global-footer" aria-label="Pie de pagina Tecni Montacargas">
        <section class="tmd-footer-cta">
            <div class="tmd-wrap">
                <h2>¿Listo para optimizar su logistica hoy mismo?</h2>
                <div class="tmd-footer-cta-actions">
                    <button type="button" data-tmd-quote="Cotizacion general">Cotizar ahora →</button>
                    <a href="<?php echo esc_url(home_url('/nosotros/contacto/')); ?>">Hablar con un asesor</a>
                </div>
            </div>
        </section>
        <section class="tmd-footer-main">
            <div class="tmd-wrap tmd-footer-grid">
                <div class="tmd-footer-brand">
                    <strong>Tecni Montacargas</strong>
                    <div class="tmd-footer-social" aria-label="Redes sociales">
                        <a href="<?php echo esc_url(tmd_site_kit_option('linkedin_url')); ?>" aria-label="LinkedIn">in</a>
                        <a href="https://www.instagram.com/" aria-label="Instagram">◎</a>
                        <a href="https://www.facebook.com/" aria-label="Facebook">f</a>
                        <a href="https://www.youtube.com/" aria-label="YouTube">▶</a>
                    </div>
                    <ul class="tmd-footer-contact-list">
                        <li><span>●</span>Cra 108 #22F-21</li>
                        <li><span>☎</span>601 256 2256</li>
                        <li><span>●</span>324 429 8326 WhatsApp</li>
                        <li><span>✉</span><?php echo esc_html($email); ?></li>
                        <li><span>●</span>L-V 7:00-17:00</li>
                    </ul>
                </div>
                <nav class="tmd-footer-links" aria-label="Catalogos">
                    <strong>Catalogos</strong>
                    <a href="<?php echo esc_url(home_url('/equipos/')); ?>">Maquinas</a>
                    <a href="<?php echo esc_url(home_url('/energia/')); ?>">Baterias</a>
                    <a href="<?php echo esc_url(home_url('/energia/')); ?>">Cargadores</a>
                    <a href="<?php echo esc_url(home_url('/repuestos/')); ?>">Repuestos</a>
                    <a class="tmd-footer-quiz" href="<?php echo esc_url(home_url('/encuentra-tu-equipo/')); ?>">Encuentra tu equipo ideal</a>
                </nav>
                <nav class="tmd-footer-links" aria-label="Servicios">
                    <strong>Servicios</strong>
                    <a href="<?php echo esc_url(home_url('/servicios/')); ?>">Mantenimiento preventivo</a>
                    <a href="<?php echo esc_url(home_url('/servicios/')); ?>">Mantenimiento correctivo</a>
                    <a href="<?php echo esc_url($wa); ?>">Emergencia 24/7</a>
                </nav>
                <nav class="tmd-footer-links" aria-label="Empresa">
                    <strong>Empresa</strong>
                    <a href="<?php echo esc_url(home_url('/nosotros/')); ?>">Quienes somos</a>
                    <a href="<?php echo esc_url(home_url('/nosotros/blog/')); ?>">Blog</a>
                    <a href="<?php echo esc_url(home_url('/nosotros/alianzas/')); ?>">Alianzas</a>
                    <a href="<?php echo esc_url(home_url('/nosotros/trabaja-con-nosotros/')); ?>">Trabaja con nosotros</a>
                    <a href="<?php echo esc_url(home_url('/nosotros/legal/pqr/')); ?>">PQR</a>
                </nav>
            </div>
        </section>
        <section class="tmd-footer-bottom">
            <div class="tmd-wrap">
                <span>Copyright © <?php echo esc_html(date_i18n('Y')); ?> Tecni Montacargas. Todos los derechos reservados.</span>
                <nav><a href="<?php echo esc_url(home_url('/devoluciones/')); ?>">Devoluciones</a><a href="<?php echo esc_url(home_url('/garantias/')); ?>">Garantias</a><a href="<?php echo esc_url(home_url('/sg-sst/')); ?>">SG-SST</a><a href="<?php echo esc_url(home_url('/privacidad/')); ?>">Privacidad</a><a href="<?php echo esc_url(home_url('/terminos/')); ?>">Terminos</a></nav>
            </div>
        </section>
    </footer>
    <?php
}

function tmd_site_kit_submit_form(): void
{
    check_ajax_referer('tmd_site_kit_form', 'nonce');

    $form_type = sanitize_key($_POST['form_type'] ?? 'contacto');
    $allowed_types = ['contacto', 'pqr', 'quiz', 'cotizacion'];

    if (!in_array($form_type, $allowed_types, true)) {
        wp_send_json_error(['message' => 'Formulario no valido.'], 400);
    }

    $required_by_type = [
        'contacto' => ['name', 'email', 'message'],
        'pqr' => ['request_type', 'subject', 'name', 'email', 'phone', 'message', 'terms'],
        'quiz' => ['name', 'phone', 'message'],
        'cotizacion' => ['name', 'phone', 'message', 'product'],
    ];

    foreach ($required_by_type[$form_type] as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(['message' => 'Completa los campos requeridos.'], 400);
        }
    }

    $payload = [];

    foreach ($_POST as $key => $value) {
        if (in_array($key, ['action', 'nonce'], true)) {
            continue;
        }

        $clean_key = sanitize_key($key);
        $payload[$clean_key] = is_array($value)
            ? array_map('sanitize_text_field', $value)
            : sanitize_textarea_field((string) $value);
    }

    $name = sanitize_text_field($_POST['name'] ?? 'Visitante');
    $title = sprintf('%s - %s - %s', strtoupper($form_type), $name, current_time('mysql'));

    $lead_id = wp_insert_post([
        'post_type' => 'tmd_lead',
        'post_status' => 'private',
        'post_title' => $title,
    ]);

    if (is_wp_error($lead_id) || !$lead_id) {
        wp_send_json_error(['message' => 'No se pudo guardar la solicitud.'], 500);
    }

    update_post_meta($lead_id, 'tmd_form_type', $form_type);
    update_post_meta($lead_id, 'tmd_payload', wp_json_encode($payload, JSON_UNESCAPED_UNICODE));

    $lines = [];
    foreach ($payload as $key => $value) {
        $lines[] = strtoupper(str_replace('_', ' ', $key)) . ': ' . (is_array($value) ? implode(', ', $value) : $value);
    }

    $to = tmd_site_kit_option('email');
    $subject = 'Nueva solicitud TMD: ' . strtoupper($form_type);
    $body = implode("\n", $lines) . "\n\nLead ID: " . $lead_id;
    wp_mail($to, $subject, $body);

    wp_send_json_success(['message' => 'Solicitud enviada. Un asesor te contactara pronto.']);
}
add_action('wp_ajax_tmd_site_kit_submit_form', 'tmd_site_kit_submit_form');
add_action('wp_ajax_nopriv_tmd_site_kit_submit_form', 'tmd_site_kit_submit_form');

function tmd_site_kit_register_settings(): void
{
    foreach (array_keys(tmd_site_kit_defaults()) as $key) {
        register_setting('tmd_site_kit', 'tmd_site_kit_' . $key, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => tmd_site_kit_defaults()[$key],
        ]);
    }
}
add_action('admin_init', 'tmd_site_kit_register_settings');

function tmd_site_kit_settings_page(): void
{
    add_options_page(
        'TMD Site Kit',
        'TMD Site Kit',
        'manage_options',
        'tmd-site-kit',
        'tmd_site_kit_render_settings_page'
    );
}
add_action('admin_menu', 'tmd_site_kit_settings_page');

function tmd_site_kit_render_settings_page(): void
{
    $fields = [
        'phone' => 'WhatsApp en formato internacional',
        'whatsapp_text' => 'Mensaje inicial de WhatsApp',
        'email' => 'Correo',
        'maps_query' => 'Direccion para Google Maps',
        'linkedin_url' => 'URL de LinkedIn',
    ];
    ?>
    <div class="wrap">
        <h1>TMD Site Kit</h1>
        <form method="post" action="options.php">
            <?php settings_fields('tmd_site_kit'); ?>
            <table class="form-table" role="presentation">
                <?php foreach ($fields as $key => $label) : ?>
                    <tr>
                        <th scope="row"><label for="tmd_site_kit_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td>
                            <input
                                class="regular-text"
                                id="tmd_site_kit_<?php echo esc_attr($key); ?>"
                                name="tmd_site_kit_<?php echo esc_attr($key); ?>"
                                value="<?php echo esc_attr(tmd_site_kit_option($key)); ?>"
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
