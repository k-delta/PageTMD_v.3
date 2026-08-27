(function () {
  'use strict';

  var browserWindow = typeof window !== 'undefined' ? window : null;
  var config = browserWindow ? (browserWindow.tmdContactConversion || {}) : {};

  function ready(callback) {
    if (document.readyState !== 'loading') {
      callback();
      return;
    }

    document.addEventListener('DOMContentLoaded', callback);
  }

  function queryContext(search) {
    var query = typeof search === 'string'
      ? search
      : (browserWindow && browserWindow.location ? browserWindow.location.search : '');
    var params = new URLSearchParams(query);
    var id = params.get('tmd_cotizacion_id') || params.get('equipo_id') || '';
    var type = (params.get('tmd_tipo_cotizacion') || '').toLowerCase();
    var title = params.get('tmd_cotizacion') || '';

    if (!title) {
      title = params.get('equipo') || '';
      if (title) {
        type = 'montacargas';
      }
    }

    if (!title) {
      title = params.get('tmd_cotizacion_energia') || params.get('energia') || '';
      if (title) {
        type = 'bateria';
      }
    }

    if (['bateria', 'batería', 'energia', 'energía'].indexOf(type) !== -1) {
      type = 'bateria';
    } else if (['equipo', 'montacargas'].indexOf(type) !== -1) {
      type = 'montacargas';
    } else {
      type = '';
    }

    if (!title || !type) {
      return null;
    }

    return {
      id: id,
      type: type,
      typeLabel: type === 'bateria' ? 'Energía' : 'Equipo',
      title: title
    };
  }

  function ensureHiddenField(form, name) {
    var field = form.querySelector('[name="' + name + '"]');

    if (field) {
      return field;
    }

    field = document.createElement('input');
    field.type = 'hidden';
    field.name = name;
    form.appendChild(field);
    return field;
  }

  function ensureStatus(form) {
    var status = form.querySelector('[data-tmd-conversion-status]');

    if (status) {
      return status;
    }

    status = document.createElement('div');
    status.className = 'tmd-conversion-status';
    status.setAttribute('data-tmd-conversion-status', '');
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    form.appendChild(status);
    return status;
  }

  function setStatus(form, message, type, withWhatsapp) {
    var status = ensureStatus(form);
    status.replaceChildren();
    status.className = 'tmd-conversion-status' + (type ? ' is-' + type : '');

    if (message) {
      status.appendChild(document.createTextNode(message));
    }

    if (withWhatsapp && config.whatsappUrl) {
      status.appendChild(document.createTextNode(' '));
      var link = document.createElement('a');
      link.href = config.whatsappUrl;
      link.target = '_blank';
      link.rel = 'noopener noreferrer';
      link.textContent = 'Escribir por WhatsApp';
      status.appendChild(link);
    }
  }

  function setBusy(form, busy) {
    var submit = form.querySelector('[type="submit"]');

    form.setAttribute('aria-busy', busy ? 'true' : 'false');
    if (!submit) {
      return;
    }

    if (!submit.dataset.tmdOriginalLabel) {
      submit.dataset.tmdOriginalLabel = submit.value || submit.textContent || 'Enviar solicitud';
    }

    submit.disabled = busy;
    submit.setAttribute('aria-disabled', busy ? 'true' : 'false');

    if ('value' in submit && submit.tagName === 'INPUT') {
      submit.value = busy ? 'Enviando…' : submit.dataset.tmdOriginalLabel;
    } else {
      submit.textContent = busy ? 'Enviando…' : submit.dataset.tmdOriginalLabel;
    }
  }

  function applyContext(form, context) {
    if (!context) {
      return;
    }

    ensureHiddenField(form, 'tmd_cotizacion_id').value = context.id;
    ensureHiddenField(form, 'tmd_tipo_cotizacion').value = context.typeLabel;
    ensureHiddenField(form, 'tmd_cotizacion').value = context.title;
    ensureHiddenField(form, 'tmd_url_origen').value = window.location.href;

    var service = form.querySelector('[name="service"]');
    if (service) {
      service.value = context.type === 'bateria' ? 'Baterías y cargadores' : 'Venta de equipo';
    }

    var message = form.querySelector('[name="message"]');
    if (message && !message.value) {
      message.value = 'Hola, quiero recibir información sobre: ' + context.title;
    }
  }

  function eventBelongsToForm(event, form) {
    return event.target === form || (event.target && event.target.contains && event.target.contains(form));
  }

  function bindForm(form, context) {
    if (form.dataset.tmdConversionBound === 'true') {
      applyContext(form, context);
      return;
    }

    form.dataset.tmdConversionBound = 'true';
    ensureStatus(form);
    applyContext(form, context);

    form.addEventListener('invalid', function () {
      setStatus(form, 'Revisa los campos señalados y completa la información obligatoria.', 'validation', false);
    }, true);

    form.addEventListener('submit', function () {
      applyContext(form, context);

      if (form.checkValidity()) {
        setStatus(form, 'Enviando tu solicitud…', 'loading', false);
      }
    });

    document.addEventListener('wpcf7beforesubmit', function (event) {
      if (!eventBelongsToForm(event, form)) {
        return;
      }

      setBusy(form, true);
      setStatus(form, 'Enviando tu solicitud…', 'loading', false);
    });

    document.addEventListener('wpcf7invalid', function (event) {
      if (eventBelongsToForm(event, form)) {
        setStatus(form, 'Revisa los campos señalados y completa la información obligatoria.', 'validation', false);
      }
    });

    document.addEventListener('wpcf7mailsent', function (event) {
      if (eventBelongsToForm(event, form)) {
        setStatus(form, 'Solicitud enviada. Nuestro equipo revisará la información y se pondrá en contacto contigo.', 'success', false);
      }
    });

    ['wpcf7mailfailed', 'wpcf7spam', 'wpcf7aborted'].forEach(function (eventName) {
      document.addEventListener(eventName, function (event) {
        if (eventBelongsToForm(event, form)) {
          setStatus(form, 'No pudimos enviar la solicitud. Revisa tu conexión e inténtalo nuevamente.', 'error', true);
        }
      });
    });

    document.addEventListener('wpcf7submit', function (event) {
      if (eventBelongsToForm(event, form)) {
        setBusy(form, false);
      }
    });

    document.addEventListener('wpcf7reset', function (event) {
      if (eventBelongsToForm(event, form)) {
        window.setTimeout(function () {
          applyContext(form, context);
        }, 0);
      }
    });
  }

  function init() {
    var context = queryContext();
    var forms = document.querySelectorAll('.tmd-contact-grid .wpcf7-form, .tmd-contact-grid form.tmd-form-card');

    for (var index = 0; index < forms.length; index += 1) {
      bindForm(forms[index], context);
    }
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      eventBelongsToForm: eventBelongsToForm,
      queryContext: queryContext
    };
  }

  if (typeof document === 'undefined') {
    return;
  }

  ready(init);
  document.addEventListener('wpcf7init', init);
})();
