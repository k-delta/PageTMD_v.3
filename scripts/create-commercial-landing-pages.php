<?php
/**
 * Prepara como borradores las páginas comerciales de alquiler y baterías.
 *
 * Dry-run:
 *   wp eval-file scripts/create-commercial-landing-pages.php
 *
 * Ejecución después de validar un backup:
 *   TMD_COMMERCIAL_LANDINGS_RECIPIENT=correo@dominio.com \
 *   TMD_COMMERCIAL_LANDINGS_EXECUTE=1 \
 *   TMD_VERIFIED_BACKUP_PATH=/ruta/backup-validado \
 *   wp eval-file scripts/create-commercial-landing-pages.php
 *
 * Actualización acotada de la landing publicada (dry-run por defecto):
 *   TMD_COMMERCIAL_LANDINGS_MODE=update-rental-page \
 *   TMD_RENTAL_V2_EXPECTED_PAGE_SHA256=<hash-actual> \
 *   TMD_RENTAL_V2_TARGET_PAGE_SHA256=<hash-objetivo> \
 *   wp eval-file scripts/create-commercial-landing-pages.php
 * Pasar también los hashes CF7/meta exigidos por update-rental-landing-v2.php.
 * Para escribir, añadir TMD_COMMERCIAL_LANDINGS_EXECUTE=1 y un backup verificado. *
 * Reemplazo acotado de contenido/formulario de baterías (dry-run por defecto):
 *   TMD_COMMERCIAL_LANDINGS_MODE=update-battery-maqueta \
 *   TMD_BATTERY_PAGE_EXPECTED_SHA256=<hash-actual> \
 *   TMD_BATTERY_PAGE_TARGET_SHA256=<hash-destino> \
 *   TMD_BATTERY_FORM_EXPECTED_SHA256=<hash-actual> \
 *   TMD_BATTERY_FORM_TARGET_SHA256=<hash-destino> \
 *   wp eval-file scripts/create-commercial-landing-pages.php
 * Para escribir, añadir TMD_COMMERCIAL_LANDINGS_EXECUTE=1 y un backup verificado.

 */

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

require_once __DIR__ . '/commercial-landing-rental-v2.php';
require_once __DIR__ . '/update-rental-landing-v2.php';

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
    if ('rental' === $type) {
        return tmd_commercial_landing_rental_v2_form_markup();
    }

    $privacy_url = esc_url(home_url('/nosotros/legal/politica-de-privacidad/'));
    $honeypot = '<div class="tmd-landing-form__honeypot" aria-hidden="true">'
        . '<label>Dejar este campo vacío [text tmd_website tabindex:-1 autocomplete:off]</label>'
        . '</div>';
    $privacy = '<p class="tmd-landing-form__privacy">'
        . '[checkbox* privacidad use_label_element "He leído y autorizo el tratamiento de mis datos"]'
        . '</p><p class="tmd-landing-form__privacy-copy">Consulta nuestra '
        . '<a href="' . $privacy_url . '" target="_blank" rel="noopener">política de privacidad</a>.'
        . '</p>';

    $consent = '<p class="tmd-landing-form__privacy">'
        . '[checkbox* privacidad use_label_element "He leído y autorizo el tratamiento de mis datos"]'
        . '</p>';
    $privacy_link = '<a href="' . $privacy_url . '" target="_blank" rel="noopener">política de privacidad</a>';
    $battery_maqueta_privacy = $consent . '<p class="tmd-landing-form__privacy-copy">Usamos tus datos solo para responder esta solicitud, según nuestra '
        . $privacy_link . '.</p>';

    if ('battery-maqueta' === $type) {
        return '<div class="tmd-landing-form">'
            . '<div class="tmd-landing-form__grid">'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Nombre y cargo</span>[text* nombre_cargo autocomplete:name]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Empresa y ciudad</span>[text* empresa_ciudad]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Correo o celular</span>[text* contacto]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Marca y modelo del montacargas</span>[text* marca_modelo]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Voltaje y capacidad de la batería actual</span>[text* voltaje_capacidad]</label>'
            . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Compra o alquiler</span>[select* modalidad include_blank "Compra" "Alquiler"]</label>'
            . '</div>' . $honeypot . $battery_maqueta_privacy
            . '<p class="tmd-landing-form__submit">[submit "Cotizar batería"]</p>'
            . '</div>';
    }

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
                    . "Requerimientos de carga, altura y pasillo: [requerimientos]\n",
            ]),
            'mail_2' => $secondary_mail,
        ],
        'battery' => [
            'title' => 'TMD | Cotización de baterías para montacargas',
            'locale' => $locale,
            'form' => tmd_commercial_landing_script_form_markup('battery-maqueta'),
            'mail' => array_merge($shared_mail, [
                'subject' => 'Nueva solicitud: batería para montacargas',
                'body' => "Solicitud de cotización de batería\n\n"
                    . "Nombre y cargo: [nombre_cargo]\nEmpresa y ciudad: [empresa_ciudad]\n"
                    . "Correo o celular: [contacto]\nMarca y modelo: [marca_modelo]\n"
                    . "Voltaje y capacidad actuales: [voltaje_capacidad]\n"
                    . "Modalidad: [modalidad]\n",
            ]),
            'mail_2' => $secondary_mail,
        ],
    ];
}

