<?php
/**
 * Public catalog backed by Inventario Firebase API.
 */

if (! defined('ABSPATH')) {
    exit;
}

const TMD_INVENTORY_API_URL = 'https://us-central1-inventariomaquinas-t.cloudfunctions.net/listarEquiposWordpress';
const TMD_INVENTORY_API_CACHE_KEY = 'tmd_inventory_api_payload_v1';
const TMD_INVENTORY_API_FALLBACK_KEY = 'tmd_inventory_api_last_good_v1';

function tmd_inventory_api_payload() {
    $cached = get_transient(TMD_INVENTORY_API_CACHE_KEY);
    if (is_array($cached) && ! empty($cached['items'])) {
        return $cached;
    }

    $response = wp_remote_get(TMD_INVENTORY_API_URL, [
        'timeout' => 18,
        'redirection' => 2,
        'headers' => ['Accept' => 'application/json'],
        'user-agent' => 'TecniMontacargasWordPress/1.0; ' . home_url('/'),
    ]);

    if (! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (is_array($body) && ! empty($body['ok']) && ! empty($body['items']) && is_array($body['items'])) {
            $payload = [
                'items' => array_values($body['items']),
                'generatedAt' => sanitize_text_field((string) ($body['generatedAt'] ?? '')),
                'fetchedAt' => time(),
                'source' => 'live',
            ];
            set_transient(TMD_INVENTORY_API_CACHE_KEY, $payload, DAY_IN_SECONDS);
            update_option(TMD_INVENTORY_API_FALLBACK_KEY, $payload, false);
            return $payload;
        }
    }

    $fallback = get_option(TMD_INVENTORY_API_FALLBACK_KEY, []);
    if (is_array($fallback) && ! empty($fallback['items'])) {
        $fallback['source'] = 'fallback';
        set_transient(TMD_INVENTORY_API_CACHE_KEY, $fallback, 2 * MINUTE_IN_SECONDS);
        return $fallback;
    }

    return ['items' => [], 'generatedAt' => '', 'fetchedAt' => 0, 'source' => 'error'];
}

function tmd_inventory_api_items_by_type($type) {
    $payload = tmd_inventory_api_payload();
    return array_values(array_filter($payload['items'], static function ($item) use ($type) {
        return is_array($item)
            && ($item['tipo'] ?? '') === $type
            && (int) ($item['estado']['codigo'] ?? 0) === 1;
    }));
}

function tmd_inventory_api_request_value($key) {
    if (! isset($_GET[$key]) || is_array($_GET[$key])) {
        return '';
    }
    return sanitize_text_field(wp_unslash($_GET[$key]));
}

function tmd_inventory_api_text($value) {
    if (is_bool($value)) {
        return $value ? 'Sí' : 'No';
    }
    if (! is_scalar($value)) {
        return '';
    }
    $value = trim((string) $value);
    return in_array(strtolower($value), ['', 'null', 'n/a', '-'], true) ? '' : $value;
}

function tmd_inventory_api_number($value, $suffix = '') {
    if (! is_numeric($value) || (float) $value <= 0) {
        return '';
    }
    $number = rtrim(rtrim(number_format_i18n((float) $value, 2), '0'), ',.');
    return $number . $suffix;
}

function tmd_inventory_api_title($item, $type) {
    $parts = [];
    $marca = tmd_inventory_api_text($item['marca'] ?? '');
    $modelo = tmd_inventory_api_text($item['modelo'] ?? '');
    $referencia = tmd_inventory_api_text($item['referencia'] ?? '');

    if ($type === 'bateria') {
        $parts[] = 'Batería';
        if ($marca) { $parts[] = $marca; }
        if ($modelo) { $parts[] = $modelo; }
        elseif ($referencia) { $parts[] = $referencia; }
    } else {
        if ($marca) { $parts[] = $marca; }
        if ($modelo) { $parts[] = $modelo; }
        if (! $parts) { $parts[] = 'Montacargas disponible'; }
    }

    return implode(' ', array_unique($parts));
}

function tmd_inventory_api_classification_key($value) {
    $value = strtoupper(remove_accents(tmd_inventory_api_text($value)));
    $value = str_replace(['_', '-'], ' ', $value);
    return trim((string) preg_replace('/\s+/', ' ', $value));
}

