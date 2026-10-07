<?php

$test_root = sys_get_temp_dir() . '/tmd-thank-you-pages-test-' . bin2hex(random_bytes(6));
mkdir($test_root, 0700, true);
mkdir($test_root . '/wordpress', 0700);
define('ABSPATH', $test_root . '/wordpress/');
define('WP_CLI', true);
define('OBJECT', 'OBJECT');
define('ARRAY_A', 'ARRAY_A');

$GLOBALS['tmd_thank_you_pages'] = [];
$GLOBALS['tmd_thank_you_meta'] = [];
$GLOBALS['tmd_thank_you_next_id'] = 1000;
$GLOBALS['tmd_thank_you_insert_calls'] = 0;
$GLOBALS['tmd_thank_you_meta_calls'] = 0;
$GLOBALS['tmd_thank_you_fail_meta_id'] = 0;

class WP_Post
{
    public $ID;
    public $post_type;
    public $post_name;
    public $post_title;
    public $post_content;
    public $post_status;
    public $post_parent;
    public $comment_status;
    public $ping_status;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}

class WP_Error
{
}

class WP_CLI
{
    public static $messages = [];

    public static function line(string $message): void { self::$messages[] = ['line', $message]; }
    public static function success(string $message): void { self::$messages[] = ['success', $message]; }
    public static function warning(string $message): void { self::$messages[] = ['warning', $message]; }
    public static function error(string $message): void { throw new RuntimeException($message); }
}

class TMD_Thank_You_Test_WPDB
{
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';
    public $last_error = '';
    public $engine = 'InnoDB';
    public $queries = [];
    public $snapshot = null;
    public $in_transaction = false;

    public function prepare(string $query, ...$args): string
    {
        return preg_replace_callback('/%[ds]/', static function ($match) use (&$args): string {
            $value = array_shift($args);
            return '%d' === $match[0] ? (string) (int) $value : "'" . addslashes((string) $value) . "'";
        }, $query);
    }

    public function get_var(string $query)
    {
        if (false !== strpos($query, 'information_schema.TABLES')) {
            return $this->engine;
        }
        if ('SELECT @@in_transaction' === $query) {
            return $this->in_transaction ? '1' : '0';
        }
        return null;
    }

    public function get_row(string $query, $output = null)
    {
        if (! preg_match("/WHERE post_type = '([^']+)' AND post_name = '([^']+)'/", $query, $matches)) {
            return null;
        }
        foreach ($GLOBALS['tmd_thank_you_pages'] as $page) {
            if ($matches[1] === $page->post_type && $matches[2] === $page->post_name) {
                return get_object_vars($page);
            }
        }
        return null;
    }

    public function query(string $query)
    {
        $this->queries[] = $query;
        if ('START TRANSACTION' === $query) {
            $this->snapshot = [
                'pages' => array_map(static fn ($page) => clone $page, $GLOBALS['tmd_thank_you_pages']),
                'meta' => $GLOBALS['tmd_thank_you_meta'],
            ];
            $this->in_transaction = true;
        } elseif ('COMMIT' === $query) {
            $this->snapshot = null;
            $this->in_transaction = false;
        } elseif ('ROLLBACK' === $query && is_array($this->snapshot)) {
            $GLOBALS['tmd_thank_you_pages'] = $this->snapshot['pages'];
            $GLOBALS['tmd_thank_you_meta'] = $this->snapshot['meta'];
            $this->snapshot = null;
            $this->in_transaction = false;
        }
        return 0;
    }
}

