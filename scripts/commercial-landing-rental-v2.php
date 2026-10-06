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
    $page_url = static fn (string $path): string => esc_url(home_url($path));
    $html_block = static fn (string $markup): string => "<!-- wp:html -->\n" . trim($markup) . "\n<!-- /wp:html -->";
    $shortcode_block = static fn (string $shortcode): string => "<!-- wp:shortcode -->\n" . trim($shortcode) . "\n<!-- /wp:shortcode -->";

    $brand_heading = 'Yale, Crown, Clark, Jungheinrich y Hyster en la flota disponible';
    $metrics_items = '<p><span class="tmd-rental-v2__metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17h11V5H4v12Zm11-7h4l3 4v3h-7v-7Z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/><path d="M6 8h5M6 11h5M14 14h3"/></svg></span><strong>120 equipos en flota propia</strong></p>';
    $metrics_items .= '<p><span class="tmd-rental-v2__metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m14.7 6.3 3-3a4.5 4.5 0 0 0-5.9 5.9l-7.5 7.5a2.1 2.1 0 0 0 3 3l7.5-7.5a4.5 4.5 0 0 0 5.9-5.9l-3 3-4-1-1-4Z"/></svg></span><strong>Desde el año 2000 en servicio técnico</strong></p>';
    $metrics_items .= '<p><span class="tmd-rental-v2__metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18M8 15h.01M12 15h.01M16 15h.01"/></svg></span><strong>15 días de alquiler mínimo</strong></p>';
    $metrics = '<div class="tmd-rental-v2__metrics" aria-label="Datos de flota y servicio">' . $metrics_items . '</div>';

    $blocks = [];
    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__hero" aria-labelledby="tmd-rental-v2-heading">'
        . '<img class="tmd-rental-v2__hero-image" src="' . esc_url($asset('hero-dark.webp')) . '" alt="" fetchpriority="high" decoding="async">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__hero-content">'
        . '<h1 id="tmd-rental-v2-heading">Alquiler de <span>montacargas eléctricos</span></h1>'
        . '<h2>Equipos propios con mantenimiento en nuestro taller técnico</h2>'
        . '<p>Contrabalanceados, reach, pantógrafos y apiladores para bodegas, centros de distribución y plantas que necesitan más equipos en operación sin comprarlos.</p>'
        . $metrics
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__description" aria-labelledby="tmd-rental-v2-description-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__description-layout"><div>'
        . '<p class="tmd-rental-v2__eyebrow">SOLUCIÓN PARA TU OPERACIÓN</p>'
        . '<h2 id="tmd-rental-v2-description-heading">Montacargas eléctricos para operación continua en bodega</h2>'
        . '<h3>' . esc_html($brand_heading) . '</h3>'
        . $metrics
        . '<a class="tmd-rental-v2__description-cta" href="#tmd-rental-v2-quote">cotización →</a>'
        . '</div><div class="tmd-rental-v2__description-visual"><img src="' . esc_url($asset('description.webp')) . '" alt="Montacargas eléctrico trabajando en una bodega" loading="lazy" decoding="async">'
        . '<div class="tmd-rental-v2__description-badge"><span class="tmd-rental-v2__description-badge-accent" aria-hidden="true"></span>'
        . '<svg class="tmd-rental-v2__description-badge-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 6v29m0-24h3m-3 6h3m-3 6h3m-3 6h3M10 12h15l5 7v11H10V12Zm20 7h7l5 6v5H30M16 17h6m-6 5h6"/><circle cx="17" cy="33" r="4"/><circle cx="36" cy="33" r="4"/><path d="M3 38h42"/></svg>'
        . '<div class="tmd-rental-v2__description-badge-copy"><span>FLOTA DISPONIBLE</span><strong>Equipos listos para tu operación</strong></div>'
        . '</div></div>'
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__needs" aria-labelledby="tmd-rental-v2-needs-heading">'
        . '<div class="tmd-rental-v2__container"><header class="tmd-rental-v2__section-heading">'
        . '<p class="tmd-rental-v2__eyebrow">NUESTRA FLOTA</p>'
        . '<h2 id="tmd-rental-v2-needs-heading">Equipos para muelle, pasillo angosto y doble profundidad</h2>'
        . '<h3>El ancho del pasillo y la altura de la estiba definen la configuración</h3></header>'
        . '<div class="tmd-rental-v2__needs-grid">'
        . '<article><a href="' . $page_url('/equipos/tipos/contrabalanceados/') . '"><img src="' . esc_url($asset('counterbalance-reference.webp')) . '" alt="Montacargas contrabalanceado de referencia" loading="lazy" decoding="async"><h4>Carga y descarga en muelle:</h4><p>Contrabalanceados eléctricos de tres y cuatro ruedas para plataformas y patios.</p><span class="tmd-rental-v2__need-arrow" aria-hidden="true">→</span></a></article>'
        . '<article><a href="' . $page_url('/equipos/tipos/reach-retractiles/') . '"><img src="' . esc_url($asset('reach-reference.webp')) . '" alt="Montacargas reach de referencia" loading="lazy" decoding="async"><h4>Pasillos angostos:</h4><p>Reach y retráctiles de mástil móvil que elevan la estiba sin exigir más ancho.</p><span class="tmd-rental-v2__need-arrow" aria-hidden="true">→</span></a></article>'
        . '<article><a href="' . $page_url('/equipos/tipos/pantografo-doble-profundidad/') . '"><img src="' . esc_url($asset('double-depth-reference.webp')) . '" alt="Montacargas de doble profundidad de referencia" loading="lazy" decoding="async"><h4>Estanterías de doble profundidad:</h4><p>Pantógrafos que alcanzan la segunda estiba sin retirar la primera.</p><span class="tmd-rental-v2__need-arrow" aria-hidden="true">→</span></a></article>'
        . '<article><a href="' . $page_url('/equipos/tipos/estibadores-y-apiladores/') . '"><img src="' . esc_url($asset('low-level-reference.webp')) . '" alt="Estibador eléctrico de referencia" loading="lazy" decoding="async"><h4>Traslado a nivel de piso:</h4><p>Estibadores y apiladores eléctricos para recorridos cortos y elevación baja.</p><span class="tmd-rental-v2__need-arrow" aria-hidden="true">→</span></a></article>'
        . '</div></div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__buy" aria-labelledby="tmd-rental-v2-buy-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__buy-layout"><div class="tmd-rental-v2__buy-copy"><h2 id="tmd-rental-v2-buy-heading">Alquiler mensual frente a <span class="tmd-rental-v2__buy-heading-accent">compra de maquinaria</span></h2>'
        . '<h3 class="tmd-rental-v2__buy-subtitle">Capacidad adicional en tu bodega sin sumar un activo a tu balance</h3>'
        . '<ul class="tmd-rental-v2__buy-points">'
        . '<li><span class="tmd-rental-v2__buy-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg></span><span><strong>Capital disponible:</strong><p>la tarifa mensual reemplaza la inversión en un equipo nuevo o usado.</p></span></li>'
        . '<li><span class="tmd-rental-v2__buy-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span><strong>Tarifa por periodo:</strong><p>la cotización fija el valor del equipo según el tiempo de alquiler.</p></span></li>'
        . '<li><span class="tmd-rental-v2__buy-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h12l4 4v8H4z"/><path d="M8 7V4h7v3M8 14l2 2 4-4"/></svg></span><span><strong>Sin reventa ni bodegaje:</strong><p>al terminar, el equipo vuelve a nuestra flota y no ocupa espacio en tu bodega.</p></span></li>'
        . '</ul></div>'
        . '<img src="' . esc_url($asset('rental-vs-buy.webp')) . '" alt="Montacargas eléctrico en un centro de distribución" loading="lazy" decoding="async">'
        . '</div></section>');

    $blocks[] = $shortcode_block('[tmd_commercial_landing_inventory type="equipment" variant="rental-v2" eyebrow="Inventario real" heading="Contrabalanceados, reach y apiladores en inventario" intro="Compara capacidad y altura de levante antes de pedir la cotización" link_text="Ver todos los equipos" link_url="/equipos/"]');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__process" aria-labelledby="tmd-rental-v2-process-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__process-layout"><div class="tmd-rental-v2__process-copy"><header class="tmd-rental-v2__section-heading">'
        . '<p class="tmd-rental-v2__eyebrow">CÓMO FUNCIONA</p>'
        . '<h2 id="tmd-rental-v2-process-heading">Alquiler de montacargas en <span class="tmd-rental-v2__process-heading-accent">cuatro pasos</span></h2>'
        . '<h3>La recomendación técnica del equipo llega antes que la tarifa del alquiler</h3></header>'
        . '<ol><li><strong>Datos de tu operación:</strong><span> peso de la carga, altura de las estanterías, ancho de pasillo y ciudad.</span></li>'
        . '<li><strong>Recomendación técnica:</strong><span> un asesor propone el tipo de equipo y la capacidad que requiere tu bodega.</span></li>'
        . '<li><strong>Cotización:</strong><span> recibes la tarifa, el periodo de alquiler y las condiciones de cada equipo.</span></li>'
        . '<li><strong>Entrega en tu sede:</strong><span> coordinamos el traslado del equipo hasta tu bodega en la fecha acordada.</span></li></ol>'
        . '</div><img src="' . esc_url($asset('process.webp')) . '" alt="Montacargas reach operando dentro de una bodega" loading="lazy" decoding="async">'
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__sectors" aria-labelledby="tmd-rental-v2-sectors-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__sectors-layout"><header><h2 id="tmd-rental-v2-sectors-heading">Contrabalanceados y reach para <span>logística, retail y manufactura</span></h2>'
        . '<h3>Operaciones que reciben, almacenan y despachan estibas a diario</h3></header>'
        . '<img class="tmd-rental-v2__sectors-main-image" src="' . esc_url($asset('sectors-main.webp')) . '" alt="Operación de almacenamiento y despacho en un centro de distribución" loading="lazy" decoding="async">'
        . '<div class="tmd-rental-v2__sector-grid">'
        . '<article><img src="' . esc_url($asset('sector-logistics.webp')) . '" alt="Montacargas trabajando entre estanterías para almacenamiento logístico" loading="lazy" decoding="async"><span class="tmd-rental-v2__sector-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 25V8h10v17M14 12h14v13M8 12h2m-2 4h2m-2 4h2m9-4h2m-2 4h2M3 27h26"/></svg></span><h4>Logística</h4><p>Recepción, almacenamiento y despacho para varios clientes.</p></article>'
        . '<article><img src="' . esc_url($asset('sector-retail.webp')) . '" alt="Montacargas moviendo una carga dentro de una bodega" loading="lazy" decoding="async"><span class="tmd-rental-v2__sector-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h22l-2-7H7l-2 7ZM7 12v15h18V12M12 27v-9h8v9M5 15h22"/><path d="M10 8h.01M16 8h.01M22 8h.01"/></svg></span><h4>Alimentos y bebidas</h4><p>Operaciones continuas y entornos exigentes.</p></article>'
        . '<article><img src="' . esc_url($asset('sector-manufacturing.webp')) . '" alt="Montacargas transportando materiales en una planta de manufactura" loading="lazy" decoding="async"><span class="tmd-rental-v2__sector-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 27V15l8 4v-5l8 5v-8l8 4v12H4Z"/><path d="M8 23h3m4 0h3m4 0h3M8 10V5h5v11"/></svg></span><h4>Manufactura</h4><p>Materia prima y producto terminado entre planta y bodega.</p></article>'
        . '<article><img src="' . esc_url($asset('sectors.webp')) . '" alt="Montacargas y estibas en una operación de distribución" loading="lazy" decoding="async"><span class="tmd-rental-v2__sector-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h15v15H4zM19 13h5l4 5v5h-9z"/><circle cx="10" cy="25" r="2"/><circle cx="23" cy="25" r="2"/><path d="M7 12h9m-9 4h9"/></svg></span><h4>Retail</h4><p>Reposición y distribución desde centros propios.</p></article>'
        . '<article><img src="' . esc_url($asset('montacargas-construccion.webp')) . '" alt="Montacargas trasladando materiales en una operación de construcción" loading="lazy" decoding="async"><span class="tmd-rental-v2__sector-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 27V7h24v20M4 14h24M4 21h24M11 7v7m10-7v7m-10 0v7m10-7v7m-10 0v6m10-6v6"/></svg></span><h4>Construcción</h4><p>Movimiento de materiales para obras y proyectos.</p></article>'
        . '</div></div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__uses" aria-labelledby="tmd-rental-v2-uses-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__uses-layout"><div><header class="tmd-rental-v2__section-heading"><h2 id="tmd-rental-v2-uses-heading">Alquiler de montacargas por temporada o por proyecto</h2>'
        . '<h3>Equipos por semanas o meses según dure la operación que los necesita</h3></header><ul>'
        . '<li><span class="tmd-rental-v2__use-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="7" width="22" height="20" rx="2"/><path d="M10 4v6m12-6v6M5 13h22M10 18h3m4 0h4m-11 4h3"/></svg></span><span><strong>Temporada de septiembre a diciembre:</strong> refuerzas la flota para los despachos de fin de año.</span></li>'
        . '<li><span class="tmd-rental-v2__use-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m4 15 12-10 12 10v12H4zM12 27v-9h8v9M4 15h24"/></svg></span><span><strong>Apertura o traslado de bodega:</strong> operas desde el primer día mientras defines tu flota definitiva.</span></li>'
        . '<li><span class="tmd-rental-v2__use-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5a8 8 0 0 0-8 10L5 21a4 4 0 0 0 6 6l6-6a8 8 0 0 0 10-8l-5 5-5-1-1-5 5-5a8 8 0 0 0-2-2Z"/></svg></span><span><strong>Reemplazo durante una reparación:</strong> el equipo alquilado cubre el turno mientras el tuyo está en taller.</span></li>'
        . '<li><span class="tmd-rental-v2__use-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="20" height="23" rx="2"/><path d="M11 11h10m-10 6h10m-10 6h10M8 5V3m16 2V3"/></svg></span><span><strong>Inventarios y proyectos puntuales:</strong> equipos por semanas para conteos, reorganizaciones o contratos temporales.</span></li>'
        . '</ul></div><img src="' . esc_url($asset('use-cases.webp')) . '" alt="Montacargas reach en un pasillo de estanterías" loading="lazy" decoding="async">'
        . '</div></section>');

    $blocks[] = $html_block('<section class="tmd-rental-v2-section tmd-rental-v2__support" aria-labelledby="tmd-rental-v2-support-heading">'
        . '<div class="tmd-rental-v2__container tmd-rental-v2__support-layout"><div class="tmd-rental-v2__support-content"><header><h2 id="tmd-rental-v2-support-heading">Servicio técnico propio <span>para la flota en operación</span></h2>'
        . '<h3>Mantenimiento preventivo y correctivo con repuestos para montacargas de distintas marcas</h3></header>'
        . '<div class="tmd-rental-v2__support-cards"><article><span class="tmd-rental-v2__support-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="7" width="22" height="20" rx="2"/><path d="M10 4v6m12-6v6M5 13h22M10 19h4m4 0h4"/></svg></span><strong>Alquiler desde 2013:</strong><p>la flota de alquiler nació dentro de un taller de servicio técnico.</p></article>'
        . '<article><span class="tmd-rental-v2__support-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 5a8 8 0 0 0-8 10l-6 6a4 4 0 0 0 6 6l6-6a8 8 0 0 0 10-8l-5 5-5-1-1-5 5-5a8 8 0 0 0-2-2Z"/></svg></span><strong>Reacción ante fallas:</strong><p>si un equipo alquilado se detiene, lo atiende nuestro servicio técnico, sin intermediarios.</p></article>'
        . '<article class="tmd-rental-v2__diagnosis"><span class="tmd-rental-v2__support-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="14" cy="14" r="9"/><path d="m21 21 7 7M10 14l3 3 6-7"/></svg></span><span><strong>Diagnóstico antes de intervenir:</strong><p>el alcance de la reparación se explica antes de ejecutarla.</p></span></article>'
        . '</div></div><img src="' . esc_url($asset('technical-support.webp')) . '" alt="Dos técnicos revisan un montacargas en el mástil" loading="lazy" decoding="async">'
        . '</div></div></section>');

    $blocks[] = $html_block('<div class="tmd-rental-v2-section tmd-rental-v2__quote-faq"><div class="tmd-rental-v2__container tmd-rental-v2__quote-faq-layout">'
        . '<section id="tmd-rental-v2-quote" class="tmd-rental-v2__quote" aria-labelledby="tmd-rental-v2-quote-heading"><div class="tmd-rental-v2__quote-copy">'
        . '<h2 id="tmd-rental-v2-quote-heading">Cotiza el alquiler de montacargas en Bogotá</h2>'
        . '<h3>Un asesor comercial responde tu solicitud de lunes a viernes en horario laboral</h3></div>'
        . '<div class="tmd-rental-v2__form">[contact-form-7 id="' . absint($form_id) . '"]</div></section>'
        . '<section class="tmd-rental-v2__faq" aria-labelledby="tmd-rental-v2-faq-heading"><header><h2 id="tmd-rental-v2-faq-heading">Preguntas frecuentes sobre alquiler de montacargas</h2>'
        . '<h3>Condiciones del alquiler mensual de montacargas eléctricos antes de cotizar</h3></header><div class="tmd-rental-v2__faq-list">'
        . '<details><summary>¿Cuál es el tiempo mínimo de alquiler de un montacargas?</summary><p>El alquiler mínimo es de 15 días, porque cada equipo implica un traslado de ida hasta tu sede y otro de regreso al terminar. Desde ese mínimo, el periodo se ajusta por meses a la duración de la operación y queda fijado en la cotización.</p></details>'
        . '<details><summary>¿El montacargas se alquila con operador?</summary><p>No. El equipo se alquila sin operador y lo conduce el personal de tu empresa. Por eso, en la recomendación técnica tenemos en cuenta la configuración que tus operarios ya manejan, contrabalanceado o reach, además del peso de la carga, la altura de las estanterías y el ancho de pasillo.</p></details>'
        . '<details><summary>¿Qué pasa si el montacargas alquilado presenta una falla?</summary><p>Lo atiende nuestro propio servicio técnico, el mismo que hace el mantenimiento preventivo y correctivo de la flota. Reportas la falla a tu asesor comercial, y el técnico diagnostica el equipo y define la reparación para que tu operación vuelva a moverse sin esperar a un tercero.</p></details>'
        . '<details><summary>¿Qué datos necesitan para cotizar el alquiler?</summary><p>El peso de la carga, la altura de las estanterías, el ancho de los pasillos, las horas de uso por día y la ciudad donde trabajará el equipo. Con esos datos evitamos recomendarte un equipo que no quepa en tus pasillos o que no alcance la altura de tu estantería.</p></details>'
        . '<details><summary>¿Cuánto cuesta el alquiler de un montacargas eléctrico?</summary><p>El valor depende del tipo de equipo, su capacidad, la altura de levante y el periodo de alquiler. Por eso no manejamos una tarifa única: con los datos de tu operación te enviamos la cotización del equipo que corresponde, sin sobredimensionarlo para la carga que mueves.</p></details>'
        . '<details><summary>¿Por qué elegir un montacargas eléctrico para trabajar en bodega?</summary><p>Porque no emite gases de combustión, hace menos ruido y maniobra con precisión en pasillos y estanterías, lo que lo hace adecuado para espacios cerrados y turnos junto al personal. Su energía viene de una batería de tracción, que también vendemos y alquilamos, y se recarga en una zona definida de la bodega.</p></details>'
        . '</div></section></div></div>');

    $blocks[] = $shortcode_block('[tmd_commercial_landing_related_section topic="montacargas" variant="rental-v2" fallback="none" eyebrow="Blog" heading="Guías técnicas de mantenimiento y baterías de tracción" subtitle="Vida útil de la batería de tracción y señales de mantenimiento preventivo"]');

    return implode("\n\n", $blocks);
}

