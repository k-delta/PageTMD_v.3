<?php

$test_root = sys_get_temp_dir() . '/tmd-home-battery-cta-transaction-' . bin2hex(random_bytes(6));
mkdir($test_root, 0700, true);
mkdir($test_root . '/wordpress', 0700);
mkdir($test_root . '/backups', 0700);
define('ABSPATH', $test_root . '/wordpress/');
define('WP_CLI', true);
define('ARRAY_A', 'ARRAY_A');

class WP_Error
{
}

class WP_CLI
{
    public static $messages = [];
    public static function line(string $message): void { self::$messages[] = ['line', $message]; }
    public static function success(string $message): void { self::$messages[] = ['success', $message]; }
    public static function error(string $message): void { throw new RuntimeException($message); }
}

class TMD_Home_Battery_CTA_Test_WPDB
{
    public $posts = 'wp_posts';
    public $last_error = '';
    public $in_transaction = false;
    public $transaction_content = null;
    public $engine = 'InnoDB';

    public function prepare(string $query, ...$args): string
    {
        return preg_replace_callback('/%[ds]/', static function (array $match) use (&$args): string {
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
        if (false !== strpos($query, 'SELECT post_content FROM wp_posts WHERE ID = 47')) {
            return $GLOBALS['tmd_home_battery_cta_test_page']['post_content'];
        }
        return null;
    }

    public function get_row(string $query, $output = null)
    {
        if (false === strpos($query, 'FROM wp_posts WHERE ID = 47 FOR UPDATE')) {
            return null;
        }
        return [
            'ID' => 47,
            'post_type' => $GLOBALS['tmd_home_battery_cta_test_page']['post_type'],
            'post_content' => $GLOBALS['tmd_home_battery_cta_test_page']['post_content'],
        ];
    }

    public function query(string $query)
    {
        if ('START TRANSACTION' === $query) {
            $this->transaction_content = $GLOBALS['tmd_home_battery_cta_test_page']['post_content'];
            $this->in_transaction = true;
        } elseif ('COMMIT' === $query) {
            $this->transaction_content = null;
            $this->in_transaction = false;
        } elseif ('ROLLBACK' === $query && $this->in_transaction) {
            $GLOBALS['tmd_home_battery_cta_test_page']['post_content'] = $this->transaction_content;
            $this->transaction_content = null;
            $this->in_transaction = false;
        }
        return 0;
    }
}

function clean_post_cache(int $post_id): void {}
function get_post(int $post_id)
{
    if (47 !== $post_id) {
        return null;
    }
    return (object) array_merge(['ID' => 47], $GLOBALS['tmd_home_battery_cta_test_page']);
}
function wp_update_post(array $data, bool $wp_error = false)
{
    if (47 !== (int) ($data['ID'] ?? 0)) {
        return new WP_Error();
    }
    $GLOBALS['tmd_home_battery_cta_test_page']['post_content'] = stripslashes((string) $data['post_content']);
    return 47;
}
function wp_slash(string $value): string { return addslashes($value); }
function is_wp_error($value): bool { return $value instanceof WP_Error; }
function wp_json_encode($value, int $options = 0) { return json_encode($value, $options); }
function trailingslashit(string $value): string { return rtrim($value, '/') . '/'; }

function tmd_home_battery_cta_transaction_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

function tmd_home_battery_cta_test_remove_tree(string $path): void
{
    foreach (glob($path . '/*') ?: [] as $child) {
        if (is_dir($child) && ! is_link($child)) {
            tmd_home_battery_cta_test_remove_tree($child);
        } else {
            unlink($child);
        }
    }
    if (is_dir($path)) {
        rmdir($path);
    }
}

function tmd_home_battery_cta_test_backup(string $name, string $created_at): string
{
    $directory = $GLOBALS['tmd_home_battery_cta_test_root'] . '/backups/' . $name;
    mkdir($directory, 0700);
    $database_path = $directory . '/database.sql';
    $sql = "-- MariaDB dump\nCREATE TABLE wp_posts (ID BIGINT);\n"
        . str_repeat("-- isolated fixture row\n", 4000)
        . "-- Dump completed on 2026-10-07 00:00:00\n";
    file_put_contents($database_path, $sql);
    chmod($database_path, 0600);
    $manifest = [
        'schema_version' => 1,
        'environment' => 'production',
        'backup_type' => 'full',
        'verified' => true,
        'database_file' => 'database.sql',
        'sql_format' => 'mariadb-dump',
        'sql_header_verified' => true,
        'dump_completion_marker_verified' => true,
        'created_at_utc' => $created_at,
        'restore_path' => '/isolated/test/restore',
        'restore_method' => 'isolated fixture validation',
        'database_size_bytes' => filesize($database_path),
        'database_sha256' => hash_file('sha256', $database_path),
    ];
    file_put_contents($directory . '/BACKUP_MANIFEST.json', json_encode($manifest));
    chmod($directory . '/BACKUP_MANIFEST.json', 0600);
    return $directory;
}

register_shutdown_function(static function () use ($test_root): void {
    tmd_home_battery_cta_test_remove_tree($test_root);
});

$GLOBALS['tmd_home_battery_cta_test_root'] = $test_root;
$fixture = (string) file_get_contents(__DIR__ . '/fixtures/home-battery-cta-content.html');
$GLOBALS['tmd_home_battery_cta_test_page'] = ['post_type' => 'page', 'post_content' => $fixture];
$GLOBALS['wpdb'] = new TMD_Home_Battery_CTA_Test_WPDB();

$args = ['dry-run'];
require dirname(__DIR__) . '/scripts/update-home-battery-cta.php';
$spec = tmd_home_battery_cta_spec();
$transformed = tmd_home_battery_cta_transform($fixture);
tmd_home_battery_cta_transaction_assert([] === $transformed['errors'], 'el fixture debe transformar antes de probar la transacción');

$original_backup = tmd_home_battery_cta_test_backup('original', gmdate('Y-m-d\TH:i:s\Z'));
putenv('TMD_VERIFIED_BACKUP_PATH=' . $original_backup);
putenv('TMD_HOME_BATTERY_CTA_EXECUTE=1');
tmd_home_battery_cta_run(true);
tmd_home_battery_cta_transaction_assert(
    $transformed['content'] === $GLOBALS['tmd_home_battery_cta_test_page']['post_content'],
    'execute con backup verificado debe guardar el destino exacto'
);
$snapshots = glob($original_backup . '/home-battery-cta-before-*.json') ?: [];
tmd_home_battery_cta_transaction_assert(1 === count($snapshots), 'execute debe crear un snapshot de rollback verificable');
$snapshot_path = $snapshots[0];

$manifest_path = $original_backup . '/BACKUP_MANIFEST.json';
$manifest = json_decode((string) file_get_contents($manifest_path), true);
$manifest['created_at_utc'] = gmdate('Y-m-d\TH:i:s\Z', time() - 10800);
file_put_contents($manifest_path, json_encode($manifest));
chmod($manifest_path, 0600);
$external_content = $transformed['content'] . "\n<!-- external edit -->";
$GLOBALS['tmd_home_battery_cta_test_page']['post_content'] = $external_content;
$current_backup = tmd_home_battery_cta_test_backup('rollback-conflict', gmdate('Y-m-d\TH:i:s\Z'));
putenv('TMD_VERIFIED_BACKUP_PATH=' . $current_backup);
putenv('TMD_HOME_BATTERY_CTA_ORIGINAL_BACKUP_PATH=' . $original_backup);
putenv('TMD_HOME_BATTERY_CTA_SNAPSHOT_PATH=' . $snapshot_path);
putenv('TMD_HOME_BATTERY_CTA_ROLLBACK=1');

$rejected = null;
try {
    tmd_home_battery_cta_rollback();
} catch (RuntimeException $exception) {
    $rejected = $exception;
}
tmd_home_battery_cta_transaction_assert($rejected instanceof RuntimeException, 'rollback debe rechazarse si la portada cambió después del deploy');
tmd_home_battery_cta_transaction_assert(
    false !== strpos($rejected->getMessage(), 'cambió después del deploy'),
    'el rechazo debe explicar que el contenido posterior se preservó'
);
tmd_home_battery_cta_transaction_assert(
    $external_content === $GLOBALS['tmd_home_battery_cta_test_page']['post_content'],
    'un conflicto no debe sobrescribir cambios externos'
);

$GLOBALS['tmd_home_battery_cta_test_page']['post_content'] = $transformed['content'];
$current_backup = tmd_home_battery_cta_test_backup('rollback-success', gmdate('Y-m-d\TH:i:s\Z'));
putenv('TMD_VERIFIED_BACKUP_PATH=' . $current_backup);
tmd_home_battery_cta_rollback();
tmd_home_battery_cta_transaction_assert(
    $fixture === $GLOBALS['tmd_home_battery_cta_test_page']['post_content'],
    'rollback con backup reciente debe restaurar el snapshot aunque el backup original tenga más de dos horas'
);
tmd_home_battery_cta_transaction_assert(
    false !== strpos(implode("\n", array_column(WP_CLI::$messages, 1)), 'Rollback focalizado verificado'),
    'rollback debe informar que la restauración fue verificada'
);

fwrite(STDOUT, "OK: escritura protegida, rollback condicional y backup original anterior a dos horas.\n");