function add_shortcode(string $tag, $callback): void {}
function add_filter(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void {}
function add_action(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void {}
function shortcode_atts(array $defaults, $attributes, string $shortcode = ''): array { return array_merge($defaults, (array) $attributes); }
function sanitize_key(string $value): string { return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value)); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function get_stylesheet_directory(): string { return dirname(__DIR__) . '/wp-content/themes/blocksy-child'; }
function get_stylesheet_directory_uri(): string { return 'https://example.test/wp-content/themes/blocksy-child'; }
function get_temp_dir(): string { return $GLOBALS['tmd_thank_you_test_temp']; }
function trailingslashit(string $value): string { return rtrim($value, '/') . '/'; }
function wp_json_encode($value, int $options = 0) { return json_encode($value, $options); }
function wp_slash(string $value): string { return addslashes($value); }
function is_wp_error($value): bool { return $value instanceof WP_Error; }
function clean_post_cache(int $post_id): void {}

function get_post(int $post_id)
{
    return $GLOBALS['tmd_thank_you_pages'][$post_id] ?? null;
}

function get_page_by_path(string $slug, $output = OBJECT, string $post_type = 'page')
{
    foreach ($GLOBALS['tmd_thank_you_pages'] as $page) {
        if ($post_type === $page->post_type && $slug === $page->post_name) {
            return $page;
        }
    }
    return null;
}

function get_post_meta(int $post_id, string $key, bool $single = false)
{
    if ($single) {
        return $GLOBALS['tmd_thank_you_meta'][$post_id][$key] ?? '';
    }
    return array_key_exists($key, $GLOBALS['tmd_thank_you_meta'][$post_id] ?? [])
        ? [$GLOBALS['tmd_thank_you_meta'][$post_id][$key]]
        : [];
}

function wp_insert_post(array $data, bool $wp_error = false)
{
    ++$GLOBALS['tmd_thank_you_insert_calls'];
    $id = ++$GLOBALS['tmd_thank_you_next_id'];
    $data['ID'] = $id;
    $data['post_content'] = stripslashes((string) $data['post_content']);
    $GLOBALS['tmd_thank_you_pages'][$id] = new WP_Post($data);
    return $id;
}

function update_post_meta(int $post_id, string $key, $value)
{
    ++$GLOBALS['tmd_thank_you_meta_calls'];
    if ($post_id === $GLOBALS['tmd_thank_you_fail_meta_id']) {
        return false;
    }
    $GLOBALS['tmd_thank_you_meta'][$post_id][$key] = $value;
    return true;
}

function tmd_thank_you_update_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

function tmd_thank_you_update_expect_error(callable $callback, string $message_part): void
{
    try {
        $callback();
    } catch (RuntimeException $exception) {
        tmd_thank_you_update_assert(false !== stripos($exception->getMessage(), $message_part), 'el rechazo debe explicar: ' . $message_part . ' (recibido: ' . $exception->getMessage() . ')');
        return;
    }
    tmd_thank_you_update_assert(false, 'se esperaba rechazo: ' . $message_part);
}

function tmd_thank_you_update_remove_tree(string $path): void
{
    foreach (glob($path . '/*') ?: [] as $child) {
        if (is_dir($child) && ! is_link($child)) {
            tmd_thank_you_update_remove_tree($child);
        } else {
            unlink($child);
        }
    }
    if (is_dir($path)) {
        rmdir($path);
    }
}

register_shutdown_function(static function () use ($test_root): void {
    tmd_thank_you_update_remove_tree($test_root);
});

$GLOBALS['wpdb'] = new TMD_Thank_You_Test_WPDB();
$GLOBALS['tmd_thank_you_test_temp'] = $test_root . '/wordpress/tmp';
mkdir($GLOBALS['tmd_thank_you_test_temp'], 0700);
putenv('TMD_COMMERCIAL_THANK_YOU_PAGES_EXECUTE');
putenv('TMD_VERIFIED_BACKUP_PATH');

require_once get_stylesheet_directory() . '/inc/tmd-commercial-landing-thank-you.php';
$creator_path = dirname(__DIR__) . '/scripts/create-commercial-landing-thank-you-pages.php';
if (is_file($creator_path)) {
    require $creator_path;
}
tmd_thank_you_update_assert(function_exists('tmd_commercial_thank_you_pages_run'), 'el WP-CLI debe definir el creador protegido de páginas');

tmd_thank_you_update_assert(0 === $GLOBALS['tmd_thank_you_insert_calls'], 'el dry-run inicial no debe insertar páginas');
tmd_thank_you_update_assert([] === $GLOBALS['wpdb']->queries, 'el dry-run inicial no debe abrir una transacción');
tmd_thank_you_update_assert(
    false !== stripos(implode("\n", array_column(WP_CLI::$messages, 1)), 'dry-run sin escrituras'),
    'el comando debe informar que el resultado inicial es una simulación sin escrituras'
);

tmd_thank_you_update_expect_error(
    static fn () => tmd_commercial_thank_you_pages_run(true),
    'backup completo, reciente y verificado'
);
tmd_thank_you_update_assert([] === $GLOBALS['tmd_thank_you_pages'], 'sin backup válido no debe haber páginas');

$backup_path = $test_root . '/private-backup';
mkdir($backup_path, 0700);
$database = "-- MariaDB dump 10.19\nCREATE TABLE wp_posts (id int);\n" . str_repeat('-- verified fixture\n', 5000) . "-- Dump completed on 2026-10-07 12:00:00\n";
file_put_contents($backup_path . '/database.sql', $database);
chmod($backup_path . '/database.sql', 0600);
$manifest = [
    'schema_version' => 1,
    'created_at_utc' => gmdate('Y-m-d\TH:i:s\Z'),
    'environment' => 'production',
    'backup_type' => 'full',
    'verified' => true,
    'database_file' => 'database.sql',
    'database_size_bytes' => strlen($database),
    'database_sha256' => hash('sha256', $database),
    'sql_format' => 'mariadb-dump',
    'sql_header_verified' => true,
    'dump_completion_marker_verified' => true,
    'restore_path' => '/private/database.sql',
    'restore_method' => 'Restore using the repository runbook.',
];
file_put_contents($backup_path . '/BACKUP_MANIFEST.json', json_encode($manifest));
chmod($backup_path . '/BACKUP_MANIFEST.json', 0600);
putenv('TMD_VERIFIED_BACKUP_PATH=' . $backup_path);

tmd_commercial_thank_you_pages_run(true);
$specs = tmd_commercial_thank_you_page_specs();
tmd_thank_you_update_assert(2 === count($GLOBALS['tmd_thank_you_pages']), 'la ejecución debe crear exactamente dos páginas publicadas');
foreach ($specs as $type => $spec) {
    $page = get_page_by_path($spec['slug'], OBJECT, 'page');
    tmd_thank_you_update_assert($page instanceof WP_Post && 'publish' === $page->post_status, $type . ' debe quedar publicada');
    tmd_thank_you_update_assert($spec['title'] === $page->post_title, $type . ' debe guardar el título definido');
    tmd_thank_you_update_assert(hash_equals(hash('sha256', $spec['shortcode']), hash('sha256', $page->post_content)), $type . ' debe guardar el shortcode íntegro');
    tmd_thank_you_update_assert(['noindex', 'follow'] === get_post_meta($page->ID, 'rank_math_robots', true), $type . ' debe guardar robots noindex, follow');
}
$snapshots = glob($backup_path . '/commercial-thank-you-pages-before-*.json') ?: [];
tmd_thank_you_update_assert(1 === count($snapshots), 'la ejecución debe guardar un snapshot privado de rollback');
tmd_thank_you_update_assert(0600 === (fileperms($snapshots[0]) & 0777), 'el snapshot de rollback debe tener permisos 0600');
$snapshot_data = json_decode((string) file_get_contents($snapshots[0]), true);
tmd_thank_you_update_assert(
    hash('sha256', $specs['battery']['shortcode']) === ($snapshot_data['targets']['battery']['post_content_sha256'] ?? ''),
    'el snapshot debe registrar el hash de contenido objetivo'
);

$insert_count = $GLOBALS['tmd_thank_you_insert_calls'];
$query_count = count($GLOBALS['wpdb']->queries);
tmd_commercial_thank_you_pages_run(true);
tmd_thank_you_update_assert($insert_count === $GLOBALS['tmd_thank_you_insert_calls'], 'una segunda ejecución exacta debe ser idempotente');
tmd_thank_you_update_assert($query_count === count($GLOBALS['wpdb']->queries), 'la ejecución idempotente no debe abrir otra transacción');
tmd_thank_you_update_assert(1 === count(glob($backup_path . '/commercial-thank-you-pages-before-*.json') ?: []), 'la ejecución idempotente no debe crear otro snapshot');

$GLOBALS['tmd_thank_you_pages'] = [
    77 => new WP_Post([
        'ID' => 77,
        'post_type' => 'page',
        'post_name' => 'gracias-baterias',
        'post_title' => 'Contenido ajeno',
        'post_content' => 'No sobrescribir',
        'post_status' => 'publish',
        'post_parent' => 0,
    ]),
];
$GLOBALS['tmd_thank_you_meta'] = [];
$writes_before_conflict = $GLOBALS['tmd_thank_you_insert_calls'];
tmd_thank_you_update_expect_error(
    static fn () => tmd_commercial_thank_you_pages_run(false),
    'conflicto'
);
tmd_thank_you_update_assert($writes_before_conflict === $GLOBALS['tmd_thank_you_insert_calls'], 'una colisión de slug no debe sobrescribir ni crear páginas');

$GLOBALS['tmd_thank_you_pages'] = [];
$GLOBALS['tmd_thank_you_meta'] = [];
$GLOBALS['wpdb'] = new TMD_Thank_You_Test_WPDB();
$GLOBALS['tmd_thank_you_next_id'] = 3000;
$GLOBALS['tmd_thank_you_fail_meta_id'] = 3002;
tmd_thank_you_update_expect_error(
    static fn () => tmd_commercial_thank_you_pages_run(true),
    'estado original'
);
tmd_thank_you_update_assert([] === $GLOBALS['tmd_thank_you_pages'], 'el fallo parcial debe revertir las páginas recién insertadas');
tmd_thank_you_update_assert([] === $GLOBALS['tmd_thank_you_meta'], 'el fallo parcial debe revertir los metadatos');
tmd_thank_you_update_assert('ROLLBACK' === end($GLOBALS['wpdb']->queries), 'el fallo parcial debe cerrar con ROLLBACK');

echo "OK: creador protegido de páginas de agradecimiento, backup, idempotencia y rollback.\n";
