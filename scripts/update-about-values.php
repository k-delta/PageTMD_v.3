<?php
/**
 * Actualiza los valores corporativos de la página "Quiénes somos" de forma idempotente.
 *
 * Validación local sin escritura:
 * wp eval-file scripts/update-about-values.php -- dry-run
 *
 * Ejecución autorizada, después de backup:
 * wp eval-file scripts/update-about-values.php -- execute
 */

if (! function_exists('tmd_about_values_target_grid')) {
    function tmd_about_values_target_grid() {
        return <<<'HTML'
<div class="tmd-about__values-grid">
      <article class="tmd-about__value">
        <span aria-hidden="true">01</span>
        <h3>Excelencia con Propósito</h3>
        <p>La excelencia es nuestro hábito diario. Desarrollamos soluciones confiables e innovadoras impulsadas por la mejora continua a través del ciclo PEDEM (Planear, Ejecutar, Documentar, Evaluar y Mejorar). Trabajamos con actitud proactiva para perfeccionar nuestros procesos, garantizar la seguridad en cada operación y brindar un servicio eficiente que supere las expectativas de nuestros clientes.</p>
      </article>
      <article class="tmd-about__value">
        <span aria-hidden="true">02</span>
        <h3>Compromiso con el Progreso Sostenible</h3>
        <p>Impulsamos la productividad y competitividad de nuestros clientes con una visión a largo plazo. Pensamos y ejecutamos con enfoque en resultados: cada colaborador es un embajador responsable de optimizar recursos, cuidar el negocio y generar valor, promoviendo siempre una gestión sostenible que proteja el medio ambiente y beneficie a la sociedad.</p>
      </article>
      <article class="tmd-about__value">
        <span aria-hidden="true">03</span>
        <h3>Confiabilidad Inquebrantable</h3>
        <p>Construimos relaciones duraderas basadas en la ética, la transparencia y la responsabilidad. Nos distingue la pasión y el compromiso absoluto de nuestro equipo, cuya entrega diaria y sentido de pertenencia impulsan a la empresa hacia adelante. Respaldamos las operaciones de nuestros clientes con respuestas oportunas, consolidándonos como un aliado estratégico que transmite tranquilidad y genera experiencias memorables en cada interacción.</p>
      </article>
    </div>
HTML;
    }
}

if (! function_exists('tmd_about_values_find_grid')) {
    function tmd_about_values_find_grid($content, &$error) {
        $marker = '<div class="tmd-about__values-grid">';
        $count  = substr_count($content, $marker);

        if (1 !== $count) {
            $error = sprintf('Se esperaba una única grilla de valores corporativos; encontradas: %d.', $count);
            return null;
        }

        $offset = strpos($content, $marker);
        if (false === $offset) {
            $error = 'No se pudo localizar el inicio de la grilla de valores corporativos.';
            return null;
        }

        $end = strpos($content, '</div>', $offset);
        if (false === $end) {
            $error = 'No se pudo localizar el cierre de la grilla de valores corporativos.';
            return null;
        }

        $end += strlen('</div>');

        return [
            'full'   => substr($content, $offset, $end - $offset),
            'offset' => $offset,
        ];
    }
}

if (! function_exists('tmd_transform_about_values')) {
    function tmd_transform_about_values($content) {
        $original = (string) $content;
        $error    = '';
        $grid     = tmd_about_values_find_grid($original, $error);

        if (null === $grid) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => [$error],
            ];
        }

        $target = tmd_about_values_target_grid();
        if ($grid['full'] === $target) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => [],
            ];
        }

        $previous_titles = [
            'Sentido de pertenencia',
            'Pasión en nuestro trabajo',
            'Responsabilidad',
            'Respeto al medio ambiente',
            'Honestidad',
        ];

        if (5 !== substr_count($grid['full'], '<article class="tmd-about__value">')) {
            return [
                'content' => $original,
                'changes' => [],
                'errors'  => ['La grilla actual no contiene exactamente los cinco valores esperados.'],
            ];
        }

        foreach ($previous_titles as $title) {
            if (1 !== substr_count($grid['full'], '<h3>' . $title . '</h3>')) {
                return [
                    'content' => $original,
                    'changes' => [],
                    'errors'  => [sprintf('La precondición del valor "%s" no se cumple.', $title)],
                ];
            }
        }

        $updated = substr_replace($original, $target, $grid['offset'], strlen($grid['full']));

        return [
            'content' => $updated,
            'changes' => ['corporate-values'],
            'errors'  => [],
        ];
    }
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];

if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/update-about-values.php -- [dry-run|execute]');
}

$page_id = 278;
$page    = get_post($page_id);

if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página 'Quiénes somos' esperada con ID {$page_id}.");
}

$result = tmd_transform_about_values((string) $page->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La migración se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('Los valores corporativos ya cumplen el contrato; no hay cambios.');
    return;
}

WP_CLI::line('Cambios validados: ' . implode(', ', $result['changes']));
if (['execute'] !== $command_args) {
    WP_CLI::success('Dry-run correcto. No se escribió contenido.');
    return;
}

$updated_id = wp_update_post([
    'ID'           => $page_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error("No se pudo actualizar la página 'Quiénes somos': " . $message);
}

clean_post_cache($page_id);
WP_CLI::success('Valores corporativos actualizados de forma focalizada.');
