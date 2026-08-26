<?php
/**
 * Actualiza de forma idempotente la sección "Compatibilidad antes que velocidad"
 * de /energia/cargadores/ (página 255).
 *
 * Cambios limitados a:
 * - introducción centrada bajo el título;
 * - "Voltaje correcto" -> "Voltaje compatible";
 * - nueva redacción de las tres tarjetas.
 */

function tmd_charger_compatibility_replace_once(
    string $content,
    string $label,
    string $old,
    string $new,
    array &$changes,
    array &$errors
): string {
    $old_count = substr_count($content, $old);
    $new_count = substr_count($content, $new);

    if (1 === $old_count) {
        $updated = str_replace($old, $new, $content, $replacements);
        if (1 !== $replacements) {
            $errors[] = sprintf('%s: se esperaba un reemplazo y se obtuvieron %d.', $label, $replacements);
            return $content;
        }

        $changes[] = $label;
        return $updated;
    }

    if (0 === $old_count && $new_count >= 1) {
        return $content;
    }

    $errors[] = sprintf('%s: precondición inválida (anterior=%d, nuevo=%d).', $label, $old_count, $new_count);
    return $content;
}

function tmd_transform_charger_compatibility_content(string $content): array
{
    $original = $content;
    $changes = [];
    $errors = [];

    $intro_class = 'tmd-energy-compatibility-intro';
    $intro_text = 'Seleccionar el cargador adecuado es fundamental para garantizar una carga segura y eficiente, sin afectar el rendimiento ni la vida útil de la batería.';

    if (! str_contains($content, $intro_class)) {
        $heading_pattern = '#(<h2\b[^>]*>)(\s*Compatibilidad antes que velocidad\s*)(</h2>)#u';
        $heading_matches = preg_match_all($heading_pattern, $content, $matches);

        if (1 !== $heading_matches) {
            $errors[] = sprintf('introducción: se esperaba un título de compatibilidad y se encontraron %d.', $heading_matches ?: 0);
        } else {
            $intro = '<span class="' . $intro_class . '" style="display:block;max-width:760px;margin:16px auto 0;color:#5e748b;font-size:clamp(15px,1.6vw,17px);font-weight:400;line-height:1.6;letter-spacing:0;">' . $intro_text . '</span>';
            $content = preg_replace($heading_pattern, '$1$2' . $intro . '$3', $content, 1, $replacements);

            if (1 !== $replacements || ! is_string($content)) {
                $errors[] = 'introducción: no se pudo insertar el texto debajo del título.';
            } else {
                $changes[] = 'introducción';
            }
        }
    } elseif (! str_contains($content, $intro_text)) {
        $errors[] = 'introducción: existe la clase esperada, pero contiene un texto diferente.';
    }

    $replacements = [
        [
            'voltaje-título',
            '>Voltaje correcto<',
            '>Voltaje compatible<',
        ],
        [
            'voltaje-texto',
            'El cargador debe coincidir con el voltaje nominal de la batería y del equipo para evitar daños o cargas incompletas.',
            'El cargador debe corresponder al voltaje nominal y a la tecnología de la batería para garantizar un proceso de carga adecuado.',
        ],
        [
            'capacidad-texto',
            'La corriente de carga se define según la capacidad de la batería, el tiempo disponible y el tipo de operación.',
            'La corriente de carga debe definirse según la capacidad de la batería, su tecnología y el tiempo disponible para completar el ciclo.',
        ],
        [
            'instalación-texto',
            'Revisamos ventilación, conexión eléctrica, protecciones, espacio de carga y buenas prácticas para operación diaria.',
            'Se deben verificar la alimentación eléctrica, las protecciones, la ventilación y las condiciones del área de carga para garantizar una operación segura.',
        ],
    ];

    foreach ($replacements as [$label, $old, $new]) {
        $content = tmd_charger_compatibility_replace_once($content, $label, $old, $new, $changes, $errors);
    }

    return [
        'content' => $content,
        'changes' => $changes,
        'errors' => $errors,
        'changed' => $content !== $original,
    ];
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$page_id = 255;
$page = get_post($page_id);

if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página de Cargadores esperada con ID {$page_id}.");
}

$result = tmd_transform_charger_compatibility_content((string) $page->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización de compatibilidad se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (! $result['changed']) {
    WP_CLI::success('La sección de compatibilidad ya contiene los textos solicitados; no hay cambios.');
    return;
}

$updated_id = wp_update_post([
    'ID' => $page_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la sección de compatibilidad: ' . $message);
}

clean_post_cache($page_id);
WP_CLI::success('Compatibilidad de Cargadores actualizada: ' . implode(', ', $result['changes']));
