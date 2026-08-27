<?php
/**
 * Flujo público de cotización y configuración canónica de contacto.
 */

defined('ABSPATH') || exit;

if (! function_exists('tmd_conversion_contact_config')) {
    function tmd_conversion_contact_config() {
        return [
            'brand_name' => 'Tecnimontacargas',
            'official_whatsapp' => '573015556180',
            'official_whatsapp_label' => '301 555 6180',
            'email' => 'info@tmdual.com',
        ];
    }
}

if (! function_exists('tmd_conversion_whatsapp_url')) {
    function tmd_conversion_whatsapp_url($message = '') {
        $config = tmd_conversion_contact_config();
        $url = 'https://wa.me/' . rawurlencode($config['official_whatsapp']);

        if ($message !== '') {
            $url = add_query_arg('text', $message, $url);
        }

        return $url;
    }
}

if (! function_exists('tmd_conversion_normalize_type')) {
    function tmd_conversion_normalize_type($type) {
        $type = strtolower(trim((string) $type));

        if (in_array($type, ['bateria', 'batería', 'energia', 'energía'], true)) {
            return 'bateria';
        }

        if (in_array($type, ['equipo', 'montacargas'], true)) {
            return 'montacargas';
        }

        return '';
    }
}

if (! function_exists('tmd_conversion_request_value')) {
    function tmd_conversion_request_value($query, $key) {
        if (! is_array($query) || ! isset($query[$key]) || is_array($query[$key])) {
            return '';
        }

        return sanitize_text_field(wp_unslash((string) $query[$key]));
    }
}

if (! function_exists('tmd_conversion_quote_context')) {
    function tmd_conversion_quote_context($query = null) {
        $query = is_array($query) ? $query : $_GET;
        $id = tmd_conversion_request_value($query, 'tmd_cotizacion_id');
        $type = tmd_conversion_normalize_type(tmd_conversion_request_value($query, 'tmd_tipo_cotizacion'));
        $title = tmd_conversion_request_value($query, 'tmd_cotizacion');

        if ($id === '') {
            $id = tmd_conversion_request_value($query, 'equipo_id');
        }

        if ($title === '') {
            $equipment = tmd_conversion_request_value($query, 'equipo');
            $energy = tmd_conversion_request_value($query, 'tmd_cotizacion_energia');

            if ($energy === '') {
                $energy = tmd_conversion_request_value($query, 'energia');
            }

            if ($equipment !== '') {
                $title = $equipment;
                $type = 'montacargas';
            } elseif ($energy !== '') {
                $title = $energy;
                $type = 'bateria';
            }
        }

        if ($title === '' || $type === '') {
            return [];
        }

        return [
            'id' => $id,
            'type' => $type,
            'type_label' => $type === 'bateria' ? 'Energía' : 'Equipo',
            'title' => $title,
        ];
    }
}

if (! function_exists('tmd_conversion_quote_url')) {
    function tmd_conversion_quote_url($type, $id, $title, $base_url = '') {
        $type = tmd_conversion_normalize_type($type);
        $id = sanitize_text_field((string) $id);
        $title = sanitize_text_field((string) $title);
        $base_url = $base_url !== '' ? $base_url : home_url('/nosotros/contacto/');

        if ($type === '' || $title === '') {
            return $base_url;
        }

        return add_query_arg([
            'tmd_cotizacion_id' => $id,
            'tmd_tipo_cotizacion' => $type,
            'tmd_cotizacion' => $title,
        ], $base_url);
    }
}

if (! function_exists('tmd_conversion_context_html')) {
    function tmd_conversion_context_html($context) {
        if (empty($context['title']) || empty($context['type_label'])) {
            return '';
        }

        $catalog_url = ($context['type'] ?? '') === 'bateria' ? home_url('/energia/') : home_url('/equipos/');
        $context_id = (string) ($context['id'] ?? '');
        $id_html = $context_id !== ''
            ? '<span>ID: ' . esc_html($context_id) . '</span>'
            : '';

        return '<section class="tmd-contact-source-box-server tmd-quote-context" aria-labelledby="tmd-quote-context-title">'
            . '<span class="tmd-quote-context__eyebrow">Solicitud de cotización</span>'
            . '<div class="tmd-quote-context__body">'
            . '<strong id="tmd-quote-context-title">' . esc_html($context['title']) . '</strong>'
            . '<div class="tmd-quote-context__meta"><span>Tipo: ' . esc_html($context['type_label']) . '</span>' . $id_html . '</div>'
            . '</div>'
            . '<a class="tmd-quote-context__change" href="' . esc_url($catalog_url) . '">Elegir otro</a>'
            . '</section>';
    }
}

if (! function_exists('tmd_conversion_inject_context')) {
    function tmd_conversion_inject_context($content) {
        if (! is_page(57) || strpos($content, 'tmd-quote-context') !== false) {
            return $content;
        }

        $context_html = tmd_conversion_context_html(tmd_conversion_quote_context());
        if ($context_html === '') {
            return $content;
        }

        $section_pattern = '/(<section\b[^>]*class=(["\'])[^"\']*\btmd-contact-grid\b[^"\']*\2[^>]*>)/i';
        $with_context = preg_replace($section_pattern, '$1' . $context_html, $content, 1, $count);

        if ($count === 1 && is_string($with_context)) {
            return $with_context;
        }

        $form_pattern = '/(<form\b[^>]*class=(["\'])[^"\']*(?:tmd-form-card|wpcf7-form)[^"\']*\2[^>]*>)/i';
        $with_context = preg_replace($form_pattern, $context_html . '$1', $content, 1, $count);

        return $count === 1 && is_string($with_context) ? $with_context : $content;
    }
}

add_filter('the_content', 'tmd_conversion_inject_context', 25);

add_filter('wpcf7_form_tag', function ($tag) {
    if (! is_page(57) || ! $tag instanceof WPCF7_FormTag) {
        return $tag;
    }

    $context = tmd_conversion_quote_context();
    if (! $context) {
        return $tag;
    }

    $values = [
        'tmd_tipo_cotizacion' => $context['type_label'],
        'tmd_cotizacion' => $context['title'],
        'tmd_cotizacion_id' => $context['id'],
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
        $tag->options[] = $context['type'] === 'bateria' ? 'default:5' : 'default:3';
    }

    if ($tag->name === 'message') {
        $message = 'Hola, quiero recibir información sobre: ' . $context['title'];
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

add_action('wp_enqueue_scripts', function () {
    if (! is_page(57)) {
        return;
    }

    $css_path = get_stylesheet_directory() . '/assets/css/tmd-contact-conversion.css';
    wp_enqueue_style(
        'tmd-contact-conversion',
        get_stylesheet_directory_uri() . '/assets/css/tmd-contact-conversion.css',
        [],
        file_exists($css_path) ? filemtime($css_path) : '1.0.0'
    );

    $js_path = get_stylesheet_directory() . '/assets/js/tmd-contact-conversion.js';
    wp_enqueue_script(
        'tmd-contact-conversion',
        get_stylesheet_directory_uri() . '/assets/js/tmd-contact-conversion.js',
        [],
        file_exists($js_path) ? filemtime($js_path) : '1.0.0',
        true
    );

    wp_localize_script('tmd-contact-conversion', 'tmdContactConversion', [
        'whatsappUrl' => tmd_conversion_whatsapp_url('Hola, necesito ayuda con una cotización.'),
    ]);
}, 130);
