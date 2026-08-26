<?php
/**
 * Plugin Name: TM Chatbot Fase 1
 * Description: Chatbot simple sin IA para Tecni Montacargas: FAQ, WhatsApp, leads e historial.
 * Version: 1.0.0
 * Author: Tecni Montacargas
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crear tabla de leads e historial al activar.
 */
register_activation_hook(__FILE__, 'tm_chatbot_fase1_activate');

function tm_chatbot_fase1_activate() {
    global $wpdb;

    $table = $wpdb->prefix . 'tm_chatbot_fase1_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME NOT NULL,
        type VARCHAR(30) NOT NULL DEFAULT 'chat',
        name VARCHAR(160) NULL,
        phone VARCHAR(80) NULL,
        email VARCHAR(190) NULL,
        message TEXT NULL,
        response TEXT NULL,
        ip VARCHAR(80) NULL,
        user_agent TEXT NULL,
        PRIMARY KEY (id),
        KEY created_at (created_at),
        KEY type (type)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Configuración rápida.
 */
function tm_chatbot_fase1_config() {
    return [
        'whatsapp_number' => '573015556180', // Número con indicativo de país, sin +
        'company_name' => 'Tecni Montacargas',
        'human_schedule' => 'Atención humana: lunes a viernes de 8:00 a.m. a 5:00 p.m.',
    ];
}

/**
 * Insertar widget global.
 */
add_action('wp_footer', 'tm_chatbot_fase1_render', 99);

function tm_chatbot_fase1_render() {
    if (is_admin()) {
        return;
    }

    $config = tm_chatbot_fase1_config();
    $ajax_url = admin_url('admin-ajax.php');
    $nonce = wp_create_nonce('tm_chatbot_fase1_nonce');
    ?>

    <div id="tm-chatbot-fase1" class="tm-chatbot-fase1">
        <div class="tm-chat-window" hidden>
            <div class="tm-chat-header">
                <div class="tm-chat-brand">
                    <div class="tm-chat-logo">TM</div>
                    <div>
                        <strong>Asistente Tecni Montacargas</strong>
                        <span>Soporte y orientación rápida</span>
                    </div>
                </div>
                <button type="button" class="tm-chat-close" aria-label="Cerrar chat">×</button>
            </div>

            <div class="tm-chat-body">
                <div class="tm-chat-hours">
                    <?php echo esc_html($config['human_schedule']); ?>
                </div>

                <div class="tm-chat-messages">
                    <div class="tm-msg tm-msg-bot">
                        Hola, soy el asistente virtual de Tecni Montacargas. Puedo ayudarte con mantenimiento, repuestos, alquiler, venta, garantías, horarios o contacto.
                    </div>
                </div>

                <div class="tm-quick-actions">
                    <button type="button" data-question="Necesito una cotización">Cotización</button>
                    <button type="button" data-question="Necesito mantenimiento">Mantenimiento</button>
                    <button type="button" data-question="Necesito repuestos">Repuestos</button>
                    <button type="button" data-question="Quiero hablar con un asesor">Asesor humano</button>
                </div>

                <div class="tm-lead-box" hidden>
                    <strong>Déjanos tus datos</strong>
                    <p>Un asesor podrá contactarte para revisar tu solicitud.</p>

                    <form class="tm-lead-form">
                        <input type="text" name="name" placeholder="Nombre" required>
                        <input type="tel" name="phone" placeholder="Teléfono / WhatsApp" required>
                        <input type="email" name="email" placeholder="Correo opcional">
                        <textarea name="message" placeholder="Cuéntanos qué necesitas" required></textarea>
                        <button type="submit">Enviar solicitud</button>
                    </form>
                </div>
            </div>

            <div class="tm-chat-footer">
                <form class="tm-chat-form">
                    <input type="text" class="tm-chat-input" placeholder="Escribe tu consulta..." maxlength="500" required>
                    <button type="submit">Enviar</button>
                </form>
            </div>
        </div>

        <button type="button" class="tm-chat-tab" aria-label="Abrir chat">
            <span class="tm-chat-icon">💬</span>
            <span class="tm-chat-text">Chat</span>
            <span class="tm-chat-badge" hidden>1</span>
        </button>
    </div>

    <style>
        .tm-chatbot-fase1 {
            position: fixed;
            right: 0;
            top: 50%;
            bottom: auto;
            transform: translateY(-50%);
            z-index: 10000;
            font-family: Arial, sans-serif;
        }

        .tm-chat-tab {
            width: 56px;
            height: 56px;
            min-width: 56px;
            border: 0;
            border-radius: 999px 0 0 999px;
            background: #128CEB;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            cursor: pointer;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.25);
            transition: transform 200ms ease, background 200ms ease;
            position: relative;
            font-weight: 800;
        }

        .tm-chat-tab:hover {
            background: #0f7ad4;
            transform: translateX(-4px);
        }

        .tm-chat-icon {
            font-size: 22px;
            line-height: 1;
        }

        .tm-chat-text {
            display: none;
        }

        .tm-chat-badge {
            position: absolute;
            top: -5px;
            right: 4px;
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #FFC33C;
            color: #262E4F;
            font-size: 12px;
            font-weight: 900;
            line-height: 20px;
            text-align: center;
        }

        .tm-chat-window {
            position: absolute;
            right: 70px;
            top: 50%;
            width: 360px;
            max-width: calc(100vw - 90px);
            height: 520px;
            max-height: calc(100vh - 40px);
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.30);
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            opacity: 0;
            transform: translateY(-50%) translateX(12px);
            pointer-events: none;
            transition: opacity 200ms ease, transform 200ms ease;
        }

        .tm-chatbot-fase1.is-open .tm-chat-window {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
            pointer-events: auto;
        }

        .tm-chat-header {
            background: #262E4F;
            color: #ffffff;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tm-chat-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tm-chat-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #128CEB;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 14px;
        }

        .tm-chat-brand strong {
            display: block;
            font-size: 14px;
            line-height: 1.2;
        }

        .tm-chat-brand span {
            display: block;
            font-size: 12px;
            opacity: 0.8;
            margin-top: 2px;
        }

        .tm-chat-close {
            border: 0;
            background: transparent;
            color: #ffffff;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
        }

        .tm-chat-body {
            flex: 1;
            padding: 14px;
            overflow-y: auto;
            background: #ffffff;
        }

        .tm-chat-hours {
            background: #f3f6fb;
            color: #262E4F;
            border-left: 4px solid #128CEB;
            border-radius: 10px;
            padding: 10px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .tm-chat-messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .tm-msg {
            max-width: 86%;
            padding: 10px 12px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.45;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .tm-msg-bot {
            align-self: flex-start;
            background: #f1f4f8;
            color: #111111;
            border-bottom-left-radius: 4px;
        }

        .tm-msg-user {
            align-self: flex-end;
            background: #128CEB;
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }

        .tm-quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 14px;
        }

        .tm-quick-actions button {
            border: 1px solid #128CEB;
            background: #ffffff;
            color: #128CEB;
            border-radius: 999px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .tm-quick-actions button:hover {
            background: #eef7ff;
        }

        .tm-lead-box {
            margin-top: 14px;
            background: #f8fafc;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            padding: 12px;
        }

        .tm-lead-box strong {
            display: block;
            color: #262E4F;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .tm-lead-box p {
            margin: 0 0 10px;
            color: #555;
            font-size: 12px;
        }

        .tm-lead-form {
            display: grid;
            gap: 8px;
        }

        .tm-lead-form input,
        .tm-lead-form textarea {
            width: 100%;
            border: 1px solid #d7dde6;
            border-radius: 10px;
            padding: 9px 10px;
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
        }

        .tm-lead-form textarea {
            min-height: 70px;
            resize: vertical;
        }

        .tm-lead-form button {
            border: 0;
            background: #128CEB;
            color: #ffffff;
            border-radius: 10px;
            padding: 10px;
            font-weight: 800;
            cursor: pointer;
        }

        .tm-chat-footer {
            border-top: 1px solid #eef1f5;
            padding: 12px;
            background: #ffffff;
        }

        .tm-chat-form {
            display: flex;
            gap: 8px;
        }

        .tm-chat-input {
            flex: 1;
            min-width: 0;
            border: 1px solid #d7dde6;
            border-radius: 999px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
        }

        .tm-chat-input:focus {
            border-color: #128CEB;
            box-shadow: 0 0 0 3px rgba(18, 140, 235, 0.12);
        }

        .tm-chat-form button {
            border: 0;
            background: #128CEB;
            color: #ffffff;
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .tm-chat-form button:hover {
            background: #0f7ad4;
        }

        @media (max-width: 767px) {
            .tm-chatbot-fase1 {
                right: 0;
                top: 50%;
                bottom: auto;
                transform: translateY(-50%);
            }

            .tm-chat-window {
                right: 64px;
                top: 50%;
                width: calc(100vw - 82px);
                height: min(560px, calc(100vh - 32px));
                max-height: calc(100vh - 32px);
                transform: translateY(-50%) translateX(12px);
            }

            .tm-chatbot-fase1.is-open .tm-chat-window {
                transform: translateY(-50%) translateX(0);
            }

            .tm-chat-tab {
                width: 54px;
                height: 54px;
                min-width: 54px;
                border-radius: 999px 0 0 999px;
            }
        }
    </style>

    <script>
        (function () {
            const root = document.getElementById('tm-chatbot-fase1');
            if (!root) return;

            const windowBox = root.querySelector('.tm-chat-window');
            const tab = root.querySelector('.tm-chat-tab');
            const closeBtn = root.querySelector('.tm-chat-close');
            const form = root.querySelector('.tm-chat-form');
            const input = root.querySelector('.tm-chat-input');
            const messages = root.querySelector('.tm-chat-messages');
            const leadBox = root.querySelector('.tm-lead-box');
            const leadForm = root.querySelector('.tm-lead-form');
            const quickButtons = root.querySelectorAll('.tm-quick-actions button');
            const badge = root.querySelector('.tm-chat-badge');

            const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
            const nonce = <?php echo wp_json_encode($nonce); ?>;
            const whatsappNumber = <?php echo wp_json_encode($config['whatsapp_number']); ?>;

            function openChat() {
                windowBox.hidden = false;
                requestAnimationFrame(() => {
                    root.classList.add('is-open');
                    badge.hidden = true;
                    input.focus();
                });
            }

            function closeChat() {
                root.classList.remove('is-open');
                setTimeout(() => {
                    windowBox.hidden = true;
                }, 200);
            }

            function addMessage(text, type) {
                const el = document.createElement('div');
                el.className = 'tm-msg ' + (type === 'user' ? 'tm-msg-user' : 'tm-msg-bot');
                el.textContent = text;
                messages.appendChild(el);
                messages.parentElement.scrollTop = messages.parentElement.scrollHeight;
            }

            function normalize(text) {
                return text.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            function getResponse(message) {
                const msg = normalize(message);

                if (
                    msg.includes('asesor') ||
                    msg.includes('humano') ||
                    msg.includes('contacto') ||
                    msg.includes('whatsapp') ||
                    msg.includes('llamar')
                ) {
                    leadBox.hidden = false;
                    return 'Claro. Puedes dejar tus datos aquí o escribir directamente por WhatsApp. Un asesor humano podrá revisar tu caso.';
                }

                if (
                    msg.includes('cotizacion') ||
                    msg.includes('cotizar') ||
                    msg.includes('precio') ||
                    msg.includes('valor') ||
                    msg.includes('cuanto cuesta')
                ) {
                    leadBox.hidden = false;
                    return 'Para una cotización necesitamos algunos datos: ciudad, tipo de equipo, capacidad requerida, tiempo de uso y datos de contacto. Déjame tus datos y un asesor te contactará.';
                }

                if (
                    msg.includes('mantenimiento') ||
                    msg.includes('reparacion') ||
                    msg.includes('revision') ||
                    msg.includes('diagnostico') ||
                    msg.includes('tecnico')
                ) {
                    leadBox.hidden = false;
                    return 'Sí, Tecni Montacargas puede orientar solicitudes de mantenimiento preventivo, correctivo y diagnóstico técnico. Para revisar el caso se requiere información del equipo y tus datos de contacto.';
                }

                if (
                    msg.includes('repuesto') ||
                    msg.includes('repuestos') ||
                    msg.includes('pieza') ||
                    msg.includes('partes')
                ) {
                    leadBox.hidden = false;
                    return 'Sí, puedes consultar por repuestos. Lo ideal es indicar marca, modelo, referencia del equipo y el repuesto requerido. Déjanos tus datos para que un asesor lo revise.';
                }

                if (
                    msg.includes('alquiler') ||
                    msg.includes('renta') ||
                    msg.includes('arrendar') ||
                    msg.includes('arriendo')
                ) {
                    leadBox.hidden = false;
                    return 'Para alquiler de montacargas se debe validar ciudad, capacidad requerida, tipo de operación y tiempo de uso. Déjanos tus datos y un asesor te contactará.';
                }

                if (
                    msg.includes('venta') ||
                    msg.includes('comprar') ||
                    msg.includes('montacargas') ||
                    msg.includes('equipo')
                ) {
                    leadBox.hidden = false;
                    return 'Tecni Montacargas puede orientarte en venta y selección de equipos para movimiento de carga. Para avanzar necesitamos conocer capacidad, tipo de operación, ciudad y datos de contacto.';
                }

                if (
                    msg.includes('garantia') ||
                    msg.includes('reclamo') ||
                    msg.includes('fallo') ||
                    msg.includes('problema')
                ) {
                    leadBox.hidden = false;
                    return 'Para revisar una garantía o reclamo se necesita información del equipo, soporte de compra o factura, descripción del problema y datos de contacto. No puedo confirmar cobertura sin revisión humana.';
                }

                if (
                    msg.includes('horario') ||
                    msg.includes('atienden') ||
                    msg.includes('abierto')
                ) {
                    return 'La atención humana es de lunes a viernes de 8:00 a.m. a 5:00 p.m. Puedes dejar tu solicitud aquí y un asesor la revisará.';
                }

                if (
                    msg.includes('ubicacion') ||
                    msg.includes('direccion') ||
                    msg.includes('donde estan') ||
                    msg.includes('mapa')
                ) {
                    leadBox.hidden = false;
                    return 'Para darte la ubicación o cobertura correcta, déjanos tu ciudad o escribe por WhatsApp para que un asesor te oriente.';
                }

                return 'Puedo ayudarte con mantenimiento, repuestos, alquiler, venta, garantías, horarios, contacto o cotizaciones. Si tu consulta es comercial o técnica, lo mejor es dejar tus datos para que un asesor humano te contacte.';
            }

            function sendMessage(text) {
                const cleanText = text.trim();
                if (!cleanText) return;

                addMessage(cleanText, 'user');

                const response = getResponse(cleanText);

                setTimeout(() => {
                    addMessage(response, 'bot');

                    const data = new FormData();
                    data.append('action', 'tm_chatbot_fase1_log_chat');
                    data.append('nonce', nonce);
                    data.append('message', cleanText);
                    data.append('response', response);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: data
                    });
                }, 300);
            }

            tab.addEventListener('click', function () {
                if (root.classList.contains('is-open')) {
                    closeChat();
                } else {
                    openChat();
                }
            });

            closeBtn.addEventListener('click', closeChat);

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const text = input.value;
                input.value = '';
                sendMessage(text);
            });

            quickButtons.forEach(button => {
                button.addEventListener('click', function () {
                    sendMessage(button.dataset.question);
                });
            });

            leadForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData(leadForm);
                formData.append('action', 'tm_chatbot_fase1_save_lead');
                formData.append('nonce', nonce);

                try {
                    const res = await fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    });

                    const json = await res.json();

                    if (!json.success) {
                        addMessage('No pude registrar tus datos. Intenta escribir directamente por WhatsApp.', 'bot');
                        return;
                    }

                    addMessage('Listo. Tu solicitud quedó registrada. También puedes continuar por WhatsApp.', 'bot');

                    const name = formData.get('name') || '';
                    const phone = formData.get('phone') || '';
                    const message = formData.get('message') || '';

                    const whatsappText =
                        'Hola, quiero hablar con un asesor de Tecni Montacargas.' +
                        '\n\nNombre: ' + name +
                        '\nTeléfono: ' + phone +
                        '\nSolicitud: ' + message;

                    const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(whatsappText);

                    leadForm.reset();
                    leadBox.hidden = true;

                    window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
                } catch (error) {
                    addMessage('No pude registrar tus datos. Intenta escribir directamente por WhatsApp.', 'bot');
                }
            });
        })();
    </script>

    <?php
}

