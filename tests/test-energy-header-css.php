<?php

$css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-energy-catalog.css');

foreach ([
    'body.page-id-63 .hero-section',
    'body.page-id-63 .ct-hero-section',
    'body.page-id-63 .ct-page-title',
    'body.page-id-63 .entry-header',
] as $selector) {
    if (false === strpos($css, $selector)) {
        fwrite(STDERR, "FAIL: Falta el selector focalizado {$selector}.\n");
        exit(1);
    }
}

if (! preg_match('/body\.page-id-63 \.hero-section,[\s\S]*?body\.page-id-63 \.entry-header\s*\{\s*display:\s*none\s*!important;\s*\}/', $css)) {
    fwrite(STDERR, "FAIL: El grupo de cabeceras de Energía debe ocultarse por completo.\n");
    exit(1);
}

fwrite(STDOUT, "OK: cabecera de tema oculta exclusivamente en la página 63.\n");
