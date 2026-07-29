<?php
/**
 * Migración idempotente de metadatos SEO y limpieza de contenido histórico.
 *
 * Ejecutar únicamente con:
 * wp --allow-root --path=/var/www/vhosts/localhost/html eval-file apply-production-seo.php
 */

defined('ABSPATH') || exit;

$pages = [
    47 => [
        'title' => 'Alquiler de Montacargas en Colombia | Tecnimontacargas',
        'description' => 'Alquiler mensual y a largo plazo de montacargas sin operador. Venta de equipos usados y mantenimiento nacional. 26 años de experiencia.',
        'keywords' => 'alquiler de montacargas en Colombia, alquiler mensual de montacargas, montacargas usados',
    ],
    49 => [
        'title' => 'Montacargas en Alquiler y Venta | Tecnimontacargas',
        'description' => 'Consulta montacargas disponibles para alquiler mensual o venta de usados en Colombia. Equipos sin operador y cotización personalizada.',
        'keywords' => 'alquiler de montacargas, venta de montacargas usados, montacargas Colombia',
    ],
    53 => [
        'title' => 'Encuentra tu Montacargas Ideal | Tecnimontacargas',
        'description' => 'Responde el quiz y encuentra el tipo de montacargas adecuado según carga, altura, pasillos y operación. Solicita asesoría en Colombia.',
        'keywords' => 'qué montacargas necesito, elegir montacargas, montacargas ideal',
    ],
    55 => [
        'title' => 'Nosotros | Tecnimontacargas',
        'description' => 'Conoce a Tecnimontacargas, empresa colombiana con 26 años de experiencia en alquiler, venta y mantenimiento de montacargas.',
        'keywords' => 'Tecnimontacargas, empresa de montacargas Colombia',
    ],
    57 => [
        'title' => 'Contacto para Montacargas en Colombia | Tecnimontacargas',
        'description' => 'Cotiza alquiler mensual, compra de montacargas usados, baterías o mantenimiento nacional. Contacta al equipo de Tecnimontacargas.',
        'keywords' => 'contacto montacargas Colombia, cotizar montacargas, Tecnimontacargas contacto',
    ],
    63 => [
        'title' => 'Baterías y Cargadores para Montacargas | Tecnimontacargas',
        'description' => 'Soluciones de energía para montacargas: baterías de plomo, cargadores industriales y monitoreo BMS. Asesoría y cobertura en Colombia.',
        'keywords' => 'baterías para montacargas, cargadores para montacargas, BMS baterías industriales',
    ],
    253 => [
        'title' => 'Baterías para Montacargas en Colombia | Tecnimontacargas',
        'description' => 'Conoce soluciones de baterías de tracción para montacargas eléctricos, criterios de selección, carga, cuidado y monitoreo en Colombia.',
        'keywords' => 'baterías para montacargas, baterías de tracción, baterías industriales Colombia',
    ],
    255 => [
        'title' => 'Cargadores para Baterías de Montacargas | Tecnimontacargas',
        'description' => 'Cargadores industriales para baterías de montacargas. Revisa compatibilidad, voltaje, capacidad y condiciones de carga con asesoría técnica.',
        'keywords' => 'cargadores para montacargas, cargador de batería industrial, cargadores baterías de tracción',
    ],
    273 => [
        'title' => 'Trabaja con Nosotros | Tecnimontacargas',
        'description' => 'Conoce oportunidades laborales en Tecnimontacargas y perfiles relacionados con servicio técnico, logística y manejo de materiales.',
        'keywords' => 'trabaja en Tecnimontacargas, empleo montacargas Colombia',
    ],
    275 => [
        'title' => 'Alianzas Empresariales | Tecnimontacargas',
        'description' => 'Conoce las alianzas de Tecnimontacargas para fortalecer soluciones de equipos, energía y servicio técnico para empresas en Colombia.',
        'keywords' => 'alianzas Tecnimontacargas, aliados montacargas Colombia',
    ],
    278 => [
        'title' => 'Empresa de Montacargas en Colombia | Tecnimontacargas',
        'description' => 'Somos Tecnimontacargas: 26 años en alquiler de largo plazo, venta de equipos usados y mantenimiento de montacargas en Colombia.',
        'keywords' => 'empresa de montacargas Colombia, Tecnimontacargas, alquiler de montacargas',
    ],
    281 => [
        'title' => 'Blog de Montacargas y Baterías | Tecnimontacargas',
        'description' => 'Guías sobre selección, operación, baterías y mantenimiento de montacargas para empresas, bodegas y centros logísticos.',
        'keywords' => 'blog de montacargas, guías de montacargas, mantenimiento de montacargas',
    ],
    284 => [
        'title' => 'PQR | Tecnimontacargas',
        'description' => 'Canal para peticiones, quejas y reclamos relacionados con la atención y los servicios de Tecnimontacargas.',
        'keywords' => 'PQR Tecnimontacargas',
    ],
    288 => [
        'title' => 'Mantenimiento Preventivo de Montacargas | Tecnimontacargas',
        'description' => 'Planes de mantenimiento preventivo para montacargas en Colombia. Inspección, ajustes y seguimiento para reducir fallas y paradas.',
        'keywords' => 'mantenimiento preventivo de montacargas, plan de mantenimiento montacargas, mantenimiento nacional',
    ],
    290 => [
        'title' => 'Mantenimiento Correctivo de Montacargas | Tecnimontacargas',
        'description' => 'Diagnóstico y reparación de fallas eléctricas, hidráulicas y mecánicas en montacargas. Servicio técnico con cobertura nacional.',
        'keywords' => 'mantenimiento correctivo de montacargas, reparación de montacargas, servicio técnico montacargas',
    ],
    349 => [
        'title' => 'Tipos de Montacargas y Equipos de Bodega | Guía',
        'description' => 'Compara estibadores, apiladores, reach trucks, tomapedidos y montacargas contrabalanceados según espacio, carga y operación.',
        'keywords' => 'tipos de montacargas, equipos para bodega, equipos de manejo de materiales',
    ],
    350 => [
        'title' => 'Montacargas Contrabalanceados: Tipos y Usos | Guía',
        'description' => 'Conoce cómo funcionan los montacargas contrabalanceados, sus aplicaciones y diferencias entre modelos eléctricos de 3 y 4 ruedas.',
        'keywords' => 'montacargas contrabalanceados, tipos de montacargas contrabalanceados, montacargas eléctricos',
    ],
    351 => [
        'title' => 'Montacargas Retráctil de Mástil Móvil | Guía',
        'description' => 'Conoce el funcionamiento, ventajas y usos del montacargas retráctil de mástil móvil para pasillos angostos y almacenamiento en altura.',
        'keywords' => 'montacargas retráctil de mástil móvil, reach truck, montacargas para pasillos angostos',
    ],
    352 => [
        'title' => 'Apiladores Eléctricos: Usos y Selección | Guía',
        'description' => 'Conoce qué es un apilador eléctrico, cómo traslada y eleva estibas y qué revisar según altura, capacidad y espacio de maniobra.',
        'keywords' => 'apiladores eléctricos, apilador eléctrico para bodega, equipo para elevar estibas',
    ],
    355 => [
        'title' => 'Estibadores Manuales: Qué Son y Cómo Elegirlos',
        'description' => 'Guía de estibadores manuales hidráulicos para mover estibas a nivel de piso: usos, ventajas, límites y criterios de selección.',
        'keywords' => 'estibadores manuales, estibador hidráulico, transpaleta manual',
    ],
    357 => [
        'title' => 'Información Legal | Tecnimontacargas',
        'description' => 'Consulta políticas corporativas, privacidad, calidad, SG-SST y canales de atención de Tecnimontacargas.',
        'keywords' => 'información legal Tecnimontacargas',
    ],
    358 => [
        'title' => 'Política de Privacidad | Tecnimontacargas',
        'description' => 'Consulta cómo Tecnimontacargas trata y protege los datos personales recopilados mediante su sitio web y canales de contacto.',
        'keywords' => 'política de privacidad Tecnimontacargas',
    ],
    359 => [
        'title' => 'Política de SG-SST | Tecnimontacargas',
        'description' => 'Consulta la política del Sistema de Gestión de Seguridad y Salud en el Trabajo de Tecnimontacargas.',
        'keywords' => 'política SG-SST Tecnimontacargas',
    ],
    360 => [
        'title' => 'Política de Calidad | Tecnimontacargas',
        'description' => 'Consulta los compromisos y lineamientos de calidad de Tecnimontacargas para sus servicios y atención empresarial.',
        'keywords' => 'política de calidad Tecnimontacargas',
    ],
    401 => [
        'title' => 'Baterías de Plomo para Montacargas | Tecnimontacargas',
        'description' => 'Baterías de plomo-ácido para montacargas eléctricos: funcionamiento, carga, mantenimiento y criterios de selección.',
        'keywords' => 'baterías de plomo para montacargas, batería plomo ácido industrial, batería de tracción',
    ],
    506 => [
        'title' => 'Mantenimiento de Montacargas en Colombia | Tecnimontacargas',
        'description' => 'Mantenimiento preventivo y correctivo para montacargas de distintas marcas. Diagnóstico técnico y cobertura nacional.',
        'keywords' => 'mantenimiento de montacargas en Colombia, servicio técnico de montacargas, reparación de montacargas',
    ],
    792 => [
        'title' => 'BMS para Baterías de Montacargas | Tecnimontacargas',
        'description' => 'Monitorea uso, carga, temperatura y rendimiento de baterías industriales con BMS para orientar diagnósticos y mantenimiento.',
        'keywords' => 'BMS para baterías de montacargas, monitoreo de baterías industriales, diagnóstico BMS',
    ],
    793 => [
        'title' => 'Quiero ser Proveedor de Tecnimontacargas',
        'description' => 'Presenta tu empresa, portafolio y cobertura para explorar oportunidades como proveedor o aliado de Tecnimontacargas.',
        'keywords' => 'proveedor Tecnimontacargas, alianzas empresariales Colombia',
    ],
    818 => [
        'title' => 'Estibadores y Apiladores: Tipos y Diferencias | Guía',
        'description' => 'Compara estibadores manuales, eléctricos y apiladores según traslado, elevación, recorridos y operación en bodega.',
        'keywords' => 'estibadores y apiladores, tipos de estibadores, diferencia estibador y apilador',
    ],
    820 => [
        'title' => 'Estibadores Eléctricos: Usos y Ventajas | Guía',
        'description' => 'Conoce cómo funcionan los estibadores eléctricos, sus usos en recorridos frecuentes y diferencias frente a modelos manuales.',
        'keywords' => 'estibadores eléctricos, estibador eléctrico para bodega, transpaleta eléctrica',
    ],
    822 => [
        'title' => 'Reach Truck y Montacargas Retráctiles | Guía',
        'description' => 'Guía de reach trucks y montacargas retráctiles para pasillos angostos: mecanismos, aplicaciones y criterios de selección.',
        'keywords' => 'reach truck, montacargas retráctiles, montacargas para pasillos angostos',
    ],
    824 => [
        'title' => 'Montacargas con Pantógrafo Sencillo | Guía',
        'description' => 'Conoce cómo opera un montacargas con pantógrafo sencillo y cuándo elegirlo para estanterías de profundidad simple.',
        'keywords' => 'pantógrafo sencillo, montacargas con pantógrafo, montacargas de pasillo angosto',
    ],
    825 => [
        'title' => 'Montacargas de Doble Profundidad | Guía',
        'description' => 'Conoce el pantógrafo de doble profundidad para acceder a dos posiciones de estiba y aumentar la densidad de almacenamiento.',
        'keywords' => 'montacargas doble profundidad, pantógrafo doble profundidad, reach double deep',
    ],
    826 => [
        'title' => 'Tomapedidos: Equipos para Picking en Bodega | Guía',
        'description' => 'Conoce los tomapedidos u order pickers para preparación de pedidos, sus aplicaciones y criterios de selección.',
        'keywords' => 'tomapedidos, order picker, equipos para picking',
    ],
    827 => [
        'title' => 'Tomapedidos de Alto Nivel | Funcionamiento y Usos',
        'description' => 'Guía de tomapedidos de alto nivel para picking en altura: aplicaciones, seguridad, carga y condiciones de operación.',
        'keywords' => 'tomapedidos de alto nivel, order picker de alto nivel, picking en altura',
    ],
    829 => [
        'title' => 'Montacargas Eléctricos de 3 Ruedas | Guía',
        'description' => 'Conoce las ventajas del montacargas eléctrico de 3 ruedas para giros cerrados, interiores y espacios reducidos.',
        'keywords' => 'montacargas eléctrico de 3 ruedas, montacargas para espacios reducidos',
    ],
    830 => [
        'title' => 'Montacargas Eléctricos de 4 Ruedas | Guía',
        'description' => 'Conoce cuándo elegir un montacargas eléctrico de 4 ruedas por estabilidad, capacidad y operación continua.',
        'keywords' => 'montacargas eléctrico de 4 ruedas, montacargas eléctrico industrial',
    ],
];

