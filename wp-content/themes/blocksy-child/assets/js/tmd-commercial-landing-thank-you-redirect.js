(function () {
  'use strict';

  var redirects = window.tmdCommercialThankYouRedirects;
  var allowedPaths = {
    '/gracias-baterias/': true,
    '/gracias-montacargas/': true
  };

  if (!redirects || typeof redirects !== 'object') {
    return;
  }

  document.addEventListener('wpcf7mailsent', function (event) {
    if (!event || !event.detail) {
      return;
    }

    var rawFormId = event.detail.contactFormId;
    if (typeof rawFormId !== 'string' && typeof rawFormId !== 'number') {
      return;
    }

    var formId = String(rawFormId);
    if (!/^(1556|1557)$/.test(formId) || !Object.prototype.hasOwnProperty.call(redirects, formId)) {
      return;
    }

    var destination = redirects[formId];
    if (typeof destination !== 'string' || !allowedPaths[destination]) {
      return;
    }

    if (!window.location || typeof window.location.assign !== 'function') {
      return;
    }

    window.location.assign(destination);
  });
})();
