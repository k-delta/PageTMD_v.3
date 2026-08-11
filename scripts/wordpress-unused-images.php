<?php
/**
 * Audit or delete unused WordPress image attachments.
 *
 * Usage:
 *   wp eval-file scripts/wordpress-unused-images.php dry-run
 *   wp eval-file scripts/wordpress-unused-images.php execute HASH BACKUP-VERIFIED DELETE-APPROVED
 */

if (! defined('WP_CLI') || ! WP_CLI) {
    fwrite(STDERR, "Este script debe ejecutarse mediante WP-CLI.\n");
    exit(1);
}

global $wpdb;

$environment_mode = getenv('TMD_MEDIA_MODE');
$environment_hash = getenv('TMD_MEDIA_MANIFEST_HASH');
$environment_backup = getenv('TMD_MEDIA_BACKUP_CONFIRMATION');
$environment_delete = getenv('TMD_MEDIA_DELETE_CONFIRMATION');

$mode = is_string($environment_mode) && $environment_mode !== ''
    ? $environment_mode
    : ($args[0] ?? 'dry-run');
$expected_hash = is_string($environment_hash) && $environment_hash !== ''
    ? $environment_hash
    : ($args[1] ?? '');
$backup_confirmation = is_string($environment_backup) && $environment_backup !== ''
    ? $environment_backup
    : ($args[2] ?? '');
$delete_confirmation = is_string($environment_delete) && $environment_delete !== ''
    ? $environment_delete
    : ($args[3] ?? '');

if (! in_array($mode, ['dry-run', 'manifest', 'execute'], true)) {
    WP_CLI::error('Modo inválido. Use dry-run, manifest o execute.');
}

/**
 * Return the relative original and generated file paths for an attachment.
 */
function tmd_unused_image_paths(int $attachment_id): array
{
    $relative = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $metadata = wp_get_attachment_metadata($attachment_id);
    $backup_sizes = get_post_meta($attachment_id, '_wp_attachment_backup_sizes', true);
    $paths = [];

    if ($relative !== '') {
        $paths[] = $relative;
    }

    $directory = $relative !== '' ? dirname($relative) : '';
    $directory = $directory === '.' ? '' : $directory;

    if (is_array($metadata)) {
        foreach (($metadata['sizes'] ?? []) as $size) {
            if (! empty($size['file'])) {
                $paths[] = ltrim($directory . '/' . $size['file'], '/');
            }
        }

        if (! empty($metadata['original_image'])) {
            $paths[] = ltrim($directory . '/' . $metadata['original_image'], '/');
        }
    }

    if (is_array($backup_sizes)) {
        foreach ($backup_sizes as $size) {
            if (! empty($size['file'])) {
                $paths[] = ltrim($directory . '/' . $size['file'], '/');
            }
        }
    }

    $paths = array_values(array_unique(array_filter($paths, 'strlen')));
    sort($paths, SORT_STRING);

    return $paths;
}

/**
 * Build conservative path and URL tokens. False positives exclude deletion.
 */
function tmd_unused_image_tokens(int $attachment_id, array $paths): array
{
    $uploads = wp_get_upload_dir();
    $tokens = [];

    foreach ($paths as $path) {
        $tokens[] = $path;
        $tokens[] = wp_basename($path);
        $tokens[] = trailingslashit($uploads['baseurl']) . $path;
        $tokens[] = '/wp-content/uploads/' . $path;
    }

    $attachment_url = wp_get_attachment_url($attachment_id);
    if (is_string($attachment_url) && $attachment_url !== '') {
        $tokens[] = $attachment_url;
    }

    $tokens = array_values(array_unique(array_filter($tokens, static function ($token) {
        return is_string($token) && strlen($token) >= 4;
    })));
    usort($tokens, static fn($left, $right) => strlen($right) <=> strlen($left));

    return $tokens;
}

/**
 * Return SQL LIKE clauses and values for a set of columns and tokens.
 */
function tmd_unused_image_like_parts(array $columns, array $tokens): array
{
    global $wpdb;

    $clauses = [];
    $values = [];

    foreach ($columns as $column) {
        foreach ($tokens as $token) {
            $clauses[] = sprintf('`%s` LIKE %%s', str_replace('`', '', $column));
            $values[] = '%' . $wpdb->esc_like($token) . '%';
        }
    }

    return [$clauses, $values];
}

/**
 * Count references without returning stored content.
 */
