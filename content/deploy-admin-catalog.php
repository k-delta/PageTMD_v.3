<?php
if (!defined('ABSPATH')) {
    exit;
}

$pages = [
    49 => __DIR__ . '/equipos-page.html',
    63 => __DIR__ . '/energia-page.html',
];

foreach ($pages as $page_id => $file) {
    if (file_exists($file)) {
        wp_update_post([
            'ID' => $page_id,
            'post_content' => file_get_contents($file),
        ]);
    }
}

function tmd_seed_post(string $post_type, string $title, string $content, array $meta): int
{
    $existing = get_page_by_path(sanitize_title($title), OBJECT, $post_type);
    if ($existing instanceof WP_Post) {
        $post_id = (int) $existing->ID;
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
        ]);
    } else {
        $post_id = wp_insert_post([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $content,
        ]);
    }

    foreach ($meta as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }

    return (int) $post_id;
}

$equipment_content = '<p>Equipo disponible para validacion tecnica, compra o alquiler segun disponibilidad. Nuestro equipo confirma autonomia, capacidad residual, estado de llantas, mastil, horquillas y condiciones de entrega antes de cotizar.</p>';

tmd_seed_post('tmd_equipo', 'Toyota 8FGU25', $equipment_content, [
    'tmd_tipo' => 'contrabalanceada',
    'tmd_marca' => 'Toyota',
    'tmd_modelo' => '8FGU25',
    'tmd_condicion' => 'usado',
    'tmd_energia' => 'glp',
    'tmd_capacidad' => '2.5 ton',
    'tmd_altura' => '4.7 m',
    'tmd_modalidad' => 'venta',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_destacado' => '1',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/ChatGPT-Image-25-may-2026-10_32_47-1.png',
]);

tmd_seed_post('tmd_equipo', 'Yale Reach Truck', $equipment_content, [
    'tmd_tipo' => 'retractil',
    'tmd_marca' => 'Yale',
    'tmd_modelo' => 'Reach Truck',
    'tmd_condicion' => 'nuevo',
    'tmd_energia' => 'electrico',
    'tmd_capacidad' => '1.8 ton',
    'tmd_altura' => '8 m',
    'tmd_modalidad' => 'alquiler',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_destacado' => '1',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/ChatGPT-Image-25-may-2026-11_10_17-1.png',
]);

tmd_seed_post('tmd_equipo', 'Apilador electrico', $equipment_content, [
    'tmd_tipo' => 'apilador',
    'tmd_marca' => 'Multimarca',
    'tmd_modelo' => 'Compacto',
    'tmd_condicion' => 'usado',
    'tmd_energia' => 'electrico',
    'tmd_capacidad' => '1.5 ton',
    'tmd_altura' => '3.5 m',
    'tmd_modalidad' => 'venta',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_destacado' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/Gemini_Generated_Image_86nrce86nrce86nr-1.png',
]);

tmd_seed_post('tmd_equipo', 'Transpaleta manual', $equipment_content, [
    'tmd_tipo' => 'transpaleta',
    'tmd_marca' => 'Multimarca',
    'tmd_modelo' => 'Bajo perfil',
    'tmd_condicion' => 'nuevo',
    'tmd_energia' => 'manual',
    'tmd_capacidad' => '2.5 ton',
    'tmd_altura' => 'Bajo perfil',
    'tmd_modalidad' => 'venta',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_destacado' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/Gemini_Generated_Image_dz2kifdz2kifdz2k-1.png',
]);

tmd_seed_post('tmd_equipo', 'Manlift articulado', $equipment_content, [
    'tmd_tipo' => 'manlift',
    'tmd_marca' => 'JLG / Genie',
    'tmd_modelo' => 'Articulado',
    'tmd_condicion' => 'reacondicionado',
    'tmd_energia' => 'diesel',
    'tmd_capacidad' => 'Altura 16 m',
    'tmd_altura' => '16 m',
    'tmd_modalidad' => 'alquiler',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_destacado' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/Gemini_Generated_Image_dz2kifdz2kifdz2k-1.png',
]);

tmd_seed_post('tmd_equipo', 'Crown electrico', $equipment_content, [
    'tmd_tipo' => 'contrabalanceada',
    'tmd_marca' => 'Crown',
    'tmd_modelo' => 'Electrico',
    'tmd_condicion' => 'usado',
    'tmd_energia' => 'electrico',
    'tmd_capacidad' => '2.0 ton',
    'tmd_altura' => '5 m',
    'tmd_modalidad' => 'alquiler',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_destacado' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/ChatGPT-Image-25-may-2026-11_10_17-1.png',
]);

$energy_content = '<p>Producto de energia industrial para equipos electricos. Validamos compatibilidad, voltaje, conector, ciclo de uso, autonomia esperada y condiciones de instalacion antes de cotizar.</p>';

tmd_seed_post('tmd_energia', 'Bateria de litio Li-Ion', $energy_content, [
    'tmd_categoria' => 'bateria',
    'tmd_tecnologia' => 'litio',
    'tmd_marca' => 'Multimarca',
    'tmd_voltaje' => '48V',
    'tmd_amperaje' => 'Segun equipo',
    'tmd_capacidad_ah' => 'Alta eficiencia',
    'tmd_condicion' => 'nuevo',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/Dispositivo-Baterias.png',
]);

tmd_seed_post('tmd_energia', 'Bateria plomo-acido tubular', $energy_content, [
    'tmd_categoria' => 'bateria',
    'tmd_tecnologia' => 'plomo',
    'tmd_marca' => 'Multimarca',
    'tmd_voltaje' => '24V / 36V / 48V',
    'tmd_amperaje' => 'Segun banco',
    'tmd_capacidad_ah' => 'Reacondicionable',
    'tmd_condicion' => 'reacondicionado',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/Gemini_Generated_Image_86nrce86nrce86nr-1.png',
]);

tmd_seed_post('tmd_energia', 'Cargador industrial Renma', $energy_content, [
    'tmd_categoria' => 'cargador',
    'tmd_tecnologia' => 'cargador',
    'tmd_marca' => 'Renma',
    'tmd_voltaje' => '24V-80V',
    'tmd_amperaje' => 'Segun bateria',
    'tmd_capacidad_ah' => 'Multimarca',
    'tmd_condicion' => 'nuevo',
    'tmd_precio' => '',
    'tmd_mostrar_precio' => '0',
    'tmd_imagen_url' => 'https://tecnimontacargasdual.com/wp-content/uploads/2026/05/11111.png',
]);

flush_rewrite_rules();

if (function_exists('rocket_clean_domain')) {
    rocket_clean_domain();
}

echo "Catalogo administrable TMD actualizado.\n";
