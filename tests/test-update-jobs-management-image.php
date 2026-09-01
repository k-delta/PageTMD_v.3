<?php

define('ABSPATH', __DIR__ . '/../');
require __DIR__ . '/../scripts/update-jobs-management-image.php';

function tmd_jobs_management_test_assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$target_filename = tmd_jobs_management_target_filename();
$target_url = 'https://tecnimontacargas.com/wp-content/uploads/2026/09/' . $target_filename;
$old_url = tmd_jobs_management_old_image_url();
$fixture = '<section><h2>NUESTRO EQUIPO</h2><img src="' . $old_url . '"></section>';

$result = tmd_jobs_management_transform($fixture, $target_url);

tmd_jobs_management_test_assert(true === $result['changed'], 'Debe reemplazar la imagen anterior una sola vez.');
tmd_jobs_management_test_assert([] === $result['errors'], 'La transformación válida no debe producir errores.');
tmd_jobs_management_test_assert(1 === substr_count($result['content'], $target_url), 'Debe insertar una sola URL objetivo.');
tmd_jobs_management_test_assert(0 === substr_count($result['content'], $old_url), 'La URL antigua debe desaparecer del contenido actualizado.');
tmd_jobs_management_test_assert(true === tmd_jobs_management_attached_file_matches('2026/09/gerencia.webp'), 'Debe aceptar el basename exacto en una ruta de uploads.');
tmd_jobs_management_test_assert(false === tmd_jobs_management_attached_file_matches('2026/09/gerencia-1.webp'), 'No debe aceptar un archivo numerado parecido.');
tmd_jobs_management_test_assert(false === tmd_jobs_management_attached_file_matches('2026/09/gerencia-scaled.webp'), 'No debe aceptar otra variante de gerencia.');

$idempotent = tmd_jobs_management_transform($result['content'], $target_url);
tmd_jobs_management_test_assert(false === $idempotent['changed'], 'La segunda ejecución debe ser idempotente.');
tmd_jobs_management_test_assert($result['content'] === $idempotent['content'], 'La segunda ejecución no debe alterar el contenido.');

$ambiguous = tmd_jobs_management_transform(
    '<section><h2>NUESTRO EQUIPO</h2><img src="' . $old_url . '"><img src="' . $old_url . '"></section>',
    $target_url
);
tmd_jobs_management_test_assert([] !== $ambiguous['errors'], 'Dos referencias antiguas deben bloquear la transformación.');

$missing_block = tmd_jobs_management_transform('<p>Contenido sin el bloque esperado</p>', $target_url);
tmd_jobs_management_test_assert([] !== $missing_block['errors'], 'Un contenido sin el bloque esperado debe bloquearse.');
tmd_jobs_management_test_assert('gerencia.webp' === $target_filename, 'Debe fijar el nombre exacto del attachment publicado.');

echo "OK: imagen exacta de Nuestro equipo, transformación e idempotencia.\n";
