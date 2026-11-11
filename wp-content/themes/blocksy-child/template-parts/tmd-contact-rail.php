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
    data-tmd-whatsapp-popup
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

<a
  class="tmd-whatsapp-float"
  href="https://wa.me/573168770708"
  data-tmd-whatsapp-popup
  target="_blank"
  rel="noopener noreferrer"
  aria-label="Contactar por WhatsApp"
><i class="ti ti-brand-whatsapp" aria-hidden="true"></i></a>

<dialog
  id="tmd-whatsapp-dialog"
  class="tmd-whatsapp-dialog"
  aria-labelledby="tmd-whatsapp-dialog-title"
  aria-describedby="tmd-whatsapp-dialog-description"
>
  <div class="tmd-whatsapp-dialog__content">
    <button class="tmd-whatsapp-dialog__close" type="button" data-tmd-whatsapp-close aria-label="Cerrar formulario">
      <i class="ti ti-x" aria-hidden="true"></i>
    </button>

    <h2 id="tmd-whatsapp-dialog-title" class="tmd-whatsapp-dialog__title">Hablemos por WhatsApp</h2>
    <p id="tmd-whatsapp-dialog-description" class="tmd-whatsapp-dialog__description">
      Déjanos tu nombre y celular y te conectamos con un asesor.
    </p>

    <form class="tmd-whatsapp-dialog__form" data-tmd-whatsapp-form>
      <label class="tmd-whatsapp-dialog__field">
        <span>Nombre completo</span>
        <input name="name" type="text" autocomplete="name" placeholder="Nombre completo" minlength="2" maxlength="120" required>
      </label>
      <label class="tmd-whatsapp-dialog__field">
        <span>Número de teléfono</span>
        <input name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="Teléfono" maxlength="25" required>
      </label>
      <label class="tmd-whatsapp-dialog__field">
        <span>Duración estimada del alquiler <em>(opcional)</em></span>
        <select name="duration">
          <option value="">—Por favor, elige una opción—</option>
          <option value="Mínimo 15 días">Mínimo 15 días</option>
          <option value="1 mes">1 mes</option>
          <option value="3 meses">3 meses</option>
          <option value="6 meses">6 meses</option>
          <option value="1 año">1 año</option>
          <option value="Más de 1 año">Más de 1 año</option>
        </select>
      </label>
      <label class="tmd-whatsapp-dialog__consent">
        <input name="privacy" type="checkbox" required>
        <span>Acepto la <a href="<?php echo esc_url(home_url('/privacidad/')); ?>" target="_blank" rel="noopener noreferrer">política de privacidad y tratamiento de datos personales</a>.</span>
      </label>
      <button class="tmd-whatsapp-dialog__submit" type="submit">Continuar a WhatsApp</button>
    </form>
  </div>
</dialog>
