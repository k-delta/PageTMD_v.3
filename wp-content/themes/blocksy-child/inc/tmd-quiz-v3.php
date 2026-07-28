<?php
defined('ABSPATH') || exit;

/*
 * Quiz Equipo Ideal V3
 * Shortcode: [tm_quiz_equipo_ideal]
 */

if (! function_exists('tmd_register_quiz_equipo_ideal_v3')) {
    function tmd_register_quiz_equipo_ideal_v3() {
        remove_shortcode('tm_quiz_equipo_ideal');
        add_shortcode('tm_quiz_equipo_ideal', 'tmd_quiz_equipo_ideal_v3_shortcode');
    }
}

add_action('init', 'tmd_register_quiz_equipo_ideal_v3', 999);
add_action('wp_loaded', 'tmd_register_quiz_equipo_ideal_v3', 20);

if (! function_exists('tmd_quiz_v3_inventory_items')) {
    function tmd_quiz_v3_inventory_items() {
        if (! function_exists('tmd_inventory_api_items_by_type')
            || ! function_exists('tmd_inventory_api_classification')) {
            return [];
        }

        $items = [];
        foreach (tmd_inventory_api_items_by_type('montacargas') as $item) {
            $spec = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
            $classification = tmd_inventory_api_classification($item);
            $title = tmd_inventory_api_title($item, 'montacargas');
            $id = sanitize_text_field((string) ($item['id'] ?? ''));

            if (! $id || ! $classification['category'] || ! $classification['subcategory']) {
                continue;
            }

            $items[] = [
                'id' => $id,
                'title' => $title,
                'brand' => tmd_inventory_api_text($item['marca'] ?? ''),
                'model' => tmd_inventory_api_text($item['modelo'] ?? ''),
                'image' => esc_url_raw($item['media']['imagenPrincipal'] ?? ''),
                'category' => $classification['category'],
                'subcategory' => $classification['subcategory'],
                'capacity' => is_numeric($spec['capacidad_ton'] ?? null) ? (float) $spec['capacidad_ton'] : 0,
                'liftHeight' => tmd_inventory_api_height_m($spec['alturaLevantamiento_m'] ?? 0),
                'collapsedHeight' => tmd_inventory_api_height_m($spec['alturaMastilContraido_m'] ?? 0),
                'operator' => tmd_inventory_api_text($spec['posicionOperario'] ?? ''),
                'condition' => tmd_inventory_api_text($spec['condicionEspecial'] ?? ''),
                'reach' => tmd_inventory_api_text($spec['tipoReach'] ?? ''),
                'detailUrl' => add_query_arg('ficha', rawurlencode($id), home_url('/equipos/')),
                'quoteUrl' => add_query_arg([
                    'equipo_id' => $id,
                    'equipo' => $title,
                ], home_url('/nosotros/contacto/')),
            ];
        }

        return $items;
    }
}

if (! function_exists('tmd_quiz_v3_email_answers')) {
    function tmd_quiz_v3_email_answers($raw_answers) {
        $answers = is_array($raw_answers) ? $raw_answers : [];
        $applications = [
            'carga_descarga' => 'Carga y descarga',
            'almacenamiento_apilado' => 'Almacenamiento / apilado',
            'transporte' => 'Transporte horizontal',
        ];
        $hours = [
            'hasta_2' => 'Hasta 2 horas',
            '2_a_8' => 'De 2 a 8 horas',
            'mas_8' => 'Más de 8 horas',
        ];

        $application = sanitize_key((string) ($answers['aplicacion'] ?? ''));
        $daily_hours = sanitize_key((string) ($answers['horas'] ?? ''));
        $number = static function ($key, $minimum, $maximum) use ($answers) {
            $value = isset($answers[$key]) ? (int) $answers[$key] : $minimum;
            return max($minimum, min($maximum, $value));
        };

        return [
            'Aplicación' => $applications[$application] ?? 'No especificada',
            'Pasillo' => $number('pasillo', 211, 520) . ' cm',
            'Altura de paso' => number_format_i18n($number('alturaPaso', 100, 300) / 100, 1) . ' m',
            'Carga máxima' => number_format_i18n($number('peso', 200, 5000)) . ' kg',
            'Altura de elevación' => number_format_i18n($number('alturaElevacion', 100, 13000) / 1000, 1) . ' m',
            'Carga en nivel superior' => number_format_i18n($number('pesoAlto', 200, 5000)) . ' kg',
            'Uso diario' => $hours[$daily_hours] ?? 'No especificado',
        ];
    }
}

