<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('HOUR_IN_SECONDS', 3600);

class WP_Error {
    private $code;
    private $message;
    public function __construct($code, $message) { $this->code = $code; $this->message = $message; }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
}

$mock_page = 0;
$mock_cf7_id = 14;
class WPCF7_ContactForm {
    public static function get_current() { return new self(); }
    public function id() { global $mock_cf7_id; return $mock_cf7_id; }
}
class Tmd_Business_CF7_Result {
    public $invalid = false;
    public function invalidate() { $this->invalid = true; }
}
function add_action() {}
function add_filter() {}
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_unslash($value) { return $value; }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_file_name($value) { return preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $value); }
function sanitize_email($value) { return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_url_raw($value) { return filter_var($value, FILTER_SANITIZE_URL); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_url_raw($value); }
function home_url($path = '/') { return 'https://tecnimontacargas.com' . $path; }
function admin_url($path = '') { return 'https://tecnimontacargas.com/wp-admin/' . $path; }
function wp_create_nonce($action) { return 'nonce-' . $action; }
function is_page($page) { global $mock_page; return is_array($page) ? in_array($mock_page, $page, true) : $mock_page === (int) $page; }
function remove_accents($value) { return strtr($value, ['á' => 'a', 'Á' => 'A']); }

function tmd_business_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-business-proposals.php';

$valid = [
    'form_type' => 'alianza', 'company' => 'Empresa Uno', 'tax_id' => '',
    'name' => 'Ana Pérez', 'role' => '', 'email' => 'ana@example.com',
    'phone' => '3001234567', 'city' => 'Bogotá', 'coverage' => 'Nacional',
    'company_website' => 'https://example.com', 'message' => 'Propuesta comercial',
    'terms' => 'Acepto',
];

tmd_business_assert('gerencia@gmail.com' === tmd_business_proposals_recipient_for('alianza'), 'Alianza debe ir al destinatario fijo.');
tmd_business_assert('gerencia@gmail.com' === tmd_business_proposals_recipient_for('proveedor'), 'Proveedor debe ir al destinatario fijo.');
tmd_business_assert('' === tmd_business_proposals_recipient_for('contacto'), 'Un tipo desconocido no debe tener destinatario.');
tmd_business_assert(2621440 === tmd_business_proposals_max_bytes(), 'El máximo aprobado debe ser 2.5 MB.');
tmd_business_assert(! is_wp_error(tmd_business_proposals_validate_fields($valid)), 'Los campos aprobados deben aceptarse.');

foreach (['company', 'name', 'email', 'phone', 'city', 'coverage', 'message', 'terms'] as $required) {
    $case = $valid;
    $case[$required] = '';
    tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($case)), "{$required} debe ser obligatorio.");
}

$field_limits = ['company' => 160, 'tax_id' => 40, 'name' => 120, 'role' => 120, 'phone' => 40, 'city' => 100, 'coverage' => 240, 'message' => 4000];
foreach ($field_limits as $field => $limit) {
    $at_limit = $valid;
    $at_limit[$field] = str_repeat('x', $limit);
    tmd_business_assert(! is_wp_error(tmd_business_proposals_validate_fields($at_limit)), "{$field} debe aceptar su longitud máxima.");
    $over_limit = $valid;
    $over_limit[$field] = str_repeat('x', $limit + 1);
    tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($over_limit)), "{$field} debe rechazar máximo + 1.");
}
$email_190 = str_repeat('a', 64) . '@' . str_repeat('b', 60) . '.' . str_repeat('c', 60) . '.com';
tmd_business_assert(190 === strlen($email_190), 'El fixture de email debe medir 190 caracteres.');
$email_limit = $valid;
$email_limit['email'] = $email_190;
tmd_business_assert(! is_wp_error(tmd_business_proposals_validate_fields($email_limit)), 'Email debe aceptar 190 caracteres válidos.');
$email_limit['email'] = str_repeat('a', 64) . '@' . str_repeat('b', 60) . '.' . str_repeat('c', 61) . '.com';
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($email_limit)), 'Email debe rechazar 191 caracteres.');
$website_limit = $valid;
$website_limit['company_website'] = 'https://example.com/' . str_repeat('a', 280);
tmd_business_assert(300 === strlen($website_limit['company_website']) && ! is_wp_error(tmd_business_proposals_validate_fields($website_limit)), 'Sitio web debe aceptar 300 caracteres.');
$website_limit['company_website'] .= 'b';
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($website_limit)), 'Sitio web debe rechazar 301 caracteres.');
$invalid_terms = $valid;
$invalid_terms['terms'] = 'Sí';
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($invalid_terms)), 'Una aceptación distinta de Acepto debe rechazarse.');
$ordinary_invalid_email = $valid;
$ordinary_invalid_email['email'] = 'correo-invalido';
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($ordinary_invalid_email)), 'Un email ordinario inválido debe rechazarse.');

