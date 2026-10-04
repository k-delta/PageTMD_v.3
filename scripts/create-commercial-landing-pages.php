<?php
/**
 * Prepara como borradores las páginas comerciales de alquiler y baterías.
 *
 * Dry-run:
 *   wp eval-file scripts/create-commercial-landing-pages.php
 *
 * Ejecución después de validar un backup:
 *   TMD_COMMERCIAL_LANDINGS_EXECUTE=1 \
 *   TMD_VERIFIED_BACKUP_PATH=/ruta/backup-validado \
 *   wp eval-file scripts/create-commercial-landing-pages.php
 */

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

function tmd_commercial_landing_script_block(string $markup): string
{
    return "<!-- wp:html -->\n" . trim($markup) . "\n<!-- /wp:html -->";
}

function tmd_commercial_landing_script_shortcode(string $shortcode): string
{
    return "<!-- wp:shortcode -->\n" . trim($shortcode) . "\n<!-- /wp:shortcode -->";
}

function tmd_commercial_landing_script_form_markup(string $type): string
{
    $privacy_url = esc_url(home_url('/nosotros/legal/politica-de-privacidad/'));
    $honeypot = '<div class="tmd-landing-form__honeypot" aria-hidden="true">'
        . '<label>Dejar este campo vacío [text tmd_website tabindex:-1 autocomplete:off]</label>'
        . '</div>';
    $privacy = '<p class="tmd-landing-form__privacy">'
        . '[checkbox* privacidad use_label_element "He leído y autorizo el tratamiento de mis datos"]'
        . '</p><p class="tmd-landing-form__privacy-copy">Consulta nuestra '
        . '<a href="' . $privacy_url . '" target="_blank" rel="noopener">política de privacidad</a>.'
        . '</p>';

    if ('battery' === $type) {
        return '<div class="tmd-landing-form">'
            . '<div class="tmd-landing-form__grid">'
            . '<label class="tmd-landing-form__field"><span>Nombre</span>[text* nombre autocomplete:name]</label>'
            . '<label class="tmd-landing-form__field"><span>Cargo</span>[text* cargo]</label>'
            . '<label class="tmd-landing-form__field"><span>Empresa</span>[text* empresa]</label>'
            . '<label class="tmd-landing-form__field"><span>Ciudad</span>[text* ciudad]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Correo electrónico o celular</span>[text* contacto placeholder "Escribe tu correo o número celular"]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Marca y modelo del montacargas</span>[text* marca_modelo]</label>'
            . '<label class="tmd-landing-form__field"><span>Voltaje de la batería actual</span>[text* voltaje]</label>'
            . '<label class="tmd-landing-form__field"><span>Capacidad de la batería actual</span>[text* capacidad]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>¿Buscas compra o alquiler?</span>[select* modalidad include_blank "Compra" "Alquiler"]</label>'
            . '</div>' . $honeypot . $privacy
            . '<p class="tmd-landing-form__submit">[submit "Cotizar batería"]</p>'
            . '</div>';
    }

    return '<div class="tmd-landing-form">'
        . '<div class="tmd-landing-form__grid">'
        . '<label class="tmd-landing-form__field"><span>Nombre</span>[text* nombre autocomplete:name]</label>'
        . '<label class="tmd-landing-form__field"><span>Cargo</span>[text* cargo]</label>'
        . '<label class="tmd-landing-form__field"><span>Empresa</span>[text* empresa]</label>'
        . '<label class="tmd-landing-form__field"><span>Ciudad</span>[text* ciudad]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Correo electrónico o celular</span>[text* contacto placeholder "Escribe tu correo o número celular"]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Tipo de equipo o necesidad</span>[text* necesidad placeholder "Cuéntanos qué requiere tu operación"]</label>'
        . '<label class="tmd-landing-form__field"><span>Peso de la carga</span>[text peso_carga]</label>'
        . '<label class="tmd-landing-form__field"><span>Altura de levante</span>[text altura_levante]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Ancho del pasillo</span>[text ancho_pasillo]</label>'
        . '</div>' . $honeypot . $privacy
        . '<p class="tmd-landing-form__submit">[submit "Solicitar cotización"]</p>'
        . '</div>';
}

function tmd_commercial_landing_script_form_specs(string $recipient, string $locale, array $base_properties): array
{
    $shared_mail = [
        'active' => true,
        'sender' => '[_site_title] <[_site_admin_email]>',
        'recipient' => $recipient,
        'additional_headers' => '',
        'attachments' => '',
        'use_html' => false,
        'exclude_blank' => true,
    ];
    $secondary_mail = is_array($base_properties['mail_2'] ?? null)
        ? array_merge($base_properties['mail_2'], ['active' => false])
        : ['active' => false];

    return [
        'rental' => [
            'title' => 'TMD | Cotización de alquiler y venta de montacargas eléctricos',
            'locale' => $locale,
            'form' => tmd_commercial_landing_script_form_markup('rental'),
            'mail' => array_merge($shared_mail, [
                'subject' => 'Nueva solicitud: alquiler o venta de montacargas',
                'body' => "Solicitud de cotización de montacargas\n\n"
                    . "Nombre: [nombre]\nCargo: [cargo]\nEmpresa: [empresa]\nCiudad: [ciudad]\n"
                    . "Correo o celular: [contacto]\nNecesidad: [necesidad]\n"
                    . "Peso de carga: [peso_carga]\nAltura de levante: [altura_levante]\n"
                    . "Ancho de pasillo: [ancho_pasillo]\n",
            ]),
            'mail_2' => $secondary_mail,
        ],
        'battery' => [
            'title' => 'TMD | Cotización de baterías para montacargas',
            'locale' => $locale,
            'form' => tmd_commercial_landing_script_form_markup('battery'),
            'mail' => array_merge($shared_mail, [
                'subject' => 'Nueva solicitud: batería para montacargas',
                'body' => "Solicitud de cotización de batería\n\n"
                    . "Nombre: [nombre]\nCargo: [cargo]\nEmpresa: [empresa]\nCiudad: [ciudad]\n"
                    . "Correo o celular: [contacto]\nMarca y modelo: [marca_modelo]\n"
                    . "Voltaje actual: [voltaje]\nCapacidad actual: [capacidad]\n"
                    . "Modalidad: [modalidad]\n",
            ]),
            'mail_2' => $secondary_mail,
        ],
    ];
}

