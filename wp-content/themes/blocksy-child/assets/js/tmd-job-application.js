(function () {
  'use strict';

  var heroImage = document.querySelector('.tmd-jobs-hero-card img');
  if (heroImage) {
    heroImage.src = '/wp-content/plugins/tm-quiz-equipo-ideal/assets/images/quiz/quiz-load.webp';
    heroImage.alt = 'Operación logística con montacargas';
  }

  var config = window.tmdJobApplication || {};
  var form = document.querySelector('[data-tmd-job-application]');

  if (!form || !config.ajaxUrl || !config.nonce) {
    return;
  }

  var status = form.querySelector('[data-tmd-form-status]');
  var submit = form.querySelector('[type="submit"]');
  var fileInput = form.querySelector('input[name="cv"]');
  var allowedExtensions = ['pdf', 'doc', 'docx'];
  var originalSubmitText = submit ? submit.textContent : '';

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

  function validFile(file) {
    if (!file) {
      return false;
    }

    var extension = file.name.split('.').pop().toLowerCase();
    return allowedExtensions.indexOf(extension) !== -1 && file.size > 0 && file.size <= Number(config.maxBytes || 0);
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();

    if (!form.reportValidity()) {
      return;
    }

    if (!fileInput || !validFile(fileInput.files[0])) {
      setStatus(config.invalidFile || 'Selecciona un archivo válido.', 'error');
      if (fileInput) {
        fileInput.focus();
      }
      return;
    }

    if (submit && submit.disabled) {
      return;
    }

    var data = new FormData(form);
    data.append('action', 'tmd_job_application');
    data.append('nonce', config.nonce);

    if (submit) {
      submit.disabled = true;
      submit.setAttribute('aria-disabled', 'true');
      submit.textContent = config.sendingText || 'Enviando…';
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

