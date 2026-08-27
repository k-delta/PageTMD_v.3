<?php

define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS', 86400);

$tmd_test_transients = [];
$tmd_test_transient_expirations = [];
$tmd_test_options = [];
$tmd_test_scheduled = [];
$tmd_test_schedule_calls = [];
$tmd_test_actions = [];
$tmd_test_remote_calls = 0;
$tmd_test_remote_response = null;
$tmd_test_uuid_sequence = 0;

class WP_Error {}

class TmdInventoryTestWpdb {
    public $options = 'wp_options';

    public function delete($table, $where, $formats = []) {
        global $tmd_test_options;

        $key = $where['option_name'] ?? '';
        if ($table !== $this->options
            || ! array_key_exists($key, $tmd_test_options)
            || maybe_serialize($tmd_test_options[$key]) !== ($where['option_value'] ?? null)) {
            return 0;
        }

        unset($tmd_test_options[$key]);
        return 1;
    }
}

$wpdb = new TmdInventoryTestWpdb();

function tmd_inventory_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function tmd_inventory_test_item($id = 'eq-1', $type = 'montacargas') {
    return [
        'id' => $id,
        'tipo' => $type,
        'marca' => 'CROWN',
        'modelo' => $type === 'montacargas' ? 'RD5200' : '',
        'referencia' => $type === 'bateria' ? 'BAT-100' : '',
        'estado' => ['codigo' => 1, 'nombre' => 'Disponible'],
        'especificaciones' => $type === 'montacargas'
            ? [
                'subtipo' => 'REACH',
                'capacidad_ton' => 2,
                'alturaMastilContraido_m' => 2.5,
                'alturaLevantamiento_m' => 7,
                'condicionEspecial' => 'PANTOGRAFO',
                'posicionOperario' => 'Sentado',
                'tipoReach' => 'DOBLE',
            ]
            : ['voltaje_v' => 48, 'amperaje_ah' => 625],
        'media' => ['imagenPrincipal' => 'https://example.test/equipo.jpg'],
    ];
}

function tmd_inventory_test_payload($id = 'eq-1') {
    return [
        'items' => [tmd_inventory_test_item($id)],
        'generatedAt' => '2026-08-03T15:00:00.000Z',
        'fetchedAt' => 123,
        'source' => 'live',
    ];
}

function tmd_inventory_test_reset() {
    global $tmd_test_transients, $tmd_test_transient_expirations, $tmd_test_options;
    global $tmd_test_scheduled, $tmd_test_schedule_calls;
    global $tmd_test_remote_calls, $tmd_test_remote_response, $tmd_test_uuid_sequence;

    $tmd_test_transients = [];
    $tmd_test_transient_expirations = [];
    $tmd_test_options = [];
    $tmd_test_scheduled = [];
    $tmd_test_schedule_calls = [];
    $tmd_test_remote_calls = 0;
    $tmd_test_remote_response = null;
    $tmd_test_uuid_sequence = 0;
    $_GET = [];
}

function get_transient($key) {
    global $tmd_test_transients;
    return $tmd_test_transients[$key] ?? false;
}

function set_transient($key, $value, $expiration) {
    global $tmd_test_transients, $tmd_test_transient_expirations;
    $tmd_test_transients[$key] = $value;
    $tmd_test_transient_expirations[$key] = $expiration;
    return true;
}

function delete_transient($key) {
    global $tmd_test_transients;
    unset($tmd_test_transients[$key]);
    return true;
}

function get_option($key, $default = false) {
    global $tmd_test_options;
    return $tmd_test_options[$key] ?? $default;
}

function update_option($key, $value, $autoload = null) {
    global $tmd_test_options;
    $tmd_test_options[$key] = $value;
    return true;
}

function add_option($key, $value, $deprecated = '', $autoload = 'yes') {
    global $tmd_test_options;
    if (array_key_exists($key, $tmd_test_options)) {
        return false;
    }
    $tmd_test_options[$key] = $value;
    return true;
}

function delete_option($key) {
    global $tmd_test_options;
    unset($tmd_test_options[$key]);
    return true;
}