function tmd_commercial_landing_script_page_content(string $type, int $form_id): string
{
    $asset_root = esc_url_raw(untrailingslashit(get_stylesheet_directory_uri()) . '/assets/img');
    $blocks = [];

    if ('rental' === $type) {
        $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__hero tmd-commercial-landing__hero--rental" aria-labelledby="tmd-rental-heading">
  <div class="tmd-commercial-landing__hero-media"><img src="TMD_ASSETS/commercial-landings/alquiler-hero.jpeg" alt="" fetchpriority="high" decoding="async"></div>
  <div class="tmd-commercial-landing__hero-content">
    <div class="tmd-commercial-landing__hero-copy">
      <span class="tmd-commercial-landing__eyebrow">Soluciones para Colombia</span>
      <h1 id="tmd-rental-heading">Alquiler y venta de <em>montacargas eléctricos</em></h1>
      <p class="tmd-commercial-landing__hero-lead">Equipos para centros de distribución, bodegas y plantas. Cuéntanos sobre tu operación y recibe asesoría para elegir una alternativa adecuada.</p>
      <a class="tmd-commercial-landing__button" href="#formulario-montacargas">Solicitar cotización</a>
      <div class="tmd-commercial-landing__hero-meta">
        <span>Alquiler mínimo de 1 mes</span>
        <span>Cobertura en Colombia</span>
        <span>Alquiler sin operador</span>
      </div>
    </div>
  </div>
</section>
HTML));

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__stats">
      <div class="tmd-commercial-landing__stat"><strong>120</strong><span>equipos en la flota propia</span></div>
      <div class="tmd-commercial-landing__stat"><strong>Desde 2000</strong><span>servicio técnico</span></div>
      <div class="tmd-commercial-landing__stat"><strong>1 mes</strong><span>periodo mínimo de alquiler</span></div>
    </div>
    <p class="tmd-commercial-landing__brand-note">Experiencia con equipos Yale, Crown, Clark, Jungheinrich y Hyster.</p>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft" aria-labelledby="tmd-rental-needs-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Según tu operación</span>
      <h2 id="tmd-rental-needs-heading">Un equipo para cada reto de bodega</h2>
      <p>La elección depende de la carga, la altura de trabajo y el espacio disponible.</p>
    </div>
    <div class="tmd-commercial-landing__card-grid tmd-commercial-landing__card-grid--four">
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">01</span><h3>Muelle de carga</h3><p>Montacargas contrabalanceados eléctricos para mover carga entre el muelle y la zona de almacenamiento.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">02</span><h3>Pasillos angostos</h3><p>Equipos retráctiles para operaciones que requieren aprovechar el espacio vertical de la bodega.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">03</span><h3>Almacenamiento a doble profundidad</h3><p>Soluciones con pantógrafo sencillo o de doble profundidad, según el sistema de almacenamiento.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">04</span><h3>Traslado a nivel de piso</h3><p>Estibadores y apiladores eléctricos para el movimiento y posicionamiento de mercancía.</p></article>
    </div>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section" aria-labelledby="tmd-rental-buy-heading">
  <div class="tmd-commercial-landing__container tmd-commercial-landing__split">
    <div>
      <span class="tmd-commercial-landing__eyebrow">Una decisión para tu operación</span>
      <h2 id="tmd-rental-buy-heading">¿Alquilar o comprar?</h2>
      <p>Revisamos el uso esperado, la duración y las condiciones de la bodega para orientar una cotización. El alquiler comienza en un periodo mínimo de un mes.</p>
    </div>
    <ul class="tmd-commercial-landing__benefit-list">
      <li><strong>Alquila</strong> cuando necesitas capacidad adicional por un periodo definido o mientras reorganizas tu operación.</li>
      <li><strong>Compra</strong> un equipo usado cuando buscas incorporarlo a tu operación y su disponibilidad esté confirmada.</li>
      <li><strong>Compara con asesoría</strong> el tipo de equipo, la carga, la altura y el ancho de pasillo antes de decidir.</li>
    </ul>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_shortcode('[tmd_commercial_landing_inventory type="equipment"]');

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft" aria-labelledby="tmd-rental-process-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Así lo hacemos</span>
      <h2 id="tmd-rental-process-heading">De la necesidad a la cotización</h2>
    </div>
    <div class="tmd-commercial-landing__steps">
      <article class="tmd-commercial-landing__step"><h3>Conocemos tu operación</h3><p>Conversamos sobre la bodega, los turnos y el movimiento de carga.</p></article>
      <article class="tmd-commercial-landing__step"><h3>Validamos el equipo</h3><p>Revisamos capacidad, altura de levante, pasillos y tipo de operación.</p></article>
      <article class="tmd-commercial-landing__step"><h3>Preparamos la propuesta</h3><p>Consideramos modalidad y periodo. El alquiler es por mínimo un mes.</p></article>
      <article class="tmd-commercial-landing__step"><h3>Coordinamos el servicio</h3><p>Definimos contigo la atención y el soporte técnico para la operación en Colombia.</p></article>
    </div>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section" aria-labelledby="tmd-rental-sectors-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Sectores</span>
      <h2 id="tmd-rental-sectors-heading">Acompañamos distintos entornos de trabajo</h2>
    </div>
    <div class="tmd-commercial-landing__sector-grid">
      <article class="tmd-commercial-landing__sector"><h3>Logística</h3><p>Movimiento y almacenamiento de mercancía.</p></article>
      <article class="tmd-commercial-landing__sector"><h3>Retail</h3><p>Abastecimiento de centros y puntos de distribución.</p></article>
      <article class="tmd-commercial-landing__sector"><h3>Manufactura</h3><p>Traslado de materiales dentro de la planta.</p></article>
      <article class="tmd-commercial-landing__sector"><h3>Distribución</h3><p>Recepción, preparación y despacho de pedidos.</p></article>
    </div>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft" aria-labelledby="tmd-rental-situations-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Alquiler mensual</span>
      <h2 id="tmd-rental-situations-heading">Capacidad para momentos clave</h2>
      <p>Los proyectos temporales también se cotizan con un periodo mínimo de un mes.</p>
    </div>
    <div class="tmd-commercial-landing__card-grid tmd-commercial-landing__card-grid--four">
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">01</span><h3>Temporadas de alta demanda</h3><p>Refuerza el movimiento de carga cuando crece el volumen de trabajo.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">02</span><h3>Apertura o expansión</h3><p>Incorpora equipos durante la puesta en marcha o ampliación de una bodega.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">03</span><h3>Reemplazo temporal</h3><p>Consulta alternativas mientras uno de tus equipos está fuera de operación.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">04</span><h3>Proyectos definidos</h3><p>Planea una necesidad de capacidad por un periodo acordado, desde un mes.</p></article>
    </div>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--dark" aria-labelledby="tmd-rental-support-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__callout">
      <div>
        <span class="tmd-commercial-landing__eyebrow">Respaldo técnico</span>
        <h2 id="tmd-rental-support-heading">Experiencia que acompaña el movimiento</h2>
        <p>Servicio técnico desde 2000 y experiencia en alquiler de montacargas desde 2013.</p>
      </div>
      <ul class="tmd-commercial-landing__support-points">
        <li>Diagnóstico de acuerdo con la necesidad de operación.</li>
        <li>Acompañamiento técnico para los equipos cotizados.</li>
        <li>Atención comercial para empresas en Colombia.</li>
      </ul>
    </div>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_shortcode('[tmd_commercial_landing_form id="' . $form_id . '" type="rental"]');

        $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section" aria-labelledby="tmd-rental-faq-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Preguntas frecuentes</span>
      <h2 id="tmd-rental-faq-heading">Información para cotizar</h2>
    </div>
    <div class="tmd-commercial-landing__faq-grid">
      <details class="tmd-commercial-landing__faq-item"><summary>¿Cuál es el periodo mínimo de alquiler?</summary><div class="tmd-commercial-landing__faq-answer"><p>El alquiler de montacargas eléctricos se cotiza desde un mínimo de un mes. El periodo y el equipo se definen según la operación.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿El alquiler incluye operador?</summary><div class="tmd-commercial-landing__faq-answer"><p>No. Los equipos de alquiler se entregan sin operador.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿En qué lugares prestan el servicio?</summary><div class="tmd-commercial-landing__faq-answer"><p>La cobertura comercial es nacional en Colombia. La atención de cada solicitud se coordina con el equipo comercial.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿Qué marcas manejan?</summary><div class="tmd-commercial-landing__faq-answer"><p>La experiencia de la compañía incluye equipos Yale, Crown, Clark, Jungheinrich y Hyster. Las referencias y su disponibilidad se verifican al cotizar.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿Qué equipos están disponibles para venta?</summary><div class="tmd-commercial-landing__faq-answer"><p>La oferta de venta corresponde a equipos usados cuya disponibilidad se confirma en Inventario antes de preparar una propuesta.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿Qué información debo enviar?</summary><div class="tmd-commercial-landing__faq-answer"><p>Indica ciudad, tipo de necesidad, peso de carga, altura de levante y ancho de pasillo. Si todavía no tienes todos los datos, cuéntanos lo que conoces.</p></div></details>
    </div>
  </div>