$injected = $valid;
$injected['email'] = "ana@example.com\r\nBcc: atacante@example.com";
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_fields($injected)), 'Debe rechazarse inyección de cabeceras.');

$temp = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-business-');
file_put_contents($temp, str_repeat('x', 1024));
$files = [
    'name' => ['brochure.pdf'], 'tmp_name' => [$temp], 'size' => [filesize($temp)],
    'error' => [UPLOAD_ERR_OK], 'type' => ['application/pdf'],
];
$attachments = tmd_business_proposals_validate_attachments(
    $files,
    static fn() => true,
    static fn() => 'application/pdf',
    static fn() => true
);
tmd_business_assert(! is_wp_error($attachments) && 1 === count($attachments), 'Un adjunto válido debe aceptarse.');

$too_many = $files;
foreach (['dos.pdf', 'tres.pdf', 'cuatro.pdf'] as $name) {
    $too_many['name'][] = $name;
    $too_many['tmp_name'][] = $temp;
    $too_many['size'][] = filesize($temp);
    $too_many['error'][] = UPLOAD_ERR_OK;
    $too_many['type'][] = 'application/pdf';
}
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments($too_many)), 'Más de tres adjuntos debe rechazarse.');

$oversize = $files;
$oversize['size'][0] = tmd_business_proposals_max_bytes() + 1;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments($oversize, static fn() => true)), 'Un archivo mayor de 2.5 MB debe rechazarse.');

$wrong_mime = tmd_business_proposals_validate_attachments(
    $files,
    static fn() => true,
    static fn() => 'image/jpeg',
    static fn() => true
);
tmd_business_assert(is_wp_error($wrong_mime), 'Una extensión con MIME contradictorio debe rechazarse.');
unlink($temp);

function tmd_business_files(array $entries): array {
    $files = ['name' => [], 'tmp_name' => [], 'size' => [], 'error' => [], 'type' => []];
    foreach ($entries as [$name, $path, $error]) {
        $files['name'][] = $name;
        $files['tmp_name'][] = $path;
        $files['size'][] = filesize($path);
        $files['error'][] = $error;
        $files['type'][] = '';
    }
    return $files;
}

$fixture_paths = [];
$pdf_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-pdf-');
file_put_contents($pdf_path, "%PDF-1.5\n1 0 obj << /Type /ObjStm >> stream\nnormal\nendstream\nendobj\n%%EOF");
$fixture_paths[] = $pdf_path;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['brochure.pdf', $pdf_path, UPLOAD_ERR_OK]]), static fn() => true)), 'Un PDF con ObjStm no inspeccionable debe rechazarse.');

$active_pdf = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-active-pdf-');
file_put_contents($active_pdf, "%PDF-1.4\n1 0 obj << /OpenAction 2 0 R >> endobj\n%%EOF");
$fixture_paths[] = $active_pdf;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['activo.pdf', $active_pdf, UPLOAD_ERR_OK]]), static fn() => true)), 'Un PDF con acción activa debe rechazarse.');

$xfa_pdf = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-xfa-pdf-');
file_put_contents($xfa_pdf, "%PDF-1.6\n1 0 obj << /AcroForm << /XFA 2 0 R >> >> endobj\n2 0 obj << /Filter /FlateDecode >> stream\ncompressed-xfa-script\nendstream\nendobj\n%%EOF");
$fixture_paths[] = $xfa_pdf;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['xfa.pdf', $xfa_pdf, UPLOAD_ERR_OK]]), static fn() => true)), 'Un PDF con formulario XFA debe rechazarse.');

