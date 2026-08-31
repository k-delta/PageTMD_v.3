<?php
/**
 * Guías editoriales para /equipos/tipos/.
 *
 * El contenido se mantiene en un único mapa para conservar estructura,
 * enlaces y criterios técnicos coherentes entre las catorce páginas.
 */

defined('ABSPATH') || exit;

/*
 * El CPT histórico tmd_equipo registra /equipos/{slug} y captura esta URL
 * antes de la regla jerárquica de páginas. El remapeo exacto conserva
 * intactas las fichas del catálogo y permite servir la portada publicada.
 */
add_filter('request', static function (array $query_vars): array {
    $path = trailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    return $path === '/equipos/tipos/' ? ['page_id' => 349] : $query_vars;
}, 1);

function tmd_equipment_type_guides(): array
{
    return [
        'tipos' => [
            'family' => 'Guía de selección',
            'title' => 'Tipos de equipos para manejo de materiales',
            'summary' => 'Contar con el equipo adecuado es fundamental para mejorar la productividad, reducir los tiempos de operación y aumentar la seguridad.',
            'intro' => [
                'Cada operación tiene necesidades diferentes. El ancho de los pasillos, la altura de almacenamiento, el peso de las cargas, la distancia de desplazamiento y la intensidad de uso determinan qué tipo de equipo resulta más conveniente.',
                'En esta sección encontrarás las principales clases de equipos utilizados para transportar, elevar, organizar y preparar mercancía en bodegas, centros de distribución, plantas industriales y establecimientos comerciales.',
            ],
            'highlights' => ['Mayor productividad', 'Menores tiempos de operación', 'Más seguridad'],
            'considerations' => [
                ['Ancho de pasillos', 'Mide el espacio útil disponible para circular, girar y posicionar la carga.'],
                ['Altura de almacenamiento', 'Define la elevación requerida y la capacidad necesaria en el nivel más alto.'],
                ['Peso de las cargas', 'Valida el peso real, las dimensiones y la distribución de cada carga.'],
                ['Recorrido e intensidad', 'Considera la distancia de desplazamiento, la frecuencia de movimientos y las horas de trabajo.'],
            ],
            'related' => [
                ['/equipos/tipos/estibadores-y-apiladores/', 'Traslado y elevación ligera', 'Estibadores manuales, eléctricos y apiladores.'],
                ['/equipos/tipos/reach-retractiles/', 'Pasillo angosto', 'Retráctiles y mecanismos de pantógrafo.'],
                ['/equipos/tipos/tomapedidos/', 'Preparación de pedidos', 'Equipos para picking y acceso a mercancía.'],
                ['/equipos/tipos/contrabalanceados/', 'Contrabalanceados', 'Montacargas eléctricos de tres y cuatro ruedas.'],
            ],
        ],
        'estibadores-y-apiladores' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Equipos de traslado y elevación ligera',
            'summary' => 'Soluciones para movilizar estibas y mercancías dentro de bodegas, zonas de producción, supermercados, centros logísticos y áreas de carga.',
            'intro' => [
                'Son una alternativa práctica para operaciones que requieren transportar materiales a nivel del suelo o elevarlos a alturas bajas y medianas.',
                'Dentro de esta categoría se encuentran los estibadores manuales, los estibadores eléctricos y los apiladores eléctricos.',
            ],
            'highlights' => ['Traslado de estibas', 'Elevación baja o media', 'Operación en espacios compactos'],
            'related' => [
                ['/equipos/tipos/estibadores-manuales/', 'Estibadores manuales', 'Movimiento a nivel del suelo mediante sistema hidráulico manual.'],
                ['/equipos/tipos/estibadores-electricos/', 'Estibadores eléctricos', 'Tracción eléctrica para recorridos y movimientos frecuentes.'],
                ['/equipos/tipos/apiladores-electricos/', 'Apiladores eléctricos', 'Transporte horizontal y elevación en un equipo compacto.'],
            ],
        ],
        'estibadores-manuales' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Estibadores manuales',
            'summary' => 'Equipos básicos utilizados para levantar ligeramente una estiba y trasladarla a nivel del suelo.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/estibador-manual.webp',
            'intro' => [
                'Funcionan mediante un sistema hidráulico accionado manualmente por el operador. No requieren baterías, combustible ni sistemas eléctricos, lo que simplifica su mantenimiento y permite utilizarlos en diferentes áreas de trabajo.',
                'Son adecuados para recorridos cortos, operaciones de baja o media intensidad y espacios donde el volumen de movimiento de mercancía no justifica el uso de un equipo motorizado.',
            ],
            'highlights' => ['Mantenimiento simple', 'Sin batería ni combustible', 'Uso en diferentes áreas'],
            'applications' => [
                ['Movimiento en bodega', 'Traslado de estibas dentro de una bodega.'],
                ['Despacho', 'Organización de mercancía en zonas de despacho.'],
                ['Carga y descarga', 'Movimiento de productos entre áreas y vehículos.'],
                ['Producción', 'Abastecimiento de líneas de producción.'],
                ['Superficies planas', 'Traslado de productos en recorridos cortos y nivelados.'],
            ],
            'considerations' => [
                ['Esfuerzo del operador', 'Todo el desplazamiento depende del esfuerzo físico del operador.'],
                ['Recorridos y frecuencia', 'No son la mejor alternativa para recorridos largos ni jornadas con movimientos repetitivos.'],
                ['Condiciones del piso', 'No son la opción indicada para pendientes; su mejor entorno son superficies planas.'],
            ],
        ],
        'estibadores-electricos' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Estibadores eléctricos',
            'summary' => 'Cumplen la función de un estibador manual e incorporan tracción eléctrica para facilitar el desplazamiento de la carga.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/portaestiba-electrico.webp',
            'intro' => [
                'Están diseñados para operaciones con recorridos más largos, mayor frecuencia de movimientos o cargas que resultan difíciles de transportar manualmente.',
                'Dependiendo de su configuración, el operador puede caminar junto al equipo, utilizar una plataforma abatible o conducir desde una posición integrada.',
            ],
            'highlights' => ['Menor esfuerzo físico', 'Ritmo de trabajo constante', 'Distintas posiciones de operación'],
            'applications' => [
                ['Centros de distribución', 'Movimiento continuo entre recibo, almacenamiento y despacho.'],
                ['Muelles de carga', 'Transferencia frecuente de estibas en zonas de carga.'],
                ['Plantas industriales', 'Transporte interno de materiales y producto terminado.'],
                ['Grandes superficies', 'Movimiento de mercancía en áreas comerciales amplias.'],
                ['Preparación de pedidos', 'Transporte interno y abastecimiento de zonas de picking.'],
            ],
            'considerations' => [
                ['Distancia', 'Evalúa la distancia total de desplazamiento de cada ciclo.'],
                ['Carga', 'Verifica el peso real que debe transportar el equipo.'],
                ['Jornada', 'Considera las horas de trabajo y la frecuencia de movimientos.'],
                ['Batería', 'Selecciona la tecnología y autonomía adecuadas para la operación.'],
            ],
        ],
        'apiladores-electricos' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Apiladores eléctricos',
            'summary' => 'Combinan el transporte horizontal de mercancía con la capacidad de elevar estibas.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/apiladores-electricos.webp',
            'intro' => [
                'Se utilizan para organizar productos en estanterías, alimentar líneas de producción, apilar mercancía y realizar almacenamiento a alturas bajas o medianas.',
                'Por su diseño compacto, pueden trabajar en espacios donde un montacargas convencional tendría dificultades para maniobrar. Son una solución intermedia entre un estibador eléctrico y un montacargas.',
            ],
            'highlights' => ['Transporte y elevación', 'Diseño compacto', 'Alturas bajas y medianas'],
            'applications' => [
                ['Apilamiento', 'Apilamiento y organización de mercancía.'],
                ['Estanterías', 'Almacenamiento de productos en niveles bajos o medianos.'],
                ['Carga y descarga', 'Manipulación de estibas en zonas de recibo y despacho.'],
                ['Producción', 'Abastecimiento de áreas y líneas de producción.'],
                ['Bodegas pequeñas', 'Organización de productos en espacios compactos.'],
                ['Pasillos reducidos', 'Manipulación de cargas donde la maniobra es limitada.'],
            ],
            'considerations' => [
                ['Altura requerida', 'Verifica la altura máxima a la que debe ubicarse la carga.'],
                ['Capacidad residual', 'Confirma la capacidad disponible del equipo a la altura de trabajo.'],
                ['Dimensiones del pasillo', 'Mide el espacio real de circulación y maniobra con la estiba.'],
            ],
        ],
        'reach-retractiles' => [
            'family' => 'Almacenamiento de alta densidad',
            'title' => 'Montacargas eléctricos de pasillo angosto',
            'summary' => 'Equipos diseñados para trabajar en bodegas con estanterías altas y espacios reducidos.',
            'intro' => [
                'Su configuración permite disminuir el ancho necesario para maniobrar, aprovechar mejor el área disponible y aumentar la capacidad de almacenamiento de una instalación.',
                'Se utilizan principalmente en centros de distribución y operaciones logísticas donde es necesario mover mercancía a diferentes niveles de una estantería.',
            ],
            'highlights' => ['Menor ancho de maniobra', 'Aprovechamiento del espacio', 'Trabajo en estanterías altas'],
            'related' => [
                ['/equipos/tipos/retractiles-de-mastil-movil/', 'Retráctiles de mástil móvil', 'El mástil o las horquillas avanzan y se retraen.'],
                ['/equipos/tipos/pantografo-sencillo/', 'Pantógrafo sencillo', 'Acceso a posiciones de profundidad simple.'],
                ['/equipos/tipos/pantografo-doble-profundidad/', 'Pantógrafo de doble profundidad', 'Acceso a una segunda posición detrás de la primera estiba.'],
            ],
        ],
        'retractiles-de-mastil-movil' => [
            'family' => 'Pasillo angosto',
            'title' => 'Montacargas retráctiles de mástil móvil',
            'summary' => 'Equipos reach cuyo mecanismo desplaza el mástil o las horquillas hacia adelante para tomar la carga y luego retraerla.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/Montacargas-retráctiles-de-mástil-móvil.webp',
            'intro' => [
                'El movimiento retráctil ayuda a mantener la carga dentro de una posición más estable durante el desplazamiento.',
                'Los modelos ETV, ESC y ETVC corresponden a diferentes configuraciones de este tipo de montacargas. La selección no debe realizarse únicamente por el modelo.',
            ],
            'highlights' => ['Mecanismo retráctil', 'Estabilidad de la carga', 'Operación en altura'],
            'applications' => [
                ['Pasillos angostos', 'Maniobra en espacios reducidos entre estanterías.'],
                ['Gran altura', 'Ubicación y retiro de cargas en niveles elevados.'],
                ['Almacenamiento intensivo', 'Operaciones con alta frecuencia de movimientos.'],
                ['Superficies niveladas', 'Trabajo interior sobre pisos adecuados para precisión en altura.'],
                ['Alta rotación', 'Centros de distribución con entradas y salidas frecuentes.'],
            ],
            'considerations' => [
                ['Altura de elevación', 'Define el nivel máximo de almacenamiento requerido.'],
                ['Capacidad', 'Valida la capacidad necesaria a la altura real de trabajo.'],
                ['Ancho de pasillo', 'Mide el espacio útil con la carga y las holguras de seguridad.'],
                ['Tipo de estantería', 'Comprueba compatibilidad entre equipo, estiba y rack.'],
            ],
        ],
        'pantografo-sencillo' => [
            'family' => 'Pasillo angosto',
            'title' => 'Montacargas con pantógrafo sencillo',
            'summary' => 'Incorporan un mecanismo extensible similar a una tijera para introducir o retirar las horquillas dentro de la estantería.',
            'intro' => [
                'El mecanismo permite acceder a la carga sin desplazar completamente el equipo y resulta útil cuando se busca aprovechar la profundidad de almacenamiento.',
                'El pantógrafo sencillo se utiliza en estanterías de profundidad simple. Permite tomar o depositar una estiba ubicada directamente frente al pasillo.',
            ],
            'highlights' => ['Profundidad simple', 'Acceso directo', 'Mecanismo extensible'],
            'considerations' => [
                ['Acceso a la carga', 'La configuración facilita el acceso individual a cada posición de almacenamiento.'],
                ['Profundidad del rack', 'Debe corresponder a una posición ubicada directamente frente al pasillo.'],
                ['Operación', 'La extensión de horquillas reduce la necesidad de desplazar completamente el equipo.'],
            ],
        ],
        'pantografo-doble-profundidad' => [
            'family' => 'Pasillo angosto',
            'title' => 'Montacargas con pantógrafo de doble profundidad',
            'summary' => 'Su mecanismo extensible permite alcanzar una segunda posición de almacenamiento ubicada detrás de la primera estiba.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/pantografo-doble-reach.webp',
            'intro' => [
                'Esta configuración permite introducir o retirar las horquillas dentro de la estantería sin desplazar completamente el equipo.',
                'Su principal ventaja es el aumento de la densidad de almacenamiento.',
            ],
            'highlights' => ['Doble profundidad', 'Mayor densidad', 'Alcance extendido'],
            'considerations' => [
                ['Acceso a posiciones', 'La primera estiba puede limitar el acceso inmediato a la posición posterior.'],
                ['Organización del inventario', 'Requiere definir correctamente dónde se almacena cada referencia.'],
                ['Rotación de productos', 'La planeación debe considerar qué mercancía necesita acceso más frecuente.'],
            ],
        ],
        'tomapedidos' => [
            'family' => 'Picking y distribución',
            'title' => 'Equipos de preparación de pedidos',
            'summary' => 'También conocidos como tomapedidos o recogepedidos, facilitan la recolección individual de productos dentro de una bodega.',
            'intro' => [
                'A diferencia de un montacargas convencional, estos equipos permiten que el operador se eleve junto con la plataforma de trabajo para acceder directamente a la mercancía almacenada.',
            ],
            'highlights' => ['Recolección individual', 'Acceso directo', 'Operador y plataforma en altura'],
            'related' => [
                ['/equipos/tipos/tomapedidos-de-alto-nivel/', 'Tomapedidos de alto nivel', 'Picking manual desde distintos niveles de estantería.'],
            ],
        ],
        'tomapedidos-de-alto-nivel' => [
            'family' => 'Preparación de pedidos',
            'title' => 'Tomapedidos de alto nivel',
            'summary' => 'Se utilizan en operaciones de picking donde los productos deben recogerse manualmente desde diferentes niveles de la estantería.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/toma-pedidos.webp',
            'intro' => [
                'El operador se eleva con la plataforma para acceder directamente a unidades o cajas almacenadas en niveles elevados.',
            ],
            'highlights' => ['Picking en altura', 'Acceso por unidad o caja', 'Preparación directa'],
            'applications' => [
                ['Centros de distribución', 'Preparación de pedidos en operaciones logísticas.'],
                ['Bodegas de repuestos', 'Acceso a gran variedad de referencias.'],
                ['Comercio electrónico', 'Recolección de productos para pedidos individuales.'],
                ['Alta variedad', 'Almacenes con numerosas referencias y ubicaciones.'],
                ['Unidades o cajas', 'Preparación sin movilizar necesariamente una estiba completa.'],
                ['Niveles elevados', 'Acceso a inventario almacenado en altura.'],
            ],
            'considerations' => [
                ['Seguridad', 'Requieren sistemas de seguridad adecuados para el trabajo en altura.'],
                ['Capacitación', 'El operador debe estar formado para utilizar el equipo correctamente.'],
                ['Pisos y pasillos', 'Las condiciones de circulación deben ser apropiadas para una operación estable.'],
            ],
        ],
        'contrabalanceados' => [
            'family' => 'Montacargas elevadores',
            'title' => 'Montacargas elevadores contrabalanceados',
            'summary' => 'Equipos versátiles utilizados para levantar, transportar y posicionar cargas.',
            'intro' => [
                'Reciben su nombre porque incorporan un contrapeso en la parte posterior, encargado de equilibrar el peso que se encuentra sobre las horquillas.',
                'A diferencia de los equipos retráctiles, pueden aproximarse directamente a la carga y trabajar en una mayor variedad de aplicaciones. Son comunes en patios, bodegas, plantas industriales y zonas de carga y descarga.',
            ],
            'highlights' => ['Acceso directo a la carga', 'Amplia versatilidad', 'Equilibrio mediante contrapeso'],
            'related' => [
                ['/equipos/tipos/electricos-de-3-ruedas/', 'Eléctricos de tres ruedas', 'Radio de giro reducido y alta maniobrabilidad.'],
                ['/equipos/tipos/electricos-de-4-ruedas/', 'Eléctricos de cuatro ruedas', 'Mayor estabilidad para cargas y desplazamientos exigentes.'],
            ],
        ],
        'electricos-de-3-ruedas' => [
            'family' => 'Contrabalanceados',
            'title' => 'Contrabalanceados eléctricos de tres ruedas',
            'summary' => 'Ofrecen un radio de giro reducido y una alta capacidad de maniobra.',
            'hero_image' => 'assets/img/mega-menu/contrabalanceado-3-llantas.webp',
            'intro' => [
                'Son apropiados para instalaciones con espacios limitados, pasillos estrechos y operaciones principalmente interiores.',
            ],
            'highlights' => ['Radio de giro reducido', 'Alta maniobrabilidad', 'Operación interior'],
            'applications' => [
                ['Bodegas', 'Movimiento de mercancía en espacios interiores.'],
                ['Carga y descarga', 'Manipulación de mercancía entre áreas y vehículos.'],
                ['Producción', 'Abastecimiento de materiales a zonas de trabajo.'],
                ['Áreas comerciales', 'Movimiento de cargas en establecimientos y grandes superficies.'],
                ['Giros frecuentes', 'Operaciones donde la maniobrabilidad es prioritaria.'],
            ],
            'considerations' => [
                ['Superficie', 'Deben utilizarse en superficies estables.'],
                ['Condiciones del modelo', 'Capacidad, altura y entorno deben mantenerse dentro de lo definido para cada equipo.'],
            ],
        ],
        'electricos-de-4-ruedas' => [
            'family' => 'Contrabalanceados',
            'title' => 'Contrabalanceados eléctricos de cuatro ruedas',
            'summary' => 'Ofrecen mayor estabilidad para transportar cargas pesadas y recorrer superficies con pequeñas irregularidades.',
            'hero_image' => 'assets/img/mega-menu/mega-menu-out/contrabalanceado-4-llantas.webp',
            'intro' => [
                'Se utilizan en operaciones que requieren desplazamientos frecuentes, trabajos de carga y descarga o movimiento de mercancía entre diferentes áreas.',
                'En comparación con los modelos de tres ruedas, suelen necesitar un poco más de espacio para girar.',
            ],
            'highlights' => ['Mayor estabilidad', 'Cargas pesadas', 'Desplazamientos frecuentes'],
            'applications' => [
                ['Carga y descarga', 'Trabajo frecuente en zonas de recibo y despacho.'],
                ['Transporte interno', 'Movimiento de mercancía entre diferentes áreas.'],
                ['Superficies controladas', 'Recorridos sobre pisos con pequeñas irregularidades.'],
            ],
            'considerations' => [
                ['Ancho de pasillos', 'Mide el espacio disponible con la carga real.'],
                ['Zona de maniobra', 'Verifica el radio necesario para girar y posicionar el equipo.'],
                ['Comparación con tres ruedas', 'Elige cuatro ruedas cuando la estabilidad sea prioritaria y exista espacio suficiente.'],
            ],
        ],
    ];
}

