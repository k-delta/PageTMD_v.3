<?php

require_once dirname(__DIR__) . '/scripts/update-about-values.php';

function tmd_about_values_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$snapshot_path = dirname(__DIR__) . '/production-snapshot/pages.json';
$pages         = json_decode(file_get_contents($snapshot_path), true);
$page_content  = null;

foreach ($pages as $page) {
    if (278 === (int) ($page['ID'] ?? 0)) {
        $page_content = (string) ($page['post_content'] ?? '');
        break;
    }
}

tmd_about_values_test_assert(is_string($page_content) && '' !== $page_content, 'El fixture de Quiénes somos ID 278 debe existir.');

$old_titles = [
    'Sentido de pertenencia',
    'Pasión en nuestro trabajo',
    'Responsabilidad',
    'Respeto al medio ambiente',
    'Honestidad',
];
$new_values = [
    'Excelencia con Propósito' => 'La excelencia es nuestro hábito diario. Desarrollamos soluciones confiables e innovadoras impulsadas por la mejora continua a través del ciclo PEDEM (Planear, Ejecutar, Documentar, Evaluar y Mejorar). Trabajamos con actitud proactiva para perfeccionar nuestros procesos, garantizar la seguridad en cada operación y brindar un servicio eficiente que supere las expectativas de nuestros clientes.',
    'Compromiso con el Progreso Sostenible' => 'Impulsamos la productividad y competitividad de nuestros clientes con una visión a largo plazo. Pensamos y ejecutamos con enfoque en resultados: cada colaborador es un embajador responsable de optimizar recursos, cuidar el negocio y generar valor, promoviendo siempre una gestión sostenible que proteja el medio ambiente y beneficie a la sociedad.',
    'Confiabilidad Inquebrantable' => 'Construimos relaciones duraderas basadas en la ética, la transparencia y la responsabilidad. Nos distingue la pasión y el compromiso absoluto de nuestro equipo, cuya entrega diaria y sentido de pertenencia impulsan a la empresa hacia adelante. Respaldamos las operaciones de nuestros clientes con respuestas oportunas, consolidándonos como un aliado estratégico que transmite tranquilidad y genera experiencias memorables en cada interacción.',
];

foreach ($old_titles as $title) {
    tmd_about_values_test_assert(1 === substr_count($page_content, '<h3>' . $title . '</h3>'), "El fixture debe contener el valor anterior {$title} una vez.");
}

$before_error = '';
$before_grid  = tmd_about_values_find_grid($page_content, $before_error);
tmd_about_values_test_assert(null !== $before_grid, 'La grilla original debe localizarse.');

$result = tmd_transform_about_values($page_content);
tmd_about_values_test_assert([] === $result['errors'], 'La transformación base no debe producir errores.');
tmd_about_values_test_assert(['corporate-values'] === $result['changes'], 'Debe registrarse un único cambio de valores corporativos.');

foreach ($old_titles as $title) {
    tmd_about_values_test_assert(0 === substr_count($result['content'], '<h3>' . $title . '</h3>'), "El valor anterior {$title} debe desaparecer.");
}

foreach ($new_values as $title => $description) {
    tmd_about_values_test_assert(1 === substr_count($result['content'], '<h3>' . $title . '</h3>'), "El nuevo valor {$title} debe aparecer una vez.");
    tmd_about_values_test_assert(1 === substr_count($result['content'], '<p>' . $description . '</p>'), "La descripción de {$title} debe aparecer una vez.");
}

tmd_about_values_test_assert(3 === substr_count(tmd_about_values_target_grid(), '<article class="tmd-about__value">'), 'La grilla final debe contener exactamente tres valores.');

$after_error = '';
$after_grid  = tmd_about_values_find_grid($result['content'], $after_error);
tmd_about_values_test_assert(null !== $after_grid, 'La grilla transformada debe localizarse.');

tmd_about_values_test_assert(
    substr($page_content, 0, $before_grid['offset']) === substr($result['content'], 0, $after_grid['offset']),
    'El contenido anterior a la grilla debe permanecer idéntico.'
);

tmd_about_values_test_assert(
    substr($page_content, $before_grid['offset'] + strlen($before_grid['full'])) === substr($result['content'], $after_grid['offset'] + strlen($after_grid['full'])),
    'El contenido posterior a la grilla debe permanecer idéntico.'
);

$idempotent = tmd_transform_about_values($result['content']);
tmd_about_values_test_assert([] === $idempotent['errors'], 'La segunda transformación no debe fallar.');
tmd_about_values_test_assert([] === $idempotent['changes'], 'La segunda transformación no debe producir cambios.');
tmd_about_values_test_assert($result['content'] === $idempotent['content'], 'La segunda transformación debe ser idéntica.');

$contradictory = str_replace('<h3>Responsabilidad</h3>', '<h3>Valor inesperado</h3>', $page_content, $replacement_count);
tmd_about_values_test_assert(1 === $replacement_count, 'El fixture contradictorio debe construirse una vez.');

$contradiction = tmd_transform_about_values($contradictory);
tmd_about_values_test_assert([] !== $contradiction['errors'], 'Una grilla contradictoria debe detener la transformación.');
tmd_about_values_test_assert($contradictory === $contradiction['content'], 'La contradicción no debe causar cambios parciales.');

fwrite(STDOUT, "OK: valores corporativos, descripciones, idempotencia y precondiciones.\n");