function tmd_inventory_api_classification($item) {
    $spec = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
    $model = tmd_inventory_api_classification_key($item['modelo'] ?? '');
    $subtype = tmd_inventory_api_classification_key($spec['subtipo'] ?? '');
    $conditions = array_filter([
        tmd_inventory_api_classification_key($spec['condicionEspecial'] ?? ''),
        tmd_inventory_api_classification_key($spec['condicionEspecial2'] ?? ''),
    ]);
    $reach = tmd_inventory_api_classification_key($spec['tipoReach'] ?? '');

    if (str_contains($model, 'STOCKPICKER')
        || preg_match('/^EKS\b/', $model)
        || str_contains($subtype, 'STOP PICKER')
        || str_contains($subtype, 'STOCK PICKER')) {
        return ['category' => 'Tomapedidos', 'subcategory' => 'Tomapedidos de alto nivel'];
    }

    if (in_array('CONTRABALANCEADO', $conditions, true)
        || in_array('DUAL', $conditions, true)
        || $subtype === 'COMBUSTIBLE'
        || preg_match('/^EFG\b/', $model)) {
        if ($subtype === 'COMBUSTIBLE' || in_array('DUAL', $conditions, true)) {
            return ['category' => 'Contrabalanceados', 'subcategory' => 'Combustión'];
        }
        return [
            'category' => 'Contrabalanceados',
            'subcategory' => preg_match('/^EFG\s*21[3-8]/', $model)
                ? 'Eléctricos de 3 ruedas'
                : 'Eléctricos de 4 ruedas',
        ];
    }

    if ($model === 'RD5200' || in_array('PANTOGRAFO', $conditions, true)) {
        return [
            'category' => 'Reach',
            'subcategory' => $reach === 'DOBLE'
                ? 'Pantógrafo doble profundidad'
                : 'Pantógrafo sencillo',
        ];
    }

    if ($subtype === 'PORTA ESTIBAS' || preg_match('/^(PE|LWE)/', $model)) {
        return [
            'category' => 'Estibadores',
            'subcategory' => in_array('TRACCION MANUAL', $conditions, true)
                ? 'Estibadores manuales'
                : 'Estibadores eléctricos',
        ];
    }

    if ($subtype === 'APILADOR' || preg_match('/^(ERC(?!060)|ESC|EJC|ES(?:\s|$))/', $model)) {
        return ['category' => 'Apiladores', 'subcategory' => 'Apiladores eléctricos'];
    }

    if (preg_match('/^(ETV|ETVC)/', $model) || in_array('RETRACTIL', $conditions, true)) {
        return ['category' => 'Retráctiles', 'subcategory' => 'Retráctiles de mástil móvil'];
    }

    return ['category' => '', 'subcategory' => ''];
}

function tmd_inventory_api_brand_options($items) {
    $options = [];
    foreach ($items as $item) {
        $brand = tmd_inventory_api_text($item['marca'] ?? '');
        if ($brand) {
            $options[$brand] = ($options[$brand] ?? 0) + 1;
        }
    }
    uksort($options, 'strnatcasecmp');
    return $options;
}

function tmd_inventory_api_category_options($items) {
    $counts = [];
    foreach ($items as $item) {
        $category = tmd_inventory_api_classification($item)['category'];
        if ($category) {
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }
    }

    $options = [];
    foreach (['Estibadores', 'Apiladores', 'Reach', 'Retráctiles', 'Tomapedidos', 'Contrabalanceados'] as $category) {
        if (! empty($counts[$category])) {
            $options[$category] = $counts[$category];
        }
    }
    return $options;
}

function tmd_inventory_api_subcategory_options($items) {
    $counts = [];
    $categories = [];
    foreach ($items as $item) {
        $classification = tmd_inventory_api_classification($item);
        $subcategory = $classification['subcategory'];
        if ($subcategory) {
            $counts[$subcategory] = ($counts[$subcategory] ?? 0) + 1;
            $categories[$subcategory] = $classification['category'];
        }
    }

    $order = [
        'Estibadores manuales',
        'Estibadores eléctricos',
        'Apiladores eléctricos',
        'Retráctiles de mástil móvil',
        'Pantógrafo sencillo',
        'Pantógrafo doble profundidad',
        'Tomapedidos de alto nivel',
        'Eléctricos de 3 ruedas',
        'Eléctricos de 4 ruedas',
        'Combustión',
    ];
    $options = [];
    foreach ($order as $subcategory) {
        if (! empty($counts[$subcategory])) {
            $options[$subcategory] = [
                'count' => $counts[$subcategory],
                'category' => $categories[$subcategory],
            ];
        }
    }
    return $options;
}

