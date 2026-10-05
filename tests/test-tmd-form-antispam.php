<?php

define('ABSPATH', dirname(__DIR__) . '/');

$mock_actions = [];
$mock_filters = [];
$mock_contact_mail_calls = 0;
$mock_transients = [];
$mock_landing_seed = [];
$mock_antispam_temp = sys_get_temp_dir() . '/tmd-antispam-' . bin2hex(random_bytes(5));
mkdir($mock_antispam_temp, 0700);
define('HOUR_IN_SECONDS', 3600);

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $mock_actions;
    $mock_actions[$hook][$priority][] = compact('callback', 'accepted_args');
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $mock_filters;
    $mock_filters[$hook][$priority][] = compact('callback', 'accepted_args');
}

function get_post_meta($post_id, $key, $single = false) {
    global $mock_landing_seed;
    return $mock_landing_seed[$post_id][$key] ?? '';
}

function sanitize_text_field($value) {
    return trim(strip_tags((string) $value));
}

function trailingslashit($path) {
    return rtrim($path, '/') . '/';
}

function get_temp_dir() {
    global $mock_antispam_temp;
    return $mock_antispam_temp;
}

function wp_hash($value) {
    return hash('sha256', (string) $value);
}

function get_transient($key) {
    global $mock_transients;
    return $mock_transients[$key] ?? false;
}

function set_transient($key, $value, $expiration) {
    global $mock_transients;
    $mock_transients[$key] = $value;
    return true;
}

function wp_unslash($value) {
    return $value;
}

function tmd_form_antispam_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

class Tmd_Form_Antispam_Contact_Form {
    public function id() {
        return 14;
    }
}

class Tmd_Form_Antispam_Other_Form {
    public function id() {
        return 99;
    }
}

class Tmd_Form_Antispam_Submission {
    public $spam_logs = [];

    public function add_spam_log($entry) {
        $this->spam_logs[] = $entry;
    }
}

class Tmd_Form_Antispam_Rental_Form {
    public function id() {
        return 1556;
    }
}

class Tmd_Form_Antispam_Rental_Submission extends Tmd_Form_Antispam_Submission {
    private $posted_data;
    private $contact_form;

    public function __construct($honeypot) {
        $this->posted_data = $honeypot;
        $this->contact_form = new Tmd_Form_Antispam_Rental_Form();
    }

    public function get_contact_form() {
        return $this->contact_form;
    }

    public function get_posted_data($key) {
        return 'tmd_website' === $key ? $this->posted_data : '';
    }
}

