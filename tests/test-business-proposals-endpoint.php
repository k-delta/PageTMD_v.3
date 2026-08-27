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

class Tmd_Business_Response extends RuntimeException {
    public $success;
    public $data;
    public $status;
    public function __construct($success, $data, $status) {
        parent::__construct((string) ($data['message'] ?? ''));
        $this->success = $success; $this->data = $data; $this->status = $status;
    }
}

$mock_actions = [];
$mock_filters = [];
$mock_nonce_valid = true;
$mock_mail_result = true;
$mock_mail_calls = [];
$mock_transients = [];
$mock_antispam = false;
$mock_move_calls = 0;
$mock_fail_move_at = 0;
$mock_move_destinations = [];

function add_action($hook, $callback, $priority = 10) { global $mock_actions; $mock_actions[$hook][$priority][] = $callback; }
function add_filter($hook, $callback, $priority = 10) { global $mock_filters; $mock_filters[$hook][$priority][] = $callback; }
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
function wp_hash($value) { return hash('sha256', $value); }
function check_ajax_referer() { global $mock_nonce_valid; return $mock_nonce_valid; }
function wp_send_json_success($data, $status = 200) { throw new Tmd_Business_Response(true, $data, $status); }
function wp_send_json_error($data, $status = 200) { throw new Tmd_Business_Response(false, $data, $status); }
function get_transient($key) { global $mock_transients; return $mock_transients[$key] ?? false; }
function set_transient($key, $value) { global $mock_transients; $mock_transients[$key] = $value; return true; }
function get_temp_dir() { return dirname(__DIR__) . '/.codex-tmp/'; }
function trailingslashit($path) { return rtrim($path, '/\\') . '/'; }
function wp_unique_filename($directory, $filename) { return uniqid('business-', true) . '-' . $filename; }
function wp_delete_file($path) { if (is_file($path)) unlink($path); }
function tmd_business_proposals_is_uploaded_file($path) { return is_file($path); }
function tmd_business_proposals_move_uploaded_file($source, $destination) {
    global $mock_move_calls, $mock_fail_move_at, $mock_move_destinations;
    $mock_move_calls++;
    $mock_move_destinations[] = $destination;
    if ($mock_fail_move_at > 0 && $mock_move_calls === $mock_fail_move_at) return false;
    return rename($source, $destination);
}
function tmd_form_antispam_should_block() { global $mock_antispam; return $mock_antispam; }
function wp_mail($to, $subject, $message, $headers, $attachments) {
    global $mock_mail_calls, $mock_mail_result;
    $mock_mail_calls[] = compact('to', 'subject', 'message', 'headers', 'attachments');
    if ($mock_mail_result instanceof Throwable) throw $mock_mail_result;
    return $mock_mail_result;
}
function is_page() { return false; }

