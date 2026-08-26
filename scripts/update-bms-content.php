<?php
/**
 * Actualiza de forma idempotente los textos solicitados para /energia/bms/.
 * No modifica la imagen del menú, los nombres técnicos de equipos compatibles,
 * la sección "¿Cuándo conviene utilizar un BMS?" ni el CTA final.
 */

function tmd_bms_replace_once(string $content, string $label, string $old, string $new, array &$changes, array &$errors): string
{
    $old_count = substr_count($content, $old);
    $new_count = '' === $new ? 0 : substr_count($content, $new);

    if (1 === $old_count) {
        $updated = str_replace($old, $new, $content, $replacements);
        if (1 !== $replacements) {
            $errors[] = sprintf('%s: se esperó un reemplazo y se obtuvieron %d.', $label, $replacements);
            return $content;
        }

        $changes[] = $label;
        return $updated;
    }

    if (0 === $old_count && '' !== $new && $new_count >= 1) {
        return $content;
    }

    if (0 === $old_count && '' === $new) {
        return $content;
    }

    $errors[] = sprintf('%s: precondición inválida (anterior=%d, nuevo=%d).', $label, $old_count, $new_count);
    return $content;
}

function tmd_bms_content_replacements(): array
{
    return [
        ['hero-titulo', '<h1>BMS para monitoreo de baterías de montacargas</h1>', '<h1>BMS para baterías de montacargas</h1>'],
        ['hero-descripcion', '<p class="tmd-bms-lead">Dispositivo diseñado para medir, registrar y consultar el estado operativo de las baterías utilizadas en montacargas y otros equipos eléctricos de manejo de materiales.</p>', '<p class="tmd-bms-lead">Sistema de monitoreo que permite medir, registrar y consultar parámetros clave de la batería, facilitando el seguimiento de su operación y mantenimiento.</p>'],

        ['introduccion-titulo', '<h2>Comprenda cómo trabaja la batería</h2>', '<h2>Conozca el comportamiento de su batería</h2>'],
        ['introduccion-texto', '<p>El BMS proporciona información sobre el comportamiento de la batería durante la operación y el proceso de carga.</p>', '<p>El BMS registra información clave durante la operación y los ciclos de carga, facilitando la identificación de usos inadecuados y condiciones que pueden afectar el rendimiento y la vida útil de la batería.</p>'],
        ['introduccion-eliminar-repeticion', '<p>Estos datos facilitan la detección de usos inadecuados, fallas recurrentes y condiciones que pueden reducir su autonomía o vida útil.</p>', ''],

        ['variables-etiqueta', '<p class="tmd-bms-eyebrow">Variables registradas</p>', '<p class="tmd-bms-eyebrow">Variables monitoreadas</p>'],
        ['variables-descripcion', '<p>Dependiendo del modelo y de su configuración, el dispositivo puede proporcionar información relacionada con estas variables y eventos.</p>', '<p>Según el modelo y su configuración, el BMS permite registrar y consultar variables clave del funcionamiento de la batería:</p>'],
        ['variable-horas', '<article class="tmd-bms-metric"><span>05</span><strong>Horas de uso</strong></article>', '<article class="tmd-bms-metric"><span>05</span><strong>Horas de operación</strong></article>'],
        ['variable-ciclos', '<article class="tmd-bms-metric"><span>06</span><strong>Cantidad y duración de ciclos</strong></article>', '<article class="tmd-bms-metric"><span>06</span><strong>Ciclos de carga y descarga</strong></article>'],
        ['variable-historial', '<article class="tmd-bms-metric"><span>09</span><strong>Historial de utilización</strong></article>', '<article class="tmd-bms-metric"><span>09</span><strong>Historial de uso</strong></article>'],
        ['variable-alertas', '<article class="tmd-bms-metric"><span>10</span><strong>Eventos o condiciones anormales</strong></article>', '<article class="tmd-bms-metric"><span>10</span><strong>Alertas y condiciones anormales</strong></article>'],
        ['nota-tecnica', '<p class="tmd-bms-note"><strong>Nota técnica:</strong> las funciones disponibles deben confirmarse según el modelo y la configuración del dispositivo comercializado.</p>', '<p class="tmd-bms-note"><strong>Nota técnica:</strong> Las variables y funciones disponibles pueden variar según el modelo y la configuración del BMS.</p>'],

        ['monitoreo-1', '<p>El BMS permite hacer seguimiento al comportamiento de la batería durante la jornada de trabajo.</p>', '<p>El BMS permite monitorear el comportamiento de la batería durante la operación, ayudando a identificar descargas profundas, cargas incompletas y prácticas de uso que pueden afectar su rendimiento y vida útil.</p>'],
        ['monitoreo-2', '<p>La información recopilada ayuda a verificar si se utiliza dentro de las condiciones recomendadas y permite detectar prácticas que pueden acelerar su deterioro, como descargas profundas, ciclos de carga incompletos o uso continuo sin tiempos adecuados de recuperación.</p>', '<p>Es especialmente útil en flotas de equipos eléctricos y operaciones de alta exigencia.</p>'],
        ['monitoreo-eliminar-repeticion', '<p>Este monitoreo es especialmente útil en operaciones con varios equipos, jornadas prolongadas o baterías sometidas a una alta frecuencia de trabajo.</p>', ''],

        ['rendimiento-intro', '<p>El dispositivo ofrece información que ayuda a evaluar el desempeño general de la batería e identificar posibles pérdidas de autonomía.</p>', '<p>El BMS proporciona datos para evaluar el desempeño y la autonomía de la batería, permitiendo identificar condiciones como:</p>'],
        ['rendimiento-eliminar-intro-2', '<p>Mediante el análisis de sus mediciones es posible detectar situaciones como:</p>', ''],
        ['rendimiento-li-1', '<li>Reducción progresiva del tiempo de operación.</li>', '<li>Pérdida progresiva de autonomía.</li>'],
        ['rendimiento-li-2', '<li>Temperaturas fuera del comportamiento esperado.</li>', '<li>Temperaturas fuera de rango.</li>'],
        ['rendimiento-li-3', '<li>Descargas más profundas de lo recomendado.</li>', '<li>Descargas profundas.</li>'],
        ['rendimiento-li-4', '<li>Procesos de carga deficientes.</li>', '<li>Cargas incompletas o inadecuadas.</li>'],
        ['rendimiento-li-5', '<li>Uso excesivo del equipo.</li>', '<li>Uso intensivo de la batería.</li>'],
        ['rendimiento-li-6', '<li>Diferencias entre la operación planificada y el uso real.</li>', '<li>Comportamientos anormales durante la operación.</li>'],
        ['rendimiento-eliminar-li', '<li>Posibles fallas en la batería o en el cargador.</li>', ''],
        ['rendimiento-cierre', '<p>El BMS no reemplaza la inspección técnica, pero proporciona datos para orientar el diagnóstico y evitar decisiones basadas únicamente en percepciones del operador.</p>', '<p>El BMS complementa la inspección técnica, proporcionando información objetiva para facilitar el diagnóstico y la toma de decisiones.</p>'],

        ['diagnostico-intro', '<p>El diagnóstico consiste en revisar las mediciones y los registros obtenidos por el dispositivo para comprender cómo se ha utilizado la batería.</p>', '<p>El diagnóstico permite analizar las mediciones y registros obtenidos por el dispositivo para evaluar el comportamiento y las condiciones de uso de la batería.</p>'],
        ['diagnostico-subtitulo', '<p>Esta información puede ayudar a:</p>', '<p>Esta información permite:</p>'],
        ['diagnostico-li-1', '<li>Identificar hábitos incorrectos de carga.</li>', '<li>Identificar prácticas inadecuadas de carga.</li>'],
        ['diagnostico-li-2', '<li>Detectar descargas profundas frecuentes.</li>', '<li>Detectar descargas profundas y pérdidas de autonomía.</li>'],
        ['diagnostico-eliminar-li-3', '<li>Investigar pérdidas de autonomía.</li>', ''],
        ['diagnostico-li-4', '<li>Determinar si una falla está relacionada con la batería, el cargador o la operación.</li>', '<li>Determinar si una falla está asociada a la batería, al cargador o a la operación del equipo.</li>'],
        ['diagnostico-li-6', '<li>Comparar el rendimiento entre diferentes equipos.</li>', '<li>Evaluar el rendimiento de las baterías de la flota.</li>'],
        ['diagnostico-li-7', '<li>Reducir sustituciones innecesarias.</li>', '<li>Evitar reemplazos innecesarios y optimizar su vida útil.</li>'],
        ['diagnostico-eliminar-li-8', '<li>Mejorar el control de las baterías de una flota.</li>', ''],

        ['beneficios-introduccion', '<p>Información útil para anticipar problemas, mejorar el control y orientar el mantenimiento.</p>', '<p>El monitoreo BMS proporciona información clave sobre el comportamiento de la batería, facilitando la detección temprana de anomalías y una gestión más eficiente del mantenimiento.</p>'],
        ['beneficio-1', '<article class="tmd-bms-benefit"><span>01</span><h3>Mantenimiento basado en información</h3><p>Permite programar revisiones a partir del comportamiento real de la batería, en lugar de depender únicamente de fechas o inspecciones ocasionales.</p></article>', '<article class="tmd-bms-benefit"><span>01</span><h3>Mantenimiento basado en datos</h3><p>Permite programar intervenciones de acuerdo con el comportamiento y las condiciones reales de operación de la batería.</p></article>'],
        ['beneficio-2', '<article class="tmd-bms-benefit"><span>02</span><h3>Detección temprana de problemas</h3><p>Ayuda a identificar condiciones anormales antes de que ocasionen una falla completa o una interrupción prolongada.</p></article>', '<article class="tmd-bms-benefit"><span>02</span><h3>Detección temprana de anomalías</h3><p>Facilita la identificación de condiciones anormales antes de que generen fallas o afecten la disponibilidad del equipo.</p></article>'],
        ['beneficio-3', '<article class="tmd-bms-benefit"><span>03</span><h3>Mayor control de la operación</h3><p>Facilita conocer cómo se utilizan las baterías, cuánto trabajan y bajo qué condiciones se realizan los ciclos.</p></article>', '<article class="tmd-bms-benefit"><span>03</span><h3>Mayor control operativo</h3><p>Permite conocer los ciclos de carga y descarga, tiempos de uso y condiciones de funcionamiento de la batería.</p></article>'],
        ['beneficio-4', '<article class="tmd-bms-benefit"><span>04</span><h3>Reducción de tiempos de inactividad</h3><p>Un diagnóstico más preciso puede agilizar la identificación de fallas y disminuir el tiempo del montacargas fuera de servicio.</p></article>', '<article class="tmd-bms-benefit"><span>04</span><h3>Reducción de tiempos de inactividad</h3><p>Facilita un diagnóstico más preciso, agilizando la identificación de fallas y reduciendo las paradas del equipo.</p></article>'],
        ['beneficio-5', '<article class="tmd-bms-benefit"><span>05</span><h3>Mejor aprovechamiento de la batería</h3><p>El seguimiento de hábitos de uso y carga puede contribuir a conservar su rendimiento y evitar desgaste acelerado.</p></article>', '<article class="tmd-bms-benefit"><span>05</span><h3>Optimización de la vida útil</h3><p>El seguimiento de las condiciones de uso y carga ayuda a prevenir el desgaste prematuro y mantener el rendimiento de la batería.</p></article>'],
    ];
}

function tmd_transform_bms_content(string $content): array
{
    $original = $content;
    $changes = [];
    $errors = [];

    foreach (tmd_bms_content_replacements() as [$label, $old, $new]) {
        $content = tmd_bms_replace_once($content, $label, $old, $new, $changes, $errors);
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

$page_id = 792;
$page = get_post($page_id);

if (! $page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página BMS esperada con ID {$page_id}.");
}

$result = tmd_transform_bms_content((string) $page->post_content);

if (! empty($result['errors'])) {
    WP_CLI::error("La actualización BMS se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}

if (! $result['changed']) {
    WP_CLI::success('La página BMS ya contiene los textos solicitados; no hay cambios.');
    return;
}

$updated_id = wp_update_post([
    'ID' => $page_id,
    'post_content' => $result['content'],
], true);

if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la página BMS: ' . $message);
}

clean_post_cache($page_id);
WP_CLI::success('BMS actualizado: ' . implode(', ', $result['changes']));
