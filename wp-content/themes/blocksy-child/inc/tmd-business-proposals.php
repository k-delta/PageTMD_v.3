<?php
/**
 * Formularios de propuestas empresariales para Alianzas y Proveedores.
 */

defined('ABSPATH') || exit;

function tmd_business_proposals_recipient_for($form_type) {
    return in_array($form_type, ['alianza', 'proveedor'], true) ? 'gerencia@gmail.com' : '';
}

function tmd_business_proposals_max_bytes() {
    return 2621440; // 2.5 MB por archivo y por envío.
}

function tmd_business_proposals_validate_fields($source, $forced_form_type = '') {
    $form_type = '' !== $forced_form_type
        ? sanitize_key($forced_form_type)
        : sanitize_key(wp_unslash((string) ($source['form_type'] ?? '')));
    $email_raw = wp_unslash((string) ($source['email'] ?? ''));
    $email     = sanitize_email($email_raw);
    $fields    = [
        'form_type' => $form_type,
        'recipient' => tmd_business_proposals_recipient_for($form_type),
        'company'   => sanitize_text_field(wp_unslash((string) ($source['company'] ?? ''))),
        'tax_id'    => sanitize_text_field(wp_unslash((string) ($source['tax_id'] ?? ''))),
        'name'      => sanitize_text_field(wp_unslash((string) ($source['name'] ?? ''))),
        'role'      => sanitize_text_field(wp_unslash((string) ($source['role'] ?? ''))),
        'email'     => $email,
        'phone'     => sanitize_text_field(wp_unslash((string) ($source['phone'] ?? ''))),
        'city'      => sanitize_text_field(wp_unslash((string) ($source['city'] ?? ''))),
        'coverage'  => sanitize_text_field(wp_unslash((string) ($source['coverage'] ?? ''))),
        'website'   => esc_url_raw(wp_unslash((string) ($source['company_website'] ?? ''))),
        'message'   => sanitize_textarea_field(wp_unslash((string) ($source['message'] ?? ''))),
        'terms'     => sanitize_text_field(wp_unslash((string) ($source['terms'] ?? ''))),
    ];

    if ('' === $fields['recipient']) {
        return new WP_Error('invalid_form_type', 'El tipo de solicitud no es válido.');
    }
    if ('' === $email_raw || $email !== $email_raw || ! is_email($email) || preg_match('/[\r\n]/', $email_raw)) {
        return new WP_Error('invalid_email', 'Escribe un correo electrónico válido.');
    }

    foreach (['company', 'name', 'phone', 'city', 'coverage', 'message'] as $required) {
        if ('' === $fields[$required]) {
            return new WP_Error('missing_field', 'Completa todos los campos obligatorios.');
        }
    }
    if ('Acepto' !== $fields['terms']) {
        return new WP_Error('terms_required', 'Debes aceptar la política de privacidad.');
    }

    $limits = [
        'company' => 160, 'tax_id' => 40, 'name' => 120, 'role' => 120,
        'email' => 190, 'phone' => 40, 'city' => 100, 'coverage' => 240,
        'website' => 300, 'message' => 4000,
    ];
    foreach ($limits as $key => $limit) {
        if (mb_strlen($fields[$key]) > $limit) {
            return new WP_Error('field_too_long', 'Uno de los campos supera la longitud permitida.');
        }
    }

    return $fields;
}

function tmd_business_proposals_normalize_files($files) {
    if (! is_array($files) || ! isset($files['name'])) {
        return new WP_Error('missing_attachments', 'Adjunta entre uno y tres archivos.');
    }

    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $keys  = ['tmp_name', 'size', 'error', 'type'];
    $items = [];
    foreach ($names as $index => $name) {
        if ('' === (string) $name) {
            continue;
        }
        $item = ['name' => $name];
        foreach ($keys as $key) {
            $values     = is_array($files[$key] ?? null) ? $files[$key] : [$files[$key] ?? null];
            $item[$key] = $values[$index] ?? null;
        }
        $items[] = $item;
    }

    if (count($items) < 1 || count($items) > 3) {
        return new WP_Error('invalid_attachment_count', 'Adjunta entre uno y tres archivos.');
    }

    return $items;
}

