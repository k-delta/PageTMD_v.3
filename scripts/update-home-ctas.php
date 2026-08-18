<?php
/**
 * Corrige los CTAs y textos de energía del inicio de forma idempotente.
 *
 * Validación local sin escritura:
 * wp eval-file scripts/update-home-ctas.php -- dry-run
 *
 * Ejecución autorizada, después de backup:
 * wp eval-file scripts/update-home-ctas.php -- execute
 */

if (! function_exists('tmd_home_ctas_button_targets')) {
    function tmd_home_ctas_button_targets() {
        return [
            '47_e9dd8c-4a' => '/equipos/',
            '47_23547f-fc' => '/mantenimiento/',
            '47_2c1f64-1c' => '/energia/',
            '47_2a907e-63' => '/energia/baterias/',
            '47_95e299-1f' => '/energia/cargadores/',
        ];
    }
}

if (! function_exists('tmd_home_ctas_text_targets')) {
    function tmd_home_ctas_text_targets() {
        $previous = 'Opciones de litio y plomo-ácido para equipos eléctricos de manejo de carga.';

        return [
            '47_6f15fb-12' => [
                'previous' => $previous,
                'target'   => 'Baterías de tracción para montacargas eléctricos, con criterios de selección, carga y cuidado.',
            ],
            '47_a1bb23-84' => [
                'previous' => $previous,
                'target'   => 'Cargadores industriales para baterías de montacargas, según compatibilidad, voltaje y capacidad.',
            ],
        ];
    }
}

if (! function_exists('tmd_home_ctas_find_block_comment')) {
    function tmd_home_ctas_find_block_comment($content, $block_name, $unique_id, &$error) {
        $pattern = '~<!-- wp:' . preg_quote($block_name, '~') . ' (?P<attrs>\{[^\r\n]*\}) (?:/-->|-->)~u';
        $count   = preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (false === $count) {
            $error = "No se pudo analizar el bloque {$unique_id}.";
            return null;
        }

        $found = [];

        foreach ($matches as $match) {
            $attributes = json_decode($match['attrs'][0], true);

            if (is_array($attributes) && ($attributes['uniqueID'] ?? '') === $unique_id) {
                $found[] = [
                    'full'       => $match[0][0],
                    'offset'     => $match[0][1],
                    'attrs'      => $match['attrs'][0],
                    'attributes' => $attributes,
                ];
            }
        }

        if (1 !== count($found)) {
            $error = sprintf(
                'Se esperaba un bloque %s con uniqueID %s; encontrados: %d.',
                $block_name,
                $unique_id,
                count($found)
            );
            return null;
        }

        return $found[0];
    }
}

if (! function_exists('tmd_home_ctas_set_button_url')) {
    function tmd_home_ctas_set_button_url($content, $unique_id, $target_url, &$changes, &$errors) {
        $error = '';
        $block = tmd_home_ctas_find_block_comment($content, 'kadence/singlebtn', $unique_id, $error);

        if (null === $block) {
            $errors[] = $error;
            return $content;
        }

        if (array_key_exists('url', $block['attributes'])) {
            if ($block['attributes']['url'] === $target_url) {
                return $content;
            }

            $errors[] = sprintf(
                'El CTA %s ya tiene un destino contradictorio: %s.',
                $unique_id,
                (string) $block['attributes']['url']
            );
            return $content;
        }

        $encoded_url = json_encode($target_url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false === $encoded_url) {
            $errors[] = "No se pudo codificar el destino del CTA {$unique_id}.";
            return $content;
        }

        $updated_attrs = substr($block['attrs'], 0, -1) . ',"link":' . $encoded_url . '}';
        $updated_block = str_replace($block['attrs'], $updated_attrs, $block['full']);
        $changes[]     = "cta:{$unique_id}";

        return substr_replace($content, $updated_block, $block['offset'], strlen($block['full']));
    }
}

if (! function_exists('tmd_home_ctas_set_card_text')) {
    function tmd_home_ctas_set_card_text($content, $unique_id, $previous, $target, &$changes, &$errors) {
        $error = '';
        $block = tmd_home_ctas_find_block_comment($content, 'kadence/column', $unique_id, $error);

        if (null === $block) {
            $errors[] = $error;
            return $content;
        }

        $segment_end = strpos($content, '<!-- /wp:kadence/column -->', $block['offset']);
        if (false === $segment_end) {
            $errors[] = "El cierre de la tarjeta {$unique_id} no existe.";
            return $content;
        }

        $segment_end += strlen('<!-- /wp:kadence/column -->');
        $segment      = substr($content, $block['offset'], $segment_end - $block['offset']);
        $old_count    = substr_count($segment, $previous);
        $new_count    = substr_count($segment, $target);

        if (0 === $old_count && 1 === $new_count) {
            return $content;
        }

        if (1 !== $old_count || 0 !== $new_count) {
            $errors[] = sprintf(
                'La tarjeta %s contradice la precondición de texto (anterior=%d, final=%d).',
                $unique_id,
                $old_count,
                $new_count
            );
            return $content;
        }

        $updated_segment = str_replace($previous, $target, $segment);
        $changes[]       = "texto:{$unique_id}";

        return substr_replace($content, $updated_segment, $block['offset'], strlen($segment));
    }
}

if (! function_exists('tmd_transform_home_ctas')) {
    function tmd_transform_home_ctas($content) {
        $original = (string) $content;
        $working  = $original;
        $changes  = [];
        $errors   = [];

        foreach (tmd_home_ctas_button_targets() as $unique_id => $target_url) {
            $working = tmd_home_ctas_set_button_url(
                $working,
                $unique_id,
                $target_url,
                $changes,
                $errors
            );
        }

        foreach (tmd_home_ctas_text_targets() as $unique_id => $texts) {
            $working = tmd_home_ctas_set_card_text(
                $working,
                $unique_id,
                $texts['previous'],
                $texts['target'],
                $changes,
                $errors
            );
        }

        return [
            'content' => empty($errors) ? $working : $original,
            'changes' => empty($errors) ? $changes : [],
            'errors'  => $errors,
        ];
    }
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];

if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/update-home-ctas.php -- [dry-run|execute]');
}

$home_id = 47;
$home    = get_post($home_id);

if (! $home || 'page' !== $home->post_type) {
    WP_CLI::error("No existe la página de inicio esperada con ID {$home_id}.");
}

$original_content = (string) $home->post_content;
$result           = tmd_transform_home_ctas($original_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La migración se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('El contenido ya cumple el contrato; no hay cambios.');
    return;
}

WP_CLI::line('Cambios validados: ' . implode(', ', $result['changes']));
if (['execute'] !== $command_args) {
    WP_CLI::success('Dry-run correcto. No se escribió contenido.');
    return;
}

$updated_id = wp_update_post([
    'ID'           => $home_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $home_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la página de inicio: ' . $message);
}

clean_post_cache($home_id);
WP_CLI::success('Página de inicio actualizada de forma focalizada.');
