(function () {
  'use strict';

  var heroImage = document.querySelector('.tmd-jobs-hero-card img');
  if (heroImage) {
    heroImage.src = '/wp-content/plugins/tm-quiz-equipo-ideal/assets/images/quiz/quiz-load.webp';
    heroImage.alt = 'Operación logística con montacargas';
  }

  function initVacancyCarousel() {
    var grid = document.querySelector('.tmd-jobs-vacancies .tmd-jobs-grid');

    if (!grid || !grid.children || typeof grid.addEventListener !== 'function'
      || !grid.parentNode || typeof grid.parentNode.insertBefore !== 'function'
    ) {
      return;
    }

    if (typeof grid.getAttribute === 'function'
      && 'true' === grid.getAttribute('data-tmd-vacancy-carousel')
    ) {
      return;
    }

    var cards = [];
    for (var cardIndex = 0; cardIndex < grid.children.length; cardIndex += 1) {
      var card = grid.children[cardIndex];
      if (card && typeof card.matches === 'function' && card.matches('.tmd-job-card')) {
        cards.push(card);
      }
    }

    if (cards.length === 0 || typeof document.createElement !== 'function') {
      return;
    }

    grid.setAttribute('data-tmd-vacancy-carousel', 'true');
    grid.setAttribute('role', 'region');
    grid.setAttribute('aria-roledescription', 'carousel');
    grid.setAttribute('aria-label', 'Vacantes disponibles');
    grid.setAttribute('tabindex', '0');

    cards.forEach(function (card, index) {
      card.setAttribute('role', 'group');
      card.setAttribute('aria-roledescription', 'slide');
      card.setAttribute('aria-label', (index + 1) + ' de ' + cards.length);
    });

    var controls = document.createElement('div');
    controls.className = 'tmd-jobs-carousel-controls';
    controls.setAttribute('role', 'group');
    controls.setAttribute('aria-label', 'Controles de vacantes');

    var previous = document.createElement('button');
    previous.type = 'button';
    previous.className = 'tmd-jobs-carousel-control tmd-jobs-carousel-control--previous';
    previous.setAttribute('aria-label', 'Mostrar vacante anterior');
    previous.textContent = '‹ Anterior';

    var next = document.createElement('button');
    next.type = 'button';
    next.className = 'tmd-jobs-carousel-control tmd-jobs-carousel-control--next';
    next.setAttribute('aria-label', 'Mostrar siguiente vacante');
    next.textContent = 'Siguiente ›';

    var indicators = document.createElement('div');
    indicators.className = 'tmd-jobs-carousel-indicators';
    indicators.setAttribute('role', 'group');
    indicators.setAttribute('aria-label', 'Indicadores de vacantes');

    var status = document.createElement('span');
    status.className = 'tmd-jobs-carousel-status';
    status.setAttribute('aria-live', 'polite');
    status.setAttribute('aria-atomic', 'true');

    var indicatorButtons = [];
    var currentIndex = 0;

    function visibleCardCount() {
      var computedStyle = typeof window.getComputedStyle === 'function'
        ? window.getComputedStyle(grid)
        : null;
      var configured = computedStyle && typeof computedStyle.getPropertyValue === 'function'
        ? parseInt(computedStyle.getPropertyValue('--tmd-jobs-visible'), 10)
        : 0;

      if (configured > 0) {
        return Math.min(configured, cards.length);
      }

      var viewportWidth = Number(window.innerWidth) || Number(grid.clientWidth) || 0;
      if (viewportWidth <= 640) {
        return 1;
      }
      if (viewportWidth <= 900) {
        return Math.min(2, cards.length);
      }

      return Math.min(3, cards.length);
    }

    function maxStartIndex() {
      return Math.max(0, cards.length - visibleCardCount());
    }

    function cardLeft(card, index) {
      if (typeof card.offsetLeft === 'number' && ! isNaN(card.offsetLeft)) {
        return card.offsetLeft;
      }

      return index * (Number(grid.clientWidth) || 0);
    }

    function closestIndex() {
      var currentLeft = Number(grid.scrollLeft) || 0;
      var closest = 0;
      var closestDistance = Infinity;
      var lastStartIndex = maxStartIndex();

      cards.slice(0, lastStartIndex + 1).forEach(function (card, index) {
        var distance = Math.abs(cardLeft(card, index) - currentLeft);
        if (distance < closestDistance) {
          closest = index;
          closestDistance = distance;
        }
      });

      return closest;
    }

    function syncControls(index) {
      var lastStartIndex = maxStartIndex();
      currentIndex = Math.max(0, Math.min(index, lastStartIndex));
      previous.disabled = 0 === currentIndex;
      next.disabled = lastStartIndex === currentIndex;
      status.textContent = 'Vista ' + (currentIndex + 1) + ' de ' + (lastStartIndex + 1);

      indicatorButtons.forEach(function (indicator, indicatorIndex) {
        if (indicatorIndex === currentIndex) {
          indicator.setAttribute('aria-current', 'true');
        } else {
          indicator.removeAttribute('aria-current');
        }
      });
    }

    function goTo(index) {
      var targetIndex = Math.max(0, Math.min(index, maxStartIndex()));
      var targetLeft = cardLeft(cards[targetIndex], targetIndex);
      var reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      if (typeof grid.scrollTo === 'function') {
        try {
          grid.scrollTo({
            left: targetLeft,
            behavior: reduceMotion ? 'auto' : 'smooth'
          });
        } catch (error) {
          grid.scrollLeft = targetLeft;
        }
      } else {
        grid.scrollLeft = targetLeft;
      }

      syncControls(targetIndex);
    }

    function renderIndicators() {
      while (indicators.children.length > 0) {
        indicators.removeChild(indicators.children[0]);
      }

      indicatorButtons = [];

      for (var viewIndex = 0; viewIndex <= maxStartIndex(); viewIndex += 1) {
        var indicator = document.createElement('button');
        indicator.type = 'button';
        indicator.className = 'tmd-jobs-carousel-indicator';
        indicator.setAttribute('aria-label', 'Mostrar vista de vacantes ' + (viewIndex + 1));
        indicator.textContent = String(viewIndex + 1);
        (function (selectedIndex) {
          indicator.addEventListener('click', function () {
            goTo(selectedIndex);
          });
        }(viewIndex));
        indicatorButtons.push(indicator);
        indicators.appendChild(indicator);
      }

      syncControls(currentIndex);
    }

    previous.addEventListener('click', function () {
      goTo(currentIndex - 1);
    });
    next.addEventListener('click', function () {
      goTo(currentIndex + 1);
    });
    grid.addEventListener('scroll', function () {
      syncControls(closestIndex());
    });
    grid.addEventListener('keydown', function (event) {
      var key = event.key || event.keyCode;
      if ('ArrowLeft' === key || 37 === key) {
        event.preventDefault();
        goTo(currentIndex - 1);
      }
      if ('ArrowRight' === key || 39 === key) {
        event.preventDefault();
        goTo(currentIndex + 1);
      }
    });

    if (typeof window.addEventListener === 'function') {
      window.addEventListener('resize', function () {
        renderIndicators();
        goTo(Math.min(currentIndex, maxStartIndex()));
      });
    }

    controls.appendChild(previous);
    controls.appendChild(indicators);
    controls.appendChild(next);
    controls.appendChild(status);
    grid.parentNode.insertBefore(controls, grid.nextSibling || null);
    renderIndicators();
    syncControls(0);
  }

  initVacancyCarousel();

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
