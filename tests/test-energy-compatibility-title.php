<?php

define('ABSPATH', dirname(__DIR__) . '/');

class WP_HTML_Tag_Processor {
    private $html;
    private $cursor = 0;
    private $current = null;
    private $bookmarks = [];

    public function __construct($html) {
        $this->html = $html;
    }

    public function next_tag($tag_name) {
        if ('H2' !== strtoupper($tag_name)) {
            return false;
        }

        if (! preg_match('/<h2\b[^>]*>/i', $this->html, $match, PREG_OFFSET_CAPTURE, $this->cursor)) {
            return false;
        }

        $this->current = [
            'type'   => 'tag',
            'offset' => $match[0][1],
            'length' => strlen($match[0][0]),
            'html'   => $match[0][0],
        ];
        $this->cursor = $this->current['offset'] + $this->current['length'];
        return true;
    }

    public function set_bookmark($name) {
        $this->bookmarks[$name] = $this->current;
        return true;
    }

    public function next_token() {
        $next_tag = strpos($this->html, '<', $this->cursor);
        if (false === $next_tag) {
            return false;
        }

        $this->current = [
            'type' => '#text',
            'text' => substr($this->html, $this->cursor, $next_tag - $this->cursor),
        ];
        $this->cursor = $next_tag;
        return true;
    }

    public function get_token_name() {
        return $this->current['type'] ?? null;
    }

    public function get_modifiable_text() {
        return $this->current['text'] ?? '';
    }

    public function seek($name) {
        if (! isset($this->bookmarks[$name])) {
            return false;
        }

        $this->current = $this->bookmarks[$name];
        $this->cursor = $this->current['offset'] + $this->current['length'];
        return true;
    }

    public function add_class($class_name) {
        $opening_tag = $this->current['html'];

        if (preg_match('/\sclass="([^"]*)"/', $opening_tag, $class_match)) {
            $classes = preg_split('/\s+/', trim($class_match[1])) ?: [];
            if (in_array($class_name, $classes, true)) {
                return true;
            }
            $updated_tag = preg_replace('/(\sclass=")([^"]*)"/', '$1$2 ' . $class_name . '"', $opening_tag, 1);
        } elseif (preg_match("/\sclass='([^']*)'/", $opening_tag, $class_match)) {
            $classes = preg_split('/\s+/', trim($class_match[1])) ?: [];
            if (in_array($class_name, $classes, true)) {
                return true;
            }
            $updated_tag = preg_replace("/(\sclass=')([^']*)'/", "$1$2 {$class_name}'", $opening_tag, 1);
        } else {
            $updated_tag = substr($opening_tag, 0, -1) . ' class="' . $class_name . '">';
        }

        if ($updated_tag === $opening_tag || ! is_string($updated_tag)) {
            return false;
        }

        $this->html = substr_replace(
            $this->html,
            $updated_tag,
            $this->current['offset'],
            $this->current['length']
        );
        $this->current['html'] = $updated_tag;
        $this->current['length'] = strlen($updated_tag);
        return true;
    }

    public function release_bookmark($name) {
        unset($this->bookmarks[$name]);
        return true;
    }

    public function get_updated_html() {
        return $this->html;
    }
}

$tmd_test_is_admin = false;
$tmd_test_page_id = 255;

function add_action() {}
function add_filter() {}
function is_admin() {
    global $tmd_test_is_admin;
    return $tmd_test_is_admin;
}
function is_page($page_id) {
    global $tmd_test_page_id;
    return $page_id === $tmd_test_page_id;
}

function tmd_energy_compatibility_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-energy-structure.php';

$plain_heading = '<h2>Compatibilidad antes que velocidad</h2>';
$marked_plain_heading = tmd_mark_energy_compatibility_title($plain_heading);
tmd_energy_compatibility_assert(
    '<h2 class="tmd-energy-compatibility-title">Compatibilidad antes que velocidad</h2>' === $marked_plain_heading,
    'El encabezado sin atributos debe recibir la clase focalizada.'
);

