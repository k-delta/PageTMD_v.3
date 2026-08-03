import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(
  new URL('../wp-content/themes/blocksy-child/assets/js/tmd-pqr.js', import.meta.url),
  'utf8'
);

function assert(condition, message) {
  if (!condition) {
    process.stderr.write(`FAIL: ${message}\n`);
    process.exit(1);
  }
}

class MockClassList {
  constructor(initial = []) {
    this.values = new Set(initial);
  }

  add(value) { this.values.add(value); }
  remove(...values) { values.forEach((value) => this.values.delete(value)); }
  contains(value) { return this.values.has(value); }
  toggle(value, force) {
    if (force) {
      this.values.add(value);
    } else {
      this.values.delete(value);
    }
  }
}

function mockElement(attributes = {}) {
  return {
    attributes: { ...attributes },
    listeners: {},
    classList: new MockClassList(attributes.classList || []),
    textContent: attributes.textContent || '',
    disabled: false,
    setAttribute(name, value) { this.attributes[name] = String(value); },
    removeAttribute(name) { delete this.attributes[name]; },
    getAttribute(name) { return this.attributes[name] ?? null; },
    addEventListener(name, callback) { this.listeners[name] = callback; }
  };
}

class MockFormData {
  constructor(form) {
    this.values = new Map(Object.entries(form.formValues));
  }

  append(name, value) { this.values.set(name, value); }
  has(name) { return this.values.has(name); }
}

function createHarness(fetchImpl, config = true) {
  const status = mockElement();
  const submit = mockElement({ textContent: 'Enviar PQR' });
  const requestType = { value: 'Peticion' };
  const formType = { value: 'pqr' };
  const choices = ['Peticion', 'Queja', 'Reclamo', 'Reembolso'].map((value) =>
    mockElement({ 'data-tmd-choice': value, classList: value === 'Peticion' ? ['is-active'] : [] })
  );
  const form = {
    listeners: {},
    resetCount: 0,
    formValues: { form_type: 'pqr', request_type: 'Peticion', email: 'ana@example.com' },
    querySelector(selector) {
      return {
        'input[name="form_type"]': formType,
        '[data-tmd-form-status]': status,
        '[type="submit"]': submit,
        'input[name="request_type"]': requestType
      }[selector] || null;
    },
    querySelectorAll(selector) { return selector === '[data-tmd-choice]' ? choices : []; },
    addEventListener(name, callback) { this.listeners[name] = callback; },
    reportValidity() { return true; },
    reset() {
      this.resetCount += 1;
      requestType.value = 'Peticion';
    }
  };
  const context = {
    window: config ? {
      tmdPqr: {
        ajaxUrl: 'https://example.com/wp-admin/admin-ajax.php',
        nonce: 'nonce',
        networkError: 'Error de red.',
        sendingText: 'Procesando…'
      }
    } : {},
    document: { querySelectorAll() { return [form]; } },
    FormData: MockFormData,
    fetch: fetchImpl
  };
  vm.runInNewContext(source, context);
  return { form, status, submit, requestType, choices };
}

async function submit(harness) {
  return harness.form.listeners.submit({ preventDefault() {} });
}

let fetchCalls = 0;
const successHarness = createHarness(async () => {
  fetchCalls += 1;
  return { ok: true, json: async () => ({ success: true, data: { message: 'Procesada.' } }) };
});
await submit(successHarness);
assert(fetchCalls === 1, 'Éxito debe ejecutar una petición.');
assert(successHarness.form.resetCount === 1, 'Éxito debe limpiar formulario.');
assert(successHarness.requestType.value === 'Peticion', 'Éxito debe restablecer Peticion.');
assert(successHarness.status.textContent === 'Procesada.', 'Éxito debe comunicar estado.');
assert(successHarness.status.classList.contains('is-success'), 'Éxito debe usar clase success.');
assert(!successHarness.submit.disabled && successHarness.submit.textContent === 'Enviar PQR', 'Éxito debe restaurar botón.');

const errorHarness = createHarness(async () => ({
  ok: false,
  json: async () => ({ success: false, data: { message: 'Entrada inválida.' } })
}));
await submit(errorHarness);
assert(errorHarness.form.resetCount === 0, 'Error HTTP debe conservar campos.');
assert(errorHarness.status.classList.contains('is-error'), 'Error HTTP debe comunicar error.');
assert(!errorHarness.submit.disabled, 'Error HTTP debe habilitar reintento.');

const invalidJsonHarness = createHarness(async () => ({
  ok: true,
  json: async () => { throw new Error('invalid json'); }
}));
await submit(invalidJsonHarness);
assert(invalidJsonHarness.form.resetCount === 0, 'JSON inválido debe conservar campos.');
assert(invalidJsonHarness.status.textContent === 'Error de red.', 'JSON inválido debe mostrar error recuperable.');
assert(!invalidJsonHarness.submit.disabled, 'JSON inválido debe restaurar botón.');

let resolvePending;
fetchCalls = 0;
const pendingHarness = createHarness(() => {
  fetchCalls += 1;
  return new Promise((resolve) => { resolvePending = resolve; });
});
const firstSubmit = submit(pendingHarness);
await submit(pendingHarness);
assert(fetchCalls === 1, 'Petición pendiente debe bloquear doble envío.');
resolvePending({ ok: true, json: async () => ({ success: true, data: { message: 'Procesada.' } }) });
await firstSubmit;

const noConfigHarness = createHarness(async () => ({}), false);
assert(!noConfigHarness.form.listeners.submit, 'Configuración AJAX ausente debe terminar sin registrar submit ni lanzar error.');

process.stdout.write('OK: éxito, error, JSON inválido, doble envío y configuración ausente en JS PQR.\n');
