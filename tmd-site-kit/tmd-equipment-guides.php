<?php
/**
 * Paginas editoriales de familias de equipos.
 *
 * El contenido vive en un unico mapa para mantener las trece entradas
 * consistentes sin depender del editor visual de cada pagina.
 */

if (!defined('ABSPATH')) {
    exit;
}

function tmd_equipment_guides(): array
{
    return [
        'traslado-y-elevacion-ligera' => [
            'family' => 'Movimiento interno',
            'title' => 'Traslado y elevación ligera',
            'lead' => 'Soluciones compactas para mover, elevar y posicionar estibas en recorridos cortos, zonas de despacho y bodegas con operación moderada.',
            'definition' => 'Esta familia reúne equipos de conducción manual o eléctrica diseñados para simplificar el movimiento cotidiano de mercancía. Su tamaño compacto facilita la maniobra donde un montacargas contrabalanceado resulta innecesario o difícil de operar.',
            'applications' => [
                ['Despacho y recibo', 'Movimiento ágil entre muelles, camiones y zonas de clasificación.'],
                ['Bodega compacta', 'Trabajo controlado en pasillos y áreas con radios de giro reducidos.'],
                ['Abastecimiento', 'Reposición de líneas, estanterías bajas y puntos de preparación.'],
            ],
            'advantages' => ['Operación sencilla', 'Bajo costo por movimiento', 'Excelente maniobrabilidad'],
            'factors' => [
                ['Carga real', 'Peso máximo de la estiba y distribución de la mercancía.'],
                ['Recorrido', 'Distancia, frecuencia y pendientes presentes en la ruta.'],
                ['Elevación', 'Altura necesaria para transportar o posicionar la carga.'],
                ['Piso', 'Estado, juntas y resistencia de la superficie de trabajo.'],
            ],
            'comparisons' => [
                ['Manual vs. eléctrico', 'La opción manual funciona bien con baja frecuencia; la eléctrica reduce esfuerzo y sostiene ritmos más altos.'],
                ['Traslado vs. apilado', 'Para mover a nivel de piso basta un estibador; para ubicar en altura se requiere un apilador.'],
            ],
        ],
        'estibadores-manuales' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Estibadores manuales',
            'lead' => 'La alternativa práctica para desplazar estibas a nivel de piso con recorridos cortos y una inversión inicial controlada.',
            'definition' => 'El estibador manual, también conocido como transpaleta hidráulica, levanta ligeramente la estiba mediante una bomba accionada por el operario. Está pensado para transporte horizontal, no para almacenar mercancía en niveles elevados.',
            'applications' => [
                ['Muelles', 'Carga, descarga y organización temporal de estibas.'],
                ['Comercio', 'Reposición y movimiento interno en espacios reducidos.'],
                ['Producción', 'Abastecimiento puntual de materias primas y producto terminado.'],
            ],
            'advantages' => ['Mantenimiento simple', 'No requiere batería', 'Uso intuitivo'],
            'factors' => [
                ['Capacidad', 'Peso de operación con margen de seguridad suficiente.'],
                ['Horquillas', 'Longitud y ancho compatibles con la estiba utilizada.'],
                ['Ruedas', 'Material adecuado para ruido, piso y resistencia al desgaste.'],
                ['Frecuencia', 'Para uso intensivo conviene evaluar una opción eléctrica.'],
            ],
            'comparisons' => [
                ['Frente a un estibador eléctrico', 'Es más económico y simple, pero exige mayor esfuerzo durante recorridos largos.'],
                ['Frente a un apilador', 'Transporta a nivel bajo; el apilador además posiciona la carga en estantería.'],
            ],
        ],
        'estibadores-electricos' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Estibadores eléctricos',
            'lead' => 'Movimiento horizontal rápido y ergonómico para operaciones repetitivas, recorridos medios y alto flujo de estibas.',
            'definition' => 'Un estibador eléctrico incorpora tracción y elevación asistidas por batería. Disminuye el esfuerzo físico, estabiliza el ritmo de trabajo y puede configurarse con operador acompañante o plataforma, según la distancia recorrida.',
            'applications' => [
                ['Centros de distribución', 'Transferencias frecuentes entre recibo, almacenamiento y despacho.'],
                ['Picking', 'Abastecimiento de zonas de preparación con ciclos repetitivos.'],
                ['Industria', 'Movimiento continuo de producto en proceso o terminado.'],
            ],
            'advantages' => ['Mayor productividad', 'Menor esfuerzo físico', 'Control preciso'],
            'factors' => [
                ['Autonomía', 'Horas de trabajo, turnos y oportunidad real de recarga.'],
                ['Operario', 'Acompañante, plataforma abatible o conductor sentado.'],
                ['Recorrido', 'Distancia total, rampas y cruces de operación.'],
                ['Batería', 'Tecnología, cargador y estrategia de mantenimiento.'],
            ],
            'comparisons' => [
                ['Frente al manual', 'Requiere mayor inversión, pero mejora seguridad y rendimiento cuando suben los ciclos.'],
                ['Plataforma vs. acompañante', 'La plataforma favorece recorridos medios; el acompañante prioriza control en espacios compactos.'],
            ],
        ],
        'apiladores-electricos' => [
            'family' => 'Traslado y elevación ligera',
            'title' => 'Apiladores eléctricos',
            'lead' => 'Elevación compacta para organizar estibas en racks bajos y medios sin ocupar el espacio de un montacargas convencional.',
            'definition' => 'El apilador eléctrico combina tracción compacta con un mástil de elevación. Es una solución eficiente para almacenar, retirar y transportar cargas paletizadas cuando el ancho de pasillo y el volumen no justifican un equipo de mayor tamaño.',
            'applications' => [
                ['Racks bajos y medios', 'Ubicación y retiro de estibas con maniobra controlada.'],
                ['Producción', 'Alimentación de estaciones y elevación a mesas o niveles de proceso.'],
                ['Bodegas urbanas', 'Operación silenciosa y sin emisiones directas en interiores.'],
            ],
            'advantages' => ['Equipo compacto', 'Elevación precisa', 'Operación interior'],
            'factors' => [
                ['Altura de levante', 'Altura del último nivel más margen operativo.'],
                ['Capacidad residual', 'Carga permitida a la altura máxima requerida.'],
                ['Tipo de estiba', 'Entrada inferior y compatibilidad con brazos de apoyo.'],
                ['Mástil colapsado', 'Paso seguro bajo puertas, vigas y entrepisos.'],
            ],
            'comparisons' => [
                ['Frente al contrabalanceado', 'Necesita menos espacio, aunque su aplicación y capacidad suelen ser más específicas.'],
                ['Frente al retráctil', 'Es ideal en alturas moderadas; el retráctil domina en mayor altura y densidad.'],
            ],
        ],
        'pasillo-angosto' => [
            'family' => 'Almacenamiento de alta densidad',
            'title' => 'Equipos para pasillo angosto',
            'lead' => 'Tecnología especializada para aprovechar mejor cada metro cuadrado y operar con precisión entre racks.',
            'definition' => 'Los equipos de pasillo angosto reducen el espacio necesario para maniobrar mediante chasis compactos, sistemas retráctiles o mecanismos guiados. La selección correcta depende tanto del equipo como del diseño completo de la bodega.',
            'applications' => [
                ['Alta densidad', 'Más posiciones de almacenamiento dentro de la misma superficie.'],
                ['Racks selectivos', 'Acceso directo a referencias con operación frecuente.'],
                ['Cámaras y bodegas', 'Maniobra eléctrica controlada en espacios interiores.'],
            ],
            'advantages' => ['Mayor uso del espacio', 'Precisión en altura', 'Operación eléctrica'],
            'factors' => [
                ['Pasillo útil', 'Medición entre cargas, no únicamente entre estructuras.'],
                ['Altura', 'Nivel superior y capacidad residual requerida.'],
                ['Piso', 'Planitud, nivelación y resistencia según la altura de trabajo.'],
                ['Rack', 'Dimensiones de carga, vigas y tolerancias de seguridad.'],
            ],
            'comparisons' => [
                ['Retráctil vs. contrabalanceado', 'El retráctil trabaja en menos ancho y mayor densidad; el contrabalanceado ofrece más versatilidad general.'],
                ['Sencillo vs. doble profundidad', 'El doble reach aumenta densidad, pero exige planeación de inventario y un accesorio específico.'],
            ],
        ],
        'retractiles-de-mastil-movil' => [
            'family' => 'Pasillo angosto',
            'title' => 'Retráctiles de mástil móvil',
            'lead' => 'Reach trucks diseñados para elevar en racks altos y circular con agilidad en pasillos estrechos.',
            'definition' => 'El equipo retráctil acerca el mástil o las horquillas al chasis durante el desplazamiento. Esto reduce el radio de giro y mejora la estabilidad para almacenar en altura dentro de bodegas con pisos nivelados.',
            'applications' => [
                ['Racks altos', 'Ubicación precisa de estibas en múltiples niveles.'],
                ['Centros de distribución', 'Alta rotación y aprovechamiento intensivo del espacio.'],
                ['Operación interior', 'Trabajo eléctrico, silencioso y sin emisiones directas.'],
            ],
            'advantages' => ['Gran altura de trabajo', 'Radio de giro reducido', 'Visibilidad operativa'],
            'factors' => [
                ['Capacidad residual', 'Validar la carga real en el nivel más alto.'],
                ['Altura colapsada', 'Compatibilidad con accesos, puertas y rociadores.'],
                ['Pasillo', 'Ancho requerido con la estiba real y holguras seguras.'],
                ['Piso', 'La planitud influye en estabilidad, confort y precisión.'],
            ],
            'comparisons' => [
                ['Frente al contrabalanceado', 'Gana densidad y altura en interior; pierde versatilidad en patios y superficies irregulares.'],
                ['Frente al apilador', 'Ofrece más rendimiento y altura para ciclos exigentes, con una inversión superior.'],
            ],
        ],
        'pantografo-sencillo' => [
            'family' => 'Pasillo angosto',
            'title' => 'Pantógrafo sencillo',
            'lead' => 'Alcance retráctil para manipular una posición de profundidad con control y visibilidad en racks selectivos.',
            'definition' => 'El mecanismo de pantógrafo extiende las horquillas hasta la estiba y las retrae hacia el equipo durante el desplazamiento. La configuración sencilla atiende una fila de pallets y favorece el acceso directo a cada referencia.',
            'applications' => [
                ['Rack selectivo', 'Acceso independiente a todas las posiciones almacenadas.'],
                ['Alta rotación', 'Entradas y salidas frecuentes con lectura clara de ubicaciones.'],
                ['Inventario diverso', 'Operación con múltiples referencias y prioridades.'],
            ],
            'advantages' => ['Acceso directo', 'Control de inventario', 'Buena productividad'],
            'factors' => [
                ['Profundidad', 'Distancia real entre frente del rack y centro de carga.'],
                ['Carga', 'Peso, altura y estabilidad de la unidad manipulada.'],
                ['Visibilidad', 'Cámara, indicador o ayudas según altura y operación.'],
                ['Desplazador', 'Evaluar desplazamiento lateral para posicionamiento fino.'],
            ],
            'comparisons' => [
                ['Frente al doble profundidad', 'Ofrece menor densidad, pero acceso inmediato a cada estiba y operación más simple.'],
                ['Frente al mástil móvil', 'Ambos atienden pasillo angosto; la geometría y el rack definen cuál encaja mejor.'],
            ],
        ],
        'pantografo-doble-profundidad' => [
            'family' => 'Pasillo angosto',
            'title' => 'Pantógrafo doble profundidad',
            'lead' => 'Mayor densidad de almacenamiento mediante alcance extendido a una segunda posición de estiba.',
            'definition' => 'Su pantógrafo telescópico permite alcanzar pallets ubicados detrás de la primera fila. Es una configuración enfocada en densidad que debe coordinarse con el rack, la secuencia del inventario y una operación cuidadosamente planificada.',
            'applications' => [
                ['Inventario homogéneo', 'Dos posiciones por calle para referencias con varias estibas.'],
                ['Reserva', 'Mayor densidad para producto de rotación predecible.'],
                ['Expansión interna', 'Más ubicaciones sin ampliar inmediatamente la superficie.'],
            ],
            'advantages' => ['Alta densidad', 'Mejor uso del volumen', 'Alcance especializado'],
            'factors' => [
                ['Gestión de inventario', 'La segunda estiba queda condicionada por la posición frontal.'],
                ['Capacidad residual', 'El centro de carga extendido reduce la capacidad disponible.'],
                ['Rack', 'Estructura y guías compatibles con doble profundidad.'],
                ['Ayudas visuales', 'Cámara y sensores facilitan el posicionamiento lejano.'],
            ],
            'comparisons' => [
                ['Frente al sencillo', 'Aumenta posiciones, pero reduce selectividad y exige mayor disciplina de inventario.'],
                ['Frente a drive-in', 'Mantiene acceso por calles y flexibilidad superior, aunque con distinta densidad total.'],
            ],
        ],
        'preparacion-de-pedidos' => [
            'family' => 'Picking y distribución',
            'title' => 'Preparación de pedidos',
            'lead' => 'Equipos que acercan al operario y la mercancía para completar pedidos con menos desplazamientos y mayor precisión.',
            'definition' => 'La preparación de pedidos agrupa soluciones de picking a nivel bajo, medio y alto. El equipo adecuado se define por la altura de toma, el perfil del pedido, el recorrido y la manera en que se consolidan las unidades.',
            'applications' => [
                ['E-commerce', 'Pedidos de varias referencias con ventanas de despacho cortas.'],
                ['Retail', 'Preparación por unidad, caja o empaque para tiendas.'],
                ['Repuestos', 'Acceso ordenado a inventarios extensos y referencias pequeñas.'],
            ],
            'advantages' => ['Menos recorridos', 'Mayor precisión', 'Ergonomía de picking'],
            'factors' => [
                ['Altura de toma', 'Nivel real donde el operario recoge la referencia.'],
                ['Perfil del pedido', 'Unidades, cajas, estibas y cantidad de líneas por orden.'],
                ['Recorrido', 'Distancia acumulada y congestión entre zonas.'],
                ['Integración', 'Compatibilidad con radiofrecuencia, escáner y flujo WMS.'],
            ],
            'comparisons' => [
                ['Bajo vs. alto nivel', 'El bajo nivel prioriza velocidad horizontal; el alto nivel permite recoger directamente en racks elevados.'],
                ['Picking vs. almacenamiento', 'Un order picker acerca al operario; un reach truck mueve la estiba completa.'],
            ],
        ],
        'tomapedidos-de-alto-nivel' => [
            'family' => 'Preparación de pedidos',
            'title' => 'Tomapedidos de alto nivel',
            'lead' => 'Acceso seguro del operario a referencias ubicadas en niveles elevados para picking directo desde el rack.',
            'definition' => 'El tomapedidos de alto nivel eleva la cabina junto con el operario y, según configuración, con las horquillas. Está diseñado para preparar pedidos por unidad o caja sin bajar primero la estiba completa.',
            'applications' => [
                ['Repuestos y componentes', 'Selección de referencias pequeñas en numerosos niveles.'],
                ['Distribución', 'Consolidación de pedidos mixtos directamente desde el rack.'],
                ['E-commerce', 'Picking de alta variedad con trazabilidad por ubicación.'],
            ],
            'advantages' => ['Acceso directo en altura', 'Picking preciso', 'Menos doble manipulación'],
            'factors' => [
                ['Altura de plataforma', 'Nivel donde el operario debe alcanzar la referencia.'],
                ['Seguridad', 'Arnés, puertas, interbloqueos y protocolo de rescate.'],
                ['Guiado', 'Riel, hilo o navegación libre según ancho de pasillo.'],
                ['Unidad de carga', 'Dimensiones del pallet o plataforma que recibe el pedido.'],
            ],
            'comparisons' => [
                ['Frente al reach truck', 'Eleva al operario para tomar unidades; el reach manipula pallets completos desde el suelo.'],
                ['Alto vs. bajo nivel', 'El alto nivel amplía cobertura vertical; el bajo nivel favorece ciclos horizontales rápidos.'],
            ],
        ],
        'contrabalanceados' => [
            'family' => 'Montacargas convencionales',
            'title' => 'Montacargas contrabalanceados',
            'lead' => 'La solución versátil para cargar, descargar, transportar y elevar mercancía en interiores o exteriores.',
            'definition' => 'El contrapeso trasero equilibra la carga ubicada frente al mástil, permitiendo acercarse directamente a estibas, camiones y racks. Existen configuraciones eléctricas y de combustión para distintas capacidades y entornos.',
            'applications' => [
                ['Carga y descarga', 'Acceso frontal a camiones, patios y zonas de recibo.'],
                ['Industria', 'Movimiento de materias primas y producto terminado.'],
                ['Bodega', 'Transporte y almacenamiento con operación multipropósito.'],
            ],
            'advantages' => ['Alta versatilidad', 'Acceso frontal', 'Amplia oferta de capacidades'],
            'factors' => [
                ['Energía', 'Eléctrico para interior; combustión según ventilación y exigencia.'],
                ['Capacidad', 'Peso, centro de carga y accesorio instalado.'],
                ['Altura', 'Elevación máxima y altura del mástil colapsado.'],
                ['Entorno', 'Piso, pendientes, lluvia, polvo y espacio de giro.'],
            ],
            'comparisons' => [
                ['3 vs. 4 ruedas', 'Tres ruedas prioriza maniobrabilidad; cuatro ruedas favorece estabilidad y aplicaciones exigentes.'],
                ['Frente al retráctil', 'Es más versátil y apto para exterior, pero necesita pasillos más anchos.'],
            ],
        ],
        'electricos-de-3-ruedas' => [
            'family' => 'Contrabalanceados',
            'title' => 'Eléctricos de 3 ruedas',
            'lead' => 'Montacargas de giro compacto para interiores, carga y descarga, y bodegas donde cada centímetro de maniobra importa.',
            'definition' => 'Su arquitectura de tres puntos permite un radio de giro reducido sin renunciar al acceso frontal de un contrabalanceado. La propulsión eléctrica ofrece operación silenciosa y sin emisiones directas.',
            'applications' => [
                ['Bodegas compactas', 'Maniobra entre estanterías y zonas de consolidación.'],
                ['Alimentos y retail', 'Operación limpia y controlada en interiores.'],
                ['Muelles cubiertos', 'Carga y descarga con espacio de giro limitado.'],
            ],
            'advantages' => ['Giro muy cerrado', 'Operación silenciosa', 'Cero emisiones directas'],
            'factors' => [
                ['Estabilidad', 'Revisar carga, altura y velocidad para la aplicación real.'],
                ['Batería', 'Autonomía por turno y disponibilidad del cargador correcto.'],
                ['Piso', 'Preferencia por superficies firmes, niveladas y regulares.'],
                ['Pasillo', 'Medición con pallet y maniobra completa de almacenamiento.'],
            ],
            'comparisons' => [
                ['Frente a 4 ruedas', 'Gana maniobrabilidad; el de cuatro ruedas suele responder mejor en superficies y cargas exigentes.'],
                ['Frente al retráctil', 'Ofrece mayor versatilidad frontal, pero requiere más ancho para almacenar.'],
            ],
        ],
        'electricos-de-4-ruedas' => [
            'family' => 'Contrabalanceados',
            'title' => 'Eléctricos de 4 ruedas',
            'lead' => 'Estabilidad, eficiencia y operación limpia para jornadas industriales de carga y movimiento de materiales.',
            'definition' => 'El montacargas eléctrico de cuatro ruedas combina la estabilidad de un chasis convencional con motores eléctricos de respuesta precisa. Es apropiado para operaciones interiores y exteriores controladas donde se busca reducir ruido y emisiones.',
            'applications' => [
                ['Manufactura', 'Abastecimiento de planta y traslado de producto terminado.'],
                ['Bebidas y alimentos', 'Operación limpia en producción, cámara y despacho.'],
                ['Logística', 'Manipulación multipropósito con ciclos repetitivos.'],
            ],
            'advantages' => ['Estabilidad de marcha', 'Eficiencia energética', 'Menor ruido'],
            'factors' => [
                ['Capacidad', 'Carga máxima y capacidad residual con mástil o accesorio.'],
                ['Turnos', 'Autonomía, recarga de oportunidad o cambio de batería.'],
                ['Llantas', 'Tipo de piso, juntas, humedad y uso exterior ocasional.'],
                ['Cargador', 'Voltaje de red, ubicación, ventilación y conectores.'],
            ],
            'comparisons' => [
                ['Frente a 3 ruedas', 'Ofrece más estabilidad; el de tres ruedas gira mejor en espacios muy ajustados.'],
                ['Frente a combustión', 'Reduce ruido y emisiones directas, pero exige planificar carga y autonomía.'],
            ],
        ],
    ];
}