$content_types = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>';
$docx_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-docx-');
$zip = new ZipArchive();
$zip->open($docx_path, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', $content_types);
$zip->addFromString('word/document.xml', '<w:document xmlns:w="urn:test"><w:body/></w:document>');
$zip->close();
$fixture_paths[] = $docx_path;
tmd_business_assert(! is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['portafolio.docx', $docx_path, UPLOAD_ERR_OK]]), static fn() => true)), 'Un DOCX real sin contenido activo debe aceptarse.');

$external_docx = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-external-docx-');
$zip = new ZipArchive();
$zip->open($external_docx, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', $content_types);
$zip->addFromString('word/document.xml', '<w:document/>');
$zip->addFromString('word/_rels/document.xml.rels', '<Relationships><Relationship Type="image" Target="https://attacker.invalid/pixel.png" TargetMode="External"/></Relationships>');
$zip->close();
$fixture_paths[] = $external_docx;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['externo.docx', $external_docx, UPLOAD_ERR_OK]]), static fn() => true)), 'Un DOCX con relación externa debe rechazarse.');

$image = imagecreatetruecolor(2, 2);
$image_cases = [];
foreach (['jpg' => 'imagejpeg', 'jpeg' => 'imagejpeg', 'png' => 'imagepng', 'webp' => 'imagewebp'] as $extension => $writer) {
    tmd_business_assert(function_exists($writer), "GD debe permitir generar {$extension} para la prueba.");
    $path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-image-');
    $writer($image, $path);
    $fixture_paths[] = $path;
    $image_cases[] = ["imagen.{$extension}", $path, UPLOAD_ERR_OK];
    tmd_business_assert(! is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([["imagen.{$extension}", $path, UPLOAD_ERR_OK]]), static fn() => true)), "Una imagen {$extension} real debe aceptarse.");
}
unset($image);

$polyglot_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-polyglot-');
copy($image_cases[2][1], $polyglot_path);
file_put_contents($polyglot_path, '<?php echo "active"; ?>', FILE_APPEND);
$fixture_paths[] = $polyglot_path;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['poliglot.png', $polyglot_path, UPLOAD_ERR_OK]]), static fn() => true)), 'Una imagen con contenido añadido debe rechazarse.');

$three_valid = tmd_business_proposals_validate_attachments(tmd_business_files(array_slice($image_cases, 0, 3)), static fn() => true);
tmd_business_assert(! is_wp_error($three_valid) && 3 === count($three_valid), 'Tres adjuntos válidos dentro del total deben aceptarse.');

$declared_mismatch = tmd_business_files([['brochure.pdf', $pdf_path, UPLOAD_ERR_OK]]);
$declared_mismatch['size'][0]++;
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments($declared_mismatch, static fn() => true)), 'El tamaño declarado distinto del real debe rechazarse.');
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files([['brochure.pdf', $pdf_path, UPLOAD_ERR_PARTIAL]]), static fn() => true)), 'Un error de subida debe rechazarse.');

$large_paths = [];
foreach ([1, 2] as $index) {
    $path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-large-pdf-');
    $payload = "%PDF-1.4\n" . str_repeat('x', 1400000 - 17) . "\n%%EOF";
    file_put_contents($path, $payload);
    $fixture_paths[] = $path;
    $large_paths[] = ["grande-{$index}.pdf", $path, UPLOAD_ERR_OK];
}
tmd_business_assert(is_wp_error(tmd_business_proposals_validate_attachments(tmd_business_files($large_paths), static fn() => true)), 'La suma de varios adjuntos superior a 2.5 MB debe rechazarse.');

foreach ($fixture_paths as $path) {
    if (is_file($path)) unlink($path);
}

