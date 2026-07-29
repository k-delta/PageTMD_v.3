<?php
/**
 * SEO técnico, rutas históricas y modo de cuenta sin comercio activo.
 */

defined('ABSPATH') || exit;

/**
 * Conserva autoridad de URLs históricas y evita contenido duplicado o vacío.
 */
add_action('template_redirect', static function (): void {
    $path = trailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));

    $permanent_redirects = [
        '/equipos/que-es/contrabalanceadas/' => '/equipos/tipos/contrabalanceados/',
        '/equipos/que-es/retractiles/' => '/equipos/tipos/reach-retractiles/',
        '/equipos/que-es/apiladores/' => '/equipos/tipos/apiladores-electricos/',
        '/equipos/que-es/transpaletas/' => '/equipos/tipos/estibadores-y-apiladores/',
        '/equipos/que-es/recogepedidos/' => '/equipos/tipos/tomapedidos/',
        '/equipos/que-es/estibadores-manuales/' => '/equipos/tipos/estibadores-manuales/',
        '/equipos/que-es/manlift/' => '/equipos/',
        '/equipos/toyota-8fgu25/' => '/equipos/tipos/contrabalanceados/',
        '/equipos/yale-reach-truck/' => '/equipos/tipos/reach-retractiles/',
        '/equipos/apilador-electrico/' => '/equipos/tipos/apiladores-electricos/',
        '/equipos/transpaleta-manual/' => '/equipos/tipos/estibadores-y-apiladores/',
        '/equipos/manlift-articulado/' => '/equipos/',
        '/equipos/crown-electrico/' => '/equipos/',
        '/energia/bateria-plomo-acido-tubular/' => '/energia/baterias/plomo/',
        '/energia/cargador-industrial-renma/' => '/energia/cargadores/',
        '/maquinas/' => '/equipos/',
        '/pantografos/' => '/equipos/tipos/reach-retractiles/',
        '/nosotros/' => '/nosotros/quienes-somos/',
    ];

    if (isset($permanent_redirects[$path])) {
        wp_safe_redirect(home_url($permanent_redirects[$path]), 301);
        exit;
    }

    $commerce_redirects = [
        '/tienda/' => '/equipos/',
        '/carrito/' => '/mi-cuenta/',
        '/finalizar-compra/' => '/mi-cuenta/',
    ];

    if (isset($commerce_redirects[$path])) {
        wp_safe_redirect(home_url($commerce_redirects[$path]), 302);
        exit;
    }
}, 0);

/**
 * Evita carga de estilos y scripts comerciales fuera del portal de clientes.
 */
add_filter('woocommerce_enqueue_styles', static function (array $styles): array {
    return function_exists('is_account_page') && is_account_page() ? $styles : [];
});

add_filter('woocommerce_get_script_data', static function ($params, string $handle) {
    if (function_exists('is_account_page') && is_account_page()) {
        return $params;
    }

    $commerce_handles = [
        'wc-cart-fragments',
        'woocommerce',
        'wc-add-to-cart',
        'wc-checkout',
        'wc-cart',
        'wc-add-to-cart-variation',
    ];

    return in_array($handle, $commerce_handles, true) ? null : $params;
}, 10, 2);

add_action('wp_enqueue_scripts', static function (): void {
    if (function_exists('is_account_page') && is_account_page()) {
        return;
    }

    foreach ([
        'ct-woocommerce-styles',
        'wc-blocks-style',
        'wc-blocks-packages-style',
        'wc-blocks-vendors-style',
    ] as $handle) {
        wp_dequeue_style($handle);
    }

    foreach ([
        'wc-cart-fragments',
        'woocommerce',
        'wc-add-to-cart',
        'wc-checkout',
        'wc-cart',
        'wc-add-to-cart-variation',
    ] as $handle) {
        wp_dequeue_script($handle);
    }
}, 100);

add_action('wp_print_styles', static function (): void {
    if (function_exists('is_account_page') && is_account_page()) {
        return;
    }

    foreach ([
        'ct-woocommerce-styles',
        'wc-blocks-style',
        'wc-blocks-packages-style',
        'wc-blocks-vendors-style',
    ] as $handle) {
        wp_dequeue_style($handle);
    }
}, 999);

add_filter('style_loader_tag', static function (string $html, string $handle): string {
    if (function_exists('is_account_page') && is_account_page()) {
        return $html;
    }

    return in_array($handle, [
        'ct-woocommerce-styles',
        'wc-blocks-style',
        'wc-blocks-packages-style',
        'wc-blocks-vendors-style',
    ], true) ? '' : $html;
}, 100, 2);

/**
 * Mi cuenta queda como portal de perfil, sin navegación de compras.
 */
add_filter('woocommerce_account_menu_items', static function (array $items): array {
    $allowed = ['dashboard', 'edit-account', 'customer-logout'];
    return array_intersect_key($items, array_flip($allowed));
}, 20);

/**
 * Schema de servicio emitido dentro del grafo gestionado por Rank Math.
 */
add_filter('rank_math/json_ld', static function (array $data): array {
    if (!is_page()) {
        return $data;
    }

    foreach ($data as $key => $entity) {
        $types = isset($entity['@type']) ? (array) $entity['@type'] : [];

        if (array_intersect($types, ['Article', 'Person'])) {
            unset($data[$key]);
        }
    }

    $services = [
        49 => [
            'name' => 'Alquiler mensual de montacargas en Colombia',
            'description' => 'Alquiler de montacargas sin operador por meses y mediante contratos de largo plazo para empresas en Colombia.',
            'serviceType' => 'Alquiler de montacargas',
        ],
        506 => [
            'name' => 'Mantenimiento de montacargas en Colombia',
            'description' => 'Servicio técnico preventivo y correctivo para montacargas con cobertura nacional.',
            'serviceType' => 'Mantenimiento de montacargas',
        ],
        288 => [
            'name' => 'Mantenimiento preventivo de montacargas',
            'description' => 'Planes e inspecciones de mantenimiento preventivo para montacargas en Colombia.',
            'serviceType' => 'Mantenimiento preventivo de montacargas',
        ],
        290 => [
            'name' => 'Mantenimiento correctivo de montacargas',
            'description' => 'Diagnóstico y reparación de fallas de montacargas con cobertura nacional.',
            'serviceType' => 'Mantenimiento correctivo de montacargas',
        ],
    ];

    $page_id = get_queried_object_id();
    if (!isset($services[$page_id])) {
        return $data;
    }

    $service = $services[$page_id];
    $data['tmd-service'] = [
        '@type' => 'Service',
        '@id' => trailingslashit(get_permalink($page_id)) . '#service',
        'name' => $service['name'],
        'description' => $service['description'],
        'serviceType' => $service['serviceType'],
        'provider' => ['@id' => home_url('/#organization')],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Colombia',
        ],
        'url' => get_permalink($page_id),
    ];

    return $data;
}, 90);