function tmd_unused_image_count_like(
    string $table,
    array $columns,
    array $tokens,
    string $where = '1=1',
    array $where_values = []
): int {
    global $wpdb;

    if ($tokens === [] || $columns === []) {
        return 0;
    }

    [$clauses, $values] = tmd_unused_image_like_parts($columns, $tokens);
    $safe_table = '`' . str_replace('`', '', $table) . '`';
    $sql = "SELECT COUNT(*) FROM {$safe_table} WHERE ({$where}) AND (" . implode(' OR ', $clauses) . ')';
    $prepared = $wpdb->prepare($sql, array_merge($where_values, $values));

    return (int) $wpdb->get_var($prepared);
}

/**
 * Find structured ID references that do not contain an attachment path.
 */
function tmd_unused_image_id_references(int $attachment_id): array
{
    global $wpdb;

    $id = (string) $attachment_id;
    $serialized_string = sprintf('s:%d:"%s";', strlen($id), $id);
    $patterns = [
        'wp-image-' . $id,
        'wp-image-' . $id . ' ',
        '"id":' . $id,
        '"id": ' . $id,
        '"mediaId":' . $id,
        '"mediaId": ' . $id,
        '"localID":' . $id,
        '"localID": ' . $id,
        'i:' . $id . ';',
        $serialized_string,
    ];
    $references = [];

    $post_count = tmd_unused_image_count_like(
        $wpdb->posts,
        ['post_content', 'post_excerpt'],
        $patterns,
        'ID <> %d',
        [$attachment_id]
    );
    if ($post_count > 0) {
        $references[] = ['source' => 'posts.structured-id', 'matches' => $post_count];
    }

    $meta_sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE NOT (post_id = %d AND meta_key IN ('_wp_attached_file','_wp_attachment_metadata','_wp_attachment_backup_sizes')) AND (meta_value = %s OR meta_value LIKE %s OR meta_value LIKE %s)",
        $attachment_id,
        $id,
        '%' . $wpdb->esc_like('i:' . $id . ';') . '%',
        '%' . $wpdb->esc_like($serialized_string) . '%'
    );
    $meta_count = (int) $wpdb->get_var($meta_sql);
    if ($meta_count > 0) {
        $references[] = ['source' => 'postmeta.structured-id', 'matches' => $meta_count];
    }

    $option_sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value = %s OR option_value LIKE %s OR option_value LIKE %s",
        $id,
        '%' . $wpdb->esc_like('i:' . $id . ';') . '%',
        '%' . $wpdb->esc_like($serialized_string) . '%'
    );
    $option_count = (int) $wpdb->get_var($option_sql);
    if ($option_count > 0) {
        $references[] = ['source' => 'options.structured-id', 'matches' => $option_count];
    }

    return $references;
}

/**
 * Find path and URL references in canonical WordPress storage.
 */
function tmd_unused_image_database_references(int $attachment_id, array $tokens): array
{
    global $wpdb;

    $sources = [
        [
            'name' => 'posts',
            'table' => $wpdb->posts,
            'columns' => ['post_content', 'post_excerpt', 'guid'],
            'where' => 'ID <> %d',
            'values' => [$attachment_id],
        ],
        [
            'name' => 'postmeta',
            'table' => $wpdb->postmeta,
            'columns' => ['meta_value'],
            'where' => "NOT (post_id = %d AND meta_key IN ('_wp_attached_file','_wp_attachment_metadata','_wp_attachment_backup_sizes'))",
            'values' => [$attachment_id],
        ],
        ['name' => 'options', 'table' => $wpdb->options, 'columns' => ['option_value']],
        ['name' => 'termmeta', 'table' => $wpdb->termmeta, 'columns' => ['meta_value']],
        ['name' => 'term_taxonomy', 'table' => $wpdb->term_taxonomy, 'columns' => ['description']],
        ['name' => 'usermeta', 'table' => $wpdb->usermeta, 'columns' => ['meta_value']],
        ['name' => 'links', 'table' => $wpdb->links, 'columns' => ['link_url', 'link_image', 'link_description', 'link_notes']],
    ];
    $references = [];

    foreach ($sources as $source) {
        $count = tmd_unused_image_count_like(
            $source['table'],
            $source['columns'],
            $tokens,
            $source['where'] ?? '1=1',
            $source['values'] ?? []
        );
        if ($count > 0) {
            $references[] = ['source' => $source['name'] . '.path-or-url', 'matches' => $count];
        }
    }

    $special_tables = [
        $wpdb->prefix . 'kb_optimizer' => ['content'],
        $wpdb->prefix . 'rank_math_internal_links' => ['url_to', 'url_to_full'],
        $wpdb->prefix . 'snippets' => ['code'],
    ];

    foreach ($special_tables as $table => $columns) {
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
        if ($exists !== $table) {
            continue;
        }

        $available = $wpdb->get_col('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`');
        $columns = array_values(array_intersect($columns, $available));
        $count = tmd_unused_image_count_like($table, $columns, $tokens);
        if ($count > 0) {
            $references[] = ['source' => $table . '.path-or-url', 'matches' => $count];
        }
    }

    return array_merge($references, tmd_unused_image_id_references($attachment_id));
}

