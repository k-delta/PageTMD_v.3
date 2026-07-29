<?php
/**
 * Corporate presentation for the native WooCommerce My Account area.
 *
 * Authentication, registration, password recovery and account security remain
 * entirely managed by WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load account styles only where WooCommerce renders the customer account.
 */
function tmd_account_enqueue_assets() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$relative_path = '/assets/css/tmd-account.css';
	$absolute_path = get_stylesheet_directory() . $relative_path;
	$version       = file_exists( $absolute_path ) ? (string) filemtime( $absolute_path ) : null;

	wp_enqueue_style(
		'tmd-account',
		get_stylesheet_directory_uri() . $relative_path,
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'tmd_account_enqueue_assets', 30 );

/**
 * Introduce the native login and registration forms without replacing them.
 */
function tmd_account_login_intro() {
	if ( is_user_logged_in() ) {
		return;
	}
	?>
	<section class="tmd-account-intro" aria-labelledby="tmd-account-title">
		<p class="tmd-account-intro__eyebrow"><?php esc_html_e( 'Portal de clientes', 'blocksy-child' ); ?></p>
		<h1 id="tmd-account-title"><?php esc_html_e( 'Tu operación, siempre a la mano', 'blocksy-child' ); ?></h1>
		<p><?php esc_html_e( 'Ingresa o crea tu cuenta para actualizar tus datos y gestionar tu información con Tecnimontacargas.', 'blocksy-child' ); ?></p>
		<ul class="tmd-account-benefits" aria-label="<?php esc_attr_e( 'Beneficios de tu cuenta', 'blocksy-child' ); ?>">
			<li><?php esc_html_e( 'Datos de contacto actualizados', 'blocksy-child' ); ?></li>
			<li><?php esc_html_e( 'Acceso seguro a tu perfil', 'blocksy-child' ); ?></li>
			<li><?php esc_html_e( 'Acceso protegido por WooCommerce', 'blocksy-child' ); ?></li>
		</ul>
	</section>
	<?php
}
add_action( 'woocommerce_before_customer_login_form', 'tmd_account_login_intro', 8 );

/**
 * Give authenticated customers a clear account state and safe logout action.
 */
function tmd_account_member_header() {
	if ( ! is_user_logged_in() || ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$user        = wp_get_current_user();
	$member_name = $user->display_name ? $user->display_name : $user->user_login;
	$logout_url  = function_exists( 'wc_logout_url' ) ? wc_logout_url() : wp_logout_url( home_url( '/' ) );
	?>
	<section class="tmd-account-member" aria-label="<?php esc_attr_e( 'Estado de la cuenta', 'blocksy-child' ); ?>">
		<span class="tmd-account-member__icon" aria-hidden="true">✓</span>
		<div>
			<p><?php esc_html_e( 'Sesión activa', 'blocksy-child' ); ?></p>
			<h2><?php echo esc_html( sprintf( __( 'Hola, %s', 'blocksy-child' ), $member_name ) ); ?></h2>
			<span><?php esc_html_e( 'Administra tus datos y seguridad desde este panel.', 'blocksy-child' ); ?></span>
		</div>
		<a href="<?php echo esc_url( $logout_url ); ?>"><?php esc_html_e( 'Cerrar sesión', 'blocksy-child' ); ?></a>
	</section>
	<?php
}
add_action( 'woocommerce_account_dashboard', 'tmd_account_member_header', 5 );
