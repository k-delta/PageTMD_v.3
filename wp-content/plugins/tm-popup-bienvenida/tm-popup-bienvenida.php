<?php
/**
 * Plugin Name: TM Popup Bienvenida WooCommerce
 * Description: Popup de bienvenida para Tecni Montacargas con registro, login, cookie y cupón WooCommerce único.
 * Version: 1.1.0
 * Author: Tecni Montacargas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_Popup_Bienvenida_Woo {

    const COOKIE_NAME = 'tm_popup_seen';
    const OPTION_NAME = 'tm_popup_bienvenida_options';
    const NONCE_ACTION = 'tm_popup_bienvenida_nonce';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_popup' ) );

        add_action( 'wp_ajax_nopriv_tm_register_coupon', array( $this, 'ajax_register_coupon' ) );
        add_action( 'wp_ajax_nopriv_tm_login_coupon', array( $this, 'ajax_login_coupon' ) );

        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'auto_apply_coupon' ), 20 );

        add_action( 'woocommerce_order_status_processing', array( $this, 'mark_coupon_as_used' ) );
        add_action( 'woocommerce_order_status_completed', array( $this, 'mark_coupon_as_used' ) );

        add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
    }

    private function defaults() {
        return array(
            'enabled'         => 'yes',
            'logo_url'        => '',
            'title'           => '¡Bienvenido!',
            'subtitle'        => 'Obtén $100.000 COP de descuento en tu primera compra de repuestos o alquiler de montacargas.',
            'coupon_amount'   => '100000',
            'register_button' => 'Registrarme y obtener descuento',
            'login_button'    => 'Iniciar sesión y usar descuento',
            'policy_url'      => home_url( '/politica-de-tratamiento-de-datos/' ),
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
        ?>

        <div class="wrap">
            <h1>TM Popup Bienvenida</h1>

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
        </div>

        <?php
    }

    public function enqueue_assets() {
        if ( ! $this->should_show_popup() ) {
            return;
        }

        $options = $this->get_options();

        wp_enqueue_style(
            'tm-popup-bienvenida',
            plugin_dir_url( __FILE__ ) . 'assets/tm-popup.css',
            array(),
            '1.1.0'
        );

        wp_enqueue_script(
            'tm-popup-bienvenida',
            plugin_dir_url( __FILE__ ) . 'assets/tm-popup.js',
            array(),
            '1.0.0',
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
                    <p>Usa este código en tu primera compra:</p>
                    <strong id="tm-coupon-code"></strong>
                    <p class="tm-small">También te lo enviamos al correo.</p>
                    <a class="tm-account-link" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/mi-cuenta/' ) ); ?>">Ir a Mi cuenta</a>
                </div>
            </div>
        </div>

        <?php
    }

    public function ajax_register_coupon() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( is_user_logged_in() ) {
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

        $this->send_coupon_email( $email, $full_name, $coupon_code );
        $this->set_seen_cookie();

        wp_send_json_success(
            array(
                'message' => 'Cuenta creada correctamente.',
                'coupon'  => $coupon_code,
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

        if ( $this->user_has_previous_orders( $user_id ) ) {
            $this->set_seen_cookie();

            wp_send_json_error(
                array(
                    'message' => 'No aplica: este descuento es solo para la primera compra.',
                )
            );
        }

        if ( get_user_meta( $user_id, '_tm_welcome_coupon_used', true ) === 'yes' ) {
            $this->set_seen_cookie();

            wp_send_json_error(
                array(
                    'message' => 'Ya usaste tu descuento de bienvenida.',
                )
            );
        }

        $coupon_code = $this->create_or_get_coupon_for_user( $user_id, $email );

        $this->send_coupon_email( $email, $user->display_name, $coupon_code );
        $this->set_seen_cookie();

        wp_send_json_success(
            array(
                'message' => 'Sesión iniciada correctamente.',
                'coupon'  => $coupon_code,
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
                return $existing_code;
            }
        }

        $options = $this->get_options();
        $amount  = absint( $options['coupon_amount'] );

        $code = '';

        for ( $i = 0; $i < 10; $i++ ) {
            $candidate = 'TM-BIENVENIDA-' . strtoupper( wp_generate_password( 6, false, false ) );

            if ( ! wc_get_coupon_id_by_code( $candidate ) ) {
                $code = $candidate;
                break;
            }
        }

        if ( empty( $code ) ) {
            $code = 'TM-BIENVENIDA-' . time();
        }

        $coupon = new WC_Coupon();

        $coupon->set_code( $code );
        $coupon->set_discount_type( 'fixed_cart' );
        $coupon->set_amount( $amount );
        $coupon->set_individual_use( true );
        $coupon->set_usage_limit( 1 );
        $coupon->set_usage_limit_per_user( 1 );
        $coupon->set_email_restrictions( array( $email ) );
        $coupon->set_description( 'Cupón de bienvenida generado automáticamente para ' . $email );
        $coupon->add_meta_data( '_tm_welcome_coupon_user_id', $user_id, true );
        $coupon->save();

        update_user_meta( $user_id, '_tm_welcome_coupon_code', $code );

        return $code;
    }

    private function user_has_previous_orders( $user_id ) {
        if ( ! function_exists( 'wc_get_orders' ) ) {
            return false;
        }

        $orders = wc_get_orders(
            array(
                'customer_id' => $user_id,
                'limit'       => 1,
                'status'      => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
                'return'      => 'ids',
            )
        );

        return ! empty( $orders );
    }

    public function auto_apply_coupon() {
        if ( is_admin() && ! wp_doing_ajax() ) {
            return;
        }

        if ( ! is_user_logged_in() || ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        if ( WC()->cart->is_empty() ) {
            return;
        }

        $user_id = get_current_user_id();

        if ( get_user_meta( $user_id, '_tm_welcome_coupon_used', true ) === 'yes' ) {
            return;
        }

        if ( $this->user_has_previous_orders( $user_id ) ) {
            return;
        }

        $coupon_code = get_user_meta( $user_id, '_tm_welcome_coupon_code', true );

        if ( empty( $coupon_code ) ) {
            return;
        }

        if ( WC()->cart->has_discount( $coupon_code ) ) {
            return;
        }

        WC()->cart->apply_coupon( $coupon_code );
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
                update_user_meta( $user_id, '_tm_welcome_coupon_used', 'yes' );
            }
        }
    }

    private function send_coupon_email( $email, $name, $coupon_code ) {
        $subject = 'Tu descuento de bienvenida Tecni Montacargas';
        $account = function_exists( 'wc_get_page_permalink' )
            ? wc_get_page_permalink( 'myaccount' )
            : home_url( '/mi-cuenta/' );

        $message  = '<div style="font-family:Arial,Helvetica,sans-serif;color:#34425a;line-height:1.65">';
        $message .= '<h2 style="margin:0 0 14px;color:#262e4f">¡Bienvenido, ' . esc_html( $name ) . '!</h2>';
        $message .= '<p style="margin:0 0 18px">Gracias por crear tu cuenta en Tecni Montacargas.</p>';
        $message .= '<div style="margin:22px 0;padding:24px;border-radius:12px;text-align:center;background:#f4f7fb;border:1px solid #dce6f1">';
        $message .= '<span style="display:block;margin-bottom:8px;color:#68758a;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em">Tu código de descuento</span>';
        $message .= '<strong style="display:block;color:#128ceb;font-size:25px;letter-spacing:.04em">' . esc_html( $coupon_code ) . '</strong>';
        $message .= '<span style="display:block;margin-top:8px;color:#262e4f;font-weight:700">$100.000 COP</span>';
        $message .= '</div>';
        $message .= '<p style="margin:0 0 22px">Aplica para tu primera compra de repuestos o primer alquiler de montacargas gestionado en WooCommerce.</p>';
        $message .= '<p style="margin:0"><a href="' . esc_url( $account ) . '" style="display:inline-block;padding:12px 20px;border-radius:8px;color:#fff;background:#128ceb;font-weight:700;text-decoration:none">Ir a Mi cuenta</a></p>';
        $message .= '</div>';

        wp_mail(
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
