<?php

define('WP_CLI', true);

class WP_Error {}
class WP_CLI {
    public static $success = [];
    public static function error($message) { throw new RuntimeException($message); }
    public static function success($message) { self::$success[] = $message; }
}

$mock_pages = [
    275 => (object) ['post_type' => 'page', 'post_content' => '<a href="/nosotros/contacto/">Alianza</a>'],
    793 => (object) ['post_type' => 'page', 'post_content' => '<a href="/nosotros/contacto/">Proveedor</a>'],
];
$mock_cf7_form_markup = '[select* service "Mantenimiento" "ÁLIANZAS|alianza" "Venta"]';
$mock_cf7_save_calls = 0;

class WPCF7_ContactForm {
    public static function get_instance($id) { return 14 === (int) $id ? new self() : null; }
    public function get_properties() { global $mock_cf7_form_markup; return ['form' => $mock_cf7_form_markup, 'mail' => ['subject' => 'Actual']]; }
    public function title() { return 'Formulario de contacto 1'; }
    public function locale() { return 'es_CO'; }
}

function get_post($id) { global $mock_pages; return $mock_pages[$id] ?? null; }
function wp_update_post($data) { global $mock_pages; $mock_pages[$data['ID']]->post_content = $data['post_content']; return $data['ID']; }
function is_wp_error() { return false; }
function clean_post_cache() {}
function remove_accents($value) { return strtr($value, ['Á' => 'A', 'á' => 'a']); }
function wpcf7_save_contact_form($data, $context) {
    global $mock_cf7_form_markup, $mock_cf7_save_calls;
    if ('save' !== $context || 14 !== (int) ($data['id'] ?? 0)) return false;
    $mock_cf7_save_calls++;
    $mock_cf7_form_markup = $data['form'];
    return new WPCF7_ContactForm();
}
function wpcf7_contact_form($id) { return WPCF7_ContactForm::get_instance($id); }

$backup = dirname(__DIR__) . '/.codex-tmp/business-content-backup-test';
if (! is_dir($backup)) mkdir($backup, 0700, true);
file_put_contents($backup . '/database.sql', 'verified-test-backup');
putenv('TMD_BUSINESS_PROPOSALS_EXECUTE=1');
putenv('TMD_VERIFIED_BACKUP_PATH=' . $backup);

require dirname(__DIR__) . '/scripts/update-business-proposals-content.php';

function tmd_business_command_assert($condition, $message) {
    if (! $condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}

tmd_business_command_assert(false !== strpos($mock_pages[275]->post_content, '#tmd-business-proposal-form'), 'Debe persistir el CTA de Alianzas.');
tmd_business_command_assert(false !== strpos($mock_pages[793]->post_content, '#tmd-business-proposal-form'), 'Debe persistir el CTA de Proveedores.');
tmd_business_command_assert(1 === $mock_cf7_save_calls, 'Debe guardar Contact Form 7 mediante la API soportada una vez.');
tmd_business_command_assert(false === stripos($mock_cf7_form_markup, 'alianza'), 'La verificación posterior debe observar Alianzas ausente.');
tmd_business_command_assert(! empty(WP_CLI::$success), 'El comando debe confirmar éxito solo después de verificar el postestado.');

unlink($backup . '/database.sql');
rmdir($backup);
putenv('TMD_BUSINESS_PROPOSALS_EXECUTE');
putenv('TMD_VERIFIED_BACKUP_PATH');

echo "OK: comando persistente de propuestas empresariales\n";
