<?php

/** Focused contract and transaction tests for the DEC-11 battery page updater. */

$test_root = sys_get_temp_dir() . '/tmd-battery-maqueta-test-' . bin2hex(random_bytes(6));
$wordpress_root = $test_root . '/wordpress';
$backup_root = $test_root . '/backup';
mkdir($wordpress_root, 0700, true);
mkdir($backup_root, 0700, true);
define('ABSPATH', $wordpress_root . DIRECTORY_SEPARATOR);
define('WP_CLI', true);
define('ARRAY_A', 'ARRAY_A');

class WP_Post
{
    public $ID;
    public $post_title;
    public $post_type;
    public $post_name;
    public $post_content;
    public $post_status;

    public function __construct(int $id, string $title, string $type, string $name, string $content, string $status)
    {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_type = $type;
        $this->post_name = $name;
        $this->post_content = $content;
        $this->post_status = $status;
    }
}

class WP_CLI
{
    public static $messages = [];
    public static $form_saves = 0;
    public static $page_saves = 0;

    public static function line($message): void
    {
        self::$messages[] = ['line', (string) $message];
    }

    public static function success($message): void
    {
        self::$messages[] = ['success', (string) $message];
    }

    public static function error($message): void
    {
        throw new RuntimeException((string) $message);
    }
}

class WP_Error
{
}

class WPCF7_ContactForm
{
    public function id(): int
    {
        return 1557;
    }

    public function title(): string
    {
        return 'TMD | Cotización de baterías para montacargas';
    }

    public function locale(): string
    {
        return 'es_CO';
    }

    public function get_properties(): array
    {
        return $GLOBALS['tmd_battery_form_properties'];
    }
}

class TMD_Battery_Test_WPDB
{
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';
    public $last_error = '';
    public $in_transaction = false;
    public $snapshot = null;
    public $rollback_fails = false;
    public $on_start = null;
    public $queries = [];

    public function prepare(string $query, ...$args): string
    {
        foreach ($args as $arg) {
            $replacement = is_int($arg) ? (string) $arg : "'" . addslashes((string) $arg) . "'";
            $query = preg_replace('/%[ds]/', $replacement, $query, 1);
        }
        return $query;
    }

    public function get_var(string $query)
    {
        if (false !== strpos($query, '@@in_transaction')) {
            return $this->in_transaction ? '1' : '0';
        }
        return 'InnoDB';
    }

    public function get_row(string $query, $output = null): array
    {
        if (false !== strpos($query, 'ID = 1559')) {
            $page = $GLOBALS['tmd_battery_page'];
            return [
                'ID' => 1559,
                'post_type' => 'page',
                'post_name' => $page->post_name,
                'post_status' => $page->post_status,
                'post_content' => $page->post_content,
            ];
        }
        return [
            'ID' => 1557,
            'post_type' => 'wpcf7_contact_form',
            'post_title' => 'TMD | Cotización de baterías para montacargas',
            'post_status' => 'publish',
        ];
    }

    public function get_results(string $query, $output = null): array
    {
        return [];
    }

    public function query(string $query)
    {
        $this->queries[] = $query;
        if ('START TRANSACTION' === $query) {
            if (is_callable($this->on_start)) {
                ($this->on_start)();
                $this->on_start = null;
            }
            $this->snapshot = [
                'page' => $GLOBALS['tmd_battery_page']->post_content,
                'form' => $GLOBALS['tmd_battery_form_properties'],
            ];
            $this->in_transaction = true;
            return 1;
        }
        if ('COMMIT' === $query) {
            $this->snapshot = null;
            $this->in_transaction = false;
            return 1;
        }
        if ('ROLLBACK' === $query) {
            if ($this->rollback_fails) {
                return false;
            }
            if (is_array($this->snapshot)) {
                $GLOBALS['tmd_battery_page']->post_content = $this->snapshot['page'];
                $GLOBALS['tmd_battery_form_properties'] = $this->snapshot['form'];
            }
            $this->snapshot = null;
            $this->in_transaction = false;
            return 1;
        }
        return 1;
    }
}

