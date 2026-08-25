<?php

$source = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-energy-structure.php');

function tmd_energy_icon_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function tmd_energy_icon_rule($source, $selector) {
    $pattern = '/' . preg_quote($selector, '/') . '\s*\{(?<declarations>[^}]*)\}/';
    if (! preg_match($pattern, $source, $match)) {
        return '';
    }

    return $match['declarations'];
}

tmd_energy_icon_assert(false !== $source, 'Debe poder leerse el CSS focalizado de Cargadores.');

$card_selector = 'body.page-id-255 .tmd-energy-compatibility-title + .tmd-energy-cards > .tmd-energy-card';
$icon_selector = $card_selector . '::before';
$card_rule = tmd_energy_icon_rule($source, $card_selector);
$icon_rule = tmd_energy_icon_rule($source, $icon_selector);

tmd_energy_icon_assert(
    false !== strpos($icon_rule, 'width: 46px;')
        && false !== strpos($icon_rule, 'height: 46px;')
        && false !== strpos($icon_rule, 'background-size: 28px 28px;')
        && false !== strpos($icon_rule, "content: '';"),
    'La regla focalizada de iconos debe definir SVG de 28px en cajas de 46x46px.'
);
tmd_energy_icon_assert(
    false !== strpos($card_rule, 'padding: 82px 22px 22px !important;'),
    'La regla focalizada de tarjetas debe reservar espacio suficiente sobre el titulo.'
);

$icon_rules = [
    1 => tmd_energy_icon_rule($source, $card_selector . ':nth-child(1)::before'),
    2 => tmd_energy_icon_rule($source, $card_selector . ':nth-child(2)::before'),
    3 => tmd_energy_icon_rule($source, $card_selector . ':nth-child(3)::before'),
];
$semantic_shapes = [
    1 => "M13 2 4.5 14h7L11 22l8.5-12h-7L13 2Z",
    2 => "%3Crect x='3' y='6' width='16' height='12' rx='2'/%3E",
    3 => "%3Ccircle cx='12' cy='12' r='3'/%3E",
];

foreach ($icon_rules as $card_number => $rule) {
    tmd_energy_icon_assert(
        false !== strpos($rule, 'data:image/svg+xml,')
            && false !== strpos($rule, "stroke-width='2.2'")
            && false !== strpos($rule, "stroke-linecap='round'")
            && false !== strpos($rule, "stroke-linejoin='round'"),
        "La tarjeta {$card_number} debe tener un SVG focalizado con el estilo lineal comun."
    );
    tmd_energy_icon_assert(
        false !== strpos($rule, $semantic_shapes[$card_number]),
        "La tarjeta {$card_number} debe conservar el icono semantico asignado."
    );
}

foreach (["content: '⚡';", "content: '▭';", "content: '⚙';"] as $legacy_glyph) {
    tmd_energy_icon_assert(false === strpos($source, $legacy_glyph), 'No deben permanecer glifos tipograficos como iconos.');
}

fwrite(STDOUT, "OK: iconos SVG coherentes, semanticos y de mayor tamano.\n");