/**
 * Find references in source and generated text files, excluding caches and backups.
 */
function tmd_unused_image_file_references(array $tokens): array
{
    $uploads = wp_get_upload_dir();
    $roots = [get_theme_root(), WP_PLUGIN_DIR, WPMU_PLUGIN_DIR, $uploads['basedir']];
    $extensions = ['css', 'html', 'htm', 'js', 'json', 'php', 'svg', 'txt', 'xml'];
    $references = [];
    $seen = [];

    foreach (array_unique($roots) as $root) {
        if (! is_dir($root)) {
            continue;
        }

        $directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator($directory, static function ($current) {
            if (! $current->isDir()) {
                return true;
            }
            return ! in_array($current->getFilename(), ['cache', 'upgrade', 'backups', 'ai1wm-backups'], true);
        });
        $iterator = new RecursiveIteratorIterator($filter);

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), $extensions, true)) {
                continue;
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                continue;
            }

            $path = $file->getPathname();
            if (isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $contents = file_get_contents($path);
            if (! is_string($contents)) {
                continue;
            }

            foreach ($tokens as $token) {
                if (strpos($contents, $token) !== false) {
                    $references[] = [
                        'source' => 'file',
                        'path' => str_replace(ABSPATH, '', $path),
                    ];
                    break;
                }
            }

            if (count($references) >= 20) {
                return $references;
            }
        }
    }

    return $references;
}

/**
 * Calculate the current bytes for all known files belonging to an attachment.
 */
function tmd_unused_image_file_bytes(array $paths): int
{
    $uploads = wp_get_upload_dir();
    $total = 0;

    foreach ($paths as $path) {
        $absolute = trailingslashit($uploads['basedir']) . $path;
        if (is_file($absolute)) {
            $total += (int) filesize($absolute);
        }
    }

    return $total;
}

/**
 * Emit JSON to stdout or to an explicitly requested temporary path.
 */
function tmd_unused_image_emit(array $payload): void
{
    $json = wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $output_path = getenv('TMD_MEDIA_OUTPUT_PATH');

    if (is_string($output_path) && $output_path !== '') {
        $normalized = wp_normalize_path($output_path);
        if (dirname($normalized) !== '/tmp' || pathinfo($normalized, PATHINFO_EXTENSION) !== 'json') {
            WP_CLI::error('TMD_MEDIA_OUTPUT_PATH debe ser un archivo JSON directo bajo /tmp.');
        }
        if (file_put_contents($normalized, $json . PHP_EOL, LOCK_EX) === false) {
            WP_CLI::error('No se pudo escribir el resultado temporal de la auditoría.');
        }
        return;
    }

    echo $json . PHP_EOL;
}

/**
 * Produce a fresh audit from the current database and filesystem state.
 */
