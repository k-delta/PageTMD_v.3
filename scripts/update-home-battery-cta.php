<?php
/**
 * Corrige únicamente el destino del botón Ver Baterías de la portada (ID 47).
 *
 * Lectura:
 *   wp eval-file scripts/update-home-battery-cta.php
 *
 * Escritura después de backup completo verificado:
 *   TMD_HOME_BATTERY_CTA_EXECUTE=1 \
 *   TMD_VERIFIED_BACKUP_PATH=/ruta/backup-validado \
 *   wp eval-file scripts/update-home-battery-cta.php -- execute
 *
 * Rollback focalizado, solo si el contenido actual aún coincide con el hash posterior:
 *   TMD_HOME_BATTERY_CTA_ROLLBACK=1 \
 *   TMD_VERIFIED_BACKUP_PATH=/ruta/backup-reciente-para-rollback \
 *   TMD_HOME_BATTERY_CTA_ORIGINAL_BACKUP_PATH=/ruta/backup-original-del-deploy \
 *   TMD_HOME_BATTERY_CTA_SNAPSHOT_PATH=/ruta/backup-original-del-deploy/home-battery-cta-before-....json \
 *   wp eval-file scripts/update-home-battery-cta.php -- rollback
 */

function tmd_home_battery_cta_spec(): array
{
    return [
        'page_id' => 47,
        'unique_id' => '47_2a907e-63',
        'label' => 'Ver Baterías',
        'old_url' => '/energia/baterias/',
        'new_url' => '/energia/baterias/plomo/',
    ];
}

function tmd_home_battery_cta_transform(string $content): array
{
    $spec = tmd_home_battery_cta_spec();
    $pattern = '~<!-- wp:kadence/singlebtn (?P<attrs>\{[^\r\n]*\}) /-->~u';
    $count = preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    if (false === $count) {
        return ['content' => $content, 'changes' => [], 'errors' => ['No se pudo analizar el contenido Kadence de la portada.']];
    }

    $found = [];
    foreach ($matches as $match) {
        $attributes = json_decode($match['attrs'][0], true);
        if (is_array($attributes) && ($attributes['uniqueID'] ?? '') === $spec['unique_id']) {
            $found[] = [
                'full' => $match[0][0],
                'offset' => $match[0][1],
                'attrs' => $match['attrs'][0],
                'attributes' => $attributes,
            ];
        }
    }

    if (1 !== count($found)) {
        return [
            'content' => $content,
            'changes' => [],
            'errors' => ['Se esperaba exactamente un botón Kadence ' . $spec['unique_id'] . '; encontrados: ' . count($found) . '.'],
        ];
    }

    $block = $found[0];
    if (($block['attributes']['text'] ?? '') !== $spec['label']) {
        return ['content' => $content, 'changes' => [], 'errors' => ['La etiqueta del botón objetivo cambió; no se modificó.']];
    }
    if (array_key_exists('url', $block['attributes']) || ! is_string($block['attributes']['link'] ?? null)) {
        return ['content' => $content, 'changes' => [], 'errors' => ['El botón no tiene el atributo Kadence link esperado; no se modificó.']];
    }

    $current_url = $block['attributes']['link'];
    if ($spec['new_url'] === $current_url) {
        return ['content' => $content, 'changes' => [], 'errors' => []];
    }
    if ($spec['old_url'] !== $current_url) {
        return ['content' => $content, 'changes' => [], 'errors' => ['El CTA tiene un destino distinto del anterior esperado; no se modificó.']];
    }

    $link_pattern = '~("link"\s*:\s*)"(?:\\\\.|[^"\\\\])*"~u';
    $link_count = preg_match_all($link_pattern, $block['attrs'], $link_matches);
    if (1 !== $link_count) {
        return ['content' => $content, 'changes' => [], 'errors' => ['Se esperaba un único atributo link en el botón objetivo.']];
    }

    $encoded_url = json_encode($spec['new_url'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded_url)) {
        return ['content' => $content, 'changes' => [], 'errors' => ['No se pudo codificar el nuevo destino.']];
    }
    $updated_attrs = preg_replace_callback(
        $link_pattern,
        static fn (array $match): string => $match[1] . $encoded_url,
        $block['attrs'],
        1,
        $replacements
    );
    if (! is_string($updated_attrs) || 1 !== $replacements) {
        return ['content' => $content, 'changes' => [], 'errors' => ['No se pudo reemplazar el destino del botón.']];
    }

    $updated_block = str_replace($block['attrs'], $updated_attrs, $block['full']);
    $updated_content = substr_replace($content, $updated_block, $block['offset'], strlen($block['full']));
    $updated_attributes = json_decode($updated_attrs, true);
    if (! is_array($updated_attributes) || ($updated_attributes['link'] ?? '') !== $spec['new_url']) {
        return ['content' => $content, 'changes' => [], 'errors' => ['El destino transformado no pasó su comprobación.']];
    }

    return ['content' => $updated_content, 'changes' => [$spec['unique_id']], 'errors' => []];
}