function tmd_inventory_api_subtype_options($items) {
    $options = [];
    foreach ($items as $item) {
        $subtype = tmd_inventory_api_text($item['especificaciones']['subtipo'] ?? '');
        if ($subtype) {
            $options[$subtype] = ($options[$subtype] ?? 0) + 1;
        }
    }
    uksort($options, 'strnatcasecmp');
    return $options;
}

function tmd_inventory_api_spec_options($items, $key) {
    $options = [];
    foreach ($items as $item) {
        $value = tmd_inventory_api_text($item['especificaciones'][$key] ?? '');
        if ($value) {
            $options[$value] = ($options[$value] ?? 0) + 1;
        }
    }
    uksort($options, 'strnatcasecmp');
    return $options;
}

function tmd_inventory_api_height_m($value) {
    if (! is_numeric($value) || (float) $value <= 0) {
        return 0.0;
    }
    $height = (float) $value;
    return $height > 20 ? $height / 100 : $height;
}

function tmd_inventory_api_height_matches($height, $range) {
    if (! $range) {
        return true;
    }
    if ($height <= 0) {
        return false;
    }
    [$min, $max] = array_pad(array_map('floatval', explode('-', $range, 2)), 2, 0.0);
    return $height >= $min && ($max <= 0 || $height < $max);
}

function tmd_inventory_api_voltage_options($items) {
    $options = [];
    foreach ($items as $item) {
        $voltage = tmd_inventory_api_number($item['especificaciones']['voltaje_v'] ?? 0, ' V');
        if ($voltage) {
            $options[$voltage] = ($options[$voltage] ?? 0) + 1;
        }
    }
    uksort($options, 'strnatcasecmp');
    return $options;
}

function tmd_inventory_api_battery_capacity_options($items) {
    $options = [];
    foreach ($items as $item) {
        $capacity = tmd_inventory_api_number($item['especificaciones']['amperaje_ah'] ?? 0, ' Ah');
        if ($capacity) {
            $options[$capacity] = ($options[$capacity] ?? 0) + 1;
        }
    }
    uksort($options, 'strnatcasecmp');
    return $options;
}

function tmd_inventory_api_select($name, $label, $options, $selected) {
    echo '<label class="tmd-api-filter">';
    echo '<span>' . esc_html($label) . '</span>';
    echo '<select name="' . esc_attr($name) . '">';
    echo '<option value="">Todos</option>';
    foreach ($options as $value => $count) {
        echo '<option value="' . esc_attr($value) . '"' . selected($selected, (string) $value, false) . '>';
        echo esc_html(is_numeric($count) ? $value . ' (' . $count . ')' : (string) $count);
        echo '</option>';
    }
    echo '</select>';
    echo '</label>';
}

function tmd_inventory_api_subcategory_select($options, $selected) {
    echo '<label class="tmd-api-filter">';
    echo '<span>Subcategoría</span>';
    echo '<select name="api_subcategoria" data-api-subcategory-filter>';
    echo '<option value="">Todas</option>';
    foreach ($options as $value => $details) {
        echo '<option value="' . esc_attr($value) . '" data-api-category="' . esc_attr($details['category']) . '"' . selected($selected, (string) $value, false) . '>';
        echo esc_html($value . ' (' . $details['count'] . ')');
        echo '</option>';
    }
    echo '</select>';
    echo '</label>';
}

