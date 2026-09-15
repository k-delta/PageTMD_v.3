<?php

define('ABSPATH', dirname(__DIR__) . '/');
require dirname(__DIR__) . '/scripts/update-jobs-vacancies.php';

function tmd_jobs_vacancies_test_assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function tmd_jobs_vacancies_test_section(string $content): string
{
    $errors = [];
    $bounds = tmd_jobs_vacancies_section_bounds($content, $errors);

    tmd_jobs_vacancies_test_assert([] === $errors, 'El contenido de prueba debe tener una sección de vacantes válida.');
    tmd_jobs_vacancies_test_assert(is_array($bounds), 'Deben existir límites para la sección de vacantes.');

    return substr($content, $bounds[0], $bounds[1]);
}

$pages = json_decode(
    file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json'),
    true
);
$snapshot_content = '';

foreach ($pages as $page) {
    if (273 === (int) ($page['ID'] ?? 0)) {
        $snapshot_content = (string) ($page['post_content'] ?? '');
        break;
    }
}

tmd_jobs_vacancies_test_assert('' !== $snapshot_content, 'Debe existir el contenido de la página 273.');

$original_section = tmd_jobs_vacancies_test_section($snapshot_content);
$fragments = tmd_jobs_vacancies_fragments();
$original_card_errors = [];
$original_cards = tmd_jobs_vacancies_extract_card_signatures($original_section, $original_card_errors);
$expected_cards = tmd_jobs_vacancies_expected_card_signatures();
tmd_jobs_vacancies_test_assert([] === $original_card_errors, 'Las tarjetas actuales deben poder analizarse.');
tmd_jobs_vacancies_test_assert($expected_cards['old'] === $original_cards, 'El snapshot debe conservar las dos tarjetas anteriores en el orden esperado.');
$result = tmd_transform_jobs_vacancies($snapshot_content);

tmd_jobs_vacancies_test_assert([] === $result['errors'], 'La transformación del contenido actual no debe producir errores.');
tmd_jobs_vacancies_test_assert(true === $result['changed'], 'El contenido actual debe requerir la actualización solicitada.');
tmd_jobs_vacancies_test_assert(3 === count($result['changes']), 'Debe informar las tres actualizaciones de vacantes.');

$updated_section = tmd_jobs_vacancies_test_section($result['content']);
$updated_card_errors = [];
$updated_cards = tmd_jobs_vacancies_extract_card_signatures($updated_section, $updated_card_errors);
tmd_jobs_vacancies_test_assert([] === $updated_card_errors, 'Las tarjetas actualizadas deben poder analizarse.');
tmd_jobs_vacancies_test_assert($expected_cards['new'] === $updated_cards, 'Las tres tarjetas actualizadas deben quedar asociadas y ordenadas.');
$expected_fragments = [
    '<h3>Técnico Especializado en Montacargas Eléctricos</h3>',
    '<p>Buscamos Técnico Especializado en Montacargas Eléctricos, con experiencia en diagnóstico, mantenimiento y reparación de sistemas eléctricos, electrónicos, electromecánicos e hidráulicos, para vincularse a nuestra sede principal.</p>',
    '<span class="tmd-job-badge">Técnico</span>',
    '<h3>Auxiliar Técnico en Entrenamiento</h3>',
    '<p>Buscamos Auxiliar Técnico en Entrenamiento, con conocimientos básicos en electromecánica, electricidad o mecánica, disposición para aprender y crecer profesionalmente en el mantenimiento y reparación de montacargas. No se requiere experiencia.</p>',
    '<h3>Técnico Especializado en Montacargas de Combustión</h3>',
    '<p>Buscamos Técnico Especializado en Montacargas de Combustión, con experiencia en diagnóstico, mantenimiento y reparación de motores, sistemas hidráulicos, transmisiones, frenos y componentes mecánicos. Vinculación para nuestra sede principal.</p>',
    '<span>📍 Bogotá, D.C.</span>',
    '<span>💰 Salario a convenir</span>',
    'href="#postulacion">Aplicar a esta vacante</a>',
];

foreach ($expected_fragments as $fragment) {
    tmd_jobs_vacancies_test_assert(
        false !== strpos($updated_section, $fragment),
        "Debe existir {$fragment}."
    );
}

tmd_jobs_vacancies_test_assert(
    3 === substr_count($updated_section, '<article class="tmd-job-card">'),
    'La sección actualizada debe contener exactamente tres tarjetas.'
);
tmd_jobs_vacancies_test_assert(
    false === strpos($updated_section, 'Técnico en Mantenimiento de Montacargas'),
    'El título anterior de la primera vacante debe desaparecer.'
);
tmd_jobs_vacancies_test_assert(
    false === strpos($updated_section, 'Auxiliar Administrativo'),
    'El título anterior de la segunda vacante debe desaparecer.'
);
tmd_jobs_vacancies_test_assert(
    false === strpos($updated_section, 'Responsable de la programación de servicios preventivos y hablar con clientes.'),
    'La descripción anterior de la segunda vacante debe desaparecer.'
);