</section>
HTML);

        $blocks[] = tmd_commercial_landing_script_shortcode('[tmd_commercial_landing_related_section topic="montacargas"]');

        return implode("\n\n", $blocks);
    }

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__hero tmd-commercial-landing__hero--battery" aria-labelledby="tmd-battery-heading">
  <div class="tmd-commercial-landing__hero-media"><img src="TMD_ASSETS/commercial-landings/baterias-hero.jpeg" alt="" fetchpriority="high" decoding="async"></div>
  <div class="tmd-commercial-landing__hero-content">
    <div class="tmd-commercial-landing__hero-copy">
      <span class="tmd-commercial-landing__eyebrow">Barbillon · soluciones para Colombia</span>
      <h1 id="tmd-battery-heading">Baterías para <em>montacargas eléctricos</em></h1>
      <p class="tmd-commercial-landing__hero-lead">Baterías de plomo-ácido, cargadores y monitoreo BMS, con asesoría para las necesidades de tu operación.</p>
      <a class="tmd-commercial-landing__button" href="#formulario-baterias">Cotizar batería</a>
      <div class="tmd-commercial-landing__hero-meta">
        <span>Cobertura nacional en Colombia</span>
        <span>Instalación en la bodega del cliente</span>
        <span>Alquiler desde 1 mes</span>
      </div>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft" aria-labelledby="tmd-battery-solutions-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Barbillon</span>
      <h2 id="tmd-battery-solutions-heading">Soluciones de energía para tu equipo</h2>
      <p>Explora las líneas de producto y consulta su compatibilidad para tu montacargas.</p>
    </div>
    <div class="tmd-commercial-landing__solution-grid">
      <article class="tmd-commercial-landing__solution">
        <img src="TMD_ASSETS/mega-menu/energy-baterias-plomo.webp" alt="Batería de plomo para montacargas" loading="lazy" decoding="async">
        <div><h3>Baterías de plomo-ácido</h3><p>Alternativas para equipos de movimiento eléctrico, sujetas a validación de voltaje, capacidad y medidas.</p><a href="/energia/baterias/plomo/">Conocer baterías <span aria-hidden="true">→</span></a></div>
      </article>
      <article class="tmd-commercial-landing__solution">
        <img src="TMD_ASSETS/mega-menu/energy-cargadores.png" alt="Cargador industrial para batería de montacargas" loading="lazy" decoding="async">
        <div><h3>Cargadores</h3><p>Revisa opciones para baterías de plomo-ácido y las condiciones eléctricas de tu operación.</p><a href="/energia/cargadores/">Conocer cargadores <span aria-hidden="true">→</span></a></div>
      </article>
      <article class="tmd-commercial-landing__solution">
        <img src="TMD_ASSETS/mega-menu/energy-bms.webp" alt="Sistema de monitoreo de batería BMS" loading="lazy" decoding="async">
        <div><h3>BMS</h3><p>Monitoreo de batería, estado y rendimiento para apoyar el diagnóstico técnico.</p><a href="/energia/bms/">Conocer BMS <span aria-hidden="true">→</span></a></div>
      </article>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section" aria-labelledby="tmd-battery-diagnostic-heading">
  <div class="tmd-commercial-landing__container tmd-commercial-landing__split">
    <div>
      <span class="tmd-commercial-landing__eyebrow">Acompañamiento técnico</span>
      <h2 id="tmd-battery-diagnostic-heading">Primero entendemos la operación</h2>
      <p>Antes de recomendar un cambio, revisamos los datos disponibles de la batería, el montacargas y los ciclos de trabajo. La propuesta se basa en la compatibilidad y en las necesidades del cliente.</p>
    </div>
    <ul class="tmd-commercial-landing__benefit-list">
      <li>Revisión de marca, modelo, voltaje y capacidad.</li>
      <li>Orientación sobre estado y rendimiento de la batería.</li>
      <li>Alternativas de batería, cargador o monitoreo según la evaluación técnica.</li>
    </ul>
  </div>
