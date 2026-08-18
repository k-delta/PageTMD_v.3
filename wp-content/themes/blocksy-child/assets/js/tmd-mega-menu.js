/* TMD_MEGA_MENU_TRUSTED_CLOSE_FIX */
(function () {
  function toArray(list) {
    return Array.prototype.slice.call(list || []);
  }

  function isDesktopHover() {
    return window.matchMedia && window.matchMedia('(hover: hover)').matches;
  }

  function getRootFrom(element) {
    return element ? element.closest('#tmdMegaMenu') : null;
  }

  function getButtons(root) {
    return toArray(root.querySelectorAll('[data-tmd-panel]'));
  }

  function getPanels(root) {
    return toArray(root.querySelectorAll('.tmd-mm-panel, .tmd-mm-small-panel'));
  }

  function getNav(root) {
    return root.querySelector('.tmd-mm-navbar');
  }

  function getMobileToggle(root) {
    return root.querySelector('[data-mobile-toggle]');
  }

  function getButton(root, name) {
    return getButtons(root).filter(function (button) {
      return button.getAttribute('data-tmd-panel') === name;
    })[0] || null;
  }

  function getPanel(root, name) {
    return root.querySelector('#tmd-mm-panel-' + name);
  }

  function setExpanded(element, value) {
    if (!element) {
      return;
    }

    element.setAttribute('aria-expanded', value ? 'true' : 'false');
  }

  function setMobileOpen(root, value) {
    var nav = getNav(root);
    var toggle = getMobileToggle(root);

    if (nav) {
      nav.classList.toggle('mobile-open', !!value);
    }

    setExpanded(toggle, !!value);
  }

  function clearPanels(root) {
    getPanels(root).forEach(function (panel) {
      panel.classList.remove('visible');
    });

    getButtons(root).forEach(function (button) {
      button.classList.remove('active');
      setExpanded(button, false);
    });
  }

  function closePanels(root) {
    clearPanels(root);
    root.dataset.currentPanel = '';
    root.dataset.lockedPanel = '';
  }

  function closeAll(root) {
    closePanels(root);
    setMobileOpen(root, false);
  }

  function activatePanel(root, button) {
    var name = button.getAttribute('data-tmd-panel');
    var panel = getPanel(root, name);

    if (!name || !panel) {
      return;
    }

    clearPanels(root);

    panel.classList.add('visible');
    button.classList.add('active');
    setExpanded(button, true);

    root.dataset.currentPanel = name;
    root.dataset.lockedPanel = name;

    setMobileOpen(root, true);
  }

  function openPanel(button) {
    var root = getRootFrom(button);

    if (!root) {
      return;
    }

    activatePanel(root, button);
  }

  function passiveOpen(button) {
    var root = getRootFrom(button);

    if (!root) {
      return;
    }

    var name = button.getAttribute('data-tmd-panel');

    if (!root.dataset.currentPanel) {
      activatePanel(root, button);
      return;
    }

    if (root.dataset.currentPanel === name) {
      restorePanel(root);
    }
  }

  function restorePanel(root) {
    var name = root.dataset.lockedPanel || root.dataset.currentPanel;

    if (!name) {
      return;
    }

    var button = getButton(root, name);
    var panel = getPanel(root, name);

    if (!button || !panel) {
      return;
    }

    if (
      !panel.classList.contains('visible') ||
      !button.classList.contains('active') ||
      button.getAttribute('aria-expanded') !== 'true' ||
      root.dataset.currentPanel !== name
    ) {
      clearPanels(root);

      panel.classList.add('visible');
      button.classList.add('active');
      setExpanded(button, true);

      root.dataset.currentPanel = name;
    }
  }

  function isTrustedUserEvent(event) {
    return event && event.isTrusted === true;
  }

  function initMegaMenu() {
    var root = document.getElementById('tmdMegaMenu');

    if (!root) {
      return;
    }

    if (root.dataset.tmdMegaTrustedCloseInit === '1') {
      return;
    }

    root.dataset.tmdMegaTrustedCloseInit = '1';
    root.dataset.currentPanel = root.dataset.currentPanel || '';
    root.dataset.lockedPanel = root.dataset.lockedPanel || '';

    var nav = getNav(root);
    var mobileToggle = getMobileToggle(root);

    if (mobileToggle && nav) {
      setExpanded(mobileToggle, false);

      mobileToggle.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (nav.classList.contains('mobile-open')) {
          closeAll(root);
          return;
        }

        setMobileOpen(root, true);
      });
    }

    getButtons(root).forEach(function (button) {
      setExpanded(button, false);

      button.addEventListener('mouseenter', function () {
        if (!isDesktopHover()) {
          return;
        }

        passiveOpen(button);
      });

      button.addEventListener('focus', function () {
        passiveOpen(button);
      });

      button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        openPanel(button);
      });
    });

    root.querySelectorAll('[data-tmd-close]').forEach(function (item) {
      item.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        closeAll(root);
      });
    });

    root.addEventListener('pointerdown', function (event) {
      event.stopPropagation();
    });

    root.addEventListener('click', function (event) {
      event.stopPropagation();
    });

    document.addEventListener('click', function (event) {
      if (!isTrustedUserEvent(event)) {
        return;
      }

      if (!event.target.closest('#tmdMegaMenu')) {
        closeAll(root);
      }
    }, true);

    document.addEventListener('pointerdown', function (event) {
      if (!isTrustedUserEvent(event)) {
        return;
      }

      if (!event.target.closest('#tmdMegaMenu')) {
        closeAll(root);
      }
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeAll(root);

        if (mobileToggle) {
          mobileToggle.focus();
        }
      }
    });

    var restoreScheduled = false;

    function scheduleRestore() {
      if (!root.dataset.lockedPanel || restoreScheduled) {
        return;
      }

      restoreScheduled = true;

      window.requestAnimationFrame(function () {
        restoreScheduled = false;
        restorePanel(root);
      });
    }

    if (window.MutationObserver) {
      var observer = new MutationObserver(scheduleRestore);

      observer.observe(root, {
        attributes: true,
        subtree: true,
        attributeFilter: ['class', 'aria-expanded', 'data-current-panel', 'data-locked-panel']
      });
    }

    function syncHomeHeaderBackground() {
      var hero = document.querySelector('.kb-row-layout-id47_9c201d-d2');
      var header = root.closest('.tmd-mm-header');

      if (!document.body.classList.contains('home') || !hero) {
        if (header) {
          header.classList.remove('tmd-mm-header--hero-overlap');
        }

        return;
      }

      if (!header) {
        return;
      }

      header.classList.toggle(
        'tmd-mm-header--hero-overlap',
        hero.getBoundingClientRect().bottom > header.getBoundingClientRect().bottom
      );
    }

    syncHomeHeaderBackground();
    window.addEventListener('scroll', syncHomeHeaderBackground, { passive: true });
    window.addEventListener('resize', syncHomeHeaderBackground);

    window.addEventListener('resize', scheduleRestore);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMegaMenu);
  } else {
    initMegaMenu();
  }
}());
