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

	$brand_relative_path = '/assets/css/tmd-maintenance-brand-consistency.css';
	$brand_absolute_path = get_stylesheet_directory() . $brand_relative_path;
	$brand_version       = file_exists( $brand_absolute_path ) ? (string) filemtime( $brand_absolute_path ) : null;

	wp_enqueue_style(
		'tmd-maintenance-brand-consistency',
		get_stylesheet_directory_uri() . $brand_relative_path,
		array( 'tmd-maintenance' ),
		$brand_version
	);
}
add_action( 'wp_enqueue_scripts', 'tmd_maintenance_enqueue_assets', 30 );
