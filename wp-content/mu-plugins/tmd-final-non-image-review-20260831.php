<?php
/**
 * Completa los cambios no gráficos solicitados en la revisión del 31 de agosto.
 *
 * Este MU plugin se ejecuta después de los ajustes de contenido existentes y
 * conserva intacta la sección hero, incluidas las imágenes ya configuradas.
 */
defined('ABSPATH') || exit;

function tmd_final_non_image_guide_data(): array
{
    return [
        'estibadores-manuales' => [
            'summary' => 'Equipos diseñados para elevar ligeramente cargas sobre estibas y facilitar su traslado horizontal de forma segura y eficiente en bodegas, centros de distribución y áreas logísticas.',
            'intro' => [
                'Los estibadores manuales son equipos diseñados para elevar ligeramente y trasladar cargas sobre estibas en distancias cortas.',
                'Funcionan mediante un sistema hidráulico accionado manualmente por el operador y no requieren baterías, combustible ni alimentación eléctrica, lo que facilita su operación y mantenimiento.',
                'Son ideales para operaciones de baja o media intensidad en bodegas, centros de distribución y áreas logísticas donde se requiere un manejo práctico y eficiente de mercancías.',
            ],
            'highlights' => [
                ['Mantenimiento sencillo', 'Menor cantidad de componentes y facilidad de mantenimiento.'],
                ['Sin batería ni combustible', 'Operación manual sin necesidad de fuentes externas de energía.'],
                ['Versatilidad de uso', 'Adecuados para diferentes áreas y operaciones de traslado interno.'],
            ],
            'applications_intro' => 'Solución práctica para el manejo de cargas en operaciones logísticas, industriales y comerciales, especialmente en recorridos cortos y superficies niveladas.',
            'applications' => [
                ['Movimiento en bodega', 'Traslado y ubicación de estibas entre diferentes zonas de almacenamiento.'],
                ['Preparación y despacho', 'Organización y movilización de mercancía en áreas de preparación y despacho.'],
                ['Carga y descarga', 'Apoyo en la movilización de mercancías durante procesos de recepción y despacho de vehículos.'],
                ['Abastecimiento de producción', 'Suministro de materiales e insumos a líneas y áreas de producción.'],
                ['Recorridos en superficies planas', 'Manejo de cargas en trayectos cortos sobre pisos firmes, uniformes y nivelados.'],
            ],
            'selection_intro' => 'La elección de un estibador manual debe considerar las condiciones reales de la operación, como el peso de la carga, la distancia de los recorridos, la frecuencia de uso y las características del piso.',
            'considerations' => [
                ['Esfuerzo del operador', 'El desplazamiento y la maniobra se realizan manualmente, por lo que es importante considerar el peso de la carga y la frecuencia de uso.'],
                ['Recorridos y frecuencia', 'Son recomendados para trayectos cortos y operaciones de baja o media intensidad. Para recorridos largos o movimientos continuos, puede ser más conveniente evaluar una alternativa eléctrica.'],
                ['Condiciones del piso', 'Su mejor desempeño se obtiene sobre superficies firmes, uniformes y niveladas. Para operaciones con pendientes o condiciones especiales de piso, se debe evaluar otro tipo de equipo.'],
                ['Espacio de maniobra', 'Verifica que pasillos, accesos y zonas de operación permitan realizar maniobras de forma segura con las dimensiones de la carga y el equipo.'],
            ],
        ],
        'estibadores-electricos' => [
            'summary' => 'Equipos diseñados para elevar ligeramente y trasladar cargas sobre estibas, incorporando tracción eléctrica para facilitar el desplazamiento y reducir el esfuerzo físico del operador.',
            'intro' => [
                'Los estibadores eléctricos son equipos de manejo de materiales que incorporan tracción eléctrica para facilitar el traslado de cargas sobre estibas y reducir el esfuerzo físico del operador.',
                'Son adecuados para operaciones con mayor frecuencia de movimientos o recorridos más prolongados, donde el desplazamiento manual puede resultar menos eficiente.',
                'Según su configuración, el operador puede acompañar el equipo a pie, utilizar una plataforma abatible o conducir desde una posición integrada.',
            ],
            'highlights' => [
                ['Menor esfuerzo físico', 'La tracción eléctrica facilita el desplazamiento de la carga y reduce el esfuerzo requerido durante la operación.'],
                ['Mayor eficiencia operativa', 'Favorece un desplazamiento más ágil y continuo, especialmente en operaciones con movimientos frecuentes.'],
                ['Diferentes configuraciones de operación', 'Disponibles en versiones de operador acompañante, con plataforma o con posición de conducción integrada.'],
            ],
            'applications_intro' => 'Los estibadores eléctricos facilitan el traslado de cargas en operaciones logísticas, industriales y comerciales que requieren movimientos frecuentes y recorridos internos.',
            'applications' => [
                ['Centros de distribución', 'Traslado de estibas entre áreas de recibo, almacenamiento, preparación de pedidos y despacho.'],
                ['Muelles de carga', 'Movilización de estibas durante los procesos de recepción y despacho de mercancías.'],
                ['Plantas industriales', 'Abastecimiento de materiales y traslado de producto terminado entre diferentes áreas de operación.'],
                ['Grandes superficies', 'Movilización de mercancías entre zonas de recibo, almacenamiento y abastecimiento interno.'],
                ['Preparación de pedidos', 'Traslado de mercancías y abastecimiento de áreas destinadas a la preparación de pedidos y picking.'],
            ],
            'selection_intro' => 'La selección de un estibador eléctrico debe considerar las condiciones reales de la operación, como el peso de la carga, los recorridos, la frecuencia de uso, el espacio disponible y los requerimientos de autonomía.',
            'considerations' => [
                ['Peso de la carga', 'Verifica el peso máximo que deberá transportar el equipo para seleccionar la capacidad adecuada.'],
                ['Distancia y recorridos', 'Evalúa la longitud y frecuencia de los desplazamientos, así como las condiciones del trayecto.'],
                ['Jornada y frecuencia de uso', 'Considera las horas de trabajo, el número de movimientos y la intensidad de la operación durante la jornada.'],
                ['Espacio de maniobra', 'Verifica el ancho de pasillos, accesos y zonas de trabajo para seleccionar una configuración adecuada para la operación.'],
                ['Batería y autonomía', 'Evalúa la tecnología de batería y la autonomía requerida de acuerdo con la jornada y la intensidad de uso.'],
            ],
        ],
        'apiladores-electricos' => [
            'summary' => 'Equipos diseñados para trasladar y elevar cargas sobre estibas, facilitando su almacenamiento y manipulación a diferentes alturas de forma eficiente y segura.',
            'intro' => [
                'Los apiladores eléctricos están diseñados para trasladar, elevar y posicionar cargas sobre estibas, facilitando tareas de almacenamiento, apilamiento y abastecimiento de mercancías.',
                'Gracias a su diseño compacto y sistema de elevación, pueden operar en espacios reducidos y pasillos donde se requiere mayor maniobrabilidad para ubicar cargas a diferentes alturas.',
                'Son una solución eficiente para operaciones que necesitan combinar desplazamiento horizontal y almacenamiento en altura.',
            ],
            'highlights' => [
                ['Transporte y elevación', 'Permiten trasladar y elevar cargas sobre estibas para facilitar su ubicación y almacenamiento.'],
                ['Diseño compacto', 'Facilitan las maniobras en pasillos y espacios reducidos, según las dimensiones y configuración del equipo.'],
                ['Almacenamiento en altura', 'Permiten posicionar cargas a diferentes niveles de almacenamiento, de acuerdo con la capacidad y altura de elevación del equipo.'],
            ],
            'applications_intro' => 'Los apiladores eléctricos son una solución versátil para operaciones que requieren trasladar, elevar y posicionar cargas en áreas logísticas, industriales y de almacenamiento.',
            'applications' => [
                ['Apilamiento de mercancías', 'Elevación y posicionamiento de estibas para organizar y aprovechar eficientemente las áreas de almacenamiento.'],
                ['Almacenamiento en estanterías', 'Ubicación y retiro de cargas en diferentes niveles de estantería, según la altura y capacidad del equipo.'],
                ['Recepción y despacho', 'Manipulación y posicionamiento de estibas en zonas destinadas al recibo y despacho de mercancías.'],
                ['Abastecimiento de producción', 'Traslado y suministro de materiales e insumos a diferentes áreas y líneas de producción.'],
                ['Bodegas y espacios compactos', 'Manejo y organización de mercancías en áreas donde el espacio disponible requiere equipos de dimensiones reducidas.'],
                ['Pasillos reducidos', 'Maniobra y posicionamiento de cargas en pasillos con espacio limitado, de acuerdo con el radio de giro y las dimensiones del equipo.'],
            ],
            'selection_intro' => 'Para seleccionar el apilador eléctrico adecuado, es importante evaluar el peso y las dimensiones de la carga, la altura de elevación requerida, la capacidad residual y el espacio disponible para maniobrar.',
            'considerations' => [
                ['Peso de la carga', 'Verifica el peso máximo de las estibas que se deben manipular para determinar la capacidad nominal requerida.'],
                ['Altura de elevación', 'Define la altura máxima a la que deberá posicionarse la carga, considerando los niveles de almacenamiento y la configuración de la estantería.'],
                ['Capacidad residual', 'Confirma la capacidad que el equipo puede manejar de forma segura a la altura de trabajo requerida.'],
                ['Dimensiones de la carga', 'Considera las medidas de la estiba y de la carga, ya que influyen en la estabilidad, manipulación y selección del equipo.'],
                ['Pasillos y espacio de maniobra', 'Verifica el ancho de los pasillos, accesos y zonas de trabajo considerando las dimensiones de la carga y el espacio requerido para maniobrar.'],
                ['Jornada y autonomía', 'Evalúa las horas de uso y la frecuencia de los movimientos para determinar la autonomía y configuración de batería adecuadas.'],
            ],
        ],
        'retractiles-de-mastil-movil' => [
            'summary' => 'Equipos diseñados para el manejo y almacenamiento de cargas en altura, especialmente en pasillos reducidos. Su sistema retráctil permite extender el mástil hacia la carga y retraerlo durante el desplazamiento, favoreciendo una operación compacta y eficiente.',
            'intro' => [
                'Los montacargas retráctiles están diseñados para manipular, elevar y almacenar cargas en altura, especialmente en bodegas con pasillos reducidos y sistemas de estantería.',
                'Su mecanismo retráctil permite extender el mástil para tomar o posicionar la carga y retraerlo para acercarla al cuerpo del equipo durante el desplazamiento.',
                'Esta configuración favorece la maniobrabilidad y el aprovechamiento del espacio, especialmente en operaciones que requieren almacenamiento vertical.',
            ],
            'highlights' => [
                ['Mecanismo retráctil', 'Permite extender y retraer el mástil para acceder a las cargas y posicionarlas en los diferentes niveles de la estantería.'],
                ['Maniobrabilidad en espacios reducidos', 'Su configuración facilita la operación entre estanterías y en pasillos donde el espacio de maniobra es limitado.'],
                ['Operación en altura', 'Permite elevar y posicionar cargas a diferentes alturas, de acuerdo con la capacidad residual y configuración del equipo.'],
            ],
            'applications_intro' => 'Los montacargas retráctiles son adecuados para operaciones que requieren almacenamiento en altura, optimización del espacio y maniobrabilidad entre estanterías.',
            'applications' => [
                ['Pasillos reducidos', 'Facilitan la manipulación y posicionamiento de cargas en pasillos donde el espacio de maniobra es limitado.'],
                ['Almacenamiento en altura', 'Permiten ubicar y retirar cargas en diferentes niveles de estantería, según la capacidad y configuración del equipo.'],
                ['Almacenamiento de alta densidad', 'Favorecen el aprovechamiento del espacio vertical y la distribución eficiente de las áreas de almacenamiento.'],
                ['Operaciones en interiores', 'Adecuados para trabajar en bodegas y centros logísticos sobre superficies firmes, uniformes y niveladas.'],
                ['Operaciones de alta rotación', 'Facilitan el manejo frecuente de mercancías en operaciones con flujos constantes de almacenamiento, preparación y despacho.'],
            ],
            'selection_intro' => 'Para seleccionar el montacargas retráctil adecuado, es importante evaluar las condiciones reales de almacenamiento y operación: peso y dimensiones de la carga, altura requerida, capacidad residual, ancho de pasillo y características de la estantería.',
            'considerations' => [
                ['Peso y dimensiones de la carga', 'Verifica el peso máximo y las dimensiones de las estibas que se deben manipular para determinar la capacidad requerida.'],
                ['Altura de elevación', 'Define la altura máxima de almacenamiento y los niveles de estantería donde deberá posicionarse la carga.'],
                ['Capacidad residual', 'Confirma la capacidad que el equipo puede manejar de forma segura a la altura máxima de trabajo requerida.'],
                ['Ancho de pasillo', 'Mide el espacio útil entre estanterías y valida el área necesaria para maniobrar con las dimensiones reales de la carga.'],
                ['Tipo de estantería', 'Verifica la compatibilidad entre el equipo, las dimensiones de la estiba, la configuración del rack y los niveles de almacenamiento.'],
                ['Jornada y autonomía', 'Considera las horas de trabajo, la frecuencia de movimientos y la intensidad de uso para determinar los requerimientos de autonomía y batería.'],
            ],
        ],
        'pantografo-sencillo' => [
            'summary' => 'Equipos diseñados para el manejo y almacenamiento de cargas en altura. Incorporan un mecanismo de pantógrafo que permite extender y retraer las horquillas para tomar o posicionar cargas en estanterías, sin necesidad de desplazar el mástil hacia adelante.',
            'intro' => [
                'Los montacargas con pantógrafo sencillo están diseñados para manipular y almacenar cargas en altura mediante un mecanismo que extiende y retrae las horquillas hacia la posición de la carga.',
                'Este sistema permite tomar o depositar estibas ubicadas en estanterías de profundidad simple, facilitando el acceso a la carga sin necesidad de desplazar el mástil hacia adelante.',
                'Su configuración combina alcance horizontal, precisión de posicionamiento y maniobrabilidad para operaciones de almacenamiento en estanterías.',
            ],
            'highlights' => [
                ['Profundidad simple', 'Diseñados para acceder a estibas ubicadas en una posición de almacenamiento directamente frente al pasillo.'],
                ['Alcance horizontal', 'El pantógrafo permite extender las horquillas hacia la carga y retraerlas después de tomarla o posicionarla.'],
                ['Posicionamiento en estanterías', 'Facilitan la ubicación y retiro de cargas a diferentes niveles, según la altura y capacidad del equipo.'],
            ],
            'selection_intro' => 'Para seleccionar un montacargas con pantógrafo sencillo, es importante evaluar las características de la carga, la altura de almacenamiento, la profundidad de la estantería y el espacio disponible para maniobrar.',
            'considerations' => [
                ['Peso y dimensiones de la carga', 'Verifica el peso máximo y las dimensiones de las estibas para determinar la capacidad requerida del equipo.'],
                ['Altura de elevación', 'Define la altura máxima a la que deberá ubicarse la carga y los niveles de estantería que se deben alcanzar.'],
                ['Capacidad residual', 'Confirma la capacidad disponible del equipo a la altura y alcance requeridos durante la operación.'],
                ['Profundidad de la estantería', 'Verifica que la configuración corresponda a posiciones de profundidad simple y que el alcance del pantógrafo sea compatible con el rack.'],
                ['Ancho de pasillo', 'Mide el espacio disponible entre estanterías y valida el área necesaria para maniobrar con la carga.'],
                ['Compatibilidad con la estiba', 'Comprueba que las dimensiones y orientación de la estiba sean compatibles con las horquillas, el pantógrafo y la configuración de la estantería.'],
            ],
        ],
        'pantografo-doble-profundidad' => [
            'summary' => 'Equipos diseñados para operaciones de almacenamiento de alta densidad. Su mecanismo de pantógrafo permite extender las horquillas para acceder a una segunda posición de carga ubicada detrás de la primera dentro de una configuración de estantería de doble profundidad.',
            'intro' => [
                'Los montacargas con pantógrafo de doble profundidad incorporan un mecanismo extensible que permite alcanzar cargas ubicadas en una segunda posición dentro de la estantería.',
                'Esta configuración permite almacenar dos estibas en profundidad, aumentando la densidad de almacenamiento y aprovechando mejor el espacio disponible en la bodega.',
                'Por sus características, son especialmente útiles en operaciones donde se busca maximizar la capacidad de almacenamiento y existe una adecuada planificación de la ubicación y rotación del inventario.',
            ],
            'highlights' => [
                ['Doble profundidad', 'Permiten acceder a posiciones de almacenamiento ubicadas detrás de una primera estiba, según la configuración de la estantería.'],
                ['Mayor densidad de almacenamiento', 'Favorecen un mayor aprovechamiento del espacio al permitir almacenar cargas en dos posiciones de profundidad.'],
                ['Alcance extendido', 'El mecanismo de pantógrafo proporciona el alcance horizontal necesario para tomar o posicionar cargas en la segunda profundidad.'],
            ],
            'selection_intro' => 'Para seleccionar un montacargas con pantógrafo de doble profundidad, es importante evaluar el peso y las dimensiones de la carga, la altura de elevación, el alcance requerido, la capacidad residual, la configuración de la estantería y la rotación del inventario.',
            'considerations' => [
                ['Peso y dimensiones de la carga', 'Verifica el peso máximo y las dimensiones de las estibas para determinar la capacidad y configuración requeridas.'],
                ['Altura y alcance', 'Define la altura máxima de almacenamiento y la profundidad que debe alcanzar el pantógrafo para acceder a las posiciones posteriores.'],
                ['Capacidad residual', 'Confirma la capacidad disponible del equipo considerando la altura de elevación y el alcance requerido durante la operación.'],
                ['Configuración de la estantería', 'Verifica que el rack esté diseñado para almacenamiento de doble profundidad y sea compatible con las dimensiones de las cargas y el alcance del equipo.'],
                ['Acceso a las posiciones', 'Considera que la carga ubicada en la primera posición puede limitar el acceso directo a la estiba almacenada detrás.'],
                ['Rotación del inventario', 'Planifica la ubicación de las mercancías según su frecuencia de entrada y salida para facilitar el acceso y evitar movimientos innecesarios.'],
            ],
        ],
        'tomapedidos-de-alto-nivel' => [
            'summary' => 'Equipos diseñados para operaciones de picking en altura, que permiten al operador acceder a diferentes niveles de la estantería para seleccionar y recoger productos de forma eficiente y segura.',
            'intro' => [
                'Los tomapedidos de alto nivel están diseñados para operaciones de picking en las que el operador necesita acceder directamente a productos almacenados en diferentes niveles de la estantería.',
                'La plataforma del operador se eleva hasta la altura de trabajo requerida, facilitando la selección de unidades, cajas o referencias para la preparación de pedidos.',
            ],
            'highlights' => [
                ['Picking en altura', 'Permiten realizar la selección de productos almacenados en diferentes niveles de la estantería.'],
                ['Acceso directo a productos', 'El operador puede acceder a unidades, cajas o referencias directamente desde su ubicación de almacenamiento.'],
                ['Preparación de pedidos', 'Facilitan la recolección y organización de mercancías durante los procesos de picking y preparación de pedidos.'],
            ],
            'applications_intro' => 'Los tomapedidos de alto nivel son ideales para operaciones que requieren seleccionar y preparar pedidos directamente desde diferentes niveles de la estantería, especialmente en entornos con gran variedad de referencias.',
            'applications' => [
                ['Centros de distribución', 'Preparación de pedidos y recolección de productos en operaciones con múltiples ubicaciones de almacenamiento.'],
                ['Bodegas de repuestos', 'Acceso y selección de piezas, cajas y referencias almacenadas en diferentes niveles de estantería.'],
                ['Comercio electrónico', 'Recolección de productos para la preparación de pedidos con múltiples referencias y unidades.'],
                ['Almacenes con alta variedad', 'Facilitan el acceso a inventarios con numerosas referencias distribuidas en diferentes ubicaciones y niveles.'],
                ['Picking por unidad o caja', 'Permiten seleccionar productos directamente desde la estantería sin necesidad de movilizar una estiba completa.'],
                ['Picking en niveles elevados', 'Facilitan el acceso del operador a productos almacenados en altura para su selección y preparación.'],
            ],
            'selection_intro' => 'Para seleccionar el tomapedidos de alto nivel adecuado, es importante evaluar la altura de trabajo, las características de los productos, el ancho de los pasillos, la frecuencia de uso y las condiciones del área de operación.',
            'considerations' => [
                ['Altura de trabajo', 'Define la altura máxima a la que el operador necesita acceder para realizar la selección de productos.'],
                ['Capacidad y tipo de carga', 'Verifica el peso y las dimensiones de los productos o unidades que se manipularán durante la preparación de pedidos.'],
                ['Ancho de pasillo', 'Mide el espacio disponible entre estanterías y valida que sea compatible con las dimensiones y maniobrabilidad del equipo.'],
                ['Jornada y frecuencia de uso', 'Considera las horas de trabajo y la intensidad de las operaciones de picking para determinar la autonomía requerida.'],
                ['Pisos y condiciones de operación', 'Verifica que las superficies de circulación sean firmes, uniformes y adecuadas para una operación estable y segura.'],
                ['Seguridad del operador', 'Evalúa los sistemas y elementos de seguridad requeridos para el trabajo en altura, de acuerdo con la configuración del equipo y las condiciones de la operación.'],
            ],
        ],
        'electricos-de-3-ruedas' => [
            'summary' => 'Equipos diseñados para el traslado, elevación y manipulación de cargas, con una configuración de tres ruedas que favorece un radio de giro reducido y una mayor maniobrabilidad en espacios limitados.',
            'intro' => [
                'Los montacargas contrabalanceados eléctricos de tres ruedas están diseñados para trasladar, elevar y posicionar cargas en operaciones que requieren alta maniobrabilidad y precisión.',
                'Su configuración de tres ruedas permite realizar giros en espacios más reducidos, lo que los hace adecuados para bodegas, pasillos estrechos y áreas con espacio limitado de maniobra.',
                'Su sistema de propulsión eléctrica favorece su uso en operaciones interiores donde se requiere un manejo eficiente de mercancías.',
            ],
            'highlights' => [
                ['Radio de giro reducido', 'Su configuración facilita los giros y maniobras en áreas donde el espacio disponible es limitado.'],
                ['Alta maniobrabilidad', 'Permiten realizar movimientos precisos en pasillos, zonas de almacenamiento y áreas de trabajo con espacio restringido.'],
                ['Operación en interiores', 'Su sistema eléctrico los hace especialmente adecuados para bodegas, centros logísticos y otras operaciones interiores.'],
            ],
            'applications_intro' => 'Los montacargas eléctricos de tres ruedas son adecuados para operaciones logísticas, industriales y comerciales que requieren agilidad y maniobrabilidad en espacios de trabajo limitados.',
            'applications' => [
                ['Bodegas y almacenes', 'Traslado, elevación y posicionamiento de cargas en áreas interiores de almacenamiento.'],
                ['Recepción y despacho', 'Manipulación de cargas en zonas de recibo, preparación y despacho de mercancías.'],
                ['Plantas de producción', 'Abastecimiento de materiales e insumos y traslado de producto terminado entre diferentes áreas de trabajo.'],
                ['Centros de distribución', 'Manejo de mercancías entre zonas de almacenamiento, preparación de pedidos y despacho.'],
                ['Espacios de maniobra reducidos', 'Operaciones donde un radio de giro reducido facilita el posicionamiento y manejo de cargas.'],
            ],
            'selection_intro' => 'Para seleccionar el montacargas eléctrico de tres ruedas adecuado, es importante evaluar el peso y las dimensiones de la carga, la altura de elevación, el espacio disponible, las condiciones del piso y la intensidad de la operación.',
            'considerations' => [
                ['Peso y dimensiones de la carga', 'Verifica el peso máximo y las dimensiones de las cargas que se deben manipular para determinar la capacidad adecuada del equipo.'],
                ['Altura de elevación', 'Define la altura máxima de trabajo y verifica la capacidad disponible del equipo a la altura requerida.'],
                ['Espacio de maniobra', 'Evalúa el ancho de pasillos, accesos y zonas de trabajo para aprovechar las ventajas de su radio de giro reducido.'],
                ['Condiciones del piso', 'Verifica que las superficies de circulación sean firmes, uniformes y adecuadas para una operación estable y segura.'],
                ['Jornada y autonomía', 'Considera las horas de trabajo, la frecuencia de movimientos y la intensidad de uso para determinar los requerimientos de batería y autonomía.'],
            ],
        ],
    ];
}