</section>
HTML);

    $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft" aria-labelledby="tmd-battery-benefits-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Rendimiento</span>
      <h2 id="tmd-battery-benefits-heading">Una recomendación conectada con tus turnos</h2>
    </div>
    <div class="tmd-commercial-landing__card-grid tmd-commercial-landing__card-grid--three">
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">01</span><h3>Autonomía de operación</h3><p>Consideramos recorridos, carga de trabajo y duración de los turnos al revisar una alternativa.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">02</span><h3>Estado de las celdas</h3><p>Los datos y la evaluación de la batería ayudan a orientar mantenimiento o reemplazo.</p></article>
      <article class="tmd-commercial-landing__card"><span class="tmd-commercial-landing__number">03</span><h3>Carga entre turnos</h3><p>Revisamos las ventanas de carga y la compatibilidad del cargador con la batería.</p></article>
    </div>
  </div>
</section>
HTML);

    $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section" aria-labelledby="tmd-battery-process-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Proceso</span>
      <h2 id="tmd-battery-process-heading">De los datos del equipo a la solución</h2>
    </div>
    <div class="tmd-commercial-landing__steps tmd-commercial-landing__steps--three">
      <article class="tmd-commercial-landing__step"><h3>Compartes los datos</h3><p>Cuéntanos marca, modelo, voltaje, capacidad y condiciones de uso.</p></article>
      <article class="tmd-commercial-landing__step"><h3>Validamos la necesidad</h3><p>Revisamos la aplicación y la compatibilidad de batería, cargador y monitoreo.</p></article>
      <article class="tmd-commercial-landing__step"><h3>Preparamos la cotización</h3><p>Definimos contigo una alternativa de compra o alquiler; el alquiler parte de un mes.</p></article>
    </div>
    <p class="tmd-commercial-landing__service-note"><strong>Servicio en sitio:</strong> la instalación y el mantenimiento se realizan en la bodega del cliente, con cobertura en Colombia.</p>
  </div>
