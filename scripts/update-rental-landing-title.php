<?php
/**
 * Cambia únicamente el H1 visible de la página de alquiler (ID 1558).
 * El modo es dry-run salvo que TMD_RENTAL_TITLE_UPDATE_EXECUTE=1.
 */

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

function tmd_commercial_landing_title_only_content(string $content): string
{
    $source = '<h1 id="tmd-rental-v2-heading">Alquiler de <span>montacargas eléctricos</span></h1>';
    $target = '<h1 id="tmd-rental-v2-heading">Venta o alquiler de <span>montacargas eléctricos</span></h1>';
    $source_count = substr_count($content, $source);
    $target_count = substr_count($content, $target);

    if (0 === $source_count && 1 === $target_count) {
        return $content;
    }
    if (1 !== $source_count || 0 !== $target_count) {
        throw new RuntimeException('El H1 esperado debe aparecer una sola vez y sin duplicar el destino.');
    }

    return str_replace($source, $target, $content);
}

function tmd_commercial_landing_title_only_backup_is_valid(): bool
{
    $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
    if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_readable($backup_path)) {
        return false;
    }

    $database_path = $backup_path . '/database.sql';
    $manifest_path = $backup_path . '/BACKUP_MANIFEST.json';
    if (! is_file($database_path) || is_link($database_path) || ! is_readable($database_path)
        || ! is_file($manifest_path) || is_link($manifest_path) || ! is_readable($manifest_path)) {
        return false;
    }

    $backup_mode = fileperms($backup_path);
    $database_mode = fileperms($database_path);
    $manifest_mode = fileperms($manifest_path);
    if (false === $backup_mode || false === $database_mode || false === $manifest_mode
        || 0 !== ($backup_mode & 0077) || 0 !== ($database_mode & 0077) || 0 !== ($manifest_mode & 0077)) {
        return false;
    }

    $wordpress_root = realpath(ABSPATH);
    if (is_string($wordpress_root)
        && str_starts_with($backup_path . DIRECTORY_SEPARATOR, rtrim($wordpress_root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
        return false;
    }

    $manifest = json_decode((string) file_get_contents($manifest_path), true);
    if (! is_array($manifest)
        || 1 !== ($manifest['schema_version'] ?? 0)
        || 'production' !== ($manifest['environment'] ?? '')
        || 'full' !== ($manifest['backup_type'] ?? '')
        || true !== ($manifest['verified'] ?? false)
        || 'database.sql' !== ($manifest['database_file'] ?? '')
        || 'mariadb-dump' !== ($manifest['sql_format'] ?? '')
        || true !== ($manifest['sql_header_verified'] ?? false)
        || true !== ($manifest['dump_completion_marker_verified'] ?? false)
        || ! is_string($manifest['created_at_utc'] ?? null)
        || ! is_string($manifest['restore_path'] ?? null) || '' === trim($manifest['restore_path'])
        || ! is_string($manifest['restore_method'] ?? null) || '' === trim($manifest['restore_method'])) {
        return false;
    }

    $created_at = strtotime($manifest['created_at_utc']);
    if (false === $created_at || $created_at > time() || time() - $created_at > 7200) {
        return false;
    }

    $database_size = filesize($database_path);
    $database_hash = hash_file('sha256', $database_path);
    if (! is_int($database_size) || $database_size < 65536 || ! is_string($database_hash)
        || (int) ($manifest['database_size_bytes'] ?? 0) !== $database_size
        || ! is_string($manifest['database_sha256'] ?? null)
        || ! hash_equals(strtolower($manifest['database_sha256']), strtolower($database_hash))) {
        return false;
    }

    $header = file_get_contents($database_path, false, null, 0, 65536);
    $handle = fopen($database_path, 'rb');
    if (! is_string($header) || false === $handle) {
        return false;
    }
    $tail_offset = max(0, $database_size - 16384);
    if (0 !== fseek($handle, $tail_offset)) {
        fclose($handle);
        return false;
    }
    $tail = fread($handle, 16384);
    fclose($handle);

    return (bool) preg_match('/(?:MariaDB|MySQL) dump/i', $header)
        && (bool) preg_match('/CREATE TABLE/i', $header)
        && is_string($tail)
        && (bool) preg_match('/-- Dump completed on /i', $tail);
}

function tmd_commercial_landing_title_only_save_snapshot(string $backup_path, WP_Post $page): string
{
    $resolved = realpath($backup_path);
    if (! is_string($resolved) || ! is_dir($resolved) || ! is_writable($resolved)) {
        throw new RuntimeException('No se pudo resolver la carpeta privada del backup para el rollback.');
    }
    $directory_mode = fileperms($resolved);
    if (false === $directory_mode || 0 !== ($directory_mode & 0077)) {
        throw new RuntimeException('La carpeta del backup debe conservar permisos privados.');
    }

    $payload = [
        'schema_version' => 1,
        'post_id' => 1558,
        'post_type' => $page->post_type,
        'post_name' => $page->post_name,
        'post_status' => $page->post_status,
        'post_title' => $page->post_title,
        'post_content_sha256' => hash('sha256', (string) $page->post_content),
        'post_content' => (string) $page->post_content,
    ];
    $encoded = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded)) {
        throw new RuntimeException('No se pudo serializar el snapshot de rollback.');
    }

    $artifact = $resolved . '/rental-title-page-1558-before.json';
    $handle = @fopen($artifact, 'x');
    if (! is_resource($handle)) {
        throw new RuntimeException('El snapshot privado ya existe o no pudo crearse.');
    }
    @chmod($artifact, 0600);
    $written = fwrite($handle, $encoded);
    $flushed = fflush($handle);
    fclose($handle);
    @chmod($artifact, 0600);

    $saved = json_decode((string) file_get_contents($artifact), true);
    $artifact_mode = fileperms($artifact);
    if (false === $written || $written !== strlen($encoded) || ! $flushed
        || false === $artifact_mode || 0600 !== ($artifact_mode & 0777)
        || ! is_array($saved) || 1558 !== ($saved['post_id'] ?? 0)
        || ! hash_equals($payload['post_content_sha256'], hash('sha256', (string) ($saved['post_content'] ?? '')))) {
        @unlink($artifact);
        throw new RuntimeException('El snapshot privado de rollback no pasó la comprobación de integridad.');
    }

    return $artifact;
}