function tmd_final_non_image_render_intro(array $data, int &$section_number): string
{
    $html = '<section class="tmd-type-guide__section tmd-type-guide__section--review-intro"><div class="tmd-type-guide__wrap tmd-type-guide__intro">';
    $html .= '<div><span class="tmd-type-guide__number">' . esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)) . '</span><p class="tmd-type-guide__overline">Guía práctica</p><h2>¿Qué es y para qué sirve?</h2></div>';
    $html .= '<div class="tmd-type-guide__copy">';
    foreach ($data['intro'] as $paragraph) {
        $html .= '<p>' . esc_html($paragraph) . '</p>';
    }
    $html .= '</div></div>';
    $html .= '<div class="tmd-type-guide__wrap"><p class="tmd-type-guide__highlights-title">Características destacadas</p><div class="tmd-type-guide__highlights">';
    foreach ($data['highlights'] as $index => $highlight) {
        $html .= '<div><span>' . esc_html((string) ($index + 1)) . '</span><div><strong>' . esc_html($highlight[0]) . '</strong><p>' . esc_html($highlight[1]) . '</p></div></div>';
    }
    $html .= '</div></div></section>';
    return $html;
}

function tmd_final_non_image_render_applications(array $data, int &$section_number): string
{
    if (empty($data['applications'])) {
        return '';
    }

    $html = '<section class="tmd-type-guide__section tmd-type-guide__section--soft tmd-type-guide__section--review-applications"><div class="tmd-type-guide__wrap">';
    $html .= '<div class="tmd-type-guide__heading"><div><span class="tmd-type-guide__number">' . esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)) . '</span><p class="tmd-type-guide__overline">Dónde aporta valor</p></div><h2>Aplicaciones frecuentes</h2><p>' . esc_html($data['applications_intro']) . '</p></div>';
    $html .= '<div class="tmd-type-guide__cards">';
    foreach ($data['applications'] as $index => $application) {
        $html .= '<article><span class="tmd-type-guide__card-number">' . esc_html((string) ($index + 1)) . '</span><h3>' . esc_html($application[0]) . '</h3><p>' . esc_html($application[1]) . '</p></article>';
    }
    $html .= '</div></div></section>';
    return $html;
}

