<?php

/** Focused, read-only visual contract checks for the battery landing page. */

define('ABSPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('WP_CLI', true);

class WP_CLI
{
    public static function error(string $message): void
    {
        throw new RuntimeException($message);
    }
}

function esc_url_raw(string $url): string
{
    return $url;
}

function esc_url(string $url): string
{
    return $url;
}

function home_url(string $path = ''): string
{
    return 'https://example.test' . $path;
}

function untrailingslashit(string $value): string
{
    return rtrim($value, '/');
}

function get_stylesheet_directory_uri(): string
{
    return 'https://example.test/wp-content/themes/blocksy-child';
}

putenv('TMD_COMMERCIAL_LANDINGS_MODE=battery-visual-test-load');
try {
    require dirname(__DIR__) . '/scripts/create-commercial-landing-pages.php';
} catch (RuntimeException $exception) {
    if ('El modo de ejecución comercial indicado no está reconocido.' !== $exception->getMessage()) {
        throw $exception;
    }
}
putenv('TMD_COMMERCIAL_LANDINGS_MODE');

function tmd_battery_visual_assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
}

function tmd_battery_visual_rule(string $css, string $selector, bool $mobile = false): array
{
    $scope = $mobile ? '@media\\s*\\(max-width:\\s*760px\\)[\\s\\S]*?' : '';
    $matches = [];
    preg_match('/' . $scope . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $matches);
    return $matches;
}

$page_content = tmd_commercial_landing_script_page_content('battery', 1559);
$css = (string) file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-commercial-landings.css');

$hero_match = [];
tmd_battery_visual_assert(
    1 === preg_match('/<section\\b[^>]*tmd-commercial-landing__hero--battery-maqueta[^>]*>[\\s\\S]*?<\\/section>/', $page_content, $hero_match),
    'debe existir el hero de la maqueta de baterías'
);
$hero = $hero_match[0];
tmd_battery_visual_assert(false !== strpos($hero, 'Baterías para montacargas <em>eléctricos</em>'), 'el H1 debe conservar su texto');
tmd_battery_visual_assert(false !== strpos($hero, 'Representantes de la marca francesa Barbillon'), 'el subtítulo debe conservar su texto');
tmd_battery_visual_assert(false !== strpos($hero, 'Venta y alquiler para flotas de bodega, con cargador del mismo voltaje y registro BMS de la carga y la descarga.'), 'el párrafo debe conservar su texto');
tmd_battery_visual_assert(false !== strpos($hero, 'banner-bateria-barbillon.webp" alt=""'), 'el hero debe conservar la imagen y su alternativa');

$support_match = [];
tmd_battery_visual_assert(
    1 === preg_match('/<ul\\b[^>]*class="[^"]*tmd-commercial-landing__hero-supports[^"]*"[^>]*>([\\s\\S]*?)<\\/ul>/', $hero, $support_match),
    'el hero debe mostrar la lista semántica de apoyos'
);
$support_markup = $support_match[1];
$support_copy = [
    'performance' => 'Alto rendimiento para jornadas exigentes',
    'durability' => 'Equipos confiables y de larga vida útil',
    'advisory' => 'Asesoría especializada según tu operación',
];
$last_position = -1;
foreach ($support_copy as $type => $copy) {
    $position = strpos($support_markup, $copy);
    tmd_battery_visual_assert(false !== $position && $position > $last_position, 'los apoyos deben conservar su orden: ' . $copy);
    $item_match = [];
    tmd_battery_visual_assert(
        1 === preg_match('/<li\\b[^>]*tmd-commercial-landing__hero-support--' . preg_quote($type, '/') . '[^>]*>([\\s\\S]*?)<\\/li>/', $support_markup, $item_match),
        'cada texto debe tener su icono correspondiente: ' . $type
    );
    tmd_battery_visual_assert(false !== strpos($item_match[1], 'hero-support-icon--' . $type), 'falta el icono ' . $type);
    tmd_battery_visual_assert((bool) preg_match('/<svg\\b[^>]*aria-hidden="true"[^>]*>/', $item_match[1]), 'el icono ' . $type . ' debe ser decorativo');
    $last_position = $position;
}
tmd_battery_visual_assert(strpos($hero, '</p>') < strpos($hero, '<ul class="tmd-commercial-landing__hero-supports"'), 'los apoyos deben ir inmediatamente debajo del párrafo');

$solutions_match = [];
tmd_battery_visual_assert(
    1 === preg_match('/<section\\b[^>]*tmd-commercial-landing__section--battery-solutions[^>]*>[\\s\\S]*?<\\/section>/', $page_content, $solutions_match),
    'debe existir la sección de soluciones de baterías'
);
$solutions = $solutions_match[0];
$heading_match = [];
tmd_battery_visual_assert(
    1 === preg_match('/<h2\\b[^>]*id="tmd-battery-solutions-heading"[^>]*>([\\s\\S]*?)<\\/h2>/', $solutions, $heading_match),
    'la sección debe conservar su H2'
);
tmd_battery_visual_assert(
    '<span class="tmd-commercial-landing__solutions-title-accent">Baterías de tracción</span>, cargadores y monitoreo BMS' === $heading_match[1],
    'solo Baterías de tracción debe estar resaltado y la coma debe quedar fuera del span'
);
tmd_battery_visual_assert(
    1 === preg_match('/<h3\\b[^>]*>(Compatibles con retráctiles, apiladores, estibadores, tomapedidos y equipos de pasillo angosto)<\\/h3>/', $solutions),
    'el texto de compatibilidad debe conservarse sin cambios'
);