function tmd_home_battery_cta_save_snapshot(string $backup_path, string $before, string $after): string
{
    $backup_path = realpath($backup_path);
    if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_writable($backup_path)) {
        throw new RuntimeException('No se pudo localizar la carpeta privada del backup.');
    }
    $mode = fileperms($backup_path);
    if (false === $mode || 0 !== ($mode & 0077)) {
        throw new RuntimeException('La carpeta del backup debe tener permisos privados.');
    }

    $spec = tmd_home_battery_cta_spec();
    $backup_manifest = json_decode((string) file_get_contents($backup_path . '/BACKUP_MANIFEST.json'), true);
    $database_hash = is_array($backup_manifest) ? (string) ($backup_manifest['database_sha256'] ?? '') : '';
    if (! preg_match('/^[a-f0-9]{64}$/i', $database_hash)) {
        throw new RuntimeException('El recibo del backup no contiene un hash de base de datos verificable.');
    }
    $payload = [
        'schema_version' => 1,
        'environment' => 'production',
        'created_at_utc' => gmdate('Y-m-d\TH:i:s\Z'),
        'operation' => 'update-home-battery-cta',
        'authorization' => 'Solicitud directa del usuario el 2026-10-07: corregir el botón Ver Baterías de inicio al destino indicado; despliegue solicitado en la conversación actual.',
        'backup' => [
            'database_file' => 'database.sql',
            'database_sha256' => strtolower($database_hash),
            'verified' => true,
        ],
        'page_id' => $spec['page_id'],
        'table' => 'wp_posts',
        'column' => 'post_content',
        'block_unique_id' => $spec['unique_id'],
        'old_url' => $spec['old_url'],
        'new_url' => $spec['new_url'],
        'before_sha256' => hash('sha256', $before),
        'after_sha256' => hash('sha256', $after),
        'before_post_content' => $before,
        'restore_rule' => 'Restaurar antes_post_content solo si la página 47 conserva after_sha256.',
        'verification' => ['confirmar atributo Kadence link', 'comprobar HTTP de la URL destino', 'revisar portada en navegador', 'ejecutar sync-production.sh --check'],
    ];
    $json = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($json)) {
        throw new RuntimeException('No se pudo serializar el snapshot privado de rollback.');
    }

    $path = $backup_path . '/home-battery-cta-before-' . gmdate('Ymd\THis\Z') . '-' . bin2hex(random_bytes(6)) . '.json';
    $handle = @fopen($path, 'x');
    if (! is_resource($handle)) {
        throw new RuntimeException('No se pudo crear el snapshot privado de rollback.');
    }
    @chmod($path, 0600);
    $written = fwrite($handle, $json);
    $flushed = fflush($handle);
    fclose($handle);
    @chmod($path, 0600);

    $saved = json_decode((string) file_get_contents($path), true);
    $file_mode = fileperms($path);
    if (false === $written || strlen($json) !== $written || ! $flushed
        || false === $file_mode || 0600 !== ($file_mode & 0777)
        || ! is_array($saved)
        || ! hash_equals($payload['before_sha256'], hash('sha256', (string) ($saved['before_post_content'] ?? '')))) {
        @unlink($path);
        throw new RuntimeException('El snapshot privado de rollback no pasó la comprobación de integridad.');
    }
    return $path;
}

