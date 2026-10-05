<?php
/**
 * Contenido editorial de alquiler v2. Se guarda como bloques HTML editables;
 * inventario y blog conservan sus shortcodes con datos de sus fuentes reales.
 */

if (! defined('ABSPATH') || ! defined('WP_CLI') || ! WP_CLI) {
    return;
}

function tmd_commercial_landing_script_rental_v2_content(int $form_id, string $asset_root): string
{
    $asset = static fn (string $name): string => esc_url_raw(
        untrailingslashit($asset_root) . '/commercial-landings-v2/' . rawurlencode($name)
    );
    $html_block = static fn (string $markup): string => "<!-- wp:html -->\n" . trim($markup) . "\n<!-- /wp:html -->";
    $shortcode_block = static fn (string $shortcode): string => "<!-- wp:shortcode -->\n" . trim($shortcode) . "\n<!-- /wp:shortcode -->";

    $brand_heading = 'Crown, Clark, Jungheinrich y Hyster en el inventario activo';
    $metrics_items = '<p><span class="tmd-rental-v2__metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17h11V5H4v12Zm11-7h4l3 4v3h-7v-7Z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/><path d="M6 8h5M6 11h5M14 14h3"/></svg></span><strong>Referencias activas en el inventario</strong></p>';
    $metrics_items .= '<p><span class="tmd-rental-v2__metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m14.7 6.3 3-3a4.5 4.5 0 0 0-5.9 5.9l-7.5 7.5a2.1 2.1 0 0 0 3 3l7.5-7.5a4.5 4.5 0 0 0 5.9-5.9l-3 3-4-1-1-4Z"/></svg></span><strong>Desde el año 2000 en servicio técnico</strong></p>';
    $metrics_items .= '<p><span class="tmd-rental-v2__metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18M8 15h.01M12 15h.01M16 15h.01"/></svg></span><strong>15 días de alquiler mínimo</strong></p>';
    $metrics = '<div class="tmd-rental-v2__metrics" aria-label="Datos de flota y servicio">' . $metrics_items . '</div>';

    $blocks = [];
    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__hero" aria-labelledby="tmd-rental-v2-heading">'
        . '<img class="tmd-rental-v2__hero-image" src="' . esc_url($asset('hero-dark.webp')) . '" alt="" fetchpriority="high" decoding="async">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__hero-content">'
        . '<h1 id="tmd-rental-v2-heading">Venta o alquiler de montacargas <span>eléctricos</span></h1>'
        . '<h2>Equipos propios con mantenimiento en nuestro taller técnico</h2>'
        . '<p>Contrabalanceados, reach, pantógrafos y apiladores para bodegas, centros de distribución y plantas que necesitan más equipos en operación sin comprarlos.</p>'
        . $metrics
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__description" aria-labelledby="tmd-rental-v2-description-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__description-layout"><div>'
        . '<h2 id="tmd-rental-v2-description-heading">Montacargas eléctricos para operación continua en bodega</h2>'
        . '<h3>' . esc_html($brand_heading) . '</h3>'
        . $metrics
        . '</div><img src="' . esc_url($asset('description.webp')) . '" alt="Montacargas eléctrico trabajando en una bodega" loading="lazy" decoding="async">'
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__needs" aria-labelledby="tmd-rental-v2-needs-heading">'
        . '<div class="tmd-rental-v2__container"><header class="tmd-rental-v2__section-heading">'
        . '<h2 id="tmd-rental-v2-needs-heading">Equipos para muelle, pasillo angosto y doble profundidad</h2>'
        . '<h3>El ancho del pasillo y la altura de la estiba definen la configuración</h3></header>'
        . '<div class="tmd-rental-v2__needs-grid">'
        . '<article><img src="' . esc_url($asset('counterbalance-reference.webp')) . '" alt="Montacargas contrabalanceado de referencia" loading="lazy" decoding="async"><h4>Carga y descarga en muelle</h4><p>Contrabalanceados eléctricos de tres y cuatro ruedas para plataformas y patios.</p></article>'
        . '<article><img src="' . esc_url($asset('reach-reference.webp')) . '" alt="Montacargas reach de referencia" loading="lazy" decoding="async"><h4>Pasillos angostos</h4><p>Reach y retráctiles de mástil móvil que elevan la estiba sin exigir más ancho.</p></article>'
        . '<article><img src="' . esc_url($asset('double-depth-reference.webp')) . '" alt="Montacargas de doble profundidad de referencia" loading="lazy" decoding="async"><h4>Estanterías de doble profundidad</h4><p>Pantógrafos que alcanzan la segunda estiba sin retirar la primera.</p></article>'
        . '<article><img src="' . esc_url($asset('low-level-reference.webp')) . '" alt="Estibador eléctrico de referencia" loading="lazy" decoding="async"><h4>Traslado a nivel de piso</h4><p>Estibadores y apiladores eléctricos para recorridos cortos y elevación baja.</p></article>'
        . '</div></div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__buy" aria-labelledby="tmd-rental-v2-buy-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__buy-layout"><div><h2 id="tmd-rental-v2-buy-heading">Alquiler mensual frente a compra de maquinaria</h2>'
        . '<h3>Capacidad adicional en tu bodega sin sumar un activo a tu balance</h3></div>'
        . '<img src="' . esc_url($asset('rental-vs-buy.webp')) . '" alt="Montacargas eléctrico en un centro de distribución" loading="lazy" decoding="async">'
        . '<ul><li><strong>Capital disponible:</strong> la tarifa mensual reemplaza la inversión en un equipo nuevo o usado.</li>'
        . '<li><strong>Tarifa por periodo:</strong> la cotización fija el valor del equipo según el tiempo de alquiler.</li>'
        . '<li><strong>Sin reventa ni bodegaje:</strong> al terminar, el equipo vuelve a nuestra flota y no ocupa espacio en tu bodega.</li></ul>'
        . '</div></section>');

    $blocks[] = $shortcode_block('[tmd_commercial_landing_inventory type="equipment" variant="rental-v2" eyebrow="Inventario real" heading="Contrabalanceados, reach y apiladores en inventario" intro="Compara capacidad y altura de levante antes de pedir la cotización" link_text="Ver todos los equipos" link_url="/equipos/"]');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__process" aria-labelledby="tmd-rental-v2-process-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__process-layout"><div><header class="tmd-rental-v2__section-heading">'
        . '<h2 id="tmd-rental-v2-process-heading">Alquiler de montacargas en cuatro pasos</h2>'
        . '<h3>La recomendación técnica del equipo llega antes que la tarifa del alquiler</h3></header>'
        . '<ol><li><strong>Datos de tu operación:</strong> peso de la carga, altura de las estanterías, ancho de pasillo y ciudad.</li>'
        . '<li><strong>Recomendación técnica:</strong> un asesor propone el tipo de equipo y la capacidad que requiere tu bodega.</li>'
        . '<li><strong>Cotización:</strong> recibes la tarifa, el periodo de alquiler y las condiciones de cada equipo.</li>'
        . '<li><strong>Entrega en tu sede:</strong> coordinamos el traslado del equipo hasta tu bodega en la fecha acordada.</li></ol>'
        . '</div><img src="' . esc_url($asset('process.webp')) . '" alt="Montacargas reach operando dentro de una bodega" loading="lazy" decoding="async">'
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__sectors" aria-labelledby="tmd-rental-v2-sectors-heading">'
        . '<img src="' . esc_url($asset('sectors.webp')) . '" alt="Operación de almacenamiento y despacho en un centro de distribución" loading="lazy" decoding="async">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__sectors-content"><header><h2 id="tmd-rental-v2-sectors-heading">Contrabalanceados y reach para logística, retail y manufactura</h2>'
        . '<h3>Operaciones que reciben, almacenan y despachan estibas a diario</h3></header><div class="tmd-rental-v2__sector-grid">'
        . '<article><h4>Operadores logísticos</h4><p>Recepción, almacenamiento y despacho de mercancía para varios clientes.</p></article>'
        . '<article><h4>Retail y almacenes de cadena</h4><p>Reposición de tiendas desde centros de distribución propios.</p></article>'
        . '<article><h4>Manufactura</h4><p>Materia prima y producto terminado entre la planta y la bodega.</p></article>'
        . '<article><h4>Distribuidoras de mercancía</h4><p>Rutas de despacho diario a clientes y puntos de venta.</p></article>'
        . '</div></div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__uses" aria-labelledby="tmd-rental-v2-uses-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__uses-layout"><img src="' . esc_url($asset('use-cases.webp')) . '" alt="Montacargas reach en un pasillo de estanterías" loading="lazy" decoding="async">'
        . '<div><header class="tmd-rental-v2__section-heading"><h2 id="tmd-rental-v2-uses-heading">Alquiler de montacargas por temporada o por proyecto</h2>'
        . '<h3>Equipos por semanas o meses según dure la operación que los necesita</h3></header><ul>'
        . '<li><strong>Temporada de septiembre a diciembre:</strong> refuerzas la flota para los despachos de fin de año.</li>'
        . '<li><strong>Apertura o traslado de bodega:</strong> operas desde el primer día mientras defines tu flota definitiva.</li>'
        . '<li><strong>Reemplazo durante una reparación:</strong> el equipo alquilado cubre el turno mientras el tuyo está en taller.</li>'
        . '<li><strong>Inventarios y proyectos puntuales:</strong> equipos por semanas para conteos, reorganizaciones o contratos temporales.</li>'
        . '</ul></div></div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__support" aria-labelledby="tmd-rental-v2-support-heading">'
        . '<div class="tmd-rental-v2__container"><header class="tmd-rental-v2__section-heading"><h2 id="tmd-rental-v2-support-heading">Servicio técnico propio para la flota en operación</h2>'
        . '<h3>Mantenimiento preventivo y correctivo con repuestos para montacargas de distintas marcas</h3></header>'
        . '<div class="tmd-rental-v2__support-layout"><img src="' . esc_url($asset('technical-support.webp')) . '" alt="Técnico revisando un montacargas" loading="lazy" decoding="async">'
        . '<div class="tmd-rental-v2__support-cards"><article><strong>Experiencia en alquiler desde 2013</strong><p>Servicio técnico propio para acompañar la operación.</p></article>'
        . '<article><strong>Reacción ante fallas</strong><p>Si un equipo alquilado se detiene, lo atiende nuestro servicio técnico, sin intermediarios.</p></article>'
        . '<article class="tmd-rental-v2__diagnosis"><strong>Diagnóstico antes de intervenir</strong><p>El alcance de la reparación se explica antes de ejecutarla.</p></article>'
        . '</div></div></div></section>');

    $blocks[] = $html_block('<div class="tmd-rental-v2-section tmd-rental-v2__quote-faq">'
        . '<section class="tmd-rental-v2__quote" aria-labelledby="tmd-rental-v2-quote-heading"><div class="tmd-rental-v2__quote-copy">'
        . '<h2 id="tmd-rental-v2-quote-heading">Cotiza el alquiler de montacargas en Bogotá</h2>'
        . '<h3>Un asesor comercial responde tu solicitud de lunes a viernes en horario laboral</h3></div>'
        . '<div class="tmd-rental-v2__form">[contact-form-7 id="' . absint($form_id) . '"]</div></section>'
        . '<section class="tmd-rental-v2__faq" aria-labelledby="tmd-rental-v2-faq-heading"><header><h2 id="tmd-rental-v2-faq-heading">Preguntas frecuentes sobre alquiler de montacargas</h2>'
        . '<h3>Condiciones del alquiler mensual de montacargas eléctricos antes de cotizar</h3></header><div class="tmd-rental-v2__faq-list">'
        . '<details open><summary>¿Cuál es el tiempo mínimo de alquiler de un montacargas?</summary><p>El alquiler mínimo es de 15 días, porque cada equipo implica un traslado de ida hasta tu sede y otro de regreso al terminar. Desde ese mínimo, el periodo se ajusta por meses a la duración de la operación y queda fijado en la cotización.</p></details>'
        . '<details><summary>¿El montacargas se alquila con operador?</summary><p>No. El equipo se alquila sin operador y lo conduce el personal de tu empresa. Por eso, en la recomendación técnica tenemos en cuenta la configuración que tus operarios ya manejan, contrabalanceado o reach, además del peso de la carga, la altura de las estanterías y el ancho de pasillo.</p></details>'
        . '<details><summary>¿Qué pasa si el montacargas alquilado presenta una falla?</summary><p>Lo atiende nuestro propio servicio técnico, el mismo que hace el mantenimiento preventivo y correctivo de la flota. Reportas la falla a tu asesor comercial, y el técnico diagnostica el equipo y define la reparación para que tu operación vuelva a moverse sin esperar a un tercero.</p></details>'
        . '<details><summary>¿Qué datos necesitan para cotizar el alquiler?</summary><p>El peso de la carga, la altura de las estanterías, el ancho de los pasillos, las horas de uso por día y la ciudad donde trabajará el equipo. Con esos datos evitamos recomendarte un equipo que no quepa en tus pasillos o que no alcance la altura de tu estantería.</p></details>'
        . '<details><summary>¿Cuánto cuesta el alquiler de un montacargas eléctrico?</summary><p>El valor depende del tipo de equipo, su capacidad, la altura de levante y el periodo de alquiler. Por eso no manejamos una tarifa única: con los datos de tu operación te enviamos la cotización del equipo que corresponde, sin sobredimensionarlo para la carga que mueves.</p></details>'
        . '<details><summary>¿Por qué elegir un montacargas eléctrico para trabajar en bodega?</summary><p>Porque no emite gases de combustión, hace menos ruido y maniobra con precisión en pasillos y estanterías, lo que lo hace adecuado para espacios cerrados y turnos junto al personal. Su energía viene de una batería de tracción, que también vendemos y alquilamos, y se recarga en una zona definida de la bodega.</p></details>'
        . '</div></section></div>');

    $blocks[] = $shortcode_block('[tmd_commercial_landing_related_section topic="montacargas" variant="rental-v2" eyebrow="Blog" heading="Guías técnicas de mantenimiento y baterías de tracción" subtitle="Vida útil de la batería de tracción y señales de mantenimiento preventivo"]');

    return implode("\n\n", $blocks);
}

