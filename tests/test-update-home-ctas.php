<?php

require_once dirname(__DIR__) . '/scripts/update-home-ctas.php';

function tmd_home_ctas_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$snapshot_path = dirname(__DIR__) . '/production-snapshot/pages.json';
$pages         = json_decode(file_get_contents($snapshot_path), true);
$home_content  = null;
$expected_buttons = [
    '47_e9dd8c-4a' => '/equipos/',
    '47_23547f-fc' => '/mantenimiento/',
    '47_2c1f64-1c' => '/energia/',
    '47_2a907e-63' => '/energia/baterias/',
    '47_95e299-1f' => '/energia/cargadores/',
];
$previous_text  = 'Opciones de litio y plomo-ácido para equipos eléctricos de manejo de carga.';
$expected_texts = [
    '47_6f15fb-12' => 'Baterías de tracción para montacargas eléctricos, con criterios de selección, carga y cuidado.',
    '47_a1bb23-84' => 'Cargadores industriales para baterías de montacargas, según compatibilidad, voltaje y capacidad.',
];

foreach ($pages as $page) {
    if (47 === (int) ($page['ID'] ?? 0)) {
        $home_content = (string) ($page['post_content'] ?? '');
        break;
    }
}

tmd_home_ctas_test_assert(is_string($home_content) && '' !== $home_content, 'El fixture del inicio ID 47 debe existir.');

$snapshot_content = $home_content;
foreach ($expected_buttons as $target_url) {
    $encoded      = json_encode($target_url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $home_content = str_replace(',"link":' . $encoded . '}', '}', $home_content, $replacement_count);
    tmd_home_ctas_test_assert(1 === $replacement_count, "El fixture previo debe retirar una vez {$target_url}.");
}
foreach ($expected_texts as $target_text) {
    $home_content = str_replace($target_text, $previous_text, $home_content, $replacement_count);
    tmd_home_ctas_test_assert(1 === $replacement_count, 'El fixture previo debe restaurar cada texto una vez.');
}

$result = tmd_transform_home_ctas($home_content);
tmd_home_ctas_test_assert([] === $result['errors'], 'La transformación base no debe producir errores.');
tmd_home_ctas_test_assert(7 === count($result['changes']), 'Deben aplicarse cinco URLs y dos textos.');

foreach ($expected_buttons as $unique_id => $target_url) {
    $error = '';
    $block = tmd_home_ctas_find_block_comment($result['content'], 'kadence/singlebtn', $unique_id, $error);

    tmd_home_ctas_test_assert(null !== $block, "Debe existir el CTA {$unique_id} después de transformar.");
    tmd_home_ctas_test_assert(
        ($block['attributes']['link'] ?? '') === $target_url,
        "El CTA {$unique_id} debe apuntar a {$target_url}."
    );
}

foreach ($expected_texts as $unique_id => $target_text) {
    $error = '';
    $block = tmd_home_ctas_find_block_comment($result['content'], 'kadence/column', $unique_id, $error);
    tmd_home_ctas_test_assert(null !== $block, "Debe existir la tarjeta {$unique_id} después de transformar.");
    $segment_end = strpos($result['content'], '<!-- /wp:kadence/column -->', $block['offset']);
    $segment     = substr($result['content'], $block['offset'], $segment_end - $block['offset']);

    tmd_home_ctas_test_assert(
        1 === substr_count($segment, $target_text),
        "El texto final de {$unique_id} debe aparecer una vez."
    );
}

$idempotent = tmd_transform_home_ctas($result['content']);
tmd_home_ctas_test_assert([] === $idempotent['errors'], 'La segunda transformación no debe fallar.');
tmd_home_ctas_test_assert([] === $idempotent['changes'], 'La segunda transformación no debe producir cambios.');
tmd_home_ctas_test_assert($result['content'] === $idempotent['content'], 'La segunda transformación debe ser idéntica.');

$snapshot_result = tmd_transform_home_ctas($snapshot_content);
tmd_home_ctas_test_assert([] === $snapshot_result['errors'], 'El snapshot actualizado no debe fallar.');
tmd_home_ctas_test_assert([] === $snapshot_result['changes'], 'El snapshot actualizado no debe requerir otra migración.');
tmd_home_ctas_test_assert($snapshot_content === $snapshot_result['content'], 'El snapshot actualizado debe permanecer idéntico.');

$missing_block = str_replace('47_e9dd8c-4a', '47_missing-00', $home_content);
$missing       = tmd_transform_home_ctas($missing_block);
tmd_home_ctas_test_assert([] !== $missing['errors'], 'Un bloque ausente debe detener la transformación.');
tmd_home_ctas_test_assert($missing_block === $missing['content'], 'Un error debe devolver el contenido original sin cambios parciales.');

$contradictory = preg_replace(
    '/("uniqueID":"47_e9dd8c-4a"[^\r\n]*)(}) \/-->/',
    '$1,"link":"/destino-incorrecto/"$2 /-->',
    $home_content,
    1,
    $replacement_count
);
tmd_home_ctas_test_assert(1 === $replacement_count, 'El fixture contradictorio debe construirse una vez.');

$contradiction = tmd_transform_home_ctas($contradictory);
tmd_home_ctas_test_assert([] !== $contradiction['errors'], 'Un destino contradictorio debe detener la transformación.');
tmd_home_ctas_test_assert($contradictory === $contradiction['content'], 'La contradicción no debe causar cambios parciales.');

$reverted = $result['content'];
foreach ($expected_buttons as $target_url) {
    $encoded  = json_encode($target_url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $reverted = str_replace(',"link":' . $encoded . '}', '}', $reverted);
}
foreach ($expected_texts as $target_text) {
    $reverted = str_replace($target_text, $previous_text, $reverted);
}

tmd_home_ctas_test_assert($home_content === $reverted, 'La transformación solo debe cambiar URLs y textos objetivo.');

fwrite(STDOUT, "OK: transformación, destinos, textos, idempotencia y precondiciones.\n");
