<?php

require_once dirname(__DIR__) . '/scripts/update-job-application-form.php';

function tmd_job_form_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$content = <<<'HTML'
<form class="tmd-jobs-form" data-tmd-ajax-form="" enctype="multipart/form-data">
<input type="hidden" name="form_type" value="trabaja_con_nosotros">
<div class="tmd-jobs-upload">
                Adjunta tu hoja de vida en PDF desde el correo o WhatsApp indicado.
              </div>
<button class="tmd-jobs-btn tmd-jobs-btn-blue" type="submit" style="width:100%;">Enviar Postulación</button>
<div class="tmd-form-status" data-tmd-form-status="" style="margin-top:12px;"></div>
HTML;

$pages = json_decode(file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json'), true);
$snapshot_content = '';

foreach ($pages as $page) {
    if (273 === (int) ($page['ID'] ?? 0)) {
        $snapshot_content = (string) ($page['post_content'] ?? '');
        break;
    }
}

tmd_job_form_test_assert('' !== $snapshot_content, 'Debe existir el contenido de la página 273.');

$result = tmd_transform_job_application_form($content);
tmd_job_form_test_assert([] === $result['errors'], 'La transformación base no debe fallar.');
tmd_job_form_test_assert(5 === count($result['changes']), 'Deben aplicarse cinco cambios HTML focalizados.');

$expected_fragments = [
    'data-tmd-job-application=""',
    'name="cv"',
    'type="file"',
    'accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"',
    'Máximo 2 MB',
    'name="website"',
    'name="terms"',
    'href="/nosotros/legal/politica-de-privacidad/"',
    'role="status"',
    'aria-live="polite"',
];

foreach ($expected_fragments as $fragment) {
    tmd_job_form_test_assert(false !== strpos($result['content'], $fragment), "Debe existir {$fragment}.");
}

tmd_job_form_test_assert(
    false === strpos($result['content'], 'Adjunta tu hoja de vida en PDF desde el correo o WhatsApp indicado.'),
    'Debe eliminarse la instrucción anterior.'
);

$again = tmd_transform_job_application_form($result['content']);
tmd_job_form_test_assert([] === $again['errors'], 'La segunda transformación no debe fallar.');
tmd_job_form_test_assert([] === $again['changes'], 'La segunda transformación debe ser idempotente.');
tmd_job_form_test_assert($result['content'] === $again['content'], 'El contenido idempotente debe ser idéntico.');

$snapshot_result = tmd_transform_job_application_form($snapshot_content);
tmd_job_form_test_assert([] === $snapshot_result['errors'], 'El snapshot migrado no debe fallar.');
tmd_job_form_test_assert([] === $snapshot_result['changes'], 'El snapshot actual no debe requerir otra migración.');
tmd_job_form_test_assert($snapshot_content === $snapshot_result['content'], 'El snapshot actual debe permanecer idéntico.');

$missing = str_replace('Adjunta tu hoja de vida en PDF desde el correo o WhatsApp indicado.', 'Texto inesperado.', $content);
$blocked = tmd_transform_job_application_form($missing);
tmd_job_form_test_assert([] !== $blocked['errors'], 'Una precondición ausente debe detener la transformación.');
tmd_job_form_test_assert($missing === $blocked['content'], 'Un error debe devolver el contenido original.');

$replacements = tmd_job_application_form_replacements();
$mixed = $result['content'] . "\n" . $replacements['consent']['old'];
$mixed_result = tmd_transform_job_application_form($mixed);
tmd_job_form_test_assert([] !== $mixed_result['errors'], 'Un estado mixto de HTML anterior y final debe detener la transformación.');
tmd_job_form_test_assert($mixed === $mixed_result['content'], 'Un estado mixto no debe producir una transformación parcial.');

fwrite(STDOUT, "OK: campo CV, consentimiento, accesibilidad, precondiciones e idempotencia.\n");
