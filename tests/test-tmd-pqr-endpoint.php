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

class Tmd_Pqr_Json_Response extends RuntimeException {
    public $success;
    public $data;
    public $status;

    public function __construct($success, $data, $status) {
        parent::__construct((string) ($data['message'] ?? ''));
        $this->success = $success;
        $this->data = $data;
        $this->status = $status;
    }
}

$mock_actions = [];
$mock_nonce_valid = true;
$mock_mail_result = true;
$mock_mail_calls = [];
$mock_transients = [];
$mock_is_page = false;
$mock_scripts = [];
$mock_localized = [];

function add_action($hook, $callback, $priority = 10) { global $mock_actions; $mock_actions[$hook][$priority][] = $callback; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_unslash($value) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_email($value) { return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function wp_hash($value) { return hash('sha256', $value); }
function check_ajax_referer() { global $mock_nonce_valid; return $mock_nonce_valid; }
function get_transient($key) { global $mock_transients; return $mock_transients[$key] ?? false; }
function set_transient($key, $value) { global $mock_transients; $mock_transients[$key] = $value; return true; }
function get_temp_dir() { return dirname(__DIR__) . '/.codex-tmp/'; }
function trailingslashit($path) { return rtrim($path, '/\\') . '/'; }
function wp_send_json_success($data, $status = 200) { throw new Tmd_Pqr_Json_Response(true, $data, $status); }
function wp_send_json_error($data, $status = 200) { throw new Tmd_Pqr_Json_Response(false, $data, $status); }
function wp_mail($to, $subject, $message, $headers = [], $attachments = []) {
    global $mock_mail_calls, $mock_mail_result;
    $mock_mail_calls[] = compact('to', 'subject', 'message', 'headers', 'attachments');
    if ($mock_mail_result instanceof Throwable) {
        throw $mock_mail_result;
    }
    return $mock_mail_result;
}
function is_page($page_id) { global $mock_is_page; return $mock_is_page && 284 === (int) $page_id; }
function get_stylesheet_directory() { return dirname(__DIR__) . '/wp-content/themes/blocksy-child'; }
function get_stylesheet_directory_uri() { return 'https://tecnimontacargas.com/wp-content/themes/blocksy-child'; }
function wp_enqueue_script($handle, $src, $deps, $version, $footer) { global $mock_scripts; $mock_scripts[$handle] = compact('src', 'deps', 'version', 'footer'); }
function wp_localize_script($handle, $name, $data) { global $mock_localized; $mock_localized[$handle] = compact('name', 'data'); }
function admin_url($path) { return 'https://tecnimontacargas.com/wp-admin/' . $path; }
function wp_create_nonce($action) { return 'nonce-for-' . $action; }

function tmd_pqr_endpoint_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-pqr.php';

function tmd_pqr_endpoint_post() {
    return [
        'form_type'    => 'pqr',
        'request_type' => 'Queja',
        'subject'      => 'Atención recibida',
        'name'         => 'Ana Pérez',
        'email'        => 'ana@example.com',
        'phone'        => '300 123 4567',
        'order_number' => '',
        'message'      => 'Deseo registrar una observación.',
        'terms'        => 'Acepto',
        'website'      => '',
    ];
}

function tmd_pqr_endpoint_run($post, $ip = '192.0.2.10') {
    $_POST = $post;
    $_SERVER['REMOTE_ADDR'] = $ip;

    try {
        tmd_pqr_ajax();
    } catch (Tmd_Pqr_Json_Response $response) {
        return $response;
    }

    throw new RuntimeException('Endpoint debe finalizar con respuesta JSON.');
}

tmd_pqr_endpoint_assert(isset($mock_actions['wp_ajax_tmd_pqr']), 'Debe registrar endpoint autenticado.');
tmd_pqr_endpoint_assert(isset($mock_actions['wp_ajax_nopriv_tmd_pqr']), 'Debe registrar endpoint público.');
tmd_pqr_endpoint_assert(isset($mock_actions['wp_enqueue_scripts'][45]), 'Debe registrar assets focalizados.');
tmd_pqr_endpoint_assert(false !== strpos(file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/functions.php'), "inc/tmd-pqr.php"), 'functions.php debe cargar módulo PQR.');

$enqueue = $mock_actions['wp_enqueue_scripts'][45][0];
$enqueue();
tmd_pqr_endpoint_assert([] === $mock_scripts, 'Fuera de página 284 no debe cargar JS PQR.');
$mock_is_page = true;
$enqueue();
tmd_pqr_endpoint_assert(isset($mock_scripts['tmd-pqr'], $mock_localized['tmd-pqr']), 'Página 284 debe cargar JS y configuración AJAX.');

$mock_mail_calls = [];
$mock_transients = [];
$response = tmd_pqr_endpoint_run(tmd_pqr_endpoint_post());
tmd_pqr_endpoint_assert($response->success && 200 === $response->status, 'PQR válida debe responder éxito.');
tmd_pqr_endpoint_assert(1 === count($mock_mail_calls), 'PQR válida debe invocar wp_mail una vez.');
tmd_pqr_endpoint_assert('gerencia@tmdual.com' === $mock_mail_calls[0]['to'], 'Endpoint debe enviar solo a gerencia.');

$mock_nonce_valid = false;
$mock_mail_calls = [];
$response = tmd_pqr_endpoint_run(tmd_pqr_endpoint_post());
tmd_pqr_endpoint_assert(! $response->success && 403 === $response->status, 'Nonce inválido debe responder 403.');
tmd_pqr_endpoint_assert([] === $mock_mail_calls, 'Nonce inválido no debe enviar correo.');
$mock_nonce_valid = true;

$honeypot = tmd_pqr_endpoint_post();
$honeypot['website'] = 'robot';
$mock_mail_calls = [];
$response = tmd_pqr_endpoint_run($honeypot);
tmd_pqr_endpoint_assert($response->success, 'Honeypot debe responder éxito genérico.');
tmd_pqr_endpoint_assert([] === $mock_mail_calls, 'Honeypot no debe enviar correo.');

$invalid = tmd_pqr_endpoint_post();
$invalid['request_type'] = 'Cotizacion';
$mock_mail_calls = [];
$response = tmd_pqr_endpoint_run($invalid);
tmd_pqr_endpoint_assert(! $response->success && 400 === $response->status, 'Tipo inválido debe responder 400.');
tmd_pqr_endpoint_assert([] === $mock_mail_calls, 'Tipo inválido no debe enviar correo.');

$mock_mail_result = false;
$mock_mail_calls = [];
$mock_transients = [];
$response = tmd_pqr_endpoint_run(tmd_pqr_endpoint_post());
tmd_pqr_endpoint_assert(! $response->success && 502 === $response->status, 'Fallo wp_mail debe responder 502.');

$mock_mail_result = new RuntimeException('Transport exception');
$mock_transients = [];
$response = tmd_pqr_endpoint_run(tmd_pqr_endpoint_post());
tmd_pqr_endpoint_assert(! $response->success && 502 === $response->status, 'Excepción WPO365 debe responder 502 sin filtrar detalle.');
tmd_pqr_endpoint_assert('No fue posible procesar la solicitud. Inténtalo nuevamente más tarde.' === $response->data['message'], 'Excepción debe devolver mensaje público genérico.');
tmd_pqr_endpoint_assert(false === strpos($response->data['message'], 'Transport exception'), 'Respuesta no debe filtrar detalle de WPO365.');
$mock_mail_result = true;

$mock_transients = [];
$ip_key = 'tmd_pqr_ip_' . substr(wp_hash('192.0.2.10'), 0, 32);
$mock_transients[$ip_key] = 5;
$mock_mail_calls = [];
$response = tmd_pqr_endpoint_run(tmd_pqr_endpoint_post());
tmd_pqr_endpoint_assert(! $response->success && 429 === $response->status, 'Rate limit IP debe responder 429.');
tmd_pqr_endpoint_assert([] === $mock_mail_calls, 'Rate limit no debe enviar correo.');

$mock_transients = [];
$email_key = 'tmd_pqr_email_' . substr(wp_hash('ana@example.com'), 0, 32);
$mock_transients[$email_key] = 5;
$mock_mail_calls = [];
$response = tmd_pqr_endpoint_run(tmd_pqr_endpoint_post(), '198.51.100.8');
tmd_pqr_endpoint_assert(! $response->success && 429 === $response->status, 'Rate limit email debe responder 429 aunque cambie IP.');
tmd_pqr_endpoint_assert([] === $mock_mail_calls, 'Rate limit email no debe enviar correo.');

fwrite(STDOUT, "OK: hooks, assets, nonce, honeypot, mail, errores y rate limit PQR.\n");
