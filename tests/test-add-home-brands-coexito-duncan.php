<?php

define('ABSPATH', dirname(__DIR__) . '/');

require_once dirname(__DIR__) . '/scripts/add-home-brands-coexito-duncan.php';

function tmd_home_brands_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$snapshot = file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json');
$pages = json_decode($snapshot, true);
$home = null;
foreach ($pages as $page) {
    if (47 === (int) ($page['ID'] ?? 0)) {
        $home = $page;
        break;
    }
}

tmd_home_brands_test_assert(is_array($home), 'Debe existir la página de inicio 47 en el snapshot.');

$result = tmd_transform_home_brands_coexito_duncan((string) $home['post_content']);
tmd_home_brands_test_assert([] === $result['errors'], 'La transformación del snapshot no debe producir errores.');
tmd_home_brands_test_assert(2 === count($result['changes']), 'Debe informar las dos marcas nuevas.');
tmd_home_brands_test_assert(1 === substr_count($result['content'], 'coexito.webp'), 'Debe agregar la imagen de Coéxito.');
tmd_home_brands_test_assert(1 === substr_count($result['content'], 'duncan.webp'), 'Debe agregar la imagen de Duncan.');
tmd_home_brands_test_assert(false !== strpos($result['content'], 'alt="Coéxito"'), 'Debe conservar el texto alternativo de Coéxito.');
tmd_home_brands_test_assert(false !== strpos($result['content'], 'alt="Duncan"'), 'Debe conservar el texto alternativo de Duncan.');

$idempotent = tmd_transform_home_brands_coexito_duncan($result['content']);
tmd_home_brands_test_assert([] === $idempotent['errors'], 'La segunda aplicación no debe producir errores.');
tmd_home_brands_test_assert([] === $idempotent['changes'], 'La segunda aplicación no debe informar cambios.');
tmd_home_brands_test_assert($result['content'] === $idempotent['content'], 'La segunda aplicación no debe alterar el contenido.');

echo "OK: Coéxito y Duncan se agregan de forma idempotente.\n";
