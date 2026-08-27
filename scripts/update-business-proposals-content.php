<?php
/**
 * Actualiza de forma idempotente los CTA de Alianzas/Proveedores y elimina
 * Alianzas del selector service del formulario Contact Form 7 ID 14.
 *
 * Dry-run:
 *   wp eval-file scripts/update-business-proposals-content.php
 *
 * Ejecución (solo después de backup verificado):
 *   TMD_BUSINESS_PROPOSALS_EXECUTE=1 \
 *   TMD_VERIFIED_BACKUP_PATH=/ruta/backup-validado \
 *   wp eval-file scripts/update-business-proposals-content.php
 */

function tmd_business_proposals_transform_page_content(string $content): array
{
    $updated = preg_replace(
        '/href=(["\'])\/nosotros\/contacto\/?\1/',
        'href="#tmd-business-proposal-form"',
        $content,
        -1,
        $replacements
    );

    return [
        'content' => is_string($updated) ? $updated : $content,
        'changed' => is_string($updated) && $updated !== $content,
        'replacements' => (int) $replacements,
    ];
}

function tmd_business_proposals_transform_cf7_form(string $form): array
{
    $removed = 0;
    $found_service = false;
    $updated = preg_replace_callback(
        '/\[(?:select|select\*)\s+service\b[^\]]*\]/iu',
        static function (array $match) use (&$removed, &$found_service): string {
            $found_service = true;
            $position = 0;
            $result = preg_replace_callback(
                '/"[^"]*"|\'[^\']*\'|[^\s\]]+/u',
                static function (array $token_match) use (&$position, &$removed): string {
                    $position++;
                    if ($position <= 2) {
                        return $token_match[0];
                    }
                    $token = trim($token_match[0], "\"'");
                    $label = explode('|', $token, 2)[0];
                    if (tmd_business_proposals_content_is_alliance($label)) {
                        $removed++;
                        return '';
                    }
                    return $token_match[0];
                },
                $match[0]
            );
            return is_string($result) ? preg_replace('/\s{2,}/', ' ', $result) : $match[0];
        },
        $form
    );

    return [
        'form' => is_string($updated) ? $updated : $form,
        'changed' => $removed > 0,
        'removed' => $removed,
        'found_service' => $found_service,
    ];
}

function tmd_business_proposals_content_is_alliance(string $value): bool
{
    $value = trim($value);
    if (function_exists('remove_accents')) {
        $value = remove_accents($value);
    } elseif (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = is_string($converted) ? $converted : $value;
    }
    $value = strtolower($value);
    return 'alianza' === $value || 'alianzas' === $value;
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$pages = [];
foreach ([275 => 'Alianzas', 793 => 'Proveedores'] as $page_id => $label) {
    $page = get_post($page_id);
    if (! $page || 'page' !== $page->post_type) {
        WP_CLI::error("No existe la página {$label} esperada con ID {$page_id}.");
    }
    $result = tmd_business_proposals_transform_page_content((string) $page->post_content);
    if (! $result['changed'] && false === strpos((string) $page->post_content, 'href="#tmd-business-proposal-form"')) {
        WP_CLI::error("{$label}: no se encontró el CTA anterior ni el destino idempotente.");
    }
    $pages[$page_id] = ['label' => $label, 'result' => $result];
}

if (! class_exists('WPCF7_ContactForm')) {
    WP_CLI::error('Contact Form 7 no está disponible.');
}
$contact_form = WPCF7_ContactForm::get_instance(14);
if (! $contact_form) {
    WP_CLI::error('No existe el formulario Contact Form 7 ID 14.');
}
$properties = $contact_form->get_properties();
$cf7 = tmd_business_proposals_transform_cf7_form((string) ($properties['form'] ?? ''));
if (! $cf7['found_service']) {
    WP_CLI::error('El formulario ID 14 no contiene el selector service esperado.');
}

$changes = [];
foreach ($pages as $page_id => $entry) {
    if ($entry['result']['changed']) {
        $changes[] = $entry['label'] . ': ' . $entry['result']['replacements'] . ' CTA';
    }
}
if ($cf7['changed']) {
    $changes[] = 'Contacto: opción Alianzas eliminada';
}

if ([] === $changes) {
    WP_CLI::success('El contenido empresarial ya se encuentra actualizado; no hay cambios.');
    return;
}

if ('1' !== getenv('TMD_BUSINESS_PROPOSALS_EXECUTE')) {
    WP_CLI::success('Dry-run sin escrituras. Cambios previstos: ' . implode(', ', $changes));
    return;
}

$backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_readable($backup_path)
    || ! is_file($backup_path . '/database.sql') || filesize($backup_path . '/database.sql') < 1) {
    WP_CLI::error('Ejecución detenida: TMD_VERIFIED_BACKUP_PATH debe apuntar a un backup legible con database.sql no vacío.');
}

foreach ($pages as $page_id => $entry) {
    if (! $entry['result']['changed']) {
        continue;
    }
    $updated_id = wp_update_post(['ID' => $page_id, 'post_content' => $entry['result']['content']], true);
    if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
        WP_CLI::error($entry['label'] . ': no fue posible actualizar la página.');
    }
    clean_post_cache($page_id);
}

if ($cf7['changed']) {
    if (! function_exists('wpcf7_save_contact_form') || ! function_exists('wpcf7_contact_form')) {
        WP_CLI::error('La API soportada de guardado de Contact Form 7 no está disponible; las páginas pueden haber sido actualizadas y deben restaurarse desde el backup indicado.');
    }
    $save_data = array_merge($properties, [
        'id' => 14,
        'title' => $contact_form->title(),
        'locale' => $contact_form->locale(),
        'form' => $cf7['form'],
    ]);
    $saved = wpcf7_save_contact_form($save_data, 'save');
    if (! $saved) {
        WP_CLI::error('Contact Form 7 no confirmó el guardado; las páginas pueden haber sido actualizadas y deben restaurarse desde el backup indicado.');
    }
    $verified_form = wpcf7_contact_form(14);
    $verified_properties = $verified_form ? $verified_form->get_properties() : [];
    $verified = tmd_business_proposals_transform_cf7_form((string) ($verified_properties['form'] ?? ''));
    if (! $verified_form || ! $verified['found_service'] || $verified['changed']) {
        WP_CLI::error('La verificación posterior de Contact Form 7 falló; restaura la operación desde el backup indicado.');
    }
}

WP_CLI::success('Contenido empresarial actualizado: ' . implode(', ', $changes));