function tmd_unused_image_audit(): array
{
    global $wpdb;

    $ids = $wpdb->get_col(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID"
    );
    $candidates = [];
    $excluded = [];

    foreach ($ids as $raw_id) {
        $attachment_id = (int) $raw_id;
        $post = get_post($attachment_id);
        $paths = tmd_unused_image_paths($attachment_id);
        $tokens = tmd_unused_image_tokens($attachment_id, $paths);
        $references = [];

        if ((int) $post->post_parent > 0) {
            $references[] = ['source' => 'posts.post_parent', 'matches' => 1];
        }

        $references = array_merge(
            $references,
            tmd_unused_image_database_references($attachment_id, $tokens)
        );

        if ($references === []) {
            $references = tmd_unused_image_file_references($tokens);
        }

        $entry = [
            'attachmentId' => $attachment_id,
            'name' => get_the_title($attachment_id),
            'url' => (string) wp_get_attachment_url($attachment_id),
            'mime' => (string) get_post_mime_type($attachment_id),
            'date' => (string) get_post_field('post_date_gmt', $attachment_id),
            'relativePath' => (string) get_post_meta($attachment_id, '_wp_attached_file', true),
            'derivedPaths' => $paths,
            'bytes' => tmd_unused_image_file_bytes($paths),
        ];

        if ($references === []) {
            $candidates[] = $entry;
        } else {
            $excluded[] = [
                'attachmentId' => $attachment_id,
                'references' => $references,
            ];
        }
    }

    $manifest_json = wp_json_encode($candidates, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return [
        'environment' => wp_get_environment_type(),
        'audited' => count($ids),
        'candidates' => $candidates,
        'candidateCount' => count($candidates),
        'candidateBytes' => array_sum(array_column($candidates, 'bytes')),
        'excludedCount' => count($excluded),
        'excluded' => $excluded,
        'manifestHash' => 'sha256:' . hash('sha256', $manifest_json),
    ];
}

$candidate_ids_value = getenv('TMD_MEDIA_CANDIDATE_IDS');

if ($mode === 'manifest') {
    $candidate_ids = array_values(array_unique(array_filter(array_map(
        'absint',
        explode(',', is_string($candidate_ids_value) ? $candidate_ids_value : '')
    ))));

    if ($candidate_ids === []) {
        WP_CLI::error('TMD_MEDIA_CANDIDATE_IDS debe contener al menos un ID.');
    }

    $candidates = [];
    foreach ($candidate_ids as $attachment_id) {
        $post = get_post($attachment_id);
        if (! $post instanceof WP_Post || $post->post_type !== 'attachment' || strpos((string) $post->post_mime_type, 'image/') !== 0) {
            WP_CLI::error('El ID ' . $attachment_id . ' no es un adjunto de imagen vigente.');
        }

        $paths = tmd_unused_image_paths($attachment_id);
        $candidates[] = [
            'attachmentId' => $attachment_id,
            'name' => get_the_title($attachment_id),
            'url' => (string) wp_get_attachment_url($attachment_id),
            'mime' => (string) get_post_mime_type($attachment_id),
            'date' => (string) get_post_field('post_date_gmt', $attachment_id),
            'relativePath' => (string) get_post_meta($attachment_id, '_wp_attached_file', true),
            'derivedPaths' => $paths,
            'bytes' => tmd_unused_image_file_bytes($paths),
        ];
    }

    $manifest_json = wp_json_encode($candidates, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    tmd_unused_image_emit([
        'environment' => wp_get_environment_type(),
        'audited' => (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
        ),
        'candidates' => $candidates,
        'candidateCount' => count($candidates),
        'candidateBytes' => array_sum(array_column($candidates, 'bytes')),
        'manifestHash' => 'sha256:' . hash('sha256', $manifest_json),
    ]);
    return;
}

$audit = tmd_unused_image_audit();

if ($mode === 'dry-run') {
    tmd_unused_image_emit($audit);
    return;
}

if ($expected_hash === '' || ! hash_equals($audit['manifestHash'], $expected_hash)) {
    WP_CLI::error('El hash no coincide con el manifiesto vigente. Genere y apruebe un dry-run nuevo.');
}

if ($backup_confirmation !== 'BACKUP-VERIFIED') {
    WP_CLI::error('Falta confirmar un backup verificado con BACKUP-VERIFIED.');
}

if ($delete_confirmation !== 'DELETE-APPROVED') {
    WP_CLI::error('Falta confirmar el manifiesto aprobado con DELETE-APPROVED.');
}

$result = [
    'manifestHash' => $audit['manifestHash'],
    'audited' => $audit['audited'],
    'deleted' => [],
    'skipped' => [],
    'failed' => [],
];

foreach ($audit['candidates'] as $candidate) {
    $attachment_id = (int) $candidate['attachmentId'];
    $fresh_paths = tmd_unused_image_paths($attachment_id);
    $fresh_tokens = tmd_unused_image_tokens($attachment_id, $fresh_paths);
    $fresh_references = tmd_unused_image_database_references($attachment_id, $fresh_tokens);
    $fresh_post = get_post($attachment_id);

    if ($fresh_post instanceof WP_Post && (int) $fresh_post->post_parent > 0) {
        $fresh_references[] = ['source' => 'posts.post_parent', 'matches' => 1];
    }

    if ($fresh_references !== [] || tmd_unused_image_file_references($fresh_tokens) !== []) {
        $result['skipped'][] = [
            'attachmentId' => $attachment_id,
            'reason' => 'reference-found',
        ];
        continue;
    }

    $deleted = wp_delete_attachment($attachment_id, true);
    if ($deleted instanceof WP_Post) {
        $result['deleted'][] = $attachment_id;
    } else {
        $result['failed'][] = [
            'attachmentId' => $attachment_id,
            'reason' => 'wp-delete-attachment-failed',
        ];
    }
}

tmd_unused_image_emit($result);
