<?php

$test_wp_root = __DIR__ . '/.tmp-commercial-landings-wp-root-' . bin2hex(random_bytes(6));
mkdir($test_wp_root, 0700);
define('ABSPATH', $test_wp_root . '/');
define('WP_CLI', true);
define('ARRAY_A', 'ARRAY_A');

class WP_Post {
    public $ID;
    public $post_title;
    public $post_type;
    public $post_name;
    public $post_content;
    public $post_status;

    public function __construct($id, $title, $type = 'wpcf7_contact_form', $name = '', $content = '', $status = 'publish') {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_type = $type;
        $this->post_name = $name;
        $this->post_content = $content;
        $this->post_status = $status;
    }
}

class WP_CLI {
    public static $messages = [];
    public static $save_calls = 0;

    public static function line($message) {
        self::$messages[] = ['line', $message];
    }

    public static function success($message) {
        self::$messages[] = ['success', $message];
    }

    public static function error($message) {
        throw new RuntimeException($message);
    }
}

class WPCF7_ContactForm {
    private $form_id;

    public function __construct($form_id) {
        $this->form_id = (int) $form_id;
    }

    public static function get_instance($id) {
        return 14 === (int) $id ? new self(14) : null;
    }

    public function get_properties() {
        if (14 === $this->form_id) {
            return [
                'mail' => ['recipient' => ''],
                'mail_2' => ['active' => false],
                'messages' => [],
            ];
        }

        return $GLOBALS['tmd_test_form_properties'][$this->form_id] ?? [];
    }

    public function locale() {
        return 'es_CO';
    }

    public function id() {
        return $this->form_id;
    }
}

function is_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

function get_temp_dir() {
    return $GLOBALS['tmd_test_temp_dir'];
}

function get_post($post_id) {
    return 1558 === (int) $post_id ? ($GLOBALS['tmd_test_wp_page'] ?? null) : null;
}

function wp_update_post($postarr, $wp_error = false) {
    ++$GLOBALS['tmd_test_wp_update_calls'];
    $page = $GLOBALS['tmd_test_wp_page'];
    $content = stripslashes((string) ($postarr['post_content'] ?? $page->post_content));
    $page->post_content = (string) ($GLOBALS['tmd_test_wp_update_content_override'] ?? $content);
    $page->post_status = (string) ($postarr['post_status'] ?? $page->post_status);
    if (($GLOBALS['wpdb'] ?? null) instanceof TMD_Test_WPDB) {
        $GLOBALS['wpdb']->row['post_content'] = $page->post_content;
        $GLOBALS['wpdb']->row['post_status'] = $page->post_status;
    }
    return $page->ID;
}

function clean_post_cache($post_id) {
    if (1558 !== (int) $post_id
        || ! isset($GLOBALS['tmd_test_wp_page'])
        || ! $GLOBALS['tmd_test_wp_page'] instanceof WP_Post
        || ! ($GLOBALS['wpdb'] ?? null) instanceof TMD_Test_WPDB) {
        return;
    }

    ++$GLOBALS['tmd_test_clean_post_cache_calls'];
    $row = $GLOBALS['wpdb']->row;
    $page = $GLOBALS['tmd_test_wp_page'];
    foreach (['ID', 'post_title', 'post_type', 'post_name', 'post_content', 'post_status'] as $field) {
        if (array_key_exists($field, $row)) {
            $page->{$field} = $row[$field];
        }
    }
}

function wp_slash($value) {
    return addslashes($value);
}

function wp_json_encode($value, $options = 0) {
    return json_encode($value, $options);
}

function is_wp_error($value) {
    return $value instanceof WP_Error;
}

class WP_Error {
}

class TMD_Test_WPDB {
    public $posts = 'wp_posts';
    public $engine = 'InnoDB';
    public $row = [];
    public $snapshot = null;
    public $in_transaction = false;
    public $queries = [];

    public function prepare($query, ...$args) {
        return $query;
    }

    public function get_var($query) {
        return $this->engine;
    }

    public function get_row($query, $output = null) {
        if (false !== strpos($query, 'FOR UPDATE') && isset($GLOBALS['tmd_test_wpdb_locked_row_override'])) {
            return $GLOBALS['tmd_test_wpdb_locked_row_override'];
        }
        return $this->row;
    }