$wordpress_heading = '<h2 class="wp-block-heading" data-section="compatibility">Compatibilidad antes que velocidad</h2>';
$marked_wordpress_heading = tmd_mark_energy_compatibility_title($wordpress_heading);
tmd_energy_compatibility_assert(
    false !== strpos($marked_wordpress_heading, 'class="wp-block-heading tmd-energy-compatibility-title"'),
    'El encabezado de WordPress debe conservar su clase y recibir la clase focalizada.'
);
tmd_energy_compatibility_assert(
    false !== strpos($marked_wordpress_heading, 'data-section="compatibility"'),
    'Los demás atributos del encabezado deben conservarse.'
);
tmd_energy_compatibility_assert(
    $marked_wordpress_heading === tmd_mark_energy_compatibility_title($marked_wordpress_heading),
    'El filtro debe ser idempotente.'
);

$single_quoted_heading = "<h2 class='wp-block-heading'>Compatibilidad antes que velocidad</h2>";
tmd_energy_compatibility_assert(
    false !== strpos(tmd_mark_energy_compatibility_title($single_quoted_heading), "class='wp-block-heading tmd-energy-compatibility-title'"),
    'El encabezado debe conservar atributos entre comillas simples.'
);

$data_class_heading = '<h2 data-class="compatibility">Compatibilidad antes que velocidad</h2>';
$marked_data_class_heading = tmd_mark_energy_compatibility_title($data_class_heading);
tmd_energy_compatibility_assert(
    '<h2 data-class="compatibility" class="tmd-energy-compatibility-title">Compatibilidad antes que velocidad</h2>' === $marked_data_class_heading,
    'data-class debe conservarse y no debe confundirse con el atributo class.'
);

$class_in_attribute_value = '<h2 data-note="class=\'layout-hook\'" class="wp-block-heading">Compatibilidad antes que velocidad</h2>';
$marked_class_in_attribute_value = tmd_mark_energy_compatibility_title($class_in_attribute_value);
tmd_energy_compatibility_assert(
    false !== strpos($marked_class_in_attribute_value, 'data-note="class=\'layout-hook\'"'),
    'Los valores de otros atributos que contienen class deben conservarse.'
);
tmd_energy_compatibility_assert(
    false !== strpos($marked_class_in_attribute_value, 'class="wp-block-heading tmd-energy-compatibility-title"'),
    'El parser debe modificar el atributo class real.'
);

$multiple_headings = '<h2 class="wp-block-heading">Otra sección</h2>' . $wordpress_heading;
$marked_multiple_headings = tmd_mark_energy_compatibility_title($multiple_headings);
tmd_energy_compatibility_assert(
    false !== strpos($marked_multiple_headings, '<h2 class="wp-block-heading">Otra sección</h2>'),
    'El parser debe omitir otros h2 anteriores.'
);
tmd_energy_compatibility_assert(
    false !== strpos($marked_multiple_headings, 'class="wp-block-heading tmd-energy-compatibility-title"'),
    'El parser debe marcar el h2 que contiene el título objetivo.'
);

$other_heading = '<h2 class="wp-block-heading">Otra sección</h2>';
tmd_energy_compatibility_assert(
    $other_heading === tmd_mark_energy_compatibility_title($other_heading),
    'Otros encabezados no deben modificarse.'
);

$tmd_test_page_id = 47;
tmd_energy_compatibility_assert(
    $wordpress_heading === tmd_mark_energy_compatibility_title($wordpress_heading),
    'El filtro no debe actuar fuera de la página 255.'
);

$tmd_test_page_id = 255;
$tmd_test_is_admin = true;
tmd_energy_compatibility_assert(
    $wordpress_heading === tmd_mark_energy_compatibility_title($wordpress_heading),
    'El filtro no debe actuar en el administrador.'
);

fwrite(STDOUT, "OK: encabezado de compatibilidad marcado de forma focalizada e idempotente.\n");
