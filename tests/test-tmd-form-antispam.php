<?php

define('ABSPATH', dirname(__DIR__) . '/');

$mock_actions = [];
$mock_contact_mail_calls = 0;

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $mock_actions;
    $mock_actions[$hook][$priority][] = compact('callback', 'accepted_args');
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

fwrite(STDOUT, "OK: detector de User-Agent y aborto focalizado de Contact Form 7.\n");
