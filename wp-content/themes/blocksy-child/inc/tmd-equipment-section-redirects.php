<?php
defined('ABSPATH') || exit;

add_action('template_redirect', static function () {
    $path = trailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $redirects = [
        '/equipos/tipos/traslado-y-elevacion-ligera/' => '/equipos/tipos/estibadores-y-apiladores/',
        '/equipos/tipos/pasillo-angosto/' => '/equipos/tipos/reach-retractiles/',
        '/equipos/tipos/preparacion-de-pedidos/' => '/equipos/tipos/tomapedidos/',
    ];

    if (!isset($redirects[$path])) {
        return;
    }

    wp_safe_redirect(home_url($redirects[$path]), 301);
    exit;
}, 1);

/* TMD_HOME_EQUIPMENT_CARD_START */
add_filter('the_content', static function ($content) {
    if (!is_page(47) || strpos((string) $content, '47_83d64e-ce') === false) {
        return $content;
    }

    return strtr((string) $content, [
        'Energía Industrial' => 'Montacargas',
        'Soluciones de energía para flotas eléctricas' => 'Equipos para cada operación',
        'Baterías, cargadores y soporte técnico para mantener sus equipos eléctricos operando con autonomía y seguridad.' => 'Montacargas para compra y alquiler, seleccionados según capacidad, altura de levante y condiciones de operación.',
        '"text":"Ver Energía"' => '"text":"Ver Equipos"',
        '"link":"/energia/"' => '"link":"/equipos/"',
    ]);
}, 8);
/* TMD_HOME_EQUIPMENT_CARD_END */
