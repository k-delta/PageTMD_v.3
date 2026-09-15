<?php
/**
 * Actualiza de forma focalizada las vacantes de Trabaja con nosotros (ID 273).
 *
 * Sin argumentos o con "dry-run" solo valida la transformación. La escritura
 * requiere "execute" para evitar modificar contenido persistente por accidente.
 */

defined('ABSPATH') || exit;

function tmd_jobs_vacancies_section_bounds(string $content, array &$errors): ?array
{
    $pattern = '~<section\b(?=[^>]*\btmd-jobs-vacancies\b)[^>]*>~iu';
    $count = preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

    if (1 !== $count) {
        $errors[] = sprintf(
            'No se encontró una única sección tmd-jobs-vacancies (coincidencias=%d).',
            (int) $count
        );
        return null;
    }

    $start_tag = $matches[0][0][0];
    $start = $matches[0][0][1];
    $after_start = $start + strlen($start_tag);
    $end = strpos($content, '</section>', $after_start);

    if (false === $end) {
        $errors[] = 'No se encontró el cierre de la sección tmd-jobs-vacancies.';
        return null;
    }

    $end += strlen('</section>');

    return [$start, $end - $start];
}

function tmd_jobs_vacancies_card_html(string $badge, string $title, string $description): string
{
    return implode("\n", [
        '        <article class="tmd-job-card">',
        '          <div class="tmd-job-card-top">',
        '            <span class="tmd-job-badge">' . $badge . '</span>',
        '            <span class="tmd-job-time">Tiempo Completo</span>',
        '          </div>',
        '          <h3>' . $title . '</h3>',
        '          <p>' . $description . '</p>',
        '          <div class="tmd-job-meta">',
        '            <span>📍 Bogotá, D.C.</span>',
        '            <span>💰 Salario a convenir</span>',
        '          </div>',
        '          <a class="tmd-jobs-btn tmd-jobs-btn-outline" href="#postulacion">Aplicar a esta vacante</a>',
        '        </article>',
    ]);
}

function tmd_jobs_vacancies_normalize_card(string $card): string
{
    $normalized = preg_replace('/\s+/u', ' ', trim($card));

    return is_string($normalized) ? $normalized : '';
}

function tmd_jobs_vacancies_extract_card_signatures(string $section, array &$errors): array
{
    $pattern = '~<article\b(?=[^>]*\bclass="[^"]*\btmd-job-card\b[^"]*")[^>]*>.*?</article>~isu';
    $count = preg_match_all($pattern, $section, $matches);

    if (false === $count) {
        $errors[] = 'No fue posible analizar las tarjetas de vacantes.';
        return [];
    }

    return array_map(
        static function (string $card): string {
            return tmd_jobs_vacancies_normalize_card($card);
        },
        $matches[0]
    );
}

function tmd_jobs_vacancies_expected_card_signatures(): array
{
    $old_electric = tmd_jobs_vacancies_card_html(
        'Técnico',
        'Técnico en Mantenimiento de Montacargas',
        'Buscamos técnicos con experiencia en sistemas hidráulicos y motores de combustión para nuestra sede principal.'
    );
    $old_training = tmd_jobs_vacancies_card_html(
        'Administrativo',
        'Auxiliar Administrativo',
        'Responsable de la programación de servicios preventivos y hablar con clientes.'
    );
    $new_electric = tmd_jobs_vacancies_card_html(
        'Técnico',
        'Técnico Especializado en Montacargas Eléctricos',
        'Buscamos Técnico Especializado en Montacargas Eléctricos, con experiencia en diagnóstico, mantenimiento y reparación de sistemas eléctricos, electrónicos, electromecánicos e hidráulicos, para vincularse a nuestra sede principal.'
    );
    $new_training = tmd_jobs_vacancies_card_html(
        'Técnico',
        'Auxiliar Técnico en Entrenamiento',
        'Buscamos Auxiliar Técnico en Entrenamiento, con conocimientos básicos en electromecánica, electricidad o mecánica, disposición para aprender y crecer profesionalmente en el mantenimiento y reparación de montacargas. No se requiere experiencia.'
    );
    $new_combustion = tmd_jobs_vacancies_card_html(
        'Técnico',
        'Técnico Especializado en Montacargas de Combustión',
        'Buscamos Técnico Especializado en Montacargas de Combustión, con experiencia en diagnóstico, mantenimiento y reparación de motores, sistemas hidráulicos, transmisiones, frenos y componentes mecánicos. Vinculación para nuestra sede principal.'
    );

    return [
        'old' => [
            tmd_jobs_vacancies_normalize_card($old_electric),
            tmd_jobs_vacancies_normalize_card($old_training),
        ],
        'new' => [
            tmd_jobs_vacancies_normalize_card($new_electric),
            tmd_jobs_vacancies_normalize_card($new_training),
            tmd_jobs_vacancies_normalize_card($new_combustion),
        ],
    ];
}

