/* TMD_MEGA_MENU_TRUSTED_CLOSE_FIX */
(function () {
  var MOBILE_QUERY = '(max-width: 1024px)';
  var mobileStyleHref = '';

  if (document.currentScript && document.currentScript.src) {
    try {
      var scriptUrl = new URL(document.currentScript.src, window.location.href);
      var styleUrl = new URL('../css/tmd-mobile-menu.css', scriptUrl);
      styleUrl.search = scriptUrl.search;
      mobileStyleHref = styleUrl.toString();
    } catch (error) {
      mobileStyleHref = '';
    }
  }

  function ensureMobileStyles() {
    if (!mobileStyleHref || document.querySelector('link[data-tmd-mobile-menu-style]')) {
      return;
    }

    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = mobileStyleHref;
    link.setAttribute('data-tmd-mobile-menu-style', '1');
    document.head.appendChild(link);
  }

  ensureMobileStyles();

  function toArray(list) {
    return Array.prototype.slice.call(list || []);
  }

  function isMobileLayout() {
    return window.matchMedia && window.matchMedia(MOBILE_QUERY).matches;
  }

  function isDesktopHover() {
    return !isMobileLayout() && window.matchMedia && window.matchMedia('(hover: hover)').matches;
  }

  function getRootFrom(element) {
    return element ? element.closest('#tmdMegaMenu') : null;
  }

  function getButtons(root) {
    return toArray(root.querySelectorAll('.tmd-mm-nav-link[data-tmd-panel]'));
  }

  function getPanels(root) {
    return toArray(root.querySelectorAll('.tmd-mm-panel, .tmd-mm-small-panel'));
  }

  function getMobileToggle(root) {
    return root.querySelector('[data-mobile-toggle]');
  }

  function getMobileDrawer(root) {
    return root.querySelector('#tmd-mm-mobile-drawer');
  }

  function getMobileBackdrop(root) {
    return root.querySelector('[data-mobile-backdrop]');
  }

  function getMobileSectionToggles(root) {
    return toArray(root.querySelectorAll('[data-mobile-section-toggle]'));
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

  function activatePanel(root, button) {
    if (isMobileLayout()) {
      return;
    }

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

    if (!root || isMobileLayout()) {
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
    if (isMobileLayout()) {
      return;
    }

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

  function setPageScrollLocked(value) {
    document.documentElement.classList.toggle('tmd-mm-mobile-scroll-lock', !!value);

    if (document.body) {
      document.body.classList.toggle('tmd-mm-mobile-scroll-lock', !!value);
    }
  }

  function updateMobileToggle(root, value) {
    var toggle = getMobileToggle(root);

    if (!toggle) {
      return;
    }

    setExpanded(toggle, value);
    toggle.setAttribute('aria-label', value ? 'Cerrar menú' : 'Abrir menú');

    var icon = toggle.querySelector('i');

    if (icon) {
      icon.classList.toggle('ti-menu-2', !value);
      icon.classList.toggle('ti-x', value);
    }
  }

  function clearMobileSections(root) {
    getMobileSectionToggles(root).forEach(function (button) {
      var panelId = button.getAttribute('aria-controls');
      var panel = panelId ? document.getElementById(panelId) : null;
      var label = button.getAttribute('data-mobile-section-label') || 'sección';
      var section = button.closest('.tmd-mm-mobile-section');

      setExpanded(button, false);
      button.setAttribute('aria-label', 'Mostrar opciones de ' + label);

      if (section) {
        section.classList.remove('is-open');
      }

      if (panel) {
        panel.hidden = true;
      }
    });
  }

  function toggleMobileSection(root, button) {
    var panelId = button.getAttribute('aria-controls');
    var panel = panelId ? document.getElementById(panelId) : null;
    var label = button.getAttribute('data-mobile-section-label') || 'sección';
    var isOpen = button.getAttribute('aria-expanded') === 'true';
    var section = button.closest('.tmd-mm-mobile-section');

    if (!panel) {
      return;
    }

    clearMobileSections(root);

    if (isOpen) {
      return;
    }

    panel.hidden = false;
    setExpanded(button, true);
    button.setAttribute('aria-label', 'Ocultar opciones de ' + label);

    if (section) {
      section.classList.add('is-open');
    }
  }

  function setMobileMenuOpen(root, value, focusToggle) {
    var drawer = getMobileDrawer(root);
    var backdrop = getMobileBackdrop(root);
    var header = root.closest('.tmd-mm-header');

    if (!drawer || !backdrop) {
      return;
    }

    if (value && !isMobileLayout()) {
      return;
    }

    if (value) {
      closePanels(root);
      ensureMobileStyles();

      drawer.hidden = false;
      backdrop.hidden = false;
      drawer.removeAttribute('inert');
      drawer.setAttribute('aria-hidden', 'false');
      backdrop.setAttribute('aria-hidden', 'false');

      window.requestAnimationFrame(function () {
        drawer.classList.add('is-open');
        backdrop.classList.add('is-open');
      });
    } else {
      drawer.classList.remove('is-open');
      backdrop.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
      backdrop.setAttribute('aria-hidden', 'true');
      drawer.setAttribute('inert', '');
      clearMobileSections(root);

      window.setTimeout(function () {
        if (!drawer.classList.contains('is-open')) {
          drawer.hidden = true;
        }

        if (!backdrop.classList.contains('is-open')) {
          backdrop.hidden = true;
        }
      }, 220);
    }

    root.classList.toggle('mobile-menu-open', !!value);

    if (header) {
      header.classList.toggle('tmd-mm-mobile-menu-open', !!value);
    }

    updateMobileToggle(root, !!value);
    setPageScrollLocked(!!value);

    if (!value && focusToggle) {
      var toggle = getMobileToggle(root);

      if (toggle) {
        toggle.focus();
      }
    }
  }

  function mobileMenuIsOpen(root) {
    var drawer = getMobileDrawer(root);
    return !!drawer && drawer.classList.contains('is-open');
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

    var mobileToggle = getMobileToggle(root);
    var mobileBackdrop = getMobileBackdrop(root);
    var mobileDrawer = getMobileDrawer(root);

    if (mobileDrawer) {
      mobileDrawer.hidden = true;
      mobileDrawer.setAttribute('aria-hidden', 'true');
      mobileDrawer.setAttribute('inert', '');
    }

    if (mobileBackdrop) {
      mobileBackdrop.hidden = true;
      mobileBackdrop.setAttribute('aria-hidden', 'true');
    }

    if (mobileToggle) {
      updateMobileToggle(root, false);

      mobileToggle.addEventListener('click', function (event) {
        if (!isMobileLayout()) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();

        setMobileMenuOpen(root, !mobileMenuIsOpen(root), false);
      });
    }

    getMobileSectionToggles(root).forEach(function (button) {
      setExpanded(button, false);

      button.addEventListener('click', function (event) {
        if (!isMobileLayout()) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        toggleMobileSection(root, button);
      });
    });

    if (mobileBackdrop) {
      mobileBackdrop.addEventListener('click', function () {
        setMobileMenuOpen(root, false, true);
      });
    }

    if (mobileDrawer) {
      toArray(mobileDrawer.querySelectorAll('a[href]')).forEach(function (link) {
        link.addEventListener('click', function () {
          setMobileMenuOpen(root, false, false);
        });
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
        if (isMobileLayout()) {
          return;
        }

        passiveOpen(button);
      });

      button.addEventListener('click', function (event) {
        if (isMobileLayout()) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        openPanel(button);
      });
    });

    root.querySelectorAll('[data-tmd-close]').forEach(function (item) {
      item.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        closePanels(root);
        setMobileMenuOpen(root, false, false);
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
        if (isMobileLayout()) {
          setMobileMenuOpen(root, false, false);
        } else {
          closePanels(root);
        }
      }
    }, true);

    document.addEventListener('pointerdown', function (event) {
      if (!isTrustedUserEvent(event)) {
        return;
      }

      if (!event.target.closest('#tmdMegaMenu')) {
        if (isMobileLayout()) {
          setMobileMenuOpen(root, false, false);
        } else {
          closePanels(root);
        }
      }
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') {
        return;
      }

      if (isMobileLayout()) {
        setMobileMenuOpen(root, false, true);
      } else {
        closePanels(root);
      }
    });

    var restoreScheduled = false;

    function scheduleRestore() {
      if (isMobileLayout() || !root.dataset.lockedPanel || restoreScheduled) {
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

    var previousMobileLayout = isMobileLayout();

    function handleResize() {
      var nextMobileLayout = isMobileLayout();

      if (nextMobileLayout !== previousMobileLayout) {
        closePanels(root);
        setMobileMenuOpen(root, false, false);
        previousMobileLayout = nextMobileLayout;
      }

      syncHomeHeaderBackground();
      scheduleRestore();
    }

    syncHomeHeaderBackground();
    window.addEventListener('scroll', syncHomeHeaderBackground, { passive: true });
    window.addEventListener('resize', handleResize);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMegaMenu);
  } else {
    initMegaMenu();
  }
}());
