<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('WP_CLI', true);

class WP_Post {
    public $ID;
    public $post_title;
    public $post_type;
    public $post_name;

    public function __construct($id, $title, $type = 'wpcf7_contact_form', $name = '') {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_type = $type;
        $this->post_name = $name;
    }
}

class WP_CLI {
    public static $messages = [];
    public static $save_calls = 0;

    public static function line($message) {
        self::$messages[] = ['line', $message];
    }

    public static function success($message) {
        self::$messages[] = ['success', $message];
    }

    public static function error($message) {
        throw new RuntimeException($message);
    }
}

class WPCF7_ContactForm {
    private $form_id;

    public function __construct($form_id) {
        $this->form_id = (int) $form_id;
    }

    public static function get_instance($id) {
        return 14 === (int) $id ? new self(14) : null;
    }

    public function get_properties() {
        if (14 === $this->form_id) {
            return [
                'mail' => ['recipient' => ''],
                'mail_2' => ['active' => false],
                'messages' => [],
            ];
        }

        return $GLOBALS['tmd_test_form_properties'][$this->form_id] ?? [];
    }

    public function locale() {
        return 'es_CO';
    }

    public function id() {
        return $this->form_id;
    }
}

function is_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

function get_temp_dir() {
    return $GLOBALS['tmd_test_temp_dir'];
}

function trailingslashit($path) {
    return rtrim($path, '/') . '/';
}

function untrailingslashit($path) {
    return rtrim($path, '/');
}

function get_posts($args) {
    return 'wpcf7_contact_form' === ($args['post_type'] ?? '')
        ? ($GLOBALS['tmd_test_form_posts'] ?? [])
        : [];
}

function get_post_meta($post_id, $key, $single = false) {
    return $GLOBALS['tmd_test_post_meta'][$post_id][$key] ?? '';
}

function get_post_types($args, $output = 'names') {
    return ['page'];
}

function get_stylesheet_directory_uri() {
    return 'https://example.test/wp-content/themes/blocksy-child';
}

function home_url($path = '') {
    return 'https://example.test' . $path;
}

function esc_url($value) {
    return $value;
}

function esc_url_raw($value) {
    return $value;
}

function wpcf7_save_contact_form($data, $context = 'save') {
    ++WP_CLI::$save_calls;
    return false;
}

function wpcf7_contact_form($id) {
    return isset($GLOBALS['tmd_test_form_properties'][(int) $id])
        ? new WPCF7_ContactForm($id)
        : false;
}

