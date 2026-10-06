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
    private $local_properties = null;

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
        return is_array($this->local_properties)
            ? $this->local_properties
            : $GLOBALS['tmd_battery_form_properties'];
    }

    public function set_properties(array $properties): void
    {
        $this->local_properties = array_merge($this->get_properties(), $properties);
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
function has_action(string $hook, string $callback = '')
{
    return 'wpcf7_save_contact_form' === $hook
        && 'wpcf7_sendinblue_save_contact_form' === $callback
        && ! empty($GLOBALS['tmd_battery_sendinblue_hook_registered'])
        ? 10
        : false;
}
function remove_action(string $hook, string $callback, int $priority = 10): bool
{
    if (false === has_action($hook, $callback) || 10 !== $priority) {
        return false;
    }
    $GLOBALS['tmd_battery_sendinblue_hook_registered'] = false;
    return true;
}
function add_action(string $hook, string $callback, int $priority = 10, int $accepted_args = 1): bool
{
    if ('wpcf7_save_contact_form' !== $hook
        || 'wpcf7_sendinblue_save_contact_form' !== $callback
        || 10 !== $priority
        || 3 !== $accepted_args) {
        return false;
    }
    $GLOBALS['tmd_battery_sendinblue_hook_registered'] = true;
    return true;
}

function wpcf7_sanitize_form(string $input): string
{
    return trim($input);
}

function wpcf7_sanitize_mail(array $input): array
{
    $values = array_merge([
        'active' => false,
        'subject' => '',
        'sender' => '',
        'recipient' => '',
        'body' => '',
        'additional_headers' => '',
        'attachments' => '',
        'use_html' => false,
        'exclude_blank' => false,
    ], $input);

    return [
        'active' => (bool) $values['active'],
        'subject' => trim((string) $values['subject']),
        'sender' => trim((string) $values['sender']),
        'recipient' => trim((string) $values['recipient']),
        'body' => trim((string) $values['body']),
        'additional_headers' => trim((string) $values['additional_headers']),
        'attachments' => trim((string) $values['attachments']),
        'use_html' => (bool) $values['use_html'],
        'exclude_blank' => (bool) $values['exclude_blank'],
    ];
}

function wpcf7_sanitize_messages(array $input): array
{
    return array_map(static fn ($message): string => trim((string) $message), $input);
}

function wpcf7_sanitize_additional_settings(string $input): string
{
    return trim($input);
}

function wpcf7_save_contact_form(array $data, string $context = 'save')
{
    ++WP_CLI::$form_saves;
    $GLOBALS['tmd_battery_form_properties'] = [
        'form' => wpcf7_sanitize_form($data['form']),
        'mail' => wpcf7_sanitize_mail($data['mail']),
        'mail_2' => wpcf7_sanitize_mail($data['mail_2']),
        'messages' => wpcf7_sanitize_messages($data['messages']),
        'additional_settings' => wpcf7_sanitize_additional_settings($data['additional_settings']),
    ];
    $GLOBALS['tmd_battery_form_properties']['mail']['active'] = true;
    if (false !== has_action('wpcf7_save_contact_form', 'wpcf7_sendinblue_save_contact_form')) {
        $GLOBALS['tmd_battery_form_properties']['sendinblue'] = [
            'enable_contact_list' => false,
            'contact_lists' => [],
            'enable_transactional_email' => false,
            'email_template' => 0,
        ];
    }
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
    $GLOBALS['tmd_battery_sendinblue_hook_registered'] = true;
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
    $form_target = tmd_commercial_landing_script_normalize_battery_form_properties(
        new WPCF7_ContactForm(),
        $form_target
    );
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
    $battery_hero_match = [];
    tmd_battery_test_assert(
        1 === preg_match('/<section\b[^>]*tmd-commercial-landing__hero--battery-maqueta[^>]*>[\s\S]*?<\/section>/', $page_content, $battery_hero_match),
        'el hero de batería de la maqueta debe existir'
    );
    $battery_hero = $battery_hero_match[0];
    tmd_battery_test_assert(false !== strpos($battery_hero, 'Baterías para montacargas <em>eléctricos</em>'), 'el hero debe conservar el H1 aprobado');
    tmd_battery_test_assert(false !== strpos($battery_hero, 'Representantes de la marca francesa Barbillon'), 'el hero debe conservar el subtítulo aprobado');
    tmd_battery_test_assert(false !== strpos($battery_hero, 'Venta y alquiler para flotas de bodega, con cargador del mismo voltaje y registro BMS de la carga y la descarga.'), 'el hero debe conservar el párrafo aprobado');
    tmd_battery_test_assert(
        false !== strpos($battery_hero, 'commercial-landings/baterias-referencias/banner-bateria-barbillon.webp')
            && (bool) preg_match('/<img\b(?=[^>]*alt="")[^>]*commercial-landings\/baterias-referencias\/banner-bateria-barbillon\.webp[^>]*>/', $battery_hero),
        'el hero debe conservar la imagen y su alternativa vacía'
    );
    $battery_support_match = [];
    tmd_battery_test_assert(
        1 === preg_match('/<ul\b[^>]*class="[^"]*tmd-commercial-landing__hero-supports[^"]*"[^>]*>([\s\S]*?)<\/ul>/', $battery_hero, $battery_support_match),
        'el hero debe incluir una lista semántica de apoyos'
    );
    $battery_supports = $battery_support_match[1];
    $support_specs = [
        'performance' => 'Alto rendimiento para jornadas exigentes',
        'durability' => 'Equipos confiables y de larga vida útil',
        'advisory' => 'Asesoría especializada según tu operación',
    ];
    $support_items = [];
    tmd_battery_test_assert(
        3 === preg_match_all('/<li\b[^>]*tmd-commercial-landing__hero-support--(performance|durability|advisory)[^>]*>[\s\S]*?<\/li>/', $battery_supports, $support_items),
        'el hero debe mostrar exactamente tres apoyos tipados'
    );
    $previous_support_position = -1;
    foreach ($support_specs as $support_type => $support_copy) {
        $support_position = strpos($battery_supports, $support_copy);
        tmd_battery_test_assert(false !== $support_position && $support_position > $previous_support_position, 'los apoyos deben conservar el orden aprobado: ' . $support_copy);
        $support_item_pattern = '/<li\b[^>]*tmd-commercial-landing__hero-support--' . preg_quote($support_type, '/') . '[^>]*>([\s\S]*?)<\/li>/';
        $support_item_match = [];
        tmd_battery_test_assert(1 === preg_match($support_item_pattern, $battery_supports, $support_item_match), 'el icono debe corresponder al apoyo ' . $support_type);
        tmd_battery_test_assert(false !== strpos($support_item_match[1], 'tmd-commercial-landing__hero-support-icon--' . $support_type), 'el apoyo ' . $support_type . ' debe tener su icono correspondiente');
        tmd_battery_test_assert(false !== strpos($support_item_match[1], $support_copy), 'el apoyo ' . $support_type . ' debe conservar su frase');
        tmd_battery_test_assert((bool) preg_match('/<svg\b[^>]*aria-hidden="true"[^>]*>/', $support_item_match[1]), 'el SVG de ' . $support_type . ' debe ser decorativo');
        $previous_support_position = $support_position;
    }
    tmd_battery_test_assert(false !== strpos($battery_hero, '</p>') && strpos($battery_hero, '</p>') < strpos($battery_hero, '<ul class="tmd-commercial-landing__hero-supports"'), 'los apoyos deben aparecer inmediatamente después del párrafo');
    $css = (string) file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css');
    tmd_battery_test_assert((bool) preg_match('/--tmd-landing-navy:\s*#262e4f\s*;/i', $css), 'el azul marino aprobado debe ser #262E4F');
    tmd_battery_test_assert((bool) preg_match('/\.tmd-commercial-landing__form-layout--battery-maqueta \.wpcf7-submit\s*\{[^}]*background:\s*var\(--tmd-landing-navy\);[^}]*color:\s*#fff;/s', $css), 'el CTA debe usar fondo aprobado y texto blanco');
    $hero_image_css = [];
    tmd_battery_test_assert(
        1 === preg_match('/\.tmd-commercial-landing__hero--battery-maqueta \.tmd-commercial-landing__hero-media img\s*\{([^}]*)\}/s', $css, $hero_image_css)
            && (bool) preg_match('/\bwidth:\s*90%\s*;/i', $hero_image_css[1])
            && (bool) preg_match('/\bmax-width:\s*none\s*;/i', $hero_image_css[1])
            && (bool) preg_match('/\bobject-fit:\s*contain\s*;/i', $hero_image_css[1])
            && (bool) preg_match('/\bobject-position:\s*right\s+center\s*;/i', $hero_image_css[1])
            && (bool) preg_match('/\bmargin-left:\s*auto\s*;/i', $hero_image_css[1])
            && (bool) preg_match('/\btransform:\s*none\s*;/i', $hero_image_css[1]),
        'el encuadre de escritorio debe alejar la imagen y mostrarla completa alineada a la derecha'
    );
    $hero_support_css = [];
    tmd_battery_test_assert(
        1 === preg_match('/\.tmd-commercial-landing__hero--battery-maqueta \.tmd-commercial-landing__hero-supports\s*\{([^}]*)\}/s', $css, $hero_support_css)
            && (bool) preg_match('/\bgrid-template-columns:\s*repeat\(3,\s*minmax\(0,\s*1fr\)\)\s*;/i', $hero_support_css[1]),
        'los apoyos deben tener tres columnas de escritorio dentro del selector exclusivo de baterías'
    );
    $hero_support_item_css = [];
    tmd_battery_test_assert(
        1 === preg_match('/\.tmd-commercial-landing__hero--battery-maqueta \.tmd-commercial-landing__hero-support\s*\{([^}]*)\}/s', $css, $hero_support_item_css)
            && (bool) preg_match('/\bgrid-template-columns:\s*40px\s+minmax\(0,\s*1fr\)\s*;/i', $hero_support_item_css[1]),
        'cada apoyo debe conservar el icono a la izquierda de su texto'
    );
    $hero_support_icon_css = [];
    tmd_battery_test_assert(
        1 === preg_match('/\.tmd-commercial-landing__hero--battery-maqueta \.tmd-commercial-landing__hero-support-icon\s*\{([^}]*)\}/s', $css, $hero_support_icon_css)
            && (bool) preg_match('/\bcolor:\s*#f04d2d\s*;/i', $hero_support_icon_css[1])
            && (bool) preg_match('/\bborder-radius:\s*50%\s*;/i', $hero_support_icon_css[1]),
        'cada icono debe tener un contorno circular rojo anaranjado'
    );
    $hero_mobile_image_css = [];
    tmd_battery_test_assert(
        1 === preg_match('/@media\s*\(max-width:\s*760px\)[\s\S]*?\.tmd-commercial-landing__hero--battery-maqueta \.tmd-commercial-landing__hero-media img\s*\{([^}]*)\}/s', $css, $hero_mobile_image_css)
            && (bool) preg_match('/\bwidth:\s*100%\s*;/i', $hero_mobile_image_css[1])
            && (bool) preg_match('/\bobject-fit:\s*contain\s*;/i', $hero_mobile_image_css[1])
            && (bool) preg_match('/\bobject-position:\s*right\s+top\s*;/i', $hero_mobile_image_css[1]),
        'en móvil la imagen del hero debe verse completa arriba y usar el ancho disponible'
    );
    $hero_mobile_support_css = [];
    tmd_battery_test_assert(
        1 === preg_match('/@media\s*\(max-width:\s*760px\)[\s\S]*?\.tmd-commercial-landing__hero--battery-maqueta \.tmd-commercial-landing__hero-supports\s*\{([^}]*)\}/s', $css, $hero_mobile_support_css)
            && (bool) preg_match('/\bgrid-template-columns:\s*minmax\(0,\s*1fr\)\s*;/i', $hero_mobile_support_css[1]),
        'en móvil los apoyos deben apilarse en una columna dentro del selector exclusivo de baterías'
    );
    tmd_battery_test_assert(5 === preg_match_all('/<figure\b/', substr($page_content, strpos($page_content, 'tmd-commercial-landing__battery-gallery'), strpos($page_content, '</section>', strpos($page_content, 'tmd-commercial-landing__battery-gallery')) - strpos($page_content, 'tmd-commercial-landing__battery-gallery'))), 'la galería debe mostrar cinco imágenes sin leyendas');
    tmd_battery_test_assert(false === strpos($page_content, '<figcaption') && false === strpos($page_content, 'TMD_ASSETS'), 'el contenido no debe dejar leyendas ni rutas temporales');
    $gallery_content = substr($page_content, strpos($page_content, 'tmd-commercial-landing__battery-gallery'), strpos($page_content, '</section>', strpos($page_content, 'tmd-commercial-landing__battery-gallery')) - strpos($page_content, 'tmd-commercial-landing__battery-gallery'));
    $gallery_images = [];
    preg_match_all('/<figure>\s*<img\b[^>]*src="([^"]+)"[^>]*alt="([^"]*)"[^>]*>\s*<\/figure>/', $gallery_content, $gallery_images, PREG_SET_ORDER);
    $verified_brand_gallery_assets = [
        'commercial-landings/baterias-referencias/galeria-bateria-celdas-barbillon.webp',
        'commercial-landings/baterias-referencias/galeria-bateria-traccion-barbillon.webp',
    ];
    $gallery_branding_is_verified = false !== strpos($page_content, 'Baterías y equipos eléctricos en operación')
        && 5 === count($gallery_images);
    foreach ($gallery_images as $gallery_image) {
        if (false !== strpos($gallery_image[2], 'Barbillon')) {
            $has_verified_brand_source = false;
            foreach ($verified_brand_gallery_assets as $verified_brand_gallery_asset) {
                $has_verified_brand_source = $has_verified_brand_source
                    || false !== strpos($gallery_image[1], $verified_brand_gallery_asset);
            }
            $gallery_branding_is_verified = $gallery_branding_is_verified && $has_verified_brand_source;
        }
    }
    tmd_battery_test_assert($gallery_branding_is_verified, 'la galería solo debe atribuir Barbillon a las imágenes de batería identificadas con esa marca');
    tmd_battery_test_assert(false !== strpos($gallery_content, 'commercial-landings/baterias-hero.jpeg') && false !== strpos($gallery_content, 'mega-menu/energy-baterias-plomo.webp') && false !== strpos($gallery_content, 'mega-menu/energy-bms.webp'), 'la galería debe incluir imágenes existentes de batería y monitoreo');

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

    $mail_body = tmd_commercial_landing_script_battery_mail_body();
    $mail_fields = [
        'Nombre y cargo' => ['field' => 'nombre_cargo', 'mail_label' => 'Nombre y cargo', 'mail_tag' => '[nombre_cargo]', 'form_tag' => '[text* nombre_cargo autocomplete:name]'],
        'Empresa y ciudad' => ['field' => 'empresa_ciudad', 'mail_label' => 'Empresa y ciudad', 'mail_tag' => '[empresa_ciudad]', 'form_tag' => '[text* empresa_ciudad]'],
        'Correo o celular' => ['field' => 'contacto', 'mail_label' => 'Correo o celular', 'mail_tag' => '[contacto]', 'form_tag' => '[text* contacto]'],
        'Marca y modelo del montacargas' => ['field' => 'marca_modelo', 'mail_label' => 'Marca y modelo', 'mail_tag' => '[marca_modelo]', 'form_tag' => '[text* marca_modelo]'],
        'Voltaje y capacidad de la batería actual' => ['field' => 'voltaje_capacidad', 'mail_label' => 'Voltaje y capacidad actuales', 'mail_tag' => '[voltaje_capacidad]', 'form_tag' => '[text* voltaje_capacidad]'],
        'Compra o alquiler' => ['field' => 'modalidad', 'mail_label' => 'Modalidad', 'mail_tag' => '[modalidad]', 'form_tag' => '[select* modalidad include_blank "Compra" "Alquiler"]'],
    ];
    foreach ($mail_fields as $label => $field) {
        tmd_battery_test_assert(
            false !== strpos($form_markup, '<span>' . $label . '</span>' . $field['form_tag']),
            'el campo visible ' . $label . ' debe conservar el control CF7 ' . $field['field']
        );
        tmd_battery_test_assert(
            false !== strpos($mail_body, $field['mail_label'] . ': ' . $field['mail_tag']),
            'el correo de cotización debe incluir ' . $label . ' (' . $field['mail_tag'] . ')'
        );
    }

    $required_copy = [
        'Baterías para montacargas <em>eléctricos</em>',
        'Representantes de la marca francesa Barbillon',
        'Venta y alquiler para flotas de bodega, con cargador del mismo voltaje y registro BMS de la carga y la descarga.',
        '<span class="tmd-commercial-landing__solutions-title-accent">Baterías de tracción</span>, cargadores y monitoreo BMS',
        'Compatibles con retráctiles, apiladores, estibadores, tomapedidos y equipos de pasillo angosto',
        'Baterías de tracción plomo-ácido:',
        'seleccionadas por voltaje, amperios hora y dimensiones del compartimiento.',
        'Cargadores industriales:',
        'corriente y voltaje definidos según la tecnología de la batería.',
        'Monitoreo BMS:',
        'registro de temperatura, ciclos y descargas profundas durante la operación.',
        'Referencias en <em>inventario</em>',
        'Referencias en bodega para reemplazar la batería sin esperar un pedido de importación',
        'Cotizamos las referencias en existencia junto con su cargador. Si la batería de tu equipo necesita mantenimiento y no reemplazo, nuestro servicio técnico revisa las conexiones, el nivel de electrolito y el comportamiento de carga antes de recomendarte una batería nueva.',
        'Rendimiento de la batería de tracción por turno',
        'Descargas profundas y cargas incompletas acortan la vida de las celdas',
        'Autonomía para el turno:</strong> amperios hora calculados sobre las horas de uso del equipo.',
        'Vida útil de las celdas:</strong> ciclos de carga completos con un cargador del mismo voltaje.',
        'Carga entre turnos:</strong> corriente del cargador ajustada al tiempo disponible para completar el ciclo.',
        'Cambio de acumulador paso a paso',
        'Marca, modelo y ficha de la batería actual definen la referencia compatible',
        'Datos del equipo:',
        'marca y modelo del montacargas, y voltaje y capacidad de la batería actual.',
        'Validación técnica:',
        'comparamos dimensiones, peso, conector y cargador con la referencia propuesta.',
        'Cotización:',
        'recibes la opción de compra o alquiler, con el cargador que corresponde si el actual no sirve.',
        'Baterías y equipos eléctricos en operación',
        'Soluciones de energía y montacargas en entornos de trabajo',
        'Cotiza baterías para montacargas',
        'Preguntas frecuentes sobre baterías para montacargas',
        'Compatibilidad, carga, mantenimiento y monitoreo de la batería de tracción',
        'Artículos sobre carga y electrolito del plomo-ácido',
        'Lectura de los registros del BMS y cuidado de las conexiones',
        '¿Qué batería necesita mi montacargas eléctrico?',
        'La que coincide con el equipo en voltaje, amperios hora, dimensiones, peso y tipo y posición del conector, y que rinde las horas que trabaja por turno. Si una de esas medidas no coincide, la batería no entra en el compartimiento, no conecta o no alcanza para la jornada.',
        '¿Cuánto dura una batería de tracción para montacargas?',
        'Depende de los ciclos de carga que cumple, de las horas de trabajo por turno y del mantenimiento. Un cargador que no corresponde a su voltaje o a su tecnología, un nivel de electrolito descuidado y los turnos sin tiempo para completar la carga reducen la vida útil de las celdas.',
        '¿Venden y alquilan baterías para montacargas?',
        'Sí. Puedes comprar la batería o alquilarla, según tu presupuesto y la vida útil que le quede al equipo. Tenemos baterías en inventario, y la disponibilidad de la referencia se confirma con el voltaje, la capacidad y las dimensiones. Si necesitas también el equipo, alquilamos montacargas eléctricos.',
        '¿Qué mantenimiento necesita una batería de plomo-ácido?',
        'Control del nivel de electrolito, revisión de conexiones, limpieza y ciclos de carga completos. La frecuencia se programa por horas de uso, turnos e historial de fallas más que por calendario, y cambia entre una operación de un turno y otra de varios turnos por día.',
        '¿Para qué sirve el BMS en una batería de tracción?',
        'Es un sistema de monitoreo que mide y registra el voltaje, la corriente, la temperatura, el estado de carga, las horas de operación y los ciclos de la batería. Con esos datos se identifican descargas profundas, cargas incompletas y pérdidas de autonomía, y se decide el mantenimiento con información de la operación real.',
        '¿El cargador actual sirve para una batería nueva?',
        'Solo si corresponde al voltaje nominal y a la tecnología de la batería nueva, y si su corriente de carga está calculada para esos amperios hora. Un cargador de otra tecnología o de menor corriente no completa la carga entre turnos. Si el actual no sirve, la cotización incluye el cargador que corresponde.',
    ];
    $theme_inc = (string) file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-commercial-landing-pages.php');
    foreach ($required_copy as $copy) {
        $copy_source = 'Cotiza baterías para montacargas' === $copy ? $theme_inc : $page_content;
        tmd_battery_test_assert(false !== strpos($copy_source, $copy), 'falta el texto aprobado de DEC-11: ' . $copy);
    }

    $asset_directory = dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/img/commercial-landings/baterias-referencias';
    $catalog_assets = [
        '24V-375/7.png' => ['banner-bateria-barbillon.webp', 1672, 941],
        '48V-770/5.png' => ['diferencial-celdas-barbillon.webp', 1448, 1086],
        '36V-930/6.png' => ['ventajas-bateria-barbillon.webp', 1448, 1086],
        '48V-620/4.png' => ['proceso-cambio-bateria-barbillon.webp', 1448, 1086],
        '48V-920/6.png' => ['galeria-bateria-celdas-barbillon.webp', 1448, 1086],
        '80V-620/5.png' => ['galeria-bateria-traccion-barbillon.webp', 1448, 1086],
        '80V-620/6.png' => ['formulario-bateria-barbillon.webp', 1448, 1086],
    ];
    foreach ($catalog_assets as $source_relative => [$destination_name, $expected_width, $expected_height]) {
        $source_path = dirname(__DIR__) . '/BATERIAS SEGÚN REFERENCIAS/' . $source_relative;
        $destination_path = $asset_directory . '/' . $destination_name;
        tmd_battery_test_assert(is_file($destination_path), 'debe existir la copia del catálogo: ' . $destination_name);
        $destination_image = getimagesize($destination_path);
        tmd_battery_test_assert(
            is_array($destination_image)
                && IMAGETYPE_WEBP === $destination_image[2]
                && $expected_width === $destination_image[0]
                && $expected_height === $destination_image[1]
                && filesize($destination_path) < 500000,
            'la copia debe ser WebP optimizada y conservar dimensiones: ' . $destination_name
        );
        if (is_file($source_path)) {
            $source_image = getimagesize($source_path);
            tmd_battery_test_assert(
                is_array($source_image)
                    && IMAGETYPE_PNG === $source_image[2]
                    && $source_image[0] === $destination_image[0]
                    && $source_image[1] === $destination_image[1]
                    && filesize($destination_path) < filesize($source_path),
                'la copia WebP local debe conservar dimensiones y reducir bytes frente a la fuente: ' . $source_relative
            );
        }
    }
    tmd_battery_test_assert(false !== strpos($page_content, 'commercial-landings/baterias-referencias/banner-bateria-barbillon.webp'), 'el hero debe usar una copia local del catálogo de baterías');
    tmd_battery_test_assert(false !== strpos($page_content, 'commercial-landings/baterias-referencias/galeria-bateria-traccion-barbillon.webp'), 'la galería debe usar copias locales del catálogo de baterías');
    tmd_battery_test_assert(false !== strpos($theme_inc, 'commercial-landings/baterias-referencias/formulario-bateria-barbillon.webp'), 'la sección de cotización debe usar su copia local del catálogo de baterías');

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
    tmd_battery_test_assert(10 === has_action('wpcf7_save_contact_form', 'wpcf7_sendinblue_save_contact_form'), 'la integración Sendinblue debe volver a quedar conectada después del guardado');
    tmd_battery_test_assert(false === array_key_exists('sendinblue', $GLOBALS['tmd_battery_form_properties']), 'la actualización WP-CLI no debe agregar valores Sendinblue por defecto que faltaban en el formulario original');
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