function tmd_equipment_type_guide_current(): ?array
{
    if (!is_page()) {
        return null;
    }

    $post = get_queried_object();
    if (!$post instanceof WP_Post) {
        return null;
    }

    $guides = tmd_equipment_type_guides();
    return isset($guides[$post->post_name])
        ? ['slug' => $post->post_name] + $guides[$post->post_name]
        : null;
}

add_filter('body_class', static function (array $classes): array {
    if (tmd_equipment_type_guide_current()) {
        $classes[] = 'tmd-has-equipment-type-guide';
    }
    return $classes;
});

add_action('wp_enqueue_scripts', static function (): void {
    if (!tmd_equipment_type_guide_current()) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/css/tmd-equipment-type-guides.css';
    wp_enqueue_style(
        'tmd-equipment-type-guides',
        get_stylesheet_directory_uri() . '/assets/css/tmd-equipment-type-guides.css',
        [],
        file_exists($path) ? (string) filemtime($path) : '1.0.0'
    );
}, 65);

function tmd_equipment_type_guide_content(string $content): string
{
    if (!in_the_loop() || !is_main_query()) {
        return $content;
    }

    $guide = tmd_equipment_type_guide_current();
    if (!$guide) {
        return $content;
    }

    $whatsapp = 'https://wa.me/573015556180?text='
        . rawurlencode('Hola, quiero asesoría sobre ' . $guide['title'] . '.');
    $section_number = 1;

    ob_start();
    ?>
    <main class="tmd-type-guide alignfull">
        <section class="tmd-type-guide__hero">
            <div class="tmd-type-guide__wrap tmd-type-guide__hero-grid">
                <div>
                    <a class="tmd-type-guide__back" href="<?php echo esc_url(home_url('/equipos/')); ?>">← Explorar todos los equipos</a>
                    <span class="tmd-type-guide__kicker"><?php echo esc_html($guide['family']); ?></span>
                    <h1><?php echo esc_html($guide['title']); ?></h1>
                    <p><?php echo esc_html($guide['summary']); ?></p>
                    <div class="tmd-type-guide__actions">
                        <a class="tmd-type-guide__button tmd-type-guide__button--primary" href="<?php echo esc_url(home_url('/equipos/')); ?>">Ver equipos disponibles <span>→</span></a>
                        <a class="tmd-type-guide__button tmd-type-guide__button--ghost" href="<?php echo esc_url($whatsapp); ?>">Hablar con un asesor</a>
                    </div>
                </div>
                <div class="tmd-type-guide__visual" aria-hidden="true">
                    <span>Manejo de materiales</span>
                    <?php if (!empty($guide['hero_image'])) : ?>
                        <img class="tmd-type-guide__image" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/' . $guide['hero_image']); ?>" alt="" loading="eager" decoding="async">
                    <?php else : ?>
                        <div class="tmd-type-guide__machine">
                            <i class="mast"></i><i class="cabin"></i><i class="body"></i><i class="forks"></i><i class="wheel wheel--one"></i><i class="wheel wheel--two"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="tmd-type-guide__section">
            <div class="tmd-type-guide__wrap tmd-type-guide__intro">
                <div>
                    <span class="tmd-type-guide__number"><?php echo esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)); ?></span>
                    <p class="tmd-type-guide__overline">Guía práctica</p>
                    <h2>¿Qué es y para qué sirve?</h2>
                </div>
                <div class="tmd-type-guide__copy">
                    <?php foreach ($guide['intro'] as $paragraph) : ?>
                        <p><?php echo esc_html($paragraph); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($guide['highlights'])) : ?>
                <div class="tmd-type-guide__wrap tmd-type-guide__highlights">
                    <?php foreach ($guide['highlights'] as $index => $highlight) : ?>
                        <div><span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span><strong><?php echo esc_html($highlight); ?></strong></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($guide['applications'])) : ?>
            <section class="tmd-type-guide__section tmd-type-guide__section--soft">
                <div class="tmd-type-guide__wrap">
                    <div class="tmd-type-guide__heading">
                        <div><span class="tmd-type-guide__number"><?php echo esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)); ?></span><p class="tmd-type-guide__overline">Dónde aporta valor</p></div>
                        <h2>Aplicaciones frecuentes</h2>
                        <p>Usos comunes de este equipo dentro de operaciones logísticas, industriales y comerciales.</p>
                    </div>
                    <div class="tmd-type-guide__cards">
                        <?php foreach ($guide['applications'] as $index => $application) : ?>
                            <article>
                                <span class="tmd-type-guide__card-number"><?php echo esc_html((string) ($index + 1)); ?></span>
                                <h3><?php echo esc_html($application[0]); ?></h3>
                                <p><?php echo esc_html($application[1]); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($guide['considerations'])) : ?>
            <section class="tmd-type-guide__section">
                <div class="tmd-type-guide__wrap tmd-type-guide__selection">
                    <div class="tmd-type-guide__selection-copy">
                        <span class="tmd-type-guide__number"><?php echo esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)); ?></span>
                        <p class="tmd-type-guide__overline">Selección técnica</p>
                        <h2>Qué debes validar antes de elegir</h2>
                        <p>La selección debe partir de las condiciones reales de carga, espacio y operación.</p>
                        <a href="<?php echo esc_url(home_url('/encuentra-tu-equipo/')); ?>">Usar el recomendador de equipos →</a>
                    </div>
                    <div class="tmd-type-guide__factors">
                        <?php foreach ($guide['considerations'] as $index => $factor) : ?>
                            <article>
                                <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                                <div><h3><?php echo esc_html($factor[0]); ?></h3><p><?php echo esc_html($factor[1]); ?></p></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($guide['related'])) : ?>
            <section class="tmd-type-guide__section tmd-type-guide__section--dark">
                <div class="tmd-type-guide__wrap">
                    <div class="tmd-type-guide__heading tmd-type-guide__heading--light">
                        <div><span class="tmd-type-guide__number"><?php echo esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)); ?></span><p class="tmd-type-guide__overline">Explora la categoría</p></div>
                        <h2>Tipos de equipos</h2>
                        <p>Conoce las alternativas y entra a la guía específica de cada configuración.</p>
                    </div>
                    <div class="tmd-type-guide__related">
                        <?php foreach ($guide['related'] as $item) : ?>
                            <a href="<?php echo esc_url(home_url($item[0])); ?>">
                                <span>Ver guía →</span>
                                <h3><?php echo esc_html($item[1]); ?></h3>
                                <p><?php echo esc_html($item[2]); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="tmd-type-guide__final">
            <div class="tmd-type-guide__wrap tmd-type-guide__final-grid">
                <div><p class="tmd-type-guide__overline">Siguiente paso</p><h2>Selecciona el equipo adecuado para tu operación.</h2></div>
                <div>
                    <p>Cuéntanos el peso de la carga, la altura, el ancho del pasillo, el recorrido y las horas de uso. Un asesor te ayudará a comparar las alternativas disponibles.</p>
                    <div class="tmd-type-guide__actions">
                        <a class="tmd-type-guide__button tmd-type-guide__button--primary" href="<?php echo esc_url($whatsapp); ?>">Solicitar asesoría <span>→</span></a>
                        <a class="tmd-type-guide__text-link" href="<?php echo esc_url(home_url('/nosotros/contacto/')); ?>">Ir a contacto</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php

    return (string) ob_get_clean();
}
add_filter('the_content', 'tmd_equipment_type_guide_content', 99);
