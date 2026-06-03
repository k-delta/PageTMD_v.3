<?php
if (!defined('WP_CLI')) {
    exit;
}

$pages = [
    49 => [
        'file' => '/tmp/tmd-equipos-page.html',
        'title' => 'Equipos',
        'slug' => 'equipos',
        'parent' => 0,
        'seo_title' => 'Equipos | Venta y Alquiler de Montacargas | TMD',
        'seo_description' => 'Catalogo de montacargas, apiladores, transpaletas, retractiles y manlift para venta o alquiler en Colombia.',
    ],
    63 => [
        'file' => '/tmp/tmd-energia-page.html',
        'title' => 'Energia',
        'slug' => 'energia',
        'parent' => 0,
        'seo_title' => 'Energia | Baterias y Cargadores para Montacargas',
        'seo_description' => 'Baterias de litio, plomo y cargadores industriales para montacargas electricos. Cotiza con Tecni Montacargas Dual.',
    ],
    51 => [
        'file' => '/tmp/tmd-repuestos-page.html',
        'title' => 'Repuestos',
        'slug' => 'repuestos',
        'parent' => 0,
        'seo_title' => 'Repuestos para Montacargas | Tecni Montacargas Dual',
        'seo_description' => 'Repuestos electricos, hidraulicos y mecanicos para montacargas. Consulta disponibilidad o compra en tienda.',
    ],
];

foreach ($pages as $page_id => $data) {
    if (!file_exists($data['file'])) {
        WP_CLI::error('Missing file: ' . $data['file']);
    }

    wp_update_post([
        'ID' => $page_id,
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_parent' => $data['parent'],
        'post_content' => file_get_contents($data['file']),
        'post_status' => 'publish',
    ]);

    update_post_meta($page_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($page_id, 'rank_math_description', $data['seo_description']);
}

WP_CLI::success('Catalog phase deployed.');