/**
 * Guardar conversación básica.
 */
add_action('wp_ajax_tm_chatbot_fase1_log_chat', 'tm_chatbot_fase1_log_chat');
add_action('wp_ajax_nopriv_tm_chatbot_fase1_log_chat', 'tm_chatbot_fase1_log_chat');

function tm_chatbot_fase1_log_chat() {
    check_ajax_referer('tm_chatbot_fase1_nonce', 'nonce');

    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $response = isset($_POST['response']) ? sanitize_textarea_field(wp_unslash($_POST['response'])) : '';

    tm_chatbot_fase1_insert_log([
        'type' => 'chat',
        'message' => $message,
        'response' => $response,
    ]);

    wp_send_json_success();
}

/**
 * Guardar lead.
 */
add_action('wp_ajax_tm_chatbot_fase1_save_lead', 'tm_chatbot_fase1_save_lead');
add_action('wp_ajax_nopriv_tm_chatbot_fase1_save_lead', 'tm_chatbot_fase1_save_lead');

function tm_chatbot_fase1_save_lead() {
    check_ajax_referer('tm_chatbot_fase1_nonce', 'nonce');

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    if (empty($name) || empty($phone) || empty($message)) {
        wp_send_json_error([
            'message' => 'Nombre, teléfono y mensaje son obligatorios.'
        ]);
    }

    tm_chatbot_fase1_insert_log([
        'type' => 'lead',
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'message' => $message,
        'response' => 'Lead registrado desde chatbot fase 1.',
    ]);

    wp_send_json_success();
}

