(function () {
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

  const cardMatches = (card, filters) => {
    const values = card?.filters || {};
    return exactMatch(values.brand, filters.brand)
      && exactMatch(values.category, filters.category)
      && exactMatch(values.subcategory, filters.subcategory)
      && rangeMatch(values.collapsedHeight, filters.collapsedHeight)
      && rangeMatch(values.liftHeight, filters.liftHeight)
      && exactMatch(values.condition, filters.condition)
      && exactMatch(values.operator, filters.operator)
      && exactMatch(values.reach, filters.reach)
      && exactMatch(values.voltage, filters.voltage)
      && exactMatch(values.capacity, filters.capacity);
  };

  const pageItems = (items, page, perPage) => {
    const firstVisible = (page - 1) * perPage;
    return items.slice(firstVisible, firstVisible + perPage);
  };

  const parsePayload = (text) => {
    try {
      const payload = JSON.parse(String(text || ''));
      return Array.isArray(payload?.items) ? payload.items : null;
    } catch (error) {
      return null;
    }
  };

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { normalize, exactMatch, rangeMatch, cardMatches, pageItems, parsePayload };
  }

  if (typeof document === 'undefined') {
    return;
  }

  document.addEventListener('DOMContentLoaded', () => {
    const prepareImage = (image) => {
    const showFallback = () => {
      image.parentElement?.classList.add('is-missing');
      image.remove();
    };

    image.addEventListener('error', showFallback, { once: true });
    if (image.complete && image.naturalWidth === 0) {
      showFallback();
    }
    };

    document.querySelectorAll('.tmd-api-card img, .tmd-api-detail-image img').forEach(prepareImage);

  const form = document.querySelector('.tmd-api-filters');
  const results = document.querySelector('[data-tmd-api-results]');

  if (!form || !results) {
    return;
  }

  const grid = results.querySelector('.tmd-api-grid');
  const payloadNode = results.querySelector('[data-tmd-api-items]');
  const statusCount = results.querySelector('[data-tmd-api-status] strong');
  const emptyMessage = results.querySelector('[data-tmd-api-empty]');
  const pagination = results.querySelector('[data-tmd-api-pagination]');
  const perPage = Math.max(1, Number.parseInt(results.dataset.apiPerPage || '12', 10));
  const resultLabel = results.dataset.apiLabel || 'equipos';
  const categorySelect = form.querySelector('select[name="api_categoria"]');
  const subcategorySelect = form.querySelector('select[name="api_subcategoria"]');
  let currentPage = 1;

  const cards = parsePayload(payloadNode?.textContent || '');

  if (!grid || !cards) {
    return;
  }

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

  const createCard = (card) => {
    const article = document.createElement('article');
    article.className = String(card?.classes?.card || 'tmd-api-card');

    const filters = card?.filters || {};
    article.dataset.apiBrand = String(filters.brand || '');
    article.dataset.apiCategory = String(filters.category || '');
    article.dataset.apiSubcategory = String(filters.subcategory || '');
    article.dataset.apiCondition = String(filters.condition || '');
    article.dataset.apiCollapsedHeight = String(filters.collapsedHeight || '');
    article.dataset.apiLiftHeight = String(filters.liftHeight || '');
    article.dataset.apiOperator = String(filters.operator || '');
    article.dataset.apiReach = String(filters.reach || '');
    article.dataset.apiVoltage = String(filters.voltage || '');
    article.dataset.apiCapacity = String(filters.capacity || '');

    const imageLink = document.createElement('a');
    imageLink.className = String(card?.classes?.image || '');
    imageLink.href = String(card?.detailUrl || '');
    imageLink.setAttribute('aria-label', `Ver ${String(card?.title || '')}`);
    if (card?.image) {
      const image = document.createElement('img');
      image.src = String(card.image);
      image.alt = String(card?.title || '');
      image.loading = 'lazy';
      imageLink.append(image);
      prepareImage(image);
    }
    article.append(imageLink);

    const body = document.createElement('div');
    body.className = String(card?.classes?.body || '');
    const tags = document.createElement('div');
    tags.className = 'tmd-api-tags';
    (Array.isArray(card?.tags) ? card.tags : []).forEach((tag) => {
      const tagNode = document.createElement('span');
      tagNode.className = String(tag?.className || '');
      tagNode.textContent = String(tag?.label || '');
      tags.append(tagNode);
    });
    body.append(tags);

    const heading = document.createElement('h3');
    const titleLink = document.createElement('a');
    titleLink.href = String(card?.detailUrl || '');
    titleLink.textContent = String(card?.title || '');
    heading.append(titleLink);
    body.append(heading);

    const specs = document.createElement('div');
    specs.className = 'tmd-api-specs';
    (Array.isArray(card?.specs) ? card.specs : []).forEach((spec) => {
      const row = document.createElement('div');
      const label = document.createElement('span');
      const value = document.createElement('strong');
      label.textContent = String(spec?.label || '');
      value.textContent = String(spec?.value || '');
      row.append(label, value);
      specs.append(row);
    });
    body.append(specs);

    const actions = document.createElement('div');
    actions.className = 'tmd-api-actions';
    const detailLink = document.createElement('a');
    detailLink.className = 'is-primary';
    detailLink.href = String(card?.detailUrl || '');
    detailLink.textContent = 'Ver ficha';
    const contactLink = document.createElement('a');
    contactLink.href = String(card?.contactUrl || '');
    contactLink.textContent = 'Cotizar';
    actions.append(detailLink, contactLink);
    body.append(actions);
    article.append(body);

    return article;
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

  function render(resetPage = true, preserveGrid = false) {
    const matchingCards = cards.filter((card) => cardMatches(card, values()));

    if (resetPage) {
      currentPage = 1;
    }

    const totalPages = Math.max(1, Math.ceil(matchingCards.length / perPage));
    currentPage = Math.min(currentPage, totalPages);
    const visibleCards = pageItems(matchingCards, currentPage, perPage);
    if (!preserveGrid) {
      grid.replaceChildren(...visibleCards.map(createCard));
    }

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
  render(true, true);
  });
})();