function wp_remote_get($url, $args = []) {
    global $tmd_test_remote_calls, $tmd_test_remote_response;
    ++$tmd_test_remote_calls;
    return $tmd_test_remote_response;
}

function is_wp_error($value) {
    return $value instanceof WP_Error;
}

function wp_remote_retrieve_response_code($response) {
    return (int) ($response['response']['code'] ?? 0);
}

function wp_remote_retrieve_body($response) {
    return (string) ($response['body'] ?? '');
}

function sanitize_text_field($value) {
    return trim((string) $value);
}

function wp_generate_uuid4() {
    global $tmd_test_uuid_sequence;
    ++$tmd_test_uuid_sequence;
    return sprintf('00000000-0000-4000-8000-%012d', $tmd_test_uuid_sequence);
}

function maybe_serialize($value) {
    return is_array($value) || is_object($value) ? serialize($value) : $value;
}

function wp_cache_delete() {
    return true;
}

function home_url($path = '/') {
    return 'https://example.test' . $path;
}

function wp_next_scheduled($hook) {
    global $tmd_test_scheduled;
    foreach ($tmd_test_scheduled as $event) {
        if ($event['hook'] === $hook) {
            return $event['timestamp'];
        }
    }
    return false;
}

function wp_schedule_event($timestamp, $recurrence, $hook) {
    global $tmd_test_scheduled, $tmd_test_schedule_calls;
    $event = compact('timestamp', 'recurrence', 'hook');
    $tmd_test_scheduled[] = $event;
    $tmd_test_schedule_calls[] = $event;
    return true;
}

function wp_clear_scheduled_hook($hook) {
    global $tmd_test_scheduled;
    $tmd_test_scheduled = array_values(array_filter(
        $tmd_test_scheduled,
        static fn($event) => $event['hook'] !== $hook
    ));
    return true;
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $tmd_test_actions;
    $tmd_test_actions[$hook][] = $callback;
}

