(function () {
  'use strict';

  var config = window.tmdBusinessProposals || {};
  var forms = document.querySelectorAll('[data-tmd-business-proposals]');

  if (!forms.length || !config.ajaxUrl || !config.nonce) {
    return;
  }

  function filesAreValid(files) {
    var allowed = ['pdf', 'docx', 'jpg', 'jpeg', 'png', 'webp'];
    var total = 0;

    if (!files || files.length < 1 || files.length > Number(config.maxFiles || 3)) {
      return false;
    }

    for (var index = 0; index < files.length; index += 1) {
      var file = files[index];
      var extension = file.name.split('.').pop().toLowerCase();
      total += file.size;
      if (allowed.indexOf(extension) === -1 || file.size < 1 || file.size > Number(config.maxBytes || 0)) {
        return false;
      }
    }
    return total <= Number(config.maxBytes || 0);
  }

  forms.forEach(function (form) {
    var status = form.querySelector('[data-tmd-form-status]');
    var submit = form.querySelector('[type="submit"]');
    var input = form.querySelector('input[name="attachments[]"]');
    var originalText = submit ? submit.textContent : '';

    function setStatus(message, type) {
      if (!status) return;
      status.textContent = message;
      status.classList.remove('is-success', 'is-error');
      if (type) status.classList.add('is-' + type);
    }

    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (!form.reportValidity()) return;
      if (!input || !filesAreValid(input.files)) {
        setStatus(config.invalidFiles, 'error');
        if (input) input.focus();
        return;
      }
      if (submit && submit.disabled) return;

      var data = new FormData(form);
      data.set('nonce', config.nonce);
      if (submit) {
        submit.disabled = true;
        submit.setAttribute('aria-disabled', 'true');
        submit.textContent = config.sendingText;
      }
      setStatus('', '');

      try {
        var response = await fetch(config.ajaxUrl, {method: 'POST', credentials: 'same-origin', body: data});
        var payload = await response.json();
        var message = payload && payload.data && payload.data.message ? payload.data.message : config.networkError;
        if (!response.ok || !payload.success) {
          setStatus(message, 'error');
          return;
        }
        form.reset();
        setStatus(message, 'success');
      } catch (error) {
        setStatus(config.networkError, 'error');
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.removeAttribute('aria-disabled');
          submit.textContent = originalText;
        }
      }
    });
  });
})();