$original_bounds_errors = [];
$updated_bounds_errors = [];
$original_bounds = tmd_jobs_vacancies_section_bounds($snapshot_content, $original_bounds_errors);
$updated_bounds = tmd_jobs_vacancies_section_bounds($result['content'], $updated_bounds_errors);
$original_prefix = substr($snapshot_content, 0, $original_bounds[0]);
$updated_prefix = substr($result['content'], 0, $updated_bounds[0]);
$original_suffix = substr($snapshot_content, $original_bounds[0] + $original_bounds[1]);
$updated_suffix = substr($result['content'], $updated_bounds[0] + $updated_bounds[1]);

tmd_jobs_vacancies_test_assert($original_prefix === $updated_prefix, 'El contenido anterior a las vacantes debe permanecer idéntico.');
tmd_jobs_vacancies_test_assert($original_suffix === $updated_suffix, 'El contenido posterior a las vacantes debe permanecer idéntico.');
tmd_jobs_vacancies_test_assert($original_section !== $updated_section, 'La sección de vacantes debe cambiar de forma focalizada.');

$again = tmd_transform_jobs_vacancies($result['content']);
tmd_jobs_vacancies_test_assert([] === $again['errors'], 'La segunda ejecución no debe producir errores.');
tmd_jobs_vacancies_test_assert([] === $again['changes'], 'La segunda ejecución debe ser idempotente.');
tmd_jobs_vacancies_test_assert($result['content'] === $again['content'], 'La segunda ejecución no debe alterar el contenido.');

$missing = str_replace($fragments['electric']['old'], 'Texto inesperado.', $snapshot_content, $replacements);
tmd_jobs_vacancies_test_assert(1 === $replacements, 'La fixture de precondición ausente debe cambiar una sola vez.');
$blocked_missing = tmd_transform_jobs_vacancies($missing);
tmd_jobs_vacancies_test_assert([] !== $blocked_missing['errors'], 'Una precondición ausente debe bloquear la transformación.');
tmd_jobs_vacancies_test_assert($missing === $blocked_missing['content'], 'Un error debe devolver el contenido original.');

$mixed = str_replace($fragments['electric']['old'], $fragments['electric']['new'], $snapshot_content, $replacements);
tmd_jobs_vacancies_test_assert(1 === $replacements, 'La fixture de estado mixto debe cambiar una sola vez.');
$blocked_mixed = tmd_transform_jobs_vacancies($mixed);
tmd_jobs_vacancies_test_assert([] !== $blocked_mixed['errors'], 'Un estado mixto debe bloquear la transformación.');
tmd_jobs_vacancies_test_assert($mixed === $blocked_mixed['content'], 'Un estado mixto no debe producir cambios parciales.');

$old_electric_card = tmd_jobs_vacancies_card_html(
    'Técnico',
    'Técnico en Mantenimiento de Montacargas',
    'Buscamos técnicos con experiencia en sistemas hidráulicos y motores de combustión para nuestra sede principal.'
);
$old_training_card = tmd_jobs_vacancies_card_html(
    'Administrativo',
    'Auxiliar Administrativo',
    'Responsable de la programación de servicios preventivos y hablar con clientes.'
);
$swapped_section = str_replace($old_electric_card, '__TMD_ELECTRIC_CARD__', $original_section, $replacements);
tmd_jobs_vacancies_test_assert(1 === $replacements, 'La fixture de orden invertido debe encontrar la primera tarjeta.');
$swapped_section = str_replace($old_training_card, $old_electric_card, $swapped_section, $replacements);
tmd_jobs_vacancies_test_assert(1 === $replacements, 'La fixture de orden invertido debe encontrar la segunda tarjeta.');
$swapped_section = str_replace('__TMD_ELECTRIC_CARD__', $old_training_card, $swapped_section, $replacements);
tmd_jobs_vacancies_test_assert(1 === $replacements, 'La fixture de orden invertido debe restaurar ambas tarjetas.');
$swapped = substr($snapshot_content, 0, $original_bounds[0])
    . $swapped_section
    . substr($snapshot_content, $original_bounds[0] + $original_bounds[1]);
$blocked_order = tmd_transform_jobs_vacancies($swapped);
tmd_jobs_vacancies_test_assert([] !== $blocked_order['errors'], 'Un orden de tarjetas incorrecto debe bloquear la transformación.');
tmd_jobs_vacancies_test_assert($swapped === $blocked_order['content'], 'Un orden incorrecto no debe producir cambios parciales.');

echo "OK: vacantes, preservación, precondiciones e idempotencia.\n";
