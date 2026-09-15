<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('WP_CLI', true);

final class TmdJobsVacanciesConcurrencyState
{
    public static $get_post_calls = 0;
    public static $updates = 0;
    public static $queries = [];
}

final class TmdJobsVacanciesConcurrencyDb
{
    public $posts = 'wp_posts';

    public function query($query)
    {
        TmdJobsVacanciesConcurrencyState::$queries[] = (string) $query;
        return true;
    }

    public function prepare($query, $value): string
    {
        return str_replace('%d', (string) (int) $value, $query);
    }

    public function get_row($query)
    {
        tmd_jobs_vacancies_concurrency_test_assert(
            false !== strpos($query, 'FOR UPDATE'),
            'La lectura de execute debe bloquear la fila antes de comparar y escribir.'
        );

        return $GLOBALS['tmd_jobs_vacancies_concurrency_changed'];
    }

    public function get_var($query)
    {
        return null;
    }
}

class WP_CLI
{
    public static function error($message): void
    {
        throw new RuntimeException((string) $message);
    }

    public static function line($message): void
    {
    }

    public static function success($message): void
    {
    }
}

function tmd_jobs_vacancies_concurrency_test_assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$pages = json_decode(
    file_get_contents(dirname(__DIR__) . '/production-snapshot/pages.json'),
    true
);
$content = '';

foreach ($pages as $page) {
    if (273 === (int) ($page['ID'] ?? 0)) {
        $content = (string) ($page['post_content'] ?? '');
        break;
    }
}

tmd_jobs_vacancies_concurrency_test_assert('' !== $content, 'Debe existir el contenido de prueba de la página 273.');

$GLOBALS['tmd_jobs_vacancies_concurrency_original'] = (object) [
    'post_type'    => 'page',
    'post_content' => $content,
];
$GLOBALS['tmd_jobs_vacancies_concurrency_changed'] = (object) [
    'post_type'    => 'page',
    'post_content' => $content . "\n<!-- edición concurrente -->",
];
$GLOBALS['wpdb'] = new TmdJobsVacanciesConcurrencyDb();

function get_post($id)
{
    if (273 !== (int) $id) {
        return null;
    }

    TmdJobsVacanciesConcurrencyState::$get_post_calls++;

    return $GLOBALS['tmd_jobs_vacancies_concurrency_original'];
}

function wp_update_post($post, $wp_error = false)
{
    TmdJobsVacanciesConcurrencyState::$updates++;
    return 273;
}

function is_wp_error($value): bool
{
    return false;
}

function clean_post_cache($id): void
{
}

$args = ['execute'];
$error = '';

try {
    require dirname(__DIR__) . '/scripts/update-jobs-vacancies.php';
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

tmd_jobs_vacancies_concurrency_test_assert(1 === TmdJobsVacanciesConcurrencyState::$get_post_calls, 'Execute debe leer la página inicial antes de obtener el bloqueo transaccional.');
tmd_jobs_vacancies_concurrency_test_assert(0 === TmdJobsVacanciesConcurrencyState::$updates, 'Una edición concurrente debe impedir wp_update_post().');
tmd_jobs_vacancies_concurrency_test_assert(false !== strpos($error, 'cambió mientras se obtenía el bloqueo'), 'La edición concurrente debe producir un bloqueo explícito.');
tmd_jobs_vacancies_concurrency_test_assert(['START TRANSACTION', 'ROLLBACK'] === TmdJobsVacanciesConcurrencyState::$queries, 'Una precondición concurrente debe revertir la transacción sin confirmar cambios.');

echo "OK: execute bloquea edición concurrente antes de escribir.\n";