$posts = [
    798 => [
        'title' => 'Cómo Elegir el Montacargas Adecuado para tu Operación',
        'description' => 'Aprende a elegir un montacargas según capacidad, altura, pasillos, superficie y jornada de trabajo.',
        'keywords' => 'cómo elegir un montacargas, qué montacargas necesito, capacidad de montacargas',
    ],
    799 => [
        'title' => '7 Señales de que tu Montacargas Necesita Mantenimiento',
        'description' => 'Identifica ruidos, fugas, vibraciones y pérdida de rendimiento antes de que una falla detenga tu operación.',
        'keywords' => 'señales mantenimiento montacargas, fallas de montacargas, reparación de montacargas',
    ],
    800 => [
        'title' => 'Cómo Prolongar la Vida de una Batería de Montacargas',
        'description' => 'Buenas prácticas de carga, limpieza e inspección para cuidar baterías de plomo-ácido de montacargas.',
        'keywords' => 'cuidar batería de montacargas, vida útil batería de tracción, carga batería industrial',
    ],
    801 => [
        'title' => 'Soluciones Integrales para Operaciones con Montacargas',
        'description' => 'Alquiler, equipos usados, baterías y mantenimiento coordinados para mantener operaciones de manejo de carga en movimiento.',
        'keywords' => 'soluciones para montacargas, alquiler y mantenimiento de montacargas',
    ],
    802 => [
        'title' => 'Cómo Preparar una Inspección Técnica de Montacargas',
        'description' => 'Lista práctica para preparar equipos, historial y condiciones de operación antes de una inspección técnica de flota.',
        'keywords' => 'inspección técnica de montacargas, checklist de montacargas, mantenimiento de flotas',
    ],
    803 => [
        'title' => 'Qué Aporta un BMS a las Baterías de Montacargas',
        'description' => 'Conoce cómo un BMS aporta monitoreo, trazabilidad y datos para gestionar baterías industriales y su mantenimiento.',
        'keywords' => 'BMS baterías de montacargas, monitoreo de baterías industriales, gestión de baterías',
    ],
];