function tmd_inventory_api_filter_items($items, $type) {
    $brand = tmd_inventory_api_request_value('api_marca');
    $category = tmd_inventory_api_request_value('api_categoria');
    $subcategory = tmd_inventory_api_request_value('api_subcategoria');
    $collapsed_height = tmd_inventory_api_request_value('api_altura_colapsada');
    $lift_height = tmd_inventory_api_request_value('api_altura_levante');
    $operator = tmd_inventory_api_request_value('api_operario');
    $reach = tmd_inventory_api_request_value('api_reach');
    $voltage = tmd_inventory_api_request_value('api_voltaje');
    $battery_capacity = tmd_inventory_api_request_value('api_capacidad');
    $condition = tmd_inventory_api_request_value('api_condicion');

    return array_values(array_filter($items, static function ($item) use ($type, $brand, $category, $subcategory, $collapsed_height, $lift_height, $condition, $operator, $reach, $voltage, $battery_capacity) {
        if ($brand && strcasecmp(tmd_inventory_api_text($item['marca'] ?? ''), $brand) !== 0) {
            return false;
        }

        if ($type === 'montacargas') {
            $spec = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
            $classification = tmd_inventory_api_classification($item);
            if ($category && strcasecmp($classification['category'], $category) !== 0) { return false; }
            if ($subcategory && strcasecmp($classification['subcategory'], $subcategory) !== 0) { return false; }
            if (! tmd_inventory_api_height_matches(tmd_inventory_api_height_m($spec['alturaMastilContraido_m'] ?? 0), $collapsed_height)) {
                return false;
            }
            if (! tmd_inventory_api_height_matches(tmd_inventory_api_height_m($spec['alturaLevantamiento_m'] ?? 0), $lift_height)) {
                return false;
            }
            if ($condition && strcasecmp(tmd_inventory_api_text($spec['condicionEspecial'] ?? ''), $condition) !== 0) { return false; }
            if ($operator && strcasecmp(tmd_inventory_api_text($spec['posicionOperario'] ?? ''), $operator) !== 0) { return false; }
            if ($reach && strcasecmp(tmd_inventory_api_text($spec['tipoReach'] ?? ''), $reach) !== 0) { return false; }
        } else {
            $item_voltage = tmd_inventory_api_number($item['especificaciones']['voltaje_v'] ?? 0, ' V');
            if ($voltage && $item_voltage !== $voltage) {
                return false;
            }
            $item_capacity = tmd_inventory_api_number($item['especificaciones']['amperaje_ah'] ?? 0, ' Ah');
            if ($battery_capacity && $item_capacity !== $battery_capacity) {
                return false;
            }
            $item_condition = ! empty($item['esNueva']) ? 'Nueva' : 'Usada';
            if ($condition && $item_condition !== $condition) {
                return false;
            }
        }
        return true;
    }));
}

function tmd_inventory_api_status($type, $count) {
    $payload = tmd_inventory_api_payload();
    $label = $type === 'montacargas' ? 'equipos' : 'baterías';
    $date = '';
    if (! empty($payload['generatedAt'])) {
        $timestamp = strtotime($payload['generatedAt']);
        if ($timestamp) {
            $date = wp_date('j M Y, g:i a', $timestamp);
        }
    }
    echo '<div class="tmd-api-status" data-tmd-api-status>';
    echo '<strong>' . esc_html($count . ' ' . $label . ' disponibles') . '</strong>';
    echo '<span>Inventario real' . ($date ? ' · actualizado ' . esc_html($date) : '') . '</span>';
    echo '</div>';
}

function tmd_inventory_api_filter_form($type) {
    $items = tmd_inventory_api_items_by_type($type);
    if (! $items) {
        return '<div class="tmd-api-message">Inventario temporalmente no disponible. Intente nuevamente en unos minutos.</div>';
    }

    $action = $type === 'montacargas' ? home_url('/equipos/') : home_url('/energia/');
    ob_start();
    echo '<form class="tmd-api-filters" method="get" action="' . esc_url($action) . '">';
    tmd_inventory_api_select('api_marca', 'Marca', tmd_inventory_api_brand_options($items), tmd_inventory_api_request_value('api_marca'));

    if ($type === 'montacargas') {
        tmd_inventory_api_select('api_categoria', 'Categoría', tmd_inventory_api_category_options($items), tmd_inventory_api_request_value('api_categoria'));
        tmd_inventory_api_subcategory_select(tmd_inventory_api_subcategory_options($items), tmd_inventory_api_request_value('api_subcategoria'));
        tmd_inventory_api_select('api_altura_colapsada', 'Altura colapsada', [
            '0-2' => 'Hasta 2 m',
            '2-3' => '2 a 3 m',
            '3-4' => '3 a 4 m',
            '4-0' => '4 m o más',
        ], tmd_inventory_api_request_value('api_altura_colapsada'));
        tmd_inventory_api_select('api_altura_levante', 'Altura de levante', [
            '0-4.5' => 'Hasta 4.5 m',
            '4.5-6' => '4.5 a 6 m',
            '6-8' => '6 a 8 m',
            '8-10' => '8 a 10 m',
            '10-0' => '10 m o más',
        ], tmd_inventory_api_request_value('api_altura_levante'));
        tmd_inventory_api_select('api_condicion', 'Condición', tmd_inventory_api_spec_options($items, 'condicionEspecial'), tmd_inventory_api_request_value('api_condicion'));
        tmd_inventory_api_select('api_operario', 'Operario', tmd_inventory_api_spec_options($items, 'posicionOperario'), tmd_inventory_api_request_value('api_operario'));
        tmd_inventory_api_select('api_reach', 'Reach', tmd_inventory_api_spec_options($items, 'tipoReach'), tmd_inventory_api_request_value('api_reach'));
    } else {
        tmd_inventory_api_select('api_voltaje', 'Voltaje', tmd_inventory_api_voltage_options($items), tmd_inventory_api_request_value('api_voltaje'));
        tmd_inventory_api_select('api_capacidad', 'Capacidad', tmd_inventory_api_battery_capacity_options($items), tmd_inventory_api_request_value('api_capacidad'));
        tmd_inventory_api_select('api_condicion', 'Condición', ['Nueva' => 'Nueva', 'Usada' => 'Usada'], tmd_inventory_api_request_value('api_condicion'));
    }

    echo '<button type="submit">Aplicar filtros</button>';
    echo '<a href="' . esc_url($action) . '">Limpiar filtros</a>';
    echo '</form>';
    return ob_get_clean();
}