function tmd_business_proposals_detect_mime($path) {
    $fileinfo = new finfo(FILEINFO_MIME_TYPE);
    return (string) $fileinfo->file($path);
}

if (! function_exists('tmd_business_proposals_is_uploaded_file')) {
    function tmd_business_proposals_is_uploaded_file($path) {
        return is_uploaded_file($path);
    }
}

if (! function_exists('tmd_business_proposals_move_uploaded_file')) {
    function tmd_business_proposals_move_uploaded_file($source, $destination) {
        return move_uploaded_file($source, $destination);
    }
}

function tmd_business_proposals_valid_structure($path, $extension, $mime) {
    if ('pdf' === $extension) {
        $content = @file_get_contents($path);
        if (! is_string($content) || '%PDF-' !== substr($content, 0, 5) || false === strpos(substr($content, -1024), '%%EOF')) {
            return false;
        }
        $decoded = preg_replace_callback('/#[0-9A-Fa-f]{2}/', static fn($match) => chr(hexdec(substr($match[0], 1))), $content);
        foreach (['/JavaScript', '/JS', '/OpenAction', '/Launch', '/EmbeddedFile', '/Filespec', '/RichMedia', '/AA', '/ObjStm', '/XFA', '/AcroForm', '/SubmitForm', '/ImportData'] as $marker) {
            if (false !== stripos($decoded, $marker)) {
                return false;
            }
        }
        return true;
    }

    if ('docx' === $extension && class_exists('ZipArchive')) {
        $archive = new ZipArchive();
        if (true !== $archive->open($path)) {
            return false;
        }
        $valid = $archive->numFiles > 0 && $archive->numFiles <= 200;
        $names = [];
        $total = 0;
        for ($index = 0; $valid && $index < $archive->numFiles; ++$index) {
            $stat = $archive->statIndex($index);
            $name = strtolower(str_replace('\\', '/', (string) ($stat['name'] ?? '')));
            $size = (int) ($stat['size'] ?? 0);
            $compressed_size = (int) ($stat['comp_size'] ?? 0);
            $total += $size;
            if (! is_array($stat) || isset($names[$name]) || $total > 10 * 1024 * 1024
                || ($size > 1024 * 1024 && $compressed_size > 0 && $size / $compressed_size > 100)
                || false !== strpos('/' . $name, '/../') || preg_match('/(?:vbaproject|activex|embeddings|customui)/i', $name)) {
                $valid = false;
            }
            $names[$name] = true;
        }
        $types = $valid ? $archive->getFromName('[Content_Types].xml') : false;
        $valid = $valid && isset($names['word/document.xml']) && is_string($types)
            && false !== strpos($types, 'wordprocessingml.document.main+xml')
            && ! preg_match('/(?:macroEnabled|activeX|vbaProject)/i', $types);
        for ($index = 0; $valid && $index < $archive->numFiles; ++$index) {
            $stat = $archive->statIndex($index);
            $name = strtolower(str_replace('\\', '/', (string) ($stat['name'] ?? '')));
            if ('.rels' !== substr($name, -5)) {
                continue;
            }
            $relationships = $archive->getFromIndex($index);
            $decoded = is_string($relationships) ? html_entity_decode($relationships, ENT_QUOTES | ENT_XML1, 'UTF-8') : '';
            if ((int) ($stat['size'] ?? 0) > 64 * 1024
                || preg_match('/(?:oleObject|attachedTemplate|\/package|vbaProject|embeddings\/|activeX\/)/i', $decoded)
                || preg_match('/TargetMode\s*=\s*["\']External["\']/i', $decoded)
                || preg_match('/Target\s*=\s*["\']\s*(?:https?|ftp|file):/i', $decoded)) {
                $valid = false;
            }
        }
        $archive->close();
        return $valid;
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $info = @getimagesize($path);
        return is_array($info) && ($info[0] ?? 0) > 0 && ($info[1] ?? 0) > 0
            && strtolower((string) ($info['mime'] ?? '')) === $mime
            && tmd_business_proposals_image_has_exact_end($path, $extension);
    }

    return false;
}