function tmd_final_non_image_render_selection(array $data, int &$section_number): string
{
    $html = '<section class="tmd-type-guide__section tmd-type-guide__section--review-selection"><div class="tmd-type-guide__wrap tmd-type-guide__selection">';
    $html .= '<div class="tmd-type-guide__selection-copy"><span class="tmd-type-guide__number">' . esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)) . '</span><p class="tmd-type-guide__overline">Selección técnica</p><h2>¿Qué debes validar antes de elegir?</h2><p>' . esc_html($data['selection_intro']) . '</p><a href="' . esc_url(home_url('/encuentra-tu-equipo/')) . '">Usar el recomendador de equipos →</a></div>';
    $html .= '<div class="tmd-type-guide__factors">';
    foreach ($data['considerations'] as $index => $factor) {
        $html .= '<article><span>' . esc_html((string) ($index + 1)) . '</span><div><h3>' . esc_html($factor[0]) . '</h3><p>' . esc_html($factor[1]) . '</p></div></article>';
    }
    $html .= '</div></div></section>';
    return $html;
}

function tmd_final_non_image_render_final(array $guide, int &$section_number): string
{
    $whatsapp = 'https://wa.me/573015556180?text=' . rawurlencode('Hola, quiero asesoría sobre ' . $guide['title'] . '.');
    $html = '<section class="tmd-type-guide__final tmd-type-guide__final--numbered"><div class="tmd-type-guide__wrap tmd-type-guide__final-grid">';
    $html .= '<div><span class="tmd-type-guide__number">' . esc_html(str_pad((string) $section_number++, 2, '0', STR_PAD_LEFT)) . '</span><p class="tmd-type-guide__overline">Siguiente paso</p><h2>Selecciona el equipo adecuado para tu operación.</h2></div>';
    $html .= '<div><p>Cuéntanos el peso de la carga, la altura, el ancho del pasillo, el recorrido y las horas de uso. Un asesor te ayudará a comparar las alternativas disponibles.</p><div class="tmd-type-guide__actions">';
    $html .= '<a class="tmd-type-guide__button tmd-type-guide__button--primary" href="' . esc_url($whatsapp) . '">Solicitar asesoría <span>→</span></a>';
    $html .= '<a class="tmd-type-guide__text-link" href="' . esc_url(home_url('/nosotros/contacto/')) . '">Ir a contacto</a></div></div></div></section>';
    return $html;
}

