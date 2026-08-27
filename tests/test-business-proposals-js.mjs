import fs from 'node:fs';
import vm from 'node:vm';

function assert(condition, message) {
  if (!condition) {
    process.stderr.write(`FAIL: ${message}\n`);
    process.exit(1);
  }
}

const source = fs.readFileSync(new URL('../wp-content/themes/blocksy-child/assets/js/tmd-business-proposals.js', import.meta.url), 'utf8');
let submitHandler;
let fetchCalls = 0;
let resetCalls = 0;
let appended = {};
let fetchMode = 'success';
let resolvePendingFetch;
const classes = new Set();
const status = {
  textContent: '',
  classList: {
    add(value) { classes.add(value); },
    remove(...values) { values.forEach((value) => classes.delete(value)); }
  }
};
const submit = {
  disabled: false, textContent: 'Enviar solicitud',
  setAttribute() {}, removeAttribute() {}
};
const input = {
  files: [{name: 'brochure.pdf', size: 1024}],
  focused: false,
  focus() { this.focused = true; }
};
const form = {
  querySelector(selector) {
    if (selector === '[data-tmd-form-status]') return status;
    if (selector === '[type="submit"]') return submit;
    if (selector === 'input[name="attachments[]"]') return input;
    return null;
  },
  addEventListener(type, callback) { if (type === 'submit') submitHandler = callback; },
  reportValidity() { return true; },
  reset() { resetCalls += 1; }
};

class FakeFormData {
  append(key, value) { appended[key] = value; }
  set(key, value) { appended[key] = value; }
}

const context = {
  window: {tmdBusinessProposals: {
    ajaxUrl: '/wp-admin/admin-ajax.php', nonce: 'nonce', maxBytes: 2621440, maxFiles: 3,
    invalidFiles: 'Archivos inválidos', networkError: 'Error de red', sendingText: 'Enviando…'
  }},
  document: {querySelectorAll() { return [form]; }},
  FormData: FakeFormData,
  fetch: async () => {
    fetchCalls += 1;
    if (fetchMode === 'network') throw new Error('network');
    if (fetchMode === 'pending') {
      return new Promise((resolve) => { resolvePendingFetch = resolve; });
    }
    if (fetchMode === 'invalid-json') return {ok: true, json: async () => { throw new Error('json'); }};
    if (fetchMode === 'http-error') return {ok: false, json: async () => ({success: false, data: {message: 'Solicitud rechazada.'}})};
    return {ok: true, json: async () => ({success: true, data: {message: 'Solicitud procesada correctamente.'}})};
  }
};

vm.runInNewContext(source, context);
assert(typeof submitHandler === 'function', 'Debe registrar el envío del formulario.');
await submitHandler({preventDefault() {}});
assert(fetchCalls === 1, 'Un archivo válido debe invocar el endpoint una vez.');
assert(appended.nonce === 'nonce', 'Debe enviar el nonce fijo sin sustituir la acción propia del formulario.');
assert(resetCalls === 1 && classes.has('is-success'), 'El éxito debe limpiar y confirmar el formulario.');
assert(submit.disabled === false && submit.textContent === 'Enviar solicitud', 'El botón debe restaurarse.');

input.files = [{name: 'grande.pdf', size: 2621441}];
await submitHandler({preventDefault() {}});
assert(fetchCalls === 1, 'Un archivo excedido no debe invocar el endpoint.');
assert(input.focused && classes.has('is-error'), 'El error de archivo debe anunciarse y enfocar el selector.');

input.files = [
  {name: 'uno.pdf', size: 1000}, {name: 'dos.pdf', size: 1000},
  {name: 'tres.pdf', size: 1000}, {name: 'cuatro.pdf', size: 1000}
];
await submitHandler({preventDefault() {}});
assert(fetchCalls === 1, 'Más de tres archivos no debe invocar el endpoint.');

input.files = [{name: 'uno.pdf', size: 1400000}, {name: 'dos.pdf', size: 1400000}];
await submitHandler({preventDefault() {}});
assert(fetchCalls === 1, 'Una suma superior a 2.5 MB no debe invocar el endpoint.');

input.files = [{name: 'uno.pdf', size: 1000}, {name: 'dos.png', size: 1000}];
fetchMode = 'http-error';
await submitHandler({preventDefault() {}});
assert(fetchCalls === 2 && resetCalls === 1, 'Un error HTTP no debe limpiar el formulario.');
assert(status.textContent === 'Solicitud rechazada.' && classes.has('is-error'), 'Un error HTTP debe anunciar su mensaje.');
assert(submit.disabled === false && submit.textContent === 'Enviar solicitud', 'El botón debe restaurarse tras error HTTP.');

fetchMode = 'network';
await submitHandler({preventDefault() {}});
assert(fetchCalls === 3 && resetCalls === 1 && status.textContent === 'Error de red', 'Un error de red debe conservar campos y anunciarse.');

fetchMode = 'invalid-json';
await submitHandler({preventDefault() {}});
assert(fetchCalls === 4 && resetCalls === 1 && status.textContent === 'Error de red', 'JSON inválido debe ser recuperable y conservar campos.');

fetchMode = 'pending';
const firstSubmit = submitHandler({preventDefault() {}});
await Promise.resolve();
await submitHandler({preventDefault() {}});
assert(fetchCalls === 5, 'Un segundo envío concurrente no debe crear otra petición.');
resolvePendingFetch({ok: true, json: async () => ({success: true, data: {message: 'Solicitud procesada correctamente.'}})});
await firstSubmit;
assert(resetCalls === 2 && submit.disabled === false, 'El envío pendiente debe finalizar y restaurar el formulario.');

process.stdout.write('OK: JavaScript de propuestas empresariales\n');
