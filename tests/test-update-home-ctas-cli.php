<?php

define('WP_CLI', true);

class WP_CLI {
    public static $messages = [];

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

function tmd_home_ctas_cli_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function tmd_home_ctas_cli_fixture() {
    $pages = json_decode(
        file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json'),
        true
    );

    foreach ($pages as $page) {
        if (47 === (int) ($page['ID'] ?? 0)) {
            return (string) ($page['post_content'] ?? '');
        }
    }

    return '';
}

$scenario           = $argv[1] ?? 'dry-run';
$mock_content       = tmd_home_ctas_cli_fixture();
$mock_get_post_calls = 0;
$mock_update_calls   = 0;
$mock_updated_content = '';

if ('contradiction' === $scenario) {
    $mock_content = preg_replace(
        '/("uniqueID":"47_e9dd8c-4a"[^\r\n]*)(}) \/-->/',
        '$1,"url":"/destino-incorrecto/"$2 /-->',
        $mock_content,
        1
    );
}

function get_post($post_id) {
    global $mock_content, $mock_get_post_calls;

    ++$mock_get_post_calls;

    return (object) [
        'ID'           => $post_id,
        'post_type'    => 'page',
        'post_content' => $mock_content,
    ];
}

function wp_update_post($data, $wp_error = false) {
    global $mock_update_calls, $mock_updated_content;

    ++$mock_update_calls;
    $mock_updated_content = (string) ($data['post_content'] ?? '');
    return (int) ($data['ID'] ?? 0);
}
function is_wp_error() { return false; }
function clean_post_cache() {}

switch ($scenario) {
    case 'dry-run':
        $args = [];
        break;
    case 'explicit-dry-run':
        $args = ['dry-run'];
        break;
    case 'apply-rejected':
        $args = ['apply'];
        break;
    case 'execute':
        $args = ['execute'];
        break;
    case 'contradiction':
        $args = [];
        break;
    default:
        fwrite(STDERR, "FAIL: escenario desconocido.\n");
        exit(1);
}

$caught = null;

try {
    require dirname(__DIR__) . '/scripts/update-home-ctas.php';
} catch (RuntimeException $error) {
    $caught = $error;
}

if (in_array($scenario, ['dry-run', 'explicit-dry-run', 'execute'], true)) {
    tmd_home_ctas_cli_assert(null === $caught, 'El dry-run no debe fallar.');
} else {
    tmd_home_ctas_cli_assert($caught instanceof RuntimeException, "{$scenario} debe abortar.");
}

tmd_home_ctas_cli_assert(('execute' === $scenario ? 1 : 0) === $mock_update_calls, "{$scenario} debe respetar su modo de escritura.");

if ('execute' === $scenario) {
    tmd_home_ctas_cli_assert(false !== strpos($mock_updated_content, 'Baterías de tracción'), 'execute debe guardar el contenido transformado.');
}

if ('apply-rejected' === $scenario) {
    tmd_home_ctas_cli_assert(
        'Uso: wp eval-file scripts/update-home-ctas.php -- [dry-run|execute]' === $caught->getMessage(),
        'apply debe rechazarse con el error explícito de solo lectura.'
    );
    tmd_home_ctas_cli_assert(0 === $mock_get_post_calls, 'apply debe rechazarse antes de consultar la página.');
}

fwrite(STDOUT, "OK: {$scenario}.\n");