function tmd_commercial_landing_rental_v2_form_markup(): string
{
    $privacy_url = esc_url(home_url('/nosotros/legal/politica-de-privacidad/'));
    $honeypot = '<div class="tmd-landing-form__honeypot" aria-hidden="true">'
        . '<label>Dejar este campo vacío [text tmd_website tabindex:-1 autocomplete:off]</label>'
        . '</div>';
    $privacy = '<p class="tmd-landing-form__privacy">'
        . '[checkbox* privacidad use_label_element "He leído y autorizo el tratamiento de mis datos"]'
        . '</p><p class="tmd-landing-form__privacy-copy">Usamos tus datos solo para responder esta solicitud, según nuestra '
        . '<a href="' . $privacy_url . '" target="_blank" rel="noopener">política de privacidad</a>.'
        . '</p>';

    return '<div class="tmd-landing-form">'
        . '<div class="tmd-landing-form__grid">'
        . '<fieldset class="tmd-landing-form__field tmd-landing-form__field--wide"><legend>Nombre y cargo</legend><div class="tmd-landing-form__field-pair"><label><span>Nombre</span>[text* nombre autocomplete:name]</label><label><span>Cargo</span>[text* cargo]</label></div></fieldset>'
        . '<fieldset class="tmd-landing-form__field tmd-landing-form__field--wide"><legend>Empresa y ciudad</legend><div class="tmd-landing-form__field-pair"><label><span>Empresa</span>[text* empresa]</label><label><span>Ciudad</span>[text* ciudad]</label></div></fieldset>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Correo o celular</span>[text* contacto placeholder "Escribe tu correo o número celular"]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Tipo de equipo o necesidad</span>[text* necesidad placeholder "Cuéntanos qué requiere tu operación"]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Peso de la carga, altura de levante y ancho de pasillo</span>[textarea requerimientos placeholder "Cuéntanos los requerimientos de tu operación"]</label>'
        . '</div>' . $honeypot . $privacy
        . '<p class="tmd-landing-form__submit">[submit "Solicitar cotización"]</p>'
        . '</div>';
}