function tmd_commercial_landing_script_resolve_recipient(string $override, string $fallback): string
{
    $override = trim($override);
    if ('' !== $override) {
        if (! is_string(is_email($override))) {
            throw new InvalidArgumentException('El destinatario indicado para los nuevos formularios no es válido.');
        }

        return $override;
    }

    $fallback = trim($fallback);
    if ('' === $fallback) {
        throw new InvalidArgumentException('No hay un destinatario configurado para los nuevos formularios.');
    }

    return $fallback;
}

function tmd_commercial_landing_script_page_content(string $type, int $form_id): string
{
    $asset_root = esc_url_raw(untrailingslashit(get_stylesheet_directory_uri()) . '/assets/img');
    $blocks = [];

    if ('battery' === $type) {
        return tmd_commercial_landing_script_battery_content($asset_root, $form_id);
    }

    if ('rental' === $type) {
        return tmd_commercial_landing_script_rental_v2_content($form_id, $asset_root);
    }

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__hero tmd-commercial-landing__hero--battery" aria-labelledby="tmd-battery-heading">
  <div class="tmd-commercial-landing__hero-media"><img src="TMD_ASSETS/commercial-landings/baterias-hero.jpeg" alt="" fetchpriority="high" decoding="async"></div>
  <div class="tmd-commercial-landing__hero-content">
    <div class="tmd-commercial-landing__hero-copy">
      <span class="tmd-commercial-landing__eyebrow">Representantes de la Marca Barbillon</span>
      <h1 id="tmd-battery-heading">Baterías para <em>montacargas eléctricos</em></h1>
      <p class="tmd-commercial-landing__hero-lead">Venta y alquiler para flotas de bodega, con cargador del mismo voltaje y registro BMS de la carga y la descarga.</p>
      <a class="tmd-commercial-landing__button" href="#formulario-baterias">Cotizar batería</a>
      <div class="tmd-commercial-landing__hero-meta">
        <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#128CEB" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="vertical-align:-0.18em;margin-right:8px"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>Cobertura nacional en Colombia</span>
        <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#128CEB" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="vertical-align:-0.18em;margin-right:8px"><path d="m3 10 9-6 9 6v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 21v-6h6v6M7 12h.01M12 12h.01M17 12h.01"/></svg>Instalación en la bodega del cliente</span>
        <span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#128CEB" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="vertical-align:-0.18em;margin-right:8px"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18M8 15h.01M12 15h.01M16 15h.01"/></svg>Alquiler desde 1 mes</span>
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

function tmd_commercial_landing_script_battery_content(string $asset_root, int $form_id): string
{
    $blocks = [];

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__hero tmd-commercial-landing__hero--battery tmd-commercial-landing__hero--battery-maqueta" aria-labelledby="tmd-battery-heading">
  <div class="tmd-commercial-landing__hero-media"><img src="TMD_ASSETS/commercial-landings/baterias-maqueta/banner-crown-rd-3000.webp" alt="" fetchpriority="high" decoding="async"></div>
  <div class="tmd-commercial-landing__hero-content">
    <div class="tmd-commercial-landing__hero-copy">
      <h1 id="tmd-battery-heading">Baterías para montacargas <em>eléctricos</em></h1>
      <h2 class="tmd-commercial-landing__hero-subhead">Representantes de la marca francesa Barbillon</h2>
      <p class="tmd-commercial-landing__hero-lead">Venta y alquiler para flotas de bodega, con cargador del mismo voltaje y registro BMS de la carga y la descarga.</p>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--battery-solutions" aria-labelledby="tmd-battery-solutions-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading tmd-commercial-landing__section-heading--split">
      <h2 id="tmd-battery-solutions-heading">Baterías de tracción, cargadores y monitoreo BMS</h2>
      <h3>Compatibles con retráctiles, apiladores, estibadores, tomapedidos y equipos de pasillo angosto</h3>
    </div>
    <div class="tmd-commercial-landing__solution-grid">
      <a class="tmd-commercial-landing__solution-card" href="/energia/baterias/plomo/">
        <img src="TMD_ASSETS/mega-menu/energy-baterias-plomo.webp" alt="" loading="lazy" decoding="async">
        <div class="tmd-commercial-landing__solution-copy">
          <h3>Baterías de tracción plomo-ácido:</h3>
          <p>seleccionadas por voltaje, amperios hora y dimensiones del compartimiento.</p>
          <span class="tmd-commercial-landing__solution-arrow" aria-hidden="true">→</span>
        </div>
      </a>
      <a class="tmd-commercial-landing__solution-card" href="/energia/cargadores/">
        <img src="TMD_ASSETS/mega-menu/energy-cargadores.png" alt="" loading="lazy" decoding="async">
        <div class="tmd-commercial-landing__solution-copy">
          <h3>Cargadores industriales:</h3>
          <p>corriente y voltaje definidos según la tecnología de la batería.</p>
          <span class="tmd-commercial-landing__solution-arrow" aria-hidden="true">→</span>
        </div>
      </a>
      <a class="tmd-commercial-landing__solution-card" href="/energia/bms/">
        <img src="TMD_ASSETS/mega-menu/energy-bms.webp" alt="" loading="lazy" decoding="async">
        <div class="tmd-commercial-landing__solution-copy">
          <h3>Monitoreo BMS:</h3>
          <p>registro de temperatura, ciclos y descargas profundas durante la operación.</p>
          <span class="tmd-commercial-landing__solution-arrow" aria-hidden="true">→</span>
        </div>
      </a>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--battery-differential" aria-labelledby="tmd-battery-differential-heading">
  <div class="tmd-commercial-landing__battery-differential-media" aria-hidden="true"><img src="TMD_ASSETS/commercial-landings/baterias-maqueta/diferencial-hyster-e50z-33.webp" alt="" loading="lazy" decoding="async"></div>
  <div class="tmd-commercial-landing__container tmd-commercial-landing__battery-differential-copy">
    <h2 id="tmd-battery-differential-heading">Referencias en <em>inventario</em></h2>
    <h3>Referencias en bodega para reemplazar la batería sin esperar un pedido de importación</h3>
    <p>Cotizamos las referencias en existencia junto con su cargador. Si la batería de tu equipo necesita mantenimiento y no reemplazo, nuestro servicio técnico revisa las conexiones, el nivel de electrolito y el comportamiento de carga antes de recomendarte una batería nueva.</p>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--soft tmd-commercial-landing__section--battery-benefits" aria-labelledby="tmd-battery-benefits-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading tmd-commercial-landing__section-heading--benefits">
      <div>
        <h2 id="tmd-battery-benefits-heading">Rendimiento de la batería de tracción por turno</h2>
        <h3>Descargas profundas y cargas incompletas acortan la vida de las celdas</h3>
      </div>
      <img src="TMD_ASSETS/commercial-landings/baterias-maqueta/ventajas-etv214.webp" alt="Montacargas eléctrico trabajando en una bodega" loading="lazy" decoding="async">
    </div>
    <div class="tmd-commercial-landing__battery-benefit-grid">
      <article class="tmd-commercial-landing__battery-benefit">
        <span class="tmd-commercial-landing__battery-benefit-icon" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><circle cx="24" cy="24" r="17"></circle><path d="M24 14v11l8 5"></path></svg></span>
        <p><strong>Autonomía para el turno:</strong> amperios hora calculados sobre las horas de uso del equipo.</p>
      </article>
      <article class="tmd-commercial-landing__battery-benefit">
        <span class="tmd-commercial-landing__battery-benefit-icon" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><path d="M38 20a15 15 0 0 0-26-8L8 16"></path><path d="M8 8v8h8"></path><path d="M10 28a15 15 0 0 0 26 8l4-4"></path><path d="M40 40v-8h-8"></path></svg></span>
        <p><strong>Vida útil de las celdas:</strong> ciclos de carga completos con un cargador del mismo voltaje.</p>
      </article>
      <article class="tmd-commercial-landing__battery-benefit">
        <span class="tmd-commercial-landing__battery-benefit-icon" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><path d="M27 5 12 27h11l-2 16 15-23H25l2-15Z"></path></svg></span>
        <p><strong>Carga entre turnos:</strong> corriente del cargador ajustada al tiempo disponible para completar el ciclo.</p>
      </article>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--dark tmd-commercial-landing__section--battery-process" aria-labelledby="tmd-battery-process-heading">
  <div class="tmd-commercial-landing__battery-process-media" aria-hidden="true"><img src="TMD_ASSETS/commercial-landings/baterias-maqueta/proceso-efg425.webp" alt="" loading="lazy" decoding="async"></div>
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading tmd-commercial-landing__section-heading--split">
      <h2 id="tmd-battery-process-heading">Cambio de acumulador paso a paso</h2>
      <h3>Marca, modelo y ficha de la batería actual definen la referencia compatible</h3>
    </div>
    <div class="tmd-commercial-landing__battery-process-grid">
      <article class="tmd-commercial-landing__battery-process-step">
        <span class="tmd-commercial-landing__battery-process-number" aria-hidden="true">01.</span>
        <h3>Datos del equipo:</h3>
        <p>marca y modelo del montacargas, y voltaje y capacidad de la batería actual.</p>
      </article>
      <article class="tmd-commercial-landing__battery-process-step">
        <span class="tmd-commercial-landing__battery-process-number" aria-hidden="true">02.</span>
        <h3>Validación técnica:</h3>
        <p>comparamos dimensiones, peso, conector y cargador con la referencia propuesta.</p>
      </article>
      <article class="tmd-commercial-landing__battery-process-step">
        <span class="tmd-commercial-landing__battery-process-number" aria-hidden="true">03.</span>
        <h3>Cotización:</h3>
        <p>recibes la opción de compra o alquiler, con el cargador que corresponde si el actual no sirve.</p>
      </article>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_block(str_replace('TMD_ASSETS', $asset_root, <<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--battery-gallery" aria-labelledby="tmd-battery-gallery-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading tmd-commercial-landing__section-heading--stacked">
      <h2 id="tmd-battery-gallery-heading">Baterías y equipos eléctricos en operación</h2>
      <h3>Soluciones de energía y montacargas en entornos de trabajo</h3>
    </div>
    <div class="tmd-commercial-landing__battery-gallery">
      <figure><img src="TMD_ASSETS/commercial-landings/baterias-maqueta/galeria-crown-rd-5200.webp" alt="Montacargas eléctrico de pasillo angosto en bodega" loading="lazy" decoding="async"></figure>
      <figure><img src="TMD_ASSETS/commercial-landings/baterias-hero.jpeg" alt="Vista de celdas y terminales de una batería de tracción" loading="lazy" decoding="async"></figure>
      <figure><img src="TMD_ASSETS/mega-menu/energy-baterias-plomo.webp" alt="Batería industrial de plomo-ácido con conector" loading="lazy" decoding="async"></figure>
      <figure><img src="TMD_ASSETS/mega-menu/energy-bms.webp" alt="Batería de tracción con monitoreo junto a equipos en bodega" loading="lazy" decoding="async"></figure>
      <figure><img src="TMD_ASSETS/commercial-landings/baterias-maqueta/galeria-etv325.webp" alt="Montacargas eléctrico para trabajo en pasillo" loading="lazy" decoding="async"></figure>
    </div>
  </div>
</section>
HTML));

    $blocks[] = tmd_commercial_landing_script_shortcode('[tmd_commercial_landing_form id="' . intval($form_id) . '" type="battery-maqueta"]');

    $blocks[] = tmd_commercial_landing_script_block(<<<'HTML'
<section class="tmd-commercial-landing tmd-commercial-landing__section tmd-commercial-landing__section--battery-faq" aria-labelledby="tmd-battery-faq-heading">
  <div class="tmd-commercial-landing__container">
    <div class="tmd-commercial-landing__section-heading tmd-commercial-landing__section-heading--split">
      <h2 id="tmd-battery-faq-heading">Preguntas frecuentes sobre baterías para montacargas</h2>
      <h3>Compatibilidad, carga, mantenimiento y monitoreo de la batería de tracción</h3>
    </div>
    <div class="tmd-commercial-landing__faq-grid tmd-commercial-landing__faq-grid--battery">
      <div class="tmd-commercial-landing__faq-column">
        <details class="tmd-commercial-landing__faq-item"><summary>¿Qué batería necesita mi montacargas eléctrico?</summary><div class="tmd-commercial-landing__faq-answer"><p>La que coincide con el equipo en voltaje, amperios hora, dimensiones, peso y tipo y posición del conector, y que rinde las horas que trabaja por turno. Si una de esas medidas no coincide, la batería no entra en el compartimiento, no conecta o no alcanza para la jornada.</p></div></details>
        <details class="tmd-commercial-landing__faq-item"><summary>¿Cuánto dura una batería de tracción para montacargas?</summary><div class="tmd-commercial-landing__faq-answer"><p>Depende de los ciclos de carga que cumple, de las horas de trabajo por turno y del mantenimiento. Un cargador que no corresponde a su voltaje o a su tecnología, un nivel de electrolito descuidado y los turnos sin tiempo para completar la carga reducen la vida útil de las celdas.</p></div></details>
        <details class="tmd-commercial-landing__faq-item"><summary>¿Venden y alquilan baterías para montacargas?</summary><div class="tmd-commercial-landing__faq-answer"><p>Sí. Puedes comprar la batería o alquilarla, según tu presupuesto y la vida útil que le quede al equipo. Tenemos baterías en inventario, y la disponibilidad de la referencia se confirma con el voltaje, la capacidad y las dimensiones. Si necesitas también el equipo, alquilamos montacargas eléctricos.</p></div></details>
      </div>
      <div class="tmd-commercial-landing__faq-column">
        <details class="tmd-commercial-landing__faq-item"><summary>¿Qué mantenimiento necesita una batería de plomo-ácido?</summary><div class="tmd-commercial-landing__faq-answer"><p>Control del nivel de electrolito, revisión de conexiones, limpieza y ciclos de carga completos. La frecuencia se programa por horas de uso, turnos e historial de fallas más que por calendario, y cambia entre una operación de un turno y otra de varios turnos por día.</p></div></details>
        <details class="tmd-commercial-landing__faq-item"><summary>¿Para qué sirve el BMS en una batería de tracción?</summary><div class="tmd-commercial-landing__faq-answer"><p>Es un sistema de monitoreo que mide y registra el voltaje, la corriente, la temperatura, el estado de carga, las horas de operación y los ciclos de la batería. Con esos datos se identifican descargas profundas, cargas incompletas y pérdidas de autonomía, y se decide el mantenimiento con información de la operación real.</p></div></details>
        <details class="tmd-commercial-landing__faq-item"><summary>¿El cargador actual sirve para una batería nueva?</summary><div class="tmd-commercial-landing__faq-answer"><p>Solo si corresponde al voltaje nominal y a la tecnología de la batería nueva, y si su corriente de carga está calculada para esos amperios hora. Un cargador de otra tecnología o de menor corriente no completa la carga entre turnos. Si el actual no sirve, la cotización incluye el cargador que corresponde.</p></div></details>
      </div>
    </div>
  </div>
</section>
HTML);

    $blocks[] = tmd_commercial_landing_script_shortcode(
        '[tmd_commercial_landing_related_section topic="baterias" heading="Artículos sobre carga y electrolito del plomo-ácido" subtitle="Lectura de los registros del BMS y cuidado de las conexiones" eyebrow="" fallback="none"]'
    );

    return implode("\n\n", $blocks);
}



function tmd_commercial_landing_script_page_specs(array $form_ids): array
{
    return [
        'rental' => [
            'slug' => 'alquiler-montacargas-electricos',
            'title' => 'Venta o alquiler de montacargas eléctricos',
            'rank_title' => 'Venta o alquiler de montacargas eléctricos | Tecnimontacargas',
            'rank_description' => 'Venta o alquiler de montacargas eléctricos para bodegas, centros de distribución y plantas. Alquiler sin operador desde 15 días, con recomendación técnica según tu operación.',
            'seed' => '2026-10-05-v2:rental',
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
        $existing_recipient = trim((string) ($properties['mail']['recipient'] ?? ''));
        $expected_recipient = trim((string) ($spec['mail']['recipient'] ?? ''));
        if ($existing_recipient !== $expected_recipient) {
            throw new RuntimeException('El destinatario del formulario administrado para ' . $type . ' difiere del configurado. Requiere revisión manual.');
        }
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

function tmd_commercial_landing_script_battery_mail_body(): string
{
    return "Solicitud de cotización de batería\n\n"
        . "Nombre y cargo: [nombre_cargo]\nEmpresa y ciudad: [empresa_ciudad]\n"
        . "Correo o celular: [contacto]\nMarca y modelo: [marca_modelo]\n"
        . "Voltaje y capacidad actuales: [voltaje_capacidad]\nModalidad: [modalidad]\n";
}

function tmd_commercial_landing_script_hash_form_properties(array $properties): string
{
    $encoded = wp_json_encode($properties, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded)) {
        throw new RuntimeException('No fue posible calcular la huella de la configuración del formulario.');
    }

    return hash('sha256', $encoded);
}

function tmd_commercial_landing_script_transaction_is_active(): ?bool
{
    global $wpdb;

    $state = $wpdb->get_var('SELECT @@in_transaction');
    if ('' !== (string) $wpdb->last_error || ! in_array((string) $state, ['0', '1'], true)) {
        return null;
    }

    return '1' === (string) $state;
}

function tmd_commercial_landing_script_battery_state_hashes(): array
{
    clean_post_cache(1559);
    clean_post_cache(1557);

    $page = get_post(1559);
    $form = function_exists('wpcf7_contact_form') ? wpcf7_contact_form(1557) : null;

    return [
        'page' => $page instanceof WP_Post ? hash('sha256', (string) $page->post_content) : null,
        'form' => $form instanceof WPCF7_ContactForm
            ? tmd_commercial_landing_script_hash_form_properties($form->get_properties())
            : null,
    ];
}

function tmd_commercial_landing_script_save_battery_rollback_artifact(
    WP_Post $page,
    array $form_properties,
    string $page_sha256,
    string $form_sha256
): string {
    $backup_path = realpath((string) getenv('TMD_VERIFIED_BACKUP_PATH'));
    if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_writable($backup_path)) {
        throw new RuntimeException('No se pudo guardar el snapshot privado de página y formulario.');
    }
    $directory_permissions = fileperms($backup_path);
    if (false === $directory_permissions || 0 !== ($directory_permissions & 0077)) {
        throw new RuntimeException('El directorio del backup debe conservar permisos privados para el snapshot.');
    }

    $artifact_path = $backup_path . DIRECTORY_SEPARATOR . 'battery-page-1559-form-1557-before-maqueta.json';
    $payload = [
        'page_id' => 1559,
        'page_slug' => $page->post_name,
        'page_status' => $page->post_status,
        'page_sha256' => $page_sha256,
        'page_content' => $page->post_content,
        'form_id' => 1557,
        'form_sha256' => $form_sha256,
        'form_properties' => $form_properties,
    ];
    $encoded = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (! is_string($encoded)) {
        throw new RuntimeException('No se pudo serializar el snapshot privado de página y formulario.');
    }

    $handle = @fopen($artifact_path, 'x');
    if (! is_resource($handle)) {
        throw new RuntimeException('El snapshot ya existe o no pudo crearse; no se modificó producción.');
    }
    try {
        @chmod($artifact_path, 0600);
        $written = fwrite($handle, $encoded);
        if (false === $written || $written !== strlen($encoded) || ! fflush($handle)) {
            throw new RuntimeException('No se pudo escribir el snapshot privado completo.');
        }
    } catch (Throwable $exception) {
        fclose($handle);
        @unlink($artifact_path);
        throw $exception;
    }
    fclose($handle);
    @chmod($artifact_path, 0600);

    $permissions = fileperms($artifact_path);
    $saved = json_decode((string) file_get_contents($artifact_path), true);
    if (false === $permissions
        || 0600 !== ($permissions & 0777)
        || ! is_array($saved)
        || 1559 !== ($saved['page_id'] ?? null)
        || 1557 !== ($saved['form_id'] ?? null)
        || ! is_string($saved['page_content'] ?? null)
        || ! hash_equals($page_sha256, hash('sha256', $saved['page_content']))
        || ! is_array($saved['form_properties'] ?? null)
        || ! hash_equals($form_sha256, tmd_commercial_landing_script_hash_form_properties($saved['form_properties']))) {
        @unlink($artifact_path);
        throw new RuntimeException('El snapshot privado de página y formulario no pasó su verificación.');
    }

    return $artifact_path;
}

function tmd_commercial_landing_script_run_battery_maqueta_update(bool $execute): void
{
    if (! class_exists('WPCF7_ContactForm')
        || ! function_exists('wpcf7_save_contact_form')
        || ! function_exists('wpcf7_contact_form')) {
        throw new RuntimeException('Contact Form 7 o su API de guardado no está disponible.');
    }

    $lock_path = trailingslashit(get_temp_dir()) . 'tmd-commercial-landings-seed.lock';
    $lock = @fopen($lock_path, 'c');
    if (! is_resource($lock) || ! flock($lock, LOCK_EX | LOCK_NB)) {
        is_resource($lock) && fclose($lock);
        throw new RuntimeException('No se pudo adquirir el bloqueo exclusivo de páginas comerciales.');
    }
    @chmod($lock_path, 0600);

    try {
        $expected_page_sha256 = trim((string) getenv('TMD_BATTERY_PAGE_EXPECTED_SHA256'));
        $target_page_sha256 = trim((string) getenv('TMD_BATTERY_PAGE_TARGET_SHA256'));
        $expected_form_sha256 = trim((string) getenv('TMD_BATTERY_FORM_EXPECTED_SHA256'));
        $target_form_sha256 = trim((string) getenv('TMD_BATTERY_FORM_TARGET_SHA256'));
        foreach ([$expected_page_sha256, $target_page_sha256, $expected_form_sha256, $target_form_sha256] as $sha256) {
            if (! preg_match('/\A[a-f0-9]{64}\z/', $sha256)) {
                throw new RuntimeException('La actualización exige hashes SHA-256 válidos para página y formulario.');
            }
        }

        $page = get_post(1559);
        $form = wpcf7_contact_form(1557);
        if (! $page instanceof WP_Post
            || 'page' !== $page->post_type
            || 'baterias-para-montacargas' !== $page->post_name
            || 'publish' !== $page->post_status) {
            throw new RuntimeException('La página publicada 1559 no coincide con el slug y estado esperados.');
        }
        if (! $form instanceof WPCF7_ContactForm
            || 'TMD | Cotización de baterías para montacargas' !== $form->title()
            || '2026-10-04-v1:battery' !== (string) get_post_meta(1557, '_tmd_commercial_landing_form_seed', true)) {
            throw new RuntimeException('El formulario 1557 no coincide con el formulario administrado de baterías.');
        }

        $target_page_content = tmd_commercial_landing_script_page_content('battery', 1557);
        $target_page_hash = hash('sha256', $target_page_content);
        $current_page_hash = hash('sha256', (string) $page->post_content);
        $current_form_properties = $form->get_properties();
        $target_form_properties = $current_form_properties;
        $target_form_properties['form'] = tmd_commercial_landing_script_form_markup('battery-maqueta');
        if (! isset($target_form_properties['mail']) || ! is_array($target_form_properties['mail'])) {
            throw new RuntimeException('El correo principal de CF7 1557 no tiene una configuración reconocible.');
        }
        $target_form_properties['mail']['body'] = tmd_commercial_landing_script_battery_mail_body();
        $current_form_hash = tmd_commercial_landing_script_hash_form_properties($current_form_properties);
        $target_form_hash = tmd_commercial_landing_script_hash_form_properties($target_form_properties);
        $target_form_markup = (string) $target_form_properties['form'];
        $target_form_fields = preg_match_all('/<label class="tmd-landing-form__field/', $target_form_markup);

        if (! hash_equals($expected_page_sha256, $current_page_hash)
            || ! hash_equals($target_page_sha256, $target_page_hash)
            || ! hash_equals($expected_form_sha256, $current_form_hash)
            || ! hash_equals($target_form_sha256, $target_form_hash)
            || 6 !== $target_form_fields
            || 1 !== substr_count($target_form_markup, '[checkbox* privacidad')
            || false === strpos($target_form_markup, 'Usamos tus datos solo para responder esta solicitud, según nuestra ')) {
            throw new RuntimeException('Las huellas de origen o destino no coinciden; página y formulario no se modificaron.');
        }
        if (substr_count($target_page_content, '<section') !== 7
            || 1 !== substr_count($target_page_content, 'type="battery-maqueta"')
            || 1 !== substr_count($target_page_content, 'fallback="none"')
            || substr_count($target_page_content, '<details') !== 6
            || false !== strpos($target_page_content, 'tmd-commercial-landing__hero-meta')
            || false !== strpos($target_page_content, 'Cotizar batería</a>')) {
            throw new RuntimeException('El contenido destino no satisface la estructura acotada de DEC-11.');
        }

        WP_CLI::line('Página 1559 /baterias-para-montacargas/: ' . $current_page_hash . ' → ' . $target_page_hash . '.');
        WP_CLI::line('Formulario Contact Form 7 1557: ' . $current_form_hash . ' → ' . $target_form_hash . '.');
        WP_CLI::line('Contenido almacenado: 7 secciones HTML y dos shortcodes administrados (formulario y artículos relacionados).');
        if (! $execute) {
            WP_CLI::success('Dry-run de la maqueta y el formulario sin escrituras.');
            return;
        }

        if (! tmd_commercial_landing_script_backup_is_valid()) {
            throw new RuntimeException('Ejecución detenida: se requiere un backup de producción reciente y verificado.');
        }

        global $wpdb;
        foreach ([$wpdb->posts, $wpdb->postmeta] as $table_name) {
            $table_engine = $wpdb->get_var($wpdb->prepare(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
                $table_name
            ));
            if ('INNODB' !== strtoupper((string) $table_engine)) {
                throw new RuntimeException('Las tablas de contenido y metadatos deben usar InnoDB para proteger el update con transacción.');
            }
        }

        if (false === $wpdb->query('START TRANSACTION')) {
            throw new RuntimeException('No se pudo iniciar la transacción para página y formulario.');
        }
        $transaction_open = true;
        try {
            if (true !== tmd_commercial_landing_script_transaction_is_active()) {
                throw new RuntimeException('La transacción no quedó activa; no se modificó producción.');
            }

            $locked_row = $wpdb->get_row($wpdb->prepare(
                "SELECT ID, post_type, post_name, post_status, post_content FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
                1559
            ), ARRAY_A);
            $locked_form_row = $wpdb->get_row($wpdb->prepare(
                "SELECT ID, post_type, post_title, post_status FROM {$wpdb->posts} WHERE ID = %d FOR UPDATE",
                1557
            ), ARRAY_A);
            $locked_meta_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d FOR UPDATE",
                1557
            ), ARRAY_A);
            if (! is_array($locked_meta_rows) || '' !== (string) $wpdb->last_error) {
                throw new RuntimeException('No se pudo bloquear la configuración del formulario; no se modificó producción.');
            }
            if (true !== tmd_commercial_landing_script_transaction_is_active()) {
                throw new RuntimeException('La transacción terminó durante los bloqueos; no se modificó producción.');
            }
            clean_post_cache(1559);
            clean_post_cache(1557);
            $locked_page = get_post(1559);
            $locked_form = wpcf7_contact_form(1557);
            $locked_form_properties = $locked_form instanceof WPCF7_ContactForm ? $locked_form->get_properties() : [];
            $locked_page_hash = $locked_page instanceof WP_Post ? hash('sha256', (string) $locked_page->post_content) : '';
            $locked_form_hash = tmd_commercial_landing_script_hash_form_properties($locked_form_properties);

            if (! is_array($locked_row)
                || 'page' !== $locked_row['post_type']
                || ! $locked_page instanceof WP_Post
                || 'baterias-para-montacargas' !== $locked_row['post_name']
                || 'publish' !== $locked_row['post_status']
                || ! is_array($locked_form_row)
                || 'wpcf7_contact_form' !== $locked_form_row['post_type']
                || 'TMD | Cotización de baterías para montacargas' !== $locked_form_row['post_title']
                || 'publish' !== $locked_form_row['post_status']
                || ! $locked_form instanceof WPCF7_ContactForm
                || 'TMD | Cotización de baterías para montacargas' !== $locked_form->title()
                || '2026-10-04-v1:battery' !== (string) get_post_meta(1557, '_tmd_commercial_landing_form_seed', true)
                || ! hash_equals($expected_page_sha256, $locked_page_hash)
                || ! hash_equals($expected_form_sha256, $locked_form_hash)) {
                throw new RuntimeException('La página o el formulario cambiaron antes del bloqueo; no se modificó producción.');
            }

            tmd_commercial_landing_script_save_battery_rollback_artifact(
                $locked_page,
                $locked_form_properties,
                $locked_page_hash,
                $locked_form_hash
            );

            $locked_target_form_properties = $locked_form_properties;
            $locked_target_form_properties['form'] = tmd_commercial_landing_script_form_markup('battery-maqueta');
            if (! isset($locked_target_form_properties['mail']) || ! is_array($locked_target_form_properties['mail'])) {
                throw new RuntimeException('El correo principal de CF7 1557 cambió durante la actualización.');
            }
            $locked_target_form_properties['mail']['body'] = tmd_commercial_landing_script_battery_mail_body();
            if (! hash_equals($target_form_sha256, tmd_commercial_landing_script_hash_form_properties($locked_target_form_properties))) {
                throw new RuntimeException('La configuración destino del formulario cambió bajo el bloqueo; no se modificó producción.');
            }

            $saved_form = wpcf7_save_contact_form([
                'id' => 1557,
                'title' => $locked_form->title(),
                'locale' => $locked_form->locale(),
                'form' => $locked_target_form_properties['form'],
                'mail' => $locked_target_form_properties['mail'],
                'mail_2' => $locked_target_form_properties['mail_2'] ?? [],
                'messages' => $locked_target_form_properties['messages'] ?? [],
                'additional_settings' => $locked_target_form_properties['additional_settings'] ?? '',
            ], 'save');
            if (! $saved_form instanceof WPCF7_ContactForm
                || 1557 !== (int) $saved_form->id()
                || ! hash_equals($target_form_hash, tmd_commercial_landing_script_hash_form_properties($saved_form->get_properties()))) {
                throw new RuntimeException('No se verificó el guardado del formulario 1557.');
            }
            if (true !== tmd_commercial_landing_script_transaction_is_active()) {
                throw new RuntimeException('La transacción terminó durante el guardado del formulario; no se actualizó la página.');
            }

            $updated_page_id = wp_update_post([
                'ID' => 1559,
                'post_content' => wp_slash($target_page_content),
            ], true);
            $updated_page = get_post(1559);
            if (is_wp_error($updated_page_id)
                || 1559 !== (int) $updated_page_id
                || ! $updated_page instanceof WP_Post
                || ! hash_equals($target_page_sha256, hash('sha256', (string) $updated_page->post_content))) {
                throw new RuntimeException('No se verificó el guardado del contenido de página 1559.');
            }

            if (true !== tmd_commercial_landing_script_transaction_is_active()) {
                throw new RuntimeException('La transacción terminó antes de confirmar página y formulario.');
            }
            if (false === $wpdb->query('COMMIT')) {
                throw new RuntimeException('No se pudo confirmar la transacción; se requiere revisar el backup antes de reintentar.');
            }
            $transaction_open = false;
            clean_post_cache(1559);
            clean_post_cache(1557);
            $committed_page = get_post(1559);
            $committed_form = wpcf7_contact_form(1557);
            if (! $committed_page instanceof WP_Post
                || ! hash_equals($target_page_sha256, hash('sha256', (string) $committed_page->post_content))
                || ! $committed_form instanceof WPCF7_ContactForm
                || ! hash_equals($target_form_sha256, tmd_commercial_landing_script_hash_form_properties($committed_form->get_properties()))) {
                throw new RuntimeException('El estado de página y formulario cambió después del COMMIT; no se aplicó rollback automático.');
            }

            WP_CLI::success('Maqueta DEC-11 aplicada a la página 1559 y al formulario 1557; hashes verificados.');
        } finally {
            if ($transaction_open) {
                $transaction_before_rollback = tmd_commercial_landing_script_transaction_is_active();
                $rollback_result = false;
                if (true === $transaction_before_rollback) {
                    $rollback_result = $wpdb->query('ROLLBACK');
                }
                $transaction_after_rollback = tmd_commercial_landing_script_transaction_is_active();
                $current_hashes = tmd_commercial_landing_script_battery_state_hashes();
                $source_state_verified = is_string($current_hashes['page'])
                    && is_string($current_hashes['form'])
                    && hash_equals($expected_page_sha256, $current_hashes['page'])
                    && hash_equals($expected_form_sha256, $current_hashes['form']);
                $target_state_observed = is_string($current_hashes['page'])
                    && is_string($current_hashes['form'])
                    && hash_equals($target_page_sha256, $current_hashes['page'])
                    && hash_equals($target_form_sha256, $current_hashes['form']);

                if (false !== $transaction_after_rollback) {
                    WP_CLI::line('ESTADO NO CONFIRMADO: la transacción sigue activa o su cierre es desconocido; estos hashes no prueban datos durables. Detén otras escrituras y no restaures ni reintentes hasta confirmar el cierre de la sesión y comparar página/formulario con el snapshot privado y el backup verificado.');
                } elseif ($source_state_verified) {
                    WP_CLI::line('Recuperación verificada: página y formulario conservan sus hashes de origen después del fallo.');
                } elseif ($target_state_observed) {
                    WP_CLI::line('Estado posterior al fallo: página y formulario coinciden con los hashes destino; verifica el resultado antes de reintentar.');
                } else {
                    WP_CLI::line('ESTADO INCIERTO: no reintentes ni restaures automáticamente. Compara los hashes actuales de la página 1559 y el formulario 1557 con el snapshot privado y el backup verificado; restaura ambos mediante el runbook antes de un nuevo intento.');
                }

                WP_CLI::line(sprintf(
                    'Cierre transaccional: activa antes=%s, ROLLBACK=%s, activa después=%s, hash página=%s, hash formulario=%s.',
                    null === $transaction_before_rollback ? 'desconocida' : ($transaction_before_rollback ? 'sí' : 'no'),
                    false === $rollback_result ? 'falló/no aplicaba' : 'aceptado',
                    null === $transaction_after_rollback ? 'desconocida' : ($transaction_after_rollback ? 'sí' : 'no'),
                    is_string($current_hashes['page']) ? $current_hashes['page'] : 'no disponible',
                    is_string($current_hashes['form']) ? $current_hashes['form'] : 'no disponible'
                ));
            }
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
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

$mode = trim((string) getenv('TMD_COMMERCIAL_LANDINGS_MODE'));
if ('update-rental-page' === $mode) {
    try {
        tmd_commercial_landing_script_run_rental_v2_update('1' === getenv('TMD_COMMERCIAL_LANDINGS_EXECUTE'));
    } catch (Throwable $exception) {
        WP_CLI::error($exception->getMessage());
    }
    return;
}
if ('update-battery-maqueta' === $mode) {
    try {
        tmd_commercial_landing_script_run_battery_maqueta_update('1' === getenv('TMD_COMMERCIAL_LANDINGS_EXECUTE'));
    } catch (Throwable $exception) {
        WP_CLI::error($exception->getMessage());
    }
    return;
}
if ('' !== $mode) {
    WP_CLI::error('El modo de ejecución comercial indicado no está reconocido.');
    return;
}

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
    $recipient = tmd_commercial_landing_script_resolve_recipient(
        (string) getenv('TMD_COMMERCIAL_LANDINGS_RECIPIENT'),
        (string) ($base_properties['mail']['recipient'] ?? '')
    );

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
        WP_CLI::line('Campos de alquiler: nombre/cargo, empresa/ciudad, correo o celular, necesidad, requerimientos y privacidad; control anti-spam server-side.');
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
