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

$mapped_slugs = [];
foreach ($guides as $slug => $guide) {
    if (! empty($guide['hero_image'])) {
        $mapped_slugs[] = $slug;
    }
}
$expected_mapped_slugs = array_keys($expected_images);
sort($mapped_slugs);
sort($expected_mapped_slugs);
tmd_equipment_type_guide_test_assert(
    $mapped_slugs === $expected_mapped_slugs,
    'Solo las ocho guías aprobadas deben tener hero_image.'
);

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
    $guide = $guides[$slug];
    $expected_image_pattern = '/<img class="tmd-type-guide__image" src="'
        . preg_quote($expected_url, '/')
        . '"[^>]*>/';

    tmd_equipment_type_guide_test_assert(
        1 === preg_match($expected_image_pattern, $html),
        "{$slug} debe renderizar el asset esperado en el mismo elemento del hero."
    );
    tmd_equipment_type_guide_test_assert(
        false !== strpos($html, '<h1>' . $guide['title'] . '</h1>')
            && false !== strpos($html, '<p>' . $guide['summary'] . '</p>'),
        "{$slug} debe conservar título y resumen."
    );
    tmd_equipment_type_guide_test_assert(
        false !== strpos($html, 'href="https://example.test/equipos/"')
            && false !== strpos($html, 'Ver equipos disponibles')
            && false !== strpos($html, 'Hablar con un asesor'),
        "{$slug} debe conservar los botones y sus enlaces principales."
    );
    tmd_equipment_type_guide_test_assert(
        false !== strpos($html, 'href="https://example.test/encuentra-tu-equipo/"')
            && false !== strpos($html, 'href="https://example.test/nosotros/contacto/"'),
        "{$slug} debe conservar los enlaces del recomendador y contacto."
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
        0 === preg_match('/<img\b/', $html),
        "{$slug} no debe renderizar ninguna imagen dedicada."
    );
}

$css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-equipment-type-guides.css');
tmd_equipment_type_guide_test_assert(
    1 === preg_match('/\.tmd-type-guide__image\s*\{([^}]*)\}/', $css, $image_rules),
    'Debe existir un bloque CSS específico para las imágenes del hero.'
);
tmd_equipment_type_guide_test_assert(
    false !== strpos($image_rules[1], 'height: 82%;')
        && false !== strpos($image_rules[1], 'max-width: 86%;')
        && false !== strpos($image_rules[1], 'object-fit: contain;')
        && false !== strpos($image_rules[1], 'width: 86%;'),
    'El bloque de imágenes debe conservar dimensiones y object-fit aprobados.'
);
tmd_equipment_type_guide_test_assert(
    1 === preg_match('/\.tmd-type-guide__visual\s*\{([^}]*)\}/', $css, $visual_rules),
    'Debe existir un bloque CSS específico para el panel visual.'
);
tmd_equipment_type_guide_test_assert(
    false !== strpos($visual_rules[1], 'overflow: hidden;'),
    'El panel visual debe conservar el overflow controlado para evitar desbordamiento horizontal.'
);

fwrite(STDOUT, "OK: ocho mappings, ocho heroes renderizados, fallback de tres guías y reglas visuales.\n");
