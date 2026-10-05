<?php
/**
 * Actualiza de forma atómica la página comercial 1558 y el formulario CF7 1556.
 * El modo predeterminado calcula hashes y no escribe.
 */

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

function tmd_commercial_landing_rental_v2_hash($value): string
{
    $encoded = wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded)) {
        throw new RuntimeException('No se pudo serializar el estado para calcular su hash.');
    }
    return hash('sha256', $encoded);
}

function tmd_commercial_landing_rental_v2_save_rollback_artifact(
    string $backup_path,
    WP_Post $page,
    array $page_meta,
    array $form_properties
): string {
    $resolved_backup = realpath($backup_path);
    if (! is_string($resolved_backup) || ! is_dir($resolved_backup) || ! is_writable($resolved_backup)) {
        throw new RuntimeException('No se pudo localizar la carpeta privada del backup para guardar el rollback.');
    }
    $permissions = fileperms($resolved_backup);
    if (false === $permissions || 0 !== ($permissions & 0077)) {
        throw new RuntimeException('La carpeta del backup debe tener permisos privados para continuar.');
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
        'post_meta' => $page_meta,
        'contact_form_id' => 1556,
        'contact_form_properties_sha256' => tmd_commercial_landing_rental_v2_hash($form_properties),
        'contact_form_properties' => $form_properties,
    ];
    $encoded = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded)) {
        throw new RuntimeException('No se pudo serializar el artefacto privado de restauración.');
    }

    $artifact = $resolved_backup . '/rental-v2-page-1558-form-1556-before.json';
    $handle = @fopen($artifact, 'x');
    if (! is_resource($handle)) {
        throw new RuntimeException('El artefacto de restauración ya existe o no pudo crearse.');
    }
    @chmod($artifact, 0600);
    $written = fwrite($handle, $encoded);
    $flushed = fflush($handle);
    fclose($handle);
    @chmod($artifact, 0600);

    $saved = json_decode((string) file_get_contents($artifact), true);
    $artifact_permissions = fileperms($artifact);
    if (false === $written || $written !== strlen($encoded) || ! $flushed
        || false === $artifact_permissions || 0600 !== ($artifact_permissions & 0777)
        || ! is_array($saved) || 1558 !== ($saved['post_id'] ?? 0)
        || ! hash_equals($payload['post_content_sha256'], hash('sha256', (string) ($saved['post_content'] ?? '')))
        || ! hash_equals($payload['contact_form_properties_sha256'], tmd_commercial_landing_rental_v2_hash($saved['contact_form_properties'] ?? []))) {
        @unlink($artifact);
        throw new RuntimeException('El artefacto privado de restauración no pasó su comprobación de integridad.');
    }

    return $artifact;
}

