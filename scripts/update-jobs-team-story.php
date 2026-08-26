<?php
/**
 * Actualiza de forma focalizada la foto y el texto del bloque
 * "Descubre por qué somos un gran equipo" en Trabaja con nosotros (ID 273).
 */

if (! function_exists('tmd_jobs_team_story_new_text')) {
    function tmd_jobs_team_story_new_text()
    {
        return 'Laborar para Tecnimontacargas me ha permitido ampliar mis conocimientos contando con el apoyo de compañeros y directivos, ya que la confianza depositada en mi ha contribuido a desarrollar y demostrar mis capacidades como profesional';
    }
}

if (! function_exists('tmd_jobs_team_story_replace_image')) {
    function tmd_jobs_team_story_replace_image($content, &$changes, &$errors)
    {
        $old = '/wp-content/themes/blocksy-child/assets/img/personal/trabaja-equipo.webp';
        $new = '/wp-content/themes/blocksy-child/assets/img/personal/trabaja-rh-exito-20260814-210444.png';

        $oldCount = substr_count($content, $old);
        $newCount = substr_count($content, $new);

        if (1 === $newCount && 0 === $oldCount) {
            return $content;
        }

        if (1 !== $oldCount || 0 !== $newCount) {
            $errors[] = sprintf(
                'La imagen esperada no coincide de forma unívoca (anterior=%d, nueva=%d).',
                $oldCount,
                $newCount
            );
            return $content;
        }

        $changes[] = 'imagen-equipo';
        return str_replace($old, $new, $content);
    }
}

if (! function_exists('tmd_jobs_team_story_replace_text')) {
    function tmd_jobs_team_story_replace_text($content, &$changes, &$errors)
    {
        $newText = tmd_jobs_team_story_new_text();

        if (1 === substr_count($content, $newText)) {
            return $content;
        }

        $headingPattern = '~<h([1-6])\\b[^>]*>\\s*Descubre\\s+por\\s+qué\\s+somos\\s+un\\s+gran\\s+equipo\\s*</h\\1>~iu';
        $headingCount = preg_match_all($headingPattern, $content, $headings, PREG_OFFSET_CAPTURE);

        if (1 !== $headingCount) {
            $errors[] = sprintf(
                'No se encontró un único título "Descubre por qué somos un gran equipo" (coincidencias=%d).',
                (int) $headingCount
            );
            return $content;
        }

        $headingHtml = $headings[0][0][0];
        $headingPos  = $headings[0][0][1];
        $afterPos    = $headingPos + strlen($headingHtml);
        $window      = substr($content, $afterPos, 1800);

        if (false === $window) {
            $errors[] = 'No se pudo inspeccionar el contenido posterior al título del bloque.';
            return $content;
        }

        $paragraphPattern = '~<p\\b([^>]*)>(.*?)</p>~isu';
        $paragraphCount = preg_match_all($paragraphPattern, $window, $paragraphs, PREG_OFFSET_CAPTURE);

        if ($paragraphCount < 1) {
            $errors[] = 'No se encontró el párrafo asociado al bloque de equipo.';
            return $content;
        }

        $firstParagraphHtml = $paragraphs[0][0][0];
        $firstParagraphPos  = $paragraphs[0][0][1];

        $beforeParagraph = substr($window, 0, $firstParagraphPos);
        if (preg_match('~<h[1-6]\\b~iu', $beforeParagraph)) {
            $errors[] = 'Apareció otro título antes del párrafo objetivo; no se realizará el reemplazo.';
            return $content;
        }

        $attrs = $paragraphs[1][0][0];
        $newParagraphHtml = '<p' . $attrs . '>' . $newText . '</p>';

        $absoluteParagraphPos = $afterPos + $firstParagraphPos;
        $content = substr_replace(
            $content,
            $newParagraphHtml,
            $absoluteParagraphPos,
            strlen($firstParagraphHtml)
        );

        $changes[] = 'texto-equipo';
        return $content;
    }
}

if (! function_exists('tmd_transform_jobs_team_story')) {
    function tmd_transform_jobs_team_story($content)
    {
        $original = (string) $content;
        $working  = $original;
        $changes  = [];
        $errors   = [];

        $working = tmd_jobs_team_story_replace_image($working, $changes, $errors);

        if (empty($errors)) {
            $working = tmd_jobs_team_story_replace_text($working, $changes, $errors);
        }

        return [
            'content' => empty($errors) ? $working : $original,
            'changes' => empty($errors) ? $changes : [],
            'errors'  => $errors,
            'changed' => empty($errors) && $working !== $original,
        ];
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