function tmd_jobs_vacancies_fragments(): array
{
    $line_break = "\n";

    return [
        'electric' => [
            'old' => '<h3>Técnico en Mantenimiento de Montacargas</h3>' . $line_break
                . '          <p>Buscamos técnicos con experiencia en sistemas hidráulicos y motores de combustión para nuestra sede principal.</p>',
            'new' => '<h3>Técnico Especializado en Montacargas Eléctricos</h3>' . $line_break
                . '          <p>Buscamos Técnico Especializado en Montacargas Eléctricos, con experiencia en diagnóstico, mantenimiento y reparación de sistemas eléctricos, electrónicos, electromecánicos e hidráulicos, para vincularse a nuestra sede principal.</p>',
        ],
        'training' => [
            'old' => '<span class="tmd-job-badge">Administrativo</span>' . $line_break
                . '            <span class="tmd-job-time">Tiempo Completo</span>' . $line_break
                . '          </div>' . $line_break
                . '          <h3>Auxiliar Administrativo</h3>' . $line_break
                . '          <p>Responsable de la programación de servicios preventivos y hablar con clientes.</p>',
            'new' => '<span class="tmd-job-badge">Técnico</span>' . $line_break
                . '            <span class="tmd-job-time">Tiempo Completo</span>' . $line_break
                . '          </div>' . $line_break
                . '          <h3>Auxiliar Técnico en Entrenamiento</h3>' . $line_break
                . '          <p>Buscamos Auxiliar Técnico en Entrenamiento, con conocimientos básicos en electromecánica, electricidad o mecánica, disposición para aprender y crecer profesionalmente en el mantenimiento y reparación de montacargas. No se requiere experiencia.</p>',
        ],
        'combustion' => [
            'html' => tmd_jobs_vacancies_card_html(
                'Técnico',
                'Técnico Especializado en Montacargas de Combustión',
                'Buscamos Técnico Especializado en Montacargas de Combustión, con experiencia en diagnóstico, mantenimiento y reparación de motores, sistemas hidráulicos, transmisiones, frenos y componentes mecánicos. Vinculación para nuestra sede principal.'
            ),
        ],
    ];
}

function tmd_transform_jobs_vacancies(string $content): array
{
    $original = $content;
    $working = $content;
    $changes = [];
    $errors = [];
    $bounds = tmd_jobs_vacancies_section_bounds($working, $errors);

    if (empty($errors) && is_array($bounds)) {
        [$start, $length] = $bounds;
        $section = substr($working, $start, $length);
        $fragments = tmd_jobs_vacancies_fragments();
        $card_signatures = tmd_jobs_vacancies_extract_card_signatures($section, $errors);
        $expected_cards = tmd_jobs_vacancies_expected_card_signatures();

        if (empty($errors) && $card_signatures === $expected_cards['old']) {
            $section = str_replace(
                $fragments['electric']['old'],
                $fragments['electric']['new'],
                $section,
                $replacements
            );

            if (1 !== $replacements) {
                $errors[] = sprintf('La primera vacante no se reemplazó exactamente una vez (reemplazos=%d).', $replacements);
            }

            $section = str_replace(
                $fragments['training']['old'],
                $fragments['training']['new'],
                $section,
                $replacements
            );

            if (1 !== $replacements) {
                $errors[] = sprintf('La segunda vacante no se reemplazó exactamente una vez (reemplazos=%d).', $replacements);
            }

            $closing = "\n      </div>\n    </div>\n  </section>";
            $closing_count = substr_count($section, $closing);

            if (1 !== $closing_count) {
                $errors[] = sprintf('No se encontró un único cierre de la cuadrícula de vacantes (coincidencias=%d).', $closing_count);
            } else {
                $section = str_replace(
                    $closing,
                    "\n" . $fragments['combustion']['html'] . $closing,
                    $section,
                    $replacements
                );

                if (1 !== $replacements) {
                    $errors[] = sprintf('La tercera vacante no se insertó exactamente una vez (reemplazos=%d).', $replacements);
                }
            }

            if (empty($errors)) {
                $changes = ['vacante-electrica', 'vacante-entrenamiento', 'vacante-combustion'];
            }
        } elseif (empty($errors) && $card_signatures === $expected_cards['new']) {
            // La transformación ya fue aplicada.
        } else {
            $errors[] = 'La sección de vacantes no coincide exactamente con el estado anterior o final esperado; no se aplicaron cambios parciales.';
        }

        if (empty($errors)) {
            $working = substr_replace($working, $section, $start, $length);
        }
    }

    if (! empty($errors)) {
        $working = $original;
        $changes = [];
    }

    return [
        'content' => $working,
        'changes' => $changes,
        'errors'  => $errors,
        'changed' => $working !== $original,
    ];
}