    public function query($query) {
        $this->queries[] = $query;
        if ('START TRANSACTION' === $query) {
            $this->snapshot = $this->row;
            $this->in_transaction = true;
        } elseif ('COMMIT' === $query) {
            $this->snapshot = null;
            $this->in_transaction = false;
        } elseif ('ROLLBACK' === $query) {
            if (is_array($this->snapshot)) {
                $this->row = $this->snapshot;
                $page = $GLOBALS['tmd_test_wp_page'] ?? null;
                if ($page instanceof WP_Post) {
                    $page->post_title = $this->row['post_title'];
                    $page->post_content = $this->row['post_content'];
                    $page->post_status = $this->row['post_status'];
                }
            }
            $this->snapshot = null;
            $this->in_transaction = false;
        }
        return 0;
    }
}

function trailingslashit($path) {
    return rtrim($path, '/') . '/';
}

function untrailingslashit($path) {
    return rtrim($path, '/');
}

function get_posts($args) {
    return 'wpcf7_contact_form' === ($args['post_type'] ?? '')
        ? ($GLOBALS['tmd_test_form_posts'] ?? [])
        : [];
}

function get_post_meta($post_id, $key, $single = false) {
    return $GLOBALS['tmd_test_post_meta'][$post_id][$key] ?? '';
}

function get_post_types($args, $output = 'names') {
    return ['page'];
}

function get_stylesheet_directory_uri() {
    return 'https://example.test/wp-content/themes/blocksy-child';
}

function home_url($path = '') {
    return 'https://example.test' . $path;
}

function esc_url($value) {
    return $value;
}

function esc_url_raw($value) {
    return $value;
}

function wpcf7_save_contact_form($data, $context = 'save') {
    ++WP_CLI::$save_calls;
    return false;
}

function wpcf7_contact_form($id) {
    return isset($GLOBALS['tmd_test_form_properties'][(int) $id])
        ? new WPCF7_ContactForm($id)
        : false;
}

function tmd_commercial_landing_recipient_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$temp_dir = __DIR__ . '/.tmp-commercial-landings-test-' . bin2hex(random_bytes(6));
mkdir($temp_dir, 0700);
$GLOBALS['tmd_test_temp_dir'] = $temp_dir;
$GLOBALS['tmd_test_wp_update_calls'] = 0;
$GLOBALS['tmd_test_clean_post_cache_calls'] = 0;
function tmd_test_remove_tree($path) {
    foreach (glob($path . '/*') ?: [] as $child) {
        if (is_dir($child) && ! is_link($child)) {
            tmd_test_remove_tree($child);
        } else {
            unlink($child);
        }
    }
    if (is_dir($path)) {
        rmdir($path);
    }
}
register_shutdown_function(static function () use ($temp_dir, $test_wp_root): void {
    tmd_test_remove_tree($temp_dir);
    tmd_test_remove_tree($test_wp_root);
});
putenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT=info@tmdual.com');
putenv('TMD_COMMERCIAL_LANDINGS_EXECUTE');

$seed_error = null;
try {
    require dirname(__DIR__) . '/scripts/create-commercial-landing-pages.php';
} catch (RuntimeException $exception) {
    $seed_error = $exception;
}

tmd_commercial_landing_recipient_assert(
    null === $seed_error,
    'El dry-run debe aceptar el destinatario explícito aunque CF7 ID 14 no tenga uno configurado.'
        . ($seed_error ? ' Detalle: ' . $seed_error->getMessage() : '')
);

$recipient = tmd_commercial_landing_script_resolve_recipient(
    (string) getenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT'),
    ''
);
$form_specs = tmd_commercial_landing_script_form_specs($recipient, 'es_CO', []);
tmd_commercial_landing_recipient_assert(
    'info@tmdual.com' === $form_specs['rental']['mail']['recipient'],
    'El formulario nuevo de alquiler debe dirigirse al correo autorizado.'
);
tmd_commercial_landing_recipient_assert(
    'info@tmdual.com' === $form_specs['battery']['mail']['recipient'],
    'El formulario nuevo de baterías debe dirigirse al correo autorizado.'
);

$managed_form_id = 200;
$managed_form_title = 'TMD | Cotización de alquiler y venta de montacargas eléctricos';
$GLOBALS['tmd_test_form_posts'] = [new WP_Post($managed_form_id, $managed_form_title)];
$GLOBALS['tmd_test_post_meta'][$managed_form_id]['_tmd_commercial_landing_form_seed'] = '2026-10-04-v1:rental';
$GLOBALS['tmd_test_form_properties'][$managed_form_id] = [
    'form' => '[text tmd_website tabindex:-1 autocomplete:off]',
    'mail' => ['recipient' => 'anterior@example.com'],
];

