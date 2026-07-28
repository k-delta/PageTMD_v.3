<?php
/**
 * Assets for the corporate "Quiénes somos" page.
 */

defined( 'ABSPATH' ) || exit;

function tmd_about_enqueue_assets() {
	if ( ! is_page( 278 ) ) {
		return;
	}

	$relative_path = '/assets/css/tmd-about.css';
	$absolute_path = get_stylesheet_directory() . $relative_path;
	$version       = file_exists( $absolute_path ) ? (string) filemtime( $absolute_path ) : null;

	wp_enqueue_style(
		'tmd-about',
		get_stylesheet_directory_uri() . $relative_path,
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'tmd_about_enqueue_assets', 30 );

