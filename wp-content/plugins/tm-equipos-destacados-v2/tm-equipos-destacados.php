<?php
/**
 * Plugin Name: TM Equipos Destacados V2
 * Description: Carrusel compacto de equipos destacados para el home y enlaces editables de redes sociales del footer.
 * Version: 2.2.0
 * Author: Tecni Montacargas
 * Text Domain: tm-equipos-destacados
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TM_Equipos_Destacados_V2')) {
    final class TM_Equipos_Destacados_V2 {
        const VERSION = '2.2.0';
        const OPTION_NAME = 'tm_eqd_options';
        const NONCE_ACTION = 'tm_eqd_save_meta';
        const NONCE_NAME = 'tm_eqd_nonce';
        const FEATURED_API_URL = 'https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposDestacadosWordpress';
        const FEATURED_API_CACHE_KEY = 'tm_eqd_featured_api_v1';
        const FEATURED_API_FALLBACK_KEY = 'tm_eqd_featured_api_last_good_v1';

        private static $instance = null;

        public static function instance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        private function __construct() {
            add_action('init', [$this, 'register_shortcodes'], 99);
            add_action('add_meta_boxes', [$this, 'add_metabox']);
            add_action('save_post', [$this, 'save_meta']);
            add_action('admin_menu', [$this, 'add_admin_page']);
            add_action('admin_init', [$this, 'register_settings']);
            add_action('admin_notices', [$this, 'admin_notice']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets'], 20);
            add_action('wp_head', [$this, 'print_inline_social_config'], 99);
        }

        public function register_shortcodes() {
            add_shortcode('tm_equipos_destacados', [$this, 'render_shortcode']);
            add_shortcode('tm_equipos_destacados_v2', [$this, 'render_shortcode']);
            add_shortcode('tm_footer_social_links', [$this, 'render_social_shortcode']);
        }

        public function get_options() {
            $defaults = [
                'catalog_url' => '/equipos',
                'facebook_url' => '',
                'instagram_url' => '',
                'youtube_url' => '',
                'auto_footer_social' => '1',
            ];

            $options = get_option(self::OPTION_NAME, []);
            if (!is_array($options)) {
                $options = [];
            }

            return wp_parse_args($options, $defaults);
        }

        public function sanitize_options($input) {
            $input = is_array($input) ? $input : [];

            return [
                'catalog_url' => isset($input['catalog_url']) ? esc_url_raw(trim(wp_unslash($input['catalog_url']))) : '/equipos',
                'facebook_url' => isset($input['facebook_url']) ? esc_url_raw(trim(wp_unslash($input['facebook_url']))) : '',
                'instagram_url' => isset($input['instagram_url']) ? esc_url_raw(trim(wp_unslash($input['instagram_url']))) : '',
                'youtube_url' => isset($input['youtube_url']) ? esc_url_raw(trim(wp_unslash($input['youtube_url']))) : '',
                'auto_footer_social' => !empty($input['auto_footer_social']) ? '1' : '0',
            ];
        }

        public function register_settings() {
            register_setting(
                'tm_eqd_settings_group',
                self::OPTION_NAME,
                [
                    'type' => 'array',
                    'sanitize_callback' => [$this, 'sanitize_options'],
                    'default' => $this->get_options(),
                ]
            );
        }

        public function add_admin_page() {
            add_menu_page(
                'TM Destacados',
                'TM Destacados',
                'manage_options',
                'tm-equipos-destacados',
                [$this, 'render_admin_page'],
                'dashicons-slides',
                58
            );
        }

        public function get_equipment_post_types() {
            $candidates = [
                'tmd_equipo',
                'equipo',
                'equipos',
                'product',
            ];

            $existing = [];

            foreach ($candidates as $post_type) {
                if (post_type_exists($post_type)) {
                    $existing[] = $post_type;
                }
            }

            return apply_filters('tm_eqd_equipment_post_types', $existing);
        }

        public function count_featured() {
            $post_types = $this->get_equipment_post_types();
            if (empty($post_types)) {
                return 0;
            }

            $query = new WP_Query([
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'no_found_rows' => false,
                'meta_query' => [
                    [
                        'key' => '_tm_eqd_destacado',
                        'value' => '1',
                        'compare' => '=',
                    ],
                ],
            ]);

            return (int) $query->found_posts;
        }

        public function render_admin_page() {
            if (!current_user_can('manage_options')) {
                return;
            }

            $options = $this->get_options();
            $post_types = $this->get_equipment_post_types();
            $featured_count = $this->count_featured();
            ?>
            <div class="wrap">
                <h1>TM Destacados</h1>

                <div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid #128CEB;padding:14px 18px;margin:16px 0;max-width:920px;">
                    <h2 style="margin-top:0;">Estado del plugin</h2>
                    <p><strong>Post types detectados:</strong>
                        <?php if (!empty($post_types)): ?>
                            <code><?php echo esc_html(implode(', ', $post_types)); ?></code>
                        <?php else: ?>
                            <span style="color:#b32d2e;"><strong>Ninguno.</strong> Si tus equipos usan otro post type, toca agregarlo por filtro.</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Equipos destacados publicados:</strong> <?php echo esc_html((string) $featured_count); ?></p>
                    <p><strong>Shortcode recomendado:</strong> <code>[tm_equipos_destacados_v2]</code></p>
                    <p>Si tienes el plugin viejo activo, usa el shortcode <code>[tm_equipos_destacados_v2]</code> para evitar conflictos. Si reemplazaste el plugin viejo, también sirve <code>[tm_equipos_destacados]</code>.</p>
                </div>

                <form method="post" action="options.php">
                    <?php settings_fields('tm_eqd_settings_group'); ?>

                    <h2>Catálogo</h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="tm_eqd_catalog_url">URL del catálogo completo</label></th>
                            <td>
                                <input id="tm_eqd_catalog_url" class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[catalog_url]" value="<?php echo esc_attr($options['catalog_url']); ?>" placeholder="/equipos">
                                <p class="description">Este enlace se usa en “Ver todos →”. Puedes poner <code>/equipos</code> o una URL completa.</p>
                            </td>
                        </tr>
                    </table>

                    <h2>Redes sociales del footer</h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="tm_eqd_youtube_url">YouTube</label></th>
                            <td><input id="tm_eqd_youtube_url" class="regular-text" type="url" name="<?php echo esc_attr(self::OPTION_NAME); ?>[youtube_url]" value="<?php echo esc_attr($options['youtube_url']); ?>" placeholder="https://www.youtube.com/@tu-canal"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tm_eqd_instagram_url">Instagram</label></th>
                            <td><input id="tm_eqd_instagram_url" class="regular-text" type="url" name="<?php echo esc_attr(self::OPTION_NAME); ?>[instagram_url]" value="<?php echo esc_attr($options['instagram_url']); ?>" placeholder="https://www.instagram.com/tu-cuenta"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tm_eqd_facebook_url">Facebook</label></th>
                            <td><input id="tm_eqd_facebook_url" class="regular-text" type="url" name="<?php echo esc_attr(self::OPTION_NAME); ?>[facebook_url]" value="<?php echo esc_attr($options['facebook_url']); ?>" placeholder="https://www.facebook.com/tu-pagina"></td>
                        </tr>
                        <tr>
                            <th scope="row">Actualizar footer existente</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[auto_footer_social]" value="1" <?php checked($options['auto_footer_social'], '1'); ?>>
                                    Cambiar automáticamente los enlaces dentro de <code>.tmd-footer-social</code>.
                                </label>
                                <p class="description">Si no funciona porque tus iconos no tienen texto identificable, pega este shortcode en el footer: <code>[tm_footer_social_links]</code>.</p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button('Guardar cambios'); ?>
                </form>

                <hr>
                <h2>Cómo usarlo en el home</h2>
                <p>Reemplaza la sección vieja de “Equipos destacados” por:</p>
                <p><code>[tm_equipos_destacados_v2]</code></p>
                <p>También puedes usar:</p>
                <p><code>[tm_equipos_destacados_v2 catalogo="/equipos" limit="8"]</code></p>
            </div>
            <?php
        }

        public function admin_notice() {
            if (!current_user_can('manage_options')) {
                return;
            }

            $screen = function_exists('get_current_screen') ? get_current_screen() : null;
            if ($screen && $screen->id === 'toplevel_page_tm-equipos-destacados') {
                return;
            }

            $post_types = $this->get_equipment_post_types();
            if (empty($post_types)) {
                return;
            }

            $featured_count = $this->count_featured();
            if ($featured_count > 0) {
                return;
            }

            echo '<div class="notice notice-info is-dismissible"><p><strong>TM Destacados:</strong> el plugin está activo. Falta marcar equipos como destacados desde la ficha del equipo. Revisa el menú <a href="' . esc_url(admin_url('admin.php?page=tm-equipos-destacados')) . '">TM Destacados</a>.</p></div>';
        }

        public function add_metabox() {
            foreach ($this->get_equipment_post_types() as $post_type) {
                add_meta_box(
                    'tm_eqd_metabox',
                    'Equipo destacado home',
                    [$this, 'render_metabox'],
                    $post_type,
                    'side',
                    'high'
                );
            }
        }

        public function render_metabox($post) {
            wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

            $destacado = get_post_meta($post->ID, '_tm_eqd_destacado', true);
            $orden = get_post_meta($post->ID, '_tm_eqd_orden', true);
            $condicion = get_post_meta($post->ID, '_tm_eqd_condicion', true);
            $modelo = get_post_meta($post->ID, '_tm_eqd_modelo', true);
            $anio = get_post_meta($post->ID, '_tm_eqd_anio', true);
            $energia = get_post_meta($post->ID, '_tm_eqd_energia', true);
            $capacidad = get_post_meta($post->ID, '_tm_eqd_capacidad', true);
            $altura = get_post_meta($post->ID, '_tm_eqd_altura', true);

            if (!$condicion) {
                $condicion = 'Usado';
            }
            ?>
            <p>
                <label>
                    <input type="checkbox" name="tm_eqd_destacado" value="1" <?php checked($destacado, '1'); ?>>
                    Mostrar en Equipos Destacados
                </label>
            </p>

            <p>
                <label for="tm_eqd_orden"><strong>Orden destacado</strong></label>
                <input id="tm_eqd_orden" type="number" name="tm_eqd_orden" value="<?php echo esc_attr($orden); ?>" min="0" step="1" style="width:100%;">
            </p>

            <p>
                <label for="tm_eqd_condicion"><strong>Condición</strong></label>
                <select id="tm_eqd_condicion" name="tm_eqd_condicion" style="width:100%;">
                    <option value="Usado" <?php selected($condicion, 'Usado'); ?>>Usado</option>
                    <option value="Nuevo" <?php selected($condicion, 'Nuevo'); ?>>Nuevo</option>
                    <option value="Reacondicionado" <?php selected($condicion, 'Reacondicionado'); ?>>Reacondicionado</option>
                </select>
            </p>

            <p>
                <label for="tm_eqd_modelo"><strong>Marca + modelo</strong></label>
                <input id="tm_eqd_modelo" type="text" name="tm_eqd_modelo" value="<?php echo esc_attr($modelo); ?>" placeholder="Toyota 8FGU25" style="width:100%;">
            </p>

            <p>
                <label for="tm_eqd_anio"><strong>Año</strong></label>
                <input id="tm_eqd_anio" type="text" name="tm_eqd_anio" value="<?php echo esc_attr($anio); ?>" placeholder="2022" style="width:100%;">
            </p>

            <p>
                <label for="tm_eqd_energia"><strong>Energía</strong></label>
                <input id="tm_eqd_energia" type="text" name="tm_eqd_energia" value="<?php echo esc_attr($energia); ?>" placeholder="GLP / Eléctrico / Diésel" style="width:100%;">
            </p>

            <p>
                <label for="tm_eqd_capacidad"><strong>Capacidad</strong></label>
                <input id="tm_eqd_capacidad" type="text" name="tm_eqd_capacidad" value="<?php echo esc_attr($capacidad); ?>" placeholder="2.5 ton" style="width:100%;">
            </p>

            <p>
                <label for="tm_eqd_altura"><strong>Altura</strong></label>
                <input id="tm_eqd_altura" type="text" name="tm_eqd_altura" value="<?php echo esc_attr($altura); ?>" placeholder="4.7 m" style="width:100%;">
            </p>
            <?php
        }

        public function save_meta($post_id) {
            if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            if (!in_array(get_post_type($post_id), $this->get_equipment_post_types(), true)) {
                return;
            }

            update_post_meta($post_id, '_tm_eqd_destacado', isset($_POST['tm_eqd_destacado']) ? '1' : '0');
            update_post_meta($post_id, '_tm_eqd_orden', isset($_POST['tm_eqd_orden']) ? absint($_POST['tm_eqd_orden']) : 0);

            $allowed_condiciones = ['Usado', 'Nuevo', 'Reacondicionado'];
            $condicion = isset($_POST['tm_eqd_condicion']) ? sanitize_text_field(wp_unslash($_POST['tm_eqd_condicion'])) : 'Usado';
            if (!in_array($condicion, $allowed_condiciones, true)) {
                $condicion = 'Usado';
            }
            update_post_meta($post_id, '_tm_eqd_condicion', $condicion);

            $fields = [
                '_tm_eqd_modelo' => 'tm_eqd_modelo',
                '_tm_eqd_anio' => 'tm_eqd_anio',
                '_tm_eqd_energia' => 'tm_eqd_energia',
                '_tm_eqd_capacidad' => 'tm_eqd_capacidad',
                '_tm_eqd_altura' => 'tm_eqd_altura',
            ];

            foreach ($fields as $meta_key => $post_key) {
                $value = isset($_POST[$post_key]) ? sanitize_text_field(wp_unslash($_POST[$post_key])) : '';
                update_post_meta($post_id, $meta_key, $value);
            }
        }

        public function enqueue_front_assets() {
            $base_url = plugin_dir_url(__FILE__);

            wp_enqueue_style(
                'tm-equipos-destacados-v2',
                $base_url . 'assets/css/tm-equipos-destacados.css',
                [],
                self::VERSION
            );

            wp_enqueue_script(
                'tm-equipos-destacados-v2',
                $base_url . 'assets/js/tm-equipos-destacados.js',
                [],
                self::VERSION,
                true
            );
        }

        private function meta_value($post_id, $keys, $fallback = '') {
            foreach ((array) $keys as $key) {
                $value = get_post_meta($post_id, $key, true);
                if ($value !== '' && $value !== null) {
                    return $value;
                }
            }

            return $fallback;
        }

        private function featured_api_items($limit) {
            $cached = get_transient(self::FEATURED_API_CACHE_KEY);
            if (is_array($cached) && !empty($cached)) {
                return array_slice($cached, 0, $limit);
            }

            $response = wp_remote_get(self::FEATURED_API_URL, [
                'timeout' => 18,
                'redirection' => 2,
                'headers' => [
                    'Accept' => 'application/json',
                    'Origin' => untrailingslashit(home_url('/')),
                ],
                'user-agent' => 'TecniMontacargasFeatured/2.2; ' . home_url('/'),
            ]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                if (
                    is_array($body)
                    && !empty($body['ok'])
                    && !empty($body['items'])
                    && is_array($body['items'])
                ) {
                    $items = array_values(array_filter($body['items'], static function ($item) {
                        return is_array($item) && !empty($item['id']);
                    }));

                    if ($items) {
                        set_transient(self::FEATURED_API_CACHE_KEY, $items, DAY_IN_SECONDS);
                        update_option(self::FEATURED_API_FALLBACK_KEY, $items, false);
                        return array_slice($items, 0, $limit);
                    }
                }
            }

            $fallback = get_option(self::FEATURED_API_FALLBACK_KEY, []);
            return is_array($fallback) ? array_slice($fallback, 0, $limit) : [];
        }

        private function api_text($value, $fallback = '') {
            if (!is_scalar($value)) {
                return $fallback;
            }

            $text = trim((string) $value);
            return $text !== '' ? $text : $fallback;
        }

        private function api_year($value) {
            if (is_array($value) && isset($value['_seconds']) && is_numeric($value['_seconds'])) {
                return gmdate('Y', (int) $value['_seconds']);
            }

            if (is_numeric($value)) {
                $number = (int) $value;
                return $number > 1900 && $number < 2200 ? (string) $number : '';
            }

            $text = $this->api_text($value);
            if (preg_match('/\b(19|20)\d{2}\b/', $text, $matches)) {
                return $matches[0];
            }

            return '';
        }

        private function api_number($value, $suffix) {
            if (!is_numeric($value) || (float) $value <= 0) {
                return '';
            }

            $number = (float) $value;
            if ($suffix === ' m' && $number > 20) {
                $number /= 100;
            }

            return rtrim(rtrim(number_format($number, 2, ',', ''), '0'), ',') . $suffix;
        }

        private function render_api_shortcode($items, $atts) {
            $section_id = wp_unique_id('tm-eqd-');

            ob_start();
            ?>
            <section id="<?php echo esc_attr($section_id); ?>" class="tm-eqd-section" data-tm-eqd aria-label="Equipos destacados">
                <div class="tm-eqd-container">
                    <div class="tm-eqd-head">
                        <div class="tm-eqd-heading">
                            <span class="tm-eqd-eyebrow">Flota real</span>
                            <h2>Equipos destacados</h2>
                            <p>Los equipos con mayor actividad dentro de nuestra operación</p>
                        </div>
                        <a class="tm-eqd-all" href="<?php echo esc_url($atts['catalogo']); ?>">Ver todos →</a>
                    </div>

                    <div class="tm-eqd-carousel">
                        <button class="tm-eqd-arrow tm-eqd-prev" type="button" data-tm-eqd-prev aria-label="Ver equipos anteriores">‹</button>

                        <div class="tm-eqd-viewport" data-tm-eqd-viewport>
                            <div class="tm-eqd-track" data-tm-eqd-track>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $id = sanitize_text_field((string) $item['id']);
                                    $brand = $this->api_text($item['marca'] ?? '');
                                    $model = $this->api_text($item['modelo'] ?? '');
                                    $title = trim($brand . ' ' . $model);
                                    if ($title === '') {
                                        $title = 'Equipo TMD';
                                    }

                                    $year = $this->api_year($item['ano'] ?? null);
                                    $state = $this->api_text($item['estado']['nombre'] ?? '', 'Consultar');
                                    $state_class = sanitize_title($state);
                                    $specs = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
                                    $energy = $this->api_text($specs['subtipo'] ?? '');
                                    if (strtolower($energy) === 'electrico') {
                                        $energy = 'Eléctrico';
                                    } elseif ($energy !== '') {
                                        $energy = ucfirst(strtolower($energy));
                                    }
                                    $capacity = $this->api_number($specs['capacidad_ton'] ?? 0, ' ton');
                                    $height = $this->api_number($specs['alturaLevantamiento_m'] ?? 0, ' m');
                                    $image = esc_url($item['media']['imagenPrincipal'] ?? '');
                                    $image_scheme = wp_parse_url($image, PHP_URL_SCHEME);
                                    if (!in_array($image_scheme, ['http', 'https'], true)) {
                                        $image = '';
                                    }
                                    $is_available = (int) ($item['estado']['codigo'] ?? -1) === 1;
                                    $detail_url = $is_available
                                        ? add_query_arg('ficha', $id, home_url('/equipos/'))
                                        : add_query_arg([
                                            'equipo_id' => $id,
                                            'equipo' => $title,
                                        ], home_url('/nosotros/contacto/'));
                                    $cta_label = $is_available ? 'Ver ficha →' : 'Consultar equipo →';
                                    ?>
                                    <article class="tm-eqd-card" data-tm-eqd-card data-equipment-id="<?php echo esc_attr($id); ?>">
                                        <a class="tm-eqd-media" href="<?php echo esc_url($detail_url); ?>" aria-label="<?php echo esc_attr($title); ?>">
                                            <span class="tm-eqd-badge tm-eqd-badge-<?php echo esc_attr($state_class); ?>"><?php echo esc_html($state); ?></span>
                                            <?php if ($image): ?>
                                                <img src="<?php echo $image; ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                                            <?php else: ?>
                                                <span class="tm-eqd-placeholder">Sin imagen</span>
                                            <?php endif; ?>
                                        </a>

                                        <div class="tm-eqd-info">
                                            <h3><?php echo esc_html($title); ?></h3>
                                            <?php if ($year): ?><p class="tm-eqd-year"><?php echo esc_html($year); ?></p><?php endif; ?>

                                            <div class="tm-eqd-chips" aria-label="Características del equipo">
                                                <?php if ($energy): ?><span><?php echo esc_html($energy); ?></span><?php endif; ?>
                                                <?php if ($capacity): ?><span><?php echo esc_html($capacity); ?></span><?php endif; ?>
                                                <?php if ($height): ?><span><?php echo esc_html($height); ?></span><?php endif; ?>
                                            </div>

                                            <a class="tm-eqd-cta" href="<?php echo esc_url($detail_url); ?>"><?php echo esc_html($cta_label); ?></a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button class="tm-eqd-arrow tm-eqd-next" type="button" data-tm-eqd-next aria-label="Ver más equipos">›</button>
                    </div>

                    <div class="tm-eqd-dots" data-tm-eqd-dots aria-label="Indicadores del carrusel"></div>
                </div>
            </section>
            <?php

            return ob_get_clean();
        }

        public function render_shortcode($atts) {
            $options = $this->get_options();
            $atts = shortcode_atts([
                'limit' => 8,
                'catalogo' => $options['catalog_url'],
            ], $atts, 'tm_equipos_destacados_v2');

            $post_types = $this->get_equipment_post_types();
            if (empty($post_types)) {
                if (current_user_can('manage_options')) {
                    return '<div class="tm-eqd-admin-message"><strong>TM Destacados:</strong> No detecté post type de equipos. El plugin busca <code>tmd_equipo</code>, <code>equipo</code>, <code>equipos</code> o <code>product</code>.</div>';
                }
                return '';
            }

            $limit = max(1, min(8, absint($atts['limit'])));
            $api_items = $this->featured_api_items($limit);

            if ($api_items) {
                return $this->render_api_shortcode($api_items, $atts);
            }

            $query = new WP_Query([
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => 50,
                'no_found_rows' => true,
                'meta_query' => [
                    [
                        'key' => '_tm_eqd_destacado',
                        'value' => '1',
                        'compare' => '=',
                    ],
                ],
            ]);

            if (!$query->have_posts()) {
                if (current_user_can('manage_options')) {
                    return '<div class="tm-eqd-admin-message"><strong>TM Destacados:</strong> El shortcode está funcionando, pero no hay equipos marcados como destacados. Edita una ficha de equipo y marca “Mostrar en Equipos Destacados”.</div>';
                }
                return '';
            }

            $posts = $query->posts;
            usort($posts, function ($a, $b) {
                $orden_a = get_post_meta($a->ID, '_tm_eqd_orden', true);
                $orden_b = get_post_meta($b->ID, '_tm_eqd_orden', true);
                $orden_a = $orden_a === '' ? 999 : intval($orden_a);
                $orden_b = $orden_b === '' ? 999 : intval($orden_b);

                if ($orden_a === $orden_b) {
                    return strtotime($b->post_date_gmt) <=> strtotime($a->post_date_gmt);
                }

                return $orden_a <=> $orden_b;
            });

            $posts = array_slice($posts, 0, $limit);
            $section_id = wp_unique_id('tm-eqd-');

            ob_start();
            ?>
            <section id="<?php echo esc_attr($section_id); ?>" class="tm-eqd-section" data-tm-eqd aria-label="Equipos destacados">
                <div class="tm-eqd-container">
                    <div class="tm-eqd-head">
                        <div class="tm-eqd-heading">
                            <span class="tm-eqd-eyebrow">Stock visible</span>
                            <h2>Equipos destacados</h2>
                            <p>Selección de equipos disponibles en stock</p>
                        </div>
                        <a class="tm-eqd-all" href="<?php echo esc_url($atts['catalogo']); ?>">Ver todos →</a>
                    </div>

                    <div class="tm-eqd-carousel">
                        <button class="tm-eqd-arrow tm-eqd-prev" type="button" data-tm-eqd-prev aria-label="Ver equipos anteriores">‹</button>

                        <div class="tm-eqd-viewport" data-tm-eqd-viewport>
                            <div class="tm-eqd-track" data-tm-eqd-track>
                                <?php foreach ($posts as $post_item): ?>
                                    <?php
                                    $id = $post_item->ID;
                                    $modelo = $this->meta_value($id, ['_tm_eqd_modelo'], get_the_title($id));
                                    $anio = $this->meta_value($id, ['_tm_eqd_anio']);
                                    $energia = $this->meta_value($id, ['_tm_eqd_energia']);
                                    $capacidad = $this->meta_value($id, ['_tm_eqd_capacidad']);
                                    $altura = $this->meta_value($id, ['_tm_eqd_altura']);
                                    $condicion = $this->meta_value($id, ['_tm_eqd_condicion'], 'Usado');
                                    $condicion_class = sanitize_title($condicion);
                                    $permalink = get_permalink($id);
                                    $image = get_the_post_thumbnail_url($id, 'medium_large');
                                    ?>
                                    <article class="tm-eqd-card" data-tm-eqd-card>
                                        <a class="tm-eqd-media" href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($modelo); ?>">
                                            <span class="tm-eqd-badge tm-eqd-badge-<?php echo esc_attr($condicion_class); ?>"><?php echo esc_html($condicion); ?></span>
                                            <?php if ($image): ?>
                                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($modelo); ?>" loading="lazy">
                                            <?php else: ?>
                                                <span class="tm-eqd-placeholder">Sin imagen</span>
                                            <?php endif; ?>
                                        </a>

                                        <div class="tm-eqd-info">
                                            <h3><?php echo esc_html($modelo); ?></h3>
                                            <?php if ($anio): ?>
                                                <p class="tm-eqd-year"><?php echo esc_html($anio); ?></p>
                                            <?php endif; ?>

                                            <div class="tm-eqd-chips" aria-label="Características del equipo">
                                                <?php if ($energia): ?><span><?php echo esc_html($energia); ?></span><?php endif; ?>
                                                <?php if ($capacidad): ?><span><?php echo esc_html($capacidad); ?></span><?php endif; ?>
                                                <?php if ($altura): ?><span><?php echo esc_html($altura); ?></span><?php endif; ?>
                                            </div>

                                            <a class="tm-eqd-cta" href="<?php echo esc_url($permalink); ?>">Ver ficha →</a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button class="tm-eqd-arrow tm-eqd-next" type="button" data-tm-eqd-next aria-label="Ver más equipos">›</button>
                    </div>

                    <div class="tm-eqd-dots" data-tm-eqd-dots aria-label="Indicadores del carrusel"></div>
                </div>
            </section>
            <?php
            wp_reset_postdata();

            return ob_get_clean();
        }

        private function social_links() {
            $options = $this->get_options();

            return [
                'facebook' => [
                    'url' => $options['facebook_url'],
                    'label' => 'Facebook',
                    'icon' => 'f',
                ],
                'instagram' => [
                    'url' => $options['instagram_url'],
                    'label' => 'Instagram',
                    'icon' => 'ig',
                ],
                'youtube' => [
                    'url' => $options['youtube_url'],
                    'label' => 'YouTube',
                    'icon' => 'yt',
                ],
            ];
        }

        public function render_social_shortcode() {
            $links = array_filter($this->social_links(), function ($link) {
                return !empty($link['url']);
            });

            if (empty($links)) {
                return '';
            }

            ob_start();
            ?>
            <div class="tmd-footer-social tm-footer-social-shortcode">
                <?php foreach ($links as $platform => $link): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($link['label']); ?>" data-tm-social="<?php echo esc_attr($platform); ?>"><?php echo esc_html($link['icon']); ?></a>
                <?php endforeach; ?>
            </div>
            <?php
            return ob_get_clean();
        }

        public function print_inline_social_config() {
            $options = $this->get_options();
            if ($options['auto_footer_social'] !== '1') {
                return;
            }

            $links = [];
            foreach ($this->social_links() as $platform => $link) {
                if (!empty($link['url'])) {
                    $links[$platform] = esc_url_raw($link['url']);
                }
            }

            if (empty($links)) {
                return;
            }

            echo "\n<script>window.tmEqdSocialLinks = " . wp_json_encode($links) . ";</script>\n";
        }
    }

    TM_Equipos_Destacados_V2::instance();
}