function tmd_commercial_landing_recipient_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$temp_dir = __DIR__ . '/.tmp-commercial-landings-test-' . bin2hex(random_bytes(6));
mkdir($temp_dir, 0700);
$GLOBALS['tmd_test_temp_dir'] = $temp_dir;
register_shutdown_function(static function () use ($temp_dir): void {
    foreach (glob($temp_dir . '/*') ?: [] as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    if (is_dir($temp_dir)) {
        rmdir($temp_dir);
    }
});
putenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT=info@tmdual.com');
putenv('TMD_COMMERCIAL_LANDINGS_EXECUTE');

$seed_error = null;
try {
    require dirname(__DIR__) . '/scripts/create-commercial-landing-pages.php';
} catch (RuntimeException $exception) {
    $seed_error = $exception;
}

tmd_commercial_landing_recipient_assert(
    null === $seed_error,
    'El dry-run debe aceptar el destinatario explícito aunque CF7 ID 14 no tenga uno configurado.'
        . ($seed_error ? ' Detalle: ' . $seed_error->getMessage() : '')
);

$recipient = tmd_commercial_landing_script_resolve_recipient(
    (string) getenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT'),
    ''
);
$form_specs = tmd_commercial_landing_script_form_specs($recipient, 'es_CO', []);
tmd_commercial_landing_recipient_assert(
    'info@tmdual.com' === $form_specs['rental']['mail']['recipient'],
    'El formulario nuevo de alquiler debe dirigirse al correo autorizado.'
);
tmd_commercial_landing_recipient_assert(
    'info@tmdual.com' === $form_specs['battery']['mail']['recipient'],
    'El formulario nuevo de baterías debe dirigirse al correo autorizado.'
);

$managed_form_id = 200;
$managed_form_title = 'TMD | Cotización de alquiler y venta de montacargas eléctricos';
$GLOBALS['tmd_test_form_posts'] = [new WP_Post($managed_form_id, $managed_form_title)];
$GLOBALS['tmd_test_post_meta'][$managed_form_id]['_tmd_commercial_landing_form_seed'] = '2026-10-04-v1:rental';
$GLOBALS['tmd_test_form_properties'][$managed_form_id] = [
    'form' => '[text tmd_website tabindex:-1 autocomplete:off]',
    'mail' => ['recipient' => 'anterior@example.com'],
];

$mismatch_error = null;
try {
    tmd_commercial_landing_script_existing_forms($form_specs);
} catch (RuntimeException $exception) {
    $mismatch_error = $exception;
}
tmd_commercial_landing_recipient_assert(
    $mismatch_error instanceof RuntimeException,
    'El seed debe detenerse si un formulario administrado ya tiene un destinatario diferente.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($mismatch_error->getMessage(), 'anterior@example.com')
        && false === strpos($mismatch_error->getMessage(), 'info@tmdual.com'),
    'El error por destinatario distinto no debe exponer ninguna dirección de correo.'
);

$GLOBALS['tmd_test_form_properties'][$managed_form_id]['mail']['recipient'] = 'info@tmdual.com';
$existing_forms = tmd_commercial_landing_script_existing_forms($form_specs);
tmd_commercial_landing_recipient_assert(
    $existing_forms['rental'] instanceof WPCF7_ContactForm,
    'El seed debe conservar un formulario administrado cuyo destinatario ya coincide.'
);
tmd_commercial_landing_recipient_assert(
    0 === WP_CLI::$save_calls,
    'El dry-run no debe guardar formularios ni modificar CF7 ID 14.'
);

$output = implode("\n", array_column(WP_CLI::$messages, 1));
tmd_commercial_landing_recipient_assert(
    false !== strpos($output, 'Dry-run sin escrituras'),
    'El seed debe terminar confirmando que el dry-run no escribió datos.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($output, 'info@tmdual.com'),
    'La salida del dry-run no debe imprimir el destinatario.'
);

tmd_commercial_landing_recipient_assert(
    'cotizaciones@example.com' === tmd_commercial_landing_script_resolve_recipient('', 'cotizaciones@example.com'),
    'Sin override, el seed debe conservar como alternativa el destinatario del formulario base.'
);

$invalid_recipient_rejected = false;
try {
    tmd_commercial_landing_script_resolve_recipient('correo-invalido', 'cotizaciones@example.com');
} catch (InvalidArgumentException $exception) {
    $invalid_recipient_rejected = true;
}
tmd_commercial_landing_recipient_assert(
    $invalid_recipient_rejected,
    'Un override inválido debe detener el seed, sin sustituirse silenciosamente por el destinatario base.'
);

$rental_content = tmd_commercial_landing_script_page_content('rental', 1556);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Alquiler de <em>montacargas eléctricos</em>'),
    'El hero de alquiler debe mantener el título de la referencia como HTML editable.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Equipos propios con mantenimiento en nuestro taller técnico'),
    'El hero de alquiler debe mantener su subtítulo como texto HTML editable.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Contrabalanceados, reach, pantógrafos y apiladores'),
    'El hero debe incluir el texto de apoyo aprobado en la referencia.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'href="#formulario-montacargas">Solicitar cotización</a>'),
    'El CTA de cotización debe conservar su destino al formulario.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Alquiler mínimo de 1 mes')
        && false !== strpos($rental_content, 'Cobertura en Colombia')
        && false !== strpos($rental_content, 'Alquiler sin operador'),
    'El rediseño debe conservar las condiciones comerciales prioritarias del alquiler.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($rental_content, 'tmd-commercial-landing__stats')
        && false === strpos($rental_content, 'tmd-commercial-landing__brand-note'),
    'La página no debe incluir la franja de cifras ni la nota de marcas que el usuario pidió retirar.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($rental_content, 'Alquiler y venta de <em>montacargas eléctricos</em>'),
    'El seed no debe conservar el titular anterior del hero de alquiler.'
);

foreach (glob($temp_dir . '/*') ?: [] as $file) {
    unlink($file);
}
rmdir($temp_dir);
putenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT');

echo "OK: destinatario, dry-run y contenido de la página comercial de alquiler.\n";