function tmd_business_endpoint_assert($condition, $message) {
    if (! $condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-business-proposals.php';

function tmd_business_valid_post($type = 'alianza') {
    return [
        'form_type' => $type, 'company' => 'Empresa Uno', 'tax_id' => 'NIT-900123',
        'name' => 'Ana Pérez', 'role' => 'Directora comercial', 'email' => 'ana@example.com',
        'phone' => '3001234567', 'city' => 'Bogotá', 'coverage' => 'Cobertura nacional',
        'company_website' => 'https://empresa.example', 'message' => 'Propuesta comercial única',
        'terms' => 'Acepto', 'website' => '',
    ];
}

function tmd_business_run($post, $file_mode = 'valid', $endpoint_type = '', $file_count = 1) {
    static $request_ip = 20;
    $_POST = $post;
    $_SERVER['REMOTE_ADDR'] = '192.0.2.' . $request_ip++;
    if ('missing' === $file_mode) {
        $_FILES = [];
    } else {
        $paths = [];
        $_FILES = ['attachments' => ['name' => [], 'tmp_name' => [], 'size' => [], 'error' => [], 'type' => []]];
        for ($index = 1; $index <= $file_count; $index++) {
            $path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'business-upload-');
            file_put_contents($path, 'invalid' === $file_mode ? 'not-a-pdf' : "%PDF-1.4\n%%EOF");
            $paths[] = $path;
            $_FILES['attachments']['name'][] = "brochure-{$index}.pdf";
            $_FILES['attachments']['tmp_name'][] = $path;
            $_FILES['attachments']['size'][] = filesize($path);
            $_FILES['attachments']['error'][] = UPLOAD_ERR_OK;
            $_FILES['attachments']['type'][] = 'application/pdf';
        }
    }
    try {
        $fixed_type = '' !== $endpoint_type ? $endpoint_type : (string) ($post['form_type'] ?? 'alianza');
        tmd_business_proposals_ajax($fixed_type);
    } catch (Tmd_Business_Response $response) {
        foreach ($paths ?? [] as $path) if (is_file($path)) unlink($path);
        return $response;
    }
    throw new RuntimeException('El endpoint debe terminar con JSON.');
}

tmd_business_endpoint_assert(isset($mock_actions['wp_ajax_tmd_business_alliance']), 'Debe registrar endpoint autenticado de Alianzas.');
tmd_business_endpoint_assert(isset($mock_actions['wp_ajax_nopriv_tmd_business_alliance']), 'Debe registrar endpoint público de Alianzas.');
tmd_business_endpoint_assert(isset($mock_actions['wp_ajax_tmd_business_provider']), 'Debe registrar endpoint autenticado de Proveedores.');
tmd_business_endpoint_assert(isset($mock_actions['wp_ajax_nopriv_tmd_business_provider']), 'Debe registrar endpoint público de Proveedores.');

$rate_lock = fopen(trailingslashit(get_temp_dir()) . 'tmd-business-proposals-rate.lock', 'c');
tmd_business_endpoint_assert(is_resource($rate_lock) && flock($rate_lock, LOCK_EX | LOCK_NB), 'La prueba debe adquirir el candado de rate limit.');
tmd_business_endpoint_assert(tmd_business_proposals_rate_limited('192.0.2.99'), 'Un candado ocupado debe fallar cerrado sin esperar.');
flock($rate_lock, LOCK_UN);
fclose($rate_lock);

$mock_transients = [];
for ($attempt = 1; $attempt <= 5; $attempt++) {
    tmd_business_endpoint_assert(! tmd_business_proposals_rate_limited('192.0.2.77'), "El intento {$attempt} por IP debe permitirse.");
}
tmd_business_endpoint_assert(tmd_business_proposals_rate_limited('192.0.2.77'), 'El sexto intento por la misma IP debe bloquearse.');
tmd_business_endpoint_assert(1 === count($mock_transients) && 5 === (int) reset($mock_transients), 'El rate limit solo debe conservar el contador por IP y no incrementar el intento bloqueado.');
$mock_transients = [];

$response = tmd_business_run(tmd_business_valid_post());
tmd_business_endpoint_assert($response->success, 'Una alianza válida debe procesarse.');
tmd_business_endpoint_assert(1 === count($mock_mail_calls), 'Una solicitud válida debe invocar correo una vez.');
tmd_business_endpoint_assert('gerencia@gmail.com' === $mock_mail_calls[0]['to'], 'El destinatario debe ser fijo.');
tmd_business_endpoint_assert(false !== strpos($mock_mail_calls[0]['subject'], 'Propuesta de alianza'), 'El asunto debe identificar Alianzas.');
tmd_business_endpoint_assert(in_array('Reply-To: ana@example.com', $mock_mail_calls[0]['headers'], true), 'El correo debe fijar Reply-To validado.');
tmd_business_endpoint_assert(in_array('Content-Type: text/html; charset=UTF-8', $mock_mail_calls[0]['headers'], true), 'El correo debe declarar HTML UTF-8.');
foreach (['Empresa Uno', 'NIT-900123', 'Ana Pérez', 'Directora comercial', 'ana@example.com', '3001234567', 'Bogotá', 'Cobertura nacional', 'https://empresa.example', 'Propuesta comercial única'] as $expected_value) {
    tmd_business_endpoint_assert(false !== strpos($mock_mail_calls[0]['message'], $expected_value), "El correo de Alianzas debe contener {$expected_value}.");
}
tmd_business_endpoint_assert(! is_file($mock_mail_calls[0]['attachments'][0]), 'El temporal debe borrarse después del correo.');

$before = count($mock_mail_calls);
$response = tmd_business_run(tmd_business_valid_post('alianza'), 'valid', 'contacto');
tmd_business_endpoint_assert(! $response->success && 403 === $response->status, 'Un tipo manipulado debe rechazarse.');
tmd_business_endpoint_assert($before === count($mock_mail_calls), 'Un tipo manipulado no debe enviar correo.');

$response = tmd_business_run(tmd_business_valid_post(), 'missing');
tmd_business_endpoint_assert(! $response->success && 400 === $response->status, 'Los adjuntos son obligatorios.');

$mock_antispam = true;
$response = tmd_business_run(tmd_business_valid_post(), 'missing');
tmd_business_endpoint_assert($response->success, 'El bloqueo antispam debe responder genéricamente.');
tmd_business_endpoint_assert($before === count($mock_mail_calls), 'El bloqueo antispam no debe enviar correo.');
$mock_antispam = false;

$mock_nonce_valid = false;
$response = tmd_business_run(tmd_business_valid_post(), 'missing');
tmd_business_endpoint_assert(! $response->success && 403 === $response->status, 'Nonce inválido debe rechazarse.');
$mock_nonce_valid = true;

$response = tmd_business_run(tmd_business_valid_post('alianza'), 'valid', 'proveedor', 3);
$last = $mock_mail_calls[count($mock_mail_calls) - 1];
tmd_business_endpoint_assert($response->success && 3 === count($last['attachments']), 'Proveedor debe aceptar y enviar tres adjuntos válidos.');
tmd_business_endpoint_assert(false !== strpos($last['subject'], 'Solicitud de proveedor'), 'El asunto debe identificar Proveedores.');
tmd_business_endpoint_assert(in_array('Reply-To: ana@example.com', $last['headers'], true) && in_array('Content-Type: text/html; charset=UTF-8', $last['headers'], true), 'Proveedor debe conservar ambos headers.');
foreach (['Empresa Uno', 'NIT-900123', 'Ana Pérez', 'Directora comercial', 'ana@example.com', '3001234567', 'Bogotá', 'Cobertura nacional', 'https://empresa.example', 'Propuesta comercial única'] as $expected_value) {
    tmd_business_endpoint_assert(false !== strpos($last['message'], $expected_value), "El correo de Proveedores debe contener {$expected_value}.");
}
foreach ($last['attachments'] as $path) tmd_business_endpoint_assert(! is_file($path), 'Todos los temporales de Proveedores deben borrarse.');

$mock_mail_result = false;
$response = tmd_business_run(tmd_business_valid_post('proveedor'));
tmd_business_endpoint_assert(! $response->success && 502 === $response->status, 'Fallo de correo debe ser recuperable.');
$last = $mock_mail_calls[count($mock_mail_calls) - 1];
tmd_business_endpoint_assert(false !== strpos($last['subject'], 'Solicitud de proveedor'), 'El asunto debe identificar Proveedores.');
tmd_business_endpoint_assert(! is_file($last['attachments'][0]), 'El temporal debe borrarse si falla el correo.');

$mock_mail_result = new RuntimeException('Fallo simulado');
$response = tmd_business_run(tmd_business_valid_post('alianza'));
tmd_business_endpoint_assert(! $response->success && 502 === $response->status, 'Una excepción de correo debe ser recuperable.');
$last = $mock_mail_calls[count($mock_mail_calls) - 1];
tmd_business_endpoint_assert(! is_file($last['attachments'][0]), 'El temporal debe borrarse si el correo lanza una excepción.');

$mock_mail_result = true;
$mock_move_calls = 0;
$mock_fail_move_at = 2;
$mock_move_destinations = [];
$mail_before_staging = count($mock_mail_calls);
$response = tmd_business_run(tmd_business_valid_post('alianza'), 'valid', 'alianza', 2);
tmd_business_endpoint_assert(! $response->success && 500 === $response->status, 'Un fallo parcial de staging debe responder error.');
tmd_business_endpoint_assert($mail_before_staging === count($mock_mail_calls), 'Un fallo de staging no debe invocar correo.');
foreach ($mock_move_destinations as $path) tmd_business_endpoint_assert(! is_file($path), 'El staging parcial no debe dejar temporales.');
$mock_fail_move_at = 0;

echo "OK: endpoint de propuestas empresariales\n";
