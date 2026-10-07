<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('OBJECT', 'OBJECT');

$GLOBALS['tmd_test_shortcodes'] = [];
$GLOBALS['tmd_test_filters'] = [];
$GLOBALS['tmd_test_actions'] = [];
$GLOBALS['tmd_test_enqueued_styles'] = [];
$GLOBALS['tmd_test_page_slug'] = '';
$GLOBALS['tmd_test_page_id'] = 0;
$GLOBALS['tmd_test_enqueued_scripts'] = [];
$GLOBALS['tmd_test_localized_scripts'] = [];

function add_shortcode(string $tag, $callback): void
{
    $GLOBALS['tmd_test_shortcodes'][$tag] = $callback;
}

function add_filter(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
    $GLOBALS['tmd_test_filters'][$tag][] = $callback;
}

function add_action(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
    $GLOBALS['tmd_test_actions'][$tag][] = $callback;
}

function shortcode_atts(array $defaults, $attributes, string $shortcode = ''): array
{
    return array_merge($defaults, (array) $attributes);
}

function sanitize_key(string $value): string
{
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value));
}

function esc_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function esc_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function is_page($slugs = ''): bool
{
    foreach ((array) $slugs as $slug) {
        if (is_numeric($slug) && (int) $slug === $GLOBALS['tmd_test_page_id']) {
            return true;
        }
        if (! is_numeric($slug) && $GLOBALS['tmd_test_page_slug'] === $slug) {
            return true;
        }
    }
    return false;
}

function get_stylesheet_directory(): string
{
    return dirname(__DIR__) . '/wp-content/themes/blocksy-child';
}

function get_stylesheet_directory_uri(): string
{
    return 'https://example.test/wp-content/themes/blocksy-child';
}

function wp_enqueue_style(string $handle, string $src, array $dependencies = [], $version = false): void
{
    $GLOBALS['tmd_test_enqueued_styles'][] = compact('handle', 'src', 'dependencies', 'version');
}

function wp_enqueue_script(string $handle, string $src, array $dependencies = [], $version = false, bool $in_footer = false): void
{
    $GLOBALS['tmd_test_enqueued_scripts'][] = compact('handle', 'src', 'dependencies', 'version', 'in_footer');
}

function wp_localize_script(string $handle, string $object_name, array $data): void
{
    $GLOBALS['tmd_test_localized_scripts'][] = compact('handle', 'object_name', 'data');
}

function tmd_thank_you_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

$module_path = dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-commercial-landing-thank-you.php';
if (is_file($module_path)) {
    require $module_path;
}
tmd_thank_you_assert(function_exists('tmd_commercial_thank_you_page_specs'), 'el módulo debe definir la configuración canónica de agradecimiento');

$specs = tmd_commercial_thank_you_page_specs();
tmd_thank_you_assert(
    [
        'slug' => 'gracias-baterias',
        'title' => 'Gracias por tu solicitud de baterías',
        'shortcode' => '[tmd_commercial_thank_you type="battery"]',
        'robots' => ['noindex', 'follow'],
    ] === array_intersect_key($specs['battery'] ?? [], array_flip(['slug', 'title', 'shortcode', 'robots'])),
    'la página de baterías debe conservar su slug, título, shortcode y robots aprobados'
);
tmd_thank_you_assert(
    [
        'slug' => 'gracias-montacargas',
        'title' => 'Gracias por tu solicitud de montacargas',
        'shortcode' => '[tmd_commercial_thank_you type="forklift"]',
        'robots' => ['noindex', 'follow'],
    ] === array_intersect_key($specs['forklift'] ?? [], array_flip(['slug', 'title', 'shortcode', 'robots'])),
    'la página de montacargas debe conservar su slug, título, shortcode y robots aprobados'
);
tmd_thank_you_assert(isset($GLOBALS['tmd_test_shortcodes']['tmd_commercial_thank_you']), 'el renderer debe registrarse como shortcode');

