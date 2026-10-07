<?php
/**
 * Crea las páginas publicadas de agradecimiento para baterías y montacargas.
 *
 * Dry-run (predeterminado):
 *   wp eval-file scripts/create-commercial-landing-thank-you-pages.php
 *
 * Escritura protegida después de validar el backup completo:
 *   TMD_COMMERCIAL_THANK_YOU_PAGES_EXECUTE=1 \
 *   TMD_VERIFIED_BACKUP_PATH=/ruta/backup-validado \
 *   wp eval-file scripts/create-commercial-landing-thank-you-pages.php
 */

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

require_once get_stylesheet_directory() . '/inc/tmd-commercial-landing-thank-you.php';
require_once __DIR__ . '/commercial-backup-validation.php';

function tmd_commercial_thank_you_page_matches($page, array $spec): bool
{
    if (! $page instanceof WP_Post
        || 'page' !== $page->post_type
        || 'publish' !== $page->post_status
        || $spec['slug'] !== $page->post_name
        || $spec['title'] !== $page->post_title
        || 0 !== (int) ($page->post_parent ?? 0)
        || ! hash_equals(
            hash('sha256', $spec['shortcode']),
            hash('sha256', (string) $page->post_content)
        )) {
        return false;
    }

    $robots_rows = get_post_meta((int) $page->ID, 'rank_math_robots', false);
    return is_array($robots_rows)
        && 1 === count($robots_rows)
        && $spec['robots'] === $robots_rows[0];
}

function tmd_commercial_thank_you_pages_save_snapshot(string $backup_path, array $specs, array $existing): string
{
    $backup_path = realpath($backup_path);
    if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_writable($backup_path)) {
        throw new RuntimeException('No se pudo localizar la carpeta privada del backup para guardar el rollback.');
    }
    $directory_mode = fileperms($backup_path);
    if (false === $directory_mode || 0 !== ($directory_mode & 0077)) {
        throw new RuntimeException('La carpeta del backup debe conservar permisos privados para el snapshot.');
    }

    $before = [];
    $targets = [];
    foreach ($specs as $type => $spec) {
        $page = $existing[$type] ?? null;
        $before[$type] = $page instanceof WP_Post
            ? [
                'post_id' => (int) $page->ID,
                'post_type' => (string) $page->post_type,
                'post_name' => (string) $page->post_name,
                'post_status' => (string) $page->post_status,
                'post_title' => (string) $page->post_title,
                'post_parent' => (int) ($page->post_parent ?? 0),
                'post_content' => (string) $page->post_content,
                'rank_math_robots_rows' => get_post_meta((int) $page->ID, 'rank_math_robots', false),
            ]
            : null;
        $targets[$type] = [
            'slug' => $spec['slug'],
            'title' => $spec['title'],
            'post_content_sha256' => hash('sha256', $spec['shortcode']),
            'rank_math_robots' => $spec['robots'],
        ];
    }

    $payload = [
        'schema_version' => 1,
        'created_at_utc' => gmdate('Y-m-d\TH:i:s\Z'),
        'operation' => 'create-commercial-thank-you-pages',
        'before' => $before,
        'targets' => $targets,
    ];
    $encoded = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded)) {
        throw new RuntimeException('No se pudo serializar el snapshot privado de restauración.');
    }

    $artifact_path = $backup_path . '/commercial-thank-you-pages-before-'
        . gmdate('Ymd\THis\Z') . '-' . bin2hex(random_bytes(6)) . '.json';
    $handle = @fopen($artifact_path, 'x');
    if (! is_resource($handle)) {
        throw new RuntimeException('No se pudo crear el snapshot privado de restauración.');
    }
    @chmod($artifact_path, 0600);
    $written = fwrite($handle, $encoded);
    $flushed = fflush($handle);
    fclose($handle);
    @chmod($artifact_path, 0600);

    $saved = json_decode((string) file_get_contents($artifact_path), true);
    $artifact_mode = fileperms($artifact_path);
    if (false === $written || $written !== strlen($encoded) || ! $flushed
        || false === $artifact_mode || 0600 !== ($artifact_mode & 0777)
        || ! is_array($saved)
        || 'create-commercial-thank-you-pages' !== ($saved['operation'] ?? '')) {
        @unlink($artifact_path);
        throw new RuntimeException('El snapshot privado de restauración no pasó su comprobación de integridad.');
    }

    return $artifact_path;
}

