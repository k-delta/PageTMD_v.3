<?php
/**
 * Ajustes finales solicitados en "Sugerencias Página web 3 - Catalina Finales".
 *
 * Se aplica después del renderer de guías para mantener el contenido base
 * centralizado y hacer esta revisión fácilmente reversible.
 */
defined('ABSPATH') || exit;

add_filter('the_content', static function (string $content): string {
    if (!function_exists('tmd_equipment_type_guide_current')) {
        return $content;
    }

    $guide = tmd_equipment_type_guide_current();
    if (!$guide || empty($guide['slug'])) {
        return $content;
    }

    $slug = (string) $guide['slug'];
    $maps = [
        'estibadores-manuales' => [
            'Equipos básicos utilizados para levantar ligeramente una estiba y trasladarla a nivel del suelo.' => 'Equipos diseñados para elevar ligeramente cargas sobre estibas y facilitar su traslado horizontal de forma segura y eficiente en bodegas, centros de distribución y áreas logísticas.',
            'Funcionan mediante un sistema hidráulico accionado manualmente por el operador. No requieren baterías, combustible ni sistemas eléctricos, lo que simplifica su mantenimiento y permite utilizarlos en diferentes áreas de trabajo.' => 'Los estibadores manuales son equipos diseñados para elevar ligeramente y trasladar cargas sobre estibas en distancias cortas. Funcionan mediante un sistema hidráulico accionado manualmente por el operador y no requieren baterías, combustible ni alimentación eléctrica, lo que facilita su operación y mantenimiento.',
            'Son adecuados para recorridos cortos, operaciones de baja o media intensidad y espacios donde el volumen de movimiento de mercancía no justifica el uso de un equipo motorizado.' => 'Son ideales para operaciones de baja o media intensidad en bodegas, centros de distribución y áreas logísticas donde se requiere un manejo práctico y eficiente de mercancías.',
            'Mantenimiento simple' => 'Mantenimiento sencillo',
            'Uso en diferentes áreas' => 'Versatilidad de uso',
            'Traslado de estibas dentro de una bodega.' => 'Traslado y ubicación de estibas entre diferentes zonas de almacenamiento.',
            'Organización de mercancía en zonas de despacho.' => 'Organización y movilización de mercancía en áreas de preparación y despacho.',
            'Movimiento de productos entre áreas y vehículos.' => 'Apoyo en la movilización de mercancías durante procesos de recepción y despacho de vehículos.',
            'Abastecimiento de líneas de producción.' => 'Suministro de materiales e insumos a líneas y áreas de producción.',
            'Traslado de productos en recorridos cortos y nivelados.' => 'Manejo de cargas en trayectos cortos sobre pisos firmes, uniformes y nivelados.',
            'Todo el desplazamiento depende del esfuerzo físico del operador.' => 'El desplazamiento y la maniobra se realizan manualmente, por lo que es importante considerar el peso de la carga y la frecuencia de uso.',
            'No son la mejor alternativa para recorridos largos ni jornadas con movimientos repetitivos.' => 'Son recomendados para trayectos cortos y operaciones de baja o media intensidad. Para recorridos largos o movimientos continuos, puede ser más conveniente evaluar una alternativa eléctrica.',
            'No son la opción indicada para pendientes; su mejor entorno son superficies planas.' => 'Su mejor desempeño se obtiene sobre superficies firmes, uniformes y niveladas. Para operaciones con pendientes o condiciones especiales de piso, se debe evaluar otro tipo de equipo.',
        ],
        'estibadores-electricos' => [
            'Cumplen la función de un estibador manual e incorporan tracción eléctrica para facilitar el desplazamiento de la carga.' => 'Equipos diseñados para elevar ligeramente y trasladar cargas sobre estibas, incorporando tracción eléctrica para facilitar el desplazamiento y reducir el esfuerzo físico del operador.',
            'Están diseñados para operaciones con recorridos más largos, mayor frecuencia de movimientos o cargas que resultan difíciles de transportar manualmente.' => 'Los estibadores eléctricos son equipos de manejo de materiales que incorporan tracción eléctrica para facilitar el traslado de cargas sobre estibas y reducir el esfuerzo físico del operador. Son adecuados para operaciones con mayor frecuencia de movimientos o recorridos más prolongados, donde el desplazamiento manual puede resultar menos eficiente.',
            'Dependiendo de su configuración, el operador puede caminar junto al equipo, utilizar una plataforma abatible o conducir desde una posición integrada.' => 'Según su configuración, el operador puede acompañar el equipo a pie, utilizar una plataforma abatible o conducir desde una posición integrada.',
            'Ritmo de trabajo constante' => 'Mayor eficiencia operativa',
            'Distintas posiciones de operación' => 'Diferentes configuraciones de operación',
            'Movimiento continuo entre recibo, almacenamiento y despacho.' => 'Traslado de estibas entre áreas de recibo, almacenamiento, preparación de pedidos y despacho.',
            'Transferencia frecuente de estibas en zonas de carga.' => 'Movilización de estibas durante los procesos de recepción y despacho de mercancías.',
            'Transporte interno de materiales y producto terminado.' => 'Abastecimiento de materiales y traslado de producto terminado entre diferentes áreas de operación.',
            'Movimiento de mercancía en áreas comerciales amplias.' => 'Movilización de mercancías entre zonas de recibo, almacenamiento y abastecimiento interno.',
            'Transporte interno y abastecimiento de zonas de picking.' => 'Traslado de mercancías y abastecimiento de áreas destinadas a la preparación de pedidos y picking.',
            'Evalúa la distancia total de desplazamiento de cada ciclo.' => 'Evalúa la longitud y frecuencia de los desplazamientos, así como las condiciones del trayecto.',
            'Verifica el peso real que debe transportar el equipo.' => 'Verifica el peso máximo que deberá transportar el equipo para seleccionar la capacidad adecuada.',
            'Considera las horas de trabajo y la frecuencia de movimientos.' => 'Considera las horas de trabajo, el número de movimientos y la intensidad de la operación durante la jornada.',
            'Selecciona la tecnología y autonomía adecuadas para la operación.' => 'Evalúa la tecnología de batería y la autonomía requerida de acuerdo con la jornada y la intensidad de uso.',
        ],
        'apiladores-electricos' => [
            'Combinan el transporte horizontal de mercancía con la capacidad de elevar estibas.' => 'Equipos diseñados para trasladar y elevar cargas sobre estibas, facilitando su almacenamiento y manipulación a diferentes alturas de forma eficiente y segura.',
            'Se utilizan para organizar productos en estanterías, alimentar líneas de producción, apilar mercancía y realizar almacenamiento a alturas bajas o medianas.' => 'Los apiladores eléctricos están diseñados para trasladar, elevar y posicionar cargas sobre estibas, facilitando tareas de almacenamiento, apilamiento y abastecimiento de mercancías.',
            'Por su diseño compacto, pueden trabajar en espacios donde un montacargas convencional tendría dificultades para maniobrar. Son una solución intermedia entre un estibador eléctrico y un montacargas.' => 'Gracias a su diseño compacto y sistema de elevación, pueden operar en espacios reducidos y pasillos donde se requiere mayor maniobrabilidad para ubicar cargas a diferentes alturas. Son una solución eficiente para operaciones que necesitan combinar desplazamiento horizontal y almacenamiento en altura.',
            'Transporte y elevación' => 'Transporte y elevación',
            'Alturas bajas y medianas' => 'Almacenamiento en altura',
            'Apilamiento y organización de mercancía.' => 'Elevación y posicionamiento de estibas para organizar y aprovechar eficientemente las áreas de almacenamiento.',
            'Almacenamiento de productos en niveles bajos o medianos.' => 'Ubicación y retiro de cargas en diferentes niveles de estantería, según la altura y capacidad del equipo.',
            'Manipulación de estibas en zonas de recibo y despacho.' => 'Manipulación y posicionamiento de estibas en zonas destinadas al recibo y despacho de mercancías.',
            'Abastecimiento de áreas y líneas de producción.' => 'Traslado y suministro de materiales e insumos a diferentes áreas y líneas de producción.',
            'Organización de productos en espacios compactos.' => 'Manejo y organización de mercancías en áreas donde el espacio disponible requiere equipos de dimensiones reducidas.',
            'Manipulación de cargas donde la maniobra es limitada.' => 'Maniobra y posicionamiento de cargas en pasillos con espacio limitado, de acuerdo con el radio de giro y las dimensiones del equipo.',
            'Verifica la altura máxima a la que debe ubicarse la carga.' => 'Define la altura máxima a la que deberá posicionarse la carga, considerando los niveles de almacenamiento y la configuración de la estantería.',
            'Confirma la capacidad disponible del equipo a la altura de trabajo.' => 'Confirma la capacidad que el equipo puede manejar de forma segura a la altura de trabajo requerida.',
            'Mide el espacio real de circulación y maniobra con la estiba.' => 'Verifica el ancho de los pasillos, accesos y zonas de trabajo considerando las dimensiones de la carga y el espacio requerido para maniobrar.',
        ],
        'retractiles-de-mastil-movil' => [
            'Equipos reach cuyo mecanismo desplaza el mástil o las horquillas hacia adelante para tomar la carga y luego retraerla.' => 'Equipos diseñados para el manejo y almacenamiento de cargas en altura, especialmente en pasillos reducidos. Su sistema retráctil permite extender el mástil hacia la carga y retraerlo durante el desplazamiento, favoreciendo una operación compacta y eficiente.',
            'El movimiento retráctil ayuda a mantener la carga dentro de una posición más estable durante el desplazamiento.' => 'Los montacargas retráctiles están diseñados para manipular, elevar y almacenar cargas en altura, especialmente en bodegas con pasillos reducidos y sistemas de estantería. Su mecanismo retráctil permite extender el mástil para tomar o posicionar la carga y retraerlo para acercarla al cuerpo del equipo durante el desplazamiento.',
            'Los modelos ETV, ESC y ETVC corresponden a diferentes configuraciones de este tipo de montacargas. La selección no debe realizarse únicamente por el modelo.' => 'Esta configuración favorece la maniobrabilidad y el aprovechamiento del espacio, especialmente en operaciones que requieren almacenamiento vertical.',
            'Estabilidad de la carga' => 'Maniobrabilidad en espacios reducidos',
            'Maniobra en espacios reducidos entre estanterías.' => 'Facilitan la manipulación y posicionamiento de cargas en pasillos donde el espacio de maniobra es limitado.',
            'Ubicación y retiro de cargas en niveles elevados.' => 'Permiten ubicar y retirar cargas en diferentes niveles de estantería, según la capacidad y configuración del equipo.',
            'Operaciones con alta frecuencia de movimientos.' => 'Favorecen el aprovechamiento del espacio vertical y la distribución eficiente de las áreas de almacenamiento.',
            'Trabajo interior sobre pisos adecuados para precisión en altura.' => 'Adecuados para trabajar en bodegas y centros logísticos sobre superficies firmes, uniformes y niveladas.',
            'Centros de distribución con entradas y salidas frecuentes.' => 'Facilitan el manejo frecuente de mercancías en operaciones con flujos constantes de almacenamiento, preparación y despacho.',
            'Define el nivel máximo de almacenamiento requerido.' => 'Define la altura máxima de almacenamiento y los niveles de estantería donde deberá posicionarse la carga.',
            'Valida la capacidad necesaria a la altura real de trabajo.' => 'Confirma la capacidad que el equipo puede manejar de forma segura a la altura máxima de trabajo requerida.',
            'Mide el espacio útil con la carga y las holguras de seguridad.' => 'Mide el espacio útil entre estanterías y valida el área necesaria para maniobrar con las dimensiones reales de la carga.',
            'Comprueba compatibilidad entre equipo, estiba y rack.' => 'Verifica la compatibilidad entre el equipo, las dimensiones de la estiba, la configuración del rack y los niveles de almacenamiento.',
        ],
        'pantografo-sencillo' => [
            'Incorporan un mecanismo extensible similar a una tijera para introducir o retirar las horquillas dentro de la estantería.' => 'Equipos diseñados para el manejo y almacenamiento de cargas en altura. Incorporan un mecanismo de pantógrafo que permite extender y retraer las horquillas para tomar o posicionar cargas en estanterías, sin necesidad de desplazar el mástil hacia adelante.',
            'El mecanismo permite acceder a la carga sin desplazar completamente el equipo y resulta útil cuando se busca aprovechar la profundidad de almacenamiento.' => 'Los montacargas con pantógrafo sencillo están diseñados para manipular y almacenar cargas en altura mediante un mecanismo que extiende y retrae las horquillas hacia la posición de la carga.',
            'El pantógrafo sencillo se utiliza en estanterías de profundidad simple. Permite tomar o depositar una estiba ubicada directamente frente al pasillo.' => 'Este sistema permite tomar o depositar estibas ubicadas en estanterías de profundidad simple, facilitando el acceso a la carga sin necesidad de desplazar el mástil hacia adelante. Su configuración combina alcance horizontal, precisión de posicionamiento y maniobrabilidad para operaciones de almacenamiento en estanterías.',
            'Acceso directo' => 'Alcance horizontal',
            'Mecanismo extensible' => 'Posicionamiento en estanterías',
            'La configuración facilita el acceso individual a cada posición de almacenamiento.' => 'Verifica el peso máximo y las dimensiones de las estibas para determinar la capacidad requerida del equipo.',
            'Profundidad del rack' => 'Altura de elevación',
            'Debe corresponder a una posición ubicada directamente frente al pasillo.' => 'Define la altura máxima a la que deberá ubicarse la carga y los niveles de estantería que se deben alcanzar.',
            'Operación' => 'Profundidad de la estantería',
            'La extensión de horquillas reduce la necesidad de desplazar completamente el equipo.' => 'Verifica que la configuración corresponda a posiciones de profundidad simple y que el alcance del pantógrafo sea compatible con el rack.',
        ],
        'pantografo-doble-profundidad' => [
            'Su mecanismo extensible permite alcanzar una segunda posición de almacenamiento ubicada detrás de la primera estiba.' => 'Equipos diseñados para operaciones de almacenamiento de alta densidad. Su mecanismo de pantógrafo permite extender las horquillas para acceder a una segunda posición de carga ubicada detrás de la primera dentro de una configuración de estantería de doble profundidad.',
            'Esta configuración permite introducir o retirar las horquillas dentro de la estantería sin desplazar completamente el equipo.' => 'Los montacargas con pantógrafo de doble profundidad incorporan un mecanismo extensible que permite alcanzar cargas ubicadas en una segunda posición dentro de la estantería.',
            'Su principal ventaja es el aumento de la densidad de almacenamiento.' => 'Esta configuración permite almacenar dos estibas en profundidad, aumentando la densidad de almacenamiento y aprovechando mejor el espacio disponible en la bodega. Por sus características, son especialmente útiles en operaciones donde se busca maximizar la capacidad de almacenamiento y existe una adecuada planificación de la ubicación y rotación del inventario.',
            'Mayor densidad' => 'Mayor densidad de almacenamiento',
            'La primera estiba puede limitar el acceso inmediato a la posición posterior.' => 'Verifica el peso máximo y las dimensiones de las estibas para determinar la capacidad y configuración requeridas.',
            'Organización del inventario' => 'Configuración de la estantería',
            'Requiere definir correctamente dónde se almacena cada referencia.' => 'Verifica que el rack esté diseñado para almacenamiento de doble profundidad y sea compatible con las dimensiones de las cargas y el alcance del equipo.',
            'La planeación debe considerar qué mercancía necesita acceso más frecuente.' => 'Planifica la ubicación de las mercancías según su frecuencia de entrada y salida para facilitar el acceso y evitar movimientos innecesarios.',
        ],
        'tomapedidos-de-alto-nivel' => [
            'Se utilizan en operaciones de picking donde los productos deben recogerse manualmente desde diferentes niveles de la estantería.' => 'Equipos diseñados para operaciones de picking en altura, que permiten al operador acceder a diferentes niveles de la estantería para seleccionar y recoger productos de forma eficiente y segura.',
            'El operador se eleva con la plataforma para acceder directamente a unidades o cajas almacenadas en niveles elevados.' => 'Los tomapedidos de alto nivel están diseñados para operaciones de picking en las que el operador necesita acceder directamente a productos almacenados en diferentes niveles de la estantería. La plataforma del operador se eleva hasta la altura de trabajo requerida, facilitando la selección de unidades, cajas o referencias para la preparación de pedidos.',
            'Acceso por unidad o caja' => 'Acceso directo a productos',
            'Preparación directa' => 'Preparación de pedidos',
            'Preparación de pedidos en operaciones logísticas.' => 'Preparación de pedidos y recolección de productos en operaciones con múltiples ubicaciones de almacenamiento.',
            'Acceso a gran variedad de referencias.' => 'Acceso y selección de piezas, cajas y referencias almacenadas en diferentes niveles de estantería.',
            'Recolección de productos para pedidos individuales.' => 'Recolección de productos para la preparación de pedidos con múltiples referencias y unidades.',
            'Almacenes con numerosas referencias y ubicaciones.' => 'Facilitan el acceso a inventarios con numerosas referencias distribuidas en diferentes ubicaciones y niveles.',
            'Preparación sin movilizar necesariamente una estiba completa.' => 'Permiten seleccionar productos directamente desde la estantería sin necesidad de movilizar una estiba completa.',
            'Acceso a inventario almacenado en altura.' => 'Facilitan el acceso del operador a productos almacenados en altura para su selección y preparación.',
            'Requieren sistemas de seguridad adecuados para el trabajo en altura.' => 'Evalúa los sistemas y elementos de seguridad requeridos para el trabajo en altura, de acuerdo con la configuración del equipo y las condiciones de la operación.',
            'El operador debe estar formado para utilizar el equipo correctamente.' => 'Considera las horas de trabajo y la intensidad de las operaciones de picking para determinar la autonomía requerida.',
            'Las condiciones de circulación deben ser apropiadas para una operación estable.' => 'Verifica que las superficies de circulación sean firmes, uniformes y adecuadas para una operación estable y segura.',
        ],
        'electricos-de-3-ruedas' => [
            'Ofrecen un radio de giro reducido y una alta capacidad de maniobra.' => 'Equipos diseñados para el traslado, elevación y manipulación de cargas, con una configuración de tres ruedas que favorece un radio de giro reducido y una mayor maniobrabilidad en espacios limitados.',
            'Son apropiados para instalaciones con espacios limitados, pasillos estrechos y operaciones principalmente interiores.' => 'Los montacargas contrabalanceados eléctricos de tres ruedas están diseñados para trasladar, elevar y posicionar cargas en operaciones que requieren alta maniobrabilidad y precisión. Su configuración de tres ruedas permite realizar giros en espacios más reducidos, lo que los hace adecuados para bodegas, pasillos estrechos y áreas con espacio limitado de maniobra. Su sistema de propulsión eléctrica favorece su uso en operaciones interiores donde se requiere un manejo eficiente de mercancías.',
            'Movimiento de mercancía en espacios interiores.' => 'Traslado, elevación y posicionamiento de cargas en áreas interiores de almacenamiento.',
            'Manipulación de mercancía entre áreas y vehículos.' => 'Manipulación de cargas en zonas de recibo, preparación y despacho de mercancías.',
            'Abastecimiento de materiales a zonas de trabajo.' => 'Abastecimiento de materiales e insumos y traslado de producto terminado entre diferentes áreas de trabajo.',
            'Movimiento de cargas en establecimientos y grandes superficies.' => 'Manejo de mercancías entre zonas de almacenamiento, preparación de pedidos y despacho.',
            'Operaciones donde la maniobrabilidad es prioritaria.' => 'Operaciones donde un radio de giro reducido facilita el posicionamiento y manejo de cargas.',
            'Deben utilizarse en superficies estables.' => 'Verifica que las superficies de circulación sean firmes, uniformes y adecuadas para una operación estable y segura.',
            'Capacidad, altura y entorno deben mantenerse dentro de lo definido para cada equipo.' => 'Evalúa el peso y las dimensiones de la carga, la altura de elevación, el espacio disponible y la intensidad de la operación para seleccionar la configuración adecuada.',
        ],
    ];

    if (isset($maps[$slug])) {
        $content = strtr($content, $maps[$slug]);
    }

    $images = [
        'estibadores-manuales' => 'estibador-manual.webp',
        'estibadores-electricos' => 'estibador-electrico.webp',
        'apiladores-electricos' => 'apilador-electrico.webp',
        'retractiles-de-mastil-movil' => 'retractil-mastil-movil.webp',
        'pantografo-sencillo' => 'pantografo-sencillo.webp',
    ];
    if (isset($images[$slug])) {
        $src = esc_url(get_stylesheet_directory_uri() . '/assets/img/equipment-guides/' . $images[$slug]);
        $content = preg_replace('/(<img class="tmd-type-guide__image" src=")[^"]+("[^>]*>)/', '$1' . $src . '$2', $content, 1) ?: $content;
    }

    $content = str_replace('<h2>Qué debes validar antes de elegir</h2>', '<h2>¿Qué debes validar antes de elegir?</h2>', $content);
    $content = preg_replace('/(<div class="tmd-type-guide__highlights">\s*<div><span>)0([1-9])(<\/span>)/', '$1$2$3', $content) ?: $content;
    $content = preg_replace('/(<div><span>)0([1-9])(<\/span><strong>)/', '$1$2$3', $content) ?: $content;
    $content = preg_replace('/(<article>\s*<span>)0([1-9])(<\/span>)/', '$1$2$3', $content) ?: $content;

    return $content;
}, 100);

