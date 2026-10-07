<?php

define('ABSPATH', sys_get_temp_dir() . '/tmd-home-battery-cta-cli/');
define('WP_CLI', true);

class WP_CLI
{
    public static $messages = [];
    public static function line(string $message): void { self::$messages[] = ['line', $message]; }
    public static function success(string $message): void { self::$messages[] = ['success', $message]; }
    public static function error(string $message): void { throw new RuntimeException($message); }
}

function tmd_home_battery_cta_cli_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

$content = (string) file_get_contents(__DIR__ . '/fixtures/home-battery-cta-content.html');
$GLOBALS['tmd_home_battery_cta_cli_content'] = $content;
$GLOBALS['tmd_home_battery_cta_cli_updates'] = 0;

function get_post(int $post_id)
{
    return 47 === $post_id
        ? (object) ['ID' => 47, 'post_type' => 'page', 'post_content' => $GLOBALS['tmd_home_battery_cta_cli_content']]
        : null;
}
function clean_post_cache(int $post_id): void {}
function wp_update_post(array $data, bool $wp_error = false) { ++$GLOBALS['tmd_home_battery_cta_cli_updates']; return (int) $data['ID']; }
function wp_slash(string $value): string { return addslashes($value); }
function is_wp_error($value): bool { return false; }
function wp_json_encode($value, int $options = 0) { return json_encode($value, $options); }
function trailingslashit(string $value): string { return rtrim($value, '/') . '/'; }

$scenario = $argv[1] ?? 'dry-run';
putenv('TMD_VERIFIED_BACKUP_PATH');
if ('execute-no-backup' === $scenario) {
    putenv('TMD_HOME_BATTERY_CTA_EXECUTE=1');
    $args = ['execute'];
} elseif ('dry-run' === $scenario) {
    putenv('TMD_HOME_BATTERY_CTA_EXECUTE');
    $args = [];
} else {
    fwrite(STDERR, "FAIL: escenario desconocido.\n");
    exit(1);
}

$caught = null;
try {
    require dirname(__DIR__) . '/scripts/update-home-battery-cta.php';
} catch (RuntimeException $exception) {
    $caught = $exception;
}

if ('dry-run' === $scenario) {
    tmd_home_battery_cta_cli_assert(null === $caught, 'El dry-run debe finalizar correctamente.');
    tmd_home_battery_cta_cli_assert(
        false !== strpos(implode("\n", array_column(WP_CLI::$messages, 1)), 'Dry-run sin escrituras'),
        'El dry-run debe informar que no escribió contenido.'
    );
} else {
    tmd_home_battery_cta_cli_assert($caught instanceof RuntimeException, 'Execute sin backup debe detenerse.');
    tmd_home_battery_cta_cli_assert(
        false !== strpos($caught->getMessage(), 'backup completo, reciente y verificado'),
        'El rechazo debe identificar el backup pendiente.'
    );
}
tmd_home_battery_cta_cli_assert(0 === $GLOBALS['tmd_home_battery_cta_cli_updates'], 'Ningún escenario debe escribir antes de un backup verificado.');

fwrite(STDOUT, "OK: {$scenario}.\n");