function tmd_jobs_vacancies_update_page_atomically(int $page_id, string $original_content, string $updated_content): void
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb) || ! method_exists($wpdb, 'query')
        || ! method_exists($wpdb, 'get_row') || ! method_exists($wpdb, 'prepare')
    ) {
        WP_CLI::error('No está disponible la conexión de base de datos para proteger la escritura; no se escribió contenido.');
    }

    $transaction_started = false;

    try {
        if (false === $wpdb->query('START TRANSACTION')) {
            WP_CLI::error('No se pudo iniciar la transacción; no se escribió contenido.');
        }

        $transaction_started = true;
        $locked_page = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_type, post_content FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
            $page_id
        ));

        if (! $locked_page || 'page' !== $locked_page->post_type) {
            $wpdb->query('ROLLBACK');
            $transaction_started = false;
            WP_CLI::error('La página Trabaja con nosotros ya no está disponible; no se escribió contenido.');
        }

        if (! hash_equals(hash('sha256', $original_content), hash('sha256', (string) $locked_page->post_content))) {
            $wpdb->query('ROLLBACK');
            $transaction_started = false;
            WP_CLI::error('El contenido de la página cambió mientras se obtenía el bloqueo; se detuvo la escritura.');
        }

        $updated_id = wp_update_post([
            'ID'           => $page_id,
            'post_content' => $updated_content,
        ], true);

        if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
            $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
            $wpdb->query('ROLLBACK');
            $transaction_started = false;
            WP_CLI::error('No se pudo actualizar la página Trabaja con nosotros: ' . $message);
        }

        if (method_exists($wpdb, 'get_var')) {
            $persisted_content = $wpdb->get_var($wpdb->prepare(
                "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d",
                $page_id
            ));

            if ((string) $persisted_content !== $updated_content) {
                $wpdb->query('ROLLBACK');
                $transaction_started = false;
                WP_CLI::error('La verificación transaccional no coincide con el contenido esperado; se revirtió la escritura.');
            }
        }

        if (false === $wpdb->query('COMMIT')) {
            $wpdb->query('ROLLBACK');
            $transaction_started = false;
            WP_CLI::error('No se pudo confirmar la transacción; no se confirmó el cambio.');
        }

        $transaction_started = false;
    } catch (Throwable $exception) {
        if ($transaction_started) {
            $wpdb->query('ROLLBACK');
        }

        throw $exception;
    }
}

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

$command_args = isset($args) && is_array($args) ? array_values($args) : [];
if (! in_array($command_args, [[], ['dry-run'], ['execute']], true)) {
    WP_CLI::error('Uso: wp eval-file scripts/update-jobs-vacancies.php -- [dry-run|execute]');
}

$page_id = 273;
$page = get_post($page_id);

if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página Trabaja con nosotros esperada con ID {$page_id}.");
}

$result = tmd_transform_jobs_vacancies((string) $page->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización de vacantes se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (empty($result['changes'])) {
    WP_CLI::success('Las vacantes ya cumplen el contrato; no hay cambios.');
    return;
}

WP_CLI::line('Cambios validados: ' . implode(', ', $result['changes']));
WP_CLI::line('page_content_sha256=' . hash('sha256', (string) $page->post_content));
if (['execute'] !== $command_args) {
WP_CLI::success('Dry-run correcto. No se escribió contenido.');
return;
}

tmd_jobs_vacancies_update_page_atomically(
    $page_id,
    (string) $page->post_content,
    (string) $result['content']
);

clean_post_cache($page_id);
WP_CLI::success('Vacantes de Trabaja con nosotros actualizadas: ' . implode(', ', $result['changes']));