add_filter('the_content', static function (string $content): string {
    if (!function_exists('tmd_equipment_type_guide_current')) {
        return $content;
    }

    $guide = tmd_equipment_type_guide_current();
    if (!$guide || empty($guide['slug'])) {
        return $content;
    }

    $all = tmd_final_non_image_guide_data();
    $slug = (string) $guide['slug'];
    if (!isset($all[$slug])) {
        return $content;
    }

    $data = $all[$slug];

    $content = preg_replace(
        '#(<h1>[^<]+</h1>\s*)<p>.*?</p>#s',
        '$1<p>' . esc_html($data['summary']) . '</p>',
        $content,
        1
    ) ?: $content;

    $section_number = 1;
    $body = tmd_final_non_image_render_intro($data, $section_number)
        . tmd_final_non_image_render_applications($data, $section_number)
        . tmd_final_non_image_render_selection($data, $section_number)
        . tmd_final_non_image_render_final($guide, $section_number);

    $updated = preg_replace(
        '#(<main class="tmd-type-guide alignfull">\s*<section class="tmd-type-guide__hero">.*?</section>).*?</main>#s',
        '$1' . $body . '</main>',
        $content,
        1
    );

    return is_string($updated) ? $updated : $content;
}, 130);

add_action('wp_head', static function (): void {
    if (function_exists('tmd_equipment_type_guide_current') && tmd_equipment_type_guide_current()) {
        ?>
        <style id="tmd-final-non-image-guide-review">
          .tmd-type-guide__hero h1{font-size:clamp(2.25rem,4vw,4rem);line-height:1.03;max-width:760px}
          .tmd-type-guide__section--review-intro{background:linear-gradient(135deg,rgba(18,140,235,.07),transparent 48%),#fff}
          .tmd-type-guide__section--review-applications{background:linear-gradient(135deg,rgba(255,195,60,.12),transparent 42%),#f4f7fb}
          .tmd-type-guide__section--review-selection{background:linear-gradient(135deg,rgba(94,116,139,.08),transparent 42%),#fff}
          .tmd-type-guide__number{color:#128ceb;font-size:3.8rem}
          .tmd-type-guide__section--review-applications .tmd-type-guide__number{color:#a97800}
          .tmd-type-guide__section--review-selection .tmd-type-guide__number{color:#5e748b}
          .tmd-type-guide__highlights-title{margin:2.3rem 0 1rem;color:#262e4f;font-size:1.05rem;font-weight:800;text-align:center}
          .tmd-type-guide__highlights{gap:1rem;border:0}
          .tmd-type-guide__highlights>div{align-items:flex-start;min-height:150px;border:1px solid rgba(18,140,235,.25)!important;border-top:4px solid #128ceb!important;border-radius:12px;background:#eaf4fd!important;text-align:left}
          .tmd-type-guide__highlights>div:nth-child(2){border-color:rgba(255,195,60,.48)!important;border-top-color:#ffc33c!important;background:#fff8e7!important}
          .tmd-type-guide__highlights>div:nth-child(3){border-color:rgba(38,46,79,.18)!important;border-top-color:#262e4f!important;background:#f1f3f8!important}
          .tmd-type-guide__highlights>div>span{flex:0 0 auto;font-size:1.45rem;line-height:1;color:#128ceb}
          .tmd-type-guide__highlights>div:nth-child(2)>span{color:#9b7000}
          .tmd-type-guide__highlights>div:nth-child(3)>span{color:#262e4f}
          .tmd-type-guide__highlights strong{display:block;color:#262e4f;text-align:center}
          .tmd-type-guide__highlights p{margin:.55rem 0 0;color:#5e748b;line-height:1.6;text-align:justify}
          .tmd-type-guide__cards article{border:1px solid rgba(18,140,235,.22);border-top:4px solid #128ceb;background:#eef7ff}
          .tmd-type-guide__cards article:nth-child(3n+2){border-color:rgba(255,195,60,.44);border-top-color:#ffc33c;background:#fff9e8}
          .tmd-type-guide__cards article:nth-child(3n){border-color:rgba(38,46,79,.18);border-top-color:#262e4f;background:#f2f4f8}
          .tmd-type-guide__cards h3{text-align:center}
          .tmd-type-guide__cards p,.tmd-type-guide__copy p,.tmd-type-guide__heading>p,.tmd-type-guide__selection-copy>p:not(.tmd-type-guide__overline),.tmd-type-guide__factors p{text-align:justify}
          .tmd-type-guide__card-number{font-size:1.1rem}
          .tmd-type-guide__factors{border-top:2px solid rgba(18,140,235,.28)}
          .tmd-type-guide__factors article{grid-template-columns:56px 1fr;border-bottom:2px solid rgba(18,140,235,.2);padding:1.8rem .75rem}
          .tmd-type-guide__factors article:nth-child(even){border-bottom-color:rgba(255,195,60,.55);background:rgba(255,195,60,.055)}
          .tmd-type-guide__factors>article>span{align-items:center;display:flex;justify-content:center;width:48px;height:48px;border-radius:12px;background:#eaf4fd;color:#128ceb;font-size:1.45rem;font-weight:900}
          .tmd-type-guide__factors article:nth-child(even)>span{background:#fff1c2;color:#805d00}
          .tmd-type-guide__factors h3{text-align:center}
          .tmd-type-guide__final--numbered{background:linear-gradient(115deg,rgba(255,195,60,.2),transparent 35%),#262e4f;color:#fff}
          .tmd-type-guide__final--numbered .tmd-type-guide__number{color:#ffc33c}
          .tmd-type-guide__final--numbered .tmd-type-guide__overline{color:#ffc33c}
          .tmd-type-guide__final--numbered h2{color:#fff}
          .tmd-type-guide__final--numbered .tmd-type-guide__final-grid>div:last-child>p{color:rgba(255,255,255,.78);text-align:justify}
          .tmd-type-guide__final--numbered .tmd-type-guide__text-link{color:#fff}
          @media(max-width:640px){.tmd-type-guide__hero h1{font-size:clamp(2rem,9.5vw,2.85rem)}.tmd-type-guide__highlights>div{min-height:0}.tmd-type-guide__factors article{grid-template-columns:48px 1fr;padding-left:0;padding-right:0}.tmd-type-guide__factors>article>span{width:42px;height:42px;font-size:1.25rem}}
        </style>
        <?php
    }

    if (is_page(275)) {
        ?>
        <style id="tmd-final-non-image-alliance-review">
          .tmd-alliance-grid--3 .tmd-alliance-card:nth-child(3n+1){background:#eef7ff;border-color:rgba(18,140,235,.25)}
          .tmd-alliance-grid--3 .tmd-alliance-card:nth-child(3n+2){background:#fff8e5;border-color:rgba(255,195,60,.48)}
          .tmd-alliance-grid--3 .tmd-alliance-card:nth-child(3n){background:#f1f3f8;border-color:rgba(38,46,79,.18)}
          .tmd-alliance-grid--3 .tmd-alliance-card:nth-child(3n+2)::before{background:#ffc33c}
          .tmd-alliance-grid--3 .tmd-alliance-card:nth-child(3n)::before{background:#262e4f}
          .tmd-alliance-principle:nth-child(4n+1){background:#eaf4fd;border-left:4px solid #128ceb}
          .tmd-alliance-principle:nth-child(4n+2){background:#fff7df;border-left:4px solid #ffc33c}
          .tmd-alliance-principle:nth-child(4n+3){background:#eef0f6;border-left:4px solid #262e4f}
          .tmd-alliance-principle:nth-child(4n){background:#f0f5f8;border-left:4px solid #5e748b}
          .tmd-alliance-faq details{border:1px solid rgba(38,46,79,.13);border-radius:10px;padding:0 18px;background:#fff}
          .tmd-alliance-faq details:nth-child(odd){background:#eef7ff;border-left:5px solid #128ceb}
          .tmd-alliance-faq details:nth-child(even){background:#fff8e5;border-left:5px solid #ffc33c}
        </style>
        <?php
    }
}, 130);
