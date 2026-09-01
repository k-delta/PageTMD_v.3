<?php
/**
 * Reemplaza la imagen externa de la seccion "Nuestro equipo" en Trabaja con nosotros
 * por el attachment de WordPress identificado como gerencia-scaled-e1787869020907.webp.
 */

defined('ABSPATH') || exit;

function tmd_jobs_management_target_filename(): string
{
    return 'gerencia-scaled-e1787869020907.webp';
}

function tmd_jobs_management_attached_file_matches(string $attached_file): bool
{
    return strtolower(basename(rawurldecode($attached_file))) === strtolower(tmd_jobs_management_target_filename());
}

function tmd_jobs_management_old_image_url(): string
{
    return 'https://lh3.googleusercontent.com/aida-public/AB6AXuDNN1UrDHJ22A-yQhRXAl2KS9ZqI7KmKuUkWTX04b2YCqdTar2XVnF8xG0-VNgeX8ihLGjjbRaNTF5RIqVh_3kHS0bPk669bj3m4uSlcYxSdCZbEoBLNcrKUhxHEHbC48FDa49pea9aI2F5xKjiw1Ly5kCkE3zLifptQsizvzEmlNfSFmM9h6h9bmFH4V2qls5YzL-fJBUjlGIIKnwPvWLg4WiIiyZ_Ivu2UxBOdN2I0zue9eryrF_pLk0qHz2d0GzJyguYIkXfTw';
}

function tmd_jobs_management_attachment_candidates(): array
{
    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.ID,
                    p.post_title,
                    p.post_name,
                    p.post_mime_type,
                    pm.meta_value AS attached_file
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm
                ON pm.post_id = p.ID
               AND pm.meta_key = '_wp_attached_file'
             WHERE p.post_type = 'attachment'
               AND p.post_status = 'inherit'
               AND LOWER(SUBSTRING_INDEX(pm.meta_value, '/', -1)) = %s
             ORDER BY p.ID DESC",
            strtolower(tmd_jobs_management_target_filename())
        ),
        ARRAY_A
    );

    $rows = array_values(array_filter($rows, static function (array $row): bool {
        return tmd_jobs_management_attached_file_matches((string) $row['attached_file']);
    }));

    $unique = [];

    foreach ($rows as $row) {
        $id = (int) $row['ID'];

        if (! isset($unique[$id])) {
            $unique[$id] = $row;
        }
    }

    return array_values($unique);
}

function tmd_jobs_management_describe_candidates(array $rows): string
{
    if ($rows === []) {
        return '(ninguno)';
    }

    $parts = [];

    foreach ($rows as $row) {
        $parts[] = sprintf(
            '#%d title=%s slug=%s mime=%s file=%s',
            (int) $row['ID'],
            (string) $row['post_title'],
            (string) $row['post_name'],
            (string) $row['post_mime_type'],
            (string) $row['attached_file']
        );
    }

    return implode(' | ', $parts);
}

function tmd_jobs_management_find_attachment(): array
{
    global $wpdb;

    $rows = tmd_jobs_management_attachment_candidates();
    $webp_rows = array_values(array_filter($rows, static function (array $row): bool {
        return strtolower((string) $row['post_mime_type']) === 'image/webp';
    }));

    if (count($webp_rows) !== 1) {
        $nearby = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID,
                        p.post_title,
                        p.post_name,
                        p.post_mime_type,
                        pm.meta_value AS attached_file
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm
                    ON pm.post_id = p.ID
                   AND pm.meta_key = '_wp_attached_file'
                 WHERE p.post_type = 'attachment'
                   AND p.post_status = 'inherit'
                   AND (
                        LOWER(p.post_title) LIKE %s
                        OR LOWER(p.post_name) LIKE %s
                        OR LOWER(pm.meta_value) LIKE %s
                   )
                 ORDER BY p.ID DESC
                 LIMIT 10",
                '%' . strtolower(tmd_jobs_management_target_filename()) . '%',
                '%' . strtolower(tmd_jobs_management_target_filename()) . '%',
                '%' . strtolower(tmd_jobs_management_target_filename()) . '%'
            ),
            ARRAY_A
        );

        return [
            'error' => sprintf(
                'No se pudo resolver de forma univoca %s (candidatos WebP=%d). Coincidencias directas: %s. Coincidencias cercanas: %s.',
                tmd_jobs_management_target_filename(),
                count($webp_rows),
                tmd_jobs_management_describe_candidates($rows),
                tmd_jobs_management_describe_candidates($nearby)
            ),
        ];
    }

    $row = $webp_rows[0];
    $attachment_id = (int) $row['ID'];
    $url = wp_get_attachment_url($attachment_id);

    if (! is_string($url) || $url === '') {
        return ['error' => 'No fue posible resolver la URL publica del attachment objetivo.'];
    }

    return [
        'id' => $attachment_id,
        'url' => $url,
        'file' => (string) $row['attached_file'],
        'title' => (string) $row['post_title'],
        'slug' => (string) $row['post_name'],
    ];
}

