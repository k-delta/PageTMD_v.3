const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const assetPath = path.join(
  __dirname,
  '..',
  'wp-content/themes/blocksy-child/assets/js/tmd-commercial-landing-thank-you-redirect.js'
);
const source = fs.existsSync(assetPath) ? fs.readFileSync(assetPath, 'utf8') : '';

function createHarness(redirects = {
  1557: '/gracias-baterias/',
  1556: '/gracias-montacargas/',
}) {
  const listeners = [];
  const navigations = [];
  const document = {
    addEventListener(name, callback) {
      listeners.push({ name, callback });
    },
  };
  const window = {
    tmdCommercialThankYouRedirects: redirects,
    location: {
      assign(destination) {
        navigations.push(destination);
      },
    },
  };

  vm.runInNewContext(source, { document, window }, { filename: assetPath });

  return {
    listeners,
    navigations,
    dispatch(name, detail) {
      for (const listener of listeners) {
        if (listener.name === name) {
          listener.callback({ detail });
        }
      }
    },
  };
}

const harness = createHarness();
assert.deepEqual(harness.listeners.map(({ name }) => name), ['wpcf7mailsent']);

harness.dispatch('wpcf7mailsent', {
  contactFormId: '1557',
  inputs: [{ name: 'email', value: 'private@example.test' }],
});
assert.deepEqual(harness.navigations, ['/gracias-baterias/']);
assert.equal(harness.navigations[0].includes('private@example.test'), false);

harness.dispatch('wpcf7mailsent', { contactFormId: 1556 });
assert.deepEqual(harness.navigations, ['/gracias-baterias/', '/gracias-montacargas/']);

for (const name of ['wpcf7invalid', 'wpcf7spam', 'wpcf7mailfailed', 'wpcf7aborted']) {
  harness.dispatch(name, { contactFormId: 1557 });
}
assert.equal(harness.navigations.length, 2, 'los eventos de error no deben navegar');

harness.dispatch('wpcf7mailsent');
harness.dispatch('wpcf7mailsent', {});
harness.dispatch('wpcf7mailsent', { contactFormId: 9999 });
assert.equal(harness.navigations.length, 2, 'detail ausente e IDs desconocidos no deben navegar');

for (const unsafePath of [
  'https://evil.test/collect',
  '//evil.test/collect',
  '/gracias-baterias/?email=private@example.test',
  '/gracias-baterias/#contacto',
]) {
  const unsafeHarness = createHarness({ 1557: unsafePath });
  unsafeHarness.dispatch('wpcf7mailsent', { contactFormId: 1557 });
  assert.deepEqual(unsafeHarness.navigations, [], `el destino inseguro no debe navegar: ${unsafePath}`);
}

console.log('OK: redirección de agradecimiento solo tras envío exitoso de CF7.');
