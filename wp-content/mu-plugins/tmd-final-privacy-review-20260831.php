<?php
/** Final privacy policy and routing for management/legal communications. */
defined('ABSPATH') || exit;

if (!function_exists('tmd_management_email')) {
    function tmd_management_email(): string
    {
        return 'gerencia@tmdual.com';
    }
}

add_filter('the_content', static function (string $content): string {
    if (!is_page(358)) {
        return $content;
    }

    $management_email = esc_html(tmd_management_email());
    $management_mailto = esc_attr('mailto:' . tmd_management_email());

    $article = <<<HTML
<article class="tmd-legal-article">
  <div class="tmd-legal-wrap">
    <h2>Protección de la información personal</h2>
    <p>En TECNIMONTACARGAS DUAL S.A.S. reconocemos la importancia de proteger los datos personales de nuestros clientes, proveedores, colaboradores, contratistas, candidatos y usuarios.</p>
    <p>Esta política establece los lineamientos para la recolección, almacenamiento, uso, circulación, actualización y supresión de datos personales, de conformidad con la Ley 1581 de 2012 y las demás normas colombianas aplicables.</p>

    <h2>Responsable del tratamiento</h2>
    <p><strong>TECNIMONTACARGAS DUAL S.A.S.</strong><br>
    <strong>NIT:</strong> 900.197.587-1<br>
    <strong>Dirección:</strong> Carrera 108 # 22F-21, barrio Versalles, Fontibón, Bogotá D. C., Colombia<br>
    <strong>Correo electrónico:</strong> <a href="{$management_mailto}">{$management_email}</a><br>
    <strong>Teléfono:</strong> +57 3015556180</p>

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
    <p>El área responsable de atender las solicitudes relacionadas con datos personales es: <strong>Gerencia de TECNIMONTACARGAS DUAL S.A.S.</strong>.</p>
    <p>Las consultas y reclamos podrán enviarse a <a href="{$management_mailto}">{$management_email}</a>, indicando:</p>
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
    <p>Esta política entra en vigor a partir del 1 de enero de 2027. Las bases de datos permanecerán vigentes durante el tiempo necesario para cumplir las finalidades autorizadas y las obligaciones legales o contractuales correspondientes.</p>
    <p>Cualquier modificación sustancial será informada a través de nuestros canales oficiales.</p>
  </div>
</article>
HTML;

    $updated = preg_replace('#<article class="tmd-legal-article">.*?</article>#s', $article, $content, 1);

    return is_string($updated) && $updated !== $content ? $updated : $content;
}, 120);

/**
 * The dedicated PQR endpoint already targets management. This also protects the
 * generic Site Kit PQR route, while informational/contact, quote and quiz forms
 * keep using the general info mailbox.
 */
add_filter('wp_mail', static function (array $mail): array {
    $subject = isset($mail['subject']) ? (string) $mail['subject'] : '';

    if ('Nueva solicitud TMD: PQR' === $subject) {
        $mail['to'] = tmd_management_email();
    }

    return $mail;
}, 20);
