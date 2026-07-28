document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.tmd-api-card img, .tmd-api-detail-image img').forEach((image) => {
    const showFallback = () => {
      image.parentElement?.classList.add('is-missing');
      image.remove();
    };

    image.addEventListener('error', showFallback, { once: true });
    if (image.complete && image.naturalWidth === 0) {
      showFallback();
    }
  });

  const form = document.querySelector('.tmd-api-filters');
  const results = document.querySelector('[data-tmd-api-results]');

  if (!form || !results) {
    return;
  }

  const cards = Array.from(results.querySelectorAll('.tmd-api-card'));
  const statusCount = results.querySelector('[data-tmd-api-status] strong');
  const emptyMessage = results.querySelector('[data-tmd-api-empty]');
  const pagination = results.querySelector('[data-tmd-api-pagination]');
  const perPage = Math.max(1, Number.parseInt(results.dataset.apiPerPage || '12', 10));
  const resultLabel = results.dataset.apiLabel || 'equipos';
  const categorySelect = form.querySelector('select[name="api_categoria"]');
  const subcategorySelect = form.querySelector('select[name="api_subcategoria"]');
  let currentPage = 1;

  const normalize = (value) => String(value || '').trim().toLocaleLowerCase('es');

  const exactMatch = (actual, expected) => {
    return !expected || normalize(actual) === normalize(expected);
  };

  const rangeMatch = (actual, range) => {
    if (!range) {
      return true;
    }

    const height = Number.parseFloat(actual);
    if (!Number.isFinite(height) || height <= 0) {
      return false;
    }

    const [minimum, maximum] = range.split('-', 2).map(Number);
    return height >= minimum && (maximum <= 0 || height < maximum);
  };

  const values = () => {
    const data = new FormData(form);

    return {
      brand: data.get('api_marca') || '',
      category: data.get('api_categoria') || '',
      subcategory: data.get('api_subcategoria') || '',
      collapsedHeight: data.get('api_altura_colapsada') || '',
      liftHeight: data.get('api_altura_levante') || '',
      condition: data.get('api_condicion') || '',
      operator: data.get('api_operario') || '',
      reach: data.get('api_reach') || '',
      voltage: data.get('api_voltaje') || '',
      capacity: data.get('api_capacidad') || '',
    };
  };

  const cardMatches = (card, filters) => {
    return exactMatch(card.dataset.apiBrand, filters.brand)
      && exactMatch(card.dataset.apiCategory, filters.category)
      && exactMatch(card.dataset.apiSubcategory, filters.subcategory)
      && rangeMatch(card.dataset.apiCollapsedHeight, filters.collapsedHeight)
      && rangeMatch(card.dataset.apiLiftHeight, filters.liftHeight)
      && exactMatch(card.dataset.apiCondition, filters.condition)
      && exactMatch(card.dataset.apiOperator, filters.operator)
      && exactMatch(card.dataset.apiReach, filters.reach)
      && exactMatch(card.dataset.apiVoltage, filters.voltage)
      && exactMatch(card.dataset.apiCapacity, filters.capacity);
  };

  const updateSubcategoryOptions = () => {
    if (!categorySelect || !subcategorySelect) {
      return;
    }

    const category = categorySelect.value;
    Array.from(subcategorySelect.options).forEach((option) => {
      if (!option.value) {
        return;
      }
      const visible = !category || exactMatch(option.dataset.apiCategory, category);
      option.hidden = !visible;
      option.disabled = !visible;
    });

    if (subcategorySelect.selectedOptions[0]?.disabled) {
      subcategorySelect.value = '';
    }
  };

  const syncUrl = () => {
    const url = new URL(form.action, window.location.origin);
    const data = new FormData(form);

    data.forEach((value, key) => {
      if (value) {
        url.searchParams.set(key, value);
      }
    });

    window.history.replaceState({}, '', `${url.pathname}${url.search}`);
  };

  const renderPagination = (totalPages) => {
    if (!pagination) {
      return;
    }

    pagination.replaceChildren();
    pagination.hidden = totalPages <= 1;

    for (let page = 1; page <= totalPages; page += 1) {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = String(page);
      button.className = page === currentPage ? 'is-current' : '';
      button.setAttribute('aria-label', `Página ${page}`);
      if (page === currentPage) {
        button.setAttribute('aria-current', 'page');
      }
      button.addEventListener('click', () => {
        currentPage = page;
        render(false);
        results.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
      pagination.append(button);
    }
  };

  function render(resetPage = true) {
    const matchingCards = cards.filter((card) => cardMatches(card, values()));

    if (resetPage) {
      currentPage = 1;
    }

    const totalPages = Math.max(1, Math.ceil(matchingCards.length / perPage));
    currentPage = Math.min(currentPage, totalPages);
    const firstVisible = (currentPage - 1) * perPage;
    const lastVisible = firstVisible + perPage;

    cards.forEach((card) => {
      card.hidden = true;
    });

    matchingCards.forEach((card, index) => {
      card.hidden = index < firstVisible || index >= lastVisible;
    });

    if (statusCount) {
      const label = matchingCards.length === 1
        ? (resultLabel === 'baterías' ? 'batería disponible' : 'equipo disponible')
        : `${resultLabel} disponibles`;
      statusCount.textContent = `${matchingCards.length} ${label}`;
    }

    if (emptyMessage) {
      emptyMessage.hidden = matchingCards.length !== 0;
    }

    renderPagination(matchingCards.length ? totalPages : 0);
    syncUrl();
  }

  form.addEventListener('change', () => {
    updateSubcategoryOptions();
    render(true);
  });
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    render(true);
  });

  const clearLink = form.querySelector('a[href]');
  clearLink?.addEventListener('click', (event) => {
    event.preventDefault();
    form.reset();
    updateSubcategoryOptions();
    render(true);
  });

  window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search);
    form.querySelectorAll('select[name]').forEach((select) => {
      select.value = params.get(select.name) || '';
    });
    updateSubcategoryOptions();
    render(true);
  });

  updateSubcategoryOptions();
  render(true);
});