$tag = (object) ['name' => 'service', 'values' => ['Mantenimiento', ' Alianzas ', 'Venta'], 'raw_values' => ['Mantenimiento', 'Alianzas', 'Venta']];
$mock_page = 57;
$tag = tmd_business_proposals_filter_contact_service_tag($tag);
tmd_business_assert(['Mantenimiento', 'Venta'] === $tag->values, 'Contacto debe retirar solo Alianzas.');

$other_tag = (object) ['name' => 'area', 'values' => ['Alianzas'], 'raw_values' => ['Alianzas']];
tmd_business_assert(['Alianzas'] === tmd_business_proposals_filter_contact_service_tag($other_tag)->values, 'Una etiqueta CF7 ajena no debe alterarse.');
tmd_business_assert(tmd_business_proposals_is_alliance_label(' ÁLIANZA '), 'La normalización debe admitir acentos, espacios y mayúsculas.');

$_POST['service'] = ' ALIANZAS ';
$cf7_result = new Tmd_Business_CF7_Result();
tmd_business_proposals_validate_contact_service($cf7_result, $tag);
tmd_business_assert($cf7_result->invalid, 'CF7 debe rechazar Alianzas en servidor para el formulario 14.');
$mock_cf7_id = 99;
$other_result = new Tmd_Business_CF7_Result();
tmd_business_proposals_validate_contact_service($other_result, $tag);
tmd_business_assert(! $other_result->invalid, 'CF7 no debe afectar otros formularios.');

$mock_page = 275;
$content = '<a href="/nosotros/contacto/">Presentar propuesta</a>';
$rendered = tmd_business_proposals_filter_content($content);
tmd_business_assert(false !== strpos($rendered, 'href="#tmd-business-proposal-form"'), 'El CTA debe apuntar al formulario propio.');
tmd_business_assert(false !== strpos($rendered, 'value="tmd_business_alliance"'), 'La página de Alianzas debe fijar su acción de servidor.');

$mock_page = 793;
$provider = tmd_business_proposals_filter_content('<a href="/nosotros/contacto/">Contactar</a>');
tmd_business_assert(false !== strpos($provider, 'value="tmd_business_provider"'), 'La página de Proveedores debe fijar su acción de servidor.');

$mock_page = 12;
tmd_business_assert('contenido' === tmd_business_proposals_filter_content('contenido'), 'Una página ajena no debe alterarse.');

require_once dirname(__DIR__) . '/scripts/update-business-proposals-content.php';
$page_transform = tmd_business_proposals_transform_page_content('<a href="/nosotros/contacto/">CTA</a>');
tmd_business_assert($page_transform['changed'] && 1 === $page_transform['replacements'], 'El procedimiento persistente debe transformar el CTA una vez.');
$page_idempotent = tmd_business_proposals_transform_page_content($page_transform['content']);
tmd_business_assert(! $page_idempotent['changed'], 'La transformación persistente de CTA debe ser idempotente.');
$cf7_cases = [
    '[select* service "Mantenimiento" "Alianzas" "Venta"]' => '[select* service "Mantenimiento" "Venta"]',
    '[select service "Mantenimiento" "Alianzas|alianza" "Venta"]' => '[select service "Mantenimiento" "Venta"]',
    "[select service 'Mantenimiento' 'Alianza|alianza' 'Venta']" => "[select service 'Mantenimiento' 'Venta']",
    '[select service Mantenimiento Alianzas Venta]' => '[select service Mantenimiento Venta]',
    '[select service "Mantenimiento" " ÁLIANZAS |alianza" "Venta"]' => '[select service "Mantenimiento" "Venta"]',
];
foreach ($cf7_cases as $input => $expected) {
    $cf7_transform = tmd_business_proposals_transform_cf7_form($input);
    tmd_business_assert($cf7_transform['changed'] && 1 === $cf7_transform['removed'], 'El procedimiento persistente debe retirar una variante de Alianzas.');
    tmd_business_assert($expected === $cf7_transform['form'], 'La transformación CF7 debe conservar exactamente las demás opciones y su orden.');
    tmd_business_assert(! tmd_business_proposals_transform_cf7_form($cf7_transform['form'])['changed'], 'La transformación CF7 debe ser idempotente.');
}

echo "OK: propuestas empresariales\n";
