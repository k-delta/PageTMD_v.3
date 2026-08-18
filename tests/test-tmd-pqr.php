<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('HOUR_IN_SECONDS', 3600);

class WP_Error {
    private $code;
    private $message;

    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }

    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
}

$mock_transients = [];

function is_wp_error($value) { return $value instanceof WP_Error; }
function add_action() {}
function wp_unslash($value) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_email($value) { return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function wp_hash($value) { return hash('sha256', $value); }
function get_transient($key) { global $mock_transients; return $mock_transients[$key] ?? false; }
function set_transient($key, $value) { global $mock_transients; $mock_transients[$key] = $value; return true; }
function get_temp_dir() { return dirname(__DIR__) . '/.codex-tmp/'; }
function trailingslashit($path) { return rtrim($path, '/\\') . '/'; }

function tmd_pqr_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-pqr.php';

function tmd_pqr_valid_post() {
    return [
        'form_type'    => 'pqr',
        'request_type' => 'Reclamo',
        'subject'      => 'Demora en servicio',
        'name'         => 'Ana Pérez',
        'email'        => 'ana@example.com',
        'phone'        => '300 123 4567',
        'order_number' => 'PED-123',
        'message'      => 'Solicito revisar el estado del servicio.',
        'terms'        => 'Acepto',
    ];
}

tmd_pqr_test_assert('gerencia@tmdual.com' === tmd_pqr_recipient(), 'PQR debe dirigirse únicamente a gerencia.');

$fields = tmd_pqr_validate_fields(tmd_pqr_valid_post());
tmd_pqr_test_assert(! is_wp_error($fields), 'Campos PQR válidos deben aceptarse.');
tmd_pqr_test_assert('ana@example.com' === $fields['email'], 'Email debe sanearse y conservarse.');

foreach (['Peticion', 'Queja', 'Reclamo', 'Reembolso'] as $request_type) {
    $post = tmd_pqr_valid_post();
    $post['request_type'] = $request_type;
    tmd_pqr_test_assert(! is_wp_error(tmd_pqr_validate_fields($post)), "Tipo {$request_type} debe aceptarse.");
}

$invalid_type = tmd_pqr_valid_post();
$invalid_type['request_type'] = 'Cotizacion';
tmd_pqr_test_assert(is_wp_error(tmd_pqr_validate_fields($invalid_type)), 'Tipo fuera de allowlist debe rechazarse.');

$invalid_form = tmd_pqr_valid_post();
$invalid_form['form_type'] = 'contacto';
tmd_pqr_test_assert(is_wp_error(tmd_pqr_validate_fields($invalid_form)), 'form_type manipulado debe rechazarse.');

foreach (['request_type', 'subject', 'name', 'email', 'phone', 'message', 'terms'] as $required) {
    $post = tmd_pqr_valid_post();
    $post[$required] = '';
    tmd_pqr_test_assert(is_wp_error(tmd_pqr_validate_fields($post)), "Campo {$required} debe ser obligatorio.");
}

$invalid_email = tmd_pqr_valid_post();
$invalid_email['email'] = "ana@example.com\r\nBcc: attacker@example.com";
tmd_pqr_test_assert(is_wp_error(tmd_pqr_validate_fields($invalid_email)), 'Email inválido o con inyección debe rechazarse.');

$too_long = tmd_pqr_valid_post();
$too_long['message'] = str_repeat('x', 5001);
tmd_pqr_test_assert(is_wp_error(tmd_pqr_validate_fields($too_long)), 'Mensaje fuera de límite debe rechazarse.');

$mail = tmd_pqr_build_mail($fields);
tmd_pqr_test_assert('gerencia@tmdual.com' === $mail['to'], 'Correo debe usar destinatario fijo del servidor.');
tmd_pqr_test_assert('PQR | Reclamo | Demora en servicio' === $mail['subject'], 'Asunto debe identificar tipo y asunto PQR.');
tmd_pqr_test_assert(in_array('Reply-To: ana@example.com', $mail['headers'], true), 'Reply-To debe usar email validado.');
foreach ($mail['headers'] as $header) {
    tmd_pqr_test_assert(0 !== stripos($header, 'From:'), 'PQR no debe forzar From; WPO365 lo administra.');
}
foreach (['Reclamo', 'Demora en servicio', 'Ana Pérez', 'ana@example.com', '300 123 4567', 'PED-123', 'Solicito revisar el estado del servicio.'] as $expected) {
    tmd_pqr_test_assert(false !== strpos($mail['message'], esc_html($expected)), "Correo debe incluir {$expected}.");
}

$mock_transients = [];
tmd_pqr_test_assert(! tmd_pqr_rate_limited('ana@example.com', '192.0.2.10'), 'Primer intento no debe limitarse.');
for ($attempt = 0; $attempt < 4; $attempt++) {
    tmd_pqr_rate_limited('ana@example.com', '192.0.2.10');
}
tmd_pqr_test_assert(tmd_pqr_rate_limited('otra@example.com', '192.0.2.10'), 'Límite por IP no debe evadirse cambiando email.');

$mock_transients = [];
$email_key = 'tmd_pqr_email_' . substr(wp_hash('ana@example.com'), 0, 32);
$mock_transients[$email_key] = 5;
tmd_pqr_test_assert(tmd_pqr_rate_limited('ana@example.com', '198.51.100.8'), 'Límite por email no debe evadirse cambiando IP.');

fwrite(STDOUT, "OK: destinatario, validación, correo y rate limit PQR.\n");