</section>
HTML);

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft" aria-labelledby="tmd-battery-gallery-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Soluciones en operación</span>
      <h2 id="tmd-battery-gallery-heading">Baterías y equipos de trabajo</h2>
      <p>Imágenes de referencia; no representan una confirmación de inventario o disponibilidad.</p>
    </div>
    <div class="tmd-commercial-landing__battery-gallery">
      <div class="tmd-commercial-landing__battery-gallery-image tmd-commercial-landing__battery-gallery-image--one" role="img" aria-label="Montacargas en zona de almacenamiento"></div>
      <div class="tmd-commercial-landing__battery-gallery-image tmd-commercial-landing__battery-gallery-image--two" role="img" aria-label="Batería industrial con conexiones"></div>
      <div class="tmd-commercial-landing__battery-gallery-image tmd-commercial-landing__battery-gallery-image--three" role="img" aria-label="Detalle de una batería industrial"></div>
      <div class="tmd-commercial-landing__battery-gallery-image tmd-commercial-landing__battery-gallery-image--four" role="img" aria-label="Montacargas en operación de bodega"></div>
      <div class="tmd-commercial-landing__battery-gallery-image tmd-commercial-landing__battery-gallery-image--five" role="img" aria-label="Batería industrial en primer plano"></div>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_shortcode('[tmd_commercial_landing_form id="' . $form_id . '" type="battery"]');

    $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section" aria-labelledby="tmd-battery-faq-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading">
      <span class="tmd-commercial-landing__eyebrow">Preguntas frecuentes</span>
      <h2 id="tmd-battery-faq-heading">Resolvemos tus dudas</h2>
    </div>
    <div class="tmd-commercial-landing__faq-grid">
      <details class="tmd-commercial-landing__faq-item"><summary>¿Qué datos necesitan para recomendar una batería?</summary><div class="tmd-commercial-landing__faq-answer"><p>Solicitamos marca y modelo del montacargas, voltaje y capacidad de la batería actual, además de información sobre la operación.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿La instalación se hace en mi bodega?</summary><div class="tmd-commercial-landing__faq-answer"><p>Sí. La instalación se realiza en la bodega del cliente y se coordina de acuerdo con la ubicación de la operación en Colombia.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿También hacen mantenimiento en sitio?</summary><div class="tmd-commercial-landing__faq-answer"><p>Sí. El mantenimiento se coordina en la bodega del cliente.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿Qué soluciones puedo cotizar?</summary><div class="tmd-commercial-landing__faq-answer"><p>Puedes consultar baterías de plomo-ácido, cargadores y sistemas BMS, de acuerdo con la compatibilidad de tu equipo.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿En qué ciudades prestan atención?</summary><div class="tmd-commercial-landing__faq-answer"><p>La cobertura comercial es nacional en Colombia. La instalación y el mantenimiento se coordinan en las instalaciones del cliente.</p></div></details>
      <details class="tmd-commercial-landing__faq-item"><summary>¿Puedo cotizar compra o alquiler?</summary><div class="tmd-commercial-landing__faq-answer"><p>Sí. Indica la modalidad que te interesa. Para el alquiler de baterías, el periodo mínimo es de un mes.</p></div></details>
    </div>
  </div>
</section>
HTML);

    $blocks[] = tmd_commercial_landing_script_shortcode('[tmd_commercial_landing_related_section topic="baterias"]');

    return implode("\n\n", $blocks);
}

function tmd_commercial_landing_script_page_specs(array $form_ids): array
{
    return [
        'rental' => [
            'slug' => 'alquiler-montacargas-electricos',
            'title' => 'Alquiler y venta de montacargas eléctricos',
            'rank_title' => 'Alquiler de montacargas eléctricos en Colombia | Tecnimontacargas',
            'rank_description' => 'Alquila montacargas eléctricos sin operador desde un mes o consulta equipos usados con disponibilidad confirmada en Colombia.',
            'seed' => '2026-10-04-v1:rental',
            'form_id' => $form_ids['rental'],
        ],
        'battery' => [
            'slug' => 'baterias-para-montacargas',
            'title' => 'Baterías para montacargas eléctricos',
            'rank_title' => 'Baterías para montacargas eléctricos | Tecnimontacargas',
            'rank_description' => 'Baterías de plomo-ácido, BMS y cargadores Barbillon con asesoría, instalación y mantenimiento en Colombia.',
            'seed' => '2026-10-04-v1:battery',
            'form_id' => $form_ids['battery'],
        ],
    ];
}