function tmd_commercial_landing_rental_v2_form_markup(): string
{
    $privacy_url = esc_url(home_url('/nosotros/legal/politica-de-privacidad/'));
    $honeypot = '<div class="tmd-landing-form__honeypot" aria-hidden="true">'
        . '<label>Dejar este campo vacío [text tmd_website tabindex:-1 autocomplete:off]</label>'
        . '</div>';
    $privacy = '<p class="tmd-landing-form__privacy-copy">Usamos tus datos solo para responder esta solicitud, según nuestra '
        . '<a href="' . $privacy_url . '" target="_blank" rel="noopener">política de privacidad</a>.'
        . '</p>';

    return '<div class="tmd-landing-form">'
        . '<div class="tmd-landing-form__grid">'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Nombre y cargo</span>[text* nombre_cargo autocomplete:name]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Empresa y ciudad</span>[text* empresa_ciudad]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Correo o celular</span>[text* contacto]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Tipo de equipo o necesidad</span>[text* necesidad]</label>'
        . '<label class="tmd-landing-form__field tmd-landing-form__field--wide"><span>Peso de la carga, altura de levante y ancho de pasillo</span>[textarea requerimientos]</label>'
        . '</div>' . $honeypot . $privacy
        . '<p class="tmd-landing-form__submit">[submit "Solicitar cotización"]</p>'
        . '</div>';
}
