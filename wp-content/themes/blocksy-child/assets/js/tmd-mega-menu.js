(function () {
  function closeAll(root) {
    root.querySelectorAll('.tmd-mm-panel, .tmd-mm-small-panel').forEach(function (panel) {
      panel.classList.remove('visible');
    });

    root.querySelectorAll('.tmd-mm-nav-link').forEach(function (button) {
      button.classList.remove('active');
      button.setAttribute('aria-expanded', 'false');
    });

    root.dataset.currentPanel = '';
  }

  function initMegaMenu() {
    var root = document.getElementById('tmdMegaMenu');
    if (!root) return;

    var nav = root.querySelector('.tmd-mm-navbar');
    var mobileToggle = root.querySelector('[data-mobile-toggle]');

    function openPanel(button) {
      var name = button.getAttribute('data-tmd-panel');
      var panel = root.querySelector('#tmd-mm-panel-' + name);
      if (!panel) return;

      if (root.dataset.currentPanel !== name) {
        closeAll(root);
      }

      panel.classList.add('visible');
      button.classList.add('active');
      button.setAttribute('aria-expanded', 'true');
      root.dataset.currentPanel = name;
    }

    if (mobileToggle && nav) {
      mobileToggle.setAttribute('aria-expanded', 'false');

      mobileToggle.addEventListener('click', function () {
        var isOpen = nav.classList.toggle('mobile-open');
        mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    }

    root.querySelectorAll('[data-tmd-panel]').forEach(function (button) {
      button.setAttribute('aria-expanded', 'false');

      button.addEventListener('mouseenter', function () {
        if (window.matchMedia('(hover: hover)').matches) {
          openPanel(button);
        }
      });

      button.addEventListener('focus', function () {
        openPanel(button);
      });

      button.addEventListener('click', function (event) {
        event.preventDefault();
        openPanel(button);
      });
    });

    root.querySelectorAll('[data-tmd-close]').forEach(function (item) {
      item.addEventListener('click', function () {
        closeAll(root);
      });
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('#tmdMegaMenu')) {
        closeAll(root);
        if (mobileToggle && nav) {
          nav.classList.remove('mobile-open');
          mobileToggle.setAttribute('aria-expanded', 'false');
        }
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeAll(root);
        if (mobileToggle && nav) {
          nav.classList.remove('mobile-open');
          mobileToggle.setAttribute('aria-expanded', 'false');
          mobileToggle.focus();
        }
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMegaMenu);
  } else {
    initMegaMenu();
  }
})();