function trailingslashit(string $path): string { return rtrim($path, '/\\') . DIRECTORY_SEPARATOR; }
function untrailingslashit(string $path): string { return rtrim($path, '/\\'); }
function get_temp_dir(): string { return $GLOBALS['tmd_battery_test_temp']; }
function get_stylesheet_directory_uri(): string { return 'https://example.test/wp-content/themes/blocksy-child'; }
function home_url(string $path = ''): string { return 'https://example.test' . $path; }
function esc_url($value): string { return (string) $value; }
function esc_url_raw($value): string { return (string) $value; }
function absint($value): int { return abs((int) $value); }
function wp_json_encode($value, int $options = 0) { return json_encode($value, $options); }
function wp_slash($value) { return addslashes($value); }
function is_wp_error($value): bool { return $value instanceof WP_Error; }
function clean_post_cache($post_id): void {}
function get_post($post_id) { return 1559 === (int) $post_id ? $GLOBALS['tmd_battery_page'] : null; }
function get_post_meta($post_id, string $key, $single = false) { return '2026-10-04-v1:battery'; }
function wpcf7_contact_form($id) { return 1557 === (int) $id ? new WPCF7_ContactForm() : false; }

function wpcf7_save_contact_form(array $data, string $context = 'save')
{
    ++WP_CLI::$form_saves;
    $GLOBALS['tmd_battery_form_properties'] = [
        'form' => $data['form'],
        'mail' => $data['mail'],
        'mail_2' => $data['mail_2'],
        'messages' => $data['messages'],
        'additional_settings' => $data['additional_settings'],
    ];
    if (! empty($GLOBALS['tmd_battery_drop_transaction_after_form_save'])) {
        $GLOBALS['wpdb']->in_transaction = false;
        $GLOBALS['tmd_battery_drop_transaction_after_form_save'] = false;
    }
    return new WPCF7_ContactForm();
}

function wp_update_post(array $postarr, bool $wp_error = false)
{
    ++WP_CLI::$page_saves;
    $content = stripslashes((string) $postarr['post_content']);
    $GLOBALS['tmd_battery_page']->post_content = $GLOBALS['tmd_battery_page_content_override'] ?? $content;
    return 1559;
}

function tmd_battery_test_assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
}

function tmd_battery_test_reset(): void
{
    $GLOBALS['tmd_battery_page'] = new WP_Post(
        1559,
        'Baterías para montacargas',
        'page',
        'baterias-para-montacargas',
        '<!-- contenido anterior -->',
        'publish'
    );
    $GLOBALS['tmd_battery_form_properties'] = [
        'form' => '<div>[text tmd_website tabindex:-1 autocomplete:off]</div>',
        'mail' => ['recipient' => 'cotizaciones@example.test', 'body' => 'Solicitud anterior'],
        'mail_2' => ['active' => false, 'recipient' => ''],
        'messages' => ['mail_sent_ok' => 'Recibido'],
        'additional_settings' => 'demo_setting: retained',
    ];
    $GLOBALS['wpdb'] = new TMD_Battery_Test_WPDB();
    $GLOBALS['tmd_battery_page_content_override'] = null;
    $GLOBALS['tmd_battery_drop_transaction_after_form_save'] = false;
    WP_CLI::$messages = [];
    WP_CLI::$form_saves = 0;
    WP_CLI::$page_saves = 0;
    @unlink($GLOBALS['tmd_battery_test_backup'] . '/battery-page-1559-form-1557-before-maqueta.json');

    $page_target = tmd_commercial_landing_script_page_content('battery', 1557);
    $form_target = $GLOBALS['tmd_battery_form_properties'];
    $form_target['form'] = tmd_commercial_landing_script_form_markup('battery-maqueta');
    $form_target['mail']['body'] = tmd_commercial_landing_script_battery_mail_body();
    putenv('TMD_BATTERY_PAGE_EXPECTED_SHA256=' . hash('sha256', $GLOBALS['tmd_battery_page']->post_content));
    putenv('TMD_BATTERY_PAGE_TARGET_SHA256=' . hash('sha256', $page_target));
    putenv('TMD_BATTERY_FORM_EXPECTED_SHA256=' . tmd_commercial_landing_script_hash_form_properties($GLOBALS['tmd_battery_form_properties']));
    putenv('TMD_BATTERY_FORM_TARGET_SHA256=' . tmd_commercial_landing_script_hash_form_properties($form_target));
    putenv('TMD_VERIFIED_BACKUP_PATH=' . $GLOBALS['tmd_battery_test_backup']);
}

