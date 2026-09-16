<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('WP_CLI', true);

final class TmdJobsVacanciesCliState
{
    public static $updates = 0;
    public static $messages = [];
}

class WP_CLI
{
    public static function error($message): void
    {
        throw new RuntimeException((string) $message);
    }

    public static function line($message): void
    {
        TmdJobsVacanciesCliState::$messages[] = (string) $message;
    }

    public static function success($message): void
    {
        TmdJobsVacanciesCliState::$messages[] = (string) $message;
    }
}

function tmd_jobs_vacancies_cli_test_assert(bool $condition, string $message): void
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

tmd_jobs_vacancies_cli_test_assert('' !== $content, 'Debe existir el contenido de prueba de la página 273.');

$GLOBALS['tmd_jobs_vacancies_cli_page'] = (object) [
    'post_type'    => 'page',
    'post_content' => $content,
];

function get_post($id)
{
    return 273 === (int) $id ? $GLOBALS['tmd_jobs_vacancies_cli_page'] : null;
}

function wp_update_post($post, $wp_error = false)
{
    TmdJobsVacanciesCliState::$updates++;
    return 273;
}

function clean_post_cache($id): void
{
}

$args = ['--', 'dry-run'];
require dirname(__DIR__) . '/scripts/update-jobs-vacancies.php';

$messages = implode("\n", TmdJobsVacanciesCliState::$messages);
tmd_jobs_vacancies_cli_test_assert(0 === TmdJobsVacanciesCliState::$updates, 'El dry-run no debe llamar a wp_update_post().');
tmd_jobs_vacancies_cli_test_assert(false !== strpos($messages, 'Dry-run correcto. No se escribió contenido.'), 'El dry-run debe informar que no escribió contenido.');
tmd_jobs_vacancies_cli_test_assert(false !== strpos($messages, 'page_content_sha256='), 'El dry-run debe dejar una huella verificable del contenido validado.');

echo "OK: WP-CLI dry-run sin escritura.\n";
