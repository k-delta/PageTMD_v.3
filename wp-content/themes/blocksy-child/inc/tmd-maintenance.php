<?php
/**
 * Assets for the maintenance service pages.
 */

defined( 'ABSPATH' ) || exit;

function tmd_maintenance_enqueue_assets() {
	if ( ! is_page( array( 506, 288, 290 ) ) ) {
		return;
	}

	$relative_path = '/assets/css/tmd-maintenance.css';
	$absolute_path = get_stylesheet_directory() . $relative_path;
	$version       = file_exists( $absolute_path ) ? (string) filemtime( $absolute_path ) : null;

	wp_enqueue_style(
		'tmd-maintenance',
		get_stylesheet_directory_uri() . $relative_path,
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'tmd_maintenance_enqueue_assets', 30 );
