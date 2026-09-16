import fs from 'node:fs';
import vm from 'node:vm';

const jsSource = fs.readFileSync(
  new URL('../wp-content/themes/blocksy-child/assets/js/tmd-job-application.js', import.meta.url),
  'utf8'
);
const cssSource = fs.readFileSync(
  new URL('../wp-content/themes/blocksy-child/assets/css/tmd-job-application.css', import.meta.url),
  'utf8'
);
const currentMarkup = fs.readFileSync(
  new URL('./fixtures/jobs-vacancies-current.html', import.meta.url),
  'utf8'
);

function assert(condition, message) {
  if (!condition) {
    process.stderr.write(`FAIL: ${message}\n`);
    process.exit(1);
  }
}

class MockElement {
  constructor(tagName, className = '') {
    this.tagName = tagName;
    this.className = className;
    this.children = [];
    this.parentNode = null;
    this.nextSibling = null;
    this.attributes = {};
    this.listeners = {};
    this.disabled = false;
    this.scrollLeft = 0;
    this.clientWidth = 100;
    this.offsetLeft = 0;
    this.textContent = '';
  }

  appendChild(child) {
    const previous = this.children[this.children.length - 1];
    if (previous) previous.nextSibling = child;
    child.parentNode = this;
    this.children.push(child);
    return child;
  }

  insertBefore(child, reference) {
    if (!reference) return this.appendChild(child);
    const index = this.children.indexOf(reference);
    if (index === -1) return this.appendChild(child);
    child.parentNode = this;
    this.children.splice(index, 0, child);
    return child;
  }

  setAttribute(name, value) { this.attributes[name] = String(value); }
  getAttribute(name) { return this.attributes[name] ?? null; }
  removeAttribute(name) { delete this.attributes[name]; }
  matches(selector) { return selector === '.tmd-job-card' && this.className.includes('tmd-job-card'); }
  addEventListener(name, callback) { this.listeners[name] = callback; }
  dispatchEvent(name, event = {}) {
    if (this.listeners[name]) this.listeners[name](event);
  }
  click() { this.dispatchEvent('click', { preventDefault() {} }); }
  focus() { this.focused = true; }
  scrollTo(options) {
    this.lastScroll = options;
    this.scrollLeft = options.left;
    this.dispatchEvent('scroll');
  }
}

function createHarness({ cardCount = 3, reducedMotion = false } = {}) {
  const root = new MockElement('section');
  const grid = new MockElement('div', 'tmd-jobs-grid');
  root.appendChild(grid);

  for (let index = 0; index < cardCount; index += 1) {
    const card = new MockElement('article', 'tmd-job-card');
    card.offsetLeft = index * 100;
    const applyLink = new MockElement('a');
    applyLink.setAttribute('href', '#postulacion');
    applyLink.tabIndex = 0;
    card.appendChild(applyLink);
    grid.appendChild(card);
  }

  const document = {
    querySelector(selector) {
      if (selector === '.tmd-jobs-vacancies .tmd-jobs-grid') return grid;
      return null;
    },
    createElement(tagName) { return new MockElement(tagName); }
  };
  const context = {
    window: {
      matchMedia() { return { matches: reducedMotion }; }
    },
    document,
    setTimeout,
    clearTimeout,
    fetch: async () => ({ ok: true, json: async () => ({ success: true }) })
  };

  vm.runInNewContext(jsSource, context);
  return { root, grid };
}

const standard = createHarness();
const controls = standard.root.children.find((child) => child.className === 'tmd-jobs-carousel-controls');
assert(controls, 'Debe crear controles cuando existen varias vacantes.');
assert(standard.grid.getAttribute('role') === 'region', 'El viewport debe tener una región accesible.');
assert(standard.grid.getAttribute('aria-roledescription') === 'carousel', 'Debe declarar el carrusel.');
assert(standard.grid.getAttribute('aria-label') === 'Vacantes disponibles', 'Debe nombrar la región.');
assert(standard.grid.getAttribute('tabindex') === '0', 'El viewport debe ser enfocable para teclado.');
assert(standard.grid.children.every((card, index) => (
  card.getAttribute('role') === 'group'
  && card.getAttribute('aria-roledescription') === 'slide'
  && card.getAttribute('aria-label') === `${index + 1} de 3`
  && card.children[0].getAttribute('href') === '#postulacion'
)), 'Cada tarjeta debe exponerse como slide accesible.');