function tmd_business_proposals_image_has_exact_end($path, $extension) {
    $content = @file_get_contents($path);
    if (! is_string($content) || '' === $content) {
        return false;
    }
    if (in_array($extension, ['jpg', 'jpeg'], true)) {
        return "\xFF\xD9" === substr($content, -2);
    }
    if ('png' === $extension) {
        return "\x00\x00\x00\x00IEND\xAE\x42\x60\x82" === substr($content, -12);
    }
    if ('webp' === $extension && strlen($content) >= 12 && 'RIFF' === substr($content, 0, 4) && 'WEBP' === substr($content, 8, 4)) {
        $size = unpack('Vsize', substr($content, 4, 4));
        return isset($size['size']) && (int) $size['size'] + 8 === strlen($content);
    }
    return false;
}

function tmd_business_proposals_validate_attachments($files, $upload_checker = null, $mime_detector = null, $structure_checker = null) {
    $items = tmd_business_proposals_normalize_files($files);
    if (is_wp_error($items)) {
        return $items;
    }

    $upload_checker   = is_callable($upload_checker) ? $upload_checker : 'tmd_business_proposals_is_uploaded_file';
    $mime_detector    = is_callable($mime_detector) ? $mime_detector : 'tmd_business_proposals_detect_mime';
    $structure_checker = is_callable($structure_checker) ? $structure_checker : 'tmd_business_proposals_valid_structure';
    $allowed_mimes = [
        'pdf' => ['application/pdf', 'application/x-pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-zip-compressed'],
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
    ];
    $validated = [];
    $total     = 0;
    foreach ($items as $item) {
        $name      = sanitize_file_name((string) $item['name']);
        $tmp_name  = (string) $item['tmp_name'];
        $size      = (int) $item['size'];
        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        if (UPLOAD_ERR_OK !== (int) $item['error'] || '' === $name || '' === $tmp_name || $size < 1) {
            return new WP_Error('upload_error', 'No fue posible recibir uno de los archivos.');
        }
        $total += $size;
        if ($size > tmd_business_proposals_max_bytes() || $total > tmd_business_proposals_max_bytes()) {
            return new WP_Error('attachments_too_large', 'Cada archivo y la suma total deben pesar máximo 2.5 MB.');
        }
        if (! isset($allowed_mimes[$extension]) || ! call_user_func($upload_checker, $tmp_name)) {
            return new WP_Error('invalid_attachment', 'Adjunta únicamente PDF, DOCX, JPG, PNG o WEBP válidos.');
        }
        $actual_size = @filesize($tmp_name);
        $mime        = strtolower((string) call_user_func($mime_detector, $tmp_name));
        if (false === $actual_size || (int) $actual_size !== $size || ! in_array($mime, $allowed_mimes[$extension], true)
            || ! call_user_func($structure_checker, $tmp_name, $extension, $mime)) {
            return new WP_Error('invalid_attachment_content', 'El contenido de uno de los archivos no es válido.');
        }
        $validated[] = compact('name', 'tmp_name', 'size', 'extension', 'mime');
    }
    return $validated;
}

function tmd_business_proposals_stage_attachments($attachments) {
    $paths = [];
    foreach ($attachments as $attachment) {
        $destination = trailingslashit(get_temp_dir()) . wp_unique_filename(get_temp_dir(), $attachment['name']);
        if (! tmd_business_proposals_move_uploaded_file($attachment['tmp_name'], $destination)) {
            foreach ($paths as $path) {
                wp_delete_file($path);
            }
            return new WP_Error('stage_failed', 'No fue posible preparar los archivos adjuntos.');
        }
        $paths[] = $destination;
    }
    return $paths;
}