function tmd_home_battery_cta_transaction_state(): ?bool
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

function tmd_home_battery_cta_run(bool $execute): void
{
    global $wpdb;
    $spec = tmd_home_battery_cta_spec();
    clean_post_cache($spec['page_id']);
    $home = get_post($spec['page_id']);
    if (! $home || 'page' !== $home->post_type) {
        throw new RuntimeException('No existe la portada WordPress esperada con ID 47.');
    }

    $before = (string) $home->post_content;
    $result = tmd_home_battery_cta_transform($before);
    if ([] !== $result['errors']) {
        throw new RuntimeException(implode(' ', $result['errors']));
    }
    if ([] === $result['changes']) {
        WP_CLI::success('El botón Ver Baterías ya apunta a /energia/baterias/plomo/; no hubo cambios.');
        return;
    }
    WP_CLI::line('Página 47, botón ' . $spec['unique_id'] . ': /energia/baterias/ -> /energia/baterias/plomo/.');
    if (! $execute) {
        WP_CLI::success('Dry-run sin escrituras.');
        return;
    }
    if ('1' !== getenv('TMD_HOME_BATTERY_CTA_EXECUTE')) {
        throw new RuntimeException('La escritura requiere TMD_HOME_BATTERY_CTA_EXECUTE=1.');
    }
    if (! function_exists('tmd_commercial_landing_script_backup_is_valid')
        || ! tmd_commercial_landing_script_backup_is_valid()) {
        throw new RuntimeException('Se requiere un backup completo, reciente y verificado antes de escribir.');
    }
    $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
    if (! is_string($backup_path)) {
        throw new RuntimeException('No se pudo resolver la ruta del backup verificado.');
    }

    $engine = $wpdb->get_var($wpdb->prepare(
        'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
        $wpdb->posts
    ));
    if ('INNODB' !== strtoupper((string) $engine)) {
        throw new RuntimeException('La tabla de páginas debe usar InnoDB para permitir rollback transaccional.');
    }
    $snapshot_path = tmd_home_battery_cta_save_snapshot($backup_path, $before, $result['content']);

    if (false === $wpdb->query('START TRANSACTION') || true !== tmd_home_battery_cta_transaction_state()) {
        $wpdb->query('ROLLBACK');
        throw new RuntimeException('No se pudo iniciar y verificar la transacción; no se escribió contenido.');
    }

    try {
        $locked = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_type, post_content FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
            $spec['page_id']
        ), ARRAY_A);
        if (! is_array($locked) || 'page' !== ($locked['post_type'] ?? '')
            || ! hash_equals(hash('sha256', $before), hash('sha256', (string) ($locked['post_content'] ?? '')))) {
            throw new RuntimeException('La portada cambió desde el dry-run; no se guardó la URL.');
        }

        $updated_id = wp_update_post([
            'ID' => $spec['page_id'],
            'post_content' => wp_slash($result['content']),
        ], true);
        if (is_wp_error($updated_id) || $spec['page_id'] !== (int) $updated_id) {
            throw new RuntimeException('WordPress no confirmó la actualización de la portada.');
        }
        clean_post_cache($spec['page_id']);
        $saved_content = $wpdb->get_var($wpdb->prepare(
            "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d",
            $spec['page_id']
        ));
        if (! is_string($saved_content)
            || ! hash_equals(hash('sha256', $result['content']), hash('sha256', $saved_content))) {
            throw new RuntimeException('El contenido guardado no coincide con el destino previsto.');
        }
        $commit_result = $wpdb->query('COMMIT');
        $transaction_active = tmd_home_battery_cta_transaction_state();
        if (true === $transaction_active || null === $transaction_active) {
            throw new RuntimeException('No se pudo confirmar el cierre de la transacción de la portada.');
        }
        clean_post_cache($spec['page_id']);
        $committed_content = $wpdb->get_var($wpdb->prepare(
            "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d",
            $spec['page_id']
        ));
        if (! is_string($committed_content)
            || ! hash_equals(hash('sha256', $result['content']), hash('sha256', $committed_content))) {
            throw new RuntimeException('La comprobación posterior al commit no coincide con el destino aprobado.');
        }
        if (false === $commit_result) {
            WP_CLI::warning('COMMIT informó un resultado ambiguo, pero se verificó el destino persistido de la portada.');
        }
        WP_CLI::success('Destino corregido y comprobado. Snapshot privado: ' . basename($snapshot_path));
    } catch (Throwable $error) {
        if (true === tmd_home_battery_cta_transaction_state()) {
            $wpdb->query('ROLLBACK');
        }
        clean_post_cache($spec['page_id']);
        throw $error;
    }
}

