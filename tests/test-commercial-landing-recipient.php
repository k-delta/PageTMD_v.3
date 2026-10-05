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

function esc_html($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function absint($value) {
    return abs((int) $value);
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
$page_specs = tmd_commercial_landing_script_page_specs(['rental' => 1556, 'battery' => 1559]);
tmd_commercial_landing_recipient_assert(
    'Alquiler de montacargas eléctricos' === $page_specs['rental']['title']
        && 'Alquiler de montacargas eléctricos | Tecnimontacargas' === $page_specs['rental']['rank_title']
        && 'Alquiler de montacargas eléctricos para bodegas, centros de distribución y plantas. Alquiler sin operador desde 15 días, con recomendación técnica según tu operación.' === $page_specs['rental']['rank_description'],
    'La creación de la landing debe guardar título y metadatos enfocados únicamente en alquiler.'
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
$hero_heading_position = strpos($rental_content, '<h1 id="tmd-rental-v2-heading">');
$hero_start = false === $hero_heading_position
    ? false
    : strrpos(substr($rental_content, 0, $hero_heading_position), '<section class="tmd-rental-v2-section tmd-rental-v2__hero"');
$hero_end = false === $hero_heading_position ? false : strpos($rental_content, '</section>', $hero_heading_position);
$rental_hero = false === $hero_start || false === $hero_end
    ? ''
    : substr($rental_content, $hero_start, $hero_end - $hero_start);
$hero_block_start = false === $hero_heading_position
    ? false
    : strrpos(substr($rental_content, 0, $hero_heading_position), '<!-- wp:html -->');
$hero_block_end = false === $hero_heading_position
    ? false
    : strpos($rental_content, '<!-- /wp:html -->', $hero_heading_position);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, 'Alquiler de montacargas <span>eléctricos</span>')
        && false === strpos($rental_content, 'Venta o alquiler de montacargas'),
    'El hero debe mantener el H1 actualizado por la aclaración del usuario como HTML editable.'
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
    '' !== $rental_hero
        && false === strpos($rental_hero, 'Solicitar cotización')
        && false === strpos($rental_hero, 'href='),
    'El hero vigente no debe tener CTA ni enlaces.'
);
tmd_commercial_landing_recipient_assert(
    false !== strpos($rental_content, '120 equipos en flota propia')
        && false !== strpos($rental_content, 'Desde el año 2000 en servicio técnico')
        && false !== strpos($rental_content, '15 días de alquiler mínimo')
        && false !== strpos($rental_content, 'Yale, Crown, Clark, Jungheinrich y Hyster en la flota disponible')
        && false === strpos($rental_content, 'Alquiler mínimo de 1 mes'),
    'El contenido de flota y alquiler debe seguir la maqueta vigente.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($rental_content, 'tmd-commercial-landing__stats')
        && false === strpos($rental_content, 'tmd-commercial-landing__brand-note')
        && false === strpos($rental_content, 'tmd-commercial-landing__brand-note'),
    'La página no debe incluir módulos heredados de la landing anterior.'
);
tmd_commercial_landing_recipient_assert(
    false === strpos($rental_content, '<h1 id="tmd-rental-heading">'),
    'El seed no debe conservar el titular anterior del hero de alquiler.'
);

$block_markers = [
    'tmd-rental-v2__hero',
    'tmd-rental-v2__description',
    'tmd-rental-v2__needs',
    'tmd-rental-v2__buy',
    'type="equipment" variant="rental-v2"',
    'tmd-rental-v2__process',
    'tmd-rental-v2__sectors',
    'tmd-rental-v2__uses',
    'tmd-rental-v2__support',
    'tmd-rental-v2__quote',
    'tmd-rental-v2__faq',
    'tmd_commercial_landing_related_section topic="montacargas" variant="rental-v2"',
];
$previous_position = -1;
foreach ($block_markers as $marker) {
    $position = strpos($rental_content, $marker);
    tmd_commercial_landing_recipient_assert(
        false !== $position && $position > $previous_position,
        'Los doce bloques visuales de alquiler deben aparecer una sola vez y en el orden de la maqueta.'
    );
    $previous_position = $position;
}
tmd_commercial_landing_recipient_assert(
    1 === preg_match_all('/<h1\\b/i', $rental_content)
        && 6 === preg_match_all('/<details\\b/i', $rental_content),
    'La landing debe contener un H1 y las seis preguntas frecuentes aprobadas.'
);
$sector_start = strpos($rental_content, '<section class="tmd-rental-v2-section tmd-rental-v2__sectors"');
$sector_end = false === $sector_start ? false : strpos($rental_content, '</section>', $sector_start);
$sector_markup = false === $sector_end ? '' : substr($rental_content, $sector_start, $sector_end - $sector_start);
tmd_commercial_landing_recipient_assert(
    5 === preg_match_all('/<article><img\\b/', $sector_markup)
        && false !== strpos($sector_markup, '<h4>Logística</h4><p>Recepción, almacenamiento y despacho para varios clientes.</p>')
        && false !== strpos($sector_markup, '<h4>Alimentos y bebidas</h4><p>Operaciones continuas y entornos exigentes.</p>')
        && false !== strpos($sector_markup, '<h4>Manufactura</h4><p>Materia prima y producto terminado entre planta y bodega.</p>')
        && false !== strpos($sector_markup, '<h4>Retail</h4><p>Reposición y distribución desde centros propios.</p>')
        && false !== strpos($sector_markup, '<h4>Construcción</h4><p>Movimiento de materiales para obras y proyectos.</p>')
        && false !== strpos($sector_markup, 'montacargas-construccion.webp'),
    'Sectores debe conservar el layout y presentar las cinco cards con textos e imagen de construcción aprobados.'
);
$rental_v2_css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css');
tmd_commercial_landing_recipient_assert(
    is_string($rental_v2_css)
        && 1 === preg_match('/body\\.tmd-rental-layout-v2 \\.tmd-rental-v2__sectors-layout h2 \\{\\s*color:\\s*#fff;/i', $rental_v2_css)
        && 1 === preg_match('/body\\.tmd-rental-layout-v2 \\.tmd-rental-v2__sectors-layout h2 span \\{\\s*color:\\s*#ffc33c;/i', $rental_v2_css)
        && false !== strpos($rental_v2_css, 'grid-template-columns: repeat(5, minmax(0, 1fr));')
        && is_file(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/img/commercial-landings-v2/montacargas-construccion.webp'),
    'La sección de sectores debe mostrar el H2 blanco con énfasis amarillo y admitir cinco tarjetas en escritorio.'
);
tmd_commercial_landing_recipient_assert(
    1 === substr_count($rental_content, '[contact-form-7 id="1556"]')
        && 1 === substr_count($rental_content, '[tmd_commercial_landing_related_section'),
    'Formulario y blog deben permanecer como shortcodes dinámicos del sistema.'
);

$form_markup = tmd_commercial_landing_rental_v2_form_markup();
tmd_commercial_landing_recipient_assert(
    5 === preg_match_all('/\\[(?:text\\*?|textarea\\*?)\\s+(?:nombre_cargo|empresa_ciudad|contacto|necesidad|requerimientos)\\b[^\\]]*\\]/', $form_markup)
        && 4 === preg_match_all('/\\[(?:text\\*)\\s+[a-z_]+/', $form_markup)
        && false !== strpos($form_markup, '[textarea requerimientos]')
        && false !== strpos($form_markup, 'tmd_website tabindex:-1 autocomplete:off')
        && false !== strpos($form_markup, 'politica-de-privacidad')
        && false === strpos($form_markup, '[acceptance'),
    'El formulario debe mantener cinco controles, obligatoriedad existente, honeypot, privacidad y sin checkbox de aceptación.'
);
$current_form_properties = [
    'form' => '[text tmd_website tabindex:-1 autocomplete:off]',
    'mail' => [
        'recipient' => 'cotizaciones@example.test',
        'subject' => 'Asunto que debe conservarse',
        'additional_headers' => 'Reply-To: contacto@example.test',
        'body' => 'Cuerpo anterior',
    ],
    'mail_2' => ['active' => true, 'recipient' => 'copia@example.test'],
    'messages' => ['mail_sent_ok' => 'Recibimos tu solicitud.'],
    'additional_settings' => 'demo_setting: yes',
];
$target_form_properties = tmd_commercial_landing_rental_v2_target_form_properties($current_form_properties);
tmd_commercial_landing_recipient_assert(
    $form_markup === $target_form_properties['form']
        && 'cotizaciones@example.test' === $target_form_properties['mail']['recipient']
        && 'Asunto que debe conservarse' === $target_form_properties['mail']['subject']
        && 'Reply-To: contacto@example.test' === $target_form_properties['mail']['additional_headers']
        && $current_form_properties['mail_2'] === $target_form_properties['mail_2']
        && $current_form_properties['messages'] === $target_form_properties['messages']
        && $current_form_properties['additional_settings'] === $target_form_properties['additional_settings']
        && false !== strpos($target_form_properties['mail']['body'], '[nombre_cargo]')
        && false !== strpos($target_form_properties['mail']['body'], '[requerimientos]')
        && ! str_ends_with($target_form_properties['mail']['body'], "\n"),
    'El mapeo del correo debe cambiar solo el cuerpo y conservar destinatario, cabeceras y opciones del formulario.'
);

$rental_css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css');
tmd_commercial_landing_recipient_assert(
    is_string($rental_css)
        && (bool) preg_match(
            '/tmd-rental-v2__inventory-link a\\s*\\{[^}]*background:\\s*#0d70bd;[^}]*color:\\s*#fff;/is',
            $rental_css
        ),
    'El botón de inventario debe conservar la variante azul con contraste AA aprobada para la maqueta.'
);

putenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT');

echo "OK: destinatario, doce bloques, formulario CF7 y contraste del CTA.\n";