$hero_image_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-media img');
tmd_battery_visual_assert(
    isset($hero_image_css[1])
        && (bool) preg_match('/\\bwidth:\\s*90%\\s*;/i', $hero_image_css[1])
        && (bool) preg_match('/\\bmax-width:\\s*none\\s*;/i', $hero_image_css[1])
        && (bool) preg_match('/\\bobject-fit:\\s*contain\\s*;/i', $hero_image_css[1])
        && (bool) preg_match('/\\bobject-position:\\s*right\\s+center\\s*;/i', $hero_image_css[1])
        && (bool) preg_match('/\\bmargin-left:\\s*auto\\s*;/i', $hero_image_css[1])
        && (bool) preg_match('/\\btransform:\\s*none\\s*;/i', $hero_image_css[1]),
    'la imagen debe alejarse, mostrar el encuadre completo y quedar alineada a la derecha solo en el hero de baterías'
);
$supports_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-supports');
tmd_battery_visual_assert(isset($supports_css[1]) && (bool) preg_match('/grid-template-columns:\\s*repeat\\(3,\\s*minmax\\(0,\\s*1fr\\)\\)/i', $supports_css[1]), 'los tres apoyos deben formar columnas en escritorio');
$support_item_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-support');
tmd_battery_visual_assert(isset($support_item_css[1]) && (bool) preg_match('/grid-template-columns:\\s*40px\\s+minmax\\(0,\\s*1fr\\)/i', $support_item_css[1]), 'cada apoyo debe ubicar su icono a la izquierda del texto');
$support_icon_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-support-icon');
tmd_battery_visual_assert(
    isset($support_icon_css[1])
        && (bool) preg_match('/border-radius:\\s*50%\\s*;/i', $support_icon_css[1])
        && (bool) preg_match('/border:\\s*1\\.5px\\s+solid\\s+var[(]--tmd-landing-yellow[)]\\s*;/i', $support_icon_css[1])
        && (bool) preg_match('/color:\\s*var[(]--tmd-landing-yellow[)]\\s*;/i', $support_icon_css[1]),
    'los iconos deben ser circulares y usar el amarillo de marca'
);
$mobile_image_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-media img', true);
tmd_battery_visual_assert(isset($mobile_image_css[1]) && (bool) preg_match('/\\bwidth:\\s*100%\\s*;/i', $mobile_image_css[1]) && (bool) preg_match('/\\btransform:\\s*none\\s*;/i', $mobile_image_css[1]), 'en móvil la imagen debe recuperar ancho completo y posición normal');
tmd_battery_visual_assert(isset($mobile_image_css[1]) && (bool) preg_match('/\\bobject-fit:\\s*contain\\s*;/i', $mobile_image_css[1]) && (bool) preg_match('/\\bobject-position:\\s*right\\s+top\\s*;/i', $mobile_image_css[1]), 'en móvil se debe ver la imagen completa en la parte superior');
$mobile_copy_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-copy', true);
tmd_battery_visual_assert(isset($mobile_copy_css[1]) && (bool) preg_match('/\\bwidth:\\s*100%\\s*;/i', $mobile_copy_css[1]) && (bool) preg_match('/\\bmin-width:\\s*0\\s*;/i', $mobile_copy_css[1]), 'el bloque de texto del hero debe caber en la pantalla móvil');
$mobile_lead_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-lead', true);
tmd_battery_visual_assert(isset($mobile_lead_css[1]) && (bool) preg_match('/\\bwidth:\\s*100%\\s*;/i', $mobile_lead_css[1]) && (bool) preg_match('/\\bmax-width:\\s*100%\\s*;/i', $mobile_lead_css[1]), 'el párrafo debe ajustarse al ancho disponible en móvil');
$mobile_supports_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__hero--battery-maqueta .tmd-commercial-landing__hero-supports', true);
tmd_battery_visual_assert(isset($mobile_supports_css[1]) && (bool) preg_match('/grid-template-columns:\\s*minmax\\(0,\\s*1fr\\)/i', $mobile_supports_css[1]), 'los apoyos deben apilarse en móvil');
$heading_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__section--battery-solutions .tmd-commercial-landing__section-heading');
tmd_battery_visual_assert(isset($heading_css[1]) && (bool) preg_match('/text-align:\\s*left\\s*;/i', $heading_css[1]), 'H2 y H3 deben alinearse a la izquierda solo en soluciones de baterías');
$solution_text_css = [];
tmd_battery_visual_assert(
    1 === preg_match('/\\.tmd-commercial-landing__section--battery-solutions \\.tmd-commercial-landing__section-heading h2,\\s*\\.tmd-commercial-landing__section--battery-solutions \\.tmd-commercial-landing__section-heading h3\\s*\\{([^}]*)\\}/s', $css, $solution_text_css)
        && (bool) preg_match('/text-align:\\s*justify\\s*;/i', $solution_text_css[1])
        && (bool) preg_match('/text-align-last:\\s*left\\s*;/i', $solution_text_css[1]),
    'el título y el texto de compatibilidad deben justificarse y conservar la última línea a la izquierda'
);
$accent_css = tmd_battery_visual_rule($css, '.tmd-commercial-landing__section--battery-solutions .tmd-commercial-landing__solutions-title-accent');
tmd_battery_visual_assert(isset($accent_css[1]) && (bool) preg_match('/color:\\s*var\\(--tmd-landing-blue\\)\\s*;/i', $accent_css[1]), 'el acento debe usar el azul de marca por contraste');

echo "OK: contratos visuales locales de hero y encabezado de soluciones de baterías.\n";
