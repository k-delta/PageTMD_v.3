<?php
/**
 * Transformación pura usada por la migración del formulario de Postulación General.
 */

if (! function_exists('tmd_job_application_form_replacements')) {
    function tmd_job_application_form_replacements() {
        return [
            'form' => [
                'old' => '<form class="tmd-jobs-form" data-tmd-ajax-form="" enctype="multipart/form-data">',
                'new' => '<form class="tmd-jobs-form" data-tmd-ajax-form="" data-tmd-job-application="" enctype="multipart/form-data" method="post">',
            ],
            'honeypot' => [
                'old' => '<input type="hidden" name="form_type" value="trabaja_con_nosotros">',
                'new' => '<input type="hidden" name="form_type" value="trabaja_con_nosotros">' . "\n" .
                    '          <label class="tmd-jobs-honeypot" aria-hidden="true">No completar<input name="website" tabindex="-1" autocomplete="off"></label>',
            ],
            'cv' => [
                'old' => "<div class=\"tmd-jobs-upload\">\n                Adjunta tu hoja de vida en PDF desde el correo o WhatsApp indicado.\n              </div>",
                'new' => "<div class=\"tmd-jobs-upload\">\n                <input id=\"tmd-jobs-cv\" name=\"cv\" type=\"file\" accept=\".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document\" aria-describedby=\"tmd-jobs-cv-help\" required>\n                <span class=\"tmd-jobs-upload-help\" id=\"tmd-jobs-cv-help\">Selecciona tu hoja de vida en PDF, DOC o DOCX. Máximo 2 MB.</span>\n              </div>",
            ],
            'consent' => [
                'old' => '<button class="tmd-jobs-btn tmd-jobs-btn-blue" type="submit" style="width:100%;">Enviar Postulación</button>',
                'new' => '<label class="tmd-jobs-consent"><input type="checkbox" name="terms" value="Acepto" required> <span>Acepto la <a href="/nosotros/legal/politica-de-privacidad/" target="_blank" rel="noopener">política de tratamiento de datos personales</a>.</span></label>' . "\n" .
                    '              <button class="tmd-jobs-btn tmd-jobs-btn-blue" type="submit" style="width:100%;">Enviar Postulación</button>',
            ],
            'status' => [
                'old' => '<div class="tmd-form-status" data-tmd-form-status="" style="margin-top:12px;"></div>',
                'new' => '<div class="tmd-form-status" data-tmd-form-status="" role="status" aria-live="polite" aria-atomic="true" style="margin-top:12px;"></div>',
            ],
        ];
    }
}

if (! function_exists('tmd_transform_job_application_form')) {
    function tmd_transform_job_application_form($content) {
        $original = (string) $content;
        $working  = $original;
        $changes  = [];
        $errors   = [];

        foreach (tmd_job_application_form_replacements() as $key => $replacement) {
            $old_count        = substr_count($working, $replacement['old']);
            $new_count        = substr_count($working, $replacement['new']);
            $old_inside_final = substr_count($replacement['new'], $replacement['old']);

            if (1 === $new_count && $old_inside_final === $old_count) {
                continue;
            }

            if (1 !== $old_count || 0 !== $new_count) {
                $errors[] = sprintf(
                    'La precondición %s no coincide (anterior=%d, final=%d).',
                    $key,
                    $old_count,
                    $new_count
                );
                continue;
            }

            $working   = str_replace($replacement['old'], $replacement['new'], $working);
            $changes[] = $key;
        }

        return [
            'content' => empty($errors) ? $working : $original,
            'changes' => empty($errors) ? $changes : [],
            'errors'  => $errors,
        ];
    }
}