add_action('wp_head', static function (): void {
    if (!function_exists('tmd_equipment_type_guide_current') || !tmd_equipment_type_guide_current()) {
        return;
    }
    ?>
    <style id="tmd-final-guide-review">
      .tmd-type-guide__hero h1{font-size:clamp(2.35rem,4.2vw,4.25rem);line-height:1.02}
      .tmd-type-guide__number{color:#128ceb}
      .tmd-type-guide__copy p,.tmd-type-guide__heading>p,.tmd-type-guide__cards p,.tmd-type-guide__factors p{text-align:justify}
      .tmd-type-guide__highlights>div,.tmd-type-guide__cards article{background:#eaf4fd;border:1px solid rgba(18,140,235,.22);border-top:4px solid #128ceb;border-radius:10px}
      .tmd-type-guide__highlights>div:nth-child(2),.tmd-type-guide__cards article:nth-child(3n+2){background:#fff7df;border-top-color:#ffc33c}
      .tmd-type-guide__highlights>div:nth-child(3),.tmd-type-guide__cards article:nth-child(3n){background:#f4f7fb;border-top-color:#262e4f}
      .tmd-type-guide__highlights>div{padding:1.35rem;text-align:center}
      .tmd-type-guide__highlights span,.tmd-type-guide__card-number,.tmd-type-guide__factors>article>span{color:#128ceb;font-size:1.1rem;font-weight:800}
      .tmd-type-guide__cards h3{text-align:center}
      .tmd-type-guide__section--soft{background:linear-gradient(120deg,rgba(18,140,235,.08),transparent 34%),#f4f7fb}
      .tmd-type-guide__factors article{border-bottom-color:#c8d9e8}
      .tmd-type-guide__factors article:nth-child(even){border-bottom-color:rgba(255,195,60,.65)}
      .tmd-type-guide__image{height:88%;max-height:470px;max-width:88%;object-fit:contain;width:88%}
      @media(max-width:640px){.tmd-type-guide__hero h1{font-size:clamp(2.1rem,10vw,3rem)}}
    </style>
    <?php
}, 99);
