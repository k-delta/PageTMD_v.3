<?php
/**
 * Accesos globales de contacto para escritorio y tablet.
 */

if (is_page(57)) {
    add_filter('the_content', static function ($content) {
        $content = preg_replace(
            '/(<h1\b[^>]*>)\s*Contactanos\s*(<\/h1>)/iu',
            '$1CONTÁCTANOS$2',
            $content,
            1
        );

        $content = preg_replace(
            '/(<h2\b[^>]*>)\s*Nuestro equipo de asesores\s*(<\/h2>)/iu',
            '$1Nuestro equipo$2',
            $content,
            1
        );

        $advisor_emails = [
            '+573022734800' => 'consultor3@tmdual.com',
            '+573244298326' => 'consultor2@tmdual.com',
            '+573168770708' => 'consultor1@tmdual.com',
        ];

        foreach ($advisor_emails as $phone => $email) {
            $content = preg_replace(
                '/href=(["\'])tel:' . preg_quote($phone, '/') . '\1([^>]*)>\s*Llamar ahora\s*<\/a>/iu',
                'href="mailto:' . $email . '"$2>Correo</a>',
                $content,
                1
            );
        }

        return $content;
    }, 20);
}
?>
<nav class="tmd-contact-rail" aria-label="Contacto rápido">
  <a
    class="tmd-contact-rail__link"
    href="https://maps.google.com/?q=Carrera%20108%20No.22F-21%20Bogota%20Colombia"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Ver ubicación en Google Maps"
  >
    <i class="ti ti-map-pin" aria-hidden="true"></i>
    <span class="tmd-contact-rail__label" aria-hidden="true">Ubicación</span>
  </a>

  <a
    class="tmd-contact-rail__link"
    href="https://wa.me/573244298326"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Contactar por WhatsApp"
  >
    <i class="ti ti-brand-whatsapp" aria-hidden="true"></i>
    <span class="tmd-contact-rail__label" aria-hidden="true">WhatsApp</span>
  </a>

  <a
    class="tmd-contact-rail__link"
    href="mailto:info@tmdual.com"
    aria-label="Enviar correo electrónico"
  >
    <i class="ti ti-mail" aria-hidden="true"></i>
    <span class="tmd-contact-rail__label" aria-hidden="true">Correo</span>
  </a>

  <a
    class="tmd-contact-rail__link"
    href="https://co.linkedin.com/company/tmdual"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Visitar LinkedIn de Tecnimontacargas"
  >
    <i class="ti ti-brand-linkedin" aria-hidden="true"></i>
    <span class="tmd-contact-rail__label" aria-hidden="true">LinkedIn</span>
  </a>
</nav>