function tmd_inventory_api_specs($item, $type, $full = false) {
    $spec = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
    $state = tmd_inventory_api_text($item['estado']['nombre'] ?? 'Disponible') ?: 'Disponible';
    if ($type === 'montacargas') {
        $values = [
            'Marca' => tmd_inventory_api_text($item['marca'] ?? ''),
            'Modelo' => tmd_inventory_api_text($item['modelo'] ?? ''),
            'Tipo' => tmd_inventory_api_text($spec['subtipo'] ?? ''),
            'Capacidad' => tmd_inventory_api_number($spec['capacidad_ton'] ?? 0, ' ton'),
            'Altura de levante' => tmd_inventory_api_number($spec['alturaLevantamiento_m'] ?? 0, ' m'),
            'Año' => tmd_inventory_api_text($item['ano'] ?? ''),
            'Estado' => $state,
        ];
        if ($full) {
            $values['Mástil contraído'] = tmd_inventory_api_number($spec['alturaMastilContraido_m'] ?? 0, ' m');
            $values['Posición del operario'] = tmd_inventory_api_text($spec['posicionOperario'] ?? '');
            $values['Tipo reach'] = tmd_inventory_api_text($spec['tipoReach'] ?? '');
            $values['Condición especial'] = tmd_inventory_api_text($spec['condicionEspecial'] ?? '');
        }
    } else {
        $values = [
            'Marca' => tmd_inventory_api_text($item['marca'] ?? ''),
            'Referencia' => tmd_inventory_api_text($item['referencia'] ?? ''),
            'Voltaje' => tmd_inventory_api_number($spec['voltaje_v'] ?? 0, ' V'),
            'Capacidad' => tmd_inventory_api_number($spec['amperaje_ah'] ?? 0, ' Ah'),
            'Condición' => ! empty($item['esNueva']) ? 'Nueva' : 'Usada',
            'Estado' => $state,
        ];
    }
    return array_filter($values, static fn($value) => $value !== '');
}