/**
 * Insertar registro.
 */
function tm_chatbot_fase1_insert_log($data) {
    global $wpdb;

    $table = $wpdb->prefix . 'tm_chatbot_fase1_logs';

    $wpdb->insert(
        $table,
        [
            'created_at' => current_time('mysql'),
            'type' => $data['type'] ?? 'chat',
            'name' => $data['name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'message' => $data['message'] ?? '',
            'response' => $data['response'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ],
        [
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        ]
    );
}

/**
 * Menú admin.
 */
add_action('admin_menu', 'tm_chatbot_fase1_admin_menu');

function tm_chatbot_fase1_admin_menu() {
    add_menu_page(
        'TM Chatbot',
        'TM Chatbot',
        'manage_options',
        'tm-chatbot-fase1',
        'tm_chatbot_fase1_admin_page',
        'dashicons-format-chat',
        26
    );
}

function tm_chatbot_fase1_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tm_chatbot_fase1_logs';

    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 200");
    ?>

    <div class="wrap">
        <h1>TM Chatbot - Historial y Leads</h1>

        <p>Últimos 200 registros capturados por el chatbot.</p>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Mensaje</th>
                    <th>Respuesta</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)) : ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row->created_at); ?></td>
                            <td><?php echo esc_html($row->type); ?></td>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->phone); ?></td>
                            <td><?php echo esc_html($row->email); ?></td>
                            <td><?php echo esc_html(wp_trim_words($row->message, 24)); ?></td>
                            <td><?php echo esc_html(wp_trim_words($row->response, 24)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">Todavía no hay registros.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
}
