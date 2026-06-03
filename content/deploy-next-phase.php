<?php
if (!defined('WP_CLI')) {
    exit;
}

$pages = [
    47 => [
        'file' => '/tmp/tmd-home-phase1.html',
        'title' => 'Inicio',
        'slug' => 'inicio',
        'parent' => 0,
        'seo_title' => 'Tecni Montacargas - Venta, Alquiler y Servicio Tecnico',
        'seo_description' => 'Mas de 20 anos especializados en montacargas, plataformas elevadoras, baterias, repuestos y servicio tecnico para empresas en Colombia.',
    ],
    57 => [
        'file' => '/tmp/tmd-contact-page.html',
        'title' => 'Contacto',
        'slug' => 'contacto',
        'parent' => 55,
        'seo_title' => 'Contacto | Tecni Montacargas Dual',
        'seo_description' => 'Contacta asesores de Tecni Montacargas Dual para venta, alquiler, repuestos, baterias y servicio tecnico en Colombia.',
    ],
    284 => [
        'file' => '/tmp/tmd-pqr-page.html',
        'title' => 'PQR',
        'slug' => 'pqr',
        'parent' => 357,
        'seo_title' => 'PQR | Peticiones Quejas y Reclamos | Tecni Montacargas',
        'seo_description' => 'Radica peticiones, quejas, reclamos o solicitudes de reembolso ante Tecni Montacargas Dual.',
    ],
    53 => [
        'file' => '/tmp/tmd-quiz-page.html',
        'title' => 'Encuentra tu equipo',
        'slug' => 'encuentra-tu-equipo',
        'parent' => 0,
        'seo_title' => 'Encuentra tu equipo ideal | Tecni Montacargas Dual',
        'seo_description' => 'Responde el quiz de Tecni Montacargas Dual y recibe una recomendacion para compra, alquiler, servicio o repuestos.',
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

WP_CLI::success('Next phase pages deployed.');