$meta_keys = [
    'title' => 'rank_math_title',
    'description' => 'rank_math_description',
    'keywords' => 'rank_math_focus_keyword',
];

foreach ([$pages, $posts] as $content_group) {
    foreach ($content_group as $post_id => $metadata) {
        if (!get_post($post_id)) {
            WP_CLI::warning("No existe post {$post_id}; se omite.");
            continue;
        }

        foreach ($meta_keys as $source => $meta_key) {
            update_post_meta($post_id, $meta_key, $metadata[$source]);
        }

        update_post_meta($post_id, 'rank_math_facebook_title', $metadata['title']);
        update_post_meta($post_id, 'rank_math_facebook_description', $metadata['description']);
        update_post_meta($post_id, 'rank_math_twitter_title', $metadata['title']);
        update_post_meta($post_id, 'rank_math_twitter_description', $metadata['description']);
    }
}

$noindex_ids = [10, 11, 55, 273, 284, 357, 463, 464];
foreach ($noindex_ids as $post_id) {
    if (get_post($post_id)) {
        update_post_meta($post_id, 'rank_math_robots', ['noindex', 'follow', 'noarchive']);
    }
}

$rank_math_titles = get_option('rank-math-options-titles', []);
$rank_math_titles['knowledgegraph_name'] = 'Tecnimontacargas';
$rank_math_titles['website_name'] = 'Tecnimontacargas';
$rank_math_titles['website_alternate_name'] = 'TMD';
$rank_math_titles['pt_page_default_rich_snippet'] = 'article';
$rank_math_titles['pt_page_slack_enhanced_sharing'] = 'off';
$rank_math_titles['tax_category_custom_robots'] = 'on';
$rank_math_titles['tax_category_robots'] = ['noindex'];
$rank_math_titles['pt_product_custom_robots'] = 'on';
$rank_math_titles['pt_product_robots'] = ['noindex'];
$rank_math_titles['pt_tmd_equipo_custom_robots'] = 'on';
$rank_math_titles['pt_tmd_equipo_robots'] = ['noindex'];
$rank_math_titles['pt_tmd_equipo_default_rich_snippet'] = 'off';
$rank_math_titles['pt_tmd_energia_custom_robots'] = 'on';
$rank_math_titles['pt_tmd_energia_robots'] = ['noindex'];
$rank_math_titles['pt_tmd_energia_default_rich_snippet'] = 'off';
$rank_math_titles['opening_hours'] = [
    ['day' => 'Monday', 'time' => '07:00-17:00'],
    ['day' => 'Tuesday', 'time' => '07:00-17:00'],
    ['day' => 'Wednesday', 'time' => '07:00-17:00'],
    ['day' => 'Thursday', 'time' => '07:00-17:00'],
    ['day' => 'Friday', 'time' => '07:00-17:00'],
];
update_option('rank-math-options-titles', $rank_math_titles, false);

update_option('blogname', 'Tecnimontacargas');
update_option('blogdescription', 'Alquiler y venta de montacargas usados en Colombia');
update_option('woocommerce_enable_myaccount_registration', 'yes');
update_option('woocommerce_enable_signup_and_login_from_checkout', 'no');
update_option('woocommerce_enable_guest_checkout', 'no');

$legacy_posts = [422, 423, 424, 425, 426, 427, 429, 430];
foreach ($legacy_posts as $post_id) {
    if (get_post($post_id)) {
        wp_delete_post($post_id, true);
    }
}

$obsolete_pages = [264, 509, 512];
foreach ($obsolete_pages as $post_id) {
    if (get_post($post_id)) {
        wp_trash_post($post_id);
    }
}

if (class_exists('\RankMath\Sitemap\Cache')) {
    \RankMath\Sitemap\Cache::invalidate_storage();
}

WP_CLI::success(
    sprintf(
        'SEO actualizado: %d páginas, %d artículos, %d fichas históricas eliminadas y %d páginas obsoletas enviadas a papelera.',
        count($pages),
        count($posts),
        count($legacy_posts),
        count($obsolete_pages)
    )
);
