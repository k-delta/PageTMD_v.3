<?php
/**
 * Añade BMS-page-2.webp a la página BMS de forma idempotente.
 *
 * Validación sin escritura:
 * wp eval-file scripts/add-bms-page-image.php -- dry-run
 *
 * Ejecución autorizada, después de backup:
 * wp eval-file scripts/add-bms-page-image.php -- execute
 */

if (! function_exists('tmd_bms_page_image_markup')) {
    function tmd_bms_page_image_markup($image_url)
    {
        $escaped_url = function_exists('esc_url')
            ? esc_url($image_url)
            : htmlspecialchars((string) $image_url, ENT_QUOTES, 'UTF-8');
        $alt = 'BMS para monitoreo de baterías de montacargas';
        $escaped_alt = function_exists('esc_attr')
            ? esc_attr($alt)
            : htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

        return "<section class=\"tmd-bms-media-section\" aria-label=\"Imagen del sistema BMS\">\n"
            . "  <div class=\"tmd-bms-wrap\">\n"
            . "    <figure class=\"tmd-bms-media\">\n"
            . "      <img src=\"{$escaped_url}\" alt=\"{$escaped_alt}\" loading=\"lazy\" decoding=\"async\">\n"
            . "    </figure>\n"
            . "  </div>\n"
            . "</section>";
    }
}

if (! function_exists('tmd_bms_matching_attachment_ids')) {
    function tmd_bms_matching_attachment_ids(array $attached_files, string $filename): array
    {
        $matches = [];

        foreach ($attached_files as $attachment_id => $attached_file) {
            $attached_file = rawurldecode((string) $attached_file);
            if ($filename === basename($attached_file)) {
                $matches[] = (int) $attachment_id;
            }
        }

        return array_values(array_unique($matches));
    }
}

if (! function_exists('tmd_bms_find_attachment_ids')) {
    function tmd_bms_find_attachment_ids(string $filename): array
    {
        $attachment_ids = get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image/webp',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => '_wp_attached_file',
                    'value'   => $filename,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        $attached_files = [];
        foreach ($attachment_ids as $attachment_id) {
            $attached_files[(int) $attachment_id] = get_post_meta($attachment_id, '_wp_attached_file', true);
        }

        return tmd_bms_matching_attachment_ids($attached_files, $filename);
    }
}

if (! function_exists('tmd_transform_bms_page_image')) {
    function tmd_transform_bms_page_image($content, $image_url): array
    {
        $original = (string) $content;
        $image_url = trim((string) $image_url);
        $filename = 'BMS-page-2.webp';
        $intro_marker = "<section class=\"tmd-bms-section\">\n    <div class=\"tmd-bms-wrap tmd-bms-intro\">";
        $image_markup = tmd_bms_page_image_markup($image_url);
        $image_markup_count = substr_count($original, $image_markup);
        $filename_count = substr_count($original, $filename);
        $errors = [];

        if ('' === $image_url || ! preg_match('#^https?://#i', $image_url)) {
            $errors[] = 'La URL del adjunto BMS está vacía o no es absoluta.';
        }

        if (1 === $image_markup_count && 1 === $filename_count) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => $errors,
            ];
        }

        if (0 !== $image_markup_count || 0 !== $filename_count) {
            $errors[] = sprintf(
                'Precondición inválida para BMS-page-2.webp (bloque=%d, archivo=%d).',
                $image_markup_count,
                $filename_count
            );
        }

        $intro_marker_count = substr_count($original, $intro_marker);
        if (1 !== $intro_marker_count) {
            $errors[] = sprintf(
                'Se esperaba un único bloque introductorio BMS; encontrados: %d.',
                $intro_marker_count
            );
        }

        if (! empty($errors)) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => $errors,
            ];
        }

        $insert_at = strpos($original, $intro_marker);
        $updated = substr_replace($original, $image_markup . "\n\n", $insert_at, 0);

        return [
            'content' => $updated,
            'changes' => ['imagen:BMS-page-2.webp'],
            'errors'  => [],
        ];
    }
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];
$command_args = array_values(array_filter($command_args, static function ($argument) {
    return '--' !== $argument;
}));

if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/add-bms-page-image.php -- [dry-run|execute]');
}

$page_id = 792;
$filename = 'BMS-page-2.webp';
$page = get_post($page_id);

if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página BMS esperada con ID {$page_id}.");
}

$attachment_ids = tmd_bms_find_attachment_ids($filename);
if (1 !== count($attachment_ids)) {
    WP_CLI::error(sprintf(
        'Se esperaba exactamente un adjunto %s; encontrados: %d. No se escribió contenido.',
        $filename,
        count($attachment_ids)
    ));
}

$image_url = wp_get_attachment_url($attachment_ids[0]);
if (! is_string($image_url) || '' === trim($image_url)) {
    WP_CLI::error("No se pudo resolver la URL del adjunto {$filename}. No se escribió contenido.");
}

$result = tmd_transform_bms_page_image((string) $page->post_content, $image_url);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización BMS se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('La página BMS ya contiene BMS-page-2.webp; no hay cambios.');
    return;
}

WP_CLI::line('Cambio validado: ' . implode(', ', $result['changes']));
if (['execute'] !== $command_args) {
    WP_CLI::success('Dry-run correcto. No se escribió contenido.');
    return;
}

$updated_id = wp_update_post([
    'ID'           => $page_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la página BMS: ' . $message);
}

clean_post_cache($page_id);
WP_CLI::success('Página BMS actualizada con BMS-page-2.webp.');