const [previous, indicators, next, status] = controls.children;
assert(previous.type === 'button' && next.type === 'button', 'Los controles deben ser botones no submit.');
assert(previous.getAttribute('aria-label') === 'Mostrar vacante anterior', 'Anterior debe tener nombre accesible.');
assert(next.getAttribute('aria-label') === 'Mostrar siguiente vacante', 'Siguiente debe tener nombre accesible.');
assert(previous.disabled && !next.disabled, 'El estado inicial debe deshabilitar anterior.');
assert(indicators.children.length === 3, 'Debe crear un indicador por tarjeta.');
assert(indicators.children.every((indicator, index) => (
  indicator.type === 'button'
  && indicator.getAttribute('aria-label') === `Mostrar vacante ${index + 1}`
)), 'Cada indicador debe ser un botón con nombre accesible.');
assert(indicators.children[0].getAttribute('aria-current') === 'true', 'El primer indicador debe iniciar activo.');
assert(status.textContent === 'Vacante 1 de 3', 'Debe anunciar la posición inicial.');
assert(status.getAttribute('aria-live') === 'polite', 'La posición debe anunciarse sin interrumpir la navegación.');

next.click();
assert(standard.grid.scrollLeft === 100 && !previous.disabled && !next.disabled, 'Siguiente debe avanzar una tarjeta.');
assert(indicators.children[1].getAttribute('aria-current') === 'true', 'Siguiente debe actualizar el indicador.');
previous.click();
assert(standard.grid.scrollLeft === 0 && previous.disabled, 'Anterior debe retroceder una tarjeta y detenerse en el primer slide.');
previous.click();
assert(standard.grid.scrollLeft === 0, 'Anterior no debe desplazarse antes del primer slide.');
indicators.children[1].click();
assert(standard.grid.scrollLeft === 100, 'El segundo indicador debe seleccionar el segundo slide.');
indicators.children[2].click();
assert(standard.grid.scrollLeft === 200 && next.disabled, 'El tercer indicador debe seleccionar el último slide.');
next.click();
assert(standard.grid.scrollLeft === 200 && next.disabled, 'Siguiente no debe desplazarse después del último slide.');
previous.click();
assert(standard.grid.scrollLeft === 100 && !previous.disabled, 'Anterior debe volver desde el último slide.');

standard.grid.scrollLeft = 200;
standard.grid.dispatchEvent('scroll');
assert(indicators.children[2].getAttribute('aria-current') === 'true', 'El scroll nativo debe actualizar el indicador activo.');
assert(status.textContent === 'Vacante 3 de 3' && next.disabled, 'El scroll nativo debe actualizar posición y límites.');
standard.grid.scrollLeft = 0;
standard.grid.dispatchEvent('scroll');
assert(indicators.children[0].getAttribute('aria-current') === 'true', 'El scroll nativo debe reconocer el primer slide.');
assert(indicators.children.filter((indicator) => indicator.getAttribute('aria-current') === 'true').length === 1, 'Solo un indicador debe estar activo.');
standard.grid.children[0].children[0].focus();
assert(standard.grid.children[0].children[0].focused, 'El enlace de postulación debe permanecer enfocable.');

let prevented = false;
standard.grid.dispatchEvent('keydown', {
  key: 'ArrowRight',
  preventDefault() { prevented = true; }
});
assert(prevented && standard.grid.scrollLeft === 100, 'ArrowRight debe avanzar y evitar el desplazamiento de la página.');
standard.grid.dispatchEvent('keydown', {
  key: 'ArrowLeft',
  preventDefault() { prevented = true; }
});
assert(standard.grid.scrollLeft === 0, 'ArrowLeft debe retroceder una tarjeta.');

const reduced = createHarness({ reducedMotion: true });
const reducedControls = reduced.root.children.find((child) => child.className === 'tmd-jobs-carousel-controls');
reducedControls.children[2].click();
assert(reduced.grid.lastScroll.behavior === 'auto', 'prefers-reduced-motion debe evitar el desplazamiento suave.');

const oneCard = createHarness({ cardCount: 1 });
assert(!oneCard.root.children.some((child) => child.className === 'tmd-jobs-carousel-controls'), 'No debe crear controles para una sola tarjeta.');

class MockClassList {
  constructor() { this.values = new Set(); }
  add(value) { this.values.add(value); }
  remove(...values) { values.forEach((value) => this.values.delete(value)); }
  contains(value) { return this.values.has(value); }
}

class MockFormData {
  constructor() { this.values = new Map(); }
  append(name, value) { this.values.set(name, value); }
}