$mismatch_error = null;
try {
    tmd_commercial_landing_script_existing_forms($form_specs);
} catch (RuntimeException $exception) {
    $mismatch_error = $exception;
}
tmd_commercial_landing_recipient_assert(
    $mismatch_error instanceof RuntimeException,
    'El seed debe detenerse si un formulario administrado ya tiene un destinatario diferente.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($mismatch_error->getMessage(), 'anterior@example.com')
        && false === strpos($mismatch_error->getMessage(), 'info@tmdual.com'),
    'El error por destinatario distinto no debe exponer ninguna dirección de correo.'
);

$GLOBALS['tmd_test_form_properties'][$managed_form_id]['mail']['recipient'] = 'info@tmdual.com';
$existing_forms = tmd_commercial_landing_script_existing_forms($form_specs);
tmd_commercial_landing_recipient_assert(
    $existing_forms['rental'] instanceof WPCF7_ContactForm,
    'El seed debe conservar un formulario administrado cuyo destinatario ya coincide.'
);
tmd_commercial_landing_recipient_assert(
    0 === WP_CLI::$save_calls,
    'El dry-run no debe guardar formularios ni modificar CF7 ID 14.'
);

$output = implode("\n", array_column(WP_CLI::$messages, 1));
tmd_commercial_landing_recipient_assert(
    false !== strpos($output, 'Dry-run sin escrituras'),
    'El seed debe terminar confirmando que el dry-run no escribió datos.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($output, 'info@tmdual.com'),
    'La salida del dry-run no debe imprimir el destinatario.'
);

tmd_commercial_landing_recipient_assert(
    'cotizaciones@example.com' === tmd_commercial_landing_script_resolve_recipient('', 'cotizaciones@example.com'),
    'Sin override, el seed debe conservar como alternativa el destinatario del formulario base.'
);

$invalid_recipient_rejected = false;
try {
    tmd_commercial_landing_script_resolve_recipient('correo-invalido', 'cotizaciones@example.com');
} catch (InvalidArgumentException $exception) {
    $invalid_recipient_rejected = true;
}
tmd_commercial_landing_recipient_assert(
    $invalid_recipient_rejected,
    'Un override inválido debe detener el seed, sin sustituirse silenciosamente por el destinatario base.'
);

$rental_content = tmd_commercial_landing_script_page_content('rental', 1556);
$hero_heading_position = strpos($rental_content, '<h1 id="tmd-rental-heading">');
$hero_start = false === $hero_heading_position
    ? false
    : strrpos(substr($rental_content, 0, $hero_heading_position), '<section class="tmd-commercial-landing tmd-commercial-landing__hero tmd-commercial-landing__hero--rental');
$hero_end = false === $hero_heading_position ? false : strpos($rental_content, '</section>', $hero_heading_position);
$hero_block_start = false === $hero_heading_position
    ? false
    : strrpos(substr($rental_content, 0, $hero_heading_position), '<!-- wp:html -->');
$hero_block_end = false === $hero_heading_position
    ? false
    : strpos($rental_content, '<!-- /wp:html -->', $hero_heading_position);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Alquiler de <em>montacargas eléctricos</em>'),
    'El hero de alquiler debe mantener el título de la referencia como HTML editable.'
);
tmd_commercial_landing_recipient_assert(
    false !== $hero_start
        && false !== $hero_end
        && false === strpos(substr($rental_content, $hero_start, $hero_end - $hero_start), 'tmd-commercial-landing__eyebrow'),
    'El hero no debe incluir una línea auxiliar adicional.'
);
tmd_commercial_landing_recipient_assert(
    false !== $hero_block_start
        && false !== $hero_block_end
        && $hero_block_start < $hero_heading_position
        && $hero_block_end > $hero_end,
    'El contenido del hero debe permanecer en un bloque HTML editable de WordPress.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Equipos propios con mantenimiento en nuestro taller técnico'),
    'El hero de alquiler debe mantener su subtítulo como texto HTML editable.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Contrabalanceados, reach, pantógrafos y apiladores'),
    'El hero debe incluir el texto de apoyo aprobado en la referencia.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'href="#formulario-montacargas">Solicitar cotización</a>'),
    'El CTA de cotización debe conservar su destino al formulario.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Alquiler mínimo de 1 mes')
        && false !== strpos($rental_content, 'Cobertura en Colombia')
        && false !== strpos($rental_content, 'Alquiler sin operador'),
    'El rediseño debe conservar las condiciones comerciales prioritarias del alquiler.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($rental_content, 'tmd-commercial-landing__stats')
        && false === strpos($rental_content, 'tmd-commercial-landing__brand-note')
        && false === strpos($rental_content, '<strong>120</strong>')
        && false === strpos($rental_content, 'Desde 2000')
        && false === strpos($rental_content, 'Experiencia con equipos Yale, Crown, Clark, Jungheinrich y Hyster.'),
    'La página no debe incluir la franja de cifras ni la nota de marcas que el usuario pidió retirar.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($rental_content, 'Alquiler y venta de <em>montacargas eléctricos</em>'),
    'El seed no debe conservar el titular anterior del hero de alquiler.'
);

