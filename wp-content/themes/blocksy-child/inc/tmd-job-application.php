<?php
/**
 * Formulario público de Postulación General.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('tmd_job_application_recipient_for')) {
    function tmd_job_application_recipient_for($form_type) {
        $recipients = [
            'trabaja_con_nosotros' => 'rh@tmdual.com',
        ];

        return $recipients[(string) $form_type] ?? '';
    }
}

if (! function_exists('tmd_job_application_textarea')) {
    function tmd_job_application_textarea($value) {
        if (function_exists('sanitize_textarea_field')) {
            return sanitize_textarea_field($value);
        }

        return sanitize_text_field($value);
    }
}

if (! function_exists('tmd_job_application_string_length')) {
    function tmd_job_application_string_length($value) {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}

if (! function_exists('tmd_job_application_validate_fields')) {
    function tmd_job_application_validate_fields($post) {
        $post      = is_array($post) ? $post : [];
        $form_type = sanitize_text_field(wp_unslash((string) ($post['form_type'] ?? '')));
        $recipient = tmd_job_application_recipient_for($form_type);

        if ('' === $recipient) {
            return new WP_Error('invalid_form_type', 'El formulario solicitado no está disponible.');
        }

        $raw_email = wp_unslash((string) ($post['email'] ?? ''));
        $email     = sanitize_email($raw_email);
        $fields    = [
            'form_type' => $form_type,
            'recipient' => $recipient,
            'name'      => sanitize_text_field(wp_unslash((string) ($post['name'] ?? ''))),
            'email'     => $email,
            'phone'     => sanitize_text_field(wp_unslash((string) ($post['phone'] ?? ''))),
            'city'      => sanitize_text_field(wp_unslash((string) ($post['city'] ?? ''))),
            'service'   => sanitize_text_field(wp_unslash((string) ($post['service'] ?? ''))),
            'message'   => tmd_job_application_textarea(wp_unslash((string) ($post['message'] ?? ''))),
            'terms'     => sanitize_text_field(wp_unslash((string) ($post['terms'] ?? ''))),
        ];

        foreach (['name', 'email', 'service', 'message'] as $required) {
            if ('' === $fields[$required]) {
                return new WP_Error('missing_fields', 'Completa todos los campos obligatorios.');
            }
        }

        if ($email !== trim($raw_email) || ! is_email($email)) {
            return new WP_Error('invalid_email', 'Escribe un correo electrónico válido.');
        }

        if ('Acepto' !== $fields['terms']) {
            return new WP_Error('missing_terms', 'Debes aceptar la política de tratamiento de datos.');
        }

        $limits = [
            'name'    => 120,
            'phone'   => 40,
            'city'    => 100,
            'service' => 120,
            'message' => 3000,
        ];

        foreach ($limits as $field => $limit) {
            if (tmd_job_application_string_length($fields[$field]) > $limit) {
                return new WP_Error('field_too_long', 'Uno de los campos supera la longitud permitida.');
            }
        }

        return $fields;
    }
}

if (! function_exists('tmd_job_application_detect_mime')) {
    function tmd_job_application_detect_mime($path) {
        if (! function_exists('finfo_open')) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (! $finfo) {
            return '';
        }

        $mime = finfo_file($finfo, $path);
        unset($finfo);

        return is_string($mime) ? strtolower($mime) : '';
    }
}

if (! function_exists('tmd_job_application_valid_structure')) {
    function tmd_job_application_xml_document($xml) {
        if (! class_exists('DOMDocument') || ! is_string($xml) || false !== stripos($xml, '<!DOCTYPE')) {
            return false;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $loaded ? $document : false;
    }

    function tmd_job_application_ole_directory_names($path) {
        $content = @file_get_contents($path);
        if (! is_string($content) || strlen($content) < 1536
            || "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" !== substr($content, 0, 8)) {
            return false;
        }

        $uint32 = static function ($value, $offset) {
            $unpacked = unpack('Vvalue', substr($value, $offset, 4));
            return (int) ($unpacked['value'] ?? -1);
        };
        $uint16 = static function ($value, $offset) {
            $unpacked = unpack('vvalue', substr($value, $offset, 2));
            return (int) ($unpacked['value'] ?? -1);
        };

        $major_version = $uint16($content, 26);
        $sector_shift  = $uint16($content, 30);
        $sector_size   = 1 << $sector_shift;
        $fat_count     = $uint32($content, 44);
        $first_dir     = $uint32($content, 48);
        $difat_count   = $uint32($content, 72);

        if (! in_array($major_version, [3, 4], true)
            || ! in_array($sector_size, [512, 4096], true)
            || $fat_count < 1 || $fat_count > 109 || 0 !== $difat_count) {
            return false;
        }

        $sector_at = static function ($sector_id) use ($content, $sector_size) {
            $offset = $sector_size + ($sector_id * $sector_size);
            if ($sector_id < 0 || $offset < $sector_size || $offset + $sector_size > strlen($content)) {
                return false;
            }
            return substr($content, $offset, $sector_size);
        };
        $fat = [];
        for ($index = 0; $index < $fat_count; ++$index) {
            $fat_sector_id = $uint32($content, 76 + ($index * 4));
            $fat_sector = $sector_at($fat_sector_id);
            if (! is_string($fat_sector)) {
                return false;
            }
            for ($offset = 0; $offset < $sector_size; $offset += 4) {
                $fat[] = $uint32($fat_sector, $offset);
            }
        }

        $directory = '';
        $visited   = [];
        $sector_id = $first_dir;
        while (0xFFFFFFFE !== $sector_id) {
            if (isset($visited[$sector_id]) || ! isset($fat[$sector_id]) || count($visited) > 4096) {
                return false;
            }
            $visited[$sector_id] = true;
            $sector = $sector_at($sector_id);
            if (! is_string($sector)) {
                return false;
            }
            $directory .= $sector;
            $sector_id = $fat[$sector_id];
        }

        $names = [];
        for ($offset = 0; $offset + 128 <= strlen($directory); $offset += 128) {
            $entry       = substr($directory, $offset, 128);
            $name_length = $uint16($entry, 64);
            $entry_type  = ord($entry[66]);
            if (! in_array($entry_type, [1, 2, 5], true) || $name_length < 2 || $name_length > 64 || 0 !== $name_length % 2) {
                continue;
            }
            $encoded_name = substr($entry, 0, $name_length - 2);
            $name = function_exists('mb_convert_encoding')
                ? mb_convert_encoding($encoded_name, 'UTF-8', 'UTF-16LE')
                : iconv('UTF-16LE', 'UTF-8//IGNORE', $encoded_name);
            if (is_string($name) && '' !== $name) {
                $names[] = $name;
            }
        }

        return $names;
    }

    function tmd_job_application_valid_structure($path, $extension) {
        if ('pdf' === $extension) {
            $content = @file_get_contents($path);
            if (! is_string($content) || '%PDF-' !== substr($content, 0, 5)
                || false === strpos(substr($content, -1024), '%%EOF')) {
                return false;
            }

            $decoded_names = preg_replace_callback('/#[0-9A-Fa-f]{2}/', static function ($match) {
                return chr(hexdec(substr($match[0], 1)));
            }, $content);
            foreach (['/JavaScript', '/JS', '/OpenAction', '/Launch', '/EmbeddedFile', '/Filespec', '/RichMedia', '/AA', '/ObjStm'] as $active_marker) {
                if (false !== stripos($decoded_names, $active_marker)) {
                    return false;
                }
            }

            return true;
        }

        if ('doc' === $extension) {
            $directory_names = tmd_job_application_ole_directory_names($path);
            if (! is_array($directory_names) || ! in_array('WordDocument', $directory_names, true)) {
                return false;
            }

            foreach ($directory_names as $directory_name) {
                if (preg_match('/(?:vba|macro|_vba_project|objectpool|ole10native|package)/i', $directory_name)) {
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

            $valid             = $archive->numFiles > 0 && $archive->numFiles <= 200;
            $total_size        = 0;
            $content_types_stat = $archive->statName('[Content_Types].xml');
            $normalized_names  = [];

            for ($index = 0; $valid && $index < $archive->numFiles; ++$index) {
                $entry_stat = $archive->statIndex($index);
                if (! is_array($entry_stat)) {
                    $valid = false;
                    break;
                }

                $entry_size      = (int) ($entry_stat['size'] ?? 0);
                $compressed_size = (int) ($entry_stat['comp_size'] ?? 0);
                $total_size     += $entry_size;
                $entry           = (string) ($entry_stat['name'] ?? '');
                $normalized_entry = strtolower(str_replace('\\', '/', $entry));

                if ($total_size > 10 * 1024 * 1024
                    || ($entry_size > 1024 * 1024 && $compressed_size > 0 && $entry_size / $compressed_size > 100)
                    || isset($normalized_names[$normalized_entry])
                    || false !== strpos('/' . $normalized_entry, '/../')
                    || 0 === strpos($normalized_entry, 'word/embeddings/')
                    || 0 === strpos($normalized_entry, 'word/activex/')
                    || 0 === strpos($normalized_entry, 'customui/')
                    || false !== strpos($normalized_entry, 'vbaproject')) {
                    $valid = false;
                }
                $normalized_names[$normalized_entry] = true;
            }

            $valid = $valid
                && is_array($content_types_stat)
                && (int) ($content_types_stat['size'] ?? 0) > 0
                && (int) ($content_types_stat['size'] ?? 0) <= 64 * 1024
                && isset($normalized_names['word/document.xml']);

            $content_types = $valid ? $archive->getFromName('[Content_Types].xml') : false;
            $content_types_document = tmd_job_application_xml_document($content_types);
            $has_word_main = false;
            if ($content_types_document) {
                foreach ($content_types_document->getElementsByTagName('*') as $element) {
                    $content_type = (string) $element->getAttribute('ContentType');
                    if ('application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml' === $content_type) {
                        $has_word_main = true;
                    }
                    if (preg_match('/(?:macroEnabled|activeX|vbaProject)/i', $content_type)) {
                        $valid = false;
                    }
                }
            }
            $valid = $valid && $content_types_document && $has_word_main;

            for ($index = 0; $valid && $index < $archive->numFiles; ++$index) {
                $entry_stat = $archive->statIndex($index);
                $entry_name = strtolower(str_replace('\\', '/', (string) ($entry_stat['name'] ?? '')));
                if ('.rels' !== substr($entry_name, -5)) {
                    continue;
                }
                if ((int) ($entry_stat['size'] ?? 0) > 64 * 1024) {
                    $valid = false;
                    break;
                }
                $relationships = $archive->getFromIndex($index);
                $relationships_document = tmd_job_application_xml_document($relationships);
                if (! $relationships_document) {
                    $valid = false;
                    break;
                }
                foreach ($relationships_document->getElementsByTagName('*') as $relationship) {
                    if (! $relationship->hasAttribute('Type')) {
                        continue;
                    }
                    $relationship_value = implode(' ', [
                        $relationship->getAttribute('Type'),
                        $relationship->getAttribute('Target'),
                        $relationship->getAttribute('TargetMode'),
                    ]);
                    if (preg_match('/(?:oleObject|attachedTemplate|\/package|vbaProject|embeddings\/|activeX\/)/i', $relationship_value)) {
                        $valid = false;
                        break;
                    }
                }
            }
            $archive->close();

            return $valid;
        }

        return false;
    }
}

if (! function_exists('tmd_job_application_is_uploaded_file')) {
    function tmd_job_application_is_uploaded_file($path) {
        return is_uploaded_file($path);
    }
}

if (! function_exists('tmd_job_application_move_uploaded_file')) {
    function tmd_job_application_move_uploaded_file($source, $destination) {
        return move_uploaded_file($source, $destination);
    }
}

if (! function_exists('tmd_job_application_validate_cv')) {
    function tmd_job_application_validate_cv($file, $upload_checker = null, $mime_detector = null, $structure_checker = null) {
        if (! is_array($file) || is_array($file['name'] ?? null)) {
            return new WP_Error('missing_cv', 'Adjunta un único archivo PDF, DOC o DOCX.');
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if (UPLOAD_ERR_OK !== $error) {
            return new WP_Error('upload_error', 'No fue posible recibir el archivo. Inténtalo nuevamente.');
        }

        $name     = sanitize_file_name((string) ($file['name'] ?? ''));
        $tmp_name = (string) ($file['tmp_name'] ?? '');
        $size     = (int) ($file['size'] ?? 0);
        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));

        if ('' === $name || '' === $tmp_name || $size < 1) {
            return new WP_Error('missing_cv', 'Adjunta un archivo PDF, DOC o DOCX.');
        }

        if ($size > 2 * 1024 * 1024) {
            return new WP_Error('cv_too_large', 'El archivo debe pesar máximo 2 MB.');
        }

        if (! in_array($extension, ['pdf', 'doc', 'docx'], true)) {
            return new WP_Error('invalid_cv_extension', 'El archivo debe ser PDF, DOC o DOCX.');
        }

        $upload_checker = is_callable($upload_checker) ? $upload_checker : 'tmd_job_application_is_uploaded_file';
        if (! call_user_func($upload_checker, $tmp_name)) {
            return new WP_Error('invalid_upload', 'No fue posible validar el archivo recibido.');
        }

        $actual_size = @filesize($tmp_name);
        if (false !== $actual_size && ((int) $actual_size < 1 || (int) $actual_size > 2 * 1024 * 1024)) {
            return new WP_Error('invalid_cv_size', 'El archivo debe pesar máximo 2 MB.');
        }

        $mime_detector    = is_callable($mime_detector) ? $mime_detector : 'tmd_job_application_detect_mime';
        $structure_checker = is_callable($structure_checker) ? $structure_checker : 'tmd_job_application_valid_structure';
        $mime             = strtolower((string) call_user_func($mime_detector, $tmp_name));
        $allowed_mimes    = [
            'pdf'  => ['application/pdf', 'application/x-pdf'],
            'doc'  => ['application/msword', 'application/vnd.ms-office', 'application/cdfv2', 'application/x-ole-storage'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-zip-compressed'],
        ];

        if (! in_array($mime, $allowed_mimes[$extension], true)
            || ! call_user_func($structure_checker, $tmp_name, $extension)) {
            return new WP_Error('invalid_cv_type', 'El contenido del archivo no coincide con PDF, DOC o DOCX.');
        }

        return [
            'name'      => $name,
            'tmp_name'  => $tmp_name,
            'size'      => $size,
            'extension' => $extension,
            'mime'      => $mime,
        ];
    }
}

if (! function_exists('tmd_job_application_build_mail')) {
    function tmd_job_application_build_mail($fields, $attachment_path) {
        $subject = sprintf('Postulación general | %s', $fields['service']);
        $rows    = [
            'Nombre'          => $fields['name'],
            'Email'           => $fields['email'],
            'Teléfono'        => $fields['phone'] ?: 'No informado',
            'Ciudad'          => $fields['city'] ?: 'No informada',
            'Área de interés' => $fields['service'],
            'Mensaje'         => nl2br(esc_html($fields['message'])),
        ];
        $message = '<div style="font-family:Arial,sans-serif;color:#262e4f;line-height:1.6">';
        $message .= '<h1 style="font-size:24px">Nueva postulación general</h1><table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%">';

        foreach ($rows as $label => $value) {
            $safe_value = 'Mensaje' === $label ? $value : esc_html($value);
            $message .= '<tr><th align="left" style="border-bottom:1px solid #dce3ec;width:180px">' . esc_html($label) . '</th>';
            $message .= '<td style="border-bottom:1px solid #dce3ec">' . $safe_value . '</td></tr>';
        }

        $message .= '</table><p>La hoja de vida se encuentra adjunta a este mensaje.</p></div>';

        return [
            'to'          => $fields['recipient'],
            'subject'     => $subject,
            'message'     => $message,
            'headers'     => [
                'Content-Type: text/html; charset=UTF-8',
                'Reply-To: ' . $fields['email'],
            ],
            'attachments' => [$attachment_path],
        ];
    }
}

if (! function_exists('tmd_job_application_rate_limited')) {
    function tmd_job_application_rate_limited($email, $ip) {
        $lock_path = trailingslashit(get_temp_dir()) . 'tmd-job-application-rate.lock';
        $lock      = @fopen($lock_path, 'c');

        if (! $lock || ! flock($lock, LOCK_EX)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            return true;
        }

        try {
            $keys = [
                ['key' => 'tmd_job_apply_ip_' . substr(wp_hash($ip), 0, 32), 'limit' => 5],
                ['key' => 'tmd_job_apply_email_' . substr(wp_hash(strtolower($email)), 0, 32), 'limit' => 3],
                ['key' => 'tmd_job_apply_global', 'limit' => 30],
            ];

            foreach ($keys as $entry) {
                if ((int) get_transient($entry['key']) >= $entry['limit']) {
                    return true;
                }
            }

            foreach ($keys as $entry) {
                set_transient($entry['key'], (int) get_transient($entry['key']) + 1, HOUR_IN_SECONDS);
            }

            return false;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}

if (! function_exists('tmd_job_application_stage_cv')) {
    function tmd_job_application_stage_cv($cv) {
        $directory   = get_temp_dir();
        $filename    = wp_unique_filename($directory, $cv['name']);
        $destination = trailingslashit($directory) . $filename;

        if (! tmd_job_application_move_uploaded_file($cv['tmp_name'], $destination)) {
            return new WP_Error('stage_failed', 'No fue posible preparar el archivo adjunto.');
        }

        return $destination;
    }
}

if (! function_exists('tmd_job_application_error_status')) {
    function tmd_job_application_error_status($error) {
        $forbidden = ['invalid_form_type'];
        return in_array($error->get_error_code(), $forbidden, true) ? 403 : 400;
    }
}

if (! function_exists('tmd_job_application_ajax')) {
    function tmd_job_application_ajax() {
        if (! check_ajax_referer('tmd_job_application', 'nonce', false)) {
            wp_send_json_error(['message' => 'La sesión expiró. Recarga la página e inténtalo nuevamente.'], 403);
        }

        if (! empty($_POST['website'])) {
            wp_send_json_success(['message' => 'Postulación enviada correctamente.']);
        }

        $fields = tmd_job_application_validate_fields($_POST);
        if (is_wp_error($fields)) {
            wp_send_json_error(['message' => $fields->get_error_message()], tmd_job_application_error_status($fields));
        }

        $ip = sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
        if (tmd_job_application_rate_limited($fields['email'], $ip)) {
            wp_send_json_error(['message' => 'Has realizado varios intentos. Espera un momento antes de volver a enviar.'], 429);
        }

        $cv = tmd_job_application_validate_cv($_FILES['cv'] ?? []);
        if (is_wp_error($cv)) {
            wp_send_json_error(['message' => $cv->get_error_message()], 400);
        }

        $attachment_path = tmd_job_application_stage_cv($cv);
        if (is_wp_error($attachment_path)) {
            wp_send_json_error(['message' => $attachment_path->get_error_message()], 500);
        }

        $sent = false;
        try {
            $mail = tmd_job_application_build_mail($fields, $attachment_path);
            $sent = wp_mail($mail['to'], $mail['subject'], $mail['message'], $mail['headers'], $mail['attachments']);
        } catch (Throwable $error) {
            $sent = false;
        } finally {
            if (file_exists($attachment_path)) {
                wp_delete_file($attachment_path);
            }
        }

        if (! $sent) {
            wp_send_json_error(['message' => 'No fue posible enviar la postulación. Inténtalo nuevamente más tarde.'], 502);
        }

        wp_send_json_success(['message' => 'Postulación enviada correctamente.']);
    }
}

add_action('wp_ajax_tmd_job_application', 'tmd_job_application_ajax');
add_action('wp_ajax_nopriv_tmd_job_application', 'tmd_job_application_ajax');

add_action('wp_enqueue_scripts', function () {
    if (! is_page(273)) {
        return;
    }

    $css_path = get_stylesheet_directory() . '/assets/css/tmd-job-application.css';
    $js_path  = get_stylesheet_directory() . '/assets/js/tmd-job-application.js';

    wp_enqueue_style(
        'tmd-job-application',
        get_stylesheet_directory_uri() . '/assets/css/tmd-job-application.css',
        [],
        file_exists($css_path) ? filemtime($css_path) : '1.0.0'
    );
    wp_enqueue_script(
        'tmd-job-application',
        get_stylesheet_directory_uri() . '/assets/js/tmd-job-application.js',
        [],
        file_exists($js_path) ? filemtime($js_path) : '1.0.0',
        true
    );
    wp_localize_script('tmd-job-application', 'tmdJobApplication', [
        'ajaxUrl'       => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('tmd_job_application'),
        'maxBytes'      => 2 * 1024 * 1024,
        'invalidFile'   => 'Selecciona un archivo PDF, DOC o DOCX de máximo 2 MB.',
        'networkError'  => 'No fue posible conectar con el servidor. Inténtalo nuevamente.',
        'sendingText'   => 'Enviando postulación…',
    ]);
}, 45);
