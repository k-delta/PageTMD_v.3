<?php
if (!defined('WP_CLI')) {
    exit;
}

$page_id = 47;
$content_file = '/tmp/tmd-home-phase1.html';

if (!file_exists($content_file)) {
    WP_CLI::error('Missing home content file: ' . $content_file);
}

$new_home = file_get_contents($content_file);
$new_block_match = [];

if (!preg_match('#<section class="tmd-section tmd-success-section">.*?</section>#s', $new_home, $new_block_match)) {
    WP_CLI::error('New success carousel block not found.');
}

$post = get_post($page_id);

if (!$post) {
    WP_CLI::error('Home page not found.');
}

$pattern = '#<section class="tmd-section(?:\s+tmd-success-section)?">(?:(?!<section class="tmd-section tmd-muted">).)*?Historias de (?:exito|&Eacute;xito).*?</section>\s*(?=<section class="tmd-section tmd-muted">)#s';

$updated = preg_replace($pattern, $new_block_match[0] . "\n", $post->post_content, 1, $count);

if ($count !== 1) {
    WP_CLI::error('Could not replace success stories block. Matches: ' . $count);
}

wp_update_post([
    'ID' => $page_id,
    'post_content' => $updated,
]);

WP_CLI::success('Success carousel deployed.');
