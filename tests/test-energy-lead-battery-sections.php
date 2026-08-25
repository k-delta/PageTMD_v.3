<?php

$source = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-energy-structure.php');

if (! is_string($source)) {
    fwrite(STDERR, "No se pudo leer tmd-energy-structure.php.\n");
    exit(1);
}

function tmd_lead_battery_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "FALLO: {$message}\n");
        exit(1);
    }
}

$style_start = strpos($source, '<style id="tmd-energy-lead-battery-page-adjustments">');
$style_end = false === $style_start ? false : strpos($source, '</style>', $style_start);

tmd_lead_battery_assert(false !== $style_start && false !== $style_end, 'Debe existir el bloque de estilos focalizado de Baterías de plomo.');

$styles = substr($source, $style_start, $style_end - $style_start);

tmd_lead_battery_assert(str_contains($source, 'is_page(401)'), 'Los estilos deben cargarse únicamente en la página 401.');
tmd_lead_battery_assert(str_contains($styles, 'body.page-id-401 .tmd-energy-inner--plomo'), 'Todos los selectores deben partir del ámbito canónico de la página.');
tmd_lead_battery_assert(! str_contains($styles, 'body.page-id-255'), 'El ajuste no debe alcanzar la página de Cargadores.');

$required_selectors = [
    '.tmd-energy-section:not(.tmd-energy-split) > .tmd-energy-cards',
    '.tmd-energy-card:nth-child(1)',
    '.tmd-energy-card:nth-child(2)',
    '.tmd-energy-card:nth-child(3)',
    '.tmd-energy-split > .wp-block-columns',
    '.tmd-energy-checklist li:last-child:nth-child(odd)',
];

foreach ($required_selectors as $selector) {
    tmd_lead_battery_assert(str_contains($styles, $selector), "Falta el selector requerido: {$selector}");
}

tmd_lead_battery_assert(substr_count($styles, "stroke='%23ffc33c'") >= 3, 'Los tres iconos SVG deben usar amarillo de marca.');
tmd_lead_battery_assert(str_contains($styles, "%3Crect x='4' y='5' width='16' height='15' rx='2'/%3E"), 'La primera tarjeta debe incluir un calendario SVG.');
tmd_lead_battery_assert(str_contains($styles, "%3Cpath d='M8 12h8'/%3E"), 'La segunda tarjeta debe incluir un conector SVG.');
tmd_lead_battery_assert(str_contains($styles, "%3Cpath d='m14.7 6.3 3-3"), 'La tercera tarjeta debe incluir una llave SVG.');

tmd_lead_battery_assert(str_contains($styles, 'background: #262e4f !important;'), 'La primera tarjeta debe usar azul oscuro.');
tmd_lead_battery_assert(str_contains($styles, 'border-top: 4px solid #ffc33c !important;'), 'La segunda tarjeta debe usar acento amarillo.');
tmd_lead_battery_assert(str_contains($styles, 'background: #eef4f9 !important;'), 'La tercera tarjeta debe usar azul grisáceo.');
tmd_lead_battery_assert(str_contains($styles, 'grid-template-columns: repeat(2, minmax(0, 1fr)) !important;'), 'El checklist debe usar dos columnas en escritorio.');
tmd_lead_battery_assert(str_contains($styles, '@media (max-width: 781px)'), 'Debe existir un ajuste responsive para móvil.');
tmd_lead_battery_assert(str_contains($styles, 'grid-template-columns: 1fr !important;'), 'Las composiciones deben apilarse en móvil.');

echo "OK: estilos focalizados de Baterías de plomo verificados.\n";
