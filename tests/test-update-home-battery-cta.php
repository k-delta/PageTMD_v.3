<?php

define('WP_CLI', false);
require dirname(__DIR__) . '/scripts/update-home-battery-cta.php';

function tmd_home_battery_cta_test_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

$home_content = (string) file_get_contents(__DIR__ . '/fixtures/home-battery-cta-content.html');
tmd_home_battery_cta_test_assert('' !== $home_content, 'El fixture anonimizado debe incluir el bloque objetivo.');

$result = tmd_home_battery_cta_transform($home_content);
tmd_home_battery_cta_test_assert([] === $result['errors'], 'La URL observada debe satisfacer las precondiciones.');
tmd_home_battery_cta_test_assert(['47_2a907e-63'] === $result['changes'], 'Debe cambiar únicamente el botón Ver Baterías.');
tmd_home_battery_cta_test_assert(
    $result['content'] === str_replace(
        '"uniqueID":"47_2a907e-63","text":"Ver Baterías","link":"/energia/baterias/"',
        '"uniqueID":"47_2a907e-63","text":"Ver Baterías","link":"/energia/baterias/plomo/"',
        $home_content
    ),
    'El contenido debe cambiar únicamente el destino solicitado y conservar el otro botón.'
);

$again = tmd_home_battery_cta_transform($result['content']);
tmd_home_battery_cta_test_assert([] === $again['errors'] && [] === $again['changes'], 'La transformación debe ser idempotente.');
tmd_home_battery_cta_test_assert($again['content'] === $result['content'], 'La segunda ejecución debe conservar el contenido.');

$contradictory = str_replace('"link":"/energia/baterias/"', '"link":"/energia/otro/"', $home_content);
$conflict = tmd_home_battery_cta_transform($contradictory);
tmd_home_battery_cta_test_assert([] !== $conflict['errors'], 'Un destino distinto debe rechazarse.');
tmd_home_battery_cta_test_assert($conflict['content'] === $contradictory && [] === $conflict['changes'], 'El conflicto no debe alterar el contenido.');

$missing = str_replace('47_2a907e-63', '47_missing-00', $home_content);
$absent = tmd_home_battery_cta_transform($missing);
tmd_home_battery_cta_test_assert([] !== $absent['errors'], 'La ausencia del bloque debe rechazarse.');
tmd_home_battery_cta_test_assert($absent['content'] === $missing, 'La ausencia del bloque no debe producir cambios parciales.');

fwrite(STDOUT, "OK: ruta objetivo, preservación del contenido, idempotencia y conflictos.\n");