function tmd_jobs_management_transform(string $content, string $new_url): array
{
    $old_url = tmd_jobs_management_old_image_url();
    $old_count = substr_count($content, $old_url);
    $new_count = substr_count($content, $new_url);

    if ($old_count === 0 && $new_count === 1) {
        return [
            'changed' => false,
            'content' => $content,
            'changes' => [],
            'errors' => [],
        ];
    }

    if ($old_count !== 1) {
        return [
            'changed' => false,
            'content' => $content,
            'changes' => [],
            'errors' => [sprintf(
                'La imagen anterior no coincide de forma univoca (anterior=%d, nueva=%d).',
                $old_count,
                $new_count
            )],
        ];
    }

    if (stripos($content, 'NUESTRO EQUIPO') === false) {
        return [
            'changed' => false,
            'content' => $content,
            'changes' => [],
            'errors' => ['No se encontro el bloque esperado NUESTRO EQUIPO.'],
        ];
    }

    $updated = str_replace($old_url, $new_url, $content, $count);

    if ($count !== 1) {
        return [
            'changed' => false,
            'content' => $content,
            'changes' => [],
            'errors' => ['No fue posible reemplazar exactamente una imagen.'],
        ];
    }

    return [
        'changed' => true,
        'content' => $updated,
        'changes' => ['imagen-nuestro-equipo'],
        'errors' => [],
    ];
}

if (defined('WP_CLI') && WP_CLI) {
    $page = get_page_by_path('nosotros/trabaja-con-nosotros', OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        WP_CLI::error('No se encontro la pagina nosotros/trabaja-con-nosotros.');
    }

    $attachment = tmd_jobs_management_find_attachment();

    if (isset($attachment['error'])) {
        WP_CLI::error($attachment['error']);
    }

    $result = tmd_jobs_management_transform((string) $page->post_content, $attachment['url']);

    if (! empty($result['errors'])) {
        WP_CLI::error(implode(' ', $result['errors']));
    }

    WP_CLI::log('page_id=' . (int) $page->ID);
    WP_CLI::log('attachment_id=' . (int) $attachment['id']);
    WP_CLI::log('attachment_title=' . $attachment['title']);
    WP_CLI::log('attachment_slug=' . $attachment['slug']);
    WP_CLI::log('attachment_file=' . $attachment['file']);
    WP_CLI::log('attachment_url=' . $attachment['url']);

    $dry_run = getenv('TMD_DRY_RUN') === '1';

    if (! $result['changed']) {
        WP_CLI::success('La imagen de Nuestro equipo ya estaba actualizada.');
        return;
    }

    WP_CLI::log('cambios=' . implode(',', $result['changes']));

    if ($dry_run) {
        WP_CLI::success('Dry-run OK: se reemplazaria imagen-nuestro-equipo.');
        return;
    }

    $updated = wp_update_post([
        'ID' => (int) $page->ID,
        'post_content' => $result['content'],
    ], true);

    if (is_wp_error($updated)) {
        WP_CLI::error($updated->get_error_message());
    }

    WP_CLI::success('Trabaja con nosotros actualizado: imagen-nuestro-equipo.');
}
