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

function tmd_commercial_landing_rental_v2_source_title(): string
{
    return 'Venta o alquiler de montacargas eléctricos';
}

function tmd_commercial_landing_rental_v2_is_expected_source_title(string $title): bool
{
    return in_array($title, [
        tmd_commercial_landing_rental_v2_source_title(),
        'Alquiler de montacargas eléctricos',
    ], true);
}

function tmd_commercial_landing_rental_v2_target_form_properties(array $current): array
{
    $target = $current;
    $target['form'] = tmd_commercial_landing_rental_v2_form_markup();
    $target['mail'] = is_array($target['mail'] ?? null) ? $target['mail'] : [];
    $target['mail']['body'] = "Solicitud de cotización de montacargas\n\n"
        . "Nombre y cargo: [nombre_cargo]\nEmpresa y ciudad: [empresa_ciudad]\n"
        . "Correo o celular: [contacto]\nNecesidad: [necesidad]\n"
        . "Requerimientos de carga, altura y pasillo: [requerimientos]";

    return $target;
}

function tmd_commercial_landing_rental_v2_post_record(int $post_id, bool $for_update = false): array
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d" . ($for_update ? ' FOR UPDATE' : ''), $post_id);
    $record = $wpdb->get_row($sql, ARRAY_A);
    if (! is_array($record) || '' !== $wpdb->last_error) {
        throw new RuntimeException('No se pudo leer el registro de contenido esperado.');
    }

    return $record;
}

