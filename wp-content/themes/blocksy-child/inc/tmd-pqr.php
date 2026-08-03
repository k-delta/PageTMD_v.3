<?php
/**
 * Envío público del formulario PQR.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('tmd_pqr_recipient')) {
    function tmd_pqr_recipient() {
        return 'gerencia@tmdual.com';
    }
}

if (! function_exists('tmd_pqr_textarea')) {
    function tmd_pqr_textarea($value) {
        if (function_exists('sanitize_textarea_field')) {
            return sanitize_textarea_field($value);
        }

        return sanitize_text_field($value);
    }
}

if (! function_exists('tmd_pqr_string_length')) {
    function tmd_pqr_string_length($value) {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}

if (! function_exists('tmd_pqr_validate_fields')) {
    function tmd_pqr_validate_fields($post) {
        $post      = is_array($post) ? $post : [];
        $form_type = sanitize_text_field(wp_unslash((string) ($post['form_type'] ?? '')));

        if ('pqr' !== $form_type) {
            return new WP_Error('invalid_form_type', 'El formulario solicitado no está disponible.');
        }

        $raw_email = wp_unslash((string) ($post['email'] ?? ''));
        $email     = sanitize_email($raw_email);
        $fields    = [
            'form_type'    => $form_type,
            'request_type' => sanitize_text_field(wp_unslash((string) ($post['request_type'] ?? ''))),
            'subject'      => sanitize_text_field(wp_unslash((string) ($post['subject'] ?? ''))),
            'name'         => sanitize_text_field(wp_unslash((string) ($post['name'] ?? ''))),
            'email'        => $email,
            'phone'        => sanitize_text_field(wp_unslash((string) ($post['phone'] ?? ''))),
            'order_number' => sanitize_text_field(wp_unslash((string) ($post['order_number'] ?? ''))),
            'message'      => tmd_pqr_textarea(wp_unslash((string) ($post['message'] ?? ''))),
            'terms'        => sanitize_text_field(wp_unslash((string) ($post['terms'] ?? ''))),
        ];

        foreach (['request_type', 'subject', 'name', 'email', 'phone', 'message'] as $required) {
            if ('' === $fields[$required]) {
                return new WP_Error('missing_fields', 'Completa todos los campos obligatorios.');
            }
        }

        if (! in_array($fields['request_type'], ['Peticion', 'Queja', 'Reclamo', 'Reembolso'], true)) {
            return new WP_Error('invalid_request_type', 'Selecciona un tipo de solicitud válido.');
        }

        if ($email !== trim($raw_email) || ! is_email($email)) {
            return new WP_Error('invalid_email', 'Escribe un correo electrónico válido.');
        }

        if ('Acepto' !== $fields['terms']) {
            return new WP_Error('missing_terms', 'Debes aceptar la política de tratamiento de datos.');
        }

        $limits = [
            'subject'      => 160,
            'name'         => 120,
            'phone'        => 40,
            'order_number' => 100,
            'message'      => 5000,
        ];

        foreach ($limits as $field => $limit) {
            if (tmd_pqr_string_length($fields[$field]) > $limit) {
                return new WP_Error('field_too_long', 'Uno de los campos supera la longitud permitida.');
            }
        }

        return $fields;
    }
}

if (! function_exists('tmd_pqr_build_mail')) {
    function tmd_pqr_build_mail($fields) {
        $rows = [
            'Tipo de solicitud' => $fields['request_type'],
            'Asunto'            => $fields['subject'],
            'Nombre'            => $fields['name'],
            'Email'             => $fields['email'],
            'Teléfono'          => $fields['phone'],
            'Número de pedido'  => $fields['order_number'] ?: 'No informado',
            'Descripción'       => nl2br(esc_html($fields['message'])),
        ];
        $message = '<div style="font-family:Arial,sans-serif;color:#262e4f;line-height:1.6">';
        $message .= '<h1 style="font-size:24px">Nueva solicitud PQR</h1>';
        $message .= '<table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%">';

        foreach ($rows as $label => $value) {
            $safe_value = 'Descripción' === $label ? $value : esc_html($value);
            $message .= '<tr><th align="left" style="border-bottom:1px solid #dce3ec;width:180px">' . esc_html($label) . '</th>';
            $message .= '<td style="border-bottom:1px solid #dce3ec">' . $safe_value . '</td></tr>';
        }

        $message .= '</table></div>';

        return [
            'to'      => tmd_pqr_recipient(),
            'subject' => sprintf('PQR | %s | %s', $fields['request_type'], $fields['subject']),
            'message' => $message,
            'headers' => [
                'Content-Type: text/html; charset=UTF-8',
                'Reply-To: ' . $fields['email'],
            ],
        ];
    }
}

if (! function_exists('tmd_pqr_rate_limited')) {
    function tmd_pqr_rate_limited($email, $ip) {
        $lock_path = trailingslashit(get_temp_dir()) . 'tmd-pqr-rate.lock';
        $lock      = @fopen($lock_path, 'c');

        if (! $lock || ! flock($lock, LOCK_EX)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            return true;
        }

        $keys = [
            ['key' => 'tmd_pqr_email_' . substr(wp_hash(strtolower($email)), 0, 32), 'limit' => 5],
            ['key' => 'tmd_pqr_ip_' . substr(wp_hash($ip), 0, 32), 'limit' => 5],
            ['key' => 'tmd_pqr_global', 'limit' => 50],
        ];

        try {
            foreach ($keys as $index => $entry) {
                $keys[$index]['count'] = (int) get_transient($entry['key']);
                if ($keys[$index]['count'] >= $entry['limit']) {
                    return true;
                }
            }

            foreach ($keys as $entry) {
                set_transient($entry['key'], $entry['count'] + 1, HOUR_IN_SECONDS);
            }

            return false;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}

if (! function_exists('tmd_pqr_error_status')) {
    function tmd_pqr_error_status($error) {
        return 'invalid_form_type' === $error->get_error_code() ? 403 : 400;
    }
}

if (! function_exists('tmd_pqr_ajax')) {
    function tmd_pqr_ajax() {
        if (! check_ajax_referer('tmd_pqr', 'nonce', false)) {
            wp_send_json_error(['message' => 'La sesión expiró. Recarga la página e inténtalo nuevamente.'], 403);
        }

        if (! empty($_POST['website'])) {
            wp_send_json_success(['message' => 'Solicitud PQR procesada correctamente.']);
        }

        $fields = tmd_pqr_validate_fields($_POST);
        if (is_wp_error($fields)) {
            wp_send_json_error(['message' => $fields->get_error_message()], tmd_pqr_error_status($fields));
        }

        $ip = sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
        if (tmd_pqr_rate_limited($fields['email'], $ip)) {
            wp_send_json_error(['message' => 'Has realizado varios intentos. Espera un momento antes de volver a enviar.'], 429);
        }

        $sent = false;
        try {
            $mail = tmd_pqr_build_mail($fields);
            $sent = wp_mail($mail['to'], $mail['subject'], $mail['message'], $mail['headers']);
        } catch (Throwable $error) {
            $sent = false;
        }

        if (! $sent) {
            wp_send_json_error(['message' => 'No fue posible procesar la solicitud. Inténtalo nuevamente más tarde.'], 502);
        }

        wp_send_json_success(['message' => 'Solicitud PQR procesada correctamente.']);
    }
}

add_action('wp_ajax_tmd_pqr', 'tmd_pqr_ajax');
add_action('wp_ajax_nopriv_tmd_pqr', 'tmd_pqr_ajax');

add_action('wp_enqueue_scripts', function () {
    if (! is_page(284)) {
        return;
    }

    $js_path = get_stylesheet_directory() . '/assets/js/tmd-pqr.js';
    wp_enqueue_script(
        'tmd-pqr',
        get_stylesheet_directory_uri() . '/assets/js/tmd-pqr.js',
        [],
        file_exists($js_path) ? filemtime($js_path) : '1.0.0',
        true
    );
    wp_localize_script('tmd-pqr', 'tmdPqr', [
        'ajaxUrl'      => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('tmd_pqr'),
        'networkError' => 'No fue posible conectar con el servidor. Inténtalo nuevamente.',
        'sendingText'  => 'Procesando solicitud…',
    ]);
}, 45);