function tmd_commercial_landing_title_only_run(bool $execute): void
{
    $lock_path = trailingslashit(get_temp_dir()) . 'tmd-rental-title-update.lock';
    $lock = @fopen($lock_path, 'c');
    if (! is_resource($lock) || ! flock($lock, LOCK_EX | LOCK_NB)) {
        is_resource($lock) && fclose($lock);
        throw new RuntimeException('No se pudo adquirir el bloqueo exclusivo del contenido comercial.');
    }
    @chmod($lock_path, 0600);

    try {
        clean_post_cache(1558);
        $page = get_post(1558);
        if (! $page instanceof WP_Post || 'page' !== $page->post_type
            || 'alquiler-montacargas-electricos' !== $page->post_name || 'publish' !== $page->post_status
            || 'Alquiler de montacargas eléctricos' !== $page->post_title) {
            throw new RuntimeException('La página 1558 no coincide con el ID, slug, estado y título esperados.');
        }

        $target_content = tmd_commercial_landing_title_only_content((string) $page->post_content);
        $hashes = [
            'source' => hash('sha256', (string) $page->post_content),
            'target' => hash('sha256', $target_content),
        ];
        $expected_names = [
            'source' => 'TMD_RENTAL_TITLE_EXPECTED_CONTENT_SHA256',
            'target' => 'TMD_RENTAL_TITLE_TARGET_CONTENT_SHA256',
        ];
        foreach ($expected_names as $key => $environment_name) {
            $provided = trim((string) getenv($environment_name));
            if ('' === $provided && ! $execute) {
                continue;
            }
            if (! preg_match('/\A[a-f0-9]{64}\z/', $provided) || ! hash_equals($provided, $hashes[$key])) {
                throw new RuntimeException('El hash ' . $environment_name . ' está ausente o no coincide; no se modificó contenido.');
            }
        }

        WP_CLI::line('Página 1558, H1 solamente: ' . $hashes['source'] . ' → ' . $hashes['target'] . '.');
        WP_CLI::line('Título administrativo, slug, formulario CF7 y Rank Math: sin cambios.');
        if (! $execute) {
            WP_CLI::success('Dry-run sin escrituras.');
            return;
        }
        if (hash_equals($hashes['source'], $hashes['target'])) {
            WP_CLI::success('El H1 destino ya está guardado; no se escribió contenido.');
            return;
        }
        if (! tmd_commercial_landing_title_only_backup_is_valid()) {
            throw new RuntimeException('Se requiere un backup completo, reciente y verificado antes de escribir.');
        }

        $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
        if (! is_string($backup_path)) {
            throw new RuntimeException('No se pudo resolver la ruta del backup verificado.');
        }
        $artifact = tmd_commercial_landing_title_only_save_snapshot($backup_path, $page);

        global $wpdb;
        $engine = $wpdb->get_var($wpdb->prepare(
            'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
            $wpdb->posts
        ));
        if ('INNODB' !== strtoupper((string) $engine)) {
            throw new RuntimeException('La tabla de páginas no usa InnoDB; no se inició la actualización.');
        }
        if (false === $wpdb->query('START TRANSACTION')) {
            throw new RuntimeException('No se pudo iniciar la transacción del contenido.');
        }

        $commit_attempted = false;
        $transaction_confirmed = false;
        try {
            $locked_page = $wpdb->get_row($wpdb->prepare(
                "SELECT ID, post_type, post_name, post_status, post_title, post_content FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
                1558
            ), ARRAY_A);
            if (! is_array($locked_page) || 'page' !== ($locked_page['post_type'] ?? '')
                || 'alquiler-montacargas-electricos' !== ($locked_page['post_name'] ?? '')
                || 'publish' !== ($locked_page['post_status'] ?? '')
                || 'Alquiler de montacargas eléctricos' !== ($locked_page['post_title'] ?? '')
                || ! hash_equals($hashes['source'], hash('sha256', (string) ($locked_page['post_content'] ?? '')))) {
                throw new RuntimeException('La página cambió antes de escribir; la transacción se revertirá.');
            }

            $result = wp_update_post(['ID' => 1558, 'post_content' => wp_slash($target_content)], true);
            if (is_wp_error($result) || 1558 !== (int) $result) {
                throw new RuntimeException('WordPress no confirmó la actualización de la página 1558.');
            }

            clean_post_cache(1558);
            $verified_page = get_post(1558);
            if (! $verified_page instanceof WP_Post
                || 'Alquiler de montacargas eléctricos' !== $verified_page->post_title
                || 'alquiler-montacargas-electricos' !== $verified_page->post_name
                || 'publish' !== $verified_page->post_status
                || ! hash_equals($hashes['target'], hash('sha256', (string) $verified_page->post_content))) {
                throw new RuntimeException('La verificación de la página 1558 falló; la transacción se revertirá.');
            }

            $commit_attempted = true;
            if (false === $wpdb->query('COMMIT')) {
                throw new RuntimeException('No se confirmó la transacción de contenido.');
            }
            $transaction_confirmed = true;
        } catch (Throwable $exception) {
            $rollback_result = $wpdb->query('ROLLBACK');
            clean_post_cache(1558);
            $persisted_page = get_post(1558);
            $source_persisted = $persisted_page instanceof WP_Post
                && hash_equals($hashes['source'], hash('sha256', (string) $persisted_page->post_content));
            $target_persisted = $persisted_page instanceof WP_Post
                && hash_equals($hashes['target'], hash('sha256', (string) $persisted_page->post_content));

            if ($source_persisted) {
                throw new RuntimeException(
                    $exception->getMessage() . ' Se verificó que el contenido sigue en su estado original.'
                    . (false === $rollback_result ? ' El resultado informado por ROLLBACK fue incierto.' : ''),
                    0,
                    $exception
                );
            }
            if ($target_persisted && $commit_attempted) {
                $transaction_confirmed = true;
                WP_CLI::warning('El COMMIT fue ambiguo, pero el hash destino está persistido.');
            } else {
                throw new RuntimeException(
                    'El estado persistido no coincide con origen ni destino. Conserva el artefacto de rollback y detén nuevos writes.',
                    0,
                    $exception
                );
            }
        }

        if (! $transaction_confirmed) {
            throw new RuntimeException('No se pudo confirmar el resultado de la transacción.');
        }
        clean_post_cache(1558);
        $persisted_page = get_post(1558);
        if (! $persisted_page instanceof WP_Post
            || ! hash_equals($hashes['target'], hash('sha256', (string) $persisted_page->post_content))) {
            throw new RuntimeException('La verificación posterior al COMMIT no coincide; conserva el artefacto de rollback.');
        }
        if (function_exists('do_action')) {
            do_action('litespeed_purge_post', 1558);
        }
        WP_CLI::line('Snapshot privado de rollback verificado: ' . basename($artifact) . '.');
        WP_CLI::success('El H1 de la página 1558 se actualizó y verificó; CF7 y Rank Math no se modificaron.');
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

if ('yes' === strtolower(trim((string) getenv('TMD_RENTAL_TITLE_UPDATE_RUN')))) {
    try {
        tmd_commercial_landing_title_only_run('1' === (string) getenv('TMD_RENTAL_TITLE_UPDATE_EXECUTE'));
    } catch (Throwable $exception) {
        WP_CLI::error($exception->getMessage());
    }
}
