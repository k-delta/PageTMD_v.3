<?php

define('ABSPATH', dirname(__DIR__) . '/');

$tmd_equipment_type_guide_test_filters = [];

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $tmd_equipment_type_guide_test_filters;
    $tmd_equipment_type_guide_test_filters[$hook][$priority][] = compact('callback', 'accepted_args');
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    add_filter($hook, $callback, $priority, $accepted_args);
}

class WP_Post {
    public $post_name;
}

$tmd_equipment_type_guide_test_slug = '';

function is_page() {
    return true;
}

function get_queried_object() {
    global $tmd_equipment_type_guide_test_slug;
    $post = new WP_Post();
    $post->post_name = $tmd_equipment_type_guide_test_slug;
    return $post;
}

function in_the_loop() {
    return true;
}

function is_main_query() {
    return true;
}

function home_url($path = '') {
    return 'https://example.test' . $path;
}

function get_stylesheet_directory_uri() {
    return 'https://example.test/wp-content/themes/blocksy-child';
}

function esc_url($value) {
    return $value;
}

function esc_html($value) {
    return $value;
}

require_once dirname(__DIR__) . '/wp-content/themes/blocksy-child/inc/tmd-equipment-type-guides.php';

function tmd_equipment_type_guide_test_assert($condition, $message) {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$guides = tmd_equipment_type_guides();
$expected_images = [
    'estibadores-manuales' => 'assets/img/mega-menu/mega-menu-out/estibador-manual.webp',
    'estibadores-electricos' => 'assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp',
    'apiladores-electricos' => 'assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp',
    'retractiles-de-mastil-movil' => 'assets/img/mega-menu/mega-menu-out/Montacargas-retráctiles-de-mástil-móvil.webp',
    'pantografo-doble-profundidad' => 'assets/img/mega-menu/mega-menu-out/pantografo-doble-reach.webp',
    'electricos-de-4-ruedas' => 'assets/img/mega-menu/mega-menu-out/contrabalanceado-4-llantas.webp',
    'electricos-de-3-ruedas' => 'assets/img/mega-menu/contrabalanceado-3-llantas.webp',
    'tomapedidos-de-alto-nivel' => 'assets/img/mega-menu/mega-menu-out/toma-pedidos.webp',
];

foreach ($expected_images as $slug => $relative_path) {
    tmd_equipment_type_guide_test_assert(isset($guides[$slug]), "Debe existir la guía {$slug}.");
    tmd_equipment_type_guide_test_assert(
        ($guides[$slug]['hero_image'] ?? null) === $relative_path,
        "{$slug} debe mapear a {$relative_path}."
    );
    tmd_equipment_type_guide_test_assert(
        is_file(dirname(__DIR__) . '/wp-content/themes/blocksy-child/' . $relative_path),
        "El asset de {$slug} debe existir en el child theme."
    );
}

foreach (['pantografo-sencillo', 'tomapedidos', 'contrabalanceados'] as $slug) {
    tmd_equipment_type_guide_test_assert(
        empty($guides[$slug]['hero_image']),
        "{$slug} debe conservar el fallback CSS sin hero_image."
    );
}

foreach ($expected_images as $slug => $relative_path) {
    $tmd_equipment_type_guide_test_slug = $slug;
    $html = tmd_equipment_type_guide_content('contenido original');
    $expected_url = get_stylesheet_directory_uri() . '/' . $relative_path;

    tmd_equipment_type_guide_test_assert(
        1 === substr_count($html, 'class="tmd-type-guide__image"'),
        "{$slug} debe renderizar exactamente una imagen del hero."
    );
    tmd_equipment_type_guide_test_assert(
        false !== strpos($html, 'src="' . $expected_url . '"'),
        "{$slug} debe renderizar el asset esperado."
    );
    tmd_equipment_type_guide_test_assert(
        false === strpos($html, 'tmd-type-guide__machine'),
        "{$slug} no debe renderizar la ilustración CSS temporal."
    );
}

foreach (['pantografo-sencillo', 'tomapedidos', 'contrabalanceados'] as $slug) {
    $tmd_equipment_type_guide_test_slug = $slug;
    $html = tmd_equipment_type_guide_content('contenido original');

    tmd_equipment_type_guide_test_assert(
        false !== strpos($html, 'tmd-type-guide__machine'),
        "{$slug} debe conservar la ilustración CSS temporal."
    );
    tmd_equipment_type_guide_test_assert(
        false === strpos($html, 'tmd-type-guide__image'),
        "{$slug} no debe renderizar una imagen dedicada."
    );
}

$css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css');
tmd_equipment_type_guide_test_assert(
    false !== strpos($css, 'object-fit: contain;'),
    'El hero debe conservar object-fit contain para evitar distorsión o recorte.'
);
tmd_equipment_type_guide_test_assert(
    false !== strpos($css, 'overflow: hidden;'),
    'La guía debe conservar el overflow controlado para evitar desbordamiento horizontal.'
);

fwrite(STDOUT, "OK: ocho mappings, ocho heroes renderizados, fallback de tres guías y reglas visuales.\n");