function tmd_business_proposals_build_mail($fields, $attachment_paths) {
    $labels  = ['alianza' => 'Propuesta de alianza', 'proveedor' => 'Solicitud de proveedor'];
    $subject = $labels[$fields['form_type']] . ' | ' . $fields['company'];
    $rows    = [
        'Tipo' => $labels[$fields['form_type']], 'Empresa' => $fields['company'],
        'NIT' => $fields['tax_id'] ?: 'No informado', 'Contacto' => $fields['name'],
        'Cargo' => $fields['role'] ?: 'No informado', 'Email' => $fields['email'],
        'Teléfono' => $fields['phone'], 'Ciudad' => $fields['city'],
        'Cobertura' => $fields['coverage'], 'Sitio web' => $fields['website'] ?: 'No informado',
        'Propuesta o portafolio' => nl2br(esc_html($fields['message'])),
    ];
    $message = '<div style="font-family:Arial,sans-serif;color:#262e4f;line-height:1.6"><h1>' . esc_html($labels[$fields['form_type']]) . '</h1><table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%">';
    foreach ($rows as $label => $value) {
        $safe = 'Propuesta o portafolio' === $label ? $value : esc_html($value);
        $message .= '<tr><th align="left" style="border-bottom:1px solid #dce3ec;width:180px">' . esc_html($label) . '</th><td style="border-bottom:1px solid #dce3ec">' . $safe . '</td></tr>';
    }
    $message .= '</table><p>La documentación empresarial se encuentra adjunta.</p></div>';
    return [
        'to' => $fields['recipient'], 'subject' => $subject, 'message' => $message,
        'headers' => ['Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $fields['email']],
        'attachments' => $attachment_paths,
    ];
}

function tmd_business_proposals_rate_limited($ip) {
    $lock = @fopen(trailingslashit(get_temp_dir()) . 'tmd-business-proposals-rate.lock', 'c');
    if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
        is_resource($lock) && fclose($lock);
        return true;
    }
    try {
        $keys = [
            ['key' => 'tmd_business_ip_' . substr(wp_hash($ip), 0, 32), 'limit' => 5],
        ];
        $counts = [];
        foreach ($keys as $index => $entry) {
            $counts[$index] = (int) get_transient($entry['key']);
            if ($counts[$index] >= $entry['limit']) {
                return true;
            }
        }
        foreach ($keys as $index => $entry) {
            set_transient($entry['key'], $counts[$index] + 1, HOUR_IN_SECONDS);
        }
        return false;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function tmd_business_proposals_ajax($form_type) {
    if (! check_ajax_referer('tmd_business_proposals', 'nonce', false)) {
        wp_send_json_error(['message' => 'La sesión expiró. Recarga la página e inténtalo nuevamente.'], 403);
    }
    if ((function_exists('tmd_form_antispam_should_block') && tmd_form_antispam_should_block()) || ! empty($_POST['website'])) {
        wp_send_json_success(['message' => 'Solicitud procesada correctamente.']);
    }
    $fields = tmd_business_proposals_validate_fields($_POST, $form_type);
    if (is_wp_error($fields)) {
        wp_send_json_error(['message' => $fields->get_error_message()], 'invalid_form_type' === $fields->get_error_code() ? 403 : 400);
    }
    $ip = sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
    if (tmd_business_proposals_rate_limited($ip)) {
        wp_send_json_error(['message' => 'Has realizado varios intentos. Espera un momento antes de volver a enviar.'], 429);
    }
    $attachments = tmd_business_proposals_validate_attachments($_FILES['attachments'] ?? []);
    if (is_wp_error($attachments)) {
        wp_send_json_error(['message' => $attachments->get_error_message()], 400);
    }
    $paths = tmd_business_proposals_stage_attachments($attachments);
    if (is_wp_error($paths)) {
        wp_send_json_error(['message' => $paths->get_error_message()], 500);
    }
    try {
        $mail = tmd_business_proposals_build_mail($fields, $paths);
        $sent = wp_mail($mail['to'], $mail['subject'], $mail['message'], $mail['headers'], $mail['attachments']);
    } catch (Throwable $error) {
        $sent = false;
    } finally {
        foreach ($paths as $path) {
            file_exists($path) && wp_delete_file($path);
        }
    }
    if (! $sent) {
        wp_send_json_error(['message' => 'No fue posible procesar la solicitud. Inténtalo nuevamente más tarde.'], 502);
    }
    wp_send_json_success(['message' => 'Solicitud procesada correctamente.']);
}

function tmd_business_alliance_ajax() {
    tmd_business_proposals_ajax('alianza');
}

function tmd_business_provider_ajax() {
    tmd_business_proposals_ajax('proveedor');
}

add_action('wp_ajax_tmd_business_alliance', 'tmd_business_alliance_ajax');
add_action('wp_ajax_nopriv_tmd_business_alliance', 'tmd_business_alliance_ajax');
add_action('wp_ajax_tmd_business_provider', 'tmd_business_provider_ajax');
add_action('wp_ajax_nopriv_tmd_business_provider', 'tmd_business_provider_ajax');

function tmd_business_proposals_form_html($form_type) {
    $is_alliance = 'alianza' === $form_type;
    $title       = $is_alliance ? 'Presenta una propuesta de alianza' : 'Presenta tu empresa como proveedor';
    $description = $is_alliance ? 'Cuéntanos qué capacidad deseas integrar y qué valor generaría la colaboración.' : 'Comparte tu portafolio, cobertura y documentación comercial para revisión.';
    ob_start();
    ?>
    <section class="tmd-business-form-section" id="tmd-business-proposal-form" aria-labelledby="tmd-business-form-title">
        <div class="tmd-business-form-intro">
            <span>Canal empresarial</span>
            <h2 id="tmd-business-form-title"><?php echo esc_html($title); ?></h2>
            <p><?php echo esc_html($description); ?></p>
        </div>
        <form class="tmd-business-form" data-tmd-business-proposals method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo esc_attr($is_alliance ? 'tmd_business_alliance' : 'tmd_business_provider'); ?>">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('tmd_business_proposals')); ?>">
            <div class="tmd-business-honeypot" aria-hidden="true"><label>Sitio personal<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <label>Empresa <input name="company" maxlength="160" required></label>
            <label>NIT <input name="tax_id" maxlength="40"></label>
            <label>Nombre de contacto <input name="name" maxlength="120" required></label>
            <label>Cargo <input name="role" maxlength="120"></label>
            <label>Correo electrónico <input type="email" name="email" maxlength="190" required></label>
            <label>Teléfono <input type="tel" name="phone" maxlength="40" required></label>
            <label>Ciudad <input name="city" maxlength="100" required></label>
            <label>Cobertura <input name="coverage" maxlength="240" required></label>
            <label class="tmd-business-wide">Sitio web <input type="url" name="company_website" maxlength="300" placeholder="https://"></label>
            <label class="tmd-business-wide">Propuesta o portafolio <textarea name="message" maxlength="4000" required></textarea></label>
            <label class="tmd-business-wide tmd-business-upload">Documentación empresarial
                <input type="file" name="attachments[]" accept=".pdf,.docx,.jpg,.jpeg,.png,.webp" multiple required>
                <small>Adjunta entre 1 y 3 archivos PDF, DOCX, JPG, PNG o WEBP. Máximo 2.5 MB por archivo y 2.5 MB en total.</small>
            </label>
            <label class="tmd-business-wide tmd-business-terms"><input type="checkbox" name="terms" value="Acepto" required> <span>Acepto la <a href="<?php echo esc_url(home_url('/nosotros/legal/politica-de-privacidad/')); ?>" target="_blank" rel="noopener">política de privacidad</a>.</span></label>
            <button class="tmd-business-wide" type="submit">Enviar solicitud</button>
            <div class="tmd-business-wide tmd-business-status" data-tmd-form-status role="status" aria-live="polite"></div>
        </form>
    </section>
    <?php
    return (string) ob_get_clean();
}

function tmd_business_proposals_filter_content($content) {
    $form_type = is_page(275) ? 'alianza' : (is_page(793) ? 'proveedor' : '');
    if ('' === $form_type || false !== strpos($content, 'data-tmd-business-proposals')) {
        return $content;
    }
    $content = preg_replace('/href=(["\'])\/nosotros\/contacto\/?\1/', 'href="#tmd-business-proposal-form"', $content);
    return $content . tmd_business_proposals_form_html($form_type);
}
add_filter('the_content', 'tmd_business_proposals_filter_content', 30);

function tmd_business_proposals_filter_contact_service_tag($tag) {
    if (! is_page(57) || ! is_object($tag) || 'service' !== ($tag->name ?? '')) {
        return $tag;
    }
    $tag->values     = array_values(array_filter((array) ($tag->values ?? []), static fn($value) => ! tmd_business_proposals_is_alliance_label($value)));
    $tag->raw_values = array_values(array_filter((array) ($tag->raw_values ?? []), static fn($value) => ! tmd_business_proposals_is_alliance_label($value)));
    return $tag;
}
add_filter('wpcf7_form_tag', 'tmd_business_proposals_filter_contact_service_tag', 18);

function tmd_business_proposals_is_alliance_label($value) {
    $value = trim(strtolower(function_exists('remove_accents') ? remove_accents((string) $value) : (string) $value));
    return 'alianzas' === $value || 'alianza' === $value;
}

function tmd_business_proposals_validate_contact_service($result, $tag) {
    if (! is_object($tag) || 'service' !== ($tag->name ?? '') || ! class_exists('WPCF7_ContactForm')) {
        return $result;
    }
    $contact_form = WPCF7_ContactForm::get_current();
    if (! is_object($contact_form) || ! method_exists($contact_form, 'id') || 14 !== (int) $contact_form->id()) {
        return $result;
    }
    $posted_service = $_POST['service'] ?? '';
    $service = is_scalar($posted_service) ? wp_unslash((string) $posted_service) : '';
    if (tmd_business_proposals_is_alliance_label($service) && is_object($result) && method_exists($result, 'invalidate')) {
        $result->invalidate($tag, 'Selecciona otro servicio o utiliza el formulario de Alianzas.');
    }
    return $result;
}
add_filter('wpcf7_validate_select', 'tmd_business_proposals_validate_contact_service', 20, 2);
add_filter('wpcf7_validate_select*', 'tmd_business_proposals_validate_contact_service', 20, 2);

add_action('wp_enqueue_scripts', static function () {
    if (! is_page([275, 793])) {
        return;
    }
    $css = '/assets/css/tmd-business-proposals.css';
    $js  = '/assets/js/tmd-business-proposals.js';
    wp_enqueue_style('tmd-business-proposals', get_stylesheet_directory_uri() . $css, [], filemtime(get_stylesheet_directory() . $css));
    wp_enqueue_script('tmd-business-proposals', get_stylesheet_directory_uri() . $js, [], filemtime(get_stylesheet_directory() . $js), true);
    wp_localize_script('tmd-business-proposals', 'tmdBusinessProposals', [
        'ajaxUrl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('tmd_business_proposals'),
        'maxBytes' => tmd_business_proposals_max_bytes(), 'maxFiles' => 3,
        'invalidFiles' => 'Adjunta entre 1 y 3 archivos válidos, con máximo 2.5 MB en total.',
        'networkError' => 'No fue posible conectar con el servidor. Inténtalo nuevamente.',
        'sendingText' => 'Enviando solicitud…',
    ]);
}, 45);