function tmd_commercial_thank_you_transaction_is_active(): ?bool
{
    global $wpdb;

    $active = $wpdb->get_var('SELECT @@in_transaction');
    if ('' !== (string) ($wpdb->last_error ?? '')) {
        return null;
    }
    if (in_array((string) $active, ['1', '1.0'], true)) {
        return true;
    }
    if (in_array((string) $active, ['0', '0.0'], true)) {
        return false;
    }

    return null;
}

function tmd_commercial_thank_you_pages_original_state_matches(array $specs, array $existing): bool
{
    foreach ($specs as $type => $spec) {
        $current = get_page_by_path($spec['slug'], OBJECT, 'page');
        $before = $existing[$type] ?? null;
        if ($before instanceof WP_Post) {
            clean_post_cache((int) $before->ID);
            $current = get_post((int) $before->ID);
            if (! $current instanceof WP_Post
                || (int) $before->ID !== (int) $current->ID
                || ! tmd_commercial_thank_you_page_matches($current, $spec)) {
                return false;
            }
        } elseif ($current instanceof WP_Post) {
            return false;
        }
    }

    return true;
}

function tmd_commercial_thank_you_pages_target_state_matches(array $specs): bool
{
    foreach ($specs as $spec) {
        $located = get_page_by_path($spec['slug'], OBJECT, 'page');
        if (! $located instanceof WP_Post) {
            return false;
        }
        clean_post_cache((int) $located->ID);
        $page = get_post((int) $located->ID);
        if (! tmd_commercial_thank_you_page_matches($page, $spec)) {
            return false;
        }
    }

    return true;
}

