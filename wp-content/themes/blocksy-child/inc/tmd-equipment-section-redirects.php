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
