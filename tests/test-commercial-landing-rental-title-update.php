<?php
define('ABSPATH', sys_get_temp_dir() . '/tmd-rental-title-test/');
define('WP_CLI', true);

require_once dirname(__DIR__) . '/scripts/update-rental-landing-title.php';

function rental_title_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

$source_heading = '<h1 id="tmd-rental-v2-heading">Alquiler de <span>montacargas eléctricos</span></h1>';
$target_heading = '<h1 id="tmd-rental-v2-heading">Venta o alquiler de <span>montacargas eléctricos</span></h1>';
$source = '<section>' . $source_heading . '<p>Texto que debe permanecer idéntico.</p></section>';
$target = '<section>' . $target_heading . '<p>Texto que debe permanecer idéntico.</p></section>';

rental_title_assert(
    $target === tmd_commercial_landing_title_only_content($source)
        && $target === tmd_commercial_landing_title_only_content($target)
        && 1 === substr_count($target, $target_heading)
        && 0 === substr_count($target, $source_heading),
    'El cambio transforma solo el H1 y se puede repetir sin modificar el resultado.'
);

$duplicate_rejected = false;
try {
    tmd_commercial_landing_title_only_content($source . $source_heading);
} catch (RuntimeException $exception) {
    $duplicate_rejected = true;
}
rental_title_assert($duplicate_rejected, 'Un H1 de origen duplicado debe detener la actualización.');

$unknown_rejected = false;
try {
    tmd_commercial_landing_title_only_content('<section><h1>Otro título</h1></section>');
} catch (RuntimeException $exception) {
    $unknown_rejected = true;
}
rental_title_assert($unknown_rejected, 'Un H1 no reconocido debe detener la actualización.');

echo "OK: actualizador de título rental-v2: transformación exacta e idempotente.\n";
