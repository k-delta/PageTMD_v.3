(() => {
  'use strict';

  function initWhatsAppDialog() {
    const dialog = document.getElementById('tmd-whatsapp-dialog');
    const triggers = document.querySelectorAll('[data-tmd-whatsapp-popup]');

    if (!dialog || typeof dialog.showModal !== 'function' || !triggers.length) {
      return; // Con JS desactivado o sin soporte de dialog, los enlaces siguen funcionando.
    }

    const form = dialog.querySelector('[data-tmd-whatsapp-form]');
    const nameInput = form.elements.namedItem('name');
    const phoneInput = form.elements.namedItem('phone');
    const durationInput = form.elements.namedItem('duration');
    const closeButton = dialog.querySelector('[data-tmd-whatsapp-close]');
    let activeTrigger = null;

    triggers.forEach((trigger) => {
      trigger.addEventListener('click', (event) => {
        if (event.defaultPrevented || (event.button != null && event.button !== 0) ||
          event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }

        event.preventDefault();
        activeTrigger = trigger;
        form.reset();
        nameInput.setCustomValidity('');
        phoneInput.setCustomValidity('');
        dialog.showModal();
        document.body.classList.add('tmd-whatsapp-dialog-open');
        nameInput.focus();
      });
    });

    closeButton.addEventListener('click', () => dialog.close());

    dialog.addEventListener('click', (event) => {
      if (event.target !== dialog) {
        return;
      }

      const bounds = dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right ||
        event.clientY < bounds.top || event.clientY > bounds.bottom) {
        dialog.close();
      }
    });

    dialog.addEventListener('close', () => {
      document.body.classList.remove('tmd-whatsapp-dialog-open');
      if (activeTrigger && activeTrigger.isConnected) {
        activeTrigger.focus();
      }
    });

    nameInput.addEventListener('input', () => nameInput.setCustomValidity(''));
    phoneInput.addEventListener('input', () => phoneInput.setCustomValidity(''));

    form.addEventListener('submit', (event) => {
      event.preventDefault();

      const name = nameInput.value.trim().replace(/[\r\n]+/g, ' ');
      const phone = phoneInput.value.trim().replace(/[\r\n]+/g, ' ');
      const duration = durationInput.value;
      const digits = phone.replace(/\D/g, '');

      if (name.length < 2) {
        nameInput.setCustomValidity('Escribe tu nombre completo.');
        nameInput.reportValidity();
        return;
      }

      if (digits.length < 7 || digits.length > 15) {
        phoneInput.setCustomValidity('Escribe un número de teléfono válido.');
        phoneInput.reportValidity();
        return;
      }

      if (!activeTrigger) {
        return;
      }

      // Conserva el destinatario propio de cada acceso (flotante o barra lateral).
      const whatsappUrl = new URL(activeTrigger.href);
      if (whatsappUrl.hostname !== 'wa.me') {
        return;
      }

      const message = [
        'Hola, quiero asesoría sobre alquiler de montacargas.',
        'Nombre completo: ' + name,
        'Número de teléfono: ' + phone,
        'Duración estimada del alquiler: ' + (duration || 'No especificada')
      ].join('\n');

      whatsappUrl.searchParams.set('text', message);
      window.open(whatsappUrl.toString(), '_blank', 'noopener,noreferrer');
      dialog.close();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWhatsAppDialog, { once: true });
  } else {
    initWhatsAppDialog();
  }
})();