function tmd_home_battery_cta_rollback(): void
{
    global $wpdb;
    $spec = tmd_home_battery_cta_spec();
    if ('1' !== getenv('TMD_HOME_BATTERY_CTA_ROLLBACK')) {
        throw new RuntimeException('El rollback requiere TMD_HOME_BATTERY_CTA_ROLLBACK=1.');
    }
    if (! function_exists('tmd_commercial_landing_script_backup_is_valid')
        || ! tmd_commercial_landing_script_backup_is_valid()) {
        throw new RuntimeException('El rollback requiere un backup completo, reciente y verificado del estado previo a la reversión.');
    }

    $current_backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
    $original_backup_path = realpath((string) getenv('TMD_HOME_BATTERY_CTA_ORIGINAL_BACKUP_PATH'));
    $snapshot_input = (string) getenv('TMD_HOME_BATTERY_CTA_SNAPSHOT_PATH');
    if (is_link($snapshot_input)) {
        throw new RuntimeException('El snapshot de rollback no puede ser un enlace simbólico.');
    }
    $snapshot_path = realpath($snapshot_input);
    if (! is_string($current_backup_path) || ! is_string($original_backup_path)
        || $current_backup_path === $original_backup_path
        || ! tmd_commercial_landing_script_backup_is_valid($original_backup_path, false)
        || ! is_string($snapshot_path)
        || ! str_starts_with($snapshot_path, trailingslashit($original_backup_path))
        || ! is_file($snapshot_path) || is_link($snapshot_path) || ! is_readable($snapshot_path)) {
        throw new RuntimeException('El snapshot de rollback debe estar dentro del backup validado.');
    }
    $snapshot_mode = fileperms($snapshot_path);
    if (false === $snapshot_mode || 0600 !== ($snapshot_mode & 0777)) {
        throw new RuntimeException('El snapshot de rollback debe mantener permisos 0600.');
    }
    $snapshot = json_decode((string) file_get_contents($snapshot_path), true);
    $before = is_array($snapshot) ? ($snapshot['before_post_content'] ?? null) : null;
    if (! is_array($snapshot)
        || 'production' !== ($snapshot['environment'] ?? '')
        || 'update-home-battery-cta' !== ($snapshot['operation'] ?? '')
        || $spec['page_id'] !== (int) ($snapshot['page_id'] ?? 0)
        || $spec['unique_id'] !== ($snapshot['block_unique_id'] ?? '')
        || $spec['new_url'] !== ($snapshot['new_url'] ?? '')
        || ! is_string($before)
        || ! hash_equals((string) ($snapshot['before_sha256'] ?? ''), hash('sha256', $before))) {
        throw new RuntimeException('El snapshot no corresponde a esta operación o no pasó la verificación de hash.');
    }
    $backup_manifest = json_decode((string) file_get_contents($original_backup_path . '/BACKUP_MANIFEST.json'), true);
    if (! is_array($backup_manifest)
        || ! hash_equals(strtolower((string) ($snapshot['backup']['database_sha256'] ?? '')),
            strtolower((string) ($backup_manifest['database_sha256'] ?? '')))) {
        throw new RuntimeException('El snapshot no corresponde al backup de base de datos validado.');
    }
    $restored_transform = tmd_home_battery_cta_transform($before);
    if ([] !== $restored_transform['errors'] || ! hash_equals(
        (string) ($snapshot['after_sha256'] ?? ''),
        hash('sha256', $restored_transform['content'])
    )) {
        throw new RuntimeException('El contenido anterior del snapshot no reproduce el estado publicado esperado.');
    }

    clean_post_cache($spec['page_id']);
    $home = get_post($spec['page_id']);
    if (! $home || ! hash_equals((string) ($snapshot['after_sha256'] ?? ''), hash('sha256', (string) $home->post_content))) {
        throw new RuntimeException('La portada cambió después del deploy; el rollback se detuvo para preservar esos cambios.');
    }
    $engine = $wpdb->get_var($wpdb->prepare(
        'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
        $wpdb->posts
    ));
    if ('INNODB' !== strtoupper((string) $engine)) {
        throw new RuntimeException('La tabla de páginas debe usar InnoDB para permitir rollback transaccional.');
    }
    if (false === $wpdb->query('START TRANSACTION') || true !== tmd_home_battery_cta_transaction_state()) {
        $wpdb->query('ROLLBACK');
        throw new RuntimeException('No se pudo iniciar y verificar la transacción de rollback.');
    }

    try {
        $locked = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_type, post_content FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
            $spec['page_id']
        ), ARRAY_A);
        if (! is_array($locked) || 'page' !== ($locked['post_type'] ?? '')
            || ! hash_equals((string) $snapshot['after_sha256'], hash('sha256', (string) ($locked['post_content'] ?? '')))) {
            throw new RuntimeException('La portada cambió antes del bloqueo; no se aplicó el rollback.');
        }
        $updated_id = wp_update_post([
            'ID' => $spec['page_id'],
            'post_content' => wp_slash($before),
        ], true);
        if (is_wp_error($updated_id) || $spec['page_id'] !== (int) $updated_id) {
            throw new RuntimeException('WordPress no confirmó la restauración focalizada.');
        }
        clean_post_cache($spec['page_id']);
        $restored = $wpdb->get_var($wpdb->prepare(
            "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d",
            $spec['page_id']
        ));
        if (! is_string($restored) || ! hash_equals((string) $snapshot['before_sha256'], hash('sha256', $restored))) {
            throw new RuntimeException('El contenido restaurado no coincide con el hash del snapshot.');
        }
        $commit_result = $wpdb->query('COMMIT');
        $transaction_active = tmd_home_battery_cta_transaction_state();
        if (true === $transaction_active || null === $transaction_active) {
            throw new RuntimeException('No se pudo confirmar el cierre de la transacción de rollback.');
        }
        clean_post_cache($spec['page_id']);
        $committed = $wpdb->get_var($wpdb->prepare(
            "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d",
            $spec['page_id']
        ));
        if (! is_string($committed) || ! hash_equals((string) $snapshot['before_sha256'], hash('sha256', $committed))) {
            throw new RuntimeException('La comprobación posterior al commit no coincide con el hash anterior.');
        }
        if (false === $commit_result) {
            WP_CLI::warning('COMMIT informó un resultado ambiguo, pero se verificó el contenido restaurado de la portada.');
        }
        WP_CLI::success('Rollback focalizado verificado; la página volvió al hash anterior.');
    } catch (Throwable $error) {
        if (true === tmd_home_battery_cta_transaction_state()) {
            $wpdb->query('ROLLBACK');
        }
        clean_post_cache($spec['page_id']);
        throw $error;
    }
}

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

require_once __DIR__ . '/commercial-backup-validation.php';
$command_args = isset($args) && is_array($args) ? array_values($args) : [];
if (! in_array($command_args, [[], ['dry-run'], ['execute'], ['rollback']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/update-home-battery-cta.php -- [dry-run|execute|rollback]');
}
if (['rollback'] === $command_args) {
    tmd_home_battery_cta_rollback();
} else {
    tmd_home_battery_cta_run(['execute'] === $command_args);
}
