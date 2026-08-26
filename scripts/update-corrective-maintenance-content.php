<?php
/** Idempotent content migration for Mantenimiento correctivo (page 290). */
function tmd_corr_replace(string $content, string $label, string $old, string $new, array &$changes, array &$errors): string {
    $oc = substr_count($content, $old);
    $nc = substr_count($content, $new);
    if (1 === $oc) {
        $content = str_replace($old, $new, $content, $count);
        if (1 !== $count) {
            $errors[] = sprintf('%s: reemplazos=%d.', $label, $count);
            return $content;
        }
        $changes[] = $label;
        return $content;
    }
    if (0 === $oc && $nc >= 1) { return $content; }
    $errors[] = sprintf('%s: precondicion invalida (anterior=%d, nuevo=%d).', $label, $oc, $nc);
    return $content;
}

function tmd_corrective_replacements(): array {
    return array_merge(
        require __DIR__ . '/corrective-maintenance-replacements-core.php',
        require __DIR__ . '/corrective-maintenance-replacements-detail.php'
    );
}

function tmd_transform_corrective_maintenance_content(string $content): array {
    $original = $content;
    $changes = [];
    $errors = [];
    foreach (tmd_corrective_replacements() as [$label, $old, $new]) {
        $content = tmd_corr_replace($content, $label, $old, $new, $changes, $errors);
    }
    return ['content' => $content, 'changes' => $changes, 'errors' => $errors, 'changed' => $content !== $original];
}

if (! defined('WP_CLI') || ! WP_CLI) { return; }
$page_id = 290;
$page = get_post($page_id);
if (! $page || 'page' !== $page->post_type) { WP_CLI::error("No existe la página de Mantenimiento correctivo esperada con ID {$page_id}."); }
$result = tmd_transform_corrective_maintenance_content((string) $page->post_content);
if (! empty($result['errors'])) { WP_CLI::error("La actualización de Mantenimiento correctivo se detuvo sin escribir:\n- " . implode("\n- ", $result['errors'])); }
if (! $result['changed']) { WP_CLI::success('Mantenimiento correctivo ya contiene los cambios solicitados; no hay cambios.'); return; }
$updated_id = wp_update_post(['ID' => $page_id, 'post_content' => $result['content']], true);
if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar Mantenimiento correctivo: ' . $message);
}
clean_post_cache($page_id);
WP_CLI::success('Mantenimiento correctivo actualizado: ' . implode(', ', $result['changes']));