if (! function_exists('tmd_quiz_v3_send_results_email')) {
    function tmd_quiz_v3_send_results_email($email, $equipment_ids, $answers) {
        $available = [];
        foreach (tmd_inventory_api_items_by_type('montacargas') as $item) {
            $available[(string) ($item['id'] ?? '')] = $item;
        }

        $selected = [];
        foreach (array_slice(array_values(array_unique($equipment_ids)), 0, 3) as $id) {
            if (isset($available[$id])) {
                $selected[] = $available[$id];
            }
        }

        if (count($selected) !== 3) {
            return false;
        }

        $rows = '';
        foreach ($selected as $index => $item) {
            $spec = is_array($item['especificaciones'] ?? null) ? $item['especificaciones'] : [];
            $classification = tmd_inventory_api_classification($item);
            $title = tmd_inventory_api_title($item, 'montacargas');
            $detail_url = add_query_arg('ficha', rawurlencode((string) $item['id']), home_url('/equipos/'));
            $capacity = tmd_inventory_api_number($spec['capacidad_ton'] ?? 0, ' ton');
            $lift = tmd_inventory_api_number(tmd_inventory_api_height_m($spec['alturaLevantamiento_m'] ?? 0), ' m');

            $rows .= '<div style="margin:0 0 18px;padding:18px;border:1px solid #dde6f2;border-radius:12px">';
            $rows .= '<strong style="color:#128ceb">' . esc_html(($index + 1) . 'ª opción') . '</strong>';
            $rows .= '<h2 style="margin:6px 0;color:#262e4f;font-size:20px">' . esc_html($title) . '</h2>';
            $rows .= '<p style="margin:0 0 8px;color:#5e748b">' . esc_html($classification['category'] . ' · ' . $classification['subcategory']) . '</p>';
            if ($capacity || $lift) {
                $rows .= '<p style="margin:0 0 12px;color:#262e4f">' . esc_html(trim($capacity . ($capacity && $lift ? ' · ' : '') . $lift)) . '</p>';
            }
            $rows .= '<a href="' . esc_url($detail_url) . '" style="color:#128ceb;font-weight:700">Ver ficha</a>';
            $rows .= '</div>';
        }

        $criteria = '';
        foreach (tmd_quiz_v3_email_answers($answers) as $label => $value) {
            $criteria .= '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
        }

        $body = '<div style="max-width:680px;margin:auto;font-family:Arial,sans-serif;color:#262e4f">';
        $body .= '<h1 style="font-size:28px">Tus 3 equipos recomendados</h1>';
        $body .= $rows;
        $body .= '<h3>Datos de operación</h3><ul style="line-height:1.7">' . $criteria . '</ul>';
        $body .= '<p style="color:#5e748b">Recomendación preliminar. Un asesor debe validar capacidad residual, medidas y condiciones reales antes de operar.</p>';
        $body .= '<p><a href="' . esc_url(home_url('/nosotros/contacto/')) . '" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#128ceb;color:#fff;text-decoration:none;font-weight:700">Hablar con un asesor</a></p>';
        $body .= '</div>';

        return wp_mail(
            $email,
            'Tus 3 equipos recomendados | Tecnimontacargas',
            $body,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }
}

if (! function_exists('tmd_quiz_v3_ajax_send_results')) {
    function tmd_quiz_v3_ajax_send_results() {
        if (! check_ajax_referer('tmd_quiz_v3_email', 'nonce', false)) {
            wp_send_json_error(['message' => 'La sesión expiró. Recarga la página e intenta nuevamente.'], 403);
        }

        if (! empty($_POST['website'])) {
            wp_send_json_success(['message' => 'Resultados enviados.']);
        }

        $email = sanitize_email(wp_unslash((string) ($_POST['email'] ?? '')));
        if (! is_email($email)) {
            wp_send_json_error(['message' => 'Escribe un correo electrónico válido.'], 400);
        }

        $ids = json_decode(wp_unslash((string) ($_POST['equipment_ids'] ?? '')), true);
        $answers = json_decode(wp_unslash((string) ($_POST['answers'] ?? '')), true);
        if (! is_array($ids) || count($ids) !== 3 || ! is_array($answers)) {
            wp_send_json_error(['message' => 'No fue posible validar la recomendación. Repite el quiz.'], 400);
        }

        $ids = array_map('sanitize_text_field', $ids);
        $available_ids = array_map(static function ($item) {
            return (string) ($item['id'] ?? '');
        }, tmd_inventory_api_items_by_type('montacargas'));
        if (count(array_intersect($ids, $available_ids)) !== 3) {
            wp_send_json_error(['message' => 'No fue posible validar la recomendación. Repite el quiz.'], 400);
        }

        $ip = sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
        $recipient_key = 'tmd_qmail_recipient_' . substr(wp_hash(strtolower($email) . '|' . $ip), 0, 32);
        $ip_key = 'tmd_qmail_ip_' . substr(wp_hash($ip), 0, 32);
        $ip_count = (int) get_transient($ip_key);

        if (get_transient($recipient_key) || $ip_count >= 5) {
            wp_send_json_error(['message' => 'Espera unos minutos antes de solicitar otro envío.'], 429);
        }

        if (! tmd_quiz_v3_send_results_email($email, $ids, $answers)) {
            wp_send_json_error(['message' => 'No fue posible enviar el correo. Intenta nuevamente o contacta un asesor.'], 500);
        }

        set_transient($recipient_key, 1, 2 * MINUTE_IN_SECONDS);
        set_transient($ip_key, $ip_count + 1, HOUR_IN_SECONDS);
        wp_send_json_success(['message' => 'Resultados enviados. Revisa tu bandeja de entrada.']);
    }
}

add_action('wp_ajax_tmd_quiz_v3_send_results', 'tmd_quiz_v3_ajax_send_results');
add_action('wp_ajax_nopriv_tmd_quiz_v3_send_results', 'tmd_quiz_v3_ajax_send_results');

if (! function_exists('tmd_quiz_equipo_ideal_v3_shortcode')) {
    function tmd_quiz_equipo_ideal_v3_shortcode($atts = []) {
        $inventory = tmd_quiz_v3_inventory_items();
        ob_start();
        ?>
        <style>
          .tmd-quiz-v3{
            --blue:#128CEB;
            --navy:#262E4F;
            --yellow:#FFC33C;
            --muted:#5E748B;
            --line:#DDE6F2;
            --soft:#F4F7FB;
            background:#F7F9FC;
            color:var(--navy);
            font-family:Work Sans,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            padding:42px 22px 58px;
          }

          .tmd-quiz-v3 *{box-sizing:border-box}

          .tmd-quiz-v3-shell{
            width:min(1180px,100%);
            margin:auto;
          }

          .tmd-q-progress-head{
            display:flex;
            justify-content:space-between;
            gap:16px;
            margin-bottom:10px;
            font-size:14px;
            font-weight:800;
          }

          .tmd-q-step-label{color:var(--blue)}

          .tmd-q-progress{
            height:10px;
            background:#E3EAF3;
            border-radius:999px;
            overflow:hidden;
            margin-bottom:34px;
          }

          .tmd-q-progress-bar{
            height:100%;
            width:20%;
            background:linear-gradient(90deg,#128CEB,#0F7DD4);
            border-radius:999px;
            transition:.25s ease;
          }

          .tmd-q-layout{
            display:grid;
            grid-template-columns:minmax(0,1.35fr) minmax(300px,.75fr);
            gap:34px;
          }

          .tmd-q-step{display:none}
          .tmd-q-step.is-active{display:block}

          .tmd-q-kicker{
            color:var(--blue);
            font-size:12px;
            font-weight:900;
            letter-spacing:.16em;
            text-transform:uppercase;
            margin-bottom:12px;
          }

          .tmd-q-title{
            margin:0 0 16px;
            color:var(--navy);
            font-size:clamp(34px,4vw,54px);
            line-height:1;
            letter-spacing:-.055em;
            font-weight:900;
          }

          .tmd-q-text{
            margin:0 0 28px;
            color:var(--muted);
            font-size:18px;
            line-height:1.6;
          }

          .tmd-q-options{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:18px;
          }

          .tmd-q-option{
            position:relative;
            cursor:pointer;
          }

          .tmd-q-option input{
            position:absolute;
            opacity:0;
            pointer-events:none;
          }

          .tmd-q-card{
            min-height:162px;
            background:#fff;
            border:1px solid var(--line);
            border-radius:18px;
            padding:24px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            text-align:center;
            gap:12px;
            box-shadow:0 18px 44px rgba(38,46,79,.06);
            transition:.2s ease;
          }

          .tmd-q-option input:checked + .tmd-q-card{
            border-color:var(--blue);
            background:#EEF6FF;
            box-shadow:0 18px 44px rgba(18,140,235,.14);
          }

          .tmd-q-icon{
            width:60px;
            height:60px;
            border-radius:999px;
            display:grid;
            place-items:center;
            background:#F3F7FC;
            color:var(--blue);
            font-size:28px;
            font-weight:900;
          }

          .tmd-q-card strong{
            font-size:18px;
            color:var(--navy);
          }

          .tmd-q-card span:last-child{
            color:#7A879E;
            font-size:14px;
            line-height:1.4;
          }

          .tmd-q-fields{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:22px;
          }

          .tmd-q-field{
            background:#fff;
            border:1px solid var(--line);
            border-radius:18px;
            padding:24px;
            box-shadow:0 18px 44px rgba(38,46,79,.06);
          }

          .tmd-q-field.full{
            grid-column:1/-1;
          }

          .tmd-q-field-top{
            display:flex;
            justify-content:space-between;
            gap:14px;
            align-items:flex-start;
            margin-bottom:22px;
          }

          .tmd-q-field label{
            font-size:18px;
            line-height:1.3;
            font-weight:900;
          }

          .tmd-q-value{
            white-space:nowrap;
            background:#EEF6FF;
            color:var(--blue);
            border:1px solid #CBE7FF;
            border-radius:999px;
            padding:8px 12px;
            font-size:14px;
            font-weight:900;
          }

          .tmd-q-range{
            width:100%;
            accent-color:var(--yellow);
          }

          .tmd-q-scale{
            display:flex;
            justify-content:space-between;
            color:#7A879E;
            font-size:12px;
            font-weight:700;
            margin-top:8px;
          }

          .tmd-q-side{
            min-height:560px;
            border-radius:22px;
            overflow:hidden;
            color:#fff;
            background:
              linear-gradient(180deg,rgba(18,140,235,.16),rgba(38,46,79,.92)),
              radial-gradient(circle at 24% 22%,rgba(255,195,60,.38),transparent 30%),
              linear-gradient(135deg,#128CEB,#262E4F);
            display:flex;
            align-items:flex-end;
            box-shadow:0 24px 70px rgba(38,46,79,.18);
          }

          .tmd-q-side-inner{
            padding:32px;
          }

          .tmd-q-badge{
            display:inline-flex;
            background:var(--yellow);
            color:#18223C;
            border-radius:8px;
            padding:8px 12px;
            font-size:12px;
            font-weight:900;
            letter-spacing:.06em;
            text-transform:uppercase;
            margin-bottom:16px;
          }

          .tmd-q-side h3{
            margin:0 0 12px;
            font-size:28px;
            line-height:1.1;
            color:#fff;
            font-weight:900;
          }

          .tmd-q-side p{
            margin:0;
            color:rgba(255,255,255,.84);
            line-height:1.65;
          }

          .tmd-q-nav{
            display:flex;
            justify-content:space-between;
            gap:16px;
            margin-top:34px;
          }

          .tmd-q-btn{
            min-height:54px;
            border-radius:12px;
            padding:0 24px;
            border:0;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            font-size:16px;
            font-weight:900;
            text-decoration:none;
          }

          .tmd-q-btn.primary{
            background:var(--blue);
            color:#fff;
            box-shadow:0 16px 32px rgba(18,140,235,.24);
          }

          .tmd-q-btn.secondary{
            background:#fff;
            color:var(--navy);
            border:1px solid #CCD7E5;
          }

          .tmd-q-btn:disabled{
            opacity:.45;
            cursor:not-allowed;
          }

          .tmd-q-result{
            display:grid;
            gap:22px;
          }

          .tmd-quiz-v3.is-result .tmd-q-layout{
            grid-template-columns:1fr;
          }

          .tmd-quiz-v3.is-result .tmd-q-side{
            display:none;
          }

          .tmd-q-machines{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            grid-auto-rows:1fr;
            gap:18px;
            align-items:stretch;
          }

          .tmd-q-machine{
            display:grid;
            grid-template-columns:1fr;
            grid-template-rows:248px minmax(0,1fr);
            min-width:0;
            min-height:0;
            height:100%;
            overflow:hidden;
            border:1px solid var(--line);
            border-radius:16px;
            background:#fff;
            box-shadow:0 16px 38px rgba(38,46,79,.07);
          }

          .tmd-q-machine-media{
            position:relative;
            height:248px;
            min-height:0;
            overflow:hidden;
            background:linear-gradient(135deg,#E6F2FC,#FFF2CF);
          }

          .tmd-q-machine-media img{
            display:block;
            width:100%;
            height:100%;
            min-height:0;
            object-fit:cover;
            object-position:center;
          }

          .tmd-q-machine-media.is-missing::after{
            content:"Imagen no disponible";
            position:absolute;
            inset:0;
            display:grid;
            place-items:center;
            color:var(--muted);
            font-size:13px;
            font-weight:800;
          }

          .tmd-q-machine-rank{
            position:absolute;
            z-index:1;
            top:12px;
            left:12px;
            padding:7px 11px;
            border-radius:999px;
            background:var(--yellow);
            color:#18223C;
            font-size:11px;
            font-weight:900;
          }

          .tmd-q-machine-body{
            display:grid;
            grid-template-rows:auto auto 1fr auto;
            gap:12px;
            min-width:0;
            padding:18px;
          }

          .tmd-q-machine-tags{
            display:flex;
            flex-wrap:wrap;
            gap:7px;
          }

          .tmd-q-machine-tags span{
            padding:5px 9px;
            border-radius:999px;
            background:rgba(18,140,235,.1);
            color:var(--blue);
            font-size:10px;
            font-weight:900;
            letter-spacing:.03em;
            text-transform:uppercase;
          }

          .tmd-q-machine-tags span:last-child{
            background:rgba(255,195,60,.18);
            color:#805A00;
          }

          .tmd-q-machine h3{
            margin:0;
            color:var(--navy);
            font-size:21px;
            line-height:1.2;
            font-weight:900;
          }

          .tmd-q-machine-specs{
            display:flex;
            flex-wrap:wrap;
            gap:8px 18px;
            align-content:start;
            color:#5E748B;
            font-size:13px;
            font-weight:700;
          }

          .tmd-q-machine-specs span{
            display:inline-flex;
            align-items:center;
            gap:6px;
          }

          .tmd-q-machine-specs i{color:var(--blue)}

          .tmd-q-machine-actions{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
            align-self:end;
          }

          .tmd-q-machine-actions a{
            min-height:44px;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px solid #C9D7E7;
            border-radius:8px;
            color:var(--navy);
            font-size:13px;
            font-weight:900;
            text-decoration:none;
          }

          .tmd-q-machine-actions a:last-child{
            border-color:var(--yellow);
            background:var(--yellow);
          }

          .tmd-q-email{
            display:grid;
            grid-template-columns:minmax(0,1fr) auto;
            gap:12px;
            align-items:end;
            padding:20px;
            border:1px solid var(--line);
            border-radius:16px;
            background:#fff;
          }

          .tmd-q-email label{
            display:grid;
            gap:8px;
            color:var(--navy);
            font-size:14px;
            font-weight:900;
          }

          .tmd-q-email input[type="email"]{
            width:100%;
            min-height:48px;
            padding:10px 13px;
            border:1px solid #C9D7E7;
            border-radius:9px;
            color:var(--navy);
            font-size:15px;
          }

          .tmd-q-email button{min-height:48px}
          .tmd-q-email-hp{position:absolute!important;left:-9999px!important}
          .tmd-q-email-status{
            grid-column:1/-1;
            min-height:20px;
            color:var(--muted);
            font-size:13px;
            font-weight:700;
          }

          .tmd-q-email-status.is-error{color:#B42318}
          .tmd-q-email-status.is-success{color:#067647}

          .tmd-q-result-actions{
            display:flex;
            justify-content:flex-end;
          }

          @media(max-width:960px){
            .tmd-q-layout{grid-template-columns:1fr}
            .tmd-q-side{min-height:320px}
            .tmd-q-machines{grid-template-columns:repeat(2,minmax(0,1fr))}
          }

          @media(max-width:720px){
            .tmd-quiz-v3{padding:28px 16px 44px}
            .tmd-q-options,
            .tmd-q-fields{grid-template-columns:1fr}
            .tmd-q-title{font-size:34px}
            .tmd-q-machines{grid-template-columns:1fr}
            .tmd-q-machine{
              grid-template-columns:1fr;
              grid-template-rows:220px minmax(0,1fr);
            }
            .tmd-q-machine-media,
            .tmd-q-machine-media img{
              height:220px;
              min-height:0;
              max-height:none;
            }
            .tmd-q-email{grid-template-columns:1fr}
            .tmd-q-nav{flex-direction:column}
            .tmd-q-btn{width:100%}
          }
        </style>

        <div
          class="tmd-quiz-v3"
          data-tmd-qv3
          data-email-endpoint="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
          data-email-nonce="<?php echo esc_attr(wp_create_nonce('tmd_quiz_v3_email')); ?>"
        >
          <div class="tmd-quiz-v3-shell">
            <div class="tmd-q-progress-head">
              <span>Progreso del Quiz</span>
              <span class="tmd-q-step-label" data-step-label>Paso 1 de 5</span>
            </div>

            <div class="tmd-q-progress">
              <div class="tmd-q-progress-bar" data-progress></div>
            </div>

            <div class="tmd-q-layout">
              <main>
                <section class="tmd-q-step is-active" data-step="1">
                  <div class="tmd-q-kicker">Aplicación principal</div>
                  <h2 class="tmd-q-title">¿Cuál será la aplicación principal?</h2>
                  <p class="tmd-q-text">Selecciona la operación principal para orientar la recomendación inicial.</p>

                  <div class="tmd-q-options">
                    <label class="tmd-q-option">
                      <input type="radio" name="aplicacion" value="carga_descarga" checked>
                      <span class="tmd-q-card">
                        <span class="tmd-q-icon">↕</span>
                        <strong>Carga y descarga</strong>
                        <span>Operación en muelle, camión o patio.</span>
                      </span>
                    </label>

                    <label class="tmd-q-option">
                      <input type="radio" name="aplicacion" value="almacenamiento_apilado">
                      <span class="tmd-q-card">
                        <span class="tmd-q-icon">▤</span>
                        <strong>Almacenamiento/Apilado</strong>
                        <span>Estantería, racks y operación en altura.</span>
                      </span>
                    </label>

                    <label class="tmd-q-option">
                      <input type="radio" name="aplicacion" value="transporte">
                      <span class="tmd-q-card">
                        <span class="tmd-q-icon">→</span>
                        <strong>Transporte</strong>
                        <span>Movimiento horizontal de mercancía.</span>
                      </span>
                    </label>
                  </div>
                </section>

                <section class="tmd-q-step" data-step="2">
                  <div class="tmd-q-kicker">Restricciones del espacio</div>
                  <h2 class="tmd-q-title">Dimensiones del pasillo y altura de paso</h2>
                  <p class="tmd-q-text">Estos datos ayudan a descartar equipos que no puedan maniobrar o pasar por zonas bajas.</p>

                  <div class="tmd-q-fields">
                    <div class="tmd-q-field">
                      <div class="tmd-q-field-top">
                        <label for="q-pasillo">¿Qué dimensiones tiene su pasillo de trabajo?</label>
                        <span class="tmd-q-value" data-value="pasillo">260 cm</span>
                      </div>
                      <input id="q-pasillo" class="tmd-q-range" type="range" min="211" max="520" step="1" value="260" data-range="pasillo">
                      <div class="tmd-q-scale"><span>211 cm</span><span>520 cm</span></div>
                    </div>

                    <div class="tmd-q-field">
                      <div class="tmd-q-field-top">
                        <label for="q-altura-paso">La altura de paso mínima es</label>
                        <span class="tmd-q-value" data-value="alturaPaso">2 m</span>
                      </div>
                      <input id="q-altura-paso" class="tmd-q-range" type="range" min="100" max="300" step="10" value="200" data-range="alturaPaso">
                      <div class="tmd-q-scale"><span>1 m</span><span>3 m</span></div>
                    </div>
                  </div>
                </section>

                <section class="tmd-q-step" data-step="3">
                  <div class="tmd-q-kicker">Propiedades de la carga</div>
                  <h2 class="tmd-q-title">Peso y altura de elevación</h2>
                  <p class="tmd-q-text">La carga máxima y la altura de elevación son claves para definir capacidad, mástil y estabilidad.</p>

                  <div class="tmd-q-fields">
                    <div class="tmd-q-field">
                      <div class="tmd-q-field-top">
                        <label for="q-peso">¿Cuál es el peso máximo de sus cargas a elevar respecto al suelo?</label>
                        <span class="tmd-q-value" data-value="peso">1200 kg</span>
                      </div>
                      <input id="q-peso" class="tmd-q-range" type="range" min="200" max="5000" step="100" value="1200" data-range="peso">
                      <div class="tmd-q-scale"><span>200 kg</span><span>5000 kg</span></div>
                    </div>

                    <div class="tmd-q-field">
                      <div class="tmd-q-field-top">
                        <label for="q-altura">¿Hasta qué altura hay que levantar la carga?</label>
                        <span class="tmd-q-value" data-value="alturaElevacion">3 m</span>
                      </div>
                      <input id="q-altura" class="tmd-q-range" type="range" min="100" max="13000" step="100" value="3000" data-range="alturaElevacion">
                      <div class="tmd-q-scale"><span>0.1 m</span><span>13 m</span></div>
                    </div>
                  </div>
                </section>

                <section class="tmd-q-step" data-step="4">
                  <div class="tmd-q-kicker">Peso en estantería</div>
                  <h2 class="tmd-q-title">¿Cuál es el peso máximo en el nivel más alto de la estantería?</h2>
                  <p class="tmd-q-text">Este dato permite evaluar capacidad residual en altura.</p>

                  <div class="tmd-q-fields">
                    <div class="tmd-q-field full">
                      <div class="tmd-q-field-top">
                        <label for="q-peso-alto">Peso máximo en el nivel más alto</label>
                        <span class="tmd-q-value" data-value="pesoAlto">1000 kg</span>
                      </div>
                      <input id="q-peso-alto" class="tmd-q-range" type="range" min="200" max="5000" step="100" value="1000" data-range="pesoAlto">
                      <div class="tmd-q-scale"><span>200 kg</span><span>5000 kg</span></div>
                    </div>
                  </div>
                </section>

                <section class="tmd-q-step" data-step="5">
                  <div class="tmd-q-kicker">Intensidad de uso</div>
                  <h2 class="tmd-q-title">¿Cuántas horas al día conduces la carretilla?</h2>
                  <p class="tmd-q-text">La intensidad de uso afecta autonomía, batería, robustez y tipo de equipo.</p>

                  <div class="tmd-q-options">
                    <label class="tmd-q-option">
                      <input type="radio" name="horas" value="hasta_2" checked>
                      <span class="tmd-q-card">
                        <span class="tmd-q-icon">1</span>
                        <strong>Hasta 2 horas</strong>
                        <span>Uso ocasional o baja intensidad.</span>
                      </span>
                    </label>

                    <label class="tmd-q-option">
                      <input type="radio" name="horas" value="2_a_8">
                      <span class="tmd-q-card">
                        <span class="tmd-q-icon">2</span>
                        <strong>De 2 a 8 horas</strong>
                        <span>Operación estándar de jornada.</span>
                      </span>
                    </label>

                    <label class="tmd-q-option">
                      <input type="radio" name="horas" value="mas_8">
                      <span class="tmd-q-card">
                        <span class="tmd-q-icon">3</span>
                        <strong>Más de 8 horas</strong>
                        <span>Uso intensivo o turnos prolongados.</span>
                      </span>
                    </label>
                  </div>
                </section>

                <section class="tmd-q-step" data-step="6">
                  <div class="tmd-q-kicker">Resultado</div>
                  <h2 class="tmd-q-title">Encontramos 3 equipos ideales para tu operación</h2>

                  <div class="tmd-q-result">
                    <div class="tmd-q-machines" data-result-machines aria-live="polite"></div>

                    <form class="tmd-q-email" data-result-email-form>
                      <label>
                        Enviar estas recomendaciones por correo
                        <input type="email" name="email" autocomplete="email" placeholder="tu@email.com" required>
                      </label>
                      <label class="tmd-q-email-hp" aria-hidden="true">
                        Sitio web
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                      </label>
                      <button class="tmd-q-btn primary" type="submit">Enviar resultados</button>
                      <div class="tmd-q-email-status" data-email-status role="status" aria-live="polite"></div>
                    </form>

                    <div class="tmd-q-result-actions">
                      <button class="tmd-q-btn secondary" type="button" data-restart>Repetir quiz</button>
                    </div>
                  </div>
                </section>

                <div class="tmd-q-nav">
                  <button class="tmd-q-btn secondary" type="button" data-prev>← Anterior</button>
                  <button class="tmd-q-btn primary" type="button" data-next>Siguiente →</button>
                </div>
              </main>

              <aside class="tmd-q-side">
                <div class="tmd-q-side-inner">
                  <span class="tmd-q-badge">Contexto de operación</span>
                  <h3 data-side-title>Encuentra el equipo ideal</h3>
                  <p data-side-text>Responde estas preguntas para orientar la recomendación hacia el tipo de equipo más compatible.</p>
                </div>
              </aside>
            </div>

            <script type="application/json" data-tmd-quiz-inventory><?php
              echo wp_json_encode($inventory, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            ?></script>
          </div>
        </div>

        <script>
        (function(){
          const root = document.querySelector('[data-tmd-qv3]');
          if(!root || root.dataset.ready === '1') return;
          root.dataset.ready = '1';

          let step = 1;
          const total = 5;

          const inventoryNode = root.querySelector('[data-tmd-quiz-inventory]');
          let inventory = [];
          try {
            inventory = JSON.parse(inventoryNode ? inventoryNode.textContent : '[]');
          } catch(error) {
            inventory = [];
          }

          let recommendations = [];

          const side = {
            1:['Aplicación principal','Define la familia base del equipo según la operación.'],
            2:['Restricciones del espacio','El ancho del pasillo y la altura de paso definen qué equipos pueden operar físicamente.'],
            3:['Propiedades de la carga','El peso y la altura de elevación definen capacidad, mástil y estabilidad.'],
            4:['Capacidad residual','El peso en el nivel más alto exige revisar capacidad residual.'],
            5:['Intensidad de uso','Las horas de trabajo influyen en autonomía, batería y robustez.'],
            6:['Resultado técnico inicial','La recomendación final debe validarse con un asesor.']
          };

          function fmt(num){return Number(num).toLocaleString('es-CO');}

          function val(key){
            const el = root.querySelector('[data-range="'+key+'"]');
            return el ? Number(el.value) : 0;
          }

          function radio(name){
            const el = root.querySelector('input[name="'+name+'"]:checked');
            return el ? el.value : '';
          }

          function updateRanges(){
            const map = {
              pasillo: val('pasillo') + ' cm',
              alturaPaso: (val('alturaPaso') / 100).toFixed(1).replace('.0','') + ' m',
              peso: fmt(val('peso')) + ' kg',
              alturaElevacion: (val('alturaElevacion') / 1000).toFixed(1).replace('.0','') + ' m',
              pesoAlto: fmt(val('pesoAlto')) + ' kg'
            };

            Object.keys(map).forEach(k => {
              const out = root.querySelector('[data-value="'+k+'"]');
              if(out) out.textContent = map[k];
            });
          }

          function answers(){
            return {
              aplicacion:radio('aplicacion'),
              horas:radio('horas'),
              pasillo:val('pasillo'),
              alturaPaso:val('alturaPaso'),
              peso:val('peso'),
              alturaElevacion:val('alturaElevacion'),
              pesoAlto:val('pesoAlto')
            };
          }

          function scoreEquipment(item, a){
            let score = 0;
            const category = item.category || '';
            const requiredCapacity = Math.max(a.peso, a.pesoAlto) / 1000;
            const capacity = Number(item.capacity) || 0;
            const requiredLift = a.alturaElevacion / 1000;
            const lift = Number(item.liftHeight) || 0;
            const clearance = a.alturaPaso / 100;
            const collapsed = Number(item.collapsedHeight) || 0;

            if(capacity > 0){
              const gap = capacity - requiredCapacity;
              score += gap >= 0 ? 50 - Math.min(gap * 6, 18) : -140 - Math.abs(gap) * 50;
            } else {
              score -= 80;
            }

            if(lift > 0){
              const gap = lift - requiredLift;
              score += gap >= 0 ? 45 - Math.min(gap * 2.5, 20) : -120 - Math.abs(gap) * 15;
            } else {
              score -= 80;
            }

            if(collapsed > 0){
              const gap = clearance - collapsed;
              score += gap >= 0 ? 25 - Math.min(gap * 3, 10) : -90 - Math.abs(gap) * 35;
            }

            const applicationWeights = {
              carga_descarga:{Contrabalanceados:55,Apiladores:15,Reach:5,'Retráctiles':5},
              almacenamiento_apilado:{'Retráctiles':55,Reach:52,Apiladores:38,Tomapedidos:15},
              transporte:{Estibadores:55,Apiladores:32,Contrabalanceados:12}
            };
            score += applicationWeights[a.aplicacion]?.[category] || 0;

            if(a.pasillo <= 260){
              score += ({Reach:35,'Retráctiles':30,Tomapedidos:18,Apiladores:10,Contrabalanceados:-35})[category] || 0;
            } else if(a.pasillo <= 320){
              score += ({'Retráctiles':35,Reach:28,Apiladores:15,Contrabalanceados:-15})[category] || 0;
            } else if(a.pasillo <= 400){
              score += ({Apiladores:20,Estibadores:15,'Retráctiles':12,Contrabalanceados:5})[category] || 0;
            } else {
              score += ({Contrabalanceados:30,Estibadores:18,Apiladores:10})[category] || 0;
            }

            if(requiredLift <= .5 && category === 'Estibadores') score += 24;
            if(requiredLift > .5 && requiredLift <= 3 && category === 'Apiladores') score += 16;
            if(requiredLift > 6 && ['Reach','Retráctiles'].includes(category)) score += 20;
            if(a.horas === 'mas_8' && ['Contrabalanceados','Reach','Retráctiles'].includes(category)) score += 10;
            if(a.horas === 'hasta_2' && category === 'Estibadores') score += 8;

            return score;
          }

          function calculate(){
            const a = answers();
            const ranked = inventory.map(item => ({
              ...item,
              score:scoreEquipment(item,a)
            })).sort((left,right) => right.score - left.score || String(left.title).localeCompare(String(right.title),'es'));

            const selected = [];
            const models = new Set();
            ranked.forEach(item => {
              const modelKey = (item.brand + '|' + item.model).toLocaleLowerCase('es');
              if(selected.length < 3 && !models.has(modelKey)){
                models.add(modelKey);
                selected.push(item);
              }
            });
            ranked.forEach(item => {
              if(selected.length < 3 && !selected.some(selectedItem => selectedItem.id === item.id)){
                selected.push(item);
              }
            });
            return selected.slice(0,3);
          }

          function escapeHtml(value){
            return String(value ?? '').replace(/[&<>"']/g, char => ({
              '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
            })[char]);
          }

          function formatNumber(value){
            return Number(value).toLocaleString('es-CO',{maximumFractionDigits:2});
          }

          function renderResult(){
            recommendations = calculate();
            const container = root.querySelector('[data-result-machines]');
            const rankLabels = ['1ª recomendación','2ª opción','3ª opción'];

            container.innerHTML = recommendations.map((item,index) => {
              const image = item.image
                ? '<img src="'+escapeHtml(item.image)+'" alt="'+escapeHtml(item.title)+'" loading="lazy">'
                : '';
              const specs = [
                Number(item.capacity) > 0 ? '<span><i class="ti ti-weight"></i>'+formatNumber(item.capacity)+' ton</span>' : '',
                Number(item.liftHeight) > 0 ? '<span><i class="ti ti-arrow-up"></i>'+formatNumber(item.liftHeight)+' m</span>' : '',
                Number(item.collapsedHeight) > 0 ? '<span><i class="ti ti-arrows-minimize"></i>'+formatNumber(item.collapsedHeight)+' m</span>' : ''
              ].filter(Boolean).join('');

              return '<article class="tmd-q-machine" data-equipment-id="'+escapeHtml(item.id)+'">'
                +'<div class="tmd-q-machine-media'+(image ? '' : ' is-missing')+'">'
                +'<span class="tmd-q-machine-rank">'+rankLabels[index]+'</span>'+image+'</div>'
                +'<div class="tmd-q-machine-body">'
                +'<div class="tmd-q-machine-tags"><span>'+escapeHtml(item.category)+'</span><span>'+escapeHtml(item.subcategory)+'</span></div>'
                +'<h3>'+escapeHtml(item.title)+'</h3>'
                +'<div class="tmd-q-machine-specs">'+specs+'</div>'
                +'<div class="tmd-q-machine-actions"><a href="'+escapeHtml(item.detailUrl)+'">Ver ficha</a><a href="'+escapeHtml(item.quoteUrl)+'">Cotizar</a></div>'
                +'</div></article>';
            }).join('');

            container.querySelectorAll('img').forEach(image => {
              image.addEventListener('error', () => {
                image.parentElement.classList.add('is-missing');
                image.remove();
              }, {once:true});
            });

            const status = root.querySelector('[data-email-status]');
            status.textContent = '';
            status.className = 'tmd-q-email-status';
          }

          function show(n){
            step = Math.max(1, Math.min(6, n));
            root.classList.toggle('is-result', step === 6);

            root.querySelectorAll('[data-step]').forEach(el => {
              el.classList.toggle('is-active', Number(el.dataset.step) === step);
            });

            root.querySelector('[data-step-label]').textContent = step <= total ? 'Paso ' + step + ' de ' + total : 'Resultado';
            root.querySelector('[data-progress]').style.width = step <= total ? ((step / total) * 100) + '%' : '100%';

            root.querySelector('[data-prev]').disabled = step === 1;
            root.querySelector('[data-next]').style.display = step === 6 ? 'none' : 'inline-flex';
            root.querySelector('.tmd-q-nav').style.display = step === 6 ? 'none' : 'flex';

            const sc = side[step] || side[1];
            root.querySelector('[data-side-title]').textContent = sc[0];
            root.querySelector('[data-side-text]').textContent = sc[1];

            if(step === 6) renderResult();

            root.scrollIntoView({behavior:'smooth',block:'start'});
          }

          root.querySelectorAll('[data-range]').forEach(el => {
            el.addEventListener('input', updateRanges);
          });

          root.querySelector('[data-next]').addEventListener('click', () => show(step + 1));
          root.querySelector('[data-prev]').addEventListener('click', () => show(step - 1));
          root.querySelector('[data-restart]').addEventListener('click', () => {
            recommendations = [];
            root.querySelector('[data-result-email-form]').reset();
            show(1);
          });

          root.querySelector('[data-result-email-form]').addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const button = form.querySelector('button[type="submit"]');
            const status = root.querySelector('[data-email-status]');
            const email = form.elements.email.value.trim();

            if(recommendations.length !== 3){
              status.textContent = 'No fue posible validar tres equipos. Repite el quiz.';
              status.className = 'tmd-q-email-status is-error';
              return;
            }

            button.disabled = true;
            status.textContent = 'Enviando…';
            status.className = 'tmd-q-email-status';

            const body = new URLSearchParams();
            body.set('action','tmd_quiz_v3_send_results');
            body.set('nonce',root.dataset.emailNonce || '');
            body.set('email',email);
            body.set('website',form.elements.website.value || '');
            body.set('equipment_ids',JSON.stringify(recommendations.map(item => item.id)));
            body.set('answers',JSON.stringify(answers()));

            try {
              const response = await fetch(root.dataset.emailEndpoint,{
                method:'POST',
                credentials:'same-origin',
                headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
                body:body.toString()
              });
              const payload = await response.json();
              if(!response.ok || !payload.success){
                throw new Error(payload?.data?.message || 'No fue posible enviar el correo.');
              }
              status.textContent = payload.data.message;
              status.className = 'tmd-q-email-status is-success';
            } catch(error) {
              status.textContent = error.message || 'No fue posible enviar el correo.';
              status.className = 'tmd-q-email-status is-error';
            } finally {
              button.disabled = false;
            }
          });

          updateRanges();
          show(1);
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}