function tmd_commercial_landing_script_existing_pages(array $page_specs): array
{
    $post_types = get_post_types(['public' => true], 'names');
    $post_types = array_values(array_diff($post_types, ['attachment']));
    $pages = [];

    foreach ($page_specs as $type => $spec) {
        $collisions = get_posts([
            'post_type' => $post_types,
            'post_status' => ['publish', 'future', 'draft', 'pending', 'private'],
            'name' => $spec['slug'],
            'numberposts' => -1,
            'suppress_filters' => true,
        ]);
        $collisions = array_values(array_filter($collisions, static function ($post) use ($spec): bool {
            return $post instanceof WP_Post && $post->post_name === $spec['slug'];
        }));

        if (count($collisions) > 1) {
            throw new RuntimeException('La ruta /' . $spec['slug'] . '/ tiene más de un contenido con ese slug; requiere revisión manual.');
        }
        if (! $collisions) {
            $pages[$type] = null;
            continue;
        }

        $page = $collisions[0];
        $seed = (string) get_post_meta($page->ID, '_tmd_commercial_landing_seed', true);
        if ('page' !== $page->post_type || $seed !== $spec['seed']) {
            throw new RuntimeException('La ruta /' . $spec['slug'] . '/ ya está ocupada. No se modificó el contenido existente.');
        }

        $pages[$type] = $page;
    }

    return $pages;
}

function tmd_commercial_landing_script_existing_forms(array $form_specs): array
{
    $posts = get_posts([
        'post_type' => 'wpcf7_contact_form',
        'post_status' => 'any',
        'numberposts' => -1,
        'suppress_filters' => true,
    ]);
    $forms = [];

    foreach ($form_specs as $type => $spec) {
        $matches = array_values(array_filter($posts, static function ($post) use ($spec): bool {
            return $post instanceof WP_Post && $post->post_title === $spec['title'];
        }));
        if (count($matches) > 1) {
            throw new RuntimeException('Hay más de un formulario con el título administrado para ' . $type . '; requiere revisión manual.');
        }
        if (! $matches) {
            $forms[$type] = null;
            continue;
        }

        $form_post = $matches[0];
        $seed = (string) get_post_meta($form_post->ID, '_tmd_commercial_landing_form_seed', true);
        if ($seed !== '2026-10-04-v1:' . $type) {
            throw new RuntimeException('Ya existe un formulario con el título reservado para ' . $type . '. No se modificó.');
        }

        $form = function_exists('wpcf7_contact_form') ? wpcf7_contact_form($form_post->ID) : false;
        if (! $form) {
            throw new RuntimeException('No fue posible cargar el formulario administrado para ' . $type . '.');
        }
        $properties = $form->get_properties();
        $form_markup = (string) ($properties['form'] ?? '');
        $form_markup = (string) preg_replace('/<!--.*?-->/s', '', $form_markup);
        if (! preg_match('/(?<!\[)\[text\s+tmd_website(?=[^\]]*\btabindex:-1\b)(?=[^\]]*\bautocomplete:off\b)[^\]]*\](?!\])/', $form_markup)) {
            throw new RuntimeException('El formulario administrado para ' . $type . ' no contiene el campo anti-spam requerido. Requiere revisión manual.');
        }
        $forms[$type] = $form;
    }

    return $forms;
}

function tmd_commercial_landing_script_check_existing_page_form($page, string $type, $form): void
{
    if (! $page instanceof WP_Post) {
        return;
    }
    if (! $form) {
        throw new RuntimeException('La página administrada de ' . $type . ' existe, pero su formulario falta. Requiere revisión manual.');
    }

    $pattern = '/\[tmd_commercial_landing_form\s+id=["\'](\d+)["\']\s+type=["\']' . preg_quote($type, '/') . '["\']\]/';
    if (! preg_match($pattern, $page->post_content, $matches) || (int) $matches[1] !== (int) $form->id()) {
        throw new RuntimeException('El formulario asociado a la página de ' . $type . ' no coincide con el formulario administrado. No se sobrescribió el contenido.');
    }
}