function tmd_commercial_landing_rental_v2_meta_records(int $post_id, array $keys = [], bool $for_update = false): array
{
    global $wpdb;

    $sql = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d";
    $arguments = [$post_id];
    if ([] !== $keys) {
        $sql .= ' AND meta_key IN (' . implode(', ', array_fill(0, count($keys), '%s')) . ')';
        $arguments = array_merge($arguments, $keys);
    }
    $sql .= ' ORDER BY meta_id' . ($for_update ? ' FOR UPDATE' : '');
    $records = $wpdb->get_results($wpdb->prepare($sql, ...$arguments), ARRAY_A);
    if (! is_array($records) || '' !== $wpdb->last_error) {
        throw new RuntimeException('No se pudieron leer los metadatos esperados.');
    }

    return $records;
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

        clean_post_cache(1558);
        clean_post_cache(1556);
        $page = get_post(1558);
        $form = wpcf7_contact_form(1556);
        if (! $page instanceof WP_Post || 'page' !== $page->post_type
            || 'alquiler-montacargas-electricos' !== $page->post_name || 'publish' !== $page->post_status
            || ! tmd_commercial_landing_rental_v2_is_expected_source_title((string) $page->post_title)) {
            throw new RuntimeException('La página 1558 no coincide con ID, slug y estado publicados esperados.');
        }
        if (! $form instanceof WPCF7_ContactForm || 1556 !== (int) $form->id()) {
            throw new RuntimeException('El formulario Contact Form 7 1556 no está disponible.');
        }

        $page_meta_before = [
            'rank_math_title' => (string) get_post_meta(1558, 'rank_math_title', true),
            'rank_math_description' => (string) get_post_meta(1558, 'rank_math_description', true),
        ];
        $source_page_title = (string) $page->post_title;
        $rollback_source_page_title = $source_page_title;
        $page_meta_storage_before = tmd_commercial_landing_rental_v2_meta_records(
            1558,
            ['rank_math_title', 'rank_math_description']
        );
        $form_post_storage_before = tmd_commercial_landing_rental_v2_post_record(1556);
        $form_meta_storage_before = tmd_commercial_landing_rental_v2_meta_records(1556);
        $target_meta = [
            'rank_math_title' => 'Alquiler de montacargas eléctricos | Tecnimontacargas',
            'rank_math_description' => 'Alquiler de montacargas eléctricos para bodegas, centros de distribución y plantas. Alquiler sin operador desde 15 días, con recomendación técnica según tu operación.',
        ];
        $asset_root = esc_url_raw(untrailingslashit(get_stylesheet_directory_uri()) . '/assets/img');
        $target_content = tmd_commercial_landing_script_rental_v2_content(1556, $asset_root);
        $current_form_properties = $form->get_properties();
        if (! is_array($current_form_properties)
            || ! preg_match('/(?<!\[)\[text\s+tmd_website(?=[^\]]*\btabindex:-1\b)(?=[^\]]*\bautocomplete:off\b)[^\]]*\](?!\])/', (string) ($current_form_properties['form'] ?? ''))) {
            throw new RuntimeException('El formulario 1556 no conserva el honeypot esperado; requiere revisión manual.');
        }

        $target_form_properties = tmd_commercial_landing_rental_v2_target_form_properties($current_form_properties);

        $hashes = [
            'page_before' => hash('sha256', (string) $page->post_content),
            'page_target' => hash('sha256', $target_content),
            'form_before' => tmd_commercial_landing_rental_v2_hash($current_form_properties),
            'form_target' => tmd_commercial_landing_rental_v2_hash($target_form_properties),
            'meta_before' => tmd_commercial_landing_rental_v2_hash($page_meta_before),
            'meta_target' => tmd_commercial_landing_rental_v2_hash($target_meta),
            'meta_storage_before' => tmd_commercial_landing_rental_v2_hash($page_meta_storage_before),
            'form_storage_before' => tmd_commercial_landing_rental_v2_hash([
                'post' => $form_post_storage_before,
                'meta' => $form_meta_storage_before,
            ]),
        ];
        $expected_names = [
            'page_before' => 'TMD_RENTAL_V2_EXPECTED_PAGE_SHA256',
            'page_target' => 'TMD_RENTAL_V2_TARGET_PAGE_SHA256',
            'form_before' => 'TMD_RENTAL_V2_EXPECTED_FORM_SHA256',
            'form_target' => 'TMD_RENTAL_V2_TARGET_FORM_SHA256',
            'meta_before' => 'TMD_RENTAL_V2_EXPECTED_META_SHA256',
            'meta_target' => 'TMD_RENTAL_V2_TARGET_META_SHA256',
            'meta_storage_before' => 'TMD_RENTAL_V2_EXPECTED_META_STORAGE_SHA256',
            'form_storage_before' => 'TMD_RENTAL_V2_EXPECTED_FORM_STORAGE_SHA256',
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
        WP_CLI::line('Huella de filas Rank Math: ' . $hashes['meta_storage_before'] . '.');
        WP_CLI::line('Huella de almacenamiento CF7 1556: ' . $hashes['form_storage_before'] . '.');
        WP_CLI::line('Campos solicitados: cinco campos; nota de privacidad y honeypot conservados.');
        $commercial_claims_confirmed = 'yes' === strtolower(trim((string) getenv('TMD_RENTAL_V2_COMMERCIAL_CLAIMS_CONFIRMED')));
        $inventory_button_contrast_approved = 'yes' === strtolower(trim((string) getenv('TMD_RENTAL_V2_CONTRAST_APPROVED')));
        WP_CLI::line('Confirmación comercial de 120 equipos y Yale: ' . ($commercial_claims_confirmed ? 'registrada.' : 'pendiente.'));
        WP_CLI::line('Decisión de contraste del botón de inventario: ' . ($inventory_button_contrast_approved ? 'registrada.' : 'pendiente.'));
        if (! $execute) {
            WP_CLI::success('Dry-run sin escrituras.');
            return;
        }
        if (! $commercial_claims_confirmed || ! $inventory_button_contrast_approved) {
            throw new RuntimeException(
                'No se permite ejecutar hasta confirmar los datos comerciales y resolver el contraste; '
                . 'se requieren TMD_RENTAL_V2_COMMERCIAL_CLAIMS_CONFIRMED=yes y TMD_RENTAL_V2_CONTRAST_APPROVED=yes.'
            );
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
                || ! tmd_commercial_landing_rental_v2_is_expected_source_title((string) ($locked_page['post_title'] ?? ''))
                || ! hash_equals($hashes['page_before'], hash('sha256', (string) ($locked_page['post_content'] ?? '')))) {
                throw new RuntimeException('La página cambió antes de escribir; la transacción se revertirá.');
            }
            $rollback_source_page_title = (string) $locked_page['post_title'];

            $locked_page_meta = tmd_commercial_landing_rental_v2_meta_records(
                1558,
                ['rank_math_title', 'rank_math_description'],
                true
            );
            if (! hash_equals(
                $hashes['meta_storage_before'],
                tmd_commercial_landing_rental_v2_hash($locked_page_meta)
            )) {
                throw new RuntimeException('Los metadatos Rank Math cambiaron antes de escribir; la transacción se revertirá.');
            }
            clean_post_cache(1558);
            $locked_page_meta_values = [
                'rank_math_title' => (string) get_post_meta(1558, 'rank_math_title', true),
                'rank_math_description' => (string) get_post_meta(1558, 'rank_math_description', true),
            ];
            if (! hash_equals($hashes['meta_before'], tmd_commercial_landing_rental_v2_hash($locked_page_meta_values))) {
                throw new RuntimeException('Los metadatos Rank Math cambiaron antes de escribir; la transacción se revertirá.');
            }

            $locked_form_post = tmd_commercial_landing_rental_v2_post_record(1556, true);
            $locked_form_meta = tmd_commercial_landing_rental_v2_meta_records(1556, [], true);
            if ('wpcf7_contact_form' !== ($locked_form_post['post_type'] ?? '')
                || ! hash_equals($hashes['form_storage_before'], tmd_commercial_landing_rental_v2_hash([
                    'post' => $locked_form_post,
                    'meta' => $locked_form_meta,
                ]))) {
                throw new RuntimeException('El formulario CF7 1556 cambió antes de escribir; la transacción se revertirá.');
            }
            clean_post_cache(1556);
            $form = wpcf7_contact_form(1556);
            if (! $form instanceof WPCF7_ContactForm
                || ! hash_equals($hashes['form_before'], tmd_commercial_landing_rental_v2_hash($form->get_properties()))) {
                throw new RuntimeException('Las propiedades de CF7 1556 cambiaron antes de escribir; la transacción se revertirá.');
            }

            $page_result = wp_update_post([
                'ID' => 1558,
                'post_title' => 'Alquiler de montacargas eléctricos',
                'post_content' => wp_slash($target_content),
            ], true);
            if (is_wp_error($page_result) || 1558 !== (int) $page_result) {
                throw new RuntimeException('WordPress no confirmó la actualización de la página 1558.');
            }
            if (! hash_equals($hashes['meta_before'], $hashes['meta_target'])) {
                foreach ($target_meta as $meta_key => $meta_value) {
                    update_post_meta(1558, $meta_key, $meta_value);
                }
            }

            if (! hash_equals($hashes['form_before'], $hashes['form_target'])) {
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
            }

            clean_post_cache(1558);
            $verified_page = get_post(1558);
            $verified_form = wpcf7_contact_form(1556);
            $verified_form_properties = $verified_form ? $verified_form->get_properties() : [];
            $verified_meta = [
                'rank_math_title' => (string) get_post_meta(1558, 'rank_math_title', true),
                'rank_math_description' => (string) get_post_meta(1558, 'rank_math_description', true),
            ];
            $verification_failures = [];
            if (! $verified_page instanceof WP_Post
                || 'Alquiler de montacargas eléctricos' !== $verified_page->post_title
                || ! hash_equals($hashes['page_target'], hash('sha256', (string) $verified_page->post_content))) {
                $verification_failures[] = 'página 1558';
            }
            if (! hash_equals($hashes['form_target'], tmd_commercial_landing_rental_v2_hash($verified_form_properties))) {
                $verification_failures[] = 'propiedades CF7 1556';
            }
            if (! hash_equals($hashes['meta_target'], tmd_commercial_landing_rental_v2_hash($verified_meta))) {
                $verification_failures[] = 'metadatos Rank Math';
            }
            if ([] !== $verification_failures) {
                throw new RuntimeException('La comprobación posterior falló para: ' . implode(', ', $verification_failures) . '.');
            }
            $commit_attempted = true;
            if (false === $wpdb->query('COMMIT')) {
                throw new RuntimeException('No se confirmó la transacción de contenido.');
            }
            $transaction_confirmed = true;
        } catch (Throwable $exception) {
            $rollback_result = $wpdb->query('ROLLBACK');
            clean_post_cache(1558);
            clean_post_cache(1556);
            $persisted_page = get_post(1558);
            $persisted_form = wpcf7_contact_form(1556);
            $persisted_form_properties = $persisted_form instanceof WPCF7_ContactForm
                ? $persisted_form->get_properties()
                : [];
            $persisted_meta = [
                'rank_math_title' => (string) get_post_meta(1558, 'rank_math_title', true),
                'rank_math_description' => (string) get_post_meta(1558, 'rank_math_description', true),
            ];
            $source_persisted = $persisted_page instanceof WP_Post
                && $rollback_source_page_title === $persisted_page->post_title
                && hash_equals($hashes['page_before'], hash('sha256', (string) $persisted_page->post_content))
                && hash_equals($hashes['form_before'], tmd_commercial_landing_rental_v2_hash($persisted_form_properties))
                && hash_equals($hashes['meta_before'], tmd_commercial_landing_rental_v2_hash($persisted_meta));
            $target_persisted = $persisted_page instanceof WP_Post
                && 'Alquiler de montacargas eléctricos' === $persisted_page->post_title
                && hash_equals($hashes['page_target'], hash('sha256', (string) $persisted_page->post_content))
                && hash_equals($hashes['form_target'], tmd_commercial_landing_rental_v2_hash($persisted_form_properties))
                && hash_equals($hashes['meta_target'], tmd_commercial_landing_rental_v2_hash($persisted_meta));

            if ($source_persisted) {
                throw new RuntimeException(
                    $exception->getMessage() . ' Se verificó que página, formulario y metadatos siguen en su estado original.'
                    . (false === $rollback_result ? ' La reconexión/rollback informó resultado incierto.' : ''),
                    0,
                    $exception
                );
            }
            if ($target_persisted && $commit_attempted) {
                $transaction_confirmed = true;
                WP_CLI::warning('El resultado del COMMIT fue ambiguo, pero página, formulario y metadatos coinciden con los hashes destino persistidos.');
            } else {
                throw new RuntimeException(
                    'El estado persistido no coincide con origen ni destino. No se intentó sobrescribirlo; conservar el artefacto privado de rollback y detener nuevos writes.',
                    0,
                    $exception
                );
            }
        }

        if (! $transaction_confirmed) {
            throw new RuntimeException('No se pudo confirmar el resultado de la transacción.');
        }

        if (function_exists('do_action')) {
            do_action('litespeed_purge_post', 1558);
        }
        WP_CLI::line('Artefacto de restauración privado verificado: ' . basename($artifact) . '.');
        $meta_result = hash_equals($hashes['meta_before'], $hashes['meta_target'])
            ? 'Rank Math ya coincidía con el destino'
            : 'Rank Math se actualizó al destino';
        $form_result = hash_equals($hashes['form_before'], $hashes['form_target'])
            ? 'CF7 ya coincidía con el destino'
            : 'CF7 se actualizó al destino';
        WP_CLI::success('La página 1558 se actualizó y verificó; ' . $meta_result . ' y ' . $form_result . '.');
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
