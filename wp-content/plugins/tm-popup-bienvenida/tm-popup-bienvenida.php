<?php
/**
 * Plugin Name: TM Popup Bienvenida WooCommerce
 * Description: Popup de bienvenida con cuenta de cliente y código único para el primer alquiler de montacargas o baterías.
 * Version: 1.2.2
 * Author: Tecni Montacargas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_Popup_Bienvenida_Woo {

    const COOKIE_NAME = 'tm_popup_seen';
    const OPTION_NAME = 'tm_popup_bienvenida_options';
    const NONCE_ACTION = 'tm_popup_bienvenida_nonce';
    const VERSION = '1.2.2';
    const VERSION_OPTION = 'tm_popup_bienvenida_version';
    const COUPON_SCOPE = 'first_rental_equipment_or_battery';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_popup' ) );

        add_action( 'wp_ajax_nopriv_tm_register_coupon', array( $this, 'ajax_register_coupon' ) );
        add_action( 'wp_ajax_nopriv_tm_login_coupon', array( $this, 'ajax_login_coupon' ) );

        add_action( 'woocommerce_created_customer', array( $this, 'create_coupon_for_new_customer' ), 20, 3 );
        add_action( 'woocommerce_account_dashboard', array( $this, 'render_account_coupon' ), 15 );
        add_action( 'woocommerce_order_status_processing', array( $this, 'mark_coupon_as_used' ) );
        add_action( 'woocommerce_order_status_completed', array( $this, 'mark_coupon_as_used' ) );

        add_action( 'wpcf7_init', array( $this, 'register_contact_form_tag' ) );
        add_filter( 'wpcf7_validate_tm_discount', array( $this, 'validate_contact_discount_code' ), 10, 2 );

        add_action( 'admin_post_tm_welcome_coupon_status', array( $this, 'handle_coupon_status' ) );
        add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
        add_action( 'init', array( $this, 'maybe_upgrade' ), 20 );
    }

    private function defaults() {
        return array(
            'enabled'         => 'yes',
            'logo_url'        => '',
            'title'           => '¡Tu primer alquiler tiene descuento!',
            'subtitle'        => 'Obtén $100.000 COP de descuento en tu primer alquiler de montacargas o baterías.',
            'coupon_amount'   => '100000',
            'register_button' => 'Registrarme y obtener descuento',
            'login_button'    => 'Iniciar sesión y usar descuento',
            'policy_url'      => home_url( '/nosotros/legal/politica-de-privacidad/' ),
        );
    }

    private function get_options() {
        return wp_parse_args(
            get_option( self::OPTION_NAME, array() ),
            $this->defaults()
        );
    }

    private function is_enabled() {
        $options = $this->get_options();
        return isset( $options['enabled'] ) && $options['enabled'] === 'yes';
    }

    private function should_show_popup() {
        if ( is_admin() ) {
            return false;
        }

        if ( ! $this->is_enabled() ) {
            return false;
        }

        if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Coupon' ) ) {
            return false;
        }

        if ( ! is_front_page() ) {
            return false;
        }

        if ( is_user_logged_in() ) {
            return false;
        }

        if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) && $_COOKIE[ self::COOKIE_NAME ] === 'true' ) {
            return false;
        }

        return true;
    }

    public function woocommerce_notice() {
        if ( current_user_can( 'manage_options' ) && ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="notice notice-error"><p><strong>TM Popup Bienvenida:</strong> WooCommerce debe estar activo para generar cupones.</p></div>';
        }
    }

    public function add_settings_page() {
    add_menu_page(
        'TM Popup Bienvenida',
        'TM Popup Bienvenida',
        'manage_options',
        'tm-popup-bienvenida',
        array( $this, 'settings_page_html' ),
        'dashicons-tickets-alt',
        56
    );
}

    public function register_settings() {
        register_setting(
            'tm_popup_bienvenida_group',
            self::OPTION_NAME,
            array( $this, 'sanitize_options' )
        );
    }

    public function sanitize_options( $input ) {
        $defaults = $this->defaults();

        return array(
            'enabled'         => isset( $input['enabled'] ) ? 'yes' : 'no',
            'logo_url'        => isset( $input['logo_url'] ) ? esc_url_raw( $input['logo_url'] ) : '',
            'title'           => isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : $defaults['title'],
            'subtitle'        => isset( $input['subtitle'] ) ? sanitize_textarea_field( $input['subtitle'] ) : $defaults['subtitle'],
            'coupon_amount'   => isset( $input['coupon_amount'] ) ? absint( $input['coupon_amount'] ) : 100000,
            'register_button' => isset( $input['register_button'] ) ? sanitize_text_field( $input['register_button'] ) : $defaults['register_button'],
            'login_button'    => isset( $input['login_button'] ) ? sanitize_text_field( $input['login_button'] ) : $defaults['login_button'],
            'policy_url'      => isset( $input['policy_url'] ) ? esc_url_raw( $input['policy_url'] ) : $defaults['policy_url'],
        );
    }

    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = $this->get_options();
        $coupons = class_exists( 'WC_Coupon' ) ? get_posts(
            array(
                'post_type'      => 'shop_coupon',
                'post_status'    => array( 'publish', 'draft' ),
                'posts_per_page' => 100,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_key'       => '_tm_welcome_coupon_user_id',
            )
        ) : array();
        ?>

        <div class="wrap">
            <h1>TM Popup Bienvenida</h1>

            <?php if ( isset( $_GET['tm_coupon_updated'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>El estado del código se actualizó correctamente.</p></div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'tm_popup_bienvenida_group' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Activar popup</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]" value="yes" <?php checked( $options['enabled'], 'yes' ); ?>>
                                Activo
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">URL del logo</th>
                        <td>
                            <input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[logo_url]" value="<?php echo esc_attr( $options['logo_url'] ); ?>">
                            <p class="description">Sube el logo a Medios y pega aquí la URL.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Título</th>
                        <td>
                            <input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[title]" value="<?php echo esc_attr( $options['title'] ); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Texto de oferta</th>
                        <td>
                            <textarea class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[subtitle]"><?php echo esc_textarea( $options['subtitle'] ); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Valor del cupón</th>
                        <td>
                            <input type="number" min="0" step="1000" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[coupon_amount]" value="<?php echo esc_attr( $options['coupon_amount'] ); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Botón registro</th>
                        <td>
                            <input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[register_button]" value="<?php echo esc_attr( $options['register_button'] ); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Botón login</th>
                        <td>
                            <input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[login_button]" value="<?php echo esc_attr( $options['login_button'] ); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">URL política de datos</th>
                        <td>
                            <input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[policy_url]" value="<?php echo esc_attr( $options['policy_url'] ); ?>">
                        </td>
                    </tr>
                </table>

                <?php submit_button( 'Guardar cambios' ); ?>
            </form>

            <hr>

            <h2>Códigos emitidos</h2>
            <p>El código se valida contra el correo del cliente. Marcarlo como usado cuando se formalice el primer alquiler de un montacargas o una batería.</p>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $coupons ) ) : ?>
                        <tr><td colspan="5">Aún no hay códigos emitidos.</td></tr>
                    <?php else : ?>
                        <?php foreach ( $coupons as $coupon_post ) : ?>
                            <?php
                            $coupon  = new WC_Coupon( $coupon_post->ID );
                            $user_id = absint( $coupon->get_meta( '_tm_welcome_coupon_user_id', true ) );
                            $user    = get_userdata( $user_id );
                            $used    = $this->is_coupon_used( $coupon, $user_id );
                            ?>
                            <tr>
                                <td><code><?php echo esc_html( strtoupper( $coupon->get_code() ) ); ?></code></td>
                                <td>
                                    <?php if ( $user ) : ?>
                                        <?php echo esc_html( $user->display_name ); ?><br>
                                        <small><?php echo esc_html( $user->user_email ); ?></small>
                                    <?php else : ?>
                                        Usuario eliminado
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $this->format_coupon_amount( $coupon->get_amount() ) ); ?></td>
                                <td>
                                    <strong style="color:<?php echo $used ? '#a50000' : '#1b6b35'; ?>">
                                        <?php echo $used ? 'Usado' : 'Disponible'; ?>
                                    </strong>
                                </td>
                                <td>
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                        <input type="hidden" name="action" value="tm_welcome_coupon_status">
                                        <input type="hidden" name="coupon_id" value="<?php echo esc_attr( $coupon->get_id() ); ?>">
                                        <input type="hidden" name="status" value="<?php echo $used ? 'available' : 'used'; ?>">
                                        <?php wp_nonce_field( 'tm_welcome_coupon_status_' . $coupon->get_id() ); ?>
                                        <button type="submit" class="button">
                                            <?php echo $used ? 'Restaurar' : 'Marcar usado'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
    }

    public function maybe_upgrade() {
        if ( get_option( self::VERSION_OPTION ) === self::VERSION || ! class_exists( 'WC_Coupon' ) ) {
            return;
        }

        $options = get_option( self::OPTION_NAME, array() );

        if (
            empty( $options['subtitle'] )
            || false !== stripos( $options['subtitle'], 'repuestos' )
            || false !== stripos( $options['subtitle'], 'primera compra' )
        ) {
            $options['title']    = '¡Tu primer alquiler tiene descuento!';
            $options['subtitle'] = 'Obtén $100.000 COP de descuento en tu primer alquiler de montacargas o baterías.';
        }

        $options['policy_url'] = home_url( '/nosotros/legal/politica-de-privacidad/' );
        update_option( self::OPTION_NAME, $options, false );

        $users = get_users(
            array(
                'fields'   => array( 'ID', 'user_email' ),
                'meta_key' => '_tm_welcome_coupon_code',
            )
        );

        foreach ( $users as $user ) {
            $code      = get_user_meta( $user->ID, '_tm_welcome_coupon_code', true );
            $coupon_id = $code ? wc_get_coupon_id_by_code( $code ) : 0;

            if ( ! $coupon_id ) {
                continue;
            }

            $coupon = new WC_Coupon( $coupon_id );
            $coupon->set_description( 'Código para el primer alquiler de montacargas o batería. Usuario #' . $user->ID );
            $coupon->set_email_restrictions( array( $user->user_email ) );
            $coupon->update_meta_data( '_tm_welcome_coupon_user_id', $user->ID );
            $coupon->update_meta_data( '_tm_welcome_coupon_scope', self::COUPON_SCOPE );

            if ( ! $coupon->get_meta( '_tm_welcome_coupon_created_at', true ) ) {
                $coupon->update_meta_data( '_tm_welcome_coupon_created_at', current_time( 'mysql' ) );
            }

            $coupon->save();
        }

        update_option( self::VERSION_OPTION, self::VERSION, false );
    }

    public function handle_coupon_status() {
        if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para modificar códigos de descuento.' );
        }

        $coupon_id = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;
        $status    = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';

        check_admin_referer( 'tm_welcome_coupon_status_' . $coupon_id );

        $coupon = $coupon_id && class_exists( 'WC_Coupon' ) ? new WC_Coupon( $coupon_id ) : null;

        if ( ! $coupon || ! $coupon->get_id() || ! $this->is_managed_coupon( $coupon ) ) {
            wp_die( 'El código indicado no pertenece al beneficio de bienvenida.' );
        }

        $user_id = absint( $coupon->get_meta( '_tm_welcome_coupon_user_id', true ) );

        if ( 'used' === $status ) {
            $coupon->update_meta_data( '_tm_welcome_coupon_redeemed', 'yes' );
            $coupon->update_meta_data( '_tm_welcome_coupon_redeemed_at', current_time( 'mysql' ) );
            update_user_meta( $user_id, '_tm_welcome_coupon_used', 'yes' );
        } elseif ( 'available' === $status ) {
            $coupon->delete_meta_data( '_tm_welcome_coupon_redeemed' );
            $coupon->delete_meta_data( '_tm_welcome_coupon_redeemed_at' );
            delete_user_meta( $user_id, '_tm_welcome_coupon_used' );
        } else {
            wp_die( 'Estado de código no válido.' );
        }

        $coupon->save();

        wp_safe_redirect(
            add_query_arg(
                'tm_coupon_updated',
                '1',
                admin_url( 'admin.php?page=tm-popup-bienvenida' )
            )
        );
        exit;
    }

    public function enqueue_assets() {
        $show_popup  = $this->should_show_popup();
        $show_account = function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in();

        if ( ! $show_popup && ! $show_account ) {
            return;
        }

        $options = $this->get_options();

        wp_enqueue_style(
            'tm-popup-bienvenida',
            plugin_dir_url( __FILE__ ) . 'assets/tm-popup.css',
            array(),
            self::VERSION
        );

        if ( ! $show_popup ) {
            return;
        }

        wp_enqueue_script(
            'tm-popup-bienvenida',
            plugin_dir_url( __FILE__ ) . 'assets/tm-popup.js',
            array(),
            self::VERSION,
            true
        );

        wp_localize_script(
            'tm-popup-bienvenida',
            'TMPopupBienvenida',
            array(
                'ajax_url'        => admin_url( 'admin-ajax.php' ),
                'nonce'           => wp_create_nonce( self::NONCE_ACTION ),
                'register_button' => $options['register_button'],
                'login_button'    => $options['login_button'],
            )
        );
    }

    public function render_popup() {
        if ( ! $this->should_show_popup() ) {
            return;
        }

        $options = $this->get_options();
        ?>

        <div id="tm-welcome-overlay" class="tm-popup-overlay" aria-hidden="true">
            <div class="tm-popup" role="dialog" aria-modal="true" aria-labelledby="tm-popup-title">
                <button type="button" class="tm-popup-close" aria-label="Cerrar popup">×</button>

                <?php if ( ! empty( $options['logo_url'] ) ) : ?>
                    <img class="tm-popup-logo" src="<?php echo esc_url( $options['logo_url'] ); ?>" alt="Tecni Montacargas">
                <?php endif; ?>

                <div class="tm-popup-icon">🎁</div>

                <h2 id="tm-popup-title"><?php echo esc_html( $options['title'] ); ?></h2>

                <p class="tm-popup-subtitle">
                    <?php echo esc_html( $options['subtitle'] ); ?>
                </p>

                <div class="tm-popup-tabs">
                    <button type="button" class="tm-tab active" data-tab="register">Crear cuenta</button>
                    <button type="button" class="tm-tab" data-tab="login">Ya tengo cuenta</button>
                </div>

                <div class="tm-popup-message" id="tm-popup-message"></div>

                <form id="tm-register-form" class="tm-popup-form active">
                    <label>
                        Nombre completo
                        <input type="text" name="full_name" autocomplete="name" required>
                    </label>

                    <label>
                        Correo electrónico
                        <input type="email" name="email" autocomplete="email" required>
                    </label>

                    <label>
                        Contraseña
                        <input type="password" name="password" autocomplete="new-password" minlength="8" required>
                    </label>

                    <label class="tm-policy">
                        <input type="checkbox" name="policy" value="1" required>
                        <span>
                            Acepto la
                            <a href="<?php echo esc_url( $options['policy_url'] ); ?>" target="_blank" rel="noopener">
                                política de datos
                            </a>
                        </span>
                    </label>

                    <button type="submit" class="tm-main-button">
                        <?php echo esc_html( $options['register_button'] ); ?>
                    </button>
                </form>

                <form id="tm-login-form" class="tm-popup-form">
                    <label>
                        Correo electrónico
                        <input type="email" name="email" autocomplete="email" required>
                    </label>

                    <label>
                        Contraseña
                        <input type="password" name="password" autocomplete="current-password" required>
                    </label>

                    <button type="submit" class="tm-main-button">
                        <?php echo esc_html( $options['login_button'] ); ?>
                    </button>

                    <a class="tm-forgot" href="<?php echo esc_url( function_exists( 'wc_lostpassword_url' ) ? wc_lostpassword_url() : wp_lostpassword_url() ); ?>">
                        ¿Olvidaste tu contraseña?
                    </a>
                </form>

                <div id="tm-success-box" class="tm-success-box">
                    <h3>Tu descuento está listo</h3>
                    <p>Presenta este código al solicitar tu primer alquiler de un montacargas o una batería:</p>
                    <strong id="tm-coupon-code"></strong>
                    <button type="button" id="tm-copy-coupon" class="tm-copy-button">Copiar código</button>
                    <p id="tm-coupon-delivery" class="tm-small">También quedó guardado en Mi cuenta.</p>
                    <a class="tm-account-link" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/mi-cuenta/' ) ); ?>">Ir a Mi cuenta</a>
                </div>
            </div>
        </div>

        <?php
    }

    public function ajax_register_coupon() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( is_user_logged_in() && function_exists( 'wc_get_coupon_id_by_code' ) && class_exists( 'WC_Coupon' ) ) {
            wp_send_json_error( array( 'message' => 'Ya tienes una sesión activa. Administra tus datos desde Mi cuenta.' ) );
        }

        if ( ! class_exists( 'WC_Coupon' ) || ! function_exists( 'wc_create_new_customer' ) ) {
            wp_send_json_error( array( 'message' => 'WooCommerce no está disponible.' ) );
        }

        $full_name = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
        $email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $password  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
        $policy    = isset( $_POST['policy'] ) ? absint( $_POST['policy'] ) : 0;

        if ( empty( $full_name ) || empty( $email ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'Completa todos los campos.' ) );
        }

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'El correo no es válido.' ) );
        }

        if ( strlen( $password ) < 8 ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe tener mínimo 8 caracteres.' ) );
        }

        if ( $policy !== 1 ) {
            wp_send_json_error( array( 'message' => 'Debes aceptar la política de datos.' ) );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Ese correo ya está registrado. Usa la pestaña “Ya tengo cuenta”.' ) );
        }

        $username = $this->generate_username_from_email( $email );
        $name     = $this->split_full_name( $full_name );

        $user_id = wc_create_new_customer(
            $email,
            $username,
            $password,
            array(
                'display_name' => $full_name,
                'first_name'   => $name['first_name'],
                'last_name'    => $name['last_name'],
                'source'       => 'tm-popup-bienvenida',
            )
        );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        update_user_meta( $user_id, 'billing_first_name', $name['first_name'] );
        update_user_meta( $user_id, 'billing_last_name', $name['last_name'] );
        update_user_meta( $user_id, 'billing_email', $email );

        $coupon_code = $this->create_or_get_coupon_for_user( $user_id, $email );

        if ( function_exists( 'wc_set_customer_auth_cookie' ) ) {
            wc_set_customer_auth_cookie( $user_id );
        } else {
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id, true );
        }

        $email_sent = $this->send_coupon_email( $email, $full_name, $coupon_code );
        $this->set_seen_cookie();

        wp_send_json_success(
            array(
                'message' => 'Cuenta creada correctamente.',
                'coupon'  => $coupon_code,
                'email_sent' => $email_sent,
            )
        );
    }

    public function ajax_login_coupon() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! class_exists( 'WC_Coupon' ) ) {
            wp_send_json_error( array( 'message' => 'WooCommerce no está disponible.' ) );
        }

        $email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';

        if ( empty( $email ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'Completa correo y contraseña.' ) );
        }

        $user = get_user_by( 'email', $email );

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'No encontramos una cuenta con ese correo.' ) );
        }

        $signon = wp_signon(
            array(
                'user_login'    => $user->user_login,
                'user_password' => $password,
                'remember'      => true,
            ),
            is_ssl()
        );

        if ( is_wp_error( $signon ) ) {
            wp_send_json_error( array( 'message' => 'Correo o contraseña incorrectos.' ) );
        }

        $user_id = $signon->ID;

        if ( get_user_meta( $user_id, '_tm_welcome_coupon_used', true ) === 'yes' ) {
            $this->set_seen_cookie();

            wp_send_json_error(
                array(
                    'message' => 'Ya usaste tu descuento de bienvenida.',
                )
            );
        }

        $coupon_code = $this->create_or_get_coupon_for_user( $user_id, $email );

        $email_sent = $this->send_coupon_email( $email, $user->display_name, $coupon_code );
        $this->set_seen_cookie();

        wp_send_json_success(
            array(
                'message' => 'Sesión iniciada correctamente.',
                'coupon'  => $coupon_code,
                'email_sent' => $email_sent,
            )
        );
    }

    private function generate_username_from_email( $email ) {
        $base = sanitize_user( current( explode( '@', $email ) ), true );

        if ( empty( $base ) ) {
            $base = 'cliente';
        }

        $username = $base;
        $i = 1;

        while ( username_exists( $username ) ) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    private function split_full_name( $full_name ) {
        $parts      = preg_split( '/\s+/', trim( $full_name ) );
        $first_name = array_shift( $parts );

        return array(
            'first_name' => $first_name ? $first_name : $full_name,
            'last_name'  => implode( ' ', $parts ),
        );
    }

    private function create_or_get_coupon_for_user( $user_id, $email ) {
        $existing_code = get_user_meta( $user_id, '_tm_welcome_coupon_code', true );

        if ( $existing_code && function_exists( 'wc_get_coupon_id_by_code' ) ) {
            $existing_coupon_id = wc_get_coupon_id_by_code( $existing_code );

            if ( $existing_coupon_id ) {
                $existing_coupon = new WC_Coupon( $existing_coupon_id );
                $existing_coupon->set_email_restrictions( array( $email ) );
                $existing_coupon->update_meta_data( '_tm_welcome_coupon_user_id', $user_id );
                $existing_coupon->update_meta_data( '_tm_welcome_coupon_scope', self::COUPON_SCOPE );
                $existing_coupon->save();

                return $existing_code;
            }
        }

        $options = $this->get_options();
        $amount  = absint( $options['coupon_amount'] );

        $code = '';

        for ( $i = 0; $i < 10; $i++ ) {
            $candidate = 'TM-ALQUILER-' . strtoupper( wp_generate_password( 6, false, false ) );

            if ( ! wc_get_coupon_id_by_code( $candidate ) ) {
                $code = $candidate;
                break;
            }
        }

        if ( empty( $code ) ) {
            $code = 'TM-ALQUILER-' . time();
        }

        $coupon = new WC_Coupon();

        $coupon->set_code( $code );
        $coupon->set_discount_type( 'fixed_cart' );
        $coupon->set_amount( $amount );
        $coupon->set_individual_use( true );
        $coupon->set_usage_limit( 1 );
        $coupon->set_usage_limit_per_user( 1 );
        $coupon->set_email_restrictions( array( $email ) );
        $coupon->set_description( 'Código para el primer alquiler de montacargas o batería. Usuario #' . $user_id );
        $coupon->add_meta_data( '_tm_welcome_coupon_user_id', $user_id, true );
        $coupon->add_meta_data( '_tm_welcome_coupon_scope', self::COUPON_SCOPE, true );
        $coupon->add_meta_data( '_tm_welcome_coupon_created_at', current_time( 'mysql' ), true );
        $coupon->save();

        update_user_meta( $user_id, '_tm_welcome_coupon_code', $code );

        return $code;
    }

    public function create_coupon_for_new_customer( $customer_id, $new_customer_data = array(), $password_generated = false ) {
        unset( $password_generated );

        $email = isset( $new_customer_data['user_email'] )
            ? sanitize_email( $new_customer_data['user_email'] )
            : '';

        if ( ! $email ) {
            $user  = get_userdata( $customer_id );
            $email = $user ? $user->user_email : '';
        }

        if ( $email ) {
            $this->create_or_get_coupon_for_user( $customer_id, $email );
        }
    }

    public function render_account_coupon() {
        if ( ! is_user_logged_in() || ! class_exists( 'WC_Coupon' ) ) {
            return;
        }

        $user_id = get_current_user_id();
        $user    = wp_get_current_user();
        $code    = get_user_meta( $user_id, '_tm_welcome_coupon_code', true );

        if ( ! $code && in_array( 'customer', (array) $user->roles, true ) ) {
            $code = $this->create_or_get_coupon_for_user( $user_id, $user->user_email );
        }

        $coupon_id = $code ? wc_get_coupon_id_by_code( $code ) : 0;

        if ( ! $coupon_id ) {
            return;
        }

        $coupon = new WC_Coupon( $coupon_id );
        $used   = $this->is_coupon_used( $coupon, $user_id );
        ?>
        <section class="tm-account-discount" aria-labelledby="tm-account-discount-title">
            <div>
                <span class="tm-account-discount__eyebrow">Beneficio de bienvenida</span>
                <h2 id="tm-account-discount-title">
                    <?php echo $used ? 'Código utilizado' : esc_html( $this->format_coupon_amount( $coupon->get_amount() ) . ' de descuento' ); ?>
                </h2>
                <p>
                    <?php echo $used
                        ? 'Este beneficio ya fue aplicado a tu primer alquiler.'
                        : 'Válido una sola vez para tu primer alquiler de un montacargas o una batería.'; ?>
                </p>
            </div>
            <div class="tm-account-discount__code">
                <span><?php echo $used ? 'Usado' : 'Tu código único'; ?></span>
                <strong><?php echo esc_html( $code ); ?></strong>
            </div>
            <?php if ( ! $used ) : ?>
                <div class="tm-account-discount__actions">
                    <a href="<?php echo esc_url( home_url( '/equipos/' ) ); ?>">Elegir montacargas</a>
                    <a href="<?php echo esc_url( home_url( '/energia/' ) ); ?>">Elegir batería</a>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }

    public function register_contact_form_tag() {
        if ( ! function_exists( 'wpcf7_add_form_tag' ) ) {
            return;
        }

        wpcf7_add_form_tag(
            'tm_discount',
            array( $this, 'render_contact_discount_tag' ),
            array( 'name-attr' => true )
        );
    }

    public function render_contact_discount_tag( $tag ) {
        $name  = $tag->name ? $tag->name : 'tm_discount_code';
        $value = '';

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $code    = get_user_meta( $user_id, '_tm_welcome_coupon_code', true );

            if ( $code ) {
                $coupon_id = wc_get_coupon_id_by_code( $code );
                $coupon    = $coupon_id ? new WC_Coupon( $coupon_id ) : null;

                if ( $coupon && ! $this->is_coupon_used( $coupon, $user_id ) ) {
                    $value = $code;
                }
            }
        }

        return sprintf(
            '<span class="wpcf7-form-control-wrap" data-name="%1$s"><input class="wpcf7-form-control wpcf7-text tm-discount-code" type="text" name="%1$s" value="%2$s" autocomplete="off" placeholder="TM-ALQUILER-XXXXXX"></span>',
            esc_attr( $name ),
            esc_attr( $value )
        );
    }

    public function validate_contact_discount_code( $result, $tag ) {
        if ( ! class_exists( 'WPCF7_Submission' ) || ! class_exists( 'WC_Coupon' ) ) {
            return $result;
        }

        $submission = WPCF7_Submission::get_instance();

        if ( ! $submission ) {
            return $result;
        }

        $code = strtoupper( sanitize_text_field( $submission->get_posted_string( $tag->name ) ) );

        if ( '' === $code ) {
            return $result;
        }

        $coupon_id = wc_get_coupon_id_by_code( $code );
        $coupon    = $coupon_id ? new WC_Coupon( $coupon_id ) : null;

        if ( ! $coupon || ! $coupon->get_id() || ! $this->is_managed_coupon( $coupon ) ) {
            $result->invalidate( $tag, 'El código de descuento no es válido.' );
            return $result;
        }

        $coupon_user_id = absint( $coupon->get_meta( '_tm_welcome_coupon_user_id', true ) );
        $coupon_user    = get_userdata( $coupon_user_id );
        $form_email     = sanitize_email( $submission->get_posted_string( 'email' ) );

        if (
            ! $coupon_user
            || ! $form_email
            || 0 !== strcasecmp( $coupon_user->user_email, $form_email )
            || ( is_user_logged_in() && get_current_user_id() !== $coupon_user_id )
        ) {
            $result->invalidate( $tag, 'El código no corresponde al correo de esta solicitud.' );
            return $result;
        }

        if ( $this->is_coupon_used( $coupon, $coupon_user_id ) ) {
            $result->invalidate( $tag, 'Este código de descuento ya fue utilizado.' );
        }

        return $result;
    }

    private function is_managed_coupon( $coupon ) {
        return $coupon instanceof WC_Coupon
            && self::COUPON_SCOPE === $coupon->get_meta( '_tm_welcome_coupon_scope', true )
            && absint( $coupon->get_meta( '_tm_welcome_coupon_user_id', true ) ) > 0;
    }

    private function is_coupon_used( $coupon, $user_id ) {
        return 'yes' === $coupon->get_meta( '_tm_welcome_coupon_redeemed', true )
            || 'yes' === get_user_meta( $user_id, '_tm_welcome_coupon_used', true );
    }

    private function format_coupon_amount( $amount ) {
        return '$' . number_format_i18n( (float) $amount, 0 ) . ' COP';
    }

    public function mark_coupon_as_used( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $user_id = $order->get_user_id();

        if ( ! $user_id ) {
            return;
        }

        $coupon_codes = $order->get_coupon_codes();

        if ( empty( $coupon_codes ) ) {
            return;
        }

        foreach ( $coupon_codes as $coupon_code ) {
            $coupon_id = wc_get_coupon_id_by_code( $coupon_code );

            if ( ! $coupon_id ) {
                continue;
            }

            $coupon_user_id = get_post_meta( $coupon_id, '_tm_welcome_coupon_user_id', true );

            if ( (int) $coupon_user_id === (int) $user_id ) {
                $coupon = new WC_Coupon( $coupon_id );
                $coupon->update_meta_data( '_tm_welcome_coupon_redeemed', 'yes' );
                $coupon->update_meta_data( '_tm_welcome_coupon_redeemed_at', current_time( 'mysql' ) );
                $coupon->save();
                update_user_meta( $user_id, '_tm_welcome_coupon_used', 'yes' );
            }
        }
    }

    private function send_coupon_email( $email, $name, $coupon_code ) {
        $subject = 'Tu descuento de bienvenida Tecni Montacargas';
        $options = $this->get_options();
        $amount  = $this->format_coupon_amount( $options['coupon_amount'] );
        $account = function_exists( 'wc_get_page_permalink' )
            ? wc_get_page_permalink( 'myaccount' )
            : home_url( '/mi-cuenta/' );

        $message  = '<div style="font-family:Arial,Helvetica,sans-serif;color:#34425a;line-height:1.65">';
        $message .= '<h2 style="margin:0 0 14px;color:#262e4f">¡Bienvenido, ' . esc_html( $name ) . '!</h2>';
        $message .= '<p style="margin:0 0 18px">Gracias por crear tu cuenta en Tecni Montacargas.</p>';
        $message .= '<div style="margin:22px 0;padding:24px;border-radius:12px;text-align:center;background:#f4f7fb;border:1px solid #dce6f1">';
        $message .= '<span style="display:block;margin-bottom:8px;color:#68758a;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em">Tu código de descuento</span>';
        $message .= '<strong style="display:block;color:#128ceb;font-size:25px;letter-spacing:.04em">' . esc_html( $coupon_code ) . '</strong>';
        $message .= '<span style="display:block;margin-top:8px;color:#262e4f;font-weight:700">' . esc_html( $amount ) . '</span>';
        $message .= '</div>';
        $message .= '<p style="margin:0 0 22px">Válido una sola vez para tu primer alquiler de un montacargas o una batería. Preséntalo al solicitar la cotización.</p>';
        $message .= '<p style="margin:0"><a href="' . esc_url( $account ) . '" style="display:inline-block;padding:12px 20px;border-radius:8px;color:#fff;background:#128ceb;font-weight:700;text-decoration:none">Ir a Mi cuenta</a></p>';
        $message .= '</div>';

        return wp_mail(
            $email,
            $subject,
            $message,
            array( 'Content-Type: text/html; charset=UTF-8' )
        );
    }

    private function set_seen_cookie() {
        setcookie(
            self::COOKIE_NAME,
            'true',
            time() + YEAR_IN_SECONDS,
            COOKIEPATH ? COOKIEPATH : '/',
            COOKIE_DOMAIN,
            is_ssl(),
            false
        );
    }
}

add_action(
    'plugins_loaded',
    function () {
        new TM_Popup_Bienvenida_Woo();
    }
);
