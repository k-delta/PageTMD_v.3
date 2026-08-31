<?php
/**
 * Prepara la versión final de la Política de Tratamiento y Protección de Datos.
 *
 * El documento de revisión exige confirmar teléfono, fecha de entrada en vigor
 * y área responsable antes de publicar. Por eso el script no escribe si falta
 * alguno de esos datos.
 *
 * Ejemplos:
 * wp eval-file scripts/update-privacy-policy-final.php -- dry-run --phone="+57 ..." --effective-date="..." --responsible-area="..."
 * wp eval-file scripts/update-privacy-policy-final.php -- execute --phone="+57 ..." --effective-date="..." --responsible-area="..."
 */

defined('ABSPATH') || exit;

function tmd_privacy_review_parse_args(array $args): array
{
    $parsed = [
        'mode' => 'dry-run',
        'phone' => '',
        'effective-date' => '',
        'responsible-area' => '',
    ];

    foreach ($args as $argument) {
        $argument = (string) $argument;
        if ('--' === $argument || '' === $argument) {
            continue;
        }
        if (in_array($argument, ['dry-run', 'execute'], true)) {
            $parsed['mode'] = $argument;
            continue;
        }
        if (preg_match('/^--(phone|effective-date|responsible-area)=(.*)$/', $argument, $matches)) {
            $parsed[$matches[1]] = trim($matches[2]);
        }
    }

    return $parsed;
}

function tmd_privacy_review_target_article(array $options): string
{
    $phone = esc_html($options['phone']);
    $effective_date = esc_html($options['effective-date']);
    $responsible_area = esc_html($options['responsible-area']);

    return <<<HTML
<article class="tmd-legal-article">
  <div class="tmd-legal-wrap">
    <h2>Protección de la información personal</h2>
    <p>En TECNIMONTACARGAS DUAL S.A.S. reconocemos la importancia de proteger los datos personales de nuestros clientes, proveedores, colaboradores, contratistas, candidatos y usuarios.</p>
    <p>Esta política establece los lineamientos para la recolección, almacenamiento, uso, circulación, actualización y supresión de datos personales, de conformidad con la Ley 1581 de 2012 y las demás normas colombianas aplicables.</p>

    <h2>Responsable del tratamiento</h2>
    <p><strong>TECNIMONTACARGAS DUAL S.A.S.</strong><br>
    <strong>NIT:</strong> 900.197.587-1<br>
    <strong>Dirección:</strong> Carrera 108 # 22F-21, barrio Versalles, Fontibón, Bogotá D. C., Colombia<br>
    <strong>Correo electrónico:</strong> <a href="mailto:info@tmdual.com">info@tmdual.com</a><br>
    <strong>Teléfono:</strong> {$phone}</p>

    <h2>Finalidades del tratamiento</h2>
    <p>Los datos personales podrán utilizarse para:</p>
    <ul>
      <li>Atender solicitudes, asesorías, cotizaciones y PQR.</li>
      <li>Gestionar relaciones comerciales y contractuales.</li>
      <li>Prestar servicios de mantenimiento, alquiler y venta.</li>
      <li>Realizar procesos administrativos, contables y de facturación.</li>
      <li>Gestionar relaciones laborales y procesos de selección.</li>
      <li>Contactar a clientes, proveedores y demás interesados.</li>
      <li>Enviar información comercial, promociones y novedades, previa autorización.</li>
      <li>Cumplir obligaciones legales, contractuales y de seguridad.</li>
      <li>Evaluar la calidad de nuestros productos y servicios.</li>
    </ul>

    <h2>Autorización</h2>
    <p>El tratamiento de los datos personales se realizará con la autorización previa, expresa e informada del titular, salvo las excepciones establecidas por la ley.</p>
    <p>El titular podrá abstenerse de autorizar el tratamiento de datos sensibles o de menores de edad, excepto cuando su tratamiento sea necesario y esté permitido legalmente.</p>

    <h2>Derechos de los titulares</h2>
    <p>Los titulares tienen derecho a:</p>
    <ul>
      <li>Conocer, actualizar y rectificar sus datos.</li>
      <li>Solicitar prueba de la autorización otorgada.</li>
      <li>Conocer el uso dado a su información.</li>
      <li>Presentar consultas y reclamos.</li>
      <li>Solicitar la supresión de sus datos.</li>
      <li>Revocar la autorización cuando sea procedente.</li>
      <li>Acceder gratuitamente a sus datos personales.</li>
      <li>Presentar quejas ante la Superintendencia de Industria y Comercio, una vez agotado el trámite ante la empresa.</li>
    </ul>

    <h2>Consultas y reclamos</h2>
    <p>El área responsable de atender las solicitudes relacionadas con datos personales es: <strong>{$responsible_area}</strong>.</p>
    <p>Las consultas y reclamos podrán enviarse a <a href="mailto:info@tmdual.com">info@tmdual.com</a>, indicando:</p>
    <ul>
      <li>Nombre e identificación del titular.</li>
      <li>Datos de contacto.</li>
      <li>Descripción de la solicitud o reclamo.</li>
      <li>Documentos que respalden la petición, cuando corresponda.</li>
    </ul>
    <p>Las consultas serán atendidas dentro de los diez días hábiles siguientes a su recepción. Los reclamos serán resueltos dentro de los quince días hábiles siguientes, de acuerdo con los términos legales aplicables.</p>

    <h2>Seguridad y confidencialidad</h2>
    <p>TECNIMONTACARGAS DUAL S.A.S. adopta medidas administrativas, técnicas y organizacionales para prevenir la pérdida, adulteración, consulta, uso, divulgación o acceso no autorizado a los datos personales.</p>
    <p>Las personas que intervengan en el tratamiento de la información deberán mantener su confidencialidad, incluso después de finalizar su relación con la empresa.</p>

    <h2>Transferencia y transmisión de datos</h2>
    <p>La información podrá compartirse con proveedores, contratistas o aliados cuando sea necesario para prestar un servicio, cumplir una obligación legal o desarrollar las finalidades autorizadas. Estos terceros deberán proteger la información y utilizarla únicamente para los fines establecidos.</p>

    <h2>Ámbito de aplicación</h2>
    <p>Esta política aplica a todas las bases de datos y archivos que contengan información personal y sobre los cuales TECNIMONTACARGAS DUAL S.A.S. actúe como responsable o encargado del tratamiento.</p>

    <h2>Vigencia</h2>
    <p>Esta política entra en vigor a partir del {$effective_date}. Las bases de datos permanecerán vigentes durante el tiempo necesario para cumplir las finalidades autorizadas y las obligaciones legales o contractuales correspondientes.</p>
    <p>Cualquier modificación sustancial será informada a través de nuestros canales oficiales.</p>
  </div>
</article>
HTML;
}