$renderer = $GLOBALS['tmd_test_shortcodes']['tmd_commercial_thank_you'];
foreach ([
    'battery' => 'Gracias por tu solicitud de baterías',
    'forklift' => 'Gracias por tu solicitud de montacargas',
] as $type => $expected_heading) {
    $html = $renderer(['type' => $type]);
    tmd_thank_you_assert(1 === substr_count($html, '<h1'), $type . ' debe producir exactamente un H1');
    tmd_thank_you_assert(false !== strpos($html, '>' . $expected_heading . '</h1>'), $type . ' debe mostrar su título aprobado');
    tmd_thank_you_assert(false !== strpos($html, 'Hemos recibido tu solicitud. Gracias por contactar a Tecnimontacargas.'), $type . ' debe mostrar el mensaje de confirmación');
}
tmd_thank_you_assert('' === $renderer(['type' => 'unknown']), 'un tipo no reconocido no debe renderizar una página equivocada');

$body_class_filter = $GLOBALS['tmd_test_filters']['body_class'][0] ?? null;
tmd_thank_you_assert(is_callable($body_class_filter), 'el módulo debe registrar la clase de página');
$GLOBALS['tmd_test_page_slug'] = 'gracias-baterias';
tmd_thank_you_assert(in_array('tmd-commercial-thank-you-page', $body_class_filter(['page']), true), 'las páginas de agradecimiento deben tener una clase identificable');
$GLOBALS['tmd_test_page_slug'] = 'contacto';
tmd_thank_you_assert(['page'] === $body_class_filter(['page']), 'la clase no debe añadirse a otras páginas');

$enqueue_callback = $GLOBALS['tmd_test_actions']['wp_enqueue_scripts'][0] ?? null;
tmd_thank_you_assert(is_callable($enqueue_callback), 'el módulo debe registrar la carga condicional del CSS');
$GLOBALS['tmd_test_page_slug'] = 'gracias-montacargas';
$enqueue_callback();
tmd_thank_you_assert(1 === count($GLOBALS['tmd_test_enqueued_styles']), 'el CSS debe cargar en la página de agradecimiento');
tmd_thank_you_assert(false !== strpos($GLOBALS['tmd_test_enqueued_styles'][0]['src'], 'tmd-commercial-landing-thank-you.css'), 'debe cargar la hoja de estilos propia de agradecimiento');
$GLOBALS['tmd_test_page_slug'] = 'contacto';
$enqueue_callback();
tmd_thank_you_assert(1 === count($GLOBALS['tmd_test_enqueued_styles']), 'el CSS de agradecimiento no debe cargar en otras páginas');

$redirect_enqueue_callback = $GLOBALS['tmd_test_actions']['wp_enqueue_scripts'][1] ?? null;
tmd_thank_you_assert(is_callable($redirect_enqueue_callback), 'el módulo debe registrar la configuración condicional de redirección');
$GLOBALS['tmd_test_page_id'] = 1558;
$redirect_enqueue_callback();
tmd_thank_you_assert(1 === count($GLOBALS['tmd_test_enqueued_scripts']), 'la configuración del formulario 1556 debe cargarse en la landing de montacargas');
tmd_thank_you_assert(
    ['1556' => '/gracias-montacargas/'] === ($GLOBALS['tmd_test_localized_scripts'][0]['data'] ?? null),
    'la landing de montacargas debe asociar únicamente su formulario con el destino propio'
);
$GLOBALS['tmd_test_page_id'] = 1559;
$redirect_enqueue_callback();
tmd_thank_you_assert(2 === count($GLOBALS['tmd_test_enqueued_scripts']), 'la configuración del formulario 1557 debe cargarse en la landing de baterías');
tmd_thank_you_assert(
    ['1557' => '/gracias-baterias/'] === ($GLOBALS['tmd_test_localized_scripts'][1]['data'] ?? null),
    'la landing de baterías debe asociar únicamente su formulario con el destino propio'
);
$GLOBALS['tmd_test_page_id'] = 0;
$GLOBALS['tmd_test_page_slug'] = 'contacto';
$redirect_enqueue_callback();
tmd_thank_you_assert(2 === count($GLOBALS['tmd_test_enqueued_scripts']), 'la redirección no debe cargarse en páginas ajenas');

echo "OK: renderizado de páginas de agradecimiento y robots SEO.\n";