function tmd_battery_test_remove_tree(string $path): void
{
    foreach (glob($path . DIRECTORY_SEPARATOR . '*') ?: [] as $child) {
        if (is_dir($child) && ! is_link($child)) {
            tmd_battery_test_remove_tree($child);
        } else {
            @unlink($child);
        }
    }
    if (is_dir($path)) {
        @rmdir($path);
    }
}

$GLOBALS['tmd_battery_test_temp'] = $test_root . '/tmp';
$GLOBALS['tmd_battery_test_backup'] = $backup_root;
mkdir($GLOBALS['tmd_battery_test_temp'], 0700);
$GLOBALS['wpdb'] = new TMD_Battery_Test_WPDB();
register_shutdown_function(static function () use ($test_root): void {
    tmd_battery_test_remove_tree($test_root);
});

putenv('TMD_COMMERCIAL_LANDINGS_MODE=battery-test-load');
try {
    require dirname(__DIR__) . '/scripts/create-commercial-landing-pages.php';
} catch (RuntimeException $exception) {
    tmd_battery_test_assert(
        'El modo de ejecución comercial indicado no está reconocido.' === $exception->getMessage(),
        'el cargador de pruebas debe alcanzar el dispatcher sin disparar otro error'
    );
}
putenv('TMD_COMMERCIAL_LANDINGS_MODE');

