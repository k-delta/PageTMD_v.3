import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(
  new URL('../wp-content/themes/blocksy-child/assets/js/tmd-job-application.js', import.meta.url),
  'utf8'
);

function assert(condition, message) {
  if (!condition) {
    process.stderr.write(`FAIL: ${message}\n`);
    process.exit(1);
  }
}

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

function createHarness(fetchImpl, options = {}) {
  const status = { textContent: '', classList: new MockClassList() };
  const submit = {
    textContent: 'Enviar Postulación', disabled: false, attributes: {},
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; }
  };
  const fileInput = {
    files: options.file === null ? [] : [options.file || { name: 'cv.pdf', size: 1200 }],
    focused: false,
    focus() { this.focused = true; }
  };
  const form = {
    listeners: {}, resetCount: 0,
    addEventListener(name, callback) { this.listeners[name] = callback; },
    querySelector(selector) {
      return {
        '[data-tmd-form-status]': status,
        '[type="submit"]': submit,
        'input[name="cv"]': fileInput
      }[selector] || null;
    },
    reportValidity() { return options.validity !== false; },
    reset() { this.resetCount += 1; }
  };
  const context = {
    window: options.noConfig ? {} : { tmdJobApplication: {
      ajaxUrl: '/wp-admin/admin-ajax.php', nonce: 'nonce', maxBytes: 2097152,
      invalidFile: 'Archivo inválido.', networkError: 'Error de red.', sendingText: 'Enviando…'
    } },
    document: { querySelector() { return form; } },
    FormData: MockFormData,
    fetch: fetchImpl
  };
  vm.runInNewContext(source, context);
  return { form, status, submit, fileInput };
}

const submit = (harness) => harness.form.listeners.submit({ preventDefault() {} });

let calls = 0;
const success = createHarness(async (url, request) => {
  calls += 1;
  assert(request.body.values.get('action') === 'tmd_job_application', 'Debe enviar action permitida.');
  assert(request.body.values.get('nonce') === 'nonce', 'Debe enviar nonce.');
  return { ok: true, json: async () => ({ success: true, data: { message: 'Enviada.' } }) };
});
await submit(success);
assert(calls === 1 && success.form.resetCount === 1, 'Éxito debe enviar y limpiar.');
assert(success.status.classList.contains('is-success'), 'Éxito debe anunciar estado.');
assert(!success.submit.disabled && success.submit.textContent === 'Enviar Postulación', 'Éxito debe restaurar botón.');

const invalid = createHarness(async () => ({}), { file: { name: 'virus.exe', size: 10 } });
await submit(invalid);
assert(invalid.fileInput.focused && invalid.status.classList.contains('is-error'), 'Archivo inválido debe bloquear envío y recibir foco.');

const error = createHarness(async () => ({ ok: false, json: async () => ({ success: false, data: { message: 'Rechazada.' } }) }));
await submit(error);
assert(error.form.resetCount === 0 && error.status.classList.contains('is-error'), 'Error debe conservar campos.');

const network = createHarness(async () => { throw new Error('network'); });
await submit(network);
assert(network.form.resetCount === 0 && network.status.textContent === 'Error de red.', 'Red fallida debe conservar campos.');

let resolvePending;
calls = 0;
const pending = createHarness(() => {
  calls += 1;
  return new Promise((resolve) => { resolvePending = resolve; });
});
const first = submit(pending);
await submit(pending);
assert(calls === 1, 'Doble envío debe bloquear segunda petición.');
resolvePending({ ok: true, json: async () => ({ success: true, data: { message: 'Enviada.' } }) });
await first;

const noConfig = createHarness(async () => ({}), { noConfig: true });
assert(!noConfig.form.listeners.submit, 'Sin configuración no debe registrar controlador.');

process.stdout.write('OK: archivo, payload, éxito, error, red, doble envío y configuración ausente en JS postulación.\n');