$old_eyebrow = '<span class="tmd-commercial-landing__eyebrow">Soluciones para Colombia</span>';
$old_heading = '<h1 id="tmd-rental-heading">Alquiler y venta de <em>montacargas eléctricos</em></h1>';
$old_lead = '<p class="tmd-commercial-landing__hero-lead">Equipos para centros de distribución, bodegas y plantas. Cuéntanos sobre tu operación y recibe asesoría para elegir una alternativa adecuada.</p>';
$outside_copy = '<p>Referencia previa conservada: ' . $old_heading . '</p>';
$legacy_hero = '<!-- wp:html --><section class="tmd-commercial-landing tmd-commercial-landing__hero tmd-commercial-landing__hero--rental">'
    . $old_eyebrow . $old_heading . $old_lead
    . '<a href="#formulario-montacargas">Solicitar cotización</a>'
    . '<span>Alquiler mínimo de 1 mes</span><span>Cobertura en Colombia</span><span>Alquiler sin operador</span>'
    . '</section>' . $outside_copy . '<!-- /wp:html -->';
$hero_update = tmd_commercial_landing_script_rental_hero_update_plan($legacy_hero);
tmd_commercial_landing_recipient_assert(
    hash('sha256', $legacy_hero) === $hero_update['before_sha256']
        && hash('sha256', $hero_update['content']) === $hero_update['target_sha256']
        && false !== strpos($hero_update['content'], $outside_copy),
    'El actualizador debe calcular hashes consistentes y preservar el resto del contenido.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($hero_update['content'], $old_eyebrow)
        && false !== strpos($hero_update['content'], 'Alquiler de <em>montacargas eléctricos</em>')
        && false !== strpos($hero_update['content'], 'Equipos propios con mantenimiento en nuestro taller técnico')
        && 1 === substr_count($hero_update['content'], $old_heading),
    'El plan debe aplicar solo la composición nueva del hero.'
);

$duplicate_hero_rejected = false;
try {
    tmd_commercial_landing_script_rental_hero_update_plan($legacy_hero . $legacy_hero);
} catch (RuntimeException $exception) {
    $duplicate_hero_rejected = true;
}
tmd_commercial_landing_recipient_assert(
    $duplicate_hero_rejected,
    'El actualizador debe detenerse si el contenido no contiene un único hero esperado.'
);

$heading_in_hero_position = strpos($legacy_hero, $old_heading);
$old_heading_only_outside_hero = substr_replace(
    $legacy_hero,
    '<h1 id="unexpected-heading">Texto distinto</h1>',
    $heading_in_hero_position,
    strlen($old_heading)
);
$old_heading_outside_rejected = false;
try {
    tmd_commercial_landing_script_rental_hero_update_plan($old_heading_only_outside_hero);
} catch (RuntimeException $exception) {
    $old_heading_outside_rejected = true;
}
tmd_commercial_landing_recipient_assert(
    $old_heading_outside_rejected,
    'Un texto anterior fuera del hero no debe servir para completar un hero incompleto.'
);