function tmd_inventory_api_card($item, $type) {
    $title = tmd_inventory_api_title($item, $type);
    $image = esc_url($item['media']['imagenPrincipal'] ?? '');
    $state = tmd_inventory_api_text($item['estado']['nombre'] ?? 'Disponible') ?: 'Disponible';
    $detail_url = add_query_arg('ficha', rawurlencode((string) ($item['id'] ?? '')), $type === 'montacargas' ? home_url('/equipos/') : home_url('/energia/'));
    $contact_args = [
        'equipo_id' => (string) ($item['id'] ?? ''),
    ];
    $contact_args[$type === 'bateria' ? 'tmd_cotizacion_energia' : 'equipo'] = $title;
    $contact_url = add_query_arg($contact_args, home_url('/nosotros/contacto/'));
    $card_class = $type === 'montacargas' ? 'tmd-equipment-card' : 'tmde-card';
    $image_class = $type === 'montacargas' ? 'tmd-equipment-image' : 'tmde-image';
    $body_class = $type === 'montacargas' ? 'tmd-equipment-body' : 'tmde-card-body';
    $spec = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
    $classification = $type === 'montacargas'
        ? tmd_inventory_api_classification($item)
        : ['category' => '', 'subcategory' => ''];
    $filter_data = [
        'data-api-brand' => tmd_inventory_api_text($item['marca'] ?? ''),
        'data-api-category' => $classification['category'],
        'data-api-subcategory' => $classification['subcategory'],
        'data-api-condition' => $type === 'montacargas'
            ? tmd_inventory_api_text($spec['condicionEspecial'] ?? '')
            : (! empty($item['esNueva']) ? 'Nueva' : 'Usada'),
        'data-api-collapsed-height' => $type === 'montacargas'
            ? (string) tmd_inventory_api_height_m($spec['alturaMastilContraido_m'] ?? 0)
            : '',
        'data-api-lift-height' => $type === 'montacargas'
            ? (string) tmd_inventory_api_height_m($spec['alturaLevantamiento_m'] ?? 0)
            : '',
        'data-api-operator' => $type === 'montacargas'
            ? tmd_inventory_api_text($spec['posicionOperario'] ?? '')
            : '',
        'data-api-reach' => $type === 'montacargas'
            ? tmd_inventory_api_text($spec['tipoReach'] ?? '')
            : '',
        'data-api-voltage' => $type === 'bateria'
            ? tmd_inventory_api_number($spec['voltaje_v'] ?? 0, ' V')
            : '',
        'data-api-capacity' => $type === 'bateria'
            ? tmd_inventory_api_number($spec['amperaje_ah'] ?? 0, ' Ah')
            : '',
    ];
    $filter_attributes = '';
    foreach ($filter_data as $attribute => $value) {
        $filter_attributes .= ' ' . $attribute . '="' . esc_attr($value) . '"';
    }

    echo '<article class="' . esc_attr($card_class . ' tmd-api-card') . '"' . $filter_attributes . '>';
    echo '<a class="' . esc_attr($image_class) . '" href="' . esc_url($detail_url) . '" aria-label="Ver ' . esc_attr($title) . '">';
    if ($image) {
        echo '<img src="' . $image . '" alt="' . esc_attr($title) . '" loading="lazy">';
    }
    echo '</a>';
    echo '<div class="' . esc_attr($body_class) . '">';
    echo '<div class="tmd-api-tags">';
    if ($type === 'montacargas') {
        echo '<span>' . esc_html($classification['category']) . '</span>';
        echo '<span class="is-subcategory">' . esc_html($classification['subcategory']) . '</span>';
    } else {
        echo '<span>Batería</span><span>' . esc_html($state) . '</span>';
    }
    echo '</div>';
    echo '<h3><a href="' . esc_url($detail_url) . '">' . esc_html($title) . '</a></h3>';
    echo '<div class="tmd-api-specs">';
    foreach (array_slice(tmd_inventory_api_specs($item, $type), 0, 5, true) as $label => $value) {
        echo '<div><span>' . esc_html($label) . '</span><strong>' . esc_html($value) . '</strong></div>';
    }
    echo '</div>';
    echo '<div class="tmd-api-actions"><a class="is-primary" href="' . esc_url($detail_url) . '">Ver ficha</a><a href="' . esc_url($contact_url) . '">Cotizar</a></div>';
    echo '</div></article>';
}

