<?php

require_once dirname(__DIR__) . '/scripts/update-energy-catalog-content.php';

function tmd_energy_content_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$fixture = <<<'HTML'
<!-- wp:group {"className":"tmde-shell"} -->
<div class="wp-block-group tmde-shell"><!-- wp:group {"className":"tmde-hero"} -->
<div class="wp-block-group tmde-hero"><!-- wp:heading {"level":1} -->
<h1>Hero editorial</h1>
<!-- /wp:heading --></div>
<!-- /wp:group -->
<!-- wp:html -->
<section class="tmde-bms-promo">BMS</section>
<!-- /wp:html -->
<!-- wp:group {"align":"full","className":"tmde-section"} -->
<div class="wp-block-group alignfull tmde-section"><!-- wp:group {"className":"tmde-container"} -->
<div class="wp-block-group tmde-container"><!-- wp:shortcode -->
[tmd_energy_filters set_id="589"]
<!-- /wp:shortcode -->
<!-- wp:shortcode -->
[tmd_energy_grid per_page="12"]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- wp:group {"className":"tmde-cta"} -->
<div class="wp-block-group tmde-cta">CTA</div>
<!-- /wp:group -->
HTML;

$result = tmd_transform_energy_catalog_content($fixture);
tmd_energy_content_assert([] === $result['errors'], 'La transformación base no debe fallar.');
tmd_energy_content_assert(['catalog-only'] === $result['changes'], 'Debe registrar un único cambio focalizado.');
tmd_energy_content_assert(false !== strpos($result['content'], 'tmde-section'), 'Debe conservar la sección del catálogo.');
tmd_energy_content_assert(false !== strpos($result['content'], '[tmd_energy_filters'), 'Debe conservar los filtros.');
tmd_energy_content_assert(false !== strpos($result['content'], '[tmd_energy_grid'), 'Debe conservar el grid.');

foreach (['tmde-hero', 'tmde-bms-promo', 'tmde-cta'] as $removed) {
    tmd_energy_content_assert(false === strpos($result['content'], $removed), "Debe retirar {$removed}.");
}

$again = tmd_transform_energy_catalog_content($result['content']);
tmd_energy_content_assert([] === $again['errors'], 'La segunda transformación no debe fallar.');
tmd_energy_content_assert([] === $again['changes'], 'La segunda transformación debe ser idempotente.');
tmd_energy_content_assert($result['content'] === $again['content'], 'El contenido idempotente debe ser idéntico.');

foreach (['[tmd_energy_grid per_page="12"]', '[tmd_energy_filters set_id="589"]'] as $shortcode) {
    $broken  = str_replace($shortcode, '', $fixture);
    $blocked = tmd_transform_energy_catalog_content($broken);
    tmd_energy_content_assert([] !== $blocked['errors'], "La ausencia de {$shortcode} debe bloquear la transformación.");
    tmd_energy_content_assert($broken === $blocked['content'], 'Un error debe preservar el contenido original.');
}

$mismatched = str_replace(
    "<!-- /wp:group --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->",
    "<!-- /wp:columns --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->",
    $fixture,
    $replacement_count
);
tmd_energy_content_assert(1 === $replacement_count, 'Debe construirse el cierre incompatible.');
tmd_energy_content_assert([] !== tmd_transform_energy_catalog_content($mismatched)['errors'], 'Un cierre incompatible debe bloquear la transformación.');

$unclosed = preg_replace('/<!-- \/wp:group -->\s*$/', '', $result['content']);
tmd_energy_content_assert([] !== tmd_transform_energy_catalog_content($unclosed)['errors'], 'Una sección sin cierre válido debe bloquear la transformación.');

$missing_section = str_replace('"className":"tmde-section"', '"className":"otra-seccion"', $fixture);
tmd_energy_content_assert([] !== tmd_transform_energy_catalog_content($missing_section)['errors'], 'Una sección ausente debe bloquear la transformación.');

$duplicate_section = $fixture . "\n" . $result['content'];
tmd_energy_content_assert([] !== tmd_transform_energy_catalog_content($duplicate_section)['errors'], 'Una sección duplicada debe bloquear la transformación.');

$pages = json_decode(file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json'), true);
$snapshot = '';
foreach ($pages as $page) {
    if (63 === (int) ($page['ID'] ?? 0)) {
        $snapshot = (string) ($page['post_content'] ?? '');
        break;
    }
}

$snapshot_result = tmd_transform_energy_catalog_content($snapshot);
tmd_energy_content_assert([] === $snapshot_result['errors'], 'El snapshot debe cumplir las precondiciones.');
tmd_energy_content_assert(false !== strpos($snapshot_result['content'], '[tmd_energy_grid'), 'El snapshot transformado conserva el grid.');

fwrite(STDOUT, "OK: catálogo exclusivo, precondiciones e idempotencia.\n");
