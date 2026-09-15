<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('WP_CLI', true);

final class TmdJobsVacanciesExecuteState
{
    public static $updates = 0;
    public static $updated_post = [];
    public static $messages = [];
    public static $queries = [];
    public static $persisted_content = '';
    public static $cache_clears = 0;
}

final class TmdJobsVacanciesExecuteDb
{
    public $posts = 'wp_posts';

    public function query($query)
    {
        TmdJobsVacanciesExecuteState::$queries[] = (string) $query;
        return true;
    }

    public function prepare($query, $value): string
    {
        return str_replace('%d', (string) (int) $value, $query);
    }

    public function get_row($query)
    {
        return (object) [
            'ID'           => 273,
            'post_type'    => 'page',
            'post_content' => TmdJobsVacanciesExecuteState::$persisted_content,
        ];
    }

    public function get_var($query)
    {
        return TmdJobsVacanciesExecuteState::$persisted_content;
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
        TmdJobsVacanciesExecuteState::$messages[] = (string) $message;
    }

    public static function success($message): void
    {
        TmdJobsVacanciesExecuteState::$messages[] = (string) $message;
    }
}

function tmd_jobs_vacancies_execute_test_assert(bool $condition, string $message): void
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

tmd_jobs_vacancies_execute_test_assert('' !== $content, 'Debe existir el contenido de prueba de la página 273.');

$GLOBALS['tmd_jobs_vacancies_execute_page'] = (object) [
    'post_type'    => 'page',
    'post_content' => $content,
];
$GLOBALS['wpdb'] = new TmdJobsVacanciesExecuteDb();
TmdJobsVacanciesExecuteState::$persisted_content = $content;

function get_post($id)
{
    return 273 === (int) $id ? $GLOBALS['tmd_jobs_vacancies_execute_page'] : null;
}

function wp_update_post($post, $wp_error = false)
{
    TmdJobsVacanciesExecuteState::$updates++;
    TmdJobsVacanciesExecuteState::$updated_post = $post;
    TmdJobsVacanciesExecuteState::$persisted_content = (string) $post['post_content'];
    return 273;
}

function is_wp_error($value): bool
{
    return false;
}

function clean_post_cache($id): void
{
    TmdJobsVacanciesExecuteState::$cache_clears++;
}

$args = ['execute'];
require dirname(__DIR__) . '/scripts/update-jobs-vacancies.php';

$messages = implode("\n", TmdJobsVacanciesExecuteState::$messages);
$updated_content = (string) (TmdJobsVacanciesExecuteState::$updated_post['post_content'] ?? '');

tmd_jobs_vacancies_execute_test_assert(1 === TmdJobsVacanciesExecuteState::$updates, 'Execute debe llamar una vez a wp_update_post().');
tmd_jobs_vacancies_execute_test_assert(273 === (int) (TmdJobsVacanciesExecuteState::$updated_post['ID'] ?? 0), 'Execute debe actualizar la página 273.');
tmd_jobs_vacancies_execute_test_assert(false !== strpos($updated_content, 'Técnico Especializado en Montacargas Eléctricos'), 'El payload debe incluir la vacante eléctrica actualizada.');
tmd_jobs_vacancies_execute_test_assert(false !== strpos($updated_content, 'Técnico Especializado en Montacargas de Combustión'), 'El payload debe incluir la tercera vacante.');
tmd_jobs_vacancies_execute_test_assert(3 === substr_count($updated_content, 'class="tmd-job-card"'), 'El payload debe contener tres tarjetas.');
tmd_jobs_vacancies_execute_test_assert(['START TRANSACTION', 'COMMIT'] === TmdJobsVacanciesExecuteState::$queries, 'Execute debe confirmar una transacción protegida.');
tmd_jobs_vacancies_execute_test_assert(1 === TmdJobsVacanciesExecuteState::$cache_clears, 'Execute debe limpiar la caché de la página actualizada.');
tmd_jobs_vacancies_execute_test_assert(false !== strpos($messages, 'Vacantes de Trabaja con nosotros actualizadas:'), 'Execute debe informar la actualización exitosa.');

echo "OK: WP-CLI execute escribe el payload esperado dentro de una transacción.\n";
