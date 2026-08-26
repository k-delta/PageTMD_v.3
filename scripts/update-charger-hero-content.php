<?php
/**
 * Actualiza de forma idempotente la descripcion y composicion visual del hero
 * de /energia/cargadores/ (pagina 255).
 *
 * No modifica la imagen del hero ni otras secciones de la pagina.
 */

function tmd_transform_charger_hero_content(string $content): array
{
    $original = $content;
    $changes = [];
    $errors = [];

    $old_description = '<p class="tmd-energy-lead">Cargadores industriales seleccionados según voltaje, capacidad de batería, tipo de tecnología y rutina de operación, para mantener la disponibilidad de la flota eléctrica.</p>';
    $new_description = '<p class="tmd-energy-lead">Cargadores industriales seleccionados según el voltaje, la capacidad y la tecnología de la batería, así como las condiciones de operación, para garantizar una carga eficiente y contribuir a la disponibilidad de la flota eléctrica.</p>';

    $old_count = substr_count($content, $old_description);
    $new_count = substr_count($content, $new_description);

    if (1 === $old_count) {
        $content = str_replace($old_description, $new_description, $content, $replacements);
        if (1 !== $replacements) {
            $errors[] = sprintf('descripcion: se esperaba un reemplazo y se obtuvieron %d.', $replacements);
        } else {
            $changes[] = 'descripcion';
        }
    } elseif (0 === $old_count && $new_count >= 1) {
        // Ya actualizado.
    } else {
        $errors[] = sprintf('descripcion: precondicion invalida (anterior=%d, nueva=%d).', $old_count, $new_count);
    }

    $style_id = 'tmd-charger-hero-review-style';
    $style_block = <<<'HTML'
<!-- wp:html -->
<style id="tmd-charger-hero-review-style">
body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__grid {
    display: grid !important;
    grid-template-columns: minmax(0, .92fr) minmax(360px, 1.08fr) !important;
    gap: clamp(38px, 5vw, 68px) !important;
    align-items: center !important;
}

body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content {
    display: flex;
    min-width: 0;
    max-width: 560px;
    flex-direction: column;
    justify-content: center;
}

body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content h1 {
    max-width: 540px !important;
    margin: 0 !important;
    font-size: clamp(42px, 4.6vw, 64px) !important;
    line-height: 1.02 !important;
    letter-spacing: -.04em !important;
    text-wrap: balance;
}

body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content .tmd-energy-lead {
    max-width: 555px !important;
    margin: 22px 0 0 !important;
    font-size: 18px !important;
    line-height: 1.65 !important;
}

body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content .tmd-energy-actions {
    margin-top: 30px !important;
}

@media (max-width: 820px) {
    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__grid {
        grid-template-columns: 1fr !important;
        gap: 32px !important;
    }

    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content,
    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content h1,
    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content .tmd-energy-lead {
        max-width: 680px !important;
    }
}

@media (max-width: 560px) {
    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content h1 {
        font-size: 38px !important;
        line-height: 1.05 !important;
    }

    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content .tmd-energy-lead {
        margin-top: 18px !important;
        font-size: 16px !important;
        line-height: 1.6 !important;
    }

    body.page-id-255 .tmd-energy-inner--cargadores .tmd-energy-hero__content .tmd-energy-actions {
        margin-top: 24px !important;
    }
}
</style>
<!-- /wp:html -->
HTML;

    if (! str_contains($content, 'id="' . $style_id . '"')) {
        $content = $style_block . "\n\n" . ltrim($content);
        $changes[] = 'hero-estilo';
    } elseif (! str_contains($content, $style_block)) {
        $errors[] = 'hero-estilo: ya existe un bloque con el mismo ID pero contenido diferente.';
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
    WP_CLI::error("No existe la pagina de Cargadores esperada con ID {$page_id}.");
}

$result = tmd_transform_charger_hero_content((string) $page->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualizacion de Cargadores se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (! $result['changed']) {
    WP_CLI::success('El hero de Cargadores ya contiene la descripcion y el estilo solicitados; no hay cambios.');
    return;
}

$updated_id = wp_update_post([
    'ID' => $page_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar Cargadores: ' . $message);
}

clean_post_cache($page_id);
WP_CLI::success('Cargadores actualizado: ' . implode(', ', $result['changes']));
