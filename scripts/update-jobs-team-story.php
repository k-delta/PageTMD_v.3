<?php
/**
 * Actualiza de forma focalizada el testimonio de "Trabaja con nosotros" (ID 273):
 * - reemplaza la foto circular del colaborador;
 * - reemplaza únicamente el blockquote asociado al testimonio.
 *
 * La fotografía nueva debe existir previamente en wp-content/uploads/2026/08/
 * con el nombre trabaja-colaborador-20260826.jpeg.
 */

if (! function_exists('tmd_jobs_testimonial_new_text')) {
    function tmd_jobs_testimonial_new_text()
    {
        return 'Laborar para Tecnimontacargas me ha permitido ampliar mis conocimientos contando con el apoyo de compañeros y directivos, ya que la confianza depositada en mi ha contribuido a desarrollar y demostrar mis capacidades como profesional';
    }
}

if (! function_exists('tmd_jobs_testimonial_old_image_url')) {
    function tmd_jobs_testimonial_old_image_url()
    {
        return '/wp-content/themes/blocksy-child/assets/img/personal/trabaja-rh-exito-20260814-210444.png';
    }
}

if (! function_exists('tmd_jobs_testimonial_new_image_url')) {
    function tmd_jobs_testimonial_new_image_url()
    {
        return '/wp-content/uploads/2026/08/trabaja-colaborador-20260826.jpeg';
    }
}

if (! function_exists('tmd_jobs_testimonial_new_image_sha256')) {
    function tmd_jobs_testimonial_new_image_sha256()
    {
        return 'e86fc460e8e10fa6ac91b38f12ed65b9807ad611b2ee41515760614c63317149';
    }
}

if (! function_exists('tmd_jobs_testimonial_section_bounds')) {
    function tmd_jobs_testimonial_section_bounds($content, &$errors)
    {
        $pattern = '~<section\\b[^>]*\\btmd-jobs-testimonial\\b[^>]*>~iu';
        $count = preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        if (1 !== $count) {
            $errors[] = sprintf(
                'No se encontró una única sección tmd-jobs-testimonial (coincidencias=%d).',
                (int) $count
            );
            return null;
        }

        $startTag = $matches[0][0][0];
        $start = $matches[0][0][1];
        $afterStart = $start + strlen($startTag);
        $end = strpos($content, '</section>', $afterStart);

        if (false === $end) {
            $errors[] = 'No se encontró el cierre de la sección tmd-jobs-testimonial.';
            return null;
        }

        $end += strlen('</section>');

        return [$start, $end - $start];
    }
}

if (! function_exists('tmd_jobs_testimonial_transform_section')) {
    function tmd_jobs_testimonial_transform_section($section, &$changes, &$errors)
    {
        $oldImage = tmd_jobs_testimonial_old_image_url();
        $newImage = tmd_jobs_testimonial_new_image_url();
        $newText = tmd_jobs_testimonial_new_text();

        $oldImageCount = substr_count($section, $oldImage);
        $newImageCount = substr_count($section, $newImage);

        if (1 === $oldImageCount && 0 === $newImageCount) {
            $section = str_replace($oldImage, $newImage, $section);
            $changes[] = 'imagen-testimonio';
        } elseif (0 === $oldImageCount && 1 === $newImageCount) {
            // Ya migrada: no-op.
        } else {
            $errors[] = sprintf(
                'La foto del testimonio no coincide de forma unívoca (anterior=%d, nueva=%d).',
                $oldImageCount,
                $newImageCount
            );
            return $section;
        }

        $blockquotePattern = '~<blockquote\\b([^>]*)>(.*?)</blockquote>~isu';
        $blockquoteCount = preg_match_all(
            $blockquotePattern,
            $section,
            $quotes,
            PREG_OFFSET_CAPTURE
        );

        if (1 !== $blockquoteCount) {
            $errors[] = sprintf(
                'No se encontró un único blockquote en el testimonio (coincidencias=%d).',
                (int) $blockquoteCount
            );
            return $section;
        }

        $quoteHtml = $quotes[0][0][0];
        $quotePos = $quotes[0][0][1];
        $attrs = $quotes[1][0][0];
        $currentText = trim(strip_tags($quotes[2][0][0]));

        if ($currentText !== $newText) {
            $newQuoteHtml = '<blockquote' . $attrs . '>' . $newText . '</blockquote>';
            $section = substr_replace(
                $section,
                $newQuoteHtml,
                $quotePos,
                strlen($quoteHtml)
            );
            $changes[] = 'texto-testimonio';
        }

        return $section;
    }
}

if (! function_exists('tmd_transform_jobs_team_story')) {
    function tmd_transform_jobs_team_story($content)
    {
        $original = (string) $content;
        $working = $original;
        $changes = [];
        $errors = [];

        $bounds = tmd_jobs_testimonial_section_bounds($working, $errors);

        if (empty($errors) && is_array($bounds)) {
            [$start, $length] = $bounds;
            $section = substr($working, $start, $length);
            $updatedSection = tmd_jobs_testimonial_transform_section(
                $section,
                $changes,
                $errors
            );

            if (empty($errors)) {
                $working = substr_replace(
                    $working,
                    $updatedSection,
                    $start,
                    $length
                );
            }
        }

        return [
            'content' => empty($errors) ? $working : $original,
            'changes' => empty($errors) ? $changes : [],
            'errors'  => $errors,
            'changed' => empty($errors) && $working !== $original,
        ];
    }
}

if (! function_exists('tmd_jobs_testimonial_validate_new_image')) {
    function tmd_jobs_testimonial_validate_new_image()
    {
        if (! defined('ABSPATH')) {
            return new WP_Error('tmd_missing_abspath', 'ABSPATH no está disponible.');
        }

        $path = trailingslashit(ABSPATH)
            . 'wp-content/uploads/2026/08/trabaja-colaborador-20260826.jpeg';

        if (! is_file($path)) {
            return new WP_Error(
                'tmd_missing_testimonial_image',
                'No existe la nueva fotografía en wp-content/uploads/2026/08/trabaja-colaborador-20260826.jpeg.'
            );
        }

        $hash = hash_file('sha256', $path);

        if (tmd_jobs_testimonial_new_image_sha256() !== $hash) {
            return new WP_Error(
                'tmd_testimonial_image_hash',
                'La fotografía encontrada no coincide con la imagen #2 suministrada.'
            );
        }

        return $path;
    }
}

if (defined('WP_CLI') && WP_CLI) {
    $pageId = 273;
    $post = get_post($pageId);

    if (! $post || 'page' !== $post->post_type) {
        WP_CLI::error('No se encontró la página Trabaja con nosotros (ID 273).');
    }

    $result = tmd_transform_jobs_team_story($post->post_content);

    if (! empty($result['errors'])) {
        WP_CLI::error("No se aplicaron cambios:\n- " . implode("\n- ", $result['errors']));
    }

    $imagePath = tmd_jobs_testimonial_validate_new_image();

    if (is_wp_error($imagePath)) {
        WP_CLI::error($imagePath->get_error_message());
    }

    if (! $result['changed']) {
        WP_CLI::success('Trabaja con nosotros ya contiene la foto y el texto objetivo.');
        return;
    }

    $updated = wp_update_post([
        'ID'           => $pageId,
        'post_content' => $result['content'],
    ], true);

    if (is_wp_error($updated)) {
        WP_CLI::error($updated->get_error_message());
    }

    WP_CLI::success(
        'Trabaja con nosotros actualizado: ' . implode(', ', $result['changes'])
    );
}
