<?php
function is_page($page = ''): bool { return false; }

ob_start();
require dirname(__DIR__) . '/wp-content/themes/blocksy-child/template-parts/tmd-contact-rail.php';
$html = (string) ob_get_clean();
$css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-contact-rail.css');

function contact_rail_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

contact_rail_assert(
    false !== strpos($html, 'class="tmd-whatsapp-float"')
        && false !== strpos($html, 'href="https://wa.me/573244298326"')
        && false !== strpos($html, 'aria-label="Contactar por WhatsApp"')
        && false !== strpos($html, 'target="_blank"')
        && false !== strpos($html, 'rel="noopener noreferrer"'),
    'El acceso flotante global usa el WhatsApp publicado y un nombre accesible.'
);
contact_rail_assert(
    is_string($css)
        && false !== strpos($css, '.tmd-whatsapp-float {')
        && false !== strpos($css, 'position: fixed;')
        && false !== strpos($css, 'right: 24px;')
        && false !== strpos($css, 'bottom: 24px;')
        && false !== strpos($css, 'background: #25d366;'),
    'El botón usa estilos globales y permanece disponible también en móvil.'
);
fwrite(STDOUT, "OK: botón flotante de WhatsApp global.\n");