function tmd_commercial_thank_you_pages_run(bool $execute): void
{
    global $wpdb;

    $specs = tmd_commercial_thank_you_page_specs();
    if (2 !== count($specs) || ! isset($specs['battery'], $specs['forklift'])) {
        throw new RuntimeException('La configuración canónica debe definir únicamente baterías y montacargas.');
    }

    $lock = null;
    if ($execute) {
        $temp_directory = realpath(get_temp_dir());
        if (! function_exists('posix_geteuid')) {
            throw new RuntimeException('Se requiere posix_geteuid para verificar la propiedad del bloqueo exclusivo.');
        }
        $effective_uid = posix_geteuid();
        if (! is_string($temp_directory) || ! is_dir($temp_directory)) {
            throw new RuntimeException('No se pudo resolver el directorio temporal privado para el bloqueo.');
        }
        $lock_directory = trailingslashit($temp_directory) . 'tmd-commercial-thank-you-pages-' . $effective_uid;
        if (! is_dir($lock_directory) && ! @mkdir($lock_directory, 0700) && ! is_dir($lock_directory)) {
            throw new RuntimeException('No se pudo crear el directorio privado del bloqueo de páginas de agradecimiento.');
        }
        $directory_stat = lstat($lock_directory);
        if (is_link($lock_directory) || false === $directory_stat
            || (($directory_stat['mode'] & 0170000) !== 0040000)
            || 0 !== ($directory_stat['mode'] & 0077)
            || $effective_uid !== (int) $directory_stat['uid']) {
            throw new RuntimeException('El directorio del bloqueo no es privado o no pertenece al usuario de WP-CLI.');
        }

        $lock_path = trailingslashit($lock_directory) . 'pages.lock';
        if (is_link($lock_path)) {
            throw new RuntimeException('El archivo de bloqueo no puede ser un enlace simbólico.');
        }
        $old_umask = umask(0077);
        $lock = @fopen($lock_path, 'c');
        umask($old_umask);
        if (! is_resource($lock)) {
            throw new RuntimeException('No se pudo abrir el bloqueo exclusivo de páginas de agradecimiento.');
        }
        $file_stat = fstat($lock);
        $path_stat = lstat($lock_path);
        if (false === $file_stat || false === $path_stat || is_link($lock_path)
            || (($file_stat['mode'] & 0170000) !== 0100000)
            || $file_stat['dev'] !== $path_stat['dev']
            || $file_stat['ino'] !== $path_stat['ino']
            || 0 !== ($file_stat['mode'] & 0077)
            || $effective_uid !== (int) $file_stat['uid']
            || ! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            $lock = null;
            throw new RuntimeException('No se pudo adquirir un bloqueo privado y exclusivo de páginas de agradecimiento.');
        }
    }

    try {
        $existing = [];
        $pending = [];
        foreach ($specs as $type => $spec) {
            if (! is_array($spec)
                || ! is_string($spec['slug'] ?? null)
                || ! is_string($spec['title'] ?? null)
                || ! is_string($spec['shortcode'] ?? null)
                || ! is_array($spec['robots'] ?? null)) {
                throw new RuntimeException('La configuración de ' . $type . ' está incompleta.');
            }

            $page = get_page_by_path($spec['slug'], OBJECT, 'page');
            if ($page instanceof WP_Post && ! tmd_commercial_thank_you_page_matches($page, $spec)) {
                throw new RuntimeException('Conflicto con la página /' . $spec['slug'] . '/; no se sobrescribió contenido ni metadatos.');
            }
            $existing[$type] = $page instanceof WP_Post ? $page : null;
            if (! $page instanceof WP_Post) {
                $pending[$type] = $spec;
            }
        }

        if ([] === $pending) {
            WP_CLI::success('Las dos páginas de agradecimiento ya coinciden con su configuración y se conservaron sin cambios.');
            return;
        }

        if (! $execute) {
            foreach ($pending as $spec) {
                WP_CLI::line('Página /' . $spec['slug'] . '/: se crearía y publicaría con robots noindex, follow.');
            }
            WP_CLI::success('Dry-run sin escrituras. Para crear las páginas se requiere ejecución protegida y backup validado.');
            return;
        }

        if (! tmd_commercial_landing_script_backup_is_valid()) {
            throw new RuntimeException('Se requiere un backup completo, reciente y verificado antes de escribir.');
        }
        $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
        if (! is_string($backup_path)) {
            throw new RuntimeException('No se pudo resolver la ruta del backup verificado.');
        }

        foreach ([$wpdb->posts, $wpdb->postmeta] as $table) {
            $engine = $wpdb->get_var($wpdb->prepare(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
                $table
            ));
            if ('INNODB' !== strtoupper((string) $engine)) {
                throw new RuntimeException('Las tablas de contenido y metadatos deben usar InnoDB antes de crear las páginas.');
            }
        }
        tmd_commercial_thank_you_pages_save_snapshot($backup_path, $specs, $existing);

        if (false === $wpdb->query('START TRANSACTION')) {
            throw new RuntimeException('No se pudo iniciar la transacción de las páginas de agradecimiento.');
        }
        if (true !== tmd_commercial_thank_you_transaction_is_active()) {
            $wpdb->query('ROLLBACK');
            throw new RuntimeException('La transacción no quedó activa; no se crearon páginas.');
        }

        $created_ids = [];
        try {
            foreach ($specs as $type => $spec) {
                $locked_page = $wpdb->get_row($wpdb->prepare(
                    "SELECT ID, post_type, post_name, post_parent, post_status, post_title, post_content FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s AND post_parent = %d FOR UPDATE",
                    'page',
                    $spec['slug'],
                    0
                ), ARRAY_A);
                if (! is_array($locked_page) && '' !== (string) ($wpdb->last_error ?? '')) {
                    throw new RuntimeException('No se pudo bloquear el slug /' . $spec['slug'] . '/.');
                }

                $before = $existing[$type] ?? null;
                if ($before instanceof WP_Post) {
                    clean_post_cache((int) $before->ID);
                    $current = get_page_by_path($spec['slug'], OBJECT, 'page');
                    if (! is_array($locked_page)
                        || (int) $before->ID !== (int) ($locked_page['ID'] ?? 0)
                        || ! tmd_commercial_thank_you_page_matches($current, $spec)) {
                        throw new RuntimeException('La página /' . $spec['slug'] . '/ cambió antes del bloqueo; no se modificó.');
                    }
                    continue;
                }
                if (is_array($locked_page)) {
                    if (0 === (int) ($locked_page['post_parent'] ?? -1)) {
                        throw new RuntimeException('Conflicto de slug para /' . $spec['slug'] . '/ detectado durante el bloqueo.');
                    }
                    throw new RuntimeException('La página anidada /' . $spec['slug'] . '/ cambió durante el bloqueo; no se modificó.');
                }

                $page_id = wp_insert_post([
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_name' => $spec['slug'],
                    'post_title' => $spec['title'],
                    'post_content' => wp_slash($spec['shortcode']),
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                ], true);
                if (is_wp_error($page_id) || (int) $page_id < 1) {
                    throw new RuntimeException('WordPress no confirmó la creación de la página /' . $spec['slug'] . '/.');
                }

                $page_id = (int) $page_id;
                $created_ids[] = $page_id;
                if (false === update_post_meta($page_id, 'rank_math_robots', $spec['robots'])) {
                    throw new RuntimeException('No se pudieron guardar los robots Rank Math de /' . $spec['slug'] . '/.');
                }
                clean_post_cache($page_id);
                $created_page = get_post($page_id);
                if (! tmd_commercial_thank_you_page_matches($created_page, $spec)) {
                    throw new RuntimeException('La comprobación posterior falló para /' . $spec['slug'] . '/ (hash de contenido o metadatos).');
                }
            }

            if (! tmd_commercial_thank_you_pages_target_state_matches($specs)) {
                throw new RuntimeException('La comprobación final de slugs, estado, hash de contenido o Rank Math falló.');
            }

            $commit_result = $wpdb->query('COMMIT');
            $transaction_active = tmd_commercial_thank_you_transaction_is_active();
            if (false === $commit_result) {
                if (false === $transaction_active && tmd_commercial_thank_you_pages_target_state_matches($specs)) {
                    WP_CLI::warning('COMMIT informó un resultado ambiguo, pero las dos páginas coinciden con el estado destino persistido.');
                } else {
                    throw new RuntimeException('No se pudo confirmar el COMMIT de las páginas de agradecimiento.');
                }
            } elseif (false !== $transaction_active) {
                throw new RuntimeException('No se pudo confirmar el cierre de la transacción de las páginas de agradecimiento.');
            }
            if (! tmd_commercial_thank_you_pages_target_state_matches($specs)) {
                throw new RuntimeException('La verificación durable posterior al COMMIT no coincide con las páginas objetivo.');
            }
        } catch (Throwable $exception) {
            $rollback_result = $wpdb->query('ROLLBACK');
            foreach ($created_ids as $created_id) {
                clean_post_cache($created_id);
            }
            $transaction_active = tmd_commercial_thank_you_transaction_is_active();
            if (false === $rollback_result || false !== $transaction_active) {
                throw new RuntimeException(
                    'No se pudo confirmar el cierre del rollback. Conserva el snapshot privado y detén cualquier reintento.',
                    0,
                    $exception
                );
            }
            if (! tmd_commercial_thank_you_pages_original_state_matches($specs, $existing)) {
                throw new RuntimeException(
                    'El estado persistido no coincide con el origen. Conserva el snapshot privado y detén cualquier reintento.',
                    0,
                    $exception
                );
            }
            throw new RuntimeException(
                $exception->getMessage() . ' Se verificó el rollback y el estado original de las páginas.',
                0,
                $exception
            );
        }

        WP_CLI::success('Páginas /gracias-baterias/ y /gracias-montacargas/ creadas y verificadas con Rank Math noindex, follow.');
    } finally {
        if (is_resource($lock)) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}

try {
    tmd_commercial_thank_you_pages_run('1' === getenv('TMD_COMMERCIAL_THANK_YOU_PAGES_EXECUTE'));
} catch (Throwable $exception) {
    WP_CLI::error($exception->getMessage());
}