function tmd_privacy_review_transform(string $content, array $options): array
{
    $target = tmd_privacy_review_target_article($options);
    if (false !== strpos($content, $target)) {
        return ['content' => $content, 'changed' => false, 'errors' => []];
    }

    if (1 !== preg_match_all('#<article class="tmd-legal-article">.*?</article>#s', $content)) {
        return [
            'content' => $content,
            'changed' => false,
            'errors' => ['No se encontró exactamente un artículo legal para reemplazar.'],
        ];
    }

    $updated = preg_replace('#<article class="tmd-legal-article">.*?</article>#s', $target, $content, 1);
    if (!is_string($updated) || $updated === $content) {
        return [
            'content' => $content,
            'changed' => false,
            'errors' => ['No fue posible construir el contenido final de privacidad.'],
        ];
    }

    return ['content' => $updated, 'changed' => true, 'errors' => []];
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

$options = tmd_privacy_review_parse_args(isset($args) && is_array($args) ? $args : []);
foreach (['phone', 'effective-date', 'responsible-area'] as $required) {
    if ('' === $options[$required]) {
        WP_CLI::error('Falta confirmar --' . $required . '. El documento de revisión prohíbe publicar esta política sin esos datos.');
    }
}

$page_id = 358;
$page = get_post($page_id);
if (!$page || 'page' !== $page->post_type) {
    WP_CLI::error("No existe la página de privacidad esperada con ID {$page_id}.");
}

$result = tmd_privacy_review_transform((string) $page->post_content, $options);
if (!empty($result['errors'])) {
    WP_CLI::error("La actualización se detuvo sin escribir:\n- " . implode("\n- ", $result['errors']));
}
if (!$result['changed']) {
    WP_CLI::success('La política de privacidad ya contiene la versión final confirmada.');
    return;
}
if ('execute' !== $options['mode']) {
    WP_CLI::success('Dry-run correcto. Los datos obligatorios están confirmados y no se escribió contenido.');
    return;
}

$updated_id = wp_update_post([
    'ID' => $page_id,
    'post_content' => $result['content'],
], true);
if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) {
    $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.';
    WP_CLI::error('No se pudo actualizar la política de privacidad: ' . $message);
}

clean_post_cache($page_id);
WP_CLI::success('Política de privacidad actualizada con los datos confirmados.');