try {
    $page_content = tmd_commercial_landing_script_page_content('battery', 1557);
    $section_count = preg_match_all('/<section\b/', $page_content);
    $details_count = preg_match_all('/<details\b/', $page_content);
    tmd_battery_test_assert(7 === $section_count, 'la maqueta debe tener siete secciones HTML, además del formulario y el blog');
    tmd_battery_test_assert(6 === $details_count && ! preg_match('/<details\b[^>]*\bopen\b/i', $page_content), 'las seis FAQ deben quedar cerradas por defecto');
    tmd_battery_test_assert(5 === preg_match_all('/<figure\b/', substr($page_content, strpos($page_content, 'tmd-commercial-landing__battery-gallery'), strpos($page_content, '</section>', strpos($page_content, 'tmd-commercial-landing__battery-gallery')) - strpos($page_content, 'tmd-commercial-landing__battery-gallery'))), 'la galería debe mostrar cinco imágenes sin leyendas');
    tmd_battery_test_assert(false === strpos($page_content, '<figcaption') && false === strpos($page_content, 'TMD_ASSETS'), 'el contenido no debe dejar leyendas ni rutas temporales');

    $ordered_parts = [
        'id="tmd-battery-heading"',
        'id="tmd-battery-solutions-heading"',
        'id="tmd-battery-differential-heading"',
        'id="tmd-battery-benefits-heading"',
        'id="tmd-battery-process-heading"',
        'id="tmd-battery-gallery-heading"',
        'type="battery-maqueta"',
        'id="tmd-battery-faq-heading"',
        'fallback="none"',
    ];
    $previous_position = -1;
    foreach ($ordered_parts as $part) {
        $position = strpos($page_content, $part);
        tmd_battery_test_assert(false !== $position && $position > $previous_position, 'la sección ' . $part . ' debe conservar el orden DEC-11');
        $previous_position = $position;
    }

    $form_markup = tmd_commercial_landing_script_form_markup('battery-maqueta');
    $labels = ['Nombre y cargo', 'Empresa y ciudad', 'Correo o celular', 'Marca y modelo del montacargas', 'Voltaje y capacidad de la batería actual', 'Compra o alquiler'];
    tmd_battery_test_assert(6 === preg_match_all('/class="tmd-landing-form__field/', $form_markup), 'el formulario debe contener seis campos comerciales');
    foreach ($labels as $label) {
        tmd_battery_test_assert(false !== strpos($form_markup, '<span>' . $label . '</span>'), 'falta la etiqueta compuesta: ' . $label);
    }
    tmd_battery_test_assert(1 === substr_count($form_markup, '[checkbox* privacidad'), 'el consentimiento debe seguir siendo obligatorio');
    tmd_battery_test_assert(false !== strpos($form_markup, 'tmd_website tabindex:-1 autocomplete:off'), 'el formulario debe conservar el honeypot');

    $css = (string) file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css');
    tmd_battery_test_assert((bool) preg_match('/--tmd-landing-navy:\s*#262e4f\s*;/i', $css), 'el azul marino aprobado debe ser #262E4F');
    tmd_battery_test_assert((bool) preg_match('/\.tmd-commercial-landing__form-layout--battery-maqueta \.wpcf7-submit\s*\{[^}]*background:\s*var\(--tmd-landing-navy\);[^}]*color:\s*#fff;/s', $css), 'el CTA debe usar fondo aprobado y texto blanco');

    $asset_directory = dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/img/commercial-landings/baterias-maqueta';
    $catalog_assets = [
        'CROWN RD 3000/1.png' => 'banner-crown-rd-3000.webp',
        'HYSTER E50Z-33/2.png' => 'diferencial-hyster-e50z-33.webp',
        'ETV214/1.png' => 'ventajas-etv214.webp',
        'EFG425/1.png' => 'proceso-efg425.webp',
        'CROWN RD 5200/2.png' => 'galeria-crown-rd-5200.webp',
        'CROWN PE 4000-60/1.png' => 'galeria-crown-pe-4000-60.webp',
        'EJC112/1.png' => 'galeria-ejc112.webp',
        'ERE225/1.png' => 'galeria-ere225.webp',
        'ETV325/1.png' => 'galeria-etv325.webp',
        'ETV214/2.png' => 'formulario-etv214.webp',
    ];
    foreach ($catalog_assets as $source_relative => $destination_name) {
        $source_path = dirname(__DIR__) . '/EQUIPOS SEGUN REFERENCIA/' . $source_relative;
        $destination_path = $asset_directory . '/' . $destination_name;
        tmd_battery_test_assert(is_file($source_path) && is_file($destination_path), 'deben existir el origen y la copia del catálogo: ' . $source_relative);
        $source_image = getimagesize($source_path);
        $destination_image = getimagesize($destination_path);
        tmd_battery_test_assert(
            is_array($source_image)
                && is_array($destination_image)
                && IMAGETYPE_PNG === $source_image[2]
                && IMAGETYPE_WEBP === $destination_image[2]
                && $source_image[0] === $destination_image[0]
                && $source_image[1] === $destination_image[1]
                && filesize($destination_path) < filesize($source_path),
            'la copia WebP debe conservar dimensiones y reducir bytes frente a la fuente: ' . $destination_name
        );
    }
    tmd_battery_test_assert(false !== strpos($page_content, 'commercial-landings/baterias-maqueta/banner-crown-rd-3000.webp'), 'el hero debe usar la copia local del catálogo');
    tmd_battery_test_assert(false !== strpos($page_content, 'commercial-landings/baterias-maqueta/galeria-etv325.webp'), 'la galería debe usar copias locales del catálogo');
    $theme_inc = (string) file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php');
    tmd_battery_test_assert(false !== strpos($theme_inc, 'commercial-landings/baterias-maqueta/formulario-etv214.webp'), 'la sección de cotización debe usar su copia local del catálogo');

    $sql_tail = "\n-- Dump completed on " . gmdate('Y-m-d H:i:s') . "\n";
    $sql_header = "-- MariaDB dump\nCREATE TABLE `wp_posts` (`ID` bigint);\n";
    $sql = str_pad($sql_header, 66000 - strlen($sql_tail), ' ') . $sql_tail;
    file_put_contents($backup_root . '/database.sql', $sql);
    chmod($backup_root . '/database.sql', 0600);
    $manifest = [
        'schema_version' => 1,
        'environment' => 'production',
        'backup_type' => 'full',
        'verified' => true,
        'database_file' => 'database.sql',
        'sql_format' => 'mariadb-dump',
        'sql_header_verified' => true,
        'dump_completion_marker_verified' => true,
        'created_at_utc' => gmdate('c'),
        'restore_path' => '/private/test-restore',
        'restore_method' => 'test-only fixture',
        'database_size_bytes' => strlen($sql),
        'database_sha256' => hash('sha256', $sql),
    ];
    file_put_contents($backup_root . '/BACKUP_MANIFEST.json', json_encode($manifest));
    chmod($backup_root . '/BACKUP_MANIFEST.json', 0600);

    tmd_battery_test_reset();
    tmd_battery_test_assert(tmd_commercial_landing_script_backup_is_valid(), 'el fixture no sensible debe pasar la validación de backup requerido');
    ob_start();
    tmd_commercial_landing_script_run_battery_maqueta_update(false);
    ob_end_clean();
    tmd_battery_test_assert(0 === WP_CLI::$form_saves && 0 === WP_CLI::$page_saves && ! $GLOBALS['wpdb']->in_transaction, 'el dry-run no debe iniciar transacciones ni guardar');
    tmd_battery_test_assert(false !== strpos(implode("\n", array_column(WP_CLI::$messages, 1)), 'Dry-run de la maqueta'), 'el dry-run debe confirmar su resultado');

    tmd_battery_test_reset();
    putenv('TMD_BATTERY_PAGE_EXPECTED_SHA256=' . str_repeat('0', 64));
    $stale_rejected = false;
    try {
        tmd_commercial_landing_script_run_battery_maqueta_update(true);
    } catch (RuntimeException $exception) {
        $stale_rejected = false !== strpos($exception->getMessage(), 'huellas de origen o destino');
    }
    tmd_battery_test_assert($stale_rejected && 0 === WP_CLI::$form_saves && 0 === WP_CLI::$page_saves, 'un hash de origen obsoleto debe rechazarse antes de cualquier escritura');

    tmd_battery_test_reset();
    $expected_page = hash('sha256', $GLOBALS['tmd_battery_page']->post_content);
    $expected_form = tmd_commercial_landing_script_hash_form_properties($GLOBALS['tmd_battery_form_properties']);
    ob_start();
    tmd_commercial_landing_script_run_battery_maqueta_update(true);
    ob_end_clean();
    tmd_battery_test_assert(! $GLOBALS['wpdb']->in_transaction && 1 === WP_CLI::$form_saves && 1 === WP_CLI::$page_saves, 'la ejecución aprobada debe guardar página y formulario en una transacción');
    tmd_battery_test_assert(hash('sha256', $GLOBALS['tmd_battery_page']->post_content) === getenv('TMD_BATTERY_PAGE_TARGET_SHA256'), 'la página debe terminar con el hash destino');
    tmd_battery_test_assert(tmd_commercial_landing_script_hash_form_properties($GLOBALS['tmd_battery_form_properties']) === getenv('TMD_BATTERY_FORM_TARGET_SHA256'), 'el formulario debe terminar con el hash destino completo');
    tmd_battery_test_assert(is_file($backup_root . '/battery-page-1559-form-1557-before-maqueta.json'), 'la ejecución debe guardar el snapshot previo privado');
    tmd_battery_test_assert($expected_page !== hash('sha256', $GLOBALS['tmd_battery_page']->post_content) && $expected_form !== tmd_commercial_landing_script_hash_form_properties($GLOBALS['tmd_battery_form_properties']), 'el destino debe diferir del estado original');

    tmd_battery_test_reset();
    $GLOBALS['tmd_battery_page_content_override'] = '<p>contenido incorrecto</p>';
    ob_start();
    try {
        tmd_commercial_landing_script_run_battery_maqueta_update(true);
    } catch (RuntimeException $exception) {
        tmd_battery_test_assert(false !== strpos($exception->getMessage(), 'No se verificó el guardado del contenido'), 'el fallo de guardado debe reportar verificación de página');
    }
    $failure_output = ob_get_clean() . "\n" . implode("\n", array_column(WP_CLI::$messages, 1));
    tmd_battery_test_assert(! $GLOBALS['wpdb']->in_transaction, 'el fallo verificable debe cerrar la transacción');
    tmd_battery_test_assert(hash('sha256', $GLOBALS['tmd_battery_page']->post_content) === getenv('TMD_BATTERY_PAGE_EXPECTED_SHA256'), 'el rollback debe restaurar el contenido de origen');
    tmd_battery_test_assert(tmd_commercial_landing_script_hash_form_properties($GLOBALS['tmd_battery_form_properties']) === getenv('TMD_BATTERY_FORM_EXPECTED_SHA256'), 'el rollback debe restaurar la configuración original de CF7');
    tmd_battery_test_assert(false !== strpos($failure_output, 'Recuperación verificada'), 'el rollback completo debe quedar diagnosticado');

    tmd_battery_test_reset();
    $GLOBALS['wpdb']->rollback_fails = true;
    $GLOBALS['tmd_battery_page_content_override'] = '<p>contenido incorrecto</p>';
    ob_start();
    try {
        tmd_commercial_landing_script_run_battery_maqueta_update(true);
    } catch (RuntimeException $exception) {
        // The expected save verification failure enters the rollback diagnostic.
    }
    $failure_output = ob_get_clean() . "\n" . implode("\n", array_column(WP_CLI::$messages, 1));
    tmd_battery_test_assert($GLOBALS['wpdb']->in_transaction, 'si ROLLBACK falla, la prueba debe conservar una transacción activa');
    tmd_battery_test_assert(false !== strpos($failure_output, 'ESTADO NO CONFIRMADO') && false === strpos($failure_output, 'Recuperación verificada'), 'un rollback fallido no debe afirmar recuperación');

    tmd_battery_test_reset();
    $GLOBALS['tmd_battery_drop_transaction_after_form_save'] = true;
    ob_start();
    try {
        tmd_commercial_landing_script_run_battery_maqueta_update(true);
    } catch (RuntimeException $exception) {
        tmd_battery_test_assert(false !== strpos($exception->getMessage(), 'transacción terminó durante el guardado'), 'una pérdida transaccional debe detener el update de página');
    }
    $failure_output = ob_get_clean() . "\n" . implode("\n", array_column(WP_CLI::$messages, 1));
    tmd_battery_test_assert(0 === WP_CLI::$page_saves, 'si CF7 termina la transacción, la página no debe guardarse');
    tmd_battery_test_assert(false !== strpos($failure_output, 'ESTADO INCIERTO'), 'un estado parcial tras perder la transacción debe quedar explícito');

    tmd_battery_test_reset();
    $GLOBALS['wpdb']->on_start = static function (): void {
        $GLOBALS['tmd_battery_page']->post_content = '<p>cambio concurrente</p>';
    };
    ob_start();
    try {
        tmd_commercial_landing_script_run_battery_maqueta_update(true);
    } catch (RuntimeException $exception) {
        tmd_battery_test_assert(false !== strpos($exception->getMessage(), 'cambiaron antes del bloqueo'), 'un cambio concurrente debe invalidar el hash antes de guardar');
    }
    $failure_output = ob_get_clean() . "\n" . implode("\n", array_column(WP_CLI::$messages, 1));
    tmd_battery_test_assert(0 === WP_CLI::$form_saves && 0 === WP_CLI::$page_saves, 'el cambio concurrente debe abortar antes de guardar página o formulario');
    tmd_battery_test_assert(false !== strpos($failure_output, 'ESTADO INCIERTO'), 'el cambio concurrente fuera de la transacción debe quedar diagnosticado');

    echo "OK: contrato DEC-11, CTA, dry-run, hashes, snapshot, commit y fallos transaccionales de baterías.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
