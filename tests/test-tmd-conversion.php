<?php

define('ABSPATH', __DIR__);

$tmd_conversion_is_contact_page = true;

function tmd_conversion_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function sanitize_text_field($value) {
    return trim(strip_tags((string) $value));
}

function wp_unslash($value) {
    return $value;
}

function home_url($path = '/') {
    return 'https://example.test' . $path;
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

function esc_html($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function esc_url($value) {
    return (string) $value;
}

function esc_url_raw($value) {
    return (string) $value;
}

function is_page($page_id) {
    global $tmd_conversion_is_contact_page;
    return 57 === (int) $page_id && $tmd_conversion_is_contact_page;
}

function add_filter() {}
function add_action() {}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-conversion.php';

$canonical = tmd_conversion_quote_context([
    'tmd_cotizacion_id' => 'forklift-42',
    'tmd_tipo_cotizacion' => 'montacargas',
    'tmd_cotizacion' => 'CROWN RD 5200',
]);
tmd_conversion_test_assert(
    $canonical === [
        'id' => 'forklift-42',
        'type' => 'montacargas',
        'type_label' => 'Equipo',
        'title' => 'CROWN RD 5200',
    ],
    'El contexto canónico debe conservar ID, tipo y título.'
);

$legacy_energy = tmd_conversion_quote_context([
    'equipo_id' => 'battery-9',
    'tmd_cotizacion_energia' => 'Batería 48 V 625 Ah',
]);
tmd_conversion_test_assert(
    $legacy_energy['id'] === 'battery-9'
        && $legacy_energy['type'] === 'bateria'
        && $legacy_energy['title'] === 'Batería 48 V 625 Ah',
    'Las URL antiguas de energía deben seguir funcionando.'
);

$quote_url = tmd_conversion_quote_url('bateria', 'battery-9', 'Batería 48 V 625 Ah');
tmd_conversion_test_assert(
    str_contains($quote_url, 'tmd_cotizacion_id=battery-9')
        && str_contains($quote_url, 'tmd_tipo_cotizacion=bateria')
        && str_contains($quote_url, 'tmd_cotizacion=Bater%C3%ADa+48+V+625+Ah'),
    'La URL de cotización debe transportar los tres valores canónicos.'
);

$context_html = tmd_conversion_context_html($canonical);
tmd_conversion_test_assert(
    str_contains($context_html, 'CROWN RD 5200')
        && str_contains($context_html, 'Tipo: Equipo')
        && str_contains($context_html, 'ID: forklift-42'),
    'El resumen previo al formulario debe mostrar título, tipo e ID.'
);

$_GET = [
    'tmd_cotizacion_id' => 'forklift-42',
    'tmd_tipo_cotizacion' => 'montacargas',
    'tmd_cotizacion' => 'CROWN RD 5200',
];
$content = '<section class="tmd-wrap tmd-contact-grid"><form class="tmd-form-card"></form></section>';
$injected = tmd_conversion_inject_context($content);
tmd_conversion_test_assert(
    1 === substr_count($injected, 'tmd-quote-context')
        && strpos($injected, 'tmd-quote-context') < strpos($injected, '<form'),
    'El resumen debe insertarse una sola vez antes del formulario.'
);
tmd_conversion_test_assert(
    $injected === tmd_conversion_inject_context($injected),
    'La inyección del contexto debe ser idempotente.'
);

$contact = tmd_conversion_contact_config();
tmd_conversion_test_assert(
    $contact['official_whatsapp'] === '573015556180'
        && str_contains(tmd_conversion_whatsapp_url('Hola'), '573015556180'),
    'Los puntos de conversión deben reutilizar el WhatsApp oficial.'
);

$rail_template = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/template-parts/tmd-contact-rail.php');
$rail_css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-contact-rail.css');
tmd_conversion_test_assert(
    str_contains($rail_template, '<details class="tmd-contact-rail__details">')
        && str_contains($rail_template, '<summary class="tmd-contact-rail__trigger"')
        && str_contains($rail_css, '.tmd-contact-rail__details[open] .tmd-contact-rail__links')
        && str_contains($rail_css, '@media (max-width: 767px)')
        && str_contains($rail_css, ".tmd-contact-rail {\n    display: none;"),
    'El contacto lateral debe iniciar colapsado y permanecer oculto en móvil.'
);

$conversion_js = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/js/tmd-contact-conversion.js');
tmd_conversion_test_assert(
    str_contains($conversion_js, "'wpcf7beforesubmit'")
        && str_contains($conversion_js, "'wpcf7mailsent'")
        && str_contains($conversion_js, "'wpcf7invalid'")
        && str_contains($conversion_js, "'wpcf7mailfailed'")
        && str_contains($conversion_js, 'Nuestro equipo revisará la información')
        && str_contains($conversion_js, 'Escribir por WhatsApp'),
    'El formulario debe comunicar carga, éxito, validación y error recuperable.'
);

echo "OK: conversión contextual validada.\n";
