<?php

define('ABSPATH', dirname(__DIR__) . '/');

require_once dirname(__DIR__) . '/scripts/add-bms-page-image.php';

function tmd_bms_page_image_test_assert($condition, $message)
{
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$pages = json_decode(file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json'), true);
$bms = null;
foreach ($pages as $page) {
    if (792 === (int) ($page['ID'] ?? 0)) {
        $bms = $page;
        break;
    }
}

tmd_bms_page_image_test_assert(is_array($bms), 'Debe existir la página BMS 792 en el snapshot.');

$image_url = 'https://tecnimontacargas.com/wp-content/uploads/2026/09/BMS-page-2.webp';
$result = tmd_transform_bms_page_image((string) $bms['post_content'], $image_url);

tmd_bms_page_image_test_assert([] === $result['errors'], 'La transformación del snapshot no debe producir errores.');
tmd_bms_page_image_test_assert(['imagen:BMS-page-2.webp'] === $result['changes'], 'Debe registrar la imagen nueva.');
tmd_bms_page_image_test_assert(1 === substr_count($result['content'], 'BMS-page-2.webp'), 'Debe insertar una sola referencia al archivo.');
tmd_bms_page_image_test_assert(1 === substr_count($result['content'], 'alt="BMS para monitoreo de baterías de montacargas"'), 'Debe insertar el texto alternativo contextual.');

$media_position = strpos($result['content'], '<section class="tmd-bms-media-section"');
$nav_position = strpos($result['content'], '<nav class="tmd-bms-nav');
$intro_position = strpos($result['content'], '<div class="tmd-bms-wrap tmd-bms-intro">');
tmd_bms_page_image_test_assert(false !== $media_position && $nav_position < $media_position && $media_position < $intro_position, 'La imagen debe quedar entre la navegación y la introducción.');

$idempotent = tmd_transform_bms_page_image($result['content'], $image_url);
tmd_bms_page_image_test_assert([] === $idempotent['errors'], 'La segunda aplicación no debe producir errores.');
tmd_bms_page_image_test_assert([] === $idempotent['changes'], 'La segunda aplicación no debe informar cambios.');
tmd_bms_page_image_test_assert($result['content'] === $idempotent['content'], 'La segunda aplicación debe conservar el contenido byte por byte.');

$missing_intro = str_replace(
    "<section class=\"tmd-bms-section\">\n    <div class=\"tmd-bms-wrap tmd-bms-intro\">",
    "<section class=\"tmd-bms-section\">\n    <div class=\"tmd-bms-wrap tmd-bms-intro-altered\">",
    (string) $bms['post_content'],
    $replacement_count
);
tmd_bms_page_image_test_assert(1 === $replacement_count, 'Debe poder construirse el contenido con precondición inválida.');
$blocked_intro = tmd_transform_bms_page_image($missing_intro, $image_url);
tmd_bms_page_image_test_assert([] !== $blocked_intro['errors'], 'Un bloque introductorio alterado debe bloquear la transformación.');
tmd_bms_page_image_test_assert($missing_intro === $blocked_intro['content'], 'El bloqueo no debe modificar el contenido.');

$existing_filename = (string) $bms['post_content'] . "\n<img src=\"{$image_url}\" alt=\"otra ubicación\">";
$blocked_existing = tmd_transform_bms_page_image($existing_filename, $image_url);
tmd_bms_page_image_test_assert([] !== $blocked_existing['errors'], 'Una referencia existente fuera del bloque canónico debe bloquear la transformación.');
tmd_bms_page_image_test_assert($existing_filename === $blocked_existing['content'], 'Una referencia ambigua no debe producir escritura parcial.');

tmd_bms_page_image_test_assert([] === tmd_bms_matching_attachment_ids([], 'BMS-page-2.webp'), 'Un adjunto ausente debe producir cero coincidencias.');
tmd_bms_page_image_test_assert([21] === tmd_bms_matching_attachment_ids([21 => '2026/09/BMS-page-2.webp'], 'BMS-page-2.webp'), 'Debe resolver el adjunto con el nombre exacto.');
tmd_bms_page_image_test_assert([21, 22] === tmd_bms_matching_attachment_ids([21 => '2026/09/BMS-page-2.webp', 22 => '2026/08/BMS-page-2.webp'], 'BMS-page-2.webp'), 'Dos adjuntos con el mismo nombre deben permanecer ambiguos.');

echo "OK: imagen BMS, ubicación, idempotencia y precondiciones.\n";
