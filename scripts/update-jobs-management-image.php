<?php
/**
 * Reemplaza la imagen externa de la seccion "Nuestro equipo" en Trabaja con nosotros
 * por el attachment de WordPress cuyo archivo es gerencia.webp.
 */

defined('ABSPATH') || exit;

function tmd_jobs_management_old_image_url(): string
{
    return 'https://lh3.googleusercontent.com/aida-public/AB6AXuDNN1UrDHJ22A-yQhRXAl2KS9ZqI7KmKuUkWTX04b2YCqdTar2XVnF8xG0-VNgeX8ihLGjjbRaNTF5RIqVh_3kHS0bPk669bj3m4uSlcYxSdCZbEoBLNcrKUhxHEHbC48FDa49pea9aI2F5xKjiw1Ly5kCkE3zLifptQsizvzEmlNfSFmM9h6h9bmFH4V2qls5YzL-fJBUjlGIIKnwPvWLg4WiIiyZ_Ivu2UxBOdN2I0zue9eryrF_pLk0qHz2d0GzJyguYIkXfTw';
}

function tmd_jobs_management_find_attachment(): array
{
    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.ID, pm.meta_value
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm
                ON pm.post_id = p.ID
               AND pm.meta_key = '_wp_attached_file'
             WHERE p.post_type = 'attachment'
               AND p.post_status = 'inherit'
               AND pm.meta_value LIKE %s
             ORDER BY p.ID DESC",
            '%' . $wpdb->esc_like('/gerencia.webp')
        ),
        ARRAY_A
    );

    if (count($rows) !== 1) {
        return [
            'error' => sprintf(
                'Se esperaba exactamente un attachment gerencia.webp y se encontraron %d.',
                count($rows)
            ),
        ];
    }

    $attachment_id = (int) $rows[0]['ID'];
    $url = wp_get_attachment_url($attachment_id);

    if (! is_string($url) || $url === '') {
        return ['error' => 'No fue posible resolver la URL publica de gerencia.webp.'];
    }

    $mime = (string) get_post_mime_type($attachment_id);
    if ($mime !== 'image/webp') {
        return [
            'error' => sprintf(
                'El attachment gerencia.webp no es WebP; MIME encontrado: %s.',
                $mime !== '' ? $mime : '(vacio)'
            ),
        ];
    }

    return [
        'id' => $attachment_id,
        'url' => $url,
        'file' => (string) $rows[0]['meta_value'],
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
                'La imagen de la maleta no coincide de forma univoca (anterior=%d, nueva=%d).',
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
