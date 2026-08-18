<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('HOUR_IN_SECONDS', 3600);

class WP_Error {
    private $code;
    private $message;

    public function __construct($code, $message) {
        $this->code    = $code;
        $this->message = $message;
    }

    public function get_error_code() {
        return $this->code;
    }

    public function get_error_message() {
        return $this->message;
    }
}

function is_wp_error($value) {
    return $value instanceof WP_Error;
}

function add_action() {}
function add_filter() {}
function wp_unslash($value) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_file_name($value) { return preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $value); }
function sanitize_email($value) { return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function home_url($path = '/') { return 'https://tecnimontacargas.com' . $path; }
function trailingslashit($path) { return rtrim($path, '/\\') . '/'; }
function get_temp_dir() { return dirname(__DIR__) . '/.codex-tmp/'; }

function tmd_job_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-job-application.php';
tmd_job_test_assert(class_exists('ZipArchive'), 'ZipArchive es requisito del servidor para aceptar DOCX.');

$valid_post = [
    'form_type' => 'trabaja_con_nosotros',
    'name'      => 'Ana Pérez',
    'email'     => 'ana@example.com',
    'phone'     => '300 123 4567',
    'city'      => 'Bogotá',
    'service'   => 'Técnico en mantenimiento',
    'message'   => 'Tengo experiencia en mantenimiento.',
    'terms'     => 'Acepto',
];

$recipient = tmd_job_application_recipient_for('trabaja_con_nosotros');
tmd_job_test_assert('rh@tmdual.com' === $recipient, 'Postulación debe enrutarse únicamente a RH.');
tmd_job_test_assert('' === tmd_job_application_recipient_for('pqr'), 'Un tipo no permitido no debe tener destinatario.');

$fields = tmd_job_application_validate_fields($valid_post);
tmd_job_test_assert(! is_wp_error($fields), 'Los campos válidos deben aceptarse.');
tmd_job_test_assert('ana@example.com' === $fields['email'], 'El email debe sanearse y conservarse.');

foreach (['name', 'email', 'service', 'message', 'terms'] as $required) {
    $invalid = $valid_post;
    $invalid[$required] = '';
    tmd_job_test_assert(
        is_wp_error(tmd_job_application_validate_fields($invalid)),
        "El campo {$required} debe ser obligatorio."
    );
}

$invalid_email = $valid_post;
$invalid_email['email'] = "ana@example.com\r\nBcc: attacker@example.com";
tmd_job_test_assert(is_wp_error(tmd_job_application_validate_fields($invalid_email)), 'Debe rechazarse email inválido o con inyección.');

$base_file = [
    'name'     => 'hoja-de-vida.pdf',
    'tmp_name' => '/tmp/tmd-cv-test',
    'size'     => 1024,
    'error'    => UPLOAD_ERR_OK,
];
$is_uploaded = static function () { return true; };

$file_cases = [
    'pdf' => ['application/pdf', true],
    'doc' => ['application/x-ole-storage', true],
    'docx' => ['application/zip', true],
];

foreach ($file_cases as $extension => [$mime, $structure_valid]) {
    $file = $base_file;
    $file['name'] = "cv.{$extension}";
    $result = tmd_job_application_validate_cv(
        $file,
        $is_uploaded,
        static function () use ($mime) { return $mime; },
        static function () use ($structure_valid) { return $structure_valid; }
    );
    tmd_job_test_assert(! is_wp_error($result), "El archivo {$extension} válido debe aceptarse.");
}

$too_large = $base_file;
$too_large['size'] = (2 * 1024 * 1024) + 1;
tmd_job_test_assert(is_wp_error(tmd_job_application_validate_cv($too_large, $is_uploaded)), 'Debe rechazarse un CV mayor de 2 MB.');

$executable = $base_file;
$executable['name'] = 'malware.exe';
tmd_job_test_assert(is_wp_error(tmd_job_application_validate_cv($executable, $is_uploaded)), 'Debe rechazarse una extensión no permitida.');

$renamed = $base_file;
tmd_job_test_assert(
    is_wp_error(tmd_job_application_validate_cv(
        $renamed,
        $is_uploaded,
        static function () { return 'image/jpeg'; },
        static function () { return false; }
    )),
    'Debe rechazarse un archivo renombrado con MIME contradictorio.'
);

$not_uploaded = tmd_job_application_validate_cv(
    $base_file,
    static function () { return false; }
);
tmd_job_test_assert(is_wp_error($not_uploaded), 'Debe rechazarse una ruta que no proviene de upload HTTP.');

$real_pdf_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-real-pdf-');
file_put_contents($real_pdf_path, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");
$real_pdf = [
    'name' => 'cv-real.pdf',
    'tmp_name' => $real_pdf_path,
    'size' => filesize($real_pdf_path),
    'error' => UPLOAD_ERR_OK,
];
$real_pdf_result = tmd_job_application_validate_cv($real_pdf, static function () { return true; });
tmd_job_test_assert(! is_wp_error($real_pdf_result), 'La validación real Fileinfo/firma debe aceptar un PDF válido.');
unlink($real_pdf_path);

$doc_path = dirname(__DIR__) . '/tests/fixtures/job-application-valid.doc';
tmd_job_test_assert(is_file($doc_path), 'Debe existir el fixture DOC real generado por textutil.');
tmd_job_test_assert(tmd_job_application_valid_structure($doc_path, 'doc'), 'El directorio OLE de un DOC real debe aceptarse.');
$doc_file = ['name' => 'cv.doc', 'tmp_name' => $doc_path, 'size' => filesize($doc_path), 'error' => UPLOAD_ERR_OK];
$doc_result = tmd_job_application_validate_cv(
    $doc_file,
    static function () { return true; }
);
tmd_job_test_assert(! is_wp_error($doc_result), 'Un DOC real debe aceptarse con Fileinfo y validación estructural sin mocks.');

$doc_vba_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-real-doc-vba-');
$doc_vba_content = file_get_contents($doc_path);
$word_entry = strpos($doc_vba_content, "W\0o\0r\0d\0D\0o\0c\0u\0m\0e\0n\0t\0");
tmd_job_test_assert(false !== $word_entry, 'El fixture DOC debe contener la entrada WordDocument.');
$candidate_entry = (int) (floor($word_entry / 128) * 128) + 128;
while ($candidate_entry + 128 <= strlen($doc_vba_content) && 0 !== ord($doc_vba_content[$candidate_entry + 66])) {
    $candidate_entry += 128;
}
tmd_job_test_assert($candidate_entry + 128 <= strlen($doc_vba_content), 'El fixture DOC debe tener una entrada libre para el caso VBA.');
$vba_entry = str_repeat("\0", 128);
$vba_entry = substr_replace($vba_entry, "V\0B\0A\0\0\0", 0, 8);
$vba_entry = substr_replace($vba_entry, pack('v', 8), 64, 2);
$vba_entry[66] = chr(2);
$doc_vba_content = substr_replace($doc_vba_content, $vba_entry, $candidate_entry, 128);
file_put_contents($doc_vba_path, $doc_vba_content);
tmd_job_test_assert(! tmd_job_application_valid_structure($doc_vba_path, 'doc'), 'Un DOC OLE válido con una entrada VBA debe rechazarse.');
$doc_vba_file = ['name' => 'cv.doc', 'tmp_name' => $doc_vba_path, 'size' => filesize($doc_vba_path), 'error' => UPLOAD_ERR_OK];
tmd_job_test_assert(
    is_wp_error(tmd_job_application_validate_cv($doc_vba_file, static function () { return true; })),
    'La validación completa debe rechazar un DOC con VBA.'
);
unlink($doc_vba_path);

$doc_embedded_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-real-doc-object-');
$doc_embedded_content = file_get_contents($doc_path);
$candidate_entry = $word_entry + 128;
while ($candidate_entry + 128 <= strlen($doc_embedded_content) && 0 !== ord($doc_embedded_content[$candidate_entry + 66])) {
    $candidate_entry += 128;
}
tmd_job_test_assert($candidate_entry + 128 <= strlen($doc_embedded_content), 'El fixture DOC debe tener una entrada libre para el caso ObjectPool.');
$embedded_name = mb_convert_encoding("ObjectPool\0", 'UTF-16LE', 'UTF-8');
$embedded_entry = str_pad($embedded_name, 64, "\0")
    . pack('v', strlen($embedded_name))
    . chr(1)
    . str_repeat("\0", 61);
$doc_embedded_content = substr_replace($doc_embedded_content, $embedded_entry, $candidate_entry, 128);
file_put_contents($doc_embedded_path, $doc_embedded_content);
tmd_job_test_assert(! tmd_job_application_valid_structure($doc_embedded_path, 'doc'), 'Un DOC OLE con ObjectPool debe rechazarse.');
unlink($doc_embedded_path);

if (class_exists('ZipArchive')) {
    $content_types = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>';
    $docx_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-real-docx-');
    $zip = new ZipArchive();
    tmd_job_test_assert(true === $zip->open($docx_path, ZipArchive::OVERWRITE), 'Debe poder crearse el fixture DOCX.');
    $zip->addFromString('[Content_Types].xml', $content_types);
    $zip->addFromString('word/document.xml', '<w:document xmlns:w="urn:test"><w:body/></w:document>');
    $zip->close();
    tmd_job_test_assert(tmd_job_application_valid_structure($docx_path, 'docx'), 'Un DOCX con paquete y contenido Word válidos debe aceptarse.');
    $docx_file = ['name' => 'cv.docx', 'tmp_name' => $docx_path, 'size' => filesize($docx_path), 'error' => UPLOAD_ERR_OK];
    $docx_result = tmd_job_application_validate_cv(
        $docx_file,
        static function () { return true; },
        static function () { return 'application/zip'; }
    );
    tmd_job_test_assert(! is_wp_error($docx_result), 'Un DOCX con MIME y estructura permitidos debe aceptarse.');
    unlink($docx_path);

    $macro_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-macro-docx-');
    $zip = new ZipArchive();
    $zip->open($macro_path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', str_replace('wordprocessingml.document.main+xml', 'wordprocessingml.document.macroEnabled.main+xml', $content_types));
    $zip->addFromString('word/document.xml', '<w:document/>');
    $zip->addFromString('word/vbaProject.bin', 'macro');
    $zip->close();
    tmd_job_test_assert(! tmd_job_application_valid_structure($macro_path, 'docx'), 'Un DOCX habilitado para macros debe rechazarse.');
    unlink($macro_path);

    $embedded_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-embedded-docx-');
    $zip = new ZipArchive();
    $zip->open($embedded_path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', $content_types);
    $zip->addFromString('word/document.xml', '<w:document/>');
    $zip->addFromString('word/embeddings/object.bin', 'object');
    $zip->close();
    tmd_job_test_assert(! tmd_job_application_valid_structure($embedded_path, 'docx'), 'Un DOCX con objeto incrustado debe rechazarse.');
    unlink($embedded_path);

    $encoded_relationship_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-encoded-rel-docx-');
    $zip = new ZipArchive();
    $zip->open($encoded_relationship_path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', $content_types);
    $zip->addFromString('word/document.xml', '<w:document/>');
    $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0"?><Relationships><Relationship Type="http://schemas.example/ole&#79;bject" Target="embeddings/object.bin"/></Relationships>');
    $zip->close();
    tmd_job_test_assert(! tmd_job_application_valid_structure($encoded_relationship_path, 'docx'), 'Una relación activa codificada como entidad XML debe rechazarse tras decodificarla.');
    unlink($encoded_relationship_path);

    $active_pdf_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-active-pdf-');
    file_put_contents($active_pdf_path, "%PDF-1.4\n1 0 obj << /OpenAction 2 0 R >> endobj\n%%EOF");
    tmd_job_test_assert(! tmd_job_application_valid_structure($active_pdf_path, 'pdf'), 'Un PDF con acción activa debe rechazarse.');
    file_put_contents($active_pdf_path, "%PDF-1.4\n1 0 obj << /Java#53cript 2 0 R >> endobj\n%%EOF");
    tmd_job_test_assert(! tmd_job_application_valid_structure($active_pdf_path, 'pdf'), 'Un nombre PDF activo codificado con escape hexadecimal debe rechazarse.');
    file_put_contents($active_pdf_path, "%PDF-1.5\n1 0 obj << /Type /ObjStm >> stream\nopaque\nendstream\nendobj\n%%EOF");
    tmd_job_test_assert(! tmd_job_application_valid_structure($active_pdf_path, 'pdf'), 'Un PDF con object stream no inspeccionable debe rechazarse.');
    unlink($active_pdf_path);

    $expanded_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-expanded-docx-');
    $zip = new ZipArchive();
    $zip->open($expanded_path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', $content_types . str_repeat(' ', 70 * 1024));
    $zip->addFromString('word/document.xml', '<w:document/>');
    $zip->close();
    tmd_job_test_assert(! tmd_job_application_valid_structure($expanded_path, 'docx'), 'Un DOCX con manifiesto expandido fuera del límite debe rechazarse antes de leerlo.');
    unlink($expanded_path);
}

$real_oversize_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-real-oversize-');
file_put_contents($real_oversize_path, "%PDF-1.4\n" . str_repeat('A', (2 * 1024 * 1024) + 10) . "\n%%EOF");
$real_oversize = [
    'name' => 'cv.pdf',
    'tmp_name' => $real_oversize_path,
    'size' => 100,
    'error' => UPLOAD_ERR_OK,
];
tmd_job_test_assert(
    is_wp_error(tmd_job_application_validate_cv($real_oversize, static function () { return true; })),
    'El tamaño real del temporal debe impedir un archivo superior a 2 MB aunque el tamaño declarado sea menor.'
);
unlink($real_oversize_path);

$mime_only = tmd_job_application_validate_cv(
    $base_file,
    $is_uploaded,
    static function () { return 'application/pdf'; },
    static function () { return false; }
);
tmd_job_test_assert(is_wp_error($mime_only), 'Un MIME permitido no debe reemplazar la validación estructural.');
$structure_only = tmd_job_application_validate_cv(
    $base_file,
    $is_uploaded,
    static function () { return 'image/jpeg'; },
    static function () { return true; }
);
tmd_job_test_assert(is_wp_error($structure_only), 'Una estructura permitida no debe reemplazar la validación MIME.');

$mail = tmd_job_application_build_mail($fields, '/tmp/hoja-de-vida.pdf');
tmd_job_test_assert('rh@tmdual.com' === $mail['to'], 'El mail debe usar el destinatario permitido del servidor.');
tmd_job_test_assert('Postulación general | Técnico en mantenimiento' === $mail['subject'], 'El asunto debe identificar la postulación y el área de interés.');
tmd_job_test_assert(['/tmp/hoja-de-vida.pdf'] === $mail['attachments'], 'El mail debe adjuntar únicamente el CV validado.');
tmd_job_test_assert(in_array('Reply-To: ana@example.com', $mail['headers'], true), 'Reply-To debe usar el email validado del candidato.');
foreach (['Ana Pérez', 'ana@example.com', '300 123 4567', 'Bogotá', 'Técnico en mantenimiento', 'Tengo experiencia en mantenimiento.'] as $expected) {
    tmd_job_test_assert(false !== strpos($mail['message'], esc_html($expected)), "El correo debe incluir {$expected}.");
}

fwrite(STDOUT, "OK: destinatario, campos, archivos y construcción del correo.\n");