$GLOBALS['tmd_test_wp_page'] = new WP_Post(
    1558,
    'Alquiler de montacargas eléctricos',
    'page',
    'alquiler-montacargas-electricos',
    $legacy_hero
);
$before_update_calls = $GLOBALS['tmd_test_wp_update_calls'];
putenv('TMD_VERIFIED_BACKUP_PATH=' . $temp_dir);
$rollback_artifact_path = tmd_commercial_landing_script_save_rental_hero_rollback_artifact(
    [
        'ID' => 1558,
        'post_type' => 'page',
        'post_name' => 'alquiler-montacargas-electricos',
        'post_status' => 'publish',
        'post_content' => $legacy_hero,
    ],
    hash('sha256', $legacy_hero)
);
$rollback_artifact = json_decode((string) file_get_contents($rollback_artifact_path), true);
$rollback_artifact_permissions = fileperms($rollback_artifact_path);
tmd_commercial_landing_recipient_assert(
    is_array($rollback_artifact)
        && 1558 === ($rollback_artifact['post_id'] ?? null)
        && hash('sha256', $legacy_hero) === ($rollback_artifact['content_sha256'] ?? '')
        && $legacy_hero === ($rollback_artifact['post_content'] ?? null)
        && false !== $rollback_artifact_permissions
        && 0600 === ($rollback_artifact_permissions & 0777),
    'El artefacto de restauración debe guardar y verificar el contenido con permisos privados.'
);
putenv('TMD_COMMERCIAL_LANDINGS_EXECUTE=1');
putenv('TMD_COMMERCIAL_LANDING_HERO_EXPECTED_SHA256=' . str_repeat('0', 64));
putenv('TMD_COMMERCIAL_LANDING_HERO_TARGET_SHA256=' . $hero_update['target_sha256']);
$stale_content_rejected = false;
$stale_content_message = '';
try {
    tmd_commercial_landing_script_run_rental_hero_update(true);
} catch (RuntimeException $exception) {
    $stale_content_rejected = true;
    $stale_content_message = $exception->getMessage();
}
tmd_commercial_landing_recipient_assert(
    $stale_content_rejected
        && false !== strpos($stale_content_message, 'cambió desde la revisión')
        && $before_update_calls === $GLOBALS['tmd_test_wp_update_calls'],
    'Un hash actual desactualizado debe detener la actualización antes de wp_update_post().'
);

putenv('TMD_COMMERCIAL_LANDING_HERO_EXPECTED_SHA256=' . $hero_update['before_sha256']);
putenv('TMD_COMMERCIAL_LANDING_HERO_TARGET_SHA256=' . str_repeat('0', 64));
$wrong_target_rejected = false;
$wrong_target_message = '';
try {
    tmd_commercial_landing_script_run_rental_hero_update(true);
} catch (RuntimeException $exception) {
    $wrong_target_rejected = true;
    $wrong_target_message = $exception->getMessage();
}
tmd_commercial_landing_recipient_assert(
    $wrong_target_rejected
        && false !== strpos($wrong_target_message, 'no coincide con el hash aprobado')
        && $before_update_calls === $GLOBALS['tmd_test_wp_update_calls'],
    'Un hash objetivo incorrecto debe detener la actualización antes de wp_update_post().'
);

$backup_dir = $temp_dir . '/verified-backup';
mkdir($backup_dir, 0700);
$database_path = $backup_dir . '/database.sql';
$database_dump = "-- MariaDB dump\nCREATE TABLE wp_posts (ID BIGINT);\n"
    . str_repeat("-- fixture row\n", 5000)
    . '-- Dump completed on ' . gmdate('D M j H:i:s Y') . "\n";
file_put_contents($database_path, $database_dump);
chmod($database_path, 0600);
$backup_manifest = [
    'schema_version' => 1,
    'environment' => 'production',
    'backup_type' => 'full',
    'verified' => true,
    'database_file' => 'database.sql',
    'sql_format' => 'mariadb-dump',
    'sql_header_verified' => true,
    'dump_completion_marker_verified' => true,
    'created_at_utc' => gmdate('c'),
    'restore_path' => $backup_dir . '/database.sql',
    'restore_method' => 'fixture-only MariaDB restore procedure',
    'database_size_bytes' => filesize($database_path),
    'database_sha256' => hash_file('sha256', $database_path),
];
$manifest_path = $backup_dir . '/BACKUP_MANIFEST.json';
file_put_contents($manifest_path, json_encode($backup_manifest, JSON_UNESCAPED_SLASHES));
chmod($manifest_path, 0600);
putenv('TMD_VERIFIED_BACKUP_PATH=' . $backup_dir);
tmd_commercial_landing_recipient_assert(
    tmd_commercial_landing_script_backup_is_valid(),
    'El fixture debe cumplir los mismos controles de integridad del backup que exige el actualizador.'
);