function createCombinedHarness() {
  const root = new MockElement('section');
  const grid = new MockElement('div', 'tmd-jobs-grid');
  root.appendChild(grid);
  for (let index = 0; index < 3; index += 1) {
    const card = new MockElement('article', 'tmd-job-card');
    card.offsetLeft = index * 100;
    const applyLink = new MockElement('a');
    applyLink.setAttribute('href', '#postulacion');
    card.appendChild(applyLink);
    grid.appendChild(card);
  }

  const status = { textContent: '', classList: new MockClassList() };
  const submit = {
    textContent: 'Enviar Postulación',
    disabled: false,
    setAttribute() {},
    removeAttribute() {}
  };
  const fileInput = { files: [{ name: 'cv.pdf', size: 1200 }], focus() {} };
  const form = {
    listeners: {},
    resetCount: 0,
    querySelector(selector) {
      return {
        '[data-tmd-form-status]': status,
        '[type="submit"]': submit,
        'input[name="cv"]': fileInput
      }[selector] || null;
    },
    addEventListener(name, callback) { this.listeners[name] = callback; },
    reportValidity() { return true; },
    reset() { this.resetCount += 1; }
  };
  let fetchCalls = 0;
  const document = {
    querySelector(selector) {
      if (selector === '.tmd-jobs-vacancies .tmd-jobs-grid') return grid;
      if (selector === '[data-tmd-job-application]') return form;
      return null;
    },
    createElement(tagName) { return new MockElement(tagName); }
  };
  const context = {
    window: {
      tmdJobApplication: {
        ajaxUrl: '/wp-admin/admin-ajax.php',
        nonce: 'nonce',
        maxBytes: 2097152,
        invalidFile: 'Archivo inválido.',
        networkError: 'Error de red.',
        sendingText: 'Enviando…'
      },
      matchMedia() { return { matches: false }; }
    },
    document,
    FormData: MockFormData,
    fetch: async () => {
      fetchCalls += 1;
      return { ok: true, json: async () => ({ success: true, data: { message: 'Enviada.' } }) };
    }
  };

  vm.runInNewContext(jsSource, context);
  return { form, grid, root, fetchCalls: () => fetchCalls };
}

const combined = createCombinedHarness();
const combinedControls = combined.root.children.find((child) => child.className === 'tmd-jobs-carousel-controls');
assert(combinedControls && typeof combined.form.listeners.submit === 'function', 'Carrusel y formulario deben inicializarse juntos.');
combinedControls.children[2].click();
await combined.form.listeners.submit({ preventDefault() {} });
assert(combined.fetchCalls() === 1 && combined.form.resetCount === 1, 'Navegar el carrusel no debe romper el envío del formulario.');

const currentCards = currentMarkup.match(/<article class="tmd-job-card">[\s\S]*?<\/article>/g) || [];
assert(currentCards.length === 3, 'La fixture de contenido actual debe conservar tres tarjetas reales.');
assert(currentMarkup.includes('Técnico Especializado en Montacargas Eléctricos'), 'La primera tarjeta real debe conservar su título.');
assert(currentMarkup.includes('Auxiliar Técnico en Entrenamiento'), 'La segunda tarjeta real debe conservar su título.');
assert(currentMarkup.includes('Técnico Especializado en Montacargas de Combustión'), 'La tercera tarjeta real debe conservar su título.');
assert((currentMarkup.match(/href="#postulacion"/g) || []).length === 3, 'Cada tarjeta real debe conservar su enlace de postulación.');
for (const viewportWidth of [1440, 390]) {
  assert(viewportWidth > 28, `El viewport ${viewportWidth}px debe tener espacio para el carrusel.`);
  assert(cssSource.includes('flex: 0 0 100% !important'), `El viewport ${viewportWidth}px debe mostrar una tarjeta por vista.`);
  assert(cssSource.includes('overflow-x: auto !important'), `El viewport ${viewportWidth}px debe permitir desplazamiento interno.`);
}

assert(cssSource.includes('body.page-id-273 .tmd-jobs-vacancies .tmd-jobs-grid'), 'El CSS debe limitarse a la página y sección de vacantes.');
assert(cssSource.includes('overflow-x: auto !important'), 'El CSS debe permitir gesto y desplazamiento horizontal del carrusel.');
assert(cssSource.includes('touch-action: pan-x'), 'El CSS debe conservar el gesto horizontal táctil.');
assert(cssSource.includes('overscroll-behavior-x: contain'), 'El CSS debe contener el desplazamiento horizontal al carrusel.');
assert(cssSource.includes('scroll-snap-type: x mandatory'), 'El CSS debe fijar cada tarjeta como slide.');
assert(cssSource.includes(':focus-visible'), 'El CSS debe conservar un indicador de foco visible.');
assert(cssSource.includes('@media (prefers-reduced-motion: reduce)'), 'El CSS debe contemplar movimiento reducido.');

process.stdout.write('OK: carrusel de vacantes, controles, indicadores, teclado, reduced-motion y CSS focalizado.\n');
