<?php
define('ABSPATH', sys_get_temp_dir() . '/tmd-rental-test-wp/');
define('WP_CLI', true);
define('ARRAY_A', 'ARRAY_A');

class WP_Post {
    public $ID; public $post_title; public $post_type; public $post_name; public $post_content; public $post_status;
    public function __construct(array $data) { foreach ($data as $key => $value) { $this->{$key} = $value; } }
}
class WP_Error {}
class WP_CLI {
    public static $messages = [];
    public static function line($message) { self::$messages[] = ['line', $message]; }
    public static function success($message) { self::$messages[] = ['success', $message]; }
    public static function warning($message) { self::$messages[] = ['warning', $message]; }
    public static function error($message) { throw new RuntimeException($message); }
}
class WPCF7_ContactForm {
    public function id() { return 1556; }
    public function locale() { return 'es_CO'; }
    public function get_properties() { return $GLOBALS['test_form']; }
}
class TestWpdb {
    public $posts = 'wp_posts'; public $postmeta = 'wp_postmeta'; public $last_error = ''; public $engine = 'InnoDB';
    public $rows = []; public $meta_rows = []; public $queries = []; public $snapshot = null; public $in_transaction = false;
    public function prepare($query, ...$args) {
        return preg_replace_callback('/%[ds]/', static function ($match) use (&$args) {
            $value = array_shift($args);
            return '%d' === $match[0] ? (string) (int) $value : "'" . addslashes((string) $value) . "'";
        }, $query);
    }
    public function get_var($query) { return $this->engine; }
    public function get_row($query, $output = null) {
        if (! preg_match('/WHERE ID = (\d+)/', $query, $match)) { return null; }
        return $this->rows[(int) $match[1]] ?? null;
    }
    public function get_results($query, $output = null) {
        if (! preg_match('/WHERE post_id = (\d+)/', $query, $match)) { return []; }
        $rows = $this->meta_rows[(int) $match[1]] ?? [];
        if (preg_match('/meta_key IN \(([^)]+)\)/', $query, $key_list)) {
            preg_match_all("/'((?:\\\\'|[^'])*)'/", $key_list[1], $key_matches);
            $keys = array_map('stripslashes', $key_matches[1]);
            $rows = array_values(array_filter($rows, static fn ($row) => in_array($row['meta_key'], $keys, true)));
        }
        usort($rows, static fn ($left, $right) => $left['meta_id'] <=> $right['meta_id']);
        return $rows;
    }
    public function query($query) {
        $this->queries[] = $query;
        if ('START TRANSACTION' === $query) {
            $this->snapshot = [
                'rows' => $this->rows, 'meta_rows' => $this->meta_rows,
                'meta' => $GLOBALS['test_meta'], 'form' => $GLOBALS['test_form'],
                'page' => clone $GLOBALS['test_page'],
            ];
            $this->in_transaction = true;
        } elseif ('COMMIT' === $query) {
            $this->snapshot = null; $this->in_transaction = false;
            if (! empty($GLOBALS['test_ambiguous_commit'])) { return false; }
        } elseif ('ROLLBACK' === $query && is_array($this->snapshot)) {
            $snapshot = $this->snapshot;
            $this->rows = $snapshot['rows']; $this->meta_rows = $snapshot['meta_rows'];
            $GLOBALS['test_meta'] = $snapshot['meta']; $GLOBALS['test_form'] = $snapshot['form'];
            $GLOBALS['test_page'] = $snapshot['page'];
            $this->snapshot = null; $this->in_transaction = false;
        }
        return 0;
    }
}
function wp_json_encode($value, $options = 0) { return json_encode($value, $options); }
function trailingslashit($value) { return rtrim($value, '/') . '/'; }
function untrailingslashit($value) { return rtrim($value, '/'); }
function get_temp_dir() { return $GLOBALS['test_temp']; }
function get_stylesheet_directory_uri() { return 'https://example.test/wp-content/themes/blocksy-child'; }
function home_url($path = '') { return 'https://example.test' . $path; }
function esc_url($value) { return $value; }
function esc_url_raw($value) { return $value; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function absint($value) { return abs((int) $value); }
function wp_slash($value) { return addslashes($value); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function get_post($id) { return 1558 === (int) $id ? $GLOBALS['test_page'] : null; }
function get_post_field($field, $id) { return $GLOBALS['wpdb']->rows[(int) $id][$field] ?? ''; }
function get_post_meta($id, $key, $single = false) { return $GLOBALS['test_meta'][(int) $id][$key] ?? ''; }
function clean_post_cache($id) {
    ++$GLOBALS['test_cache_clears'];
    if (1558 === (int) $id && isset($GLOBALS['wpdb']->rows[1558])) {
        $GLOBALS['test_page'] = new WP_Post($GLOBALS['wpdb']->rows[1558]);
    }
}
function wp_update_post($data, $wp_error = false) {
    ++$GLOBALS['test_page_writes'];
    foreach (['post_title', 'post_status', 'post_content'] as $key) {
        if (array_key_exists($key, $data)) {
            $GLOBALS['wpdb']->rows[1558][$key] = 'post_content' === $key ? stripslashes((string) $data[$key]) : $data[$key];
        }
    }
    return 1558;
}
function update_post_meta($id, $key, $value) {
    $GLOBALS['test_meta'][(int) $id][$key] = $value;
    foreach ($GLOBALS['wpdb']->meta_rows[(int) $id] as &$row) {
        if ($key === $row['meta_key']) { $row['meta_value'] = $value; return true; }
    }
    $GLOBALS['wpdb']->meta_rows[(int) $id][] = [
        'meta_id' => count($GLOBALS['wpdb']->meta_rows[(int) $id] ?? []) + 100,
        'meta_key' => $key, 'meta_value' => $value,
    ];
    return true;
}
function wpcf7_contact_form($id) { return 1556 === (int) $id ? new WPCF7_ContactForm() : false; }
function wpcf7_save_contact_form($data, $context = 'save') {
    ++$GLOBALS['test_form_writes'];
    if (! empty($GLOBALS['test_fail_form_save'])) { return false; }
    foreach (['form', 'mail', 'mail_2', 'messages', 'additional_settings'] as $key) {
        if (array_key_exists($key, $data)) { $GLOBALS['test_form'][$key] = $data[$key]; }
    }
    return new WPCF7_ContactForm();
}
function tmd_commercial_landing_script_backup_is_valid(): bool { return ! empty($GLOBALS['test_backup_valid']); }
require_once dirname(__DIR__) . '/scripts/commercial-landing-rental-v2.php';
require_once dirname(__DIR__) . '/scripts/update-rental-landing-v2.php';

function test_assert($condition, $message) {
    if (! $condition) { fwrite(STDERR, 'FAIL: ' . $message . "\n"); exit(1); }
}
function test_remove_tree($path) {
    foreach (glob($path . '/*') ?: [] as $child) {
        if (is_dir($child) && ! is_link($child)) { test_remove_tree($child); } else { unlink($child); }
    }
    if (is_dir($path)) { rmdir($path); }
}

$temp = sys_get_temp_dir() . '/tmd-rental-v2-' . bin2hex(random_bytes(6));
$backup = $temp . '/backup';
mkdir($temp, 0700); mkdir($backup, 0700);
$GLOBALS['test_temp'] = $temp;
$GLOBALS['test_page_writes'] = 0; $GLOBALS['test_form_writes'] = 0; $GLOBALS['test_cache_clears'] = 0;
$GLOBALS['test_backup_valid'] = false; $GLOBALS['test_fail_form_save'] = false; $GLOBALS['test_ambiguous_commit'] = false;
$GLOBALS['wpdb'] = new TestWpdb();
$GLOBALS['test_form'] = [
    'form' => '[text tmd_website tabindex:-1 autocomplete:off]',
    'mail' => ['recipient' => 'sales@example.test', 'subject' => 'Keep subject', 'additional_headers' => 'Reply-To: sales@example.test', 'body' => 'Old body'],
    'mail_2' => ['active' => false], 'messages' => ['mail_sent_ok' => 'Received'], 'additional_settings' => '',
];
$GLOBALS['test_meta'] = [1558 => ['rank_math_title' => 'Old title', 'rank_math_description' => 'Old description']];
$GLOBALS['wpdb']->rows = [
    1558 => ['ID' => 1558, 'post_type' => 'page', 'post_name' => 'alquiler-montacargas-electricos', 'post_status' => 'publish', 'post_title' => 'Alquiler y venta de montacargas eléctricos', 'post_content' => '<!-- previous page -->'],
    1556 => ['ID' => 1556, 'post_type' => 'wpcf7_contact_form', 'post_name' => 'alquiler-form', 'post_status' => 'publish', 'post_title' => 'Alquiler CF7', 'post_content' => 'Previous CF7 record'],
];
$GLOBALS['wpdb']->meta_rows = [
    1558 => [
        ['meta_id' => 1, 'meta_key' => 'rank_math_title', 'meta_value' => 'Old title'],
        ['meta_id' => 2, 'meta_key' => 'rank_math_description', 'meta_value' => 'Old description'],
    ],
    1556 => [['meta_id' => 3, 'meta_key' => '_tmd_commercial_landing_form_seed', 'meta_value' => '2026-10-04-v1:rental']],
];
$GLOBALS['test_page'] = new WP_Post($GLOBALS['wpdb']->rows[1558]);
register_shutdown_function(static function () use ($temp) { test_remove_tree($temp); });

putenv('TMD_VERIFIED_BACKUP_PATH=' . $backup);
putenv('TMD_RENTAL_V2_COMMERCIAL_CLAIMS_CONFIRMED=yes');
putenv('TMD_RENTAL_V2_CONTRAST_APPROVED=yes');
$properties_before = $GLOBALS['test_form'];
$properties_after = tmd_commercial_landing_rental_v2_target_form_properties($properties_before);
$meta_before = $GLOBALS['test_meta'][1558];
$meta_after = [
    'rank_math_title' => 'Venta o alquiler de montacargas eléctricos | Tecnimontacargas',
    'rank_math_description' => 'Venta o alquiler de montacargas eléctricos para bodegas, centros de distribución y plantas. Alquiler sin operador desde 15 días, con recomendación técnica según tu operación.',
];
$content_after = tmd_commercial_landing_script_rental_v2_content(
    1556, untrailingslashit(get_stylesheet_directory_uri()) . '/assets/img'
);
$hashes = [
    'TMD_RENTAL_V2_EXPECTED_PAGE_SHA256' => hash('sha256', $GLOBALS['test_page']->post_content),
    'TMD_RENTAL_V2_TARGET_PAGE_SHA256' => hash('sha256', $content_after),
    'TMD_RENTAL_V2_EXPECTED_FORM_SHA256' => tmd_commercial_landing_rental_v2_hash($properties_before),
    'TMD_RENTAL_V2_TARGET_FORM_SHA256' => tmd_commercial_landing_rental_v2_hash($properties_after),
    'TMD_RENTAL_V2_EXPECTED_META_SHA256' => tmd_commercial_landing_rental_v2_hash($meta_before),
    'TMD_RENTAL_V2_TARGET_META_SHA256' => tmd_commercial_landing_rental_v2_hash($meta_after),
    'TMD_RENTAL_V2_EXPECTED_META_STORAGE_SHA256' => tmd_commercial_landing_rental_v2_hash($GLOBALS['wpdb']->meta_rows[1558]),
    'TMD_RENTAL_V2_EXPECTED_FORM_STORAGE_SHA256' => tmd_commercial_landing_rental_v2_hash([
        'post' => $GLOBALS['wpdb']->rows[1556], 'meta' => $GLOBALS['wpdb']->meta_rows[1556],
    ]),
];
foreach ($hashes as $name => $value) { putenv($name . '=' . $value); }

tmd_commercial_landing_script_run_rental_v2_update(false);
test_assert(
    0 === $GLOBALS['test_page_writes'] && 0 === $GLOBALS['test_form_writes']
        && ! in_array('START TRANSACTION', $GLOBALS['wpdb']->queries, true)
        && false !== strpos(implode("\n", array_column(WP_CLI::$messages, 1)), 'Dry-run sin escrituras'),
    'El dry-run debe informar hashes sin iniciar una transacción ni escribir.'
);

putenv('TMD_RENTAL_V2_EXPECTED_PAGE_SHA256=' . str_repeat('0', 64));
$stale_rejected = false;
try { tmd_commercial_landing_script_run_rental_v2_update(true); }
catch (RuntimeException $exception) { $stale_rejected = false !== strpos($exception->getMessage(), 'no coincide'); }
test_assert($stale_rejected && 0 === $GLOBALS['test_page_writes'], 'Un hash de origen obsoleto debe rechazar antes de escribir.');
putenv('TMD_RENTAL_V2_EXPECTED_PAGE_SHA256=' . $hashes['TMD_RENTAL_V2_EXPECTED_PAGE_SHA256']);

putenv('TMD_RENTAL_V2_COMMERCIAL_CLAIMS_CONFIRMED=no');
$claim_rejected = false;
try { tmd_commercial_landing_script_run_rental_v2_update(true); }
catch (RuntimeException $exception) { $claim_rejected = false !== strpos($exception->getMessage(), 'confirmar los datos comerciales'); }
test_assert($claim_rejected && 0 === $GLOBALS['test_page_writes'], 'La ejecución sin confirmación comercial debe rechazarse.');
putenv('TMD_RENTAL_V2_COMMERCIAL_CLAIMS_CONFIRMED=yes');

$backup_rejected = false;
try { tmd_commercial_landing_script_run_rental_v2_update(true); }
catch (RuntimeException $exception) { $backup_rejected = false !== strpos($exception->getMessage(), 'backup completo'); }
test_assert($backup_rejected && 0 === $GLOBALS['test_page_writes'], 'La ejecución sin backup validado debe rechazarse.');

$GLOBALS['test_backup_valid'] = true;
$GLOBALS['test_fail_form_save'] = true;
$rollback_rejected = false;
try { tmd_commercial_landing_script_run_rental_v2_update(true); }
catch (RuntimeException $exception) { $rollback_rejected = false !== strpos($exception->getMessage(), 'estado original'); }
$artifact = $backup . '/rental-v2-page-1558-form-1556-before.json';
test_assert(
    $rollback_rejected
        && 'Alquiler y venta de montacargas eléctricos' === $GLOBALS['test_page']->post_title
        && '<!-- previous page -->' === $GLOBALS['wpdb']->rows[1558]['post_content']
        && $properties_before === $GLOBALS['test_form']
        && $meta_before === $GLOBALS['test_meta'][1558]
        && 'ROLLBACK' === end($GLOBALS['wpdb']->queries)
        && is_file($artifact) && 0600 === (fileperms($artifact) & 0777),
    'El error al guardar CF7 debe revertir página, formulario y meta, dejando rollback privado.'
);
unlink($artifact);
$GLOBALS['test_fail_form_save'] = false;

tmd_commercial_landing_script_run_rental_v2_update(true);
test_assert(
    'Venta o alquiler de montacargas eléctricos' === $GLOBALS['test_page']->post_title
        && $meta_after === $GLOBALS['test_meta'][1558]
        && hash_equals($hashes['TMD_RENTAL_V2_TARGET_PAGE_SHA256'], hash('sha256', $GLOBALS['test_page']->post_content))
        && $properties_after === $GLOBALS['test_form']
        && 'COMMIT' === end($GLOBALS['wpdb']->queries),
    'La ejecución aprobada debe guardar y verificar página, CF7, Rank Math y commit.'
);

unlink($backup . '/rental-v2-page-1558-form-1556-before.json');
$GLOBALS['wpdb']->rows[1558] = [
    'ID' => 1558, 'post_type' => 'page', 'post_name' => 'alquiler-montacargas-electricos',
    'post_status' => 'publish', 'post_title' => 'Alquiler y venta de montacargas eléctricos',
    'post_content' => '<!-- previous page -->',
];
$GLOBALS['wpdb']->meta_rows = [
    1558 => [
        ['meta_id' => 1, 'meta_key' => 'rank_math_title', 'meta_value' => 'Old title'],
        ['meta_id' => 2, 'meta_key' => 'rank_math_description', 'meta_value' => 'Old description'],
    ],
    1556 => [['meta_id' => 3, 'meta_key' => '_tmd_commercial_landing_form_seed', 'meta_value' => '2026-10-04-v1:rental']],
];
$GLOBALS['test_meta'] = [1558 => $meta_before];
$GLOBALS['test_form'] = $properties_before;
$GLOBALS['test_page'] = new WP_Post($GLOBALS['wpdb']->rows[1558]);
$GLOBALS['wpdb']->queries = [];
$GLOBALS['test_ambiguous_commit'] = true;
tmd_commercial_landing_script_run_rental_v2_update(true);
$warning_text = implode("\n", array_column(array_filter(
    WP_CLI::$messages,
    static fn ($message) => 'warning' === $message[0]
), 1));
test_assert(
    false !== strpos($warning_text, 'COMMIT fue ambiguo')
        && 'Venta o alquiler de montacargas eléctricos' === $GLOBALS['test_page']->post_title
        && $properties_after === $GLOBALS['test_form']
        && 'ROLLBACK' === end($GLOBALS['wpdb']->queries),
    'Si COMMIT devuelve estado ambiguo pero el destino persistió, el runner debe conciliarlo por hashes.'
);
fwrite(STDOUT, "OK: updater rental-v2: dry-run, hash, gates, rollback y commit.\n");