$initial_page_row = [
    'ID' => 1558,
    'post_title' => 'Título actualizado durante una edición concurrente',
    'post_type' => 'page',
    'post_name' => 'alquiler-montacargas-electricos',
    'post_status' => 'publish',
    'post_content' => $legacy_hero,
];
$GLOBALS['wpdb'] = new TMD_Test_WPDB();
$GLOBALS['wpdb']->row = $initial_page_row;
$GLOBALS['tmd_test_wpdb_locked_row_override'] = $initial_page_row;
$GLOBALS['tmd_test_wpdb_locked_row_override']['post_content'] .= '<p>Edición concurrente</p>';
putenv('TMD_COMMERCIAL_LANDING_HERO_EXPECTED_SHA256=' . $hero_update['before_sha256']);
putenv('TMD_COMMERCIAL_LANDING_HERO_TARGET_SHA256=' . $hero_update['target_sha256']);
$concurrent_change_rejected = false;
$concurrent_change_message = '';
try {
    tmd_commercial_landing_script_run_rental_hero_update(true);
} catch (RuntimeException $exception) {
    $concurrent_change_rejected = true;
    $concurrent_change_message = $exception->getMessage();
}
tmd_commercial_landing_recipient_assert(
    $concurrent_change_rejected
        && false !== strpos($concurrent_change_message, 'cambió antes de obtener el bloqueo')
        && $before_update_calls === $GLOBALS['tmd_test_wp_update_calls']
        && false === $GLOBALS['wpdb']->in_transaction,
    'Una diferencia encontrada en la fila bloqueada debe revertir la transacción sin guardar.'
);

unset($GLOBALS['tmd_test_wpdb_locked_row_override']);
$GLOBALS['wpdb']->row = $initial_page_row;
$GLOBALS['tmd_test_wp_update_content_override'] = '<p>Resultado inesperado</p>';
$before_failed_update_calls = $GLOBALS['tmd_test_wp_update_calls'];
$failed_update_rejected = false;
$failed_update_message = '';
try {
    tmd_commercial_landing_script_run_rental_hero_update(true);
} catch (RuntimeException $exception) {
    $failed_update_rejected = true;
    $failed_update_message = $exception->getMessage();
}
$last_wpdb_query = end($GLOBALS['wpdb']->queries);
tmd_commercial_landing_recipient_assert(
    $failed_update_rejected
        && false !== strpos($failed_update_message, 'transacción se revirtió')
        && $before_failed_update_calls + 1 === $GLOBALS['tmd_test_wp_update_calls']
        && 'ROLLBACK' === $last_wpdb_query
        && false === $GLOBALS['wpdb']->in_transaction
        && $legacy_hero === $GLOBALS['wpdb']->row['post_content']
        && $legacy_hero === $GLOBALS['tmd_test_wp_page']->post_content,
    'Si la escritura produce contenido inesperado, debe revertir la transacción y verificar el original.'
);
unset($GLOBALS['tmd_test_wp_update_content_override']);
unlink($backup_dir . '/page-1558-content-before-hero-update.json');

$GLOBALS['tmd_test_wp_page'] = new WP_Post(
    1558,
    'Título de caché desactualizado',
    'page',
    'alquiler-montacargas-electricos',
    $legacy_hero
);
$GLOBALS['wpdb']->row = $initial_page_row;
$clean_cache_calls_before_success = $GLOBALS['tmd_test_clean_post_cache_calls'];
$before_successful_update_calls = $GLOBALS['tmd_test_wp_update_calls'];
tmd_commercial_landing_script_run_rental_hero_update(true);
$last_wpdb_query = end($GLOBALS['wpdb']->queries);
tmd_commercial_landing_recipient_assert(
    $before_successful_update_calls + 1 === $GLOBALS['tmd_test_wp_update_calls']
        && false === $GLOBALS['wpdb']->in_transaction
        && 'COMMIT' === $last_wpdb_query
        && $initial_page_row['post_title'] === $GLOBALS['tmd_test_wp_page']->post_title
        && $clean_cache_calls_before_success < $GLOBALS['tmd_test_clean_post_cache_calls']
        && hash('sha256', $hero_update['content']) === hash('sha256', $GLOBALS['tmd_test_wp_page']->post_content)
        && hash('sha256', $hero_update['content']) === hash('sha256', $GLOBALS['wpdb']->row['post_content']),
    'La ejecución autorizada debe actualizar la fila bloqueada, confirmar la transacción y verificar el contenido.'
);

putenv('TMD_VERIFIED_BACKUP_PATH');
putenv('TMD_COMMERCIAL_LANDING_HERO_EXPECTED_SHA256');
putenv('TMD_COMMERCIAL_LANDING_HERO_TARGET_SHA256');
putenv('TMD_COMMERCIAL_LANDINGS_EXECUTE');

putenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT');

echo "OK: destinatario, hero editable y plan de actualización acotado.\n";
