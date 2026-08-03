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

class Tmd_Json_Response extends RuntimeException {
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
$mock_styles = [];
$mock_scripts = [];
$mock_localized = [];

function add_action($hook, $callback, $priority = 10) { global $mock_actions; $mock_actions[$hook][$priority][] = $callback; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_unslash($value) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_file_name($value) { return preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $value); }
function sanitize_email($value) { return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function home_url($path = '/') { return 'https://tecnimontacargas.com' . $path; }
function wp_hash($value) { return hash('sha256', $value); }
function check_ajax_referer() { global $mock_nonce_valid; return $mock_nonce_valid; }
function get_transient($key) { global $mock_transients; return $mock_transients[$key] ?? false; }
function set_transient($key, $value) { global $mock_transients; $mock_transients[$key] = $value; return true; }
function wp_send_json_success($data, $status = 200) { throw new Tmd_Json_Response(true, $data, $status); }
function wp_send_json_error($data, $status = 200) { throw new Tmd_Json_Response(false, $data, $status); }
function wp_mail($to, $subject, $message, $headers, $attachments) {
    global $mock_mail_calls, $mock_mail_result;
    $mock_mail_calls[] = compact('to', 'subject', 'message', 'headers', 'attachments');
    if ($mock_mail_result instanceof Throwable) {
        throw $mock_mail_result;
    }
    return $mock_mail_result;
}
function wp_delete_file($path) { if (is_file($path)) { unlink($path); } }
function trailingslashit($path) { return rtrim($path, '/\\') . '/'; }
function get_temp_dir() { return dirname(__DIR__) . '/.codex-tmp/'; }
function wp_unique_filename($directory, $filename) { return uniqid('tmd-', true) . '-' . $filename; }
function tmd_job_application_is_uploaded_file($path) { return is_file($path); }
function tmd_job_application_move_uploaded_file($source, $destination) { return rename($source, $destination); }
function is_page($page_id) { global $mock_is_page; return $mock_is_page && 273 === (int) $page_id; }
function get_stylesheet_directory() { return dirname(__DIR__) . '/wp-content/themes/blocksy-child'; }
function get_stylesheet_directory_uri() { return 'https://tecnimontacargas.com/wp-content/themes/blocksy-child'; }
function wp_enqueue_style($handle, $src, $deps, $version) { global $mock_styles; $mock_styles[$handle] = compact('src', 'deps', 'version'); }
function wp_enqueue_script($handle, $src, $deps, $version, $footer) { global $mock_scripts; $mock_scripts[$handle] = compact('src', 'deps', 'version', 'footer'); }
function wp_localize_script($handle, $name, $data) { global $mock_localized; $mock_localized[$handle] = compact('name', 'data'); }
function admin_url($path) { return 'https://tecnimontacargas.com/wp-admin/' . $path; }
function wp_create_nonce($action) { return 'nonce-for-' . $action; }

function tmd_job_endpoint_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-job-application.php';

function tmd_job_endpoint_post() {
    return [
        'form_type' => 'trabaja_con_nosotros',
        'name' => 'Ana Pérez',
        'email' => 'ana@example.com',
        'phone' => '300 123 4567',
        'city' => 'Bogotá',
        'service' => 'Técnico en mantenimiento',
        'message' => 'Experiencia comprobable.',
        'terms' => 'Acepto',
        'website' => '',
    ];
}

function tmd_job_endpoint_run($post, $file_mode = 'valid') {
    $_POST = $post;
    $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
    $upload_path = '';

    if ('missing' === $file_mode) {
        $_FILES = [];
    } else {
        $upload_path = tempnam(dirname(__DIR__) . '/.codex-tmp', 'tmd-cv-upload-');
        file_put_contents($upload_path, 'invalid' === $file_mode ? 'not-a-pdf' : "%PDF-1.4\n%%EOF");
        $_FILES = ['cv' => [
            'name' => 'cv.pdf',
            'tmp_name' => $upload_path,
            'size' => filesize($upload_path),
            'error' => UPLOAD_ERR_OK,
        ]];
    }

    try {
        tmd_job_application_ajax();
    } catch (Tmd_Json_Response $response) {
        return [$response, $upload_path];
    }

    throw new RuntimeException('El endpoint debe finalizar con una respuesta JSON.');
}

function tmd_job_endpoint_cleanup($path) {
    if ('' !== $path && is_file($path)) {
        unlink($path);
    }
}

tmd_job_endpoint_assert(isset($mock_actions['wp_ajax_tmd_job_application']), 'Debe registrar el endpoint autenticado.');
tmd_job_endpoint_assert(isset($mock_actions['wp_ajax_nopriv_tmd_job_application']), 'Debe registrar el endpoint público.');
tmd_job_endpoint_assert(isset($mock_actions['wp_enqueue_scripts'][45]), 'Debe registrar la carga focalizada de assets.');
tmd_job_endpoint_assert(false !== strpos(file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/functions.php'), "inc/tmd-job-application.php"), 'functions.php debe cargar el módulo del formulario.');

$enqueue = $mock_actions['wp_enqueue_scripts'][45][0];
$enqueue();
tmd_job_endpoint_assert([] === $mock_styles && [] === $mock_scripts, 'Fuera de la página 273 no deben cargarse assets.');
$mock_is_page = true;
$enqueue();
tmd_job_endpoint_assert(isset($mock_styles['tmd-job-application'], $mock_scripts['tmd-job-application'], $mock_localized['tmd-job-application']), 'En la página 273 deben cargarse CSS, JS y configuración AJAX.');

$mock_mail_calls = [];
$mock_transients = [];
[$response, $upload] = tmd_job_endpoint_run(tmd_job_endpoint_post());
tmd_job_endpoint_assert($response->success && 200 === $response->status, 'La postulación válida debe responder éxito.');
tmd_job_endpoint_assert(1 === count($mock_mail_calls), 'La postulación válida debe enviar un correo.');
tmd_job_endpoint_assert('rh@tmdual.com' === $mock_mail_calls[0]['to'], 'El destinatario debe ser RH.');
$sent_attachment = $mock_mail_calls[0]['attachments'][0];
tmd_job_endpoint_assert(false === file_exists($sent_attachment), 'El adjunto preparado debe eliminarse después de wp_mail.');

$mock_nonce_valid = false;
$mock_mail_calls = [];
[$response, $upload] = tmd_job_endpoint_run(tmd_job_endpoint_post());
tmd_job_endpoint_assert(! $response->success && 403 === $response->status, 'Nonce inválido debe responder 403.');
tmd_job_endpoint_assert([] === $mock_mail_calls, 'Nonce inválido no debe enviar correo.');
tmd_job_endpoint_cleanup($upload);
$mock_nonce_valid = true;

$honeypot = tmd_job_endpoint_post();
$honeypot['website'] = 'robot';
$mock_mail_calls = [];
[$response, $upload] = tmd_job_endpoint_run($honeypot);
tmd_job_endpoint_assert($response->success, 'El honeypot debe responder éxito genérico.');
tmd_job_endpoint_assert([] === $mock_mail_calls, 'El honeypot no debe enviar correo.');
tmd_job_endpoint_cleanup($upload);

$unknown = tmd_job_endpoint_post();
$unknown['form_type'] = 'pqr';
$mock_mail_calls = [];
[$response, $upload] = tmd_job_endpoint_run($unknown);
tmd_job_endpoint_assert(! $response->success && 403 === $response->status, 'Tipo manipulado debe responder 403.');
tmd_job_endpoint_assert([] === $mock_mail_calls, 'Tipo manipulado no debe enviar correo.');
tmd_job_endpoint_cleanup($upload);

$mock_transients = [];
[$response] = tmd_job_endpoint_run(tmd_job_endpoint_post(), 'missing');
tmd_job_endpoint_assert(! $response->success && 400 === $response->status, 'Un CV ausente debe responder 400.');
[$response, $upload] = tmd_job_endpoint_run(tmd_job_endpoint_post(), 'invalid');
tmd_job_endpoint_assert(! $response->success && 400 === $response->status, 'Un PDF con contenido inválido debe responder 400.');
tmd_job_endpoint_cleanup($upload);

$mock_mail_result = false;
$mock_mail_calls = [];
$mock_transients = [];
[$response, $upload] = tmd_job_endpoint_run(tmd_job_endpoint_post());
tmd_job_endpoint_assert(! $response->success && 502 === $response->status, 'Fallo de wp_mail debe responder 502.');
$failed_attachment = $mock_mail_calls[0]['attachments'][0];
tmd_job_endpoint_assert(false === file_exists($failed_attachment), 'El adjunto preparado debe eliminarse cuando wp_mail falla.');

$mock_mail_result = new RuntimeException('Transport exception');
$mock_mail_calls = [];
$mock_transients = [];
[$response, $upload] = tmd_job_endpoint_run(tmd_job_endpoint_post());
tmd_job_endpoint_assert(! $response->success && 502 === $response->status, 'Una excepción de wp_mail debe responder 502 sin filtrar detalles.');
tmd_job_endpoint_assert('No fue posible enviar la postulación. Inténtalo nuevamente más tarde.' === $response->data['message'], 'La excepción debe devolver únicamente el mensaje público esperado.');
tmd_job_endpoint_assert(false === strpos($response->data['message'], 'Transport exception'), 'No deben filtrarse detalles del transporte.');
$thrown_attachment = $mock_mail_calls[0]['attachments'][0];
tmd_job_endpoint_assert(false === file_exists($thrown_attachment), 'El adjunto preparado debe eliminarse cuando wp_mail lanza una excepción.');
$mock_mail_result = true;

$mock_transients = [];
$ip_rate_key = 'tmd_job_apply_ip_' . substr(wp_hash('192.0.2.10'), 0, 32);
$mock_transients[$ip_rate_key] = 5;
$mock_mail_calls = [];
$different_email = tmd_job_endpoint_post();
$different_email['email'] = 'otra@example.com';
[$response, $upload] = tmd_job_endpoint_run($different_email);
tmd_job_endpoint_assert(! $response->success && 429 === $response->status, 'El límite por IP no debe evadirse cambiando el correo.');
tmd_job_endpoint_assert([] === $mock_mail_calls, 'Rate limit no debe enviar correo.');
tmd_job_endpoint_cleanup($upload);

fwrite(STDOUT, "OK: hooks, render, assets, nonce, honeypot, allowlist, archivo real, mail, limpieza y rate limit.\n");