function tmd_inventory_api_detail($item, $type) {
    $title = tmd_inventory_api_title($item, $type);
    $image = esc_url($item['media']['imagenPrincipal'] ?? '');
    $back = $type === 'montacargas' ? home_url('/equipos/') : home_url('/energia/');
    $contact_args = [
        'equipo_id' => (string) ($item['id'] ?? ''),
    ];
    $contact_args[$type === 'bateria' ? 'tmd_cotizacion_energia' : 'equipo'] = $title;
    $contact = add_query_arg($contact_args, home_url('/nosotros/contacto/'));
    ob_start();
    echo '<article class="tmd-api-detail">';
    echo '<a class="tmd-api-back" href="' . esc_url($back) . '">← Volver al catálogo</a>';
    echo '<div class="tmd-api-detail-grid">';
    echo '<div class="tmd-api-detail-image">';
    if ($image) { echo '<img src="' . $image . '" alt="' . esc_attr($title) . '">'; }
    echo '</div><div class="tmd-api-detail-body">';
    echo '<span class="tmd-api-detail-kind">' . esc_html($type === 'montacargas' ? 'Equipo disponible' : 'Batería disponible') . '</span>';
    echo '<h2>' . esc_html($title) . '</h2>';
    echo '<div class="tmd-api-specs is-detail">';
    foreach (tmd_inventory_api_specs($item, $type, true) as $label => $value) {
        echo '<div><span>' . esc_html($label) . '</span><strong>' . esc_html($value) . '</strong></div>';
    }
    echo '</div><a class="tmd-api-quote" href="' . esc_url($contact) . '">Solicitar cotización</a>';
    echo '</div></div></article>';
    return ob_get_clean();
}

function tmd_inventory_api_grid($type, $per_page) {
    $all_items = tmd_inventory_api_items_by_type($type);
    if (! $all_items) {
        return '<div class="tmd-api-message">No fue posible cargar inventario. El catálogo reintentará automáticamente.</div>';
    }

    $detail_id = tmd_inventory_api_request_value('ficha');
    if ($detail_id) {
        foreach ($all_items as $item) {
            if (hash_equals((string) ($item['id'] ?? ''), $detail_id)) {
                return tmd_inventory_api_detail($item, $type);
            }
        }
    }

    $per_page = max(1, min(48, (int) $per_page));
    $label = $type === 'montacargas' ? 'equipos' : 'baterías';

    ob_start();
    echo '<div class="tmd-api-results" data-tmd-api-results data-api-type="' . esc_attr($type) . '" data-api-per-page="' . esc_attr((string) $per_page) . '" data-api-label="' . esc_attr($label) . '">';
    tmd_inventory_api_status($type, count($all_items));

    echo '<div class="tmd-api-grid">';
    foreach ($all_items as $item) {
        tmd_inventory_api_card($item, $type);
    }
    echo '</div>';
    echo '<div class="tmd-api-message" data-tmd-api-empty hidden>No encontramos resultados con estos filtros.</div>';
    echo '<nav class="tmd-api-pagination" data-tmd-api-pagination aria-label="Páginas del catálogo"></nav>';
    echo '</div>';
    return ob_get_clean();
}

add_action('init', function () {
    remove_shortcode('tmd_equipment_grid');
    remove_shortcode('tmd_equipment_filters');
    remove_shortcode('tmd_energy_grid');
    remove_shortcode('tmd_energy_filters');

    add_shortcode('tmd_equipment_grid', static function ($atts) {
        $atts = shortcode_atts(['per_page' => 12], $atts, 'tmd_equipment_grid');
        return tmd_inventory_api_grid('montacargas', $atts['per_page']);
    });
    add_shortcode('tmd_equipment_filters', static function () {
        return tmd_inventory_api_filter_form('montacargas');
    });
    add_shortcode('tmd_energy_grid', static function ($atts) {
        $atts = shortcode_atts(['per_page' => 12], $atts, 'tmd_energy_grid');
        return tmd_inventory_api_grid('bateria', $atts['per_page']);
    });
    add_shortcode('tmd_energy_filters', static function () {
        return tmd_inventory_api_filter_form('bateria');
    });
}, 2000);

add_action('wp_enqueue_scripts', function () {
    if (! (is_page(49) || is_page(63))) {
        return;
    }
    $css = get_stylesheet_directory() . '/assets/css/tmd-inventory-api.css';
    $js = get_stylesheet_directory() . '/assets/js/tmd-inventory-api.js';
    wp_enqueue_style('tmd-inventory-api', get_stylesheet_directory_uri() . '/assets/css/tmd-inventory-api.css', [], file_exists($css) ? filemtime($css) : '1.0.0');
    wp_enqueue_script('tmd-inventory-api', get_stylesheet_directory_uri() . '/assets/js/tmd-inventory-api.js', [], file_exists($js) ? filemtime($js) : '1.0.0', true);
}, 95);

add_filter('script_loader_tag', static function ($tag, $handle) {
    if ($handle !== 'tmd-inventory-api') {
        return $tag;
    }

    return str_replace('<script ', '<script data-no-optimize="1" data-no-defer="1" ', $tag);
}, 10, 2);
