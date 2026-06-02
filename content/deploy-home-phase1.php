<?php
if (!defined('WP_CLI')) {
    exit;
}

$content_file = '/tmp/tmd-home-phase1.html';

if (!file_exists($content_file)) {
    WP_CLI::error('Missing home content file: ' . $content_file);
}

$content = file_get_contents($content_file);
$page_id = 47;

wp_update_post([
    'ID' => $page_id,
    'post_title' => 'Inicio',
    'post_name' => 'inicio',
    'post_content' => $content,
    'post_status' => 'publish',
]);

update_option('show_on_front', 'page');
update_option('page_on_front', $page_id);

update_post_meta($page_id, 'rank_math_title', 'Tecni Montacargas - Venta, Alquiler y Servicio Tecnico');
update_post_meta($page_id, 'rank_math_description', 'Mas de 20 anos especializados en montacargas, plataformas elevadoras, baterias, repuestos y servicio tecnico para empresas en Colombia.');
update_post_meta($page_id, 'rank_math_focus_keyword', 'montacargas colombia, alquiler montacargas, venta montacargas');

WP_CLI::success('Home phase 1 deployed.');
