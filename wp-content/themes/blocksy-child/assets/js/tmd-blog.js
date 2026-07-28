document.addEventListener('DOMContentLoaded', () => {
  const filters = Array.from(document.querySelectorAll('[data-blog-filter]'));
  const items = Array.from(document.querySelectorAll('[data-blog-item]'));
  const empty = document.querySelector('[data-blog-empty]');

  filters.forEach((button) => {
    button.addEventListener('click', () => {
      const category = button.dataset.blogFilter;
      let visible = 0;

      filters.forEach((item) => item.classList.toggle('is-active', item === button));
      items.forEach((item) => {
        const categories = (item.dataset.categories || '').split(' ');
        const show = category === 'all' || categories.includes(category);
        item.hidden = !show;
        if (show) visible += 1;
      });
      if (empty) empty.hidden = visible !== 0;
    });
  });

  document.querySelectorAll('[data-copy-url]').forEach((button) => {
    button.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(button.dataset.copyUrl);
        const oldLabel = button.getAttribute('aria-label');
        button.setAttribute('aria-label', 'Enlace copiado');
        button.classList.add('is-copied');
        window.setTimeout(() => {
          button.setAttribute('aria-label', oldLabel);
          button.classList.remove('is-copied');
        }, 1800);
      } catch (error) {
        window.prompt('Copia este enlace:', button.dataset.copyUrl);
      }
    });
  });
});