function tmd_commercial_landing_script_run_rental_v2_update(bool $execute): void
{
    $lock_path = trailingslashit(get_temp_dir()) . 'tmd-commercial-landings-seed.lock';
    $lock = @fopen($lock_path, 'c');
    if (! is_resource($lock) || ! flock($lock, LOCK_EX | LOCK_NB)) {
        is_resource($lock) && fclose($lock);
        throw new RuntimeException('No se pudo adquirir el bloqueo exclusivo de páginas comerciales.');
    }
    @chmod($lock_path, 0600);

    try {
        if (! class_exists('WPCF7_ContactForm')
            || ! function_exists('wpcf7_contact_form')
            || ! function_exists('wpcf7_save_contact_form')) {
            throw new RuntimeException('La API de guardado de Contact Form 7 no está disponible.');
        }

        $page = get_post(1558);
        $form = wpcf7_contact_form(1556);
        if (! $page instanceof WP_Post || 'page' !== $page->post_type
            || 'alquiler-montacargas-electricos' !== $page->post_name || 'publish' !== $page->post_status
            || 'Alquiler y venta de montacargas eléctricos' !== $page->post_title) {
            throw new RuntimeException('La página 1558 no coincide con ID, slug y estado publicados esperados.');
        }
        if (! $form instanceof WPCF7_ContactForm || 1556 !== (int) $form->id()) {
            throw new RuntimeException('El formulario Contact Form 7 1556 no está disponible.');
        }

        $page_meta_before = [
            'rank_math_title' => (string) get_post_meta(1558, 'rank_math_title', true),
            'rank_math_description' => (string) get_post_meta(1558, 'rank_math_description', true),
        ];
        $target_meta = [
            'rank_math_title' => 'Venta o alquiler de montacargas eléctricos | Tecnimontacargas',
            'rank_math_description' => 'Venta de montacargas eléctricos usados y alquiler sin operador desde 15 días. Selección según capacidad, altura y operación en Colombia.',
        ];
        $asset_root = esc_url_raw(untrailingslashit(get_stylesheet_directory_uri()) . '/assets/img');
        $target_content = tmd_commercial_landing_script_rental_v2_content(1556, $asset_root);
        $current_form_properties = $form->get_properties();
        if (! is_array($current_form_properties)
            || ! preg_match('/(?<!\[)\[text\s+tmd_website(?=[^\]]*\btabindex:-1\b)(?=[^\]]*\bautocomplete:off\b)[^\]]*\](?!\])/', (string) ($current_form_properties['form'] ?? ''))) {
            throw new RuntimeException('El formulario 1556 no conserva el honeypot esperado; requiere revisión manual.');
        }

        $target_form_properties = $current_form_properties;
        $target_form_properties['form'] = tmd_commercial_landing_rental_v2_form_markup();
        $target_form_properties['mail'] = is_array($target_form_properties['mail'] ?? null)
            ? $target_form_properties['mail']
            : [];
        $target_form_properties['mail']['body'] = "Solicitud de cotización de montacargas\n\n"
            . "Nombre: [nombre]\nCargo: [cargo]\nEmpresa: [empresa]\nCiudad: [ciudad]\n"
            . "Correo o celular: [contacto]\nNecesidad: [necesidad]\n"
            . "Requerimientos de carga, altura y pasillo: [requerimientos]\n";

        $hashes = [
            'page_before' => hash('sha256', (string) $page->post_content),
            'page_target' => hash('sha256', $target_content),
            'form_before' => tmd_commercial_landing_rental_v2_hash($current_form_properties),
            'form_target' => tmd_commercial_landing_rental_v2_hash($target_form_properties),
            'meta_before' => tmd_commercial_landing_rental_v2_hash($page_meta_before),
            'meta_target' => tmd_commercial_landing_rental_v2_hash($target_meta),
        ];
        $expected_names = [
            'page_before' => 'TMD_RENTAL_V2_EXPECTED_PAGE_SHA256',
            'page_target' => 'TMD_RENTAL_V2_TARGET_PAGE_SHA256',
            'form_before' => 'TMD_RENTAL_V2_EXPECTED_FORM_SHA256',
            'form_target' => 'TMD_RENTAL_V2_TARGET_FORM_SHA256',
            'meta_before' => 'TMD_RENTAL_V2_EXPECTED_META_SHA256',
            'meta_target' => 'TMD_RENTAL_V2_TARGET_META_SHA256',
        ];
        foreach ($expected_names as $key => $environment_name) {
            $provided = trim((string) getenv($environment_name));
            if (! preg_match('/\A[a-f0-9]{64}\z/', $provided)
                || ! hash_equals($provided, $hashes[$key])) {
                throw new RuntimeException('El hash ' . $environment_name . ' está ausente o no coincide; no se modificó contenido.');
            }
        }

        WP_CLI::line('Página 1558: ' . $hashes['page_before'] . ' → ' . $hashes['page_target'] . '.');
        WP_CLI::line('Formulario 1556: ' . $hashes['form_before'] . ' → ' . $hashes['form_target'] . '.');
        WP_CLI::line('Metadatos Rank Math de 1558: ' . $hashes['meta_before'] . ' → ' . $hashes['meta_target'] . '.');
        WP_CLI::line('Campos solicitados: cinco grupos; consentimiento, enlace de privacidad y honeypot conservados.');
        if (! $execute) {
            WP_CLI::success('Dry-run sin escrituras.');
            return;
        }

        if (! tmd_commercial_landing_script_backup_is_valid()) {
            throw new RuntimeException('Se requiere un backup completo, reciente y verificado antes de escribir.');
        }
        $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
        if (! is_string($backup_path)) {
            throw new RuntimeException('No se pudo resolver la ruta del backup verificado.');
        }
        $artifact = tmd_commercial_landing_rental_v2_save_rollback_artifact(
            $backup_path,
            $page,
            $page_meta_before,
            $current_form_properties
        );

        global $wpdb;
        foreach ([$wpdb->posts, $wpdb->postmeta] as $table) {
            $engine = $wpdb->get_var($wpdb->prepare(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
                $table
            ));
            if ('INNODB' !== strtoupper((string) $engine)) {
                throw new RuntimeException('Las tablas de contenido no usan InnoDB; no se inició la actualización.');
            }
        }
        if (false === $wpdb->query('START TRANSACTION')) {
            throw new RuntimeException('No se pudo iniciar la transacción del contenido.');
        }
        try {
            $locked_page = $wpdb->get_row($wpdb->prepare(
                "SELECT ID, post_type, post_name, post_status, post_title, post_content FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
                1558
            ), ARRAY_A);
            if (! is_array($locked_page) || 'page' !== ($locked_page['post_type'] ?? '')
                || 'alquiler-montacargas-electricos' !== ($locked_page['post_name'] ?? '')
                || 'publish' !== ($locked_page['post_status'] ?? '')
                || 'Alquiler y venta de montacargas eléctricos' !== ($locked_page['post_title'] ?? '')
                || ! hash_equals($hashes['page_before'], hash('sha256', (string) ($locked_page['post_content'] ?? '')))) {
                throw new RuntimeException('La página cambió antes de escribir; la transacción se revertirá.');
            }

            $page_result = wp_update_post([
                'ID' => 1558,
                'post_title' => 'Venta o alquiler de montacargas eléctricos',
                'post_content' => wp_slash($target_content),
            ], true);
            if (is_wp_error($page_result) || 1558 !== (int) $page_result) {
                throw new RuntimeException('WordPress no confirmó la actualización de la página 1558.');
            }
            foreach ($target_meta as $meta_key => $meta_value) {
                update_post_meta(1558, $meta_key, $meta_value);
            }

            $saved_form = wpcf7_save_contact_form([
                'id' => 1556,
                'title' => (string) get_post_field('post_title', 1556),
                'locale' => (string) $form->locale(),
                'form' => $target_form_properties['form'],
                'mail' => $target_form_properties['mail'],
                'mail_2' => $target_form_properties['mail_2'] ?? [],
                'messages' => $target_form_properties['messages'] ?? [],
                'additional_settings' => $target_form_properties['additional_settings'] ?? '',
            ], 'save');
            if (! $saved_form instanceof WPCF7_ContactForm || 1556 !== (int) $saved_form->id()) {
                throw new RuntimeException('Contact Form 7 no confirmó el guardado del formulario 1556.');
            }

            clean_post_cache(1558);
            $verified_page = get_post(1558);
            $verified_form = wpcf7_contact_form(1556);
            $verified_form_properties = $verified_form ? $verified_form->get_properties() : [];
            $verified_meta = [
                'rank_math_title' => (string) get_post_meta(1558, 'rank_math_title', true),
                'rank_math_description' => (string) get_post_meta(1558, 'rank_math_description', true),
            ];
            if (! $verified_page instanceof WP_Post
                || 'Venta o alquiler de montacargas eléctricos' !== $verified_page->post_title
                || ! hash_equals($hashes['page_target'], hash('sha256', (string) $verified_page->post_content))
                || ! hash_equals($hashes['form_target'], tmd_commercial_landing_rental_v2_hash($verified_form_properties))
                || ! hash_equals($hashes['meta_target'], tmd_commercial_landing_rental_v2_hash($verified_meta))) {
                throw new RuntimeException('La comprobación posterior del contenido, formulario o metadatos falló.');
            }
            if (false === $wpdb->query('COMMIT')) {
                throw new RuntimeException('No se confirmó la transacción de contenido.');
            }
        } catch (Throwable $exception) {
            $wpdb->query('ROLLBACK');
            clean_post_cache(1558);
            if (function_exists('wpcf7_contact_form')) {
                wpcf7_contact_form(1556);
            }
            throw $exception;
        }

        if (function_exists('do_action')) {
            do_action('litespeed_purge_post', 1558);
        }
        WP_CLI::line('Artefacto de restauración privado verificado: ' . basename($artifact) . '.');
        WP_CLI::success('La página 1558, sus metadatos y el formulario 1556 se actualizaron y verificaron.');
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