function tmd_equipment_guide_current(): ?array
{
    if (!is_page()) {
        return null;
    }

    $post = get_queried_object();
    if (!$post instanceof WP_Post) {
        return null;
    }

    $guides = tmd_equipment_guides();
    if (!isset($guides[$post->post_name])) {
        return null;
    }

    return ['slug' => $post->post_name] + $guides[$post->post_name];
}

function tmd_equipment_guide_body_class(array $classes): array
{
    if (tmd_equipment_guide_current()) {
        $classes[] = 'tmd-has-equipment-guide';
    }

    return $classes;
}
add_filter('body_class', 'tmd_equipment_guide_body_class');

function tmd_equipment_guide_content(string $content): string
{
    if (!in_the_loop() || !is_main_query()) {
        return $content;
    }

    $guide = tmd_equipment_guide_current();
    if (!$guide) {
        return $content;
    }

    $whatsapp = 'https://wa.me/' . rawurlencode(tmd_site_kit_option('phone'))
        . '?text=' . rawurlencode('Hola, quiero asesoría sobre ' . $guide['title'] . '.');

    ob_start();
    ?>
    <main class="tmd-guide">
        <section class="tmd-guide-hero">
            <div class="tmd-wrap tmd-guide-hero-grid">
                <div class="tmd-guide-hero-copy">
                    <a class="tmd-guide-back" href="<?php echo esc_url(home_url('/equipos/')); ?>">← Explorar todos los equipos</a>
                    <span class="tmd-guide-kicker"><?php echo esc_html($guide['family']); ?></span>
                    <h1><?php echo esc_html($guide['title']); ?></h1>
                    <p><?php echo esc_html($guide['lead']); ?></p>
                    <div class="tmd-guide-actions">
                        <a class="tmd-guide-btn tmd-guide-btn-primary" href="<?php echo esc_url(home_url('/equipos/')); ?>">Ver equipos disponibles <span>→</span></a>
                        <a class="tmd-guide-btn tmd-guide-btn-ghost" href="<?php echo esc_url($whatsapp); ?>">Hablar con un asesor</a>
                    </div>
                </div>
                <div class="tmd-guide-visual" role="img" aria-label="Espacio reservado para imagen de <?php echo esc_attr($guide['title']); ?>">
                    <span class="tmd-guide-visual-label">Imagen de categoría</span>
                    <div class="tmd-guide-machine" aria-hidden="true">
                        <i class="tmd-guide-mast"></i>
                        <i class="tmd-guide-cabin"></i>
                        <i class="tmd-guide-body"></i>
                        <i class="tmd-guide-forks"></i>
                        <i class="tmd-guide-wheel tmd-guide-wheel-one"></i>
                        <i class="tmd-guide-wheel tmd-guide-wheel-two"></i>
                    </div>
                    <small>Fotografía final pendiente</small>
                </div>
            </div>
        </section>

        <section class="tmd-guide-intro">
            <div class="tmd-wrap tmd-guide-intro-grid">
                <div>
                    <span class="tmd-guide-section-number">01</span>
                    <p class="tmd-guide-overline">Guía práctica</p>
                    <h2>¿Qué es y para qué sirve?</h2>
                </div>
                <p class="tmd-guide-definition"><?php echo esc_html($guide['definition']); ?></p>
            </div>
            <div class="tmd-wrap tmd-guide-benefits" aria-label="Ventajas principales">
                <?php foreach ($guide['advantages'] as $index => $advantage) : ?>
                    <div><span>0<?php echo esc_html((string) ($index + 1)); ?></span><strong><?php echo esc_html($advantage); ?></strong></div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="tmd-guide-section tmd-guide-section-soft">
            <div class="tmd-wrap">
                <div class="tmd-guide-section-heading">
                    <div><span class="tmd-guide-section-number">02</span><p class="tmd-guide-overline">Dónde aporta valor</p></div>
                    <h2>Aplicaciones frecuentes</h2>
                    <p>La configuración final debe validarse con las condiciones reales de tu operación.</p>
                </div>
                <div class="tmd-guide-applications">
                    <?php foreach ($guide['applications'] as $index => $application) : ?>
                        <article>
                            <span class="tmd-guide-card-icon" aria-hidden="true"><?php echo esc_html((string) ($index + 1)); ?></span>
                            <h3><?php echo esc_html($application[0]); ?></h3>
                            <p><?php echo esc_html($application[1]); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="tmd-guide-section">
            <div class="tmd-wrap tmd-guide-selection-grid">
                <div class="tmd-guide-selection-copy">
                    <span class="tmd-guide-section-number">03</span>
                    <p class="tmd-guide-overline">Selección técnica</p>
                    <h2>Qué debes validar antes de elegir</h2>
                    <p>Una cotización responsable parte de datos de campo. Estos cuatro puntos evitan sobredimensionar el equipo o comprometer capacidad y seguridad.</p>
                    <a href="<?php echo esc_url(home_url('/encuentra-tu-equipo/')); ?>">Usar el recomendador de equipos →</a>
                </div>
                <div class="tmd-guide-factors">
                    <?php foreach ($guide['factors'] as $index => $factor) : ?>
                        <article>
                            <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                            <div><h3><?php echo esc_html($factor[0]); ?></h3><p><?php echo esc_html($factor[1]); ?></p></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="tmd-guide-section tmd-guide-compare">
            <div class="tmd-wrap">
                <div class="tmd-guide-section-heading tmd-guide-section-heading-light">
                    <div><span class="tmd-guide-section-number">04</span><p class="tmd-guide-overline">Decisión informada</p></div>
                    <h2>Comparación rápida</h2>
                    <p>Diferencias útiles para orientar la primera selección.</p>
                </div>
                <div class="tmd-guide-compare-grid">
                    <?php foreach ($guide['comparisons'] as $comparison) : ?>
                        <article><h3><?php echo esc_html($comparison[0]); ?></h3><p><?php echo esc_html($comparison[1]); ?></p></article>
                    <?php endforeach; ?>
                    <aside>
                        <span>Recomendación TMD</span>
                        <p>No decidas solo por la capacidad nominal. La altura, el centro de carga, el accesorio y el entorno cambian el desempeño real.</p>
                    </aside>
                </div>
            </div>
        </section>

        <section class="tmd-guide-final">
            <div class="tmd-wrap tmd-guide-final-grid">
                <div><p class="tmd-guide-overline">Siguiente paso</p><h2>Convirtamos tu operación en una especificación correcta.</h2></div>
                <div>
                    <p>Cuéntanos carga, altura, pasillo y horas de uso. Un asesor comercial te ayudará a comparar alternativas disponibles.</p>
                    <div class="tmd-guide-actions">
                        <a class="tmd-guide-btn tmd-guide-btn-primary" href="<?php echo esc_url($whatsapp); ?>">Solicitar asesoría <span>→</span></a>
                        <a class="tmd-guide-text-link" href="<?php echo esc_url(home_url('/nosotros/contacto/')); ?>">Ir a contacto</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php

    return ob_get_clean();
}
add_filter('the_content', 'tmd_equipment_guide_content', 8);
