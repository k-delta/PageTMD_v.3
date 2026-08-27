'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {
  eventBelongsToForm,
  queryContext,
} = require('../wp-content/themes/blocksy-child/assets/js/tmd-contact-conversion.js');

assert.deepEqual(
  queryContext('?tmd_cotizacion_id=forklift-42&tmd_tipo_cotizacion=montacargas&tmd_cotizacion=CROWN%20RD%205200'),
  {
    id: 'forklift-42',
    type: 'montacargas',
    typeLabel: 'Equipo',
    title: 'CROWN RD 5200',
  }
);

assert.deepEqual(
  queryContext('?equipo_id=battery-9&tmd_cotizacion_energia=Bater%C3%ADa%2048%20V'),
  {
    id: 'battery-9',
    type: 'bateria',
    typeLabel: 'Energía',
    title: 'Batería 48 V',
  }
);

assert.equal(queryContext('?tmd_cotizacion=Sin%20tipo'), null);
assert.equal(queryContext('?tmd_tipo_cotizacion=montacargas'), null);

const form = {};
assert.equal(eventBelongsToForm({ target: form }, form), true);
assert.equal(eventBelongsToForm({ target: { contains: (node) => node === form } }, form), true);
assert.equal(eventBelongsToForm({ target: { contains: () => false } }, form), false);

const source = fs.readFileSync(
  path.join(__dirname, '../wp-content/themes/blocksy-child/assets/js/tmd-contact-conversion.js'),
  'utf8'
);
const themeRoot = path.join(__dirname, '../wp-content/themes/blocksy-child');
const railTemplate = fs.readFileSync(path.join(themeRoot, 'template-parts/tmd-contact-rail.php'), 'utf8');
const railCss = fs.readFileSync(path.join(themeRoot, 'assets/css/tmd-contact-rail.css'), 'utf8');
const inventoryPhp = fs.readFileSync(path.join(themeRoot, 'inc/tmd-inventory-api.php'), 'utf8');
const equipmentFilterForm = inventoryPhp.slice(
  inventoryPhp.indexOf('function tmd_inventory_api_filter_form'),
  inventoryPhp.indexOf('function tmd_inventory_api_specs')
);

[
  'wpcf7beforesubmit',
  'wpcf7invalid',
  'wpcf7mailsent',
  'wpcf7mailfailed',
  'Escribir por WhatsApp',
  'Nuestro equipo revisará la información',
].forEach((expected) => {
  assert.equal(source.includes(expected), true, `Falta el estado de conversión: ${expected}`);
});

assert.equal(railTemplate.includes('<details class="tmd-contact-rail__details">'), true);
assert.equal(railTemplate.includes('<summary class="tmd-contact-rail__trigger"'), true);
assert.equal(railCss.includes('.tmd-contact-rail__details[open] .tmd-contact-rail__links'), true);
assert.match(railCss, /@media \(max-width: 767px\)[\s\S]*?\.tmd-contact-rail\s*\{\s*display: none;/);

[
  "tmd_inventory_api_select('api_marca'",
  "tmd_inventory_api_select('api_altura_colapsada'",
  "tmd_inventory_api_select('api_altura_levante'",
  "tmd_inventory_api_select('api_capacidad'",
].forEach((expected) => assert.equal(equipmentFilterForm.includes(expected), true));

process.stdout.write('OK: contexto y estados del formulario de conversión JS.\n');