function add_filter() {}
function add_shortcode() {}
function remove_shortcode() {}
function wp_unslash($value) { return $value; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_url($value) { return (string) $value; }
function esc_url_raw($value) { return (string) $value; }
function remove_accents($value) { return (string) $value; }
function number_format_i18n($number, $decimals = 0) { return number_format($number, $decimals, '.', ','); }
function wp_date($format, $timestamp) { return date($format, $timestamp); }
function wp_json_encode($value, $flags = 0, $depth = 512) { return json_encode($value, $flags, $depth); }
function selected($selected, $current, $echo = true) {
    $result = (string) $selected === (string) $current ? ' selected="selected"' : '';
    if ($echo) { echo $result; }
    return $result;
}
function add_query_arg($key, $value = null, $url = null) {
    if (is_array($key)) {
        $args = $key;
        $base = (string) $value;
    } else {
        $args = [$key => $value];
        $base = (string) $url;
    }
    return $base . (str_contains($base, '?') ? '&' : '?') . http_build_query($args);
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-conversion.php';
require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-inventory-api.php';

tmd_inventory_test_assert(function_exists('tmd_inventory_api_refresh'), 'Debe existir la tarea de refresco remoto.');
tmd_inventory_test_assert(function_exists('tmd_inventory_api_schedule_refresh'), 'Debe existir la programación del refresco.');

tmd_inventory_test_reset();
$tmd_test_transients[TMD_INVENTORY_API_CACHE_KEY] = tmd_inventory_test_payload('cached');
$payload = tmd_inventory_api_payload();
tmd_inventory_test_assert('cached' === $payload['items'][0]['id'], 'La lectura debe preferir el transient válido.');
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Leer el transient no debe llamar Firebase.');

tmd_inventory_test_reset();
$tmd_test_options[TMD_INVENTORY_API_FALLBACK_KEY] = tmd_inventory_test_payload('fallback');
$payload = tmd_inventory_api_payload();
tmd_inventory_test_assert('fallback' === $payload['items'][0]['id'], 'La lectura debe usar la última copia válida.');
tmd_inventory_test_assert('fallback' === $payload['source'], 'La copia persistente debe identificarse como fallback.');
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Leer el fallback no debe llamar Firebase.');

tmd_inventory_test_reset();
$payload = tmd_inventory_api_payload();
tmd_inventory_test_assert([] === $payload['items'] && 'error' === $payload['source'], 'Sin copia local debe degradar al contrato de error.');
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Una solicitud pública sin copia local no debe llamar Firebase.');
tmd_inventory_test_assert(1 === count($tmd_test_schedule_calls), 'Sin copia local debe programar el refresco sin bloquear la respuesta.');

tmd_inventory_test_reset();
$remote_items = ['remote-key' => tmd_inventory_test_item('remote')];
$tmd_test_remote_response = [
    'response' => ['code' => 200],
    'body' => json_encode([
        'ok' => true,
        'items' => $remote_items,
        'generatedAt' => '2026-08-03T16:00:00.000Z',
    ]),
];
$refresh_started_at = time();
$refreshed = tmd_inventory_api_refresh();
tmd_inventory_test_assert(true === $refreshed, 'Una respuesta válida debe actualizar la copia local.');
tmd_inventory_test_assert(1 === $tmd_test_remote_calls, 'El refresco debe ejecutar una sola llamada remota.');
$stored_payload = $tmd_test_transients[TMD_INVENTORY_API_CACHE_KEY];
tmd_inventory_test_assert(array_keys($stored_payload) === ['items', 'generatedAt', 'fetchedAt', 'source'], 'El refresco debe conservar el contrato completo.');
tmd_inventory_test_assert([0] === array_keys($stored_payload['items']), 'El refresco debe normalizar los índices de items.');
tmd_inventory_test_assert('remote' === $stored_payload['items'][0]['id'], 'El refresco debe actualizar los items.');
tmd_inventory_test_assert('2026-08-03T16:00:00.000Z' === $stored_payload['generatedAt'], 'El refresco debe conservar generatedAt.');
tmd_inventory_test_assert($stored_payload['fetchedAt'] >= $refresh_started_at && $stored_payload['fetchedAt'] <= time(), 'El refresco debe registrar fetchedAt actual.');
tmd_inventory_test_assert('live' === $stored_payload['source'], 'El refresco exitoso debe identificarse como live.');
tmd_inventory_test_assert($stored_payload === $tmd_test_options[TMD_INVENTORY_API_FALLBACK_KEY], 'Transient y fallback deben recibir el mismo contrato.');
tmd_inventory_test_assert(DAY_IN_SECONDS === $tmd_test_transient_expirations[TMD_INVENTORY_API_CACHE_KEY], 'El transient válido debe durar un día.');
tmd_inventory_test_assert(! isset($tmd_test_options[TMD_INVENTORY_API_LOCK_KEY]), 'El refresco exitoso debe liberar el bloqueo.');

foreach ([
    ['response' => ['code' => 503], 'body' => ''],
    ['response' => ['code' => 200], 'body' => '{invalid'],
    ['response' => ['code' => 200], 'body' => json_encode(['ok' => true, 'items' => []])],
    new WP_Error(),
] as $failure) {
    tmd_inventory_test_reset();
    $previous = tmd_inventory_test_payload('last-good');
    $tmd_test_options[TMD_INVENTORY_API_FALLBACK_KEY] = $previous;
    $tmd_test_remote_response = $failure;
    tmd_inventory_test_assert(false === tmd_inventory_api_refresh(), 'Un fallo remoto debe devolver false.');
    tmd_inventory_test_assert($previous === $tmd_test_options[TMD_INVENTORY_API_FALLBACK_KEY], 'Un fallo no debe sobrescribir la última copia válida.');
    tmd_inventory_test_assert(! isset($tmd_test_options[TMD_INVENTORY_API_LOCK_KEY]), 'Un fallo debe liberar el bloqueo.');
}

tmd_inventory_test_reset();
$tmd_test_options[TMD_INVENTORY_API_LOCK_KEY] = [
    'token' => 'active-owner',
    'created_at' => time(),
];
$tmd_test_remote_response = ['response' => ['code' => 200], 'body' => '{}'];
tmd_inventory_test_assert(false === tmd_inventory_api_refresh(), 'Un bloqueo activo debe omitir el refresco concurrente.');
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Un bloqueo activo debe impedir la llamada remota.');

tmd_inventory_test_reset();
$expired_lock = [
    'token' => 'expired-owner',
    'created_at' => time() - TMD_INVENTORY_API_LOCK_TTL,
];
$tmd_test_options[TMD_INVENTORY_API_LOCK_KEY] = $expired_lock;
$tmd_test_remote_response = [
    'response' => ['code' => 200],
    'body' => json_encode([
        'ok' => true,
        'items' => [tmd_inventory_test_item('after-expired-lock')],
    ]),
];
tmd_inventory_test_assert(true === tmd_inventory_api_refresh(), 'Un bloqueo vencido en el límite del TTL debe recuperarse.');
tmd_inventory_test_assert(1 === $tmd_test_remote_calls, 'Recuperar el bloqueo debe realizar una sola llamada.');
tmd_inventory_test_assert(! isset($tmd_test_options[TMD_INVENTORY_API_LOCK_KEY]), 'El nuevo propietario debe liberar su bloqueo.');

tmd_inventory_test_reset();
$first_owner = tmd_inventory_api_acquire_refresh_lock();
$successor = [
    'token' => 'successor-owner',
    'created_at' => time(),
];
$tmd_test_options[TMD_INVENTORY_API_LOCK_KEY] = $successor;
tmd_inventory_test_assert(false === tmd_inventory_api_delete_refresh_lock($first_owner), 'Un propietario anterior no debe borrar el bloqueo sucesor.');
tmd_inventory_test_assert($successor === $tmd_test_options[TMD_INVENTORY_API_LOCK_KEY], 'El bloqueo sucesor debe permanecer intacto.');

tmd_inventory_test_reset();
tmd_inventory_api_schedule_refresh();
tmd_inventory_api_schedule_refresh();
tmd_inventory_test_assert(1 === count($tmd_test_schedule_calls), 'La programación debe invocar wp_schedule_event una sola vez.');
tmd_inventory_test_assert('hourly' === $tmd_test_schedule_calls[0]['recurrence'], 'El refresco debe ser horario.');
tmd_inventory_test_assert(TMD_INVENTORY_API_REFRESH_HOOK === $tmd_test_schedule_calls[0]['hook'], 'El evento debe usar el hook esperado.');
tmd_inventory_test_assert(
    in_array('tmd_inventory_api_refresh', $tmd_test_actions[TMD_INVENTORY_API_REFRESH_HOOK] ?? [], true),
    'El cron debe ejecutar exactamente el callback de refresco.'
);

tmd_inventory_test_reset();
$catalog_payload = tmd_inventory_test_payload('forklift-public');
$catalog_payload['items'][] = tmd_inventory_test_item('battery-public', 'bateria');
$tmd_test_transients[TMD_INVENTORY_API_CACHE_KEY] = $catalog_payload;
$equipment_filters = tmd_inventory_api_filter_form('montacargas');
$equipment_grid = tmd_inventory_api_grid('montacargas', 12);
$energy_filters = tmd_inventory_api_filter_form('bateria');
$energy_grid = tmd_inventory_api_grid('bateria', 12);
tmd_inventory_test_assert(str_contains($equipment_filters, 'action="https://example.test/equipos/"'), 'Equipos debe conservar su formulario de filtros.');
tmd_inventory_test_assert(
    array_reduce(
        ['api_altura_colapsada', 'api_altura_levante', 'api_capacidad'],
        static fn($found, $name) => $found && str_contains($equipment_filters, 'name="' . $name . '"'),
        true
    ),
    'Equipos debe conservar Altura colapsada, Altura de levante y Capacidad.'
);
tmd_inventory_test_assert(
    array_reduce(
        ['api_marca', 'api_categoria', 'api_subcategoria', 'api_condicion', 'api_operario', 'api_reach'],
        static fn($hidden, $name) => $hidden && ! str_contains($equipment_filters, 'name="' . $name . '"'),
        true
    ),
    'Equipos no debe renderizar los controles ocultos.'
);
tmd_inventory_test_assert(
    str_contains($equipment_filters, '<option value="2 ton">2 ton</option>')
        && ! str_contains($equipment_filters, '2 ton ('),
    'Capacidad debe mostrar toneladas disponibles sin conteos.'
);
tmd_inventory_test_assert(3 === substr_count($equipment_filters, '<select name="'), 'Equipos debe conservar exactamente sus tres selectores visibles.');

$capacity_items = [];
foreach ([2, 2.0, 1.5, 10, 0, 'inválida', null] as $index => $capacity) {
    $capacity_item = tmd_inventory_test_item('capacity-' . $index);
    $capacity_item['especificaciones']['capacidad_ton'] = $capacity;
    $capacity_items[] = $capacity_item;
}
$capacity_options = tmd_inventory_api_equipment_capacity_options($capacity_items);
tmd_inventory_test_assert(
    ['1.5 ton', '2 ton', '10 ton'] === array_keys($capacity_options)
        && 2 === $capacity_options['2 ton'],
    'Capacidad debe normalizar, deduplicar, ordenar y excluir valores inválidos.'
);
ob_start();
tmd_inventory_api_select('api_capacidad', 'Capacidad', $capacity_options, '', false);
$capacity_select = ob_get_clean();
tmd_inventory_test_assert(
    3 === substr_count($capacity_select, '<option value="') - 1
        && ! str_contains($capacity_select, ' ton ('),
    'El selector de capacidad debe mostrar exactamente las opciones válidas sin conteos.'
);
tmd_inventory_test_assert(
    str_contains($equipment_grid, 'data-api-type="montacargas"')
        && str_contains($equipment_grid, 'data-api-per-page="12"')
        && str_contains($equipment_grid, '<nav class="tmd-api-pagination"')
        && str_contains($equipment_grid, 'ficha=forklift-public')
        && str_contains($equipment_grid, 'tmd_cotizacion_id=forklift-public')
        && str_contains($equipment_grid, 'tmd_tipo_cotizacion=montacargas')
        && str_contains($equipment_grid, 'tmd_cotizacion=CROWN+RD5200'),
    'Equipos debe conservar tarjeta, paginación, ficha y parámetros de cotización.'
);
tmd_inventory_test_assert(str_contains($energy_filters, 'action="https://example.test/energia/"'), 'Energía debe conservar su formulario de filtros.');
tmd_inventory_test_assert(
    array_reduce(
        ['api_voltaje', 'api_capacidad', 'api_condicion'],
        static fn($found, $name) => $found && str_contains($energy_filters, 'name="' . $name . '"'),
        true
    )
        && ! str_contains($energy_filters, 'name="api_marca"')
        && ! str_contains($energy_filters, '<span>Marca</span>'),
    'Energía debe mostrar Voltaje, Capacidad y Condición sin el filtro Marca.'
);
tmd_inventory_test_assert(3 === substr_count($energy_filters, '<select name="'), 'Energía debe renderizar exactamente sus tres selectores aprobados.');
preg_match_all('/<select name="([^"]+)"/', $energy_filters, $energy_filter_names);
tmd_inventory_test_assert(
    ['api_voltaje', 'api_capacidad', 'api_condicion'] === ($energy_filter_names[1] ?? []),
    'Energía debe conservar el orden Voltaje, Capacidad y Condición.'
);
tmd_inventory_test_assert(
    str_contains($energy_filters, '<option value="48 V">48 V</option>')
        && str_contains($energy_filters, '<option value="625 Ah">625 Ah</option>')
        && str_contains($energy_filters, '<option value="Nueva">Nueva</option>')
        && ! str_contains($energy_filters, 'CROWN (')
        && ! str_contains($energy_filters, '48 V (')
        && ! str_contains($energy_filters, '625 Ah ('),
    'Energía debe conservar etiquetas, valores y unidades sin conteos visibles.'
);
tmd_inventory_test_assert(
    str_contains($energy_grid, 'data-api-type="bateria"')
        && str_contains($energy_grid, 'data-api-per-page="12"')
        && str_contains($energy_grid, '<nav class="tmd-api-pagination"')
        && str_contains($energy_grid, 'ficha=battery-public')
        && str_contains($energy_grid, 'tmd_cotizacion_id=battery-public')
        && str_contains($energy_grid, 'tmd_tipo_cotizacion=bateria')
        && str_contains($energy_grid, 'tmd_cotizacion=Bater%C3%ADa+CROWN+BAT-100'),
    'Energía debe conservar tarjeta, paginación, ficha y parámetros de cotización.'
);
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Renderizar ambos catálogos con copia local no debe llamar Firebase.');

$_GET = ['api_categoria' => 'Reach'];
$legacy_equipment_filters = tmd_inventory_api_filter_form('montacargas');
tmd_inventory_test_assert(
    str_contains($legacy_equipment_filters, '<input type="hidden" name="api_categoria" value="Reach" data-api-preserved-filter>')
        && ! str_contains($legacy_equipment_filters, '<select name="api_categoria"'),
    'Una URL existente debe preservar el filtro de categoría sin volver a mostrar su control.'
);
$_GET = [];

$_GET['ficha'] = 'forklift-public';
$detail = tmd_inventory_api_grid('montacargas', 12);
tmd_inventory_test_assert(
    str_contains($detail, 'tmd-api-detail')
        && str_contains($detail, 'tmd_cotizacion_id=forklift-public')
        && str_contains($detail, 'tmd_tipo_cotizacion=montacargas')
        && str_contains($detail, 'tmd_cotizacion=CROWN+RD5200'),
    'La ficha de equipos debe conservar su cotización con datos locales.'
);
$_GET['ficha'] = 'battery-public';
$detail = tmd_inventory_api_grid('bateria', 12);
tmd_inventory_test_assert(
    str_contains($detail, 'tmd-api-detail')
        && str_contains($detail, 'tmd_cotizacion_id=battery-public')
        && str_contains($detail, 'tmd_tipo_cotizacion=bateria')
        && str_contains($detail, 'tmd_cotizacion=Bater%C3%ADa+CROWN+BAT-100'),
    'La ficha de baterías debe conservar su cotización con datos locales.'
);
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Abrir una ficha local no debe llamar Firebase.');

tmd_inventory_test_reset();
$tmd_test_options[TMD_INVENTORY_API_FALLBACK_KEY] = $catalog_payload;
$fallback_equipment = tmd_inventory_api_grid('montacargas', 12);
$fallback_energy = tmd_inventory_api_grid('bateria', 12);
tmd_inventory_test_assert(str_contains($fallback_equipment, 'forklift-public'), 'Equipos debe renderizar desde fallback persistente.');
tmd_inventory_test_assert(str_contains($fallback_energy, 'battery-public'), 'Energía debe renderizar desde fallback persistente.');
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Renderizar ambos catálogos desde fallback no debe llamar Firebase.');

tmd_inventory_test_reset();
tmd_inventory_test_assert(str_contains(tmd_inventory_api_filter_form('montacargas'), 'Inventario temporalmente no disponible'), 'Filtros sin copia deben mostrar indisponibilidad.');
tmd_inventory_test_assert(str_contains(tmd_inventory_api_grid('bateria', 12), 'No fue posible cargar inventario'), 'Resultados sin copia deben mostrar indisponibilidad.');
tmd_inventory_test_assert(0 === $tmd_test_remote_calls, 'Renderizar sin datos no debe llamar Firebase.');

tmd_inventory_test_reset();
$many_items = [];
for ($index = 1; $index <= 20; ++$index) {
    $item = tmd_inventory_test_item('many-' . $index);
    $item['marca'] = $index > 15 ? 'JUNGHEINRICH' : 'CROWN';
    $many_items[] = $item;
}
$many_items[19]['marca'] = '</script><script>alert(1)</script>';
$many_payload = tmd_inventory_test_payload('unused');
$many_payload['items'] = $many_items;
$tmd_test_transients[TMD_INVENTORY_API_CACHE_KEY] = $many_payload;
$many_grid = tmd_inventory_api_grid('montacargas', 48);
tmd_inventory_test_assert(12 === substr_count($many_grid, '<article class="'), 'HTML inicial debe contener exactamente per_page tarjetas.');
tmd_inventory_test_assert(str_contains($many_grid, 'data-api-per-page="12"'), 'El máximo compartido de tarjetas por página debe permanecer en 12.');
tmd_inventory_test_assert(
    1 === preg_match('/<script type="application\/json" data-tmd-api-items>(.*?)<\/script>/s', $many_grid, $json_match),
    'La cuadrícula debe incluir un único payload JSON estructurado.'
);
tmd_inventory_test_assert(! str_contains($json_match[1], '</script>'), 'El JSON no debe permitir cerrar su bloque script.');
$public_payload = json_decode($json_match[1], true);
tmd_inventory_test_assert(JSON_ERROR_NONE === json_last_error(), 'El payload público debe ser JSON válido.');
tmd_inventory_test_assert(20 === count($public_payload['items'] ?? []), 'El JSON debe contener todos los resultados disponibles.');
$expected_public_keys = ['id', 'title', 'image', 'detailUrl', 'contactUrl', 'classes', 'tags', 'filters', 'specs'];
tmd_inventory_test_assert($expected_public_keys === array_keys($public_payload['items'][0] ?? []), 'Cada resultado debe respetar el contrato público mínimo.');
tmd_inventory_test_assert(
    [] === array_intersect(['estado', 'especificaciones', 'media'], array_keys($public_payload['items'][0] ?? [])),
    'El JSON público no debe exponer estructuras internas completas.'
);
tmd_inventory_test_assert(
    '2 ton' === ($public_payload['items'][0]['filters']['capacity'] ?? '')
        && 'Reach' === ($public_payload['items'][0]['filters']['category'] ?? ''),
    'El modelo público debe conservar clasificación y exponer capacidad de montacargas.'
);

$_GET['api_altura_levante'] = '6-8';
$filtered_grid = tmd_inventory_api_grid('montacargas', 12);
tmd_inventory_test_assert(12 === substr_count($filtered_grid, '<article class="'), 'Una URL filtrada debe renderizar solo coincidencias iniciales.');
tmd_inventory_test_assert(12 === substr_count($filtered_grid, 'data-api-lift-height="7"'), 'Todas las tarjetas iniciales deben respetar el filtro solicitado.');

$_GET = ['ficha' => 'many-1'];
$detail_without_payload = tmd_inventory_api_grid('montacargas', 12);
tmd_inventory_test_assert(! str_contains($detail_without_payload, 'data-tmd-api-items'), 'Una ficha individual no debe incluir el payload del catálogo.');

tmd_inventory_test_reset();
$matching_equipment = tmd_inventory_test_item('filter-match');
$other_equipment = tmd_inventory_test_item('filter-other');
$other_equipment['marca'] = 'OTRA';
$other_equipment['modelo'] = 'EJC 110';
$other_equipment['especificaciones'] = [
    'subtipo' => 'APILADOR',
    'alturaMastilContraido_m' => 3.5,
    'alturaLevantamiento_m' => 5,
    'condicionEspecial' => 'ESPECIAL',
    'posicionOperario' => 'De pie',
    'tipoReach' => 'SIMPLE',
];
$equipment_filter_cases = [
    ['api_categoria' => 'Reach'],
    ['api_subcategoria' => 'Pantógrafo doble profundidad'],
    ['api_altura_colapsada' => '2-3'],
    ['api_altura_levante' => '6-8'],
    ['api_condicion' => 'pantografo'],
    ['api_operario' => 'sentado'],
    ['api_reach' => 'doble'],
    ['api_capacidad' => '2 ton'],
];
foreach ($equipment_filter_cases as $query) {
    $_GET = $query;
    $matches = tmd_inventory_api_filter_items([$matching_equipment, $other_equipment], 'montacargas');
    tmd_inventory_test_assert(
        ['filter-match'] === array_column($matches, 'id'),
        'Cada filtro de equipos debe conservar la coincidencia del servidor: ' . array_key_first($query)
    );
}

$_GET = ['api_marca' => 'OTRA'];
$matches = tmd_inventory_api_filter_items([$matching_equipment, $other_equipment], 'montacargas');
tmd_inventory_test_assert(
    ['filter-match', 'filter-other'] === array_column($matches, 'id'),
    'Equipos debe ignorar api_marca heredado para evitar un filtro invisible.'
);

$_GET = ['api_marca' => 'OTRA', 'api_altura_levante' => '6-8'];
$matches = tmd_inventory_api_filter_items([$matching_equipment, $other_equipment], 'montacargas');
tmd_inventory_test_assert(
    ['filter-match'] === array_column($matches, 'id'),
    'Equipos debe ignorar Marca y conservar los filtros visibles válidos.'
);

$matching_battery = tmd_inventory_test_item('battery-filter-match', 'bateria');
$matching_battery['marca'] = 'JUNGHEINRÍCH';
$matching_battery['esNueva'] = true;
$other_battery = tmd_inventory_test_item('battery-filter-other', 'bateria');
$other_battery['marca'] = 'OTRA';
$other_battery['especificaciones'] = ['voltaje_v' => 24, 'amperaje_ah' => 300];
$battery_filter_cases = [
    ['api_voltaje' => '48 V'],
    ['api_capacidad' => '625 Ah'],
    ['api_condicion' => 'Nueva'],
];
foreach ($battery_filter_cases as $query) {
    $_GET = $query;
    $matches = tmd_inventory_api_filter_items([$matching_battery, $other_battery], 'bateria');
    tmd_inventory_test_assert(
        ['battery-filter-match'] === array_column($matches, 'id'),
        'Cada filtro de energía debe conservar la coincidencia del servidor: ' . array_key_first($query)
    );
}

$_GET = ['api_marca' => 'JUNGHEINRÍCH'];
$matches = tmd_inventory_api_filter_items([$matching_battery, $other_battery], 'bateria');
tmd_inventory_test_assert(
    ['battery-filter-match', 'battery-filter-other'] === array_column($matches, 'id'),
    'Energía debe ignorar api_marca heredado para evitar un filtro invisible.'
);

$legacy_brand_combinations = [
    'api_voltaje' => '48 V',
    'api_capacidad' => '625 Ah',
    'api_condicion' => 'Nueva',
];
foreach ($legacy_brand_combinations as $filter_name => $filter_value) {
    $_GET = ['api_marca' => 'OTRA', $filter_name => $filter_value];
    $matches = tmd_inventory_api_filter_items([$matching_battery, $other_battery], 'bateria');
    tmd_inventory_test_assert(
        ['battery-filter-match'] === array_column($matches, 'id'),
        'Energía debe ignorar Marca y conservar el filtro válido ' . $filter_name . '.'
    );
}

tmd_inventory_test_reset();
$many_batteries = [];
for ($index = 1; $index <= 20; ++$index) {
    $many_batteries[] = tmd_inventory_test_item('battery-many-' . $index, 'bateria');
}
$battery_payload = tmd_inventory_test_payload('unused');
$battery_payload['items'] = $many_batteries;
$tmd_test_transients[TMD_INVENTORY_API_CACHE_KEY] = $battery_payload;
$many_energy_grid = tmd_inventory_api_grid('bateria', 48);
tmd_inventory_test_assert(12 === substr_count($many_energy_grid, '<article class="'), 'Energía debe limitar el HTML inicial a 12 baterías.');
tmd_inventory_test_assert(
    1 === preg_match('/<script type="application\/json" data-tmd-api-items>(.*?)<\/script>/s', $many_energy_grid, $battery_json_match),
    'Energía debe incluir su payload JSON estructurado.'
);
$battery_public_payload = json_decode($battery_json_match[1], true);
tmd_inventory_test_assert(20 === count($battery_public_payload['items'] ?? []), 'El JSON de energía debe contener todas las baterías.');
$_GET = ['ficha' => 'battery-many-1'];
$battery_detail_without_payload = tmd_inventory_api_grid('bateria', 12);
tmd_inventory_test_assert(! str_contains($battery_detail_without_payload, 'data-tmd-api-items'), 'Una ficha de batería no debe incluir el payload del catálogo.');

fwrite(STDOUT, "OK: cache, cron, fallos, lock, catalogos y payload inicial.\n");