function tmd_commercial_landing_script_backup_is_valid(): bool
{
    $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
    if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_readable($backup_path)) {
        return false;
    }

    $database_path = $backup_path . '/database.sql';
    $manifest_path = $backup_path . '/BACKUP_MANIFEST.json';
    if (! is_file($database_path) || is_link($database_path) || ! is_readable($database_path)
        || ! is_file($manifest_path) || is_link($manifest_path) || ! is_readable($manifest_path)) {
        return false;
    }

    $backup_mode = fileperms($backup_path);
    $database_mode = fileperms($database_path);
    $manifest_mode = fileperms($manifest_path);
    if (false === $backup_mode || false === $database_mode || false === $manifest_mode
        || 0 !== ($backup_mode & 0077)
        || 0 !== ($database_mode & 0077)
        || 0 !== ($manifest_mode & 0077)) {
        return false;
    }

    $wordpress_root = realpath(ABSPATH);
    if (is_string($wordpress_root)
        && str_starts_with($backup_path . DIRECTORY_SEPARATOR, rtrim($wordpress_root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
        return false;
    }

    $manifest = json_decode((string) file_get_contents($manifest_path), true);
    if (! is_array($manifest)
        || ($manifest['schema_version'] ?? 0) !== 1
        || ($manifest['environment'] ?? '') !== 'production'
        || ($manifest['backup_type'] ?? '') !== 'full'
        || ($manifest['verified'] ?? false) !== true
        || ($manifest['database_file'] ?? '') !== 'database.sql'
        || ($manifest['sql_format'] ?? '') !== 'mariadb-dump'
        || ($manifest['sql_header_verified'] ?? false) !== true
        || ($manifest['dump_completion_marker_verified'] ?? false) !== true
        || ! is_string($manifest['created_at_utc'] ?? null)
        || ! is_string($manifest['restore_path'] ?? null)
        || '' === trim($manifest['restore_path'])
        || ! is_string($manifest['restore_method'] ?? null)
        || '' === trim($manifest['restore_method'])) {
        return false;
    }

    $created_at = strtotime($manifest['created_at_utc']);
    if (false === $created_at || $created_at > time() || time() - $created_at > 7200) {
        return false;
    }

    $database_size = filesize($database_path);
    $database_hash = hash_file('sha256', $database_path);
    if (! is_int($database_size) || $database_size < 65536
        || ! is_string($database_hash)
        || (int) ($manifest['database_size_bytes'] ?? 0) !== $database_size
        || ! is_string($manifest['database_sha256'] ?? null)
        || ! hash_equals(strtolower($manifest['database_sha256']), strtolower($database_hash))) {
        return false;
    }

    $header = file_get_contents($database_path, false, null, 0, 65536);
    $handle = fopen($database_path, 'rb');
    if (! is_string($header) || false === $handle) {
        return false;
    }

    $tail_offset = max(0, $database_size - 16384);
    if (0 !== fseek($handle, $tail_offset)) {
        fclose($handle);
        return false;
    }
    $tail = fread($handle, 16384);
    fclose($handle);

    return (bool) preg_match('/(?:MariaDB|MySQL) dump/i', $header)
        && (bool) preg_match('/CREATE TABLE/i', $header)
        && is_string($tail)
        && (bool) preg_match('/-- Dump completed on /i', $tail);
}

function tmd_commercial_landing_script_remove_created(array $page_ids, array $form_ids): array
{
    $failures = [];
    foreach (array_reverse($page_ids) as $post_id) {
        if (! wp_delete_post($post_id, true)) {
            $failures[] = 'página ' . (int) $post_id;
        }
    }
    foreach (array_reverse($form_ids) as $post_id) {
        if (! wp_delete_post($post_id, true)) {
            $failures[] = 'formulario ' . (int) $post_id;
        }
    }
    return $failures;
}

$created_page_ids = [];
$created_form_ids = [];
$seed_lock = null;

try {
    $seed_lock_path = trailingslashit(get_temp_dir()) . 'tmd-commercial-landings-seed.lock';
    $seed_lock = @fopen($seed_lock_path, 'c');
    if (! is_resource($seed_lock) || ! flock($seed_lock, LOCK_EX | LOCK_NB)) {
        is_resource($seed_lock) && fclose($seed_lock);
        $seed_lock = null;
        throw new RuntimeException('No se pudo adquirir el bloqueo exclusivo de preparación; revisa si hay otra ejecución en curso.');
    }
    @chmod($seed_lock_path, 0600);

    if (! class_exists('WPCF7_ContactForm')
        || ! function_exists('wpcf7_save_contact_form')
        || ! function_exists('wpcf7_contact_form')) {
        throw new RuntimeException('Contact Form 7 o su API de guardado no está disponible.');
    }

    $base_form = WPCF7_ContactForm::get_instance(14);
    if (! $base_form) {
        throw new RuntimeException('No existe el formulario Contact Form 7 ID 14 que aporta el destinatario de cotizaciones.');
    }
    $base_properties = $base_form->get_properties();
    $recipient = trim((string) ($base_properties['mail']['recipient'] ?? ''));
    if ('' === $recipient) {
        throw new RuntimeException('El formulario Contact Form 7 ID 14 no tiene destinatario configurado.');
    }

    $form_specs = tmd_commercial_landing_script_form_specs(
        $recipient,
        (string) $base_form->locale(),
        $base_properties
    );
    $existing_forms = tmd_commercial_landing_script_existing_forms($form_specs);
    $form_ids = [];
    foreach ($existing_forms as $type => $form) {
        $form_ids[$type] = $form ? (int) $form->id() : 0;
    }
    $page_specs = tmd_commercial_landing_script_page_specs($form_ids);
    $existing_pages = tmd_commercial_landing_script_existing_pages($page_specs);
    $page_ids = [];

    foreach ($existing_pages as $type => $page) {
        $page_ids[$type] = $page instanceof WP_Post ? (int) $page->ID : 0;
        tmd_commercial_landing_script_check_existing_page_form($page, $type, $existing_forms[$type]);
        if ($page && ! $existing_forms[$type]) {
            throw new RuntimeException('La página administrada de ' . $type . ' ya existe pero su formulario administrado no está disponible.');
        }
    }

    if ('1' !== getenv('TMD_COMMERCIAL_LANDINGS_EXECUTE')) {
        foreach ($page_specs as $type => $spec) {
            $preview_content = tmd_commercial_landing_script_page_content(
                $type,
                $form_ids[$type] > 0 ? $form_ids[$type] : 1
            );
            if ('' === $preview_content || false !== strpos($preview_content, 'TMD_ASSETS')) {
                throw new RuntimeException('La estructura de contenido o sus imágenes no están listas para ' . $type . '.');
            }

            $form_action = $existing_forms[$type] ? 'conservaría el formulario administrado existente' : 'crearía un formulario nuevo';
            $page_action = $existing_pages[$type] ? 'conservaría la página administrada existente' : 'crearía un borrador';
            WP_CLI::line('Página /' . $spec['slug'] . '/: ' . $page_action . '.');
            WP_CLI::line('Formulario ' . $form_specs[$type]['title'] . ': ' . $form_action . '.');
        }
        WP_CLI::line('Campos de alquiler: nombre, cargo, empresa, ciudad, correo o celular, necesidad, carga, altura, pasillo y privacidad; control anti-spam server-side.');
        WP_CLI::line('Campos de baterías: nombre, cargo, empresa, ciudad, correo o celular, equipo, voltaje, capacidad, compra o alquiler y privacidad; control anti-spam server-side.');
        WP_CLI::success('Dry-run sin escrituras. Las páginas se prepararían como borradores y no se publicarían.');
        return;
    }

    if (! tmd_commercial_landing_script_backup_is_valid()) {
        throw new RuntimeException('Ejecución detenida: TMD_VERIFIED_BACKUP_PATH debe apuntar a un backup de producción verificado con BACKUP_MANIFEST.json y database.sql íntegro.');
    }

    foreach ($form_specs as $type => $spec) {
        if ($existing_forms[$type]) {
            $form_ids[$type] = (int) $existing_forms[$type]->id();
            continue;
        }

        $saved_form = wpcf7_save_contact_form([
            'id' => -1,
            'title' => $spec['title'],
            'locale' => $spec['locale'],
            'form' => $spec['form'],
            'mail' => $spec['mail'],
            'mail_2' => $spec['mail_2'],
            'messages' => $base_properties['messages'] ?? [],
            'additional_settings' => '',
        ], 'save');
        if (! $saved_form instanceof WPCF7_ContactForm || $saved_form->id() < 1) {
            throw new RuntimeException('Contact Form 7 no confirmó la creación del formulario de ' . $type . '.');
        }

        $form_id = (int) $saved_form->id();
        $created_form_ids[] = $form_id;
        update_post_meta($form_id, '_tmd_commercial_landing_form_seed', '2026-10-04-v1:' . $type);
        $verified_form = wpcf7_contact_form($form_id);
        $verified_properties = $verified_form ? $verified_form->get_properties() : [];
        if (! $verified_form
            || (string) ($verified_properties['mail']['recipient'] ?? '') !== $recipient
            || (string) get_post_meta($form_id, '_tmd_commercial_landing_form_seed', true) !== '2026-10-04-v1:' . $type) {
            throw new RuntimeException('La comprobación posterior del formulario de ' . $type . ' falló.');
        }
        $form_ids[$type] = $form_id;
    }

    $page_specs = tmd_commercial_landing_script_page_specs($form_ids);
    foreach ($page_specs as $type => $spec) {
        if ($existing_pages[$type]) {
            continue;
        }

        $page_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'draft',
            'post_parent' => 0,
            'post_name' => $spec['slug'],
            'post_title' => $spec['title'],
            'post_content' => tmd_commercial_landing_script_page_content($type, (int) $form_ids[$type]),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ], true);
        if (is_wp_error($page_id) || (int) $page_id < 1) {
            throw new RuntimeException('No fue posible crear la página borrador de ' . $type . '.');
        }

        $page_id = (int) $page_id;
        $created_page_ids[] = $page_id;
        $page_ids[$type] = $page_id;
        update_post_meta($page_id, '_tmd_commercial_landing_seed', $spec['seed']);
        update_post_meta($page_id, 'rank_math_title', $spec['rank_title']);
        update_post_meta($page_id, 'rank_math_description', $spec['rank_description']);
        $verified_page = get_post($page_id);
        if (! $verified_page instanceof WP_Post
            || 'page' !== $verified_page->post_type
            || 'draft' !== $verified_page->post_status
            || 0 !== (int) $verified_page->post_parent
            || $spec['slug'] !== $verified_page->post_name
            || (string) get_post_meta($page_id, '_tmd_commercial_landing_seed', true) !== $spec['seed']
            || (string) get_post_meta($page_id, 'rank_math_title', true) !== $spec['rank_title']
            || (string) get_post_meta($page_id, 'rank_math_description', true) !== $spec['rank_description']) {
            throw new RuntimeException('La comprobación posterior de la página ' . $type . ' falló.');
        }
    }

    $created = [];
    foreach ($page_specs as $type => $spec) {
        $page_id = (int) $page_ids[$type];
        if ($existing_pages[$type] instanceof WP_Post) {
            $created[] = '/' . $spec['slug'] . '/ ya existe y se conservó sin cambios.';
        } else {
            $created[] = '/' . $spec['slug'] . '/ creada como borrador (ID ' . $page_id . ').';
        }
    }
    WP_CLI::success(implode(' ', $created) . ' Los enlaces del menú público aparecerán al publicar cada página.');
} catch (Throwable $exception) {
    $rollback_failures = tmd_commercial_landing_script_remove_created($created_page_ids, $created_form_ids);
    $message = $exception->getMessage();
    if ($rollback_failures) {
        $message .= ' No se pudieron retirar estos elementos creados durante esta ejecución: ' . implode(', ', $rollback_failures) . '. Revisa el backup antes de continuar.';
    }
    WP_CLI::error($message);
} finally {
    if (is_resource($seed_lock)) {
        flock($seed_lock, LOCK_UN);
        fclose($seed_lock);
    }
}
