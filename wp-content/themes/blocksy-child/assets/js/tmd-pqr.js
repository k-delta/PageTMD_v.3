(function () {
  'use strict';

  var config = window.tmdPqr || {};
  var forms = document.querySelectorAll('form[data-tmd-ajax-form]');
  var form = null;

  for (var index = 0; index < forms.length; index += 1) {
    var formType = forms[index].querySelector('input[name="form_type"]');
    if (formType && formType.value === 'pqr') {
      form = forms[index];
      break;
    }
  }

  if (!form || !config.ajaxUrl || !config.nonce) {
    return;
  }

  var status = form.querySelector('[data-tmd-form-status]');
  var submit = form.querySelector('[type="submit"]');
  var requestType = form.querySelector('input[name="request_type"]');
  var choiceButtons = form.querySelectorAll('[data-tmd-choice]');
  var originalSubmitText = submit ? submit.textContent : '';

  if (status) {
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
  }

  function setStatus(message, type) {
    if (!status) {
      return;
    }

    status.textContent = message;
    status.classList.remove('is-success', 'is-error');
    if (type) {
      status.classList.add('is-' + type);
    }
  }

  function selectRequestType(value) {
    if (requestType) {
      requestType.value = value;
    }

    for (var buttonIndex = 0; buttonIndex < choiceButtons.length; buttonIndex += 1) {
      var button = choiceButtons[buttonIndex];
      var selected = button.getAttribute('data-tmd-choice') === value;
      button.classList.toggle('is-active', selected);
      button.setAttribute('aria-pressed', selected ? 'true' : 'false');
    }
  }

  for (var buttonIndex = 0; buttonIndex < choiceButtons.length; buttonIndex += 1) {
    choiceButtons[buttonIndex].addEventListener('click', function () {
      selectRequestType(this.getAttribute('data-tmd-choice') || 'Peticion');
    });
  }

  selectRequestType(requestType && requestType.value ? requestType.value : 'Peticion');

  form.addEventListener('submit', async function (event) {
    event.preventDefault();

    if (!form.reportValidity() || (submit && submit.disabled)) {
      return;
    }

    var data = new FormData(form);
    data.append('action', 'tmd_pqr');
    data.append('nonce', config.nonce);
    if (!data.has('website')) {
      data.append('website', '');
    }

    if (submit) {
      submit.disabled = true;
      submit.setAttribute('aria-disabled', 'true');
      submit.textContent = config.sendingText || 'Procesando…';
    }
    setStatus('', '');

    try {
      var response = await fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      });
      var payload = await response.json();
      var message = payload && payload.data && payload.data.message
        ? payload.data.message
        : config.networkError;

      if (!response.ok || !payload.success) {
        setStatus(message, 'error');
        return;
      }

      form.reset();
      selectRequestType('Peticion');
      setStatus(message, 'success');
    } catch (error) {
      setStatus(config.networkError || 'No fue posible conectar con el servidor.', 'error');
    } finally {
      if (submit) {
        submit.disabled = false;
        submit.removeAttribute('aria-disabled');
        submit.textContent = originalSubmitText;
      }
    }
  });
})();
