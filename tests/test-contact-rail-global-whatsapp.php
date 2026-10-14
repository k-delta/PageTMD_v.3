<?php
function is_page($page = ''): bool { return false; }

ob_start();
require dirname(__DIR__) . '/wp-content/themes/blocksy-child/template-parts/tmd-contact-rail.php';
$html = (string) ob_get_clean();
$css = file_get_contents(dirname(__DIR__) . '/wp-content/themes/blocksy-child/assets/css/tmd-contact-rail.css');
preg_match_all('/<a\b([^>]*)>/s', $html, $anchor_matches);
$floating_button_attributes = '';
foreach ($anchor_matches[1] as $attributes) {
    if (
        preg_match('/(?:^|\s)class="([^"]*)"/', $attributes, $class_match)
        && in_array('tmd-whatsapp-float', preg_split('/\s+/', trim($class_match[1])), true)
    ) {
        $floating_button_attributes = $attributes;
        break;
    }
}

function contact_rail_assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

function contact_rail_has_attribute(string $attributes, string $name, string $value): bool
{
    $pattern = '/(?:^|\s)' . preg_quote($name, '/') . '="' . preg_quote($value, '/') . '"/';

    return 1 === preg_match($pattern, $attributes);
}

contact_rail_assert(
    '' !== $floating_button_attributes
        && contact_rail_has_attribute($floating_button_attributes, 'href', 'https://wa.me/573168770708')
        && contact_rail_has_attribute($floating_button_attributes, 'aria-label', 'Contactar por WhatsApp')
        && contact_rail_has_attribute($floating_button_attributes, 'target', '_blank')
        && contact_rail_has_attribute($floating_button_attributes, 'rel', 'noopener noreferrer'),
    'El acceso flotante global redirige al WhatsApp solicitado y conserva su nombre accesible.'
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
