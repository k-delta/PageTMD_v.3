<?php
/** Final company copy requested in the August 31 review document. */
defined('ABSPATH') || exit;

add_filter('the_content', static function (string $content): string {
    if (!is_page()) {
        return $content;
    }

    $page_id = (int) get_queried_object_id();

    if (278 === $page_id) {
        $content = strtr($content, [
            'Nuestra dirección empresarial nace del servicio especializado, la mejora continua y el compromiso con cada cliente.' => 'Nuestra filosofía empresarial se fundamenta en el servicio especializado, la mejora continua y el compromiso permanente con cada uno de nuestros clientes.',
            'Ser un aliado estratégico para nuestros clientes en el mantenimiento, alquiler y venta de montacargas, contribuyendo al desarrollo productivo de la región con servicio especializado y altos estándares de calidad.' => 'Ser un aliado estratégico para nuestros clientes, brindando soluciones especializadas en el mantenimiento, alquiler y venta de montacargas, que contribuyan al desarrollo productivo de sus operaciones, mediante un servicio confiable, oportuno y con altos estándares de calidad.',
            'Consolidar nuestra marca y ser un referente nacional en el sector de mantenimiento, alquiler y venta de montacargas, actuando de manera sostenible, mejorando continuamente nuestros procesos y facilitando el acceso a recursos y soluciones a la vanguardia global.' => 'Consolidar nuestra marca y posicionarnos como un referente nacional en el sector del mantenimiento, alquiler y venta de montacargas, mediante una gestión sostenible, la mejora continua de nuestros procesos y la implementación de soluciones innovadoras alineadas con las tendencias y tecnologías del mercado global.',
        ]);

        $values = <<<'HTML'
<section class="tmd-about__values" aria-labelledby="tmd-about-values-title">
  <div class="tmd-about__values-heading">
    <div><p class="tmd-about__eyebrow">Lo que nos guía</p><h2 id="tmd-about-values-title">Valores corporativos</h2></div>
    <p>Son los principios que orientan nuestro trabajo, respaldan nuestras decisiones y fortalecen la relación con nuestros clientes, aliados estratégicos y equipo humano.</p>
  </div>
  <div class="tmd-about__values-grid">
    <article class="tmd-about__value"><span>01</span><h3>Excelencia con propósito</h3><p>La excelencia es parte de nuestro trabajo diario. Desarrollamos soluciones confiables e innovadoras, impulsadas por la mejora continua mediante el ciclo PEDEM: Planear, Ejecutar, Documentar, Evaluar y Mejorar.</p><p>Actuamos de manera proactiva para optimizar nuestros procesos, garantizar la seguridad en cada operación y brindar un servicio técnico eficiente y de calidad que responda a las necesidades y supere las expectativas de nuestros clientes.</p></article>
    <article class="tmd-about__value"><span>02</span><h3>Compromiso con el progreso sostenible</h3><p>Contribuimos a la productividad y competitividad de nuestros clientes mediante soluciones responsables y una visión a largo plazo. Orientamos nuestras acciones al cumplimiento de resultados, entendiendo que cada colaborador desempeña un papel fundamental en el uso eficiente de los recursos, el cuidado de la organización y la generación de valor.</p><p>Promovemos una gestión sostenible que contribuya a la protección del medio ambiente, al bienestar de la sociedad y al crecimiento responsable de nuestra empresa y de nuestros clientes.</p></article>
    <article class="tmd-about__value"><span>03</span><h3>Confiabilidad inquebrantable</h3><p>Construimos relaciones sólidas y duraderas, fundamentadas en la ética, la transparencia, el cumplimiento y la responsabilidad. La pasión, el compromiso y el sentido de pertenencia de nuestro equipo impulsan el crecimiento de la empresa y respaldan la calidad de cada servicio.</p><p>Respondemos de manera oportuna y confiable a las necesidades operativas de nuestros clientes, consolidándonos como un aliado estratégico que brinda respaldo, seguridad y tranquilidad en cada intervención.</p></article>
  </div>
</section>
HTML;
        $content = preg_replace('#<section class="tmd-about__values".*?</section>#s', $values, $content, 1) ?: $content;
    }

    if (275 === $page_id) {
        $faq = <<<'HTML'
<div class="tmd-alliance-faq">
  <details><summary>¿Publican los nombres de sus aliados?</summary><p>Sí, únicamente con autorización previa de las partes involucradas.</p></details>
  <details><summary>¿Enviar una propuesta garantiza una reunión o un acuerdo?</summary><p>No. Todas las propuestas se revisan según su viabilidad y relación con nuestra actividad.</p></details>
  <details><summary>¿Puedo proponer una alianza desde otra ciudad?</summary><p>Sí. Indique su ubicación, cobertura y capacidad operativa.</p></details>
  <details><summary>¿Cómo manejan la información confidencial?</summary><p>La información se revisa de manera reservada. Recomendamos compartir inicialmente solo lo necesario.</p></details>
  <details><summary>¿Qué debe incluir la propuesta?</summary><p>El objetivo, los aportes de cada parte, la cobertura y el beneficio para el cliente.</p></details>
  <details><summary>¿Cuándo recibiré una respuesta?</summary><p>Nos comunicaremos con usted si identificamos una oportunidad viable de colaboración.</p></details>
</div>
HTML;
        $content = preg_replace('#<div class="tmd-alliance-faq">.*?</div>#s', $faq, $content, 1) ?: $content;
    }

    if (359 === $page_id) {
        $article = <<<'HTML'
<article class="tmd-legal-article">
  <div class="tmd-legal-wrap">
    <h2>Nuestro compromiso con la seguridad</h2>
    <p>En TECNIMONTACARGAS DUAL S.A.S. reconocemos que la seguridad y la salud en el trabajo son fundamentales para el desarrollo responsable de nuestras actividades.</p>
    <p>Por ello, asumimos el compromiso de implementar, mantener y mejorar continuamente el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST), promoviendo ambientes seguros y saludables para trabajadores, contratistas, proveedores y demás partes interesadas.</p>
    <h2>Compromisos en Seguridad y Salud en el Trabajo</h2>
    <p>TECNIMONTACARGAS DUAL S.A.S. se compromete a:</p>
    <ul>
      <li>Cumplir la legislación vigente y los demás requisitos aplicables en SST.</li>
      <li>Identificar los peligros, evaluar los riesgos y establecer los controles necesarios.</li>
      <li>Prevenir accidentes, incidentes, lesiones y enfermedades laborales.</li>
      <li>Proteger la seguridad y la salud de todos los trabajadores y contratistas.</li>
      <li>Asignar los recursos humanos, técnicos y económicos necesarios para el SG-SST.</li>
      <li>Capacitar al personal en prevención de riesgos, trabajo seguro y autocuidado.</li>
      <li>Promover el uso correcto de los elementos de protección personal, herramientas y equipos.</li>
      <li>Controlar los riesgos relacionados con mantenimiento mecánico, eléctrico e hidráulico, manipulación de baterías, transporte y operación de equipos.</li>
      <li>Fomentar la consulta y participación de trabajadores y contratistas.</li>
      <li>Mejorar continuamente el desempeño del SG-SST.</li>
    </ul>
    <h2>Cultura preventiva</h2>
    <p>Promovemos una cultura de prevención en la que cada persona participa en la identificación de peligros, el reporte de condiciones inseguras y la adopción de comportamientos seguros.</p>
    <h2>Aplicación</h2>
    <p>Esta política aplica a todos los trabajadores, contratistas, subcontratistas y demás personas que desarrollen actividades bajo la responsabilidad de TECNIMONTACARGAS DUAL S.A.S.</p>
    <p>La Gerencia reafirma su compromiso con la protección de la vida, la salud y el bienestar de quienes forman parte de nuestra organización.</p>
  </div>
</article>
HTML;
        $content = preg_replace('#<article class="tmd-legal-article">.*?</article>#s', $article, $content, 1) ?: $content;
    }

    if (360 === $page_id) {
        $article = <<<'HTML'
<article class="tmd-legal-article">
  <div class="tmd-legal-wrap">
    <h2>Nuestro compromiso con la calidad</h2>
    <p>En TECNIMONTACARGAS DUAL S.A.S. reconocemos la calidad como un principio fundamental para ofrecer soluciones confiables, eficientes y oportunas.</p>
    <p>Brindamos soluciones integrales en alquiler, venta y mantenimiento de montacargas, equipos para manejo de materiales, baterías de tracción, cargadores y repuestos, contribuyendo a la seguridad, productividad y continuidad operativa de nuestros clientes.</p>
    <p>Trabajamos con responsabilidad, cumplimiento técnico, comunicación efectiva y mejora continua, fortaleciendo relaciones comerciales basadas en la confianza, el respaldo y la satisfacción del cliente.</p>
    <h2>Compromisos de calidad</h2>
    <p>TECNIMONTACARGAS DUAL S.A.S. se compromete a:</p>
    <ul>
      <li>Comprender y atender las necesidades de nuestros clientes.</li>
      <li>Brindar soluciones técnicas acordes con cada operación.</li>
      <li>Ejecutar los servicios bajo criterios técnicos y de seguridad.</li>
      <li>Promover la disponibilidad, confiabilidad y correcto funcionamiento de los equipos.</li>
      <li>Contar con personal competente y en constante formación.</li>
      <li>Cumplir los requisitos técnicos, legales, contractuales y comerciales aplicables.</li>
      <li>Mantener una comunicación clara y oportuna con clientes y partes interesadas.</li>
      <li>Gestionar las solicitudes, novedades y oportunidades de mejora.</li>
      <li>Evaluar nuestros procesos y mejorar continuamente la calidad del servicio.</li>
    </ul>
    <h2>Aplicación</h2>
    <p>Esta política aplica a todos los procesos y colaboradores de TECNIMONTACARGAS DUAL S.A.S., así como a los contratistas y proveedores que intervengan en la prestación de nuestros servicios.</p>
    <p>La Gerencia reafirma su compromiso con la satisfacción del cliente, la mejora continua y la prestación de soluciones confiables que aporten valor a cada operación.</p>
  </div>
</article>
HTML;
        $content = preg_replace('#<article class="tmd-legal-article">.*?</article>#s', $article, $content, 1) ?: $content;
    }

    return $content;
}, 110);

add_action('wp_head', static function (): void {
    if (is_page(278)) {
        echo '<style id="tmd-final-about-review">.tmd-about__value{border-top:4px solid #128ceb}.tmd-about__value:nth-child(2){border-top-color:#ffc33c}.tmd-about__value:nth-child(3){border-top-color:#262e4f}.tmd-about__value p{text-align:justify}</style>';
    }
    if (is_page(275)) {
        echo '<style id="tmd-final-alliance-review">.tmd-alliance-faq details:nth-child(odd){border-left:4px solid #128ceb}.tmd-alliance-faq details:nth-child(even){border-left:4px solid #ffc33c}</style>';
    }
}, 110);
