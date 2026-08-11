<?php

define('ABSPATH', dirname(__DIR__) . '/');

$mock_actions = [];

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

$_SERVER['HTTP_USER_AGENT'] = $normal;
$abort = false;
$submission = new Tmd_Form_Antispam_Submission();
tmd_form_antispam_cf7_before_send_mail(new Tmd_Form_Antispam_Contact_Form(), $abort, $submission);
tmd_form_antispam_assert(false === $abort && [] === $submission->spam_logs, 'Contacto legítimo debe conservar el envío.');

$_SERVER['HTTP_USER_AGENT'] = $quoted;
$abort = false;
$submission = new Tmd_Form_Antispam_Submission();
tmd_form_antispam_cf7_before_send_mail(new Tmd_Form_Antispam_Contact_Form(), $abort, $submission);
tmd_form_antispam_assert(true === $abort, 'Contacto automatizado debe abortar antes del correo.');
tmd_form_antispam_assert(1 === count($submission->spam_logs), 'Contacto automatizado debe registrar solo el motivo técnico sin datos personales.');

$abort = false;
$submission = new Tmd_Form_Antispam_Submission();
tmd_form_antispam_cf7_before_send_mail(new Tmd_Form_Antispam_Other_Form(), $abort, $submission);
tmd_form_antispam_assert(false === $abort, 'El hook no debe afectar otros formularios de Contact Form 7.');

fwrite(STDOUT, "OK: detector de User-Agent y aborto focalizado de Contact Form 7.\n");