function tmd_form_antispam_run_cf7_cycle($contact_form, $submission) {
    global $mock_actions, $mock_contact_mail_calls;

    $abort = false;
    $registration = $mock_actions['wpcf7_before_send_mail'][10][0];
    $callback = $registration['callback'];
    $callback($contact_form, $abort, $submission);

    if (! $abort) {
        $mock_contact_mail_calls++;
    }

    return $abort;
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-form-antispam.php';

$quoted = '"Mozilla/5.0 (Macintosh) Chrome/142.0.0.0 Safari/537.36"';
$normal = 'Mozilla/5.0 (Macintosh) Chrome/142.0.0.0 Safari/537.36';

tmd_form_antispam_assert(tmd_form_antispam_is_quoted_user_agent($quoted), 'Debe detectar la huella envuelta en comillas literales.');
tmd_form_antispam_assert(! tmd_form_antispam_is_quoted_user_agent($normal), 'Chrome correctamente formado debe permanecer permitido.');
tmd_form_antispam_assert(! tmd_form_antispam_is_quoted_user_agent(''), 'User-Agent vacío no debe bloquearse por esta regla.');
tmd_form_antispam_assert(! tmd_form_antispam_is_quoted_user_agent('Mozilla/5.0 "Chrome" Safari/537.36'), 'Comillas internas no deben activar la regla.');

tmd_form_antispam_assert(isset($mock_actions['wpcf7_before_send_mail'][10]), 'Debe registrar el hook soportado de Contact Form 7.');
$registration = $mock_actions['wpcf7_before_send_mail'][10][0];
tmd_form_antispam_assert(3 === $registration['accepted_args'], 'El hook debe aceptar formulario, abort y submission.');

$functions = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/functions.php');
$antispam_include = strpos($functions, "inc/tmd-form-antispam.php");
$job_include = strpos($functions, "inc/tmd-job-application.php");
$pqr_include = strpos($functions, "inc/tmd-pqr.php");
tmd_form_antispam_assert(false !== $antispam_include, 'functions.php debe cargar el módulo antispam.');
tmd_form_antispam_assert(
    $antispam_include < $job_include && $antispam_include < $pqr_include,
    'El módulo antispam debe cargarse antes de postulaciones y PQR.'
);

$_SERVER['HTTP_USER_AGENT'] = $normal;
$mock_contact_mail_calls = 0;
$submission = new Tmd_Form_Antispam_Submission();
$abort = tmd_form_antispam_run_cf7_cycle(new Tmd_Form_Antispam_Contact_Form(), $submission);
tmd_form_antispam_assert(false === $abort && [] === $submission->spam_logs, 'Contacto legítimo debe conservar el envío.');
tmd_form_antispam_assert(1 === $mock_contact_mail_calls, 'Contacto legítimo debe invocar el transporte una vez.');

$_SERVER['HTTP_USER_AGENT'] = $quoted;
$submission = new Tmd_Form_Antispam_Submission();
$abort = tmd_form_antispam_run_cf7_cycle(new Tmd_Form_Antispam_Contact_Form(), $submission);
tmd_form_antispam_assert(true === $abort, 'Contacto automatizado debe abortar antes del correo.');
tmd_form_antispam_assert(1 === count($submission->spam_logs), 'Contacto automatizado debe registrar solo el motivo técnico sin datos personales.');
tmd_form_antispam_assert(1 === $mock_contact_mail_calls, 'Contacto automatizado no debe invocar el transporte.');

$submission = new Tmd_Form_Antispam_Submission();
$abort = tmd_form_antispam_run_cf7_cycle(new Tmd_Form_Antispam_Other_Form(), $submission);
tmd_form_antispam_assert(false === $abort, 'El hook no debe afectar otros formularios de Contact Form 7.');
tmd_form_antispam_assert(2 === $mock_contact_mail_calls, 'Otro formulario de Contact Form 7 debe conservar su transporte.');

$mock_landing_seed[1556]['_tmd_commercial_landing_form_seed'] = '2026-10-04-v1:rental';
$_SERVER['HTTP_USER_AGENT'] = $normal;
$_SERVER['REMOTE_ADDR'] = '203.0.113.77';
$rental_spam_filter = $mock_filters['wpcf7_spam'][20][0]['callback'];
$empty_honeypot_submission = new Tmd_Form_Antispam_Rental_Submission('');
$empty_honeypot_is_spam = $rental_spam_filter(false, $empty_honeypot_submission);
tmd_form_antispam_assert(
    false === $empty_honeypot_is_spam && [] === $empty_honeypot_submission->spam_logs,
    'CF7 1556 debe permitir un envío con el honeypot vacío.'
);

$filled_honeypot_submission = new Tmd_Form_Antispam_Rental_Submission('bot-filled');
$filled_honeypot_is_spam = $rental_spam_filter(false, $filled_honeypot_submission);
tmd_form_antispam_assert(
    true === $filled_honeypot_is_spam
        && 1 === count($filled_honeypot_submission->spam_logs)
        && 'Honeypot field was filled.' === $filled_honeypot_submission->spam_logs[0]['reason'],
    'CF7 1556 debe marcar spam si el honeypot está lleno, sin depender del límite de tasa.'
);
tmd_form_antispam_assert(
    1 === count($mock_transients),
    'El envío con honeypot lleno debe evitar incrementar el contador de tasa legítimo.'
);

foreach (glob($mock_antispam_temp . '/*') ?: [] as $temporary_file) {
    unlink($temporary_file);
}
rmdir($mock_antispam_temp);

fwrite(STDOUT, "OK: detector de User-Agent, envío legítimo y honeypot CF7 1556 vacío/lleno.\n");
