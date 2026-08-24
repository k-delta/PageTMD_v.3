'use strict';

const assert = require('node:assert/strict');
const {
  cardMatches,
  clearPreservedFilters,
  pageItems,
  parsePayload,
  rangeMatch,
  restoreFormControls,
} = require('../wp-content/themes/blocksy-child/assets/js/tmd-inventory-api.js');

const items = Array.from({ length: 25 }, (_, index) => ({
  id: `item-${index + 1}`,
  filters: {
    brand: index < 13 ? 'CROWN' : 'JUNGHEINRICH',
    category: 'Reach',
    subcategory: 'Pantógrafo doble profundidad',
    condition: 'PANTOGRAFO',
    collapsedHeight: '2.5',
    liftHeight: '7',
    operator: 'Sentado',
    reach: 'DOBLE',
    voltage: '',
    capacity: '2 ton',
  },
}));

const noFilters = {
  brand: '', category: '', subcategory: '', condition: '', collapsedHeight: '',
  liftHeight: '', operator: '', reach: '', voltage: '', capacity: '',
};

assert.equal(items.filter((item) => cardMatches(item, noFilters)).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, brand: 'crown' })).length, 13);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, category: 'reach' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, subcategory: 'pantógrafo doble profundidad' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, collapsedHeight: '2-3' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, liftHeight: '6-8' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, liftHeight: '0-6' })).length, 0);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, condition: 'pantografo' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, operator: 'sentado' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, reach: 'doble' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, capacity: '2 ton' })).length, 25);
assert.equal(items.filter((item) => cardMatches(item, { ...noFilters, capacity: '2.5 ton' })).length, 0);
assert.equal(rangeMatch('7', '6-8'), true);
assert.equal(rangeMatch('', '6-8'), false);

const preservedCategory = { name: 'api_categoria', value: 'Reach' };
const capacityControl = { name: 'api_capacidad', value: '' };
const formControls = {
  querySelectorAll(selector) {
    return selector === '[data-api-preserved-filter]'
      ? [preservedCategory]
      : [preservedCategory, capacityControl];
  },
};
restoreFormControls(formControls, '?api_categoria=Apiladores&api_capacidad=2%20ton');
assert.equal(preservedCategory.value, 'Apiladores');
assert.equal(capacityControl.value, '2 ton');
clearPreservedFilters(formControls);
assert.equal(preservedCategory.value, '');
assert.equal(capacityControl.value, '2 ton');

const battery = {
  filters: {
    brand: 'JUNGHEINRÍCH', category: '', subcategory: '', condition: 'Nueva',
    collapsedHeight: '', liftHeight: '', operator: '', reach: '', voltage: '48 V', capacity: '625 Ah',
  },
};
assert.equal(cardMatches(battery, { ...noFilters, brand: 'jungheinrích' }), true);
assert.equal(cardMatches(battery, { ...noFilters, voltage: '48 V' }), true);
assert.equal(cardMatches(battery, { ...noFilters, capacity: '625 Ah' }), true);
assert.equal(cardMatches(battery, { ...noFilters, condition: 'nueva' }), true);

assert.equal(pageItems(items, 1, 12).length, 12);
assert.equal(pageItems(items, 2, 12).length, 12);
assert.deepEqual(pageItems(items, 3, 12).map((item) => item.id), ['item-25']);

assert.equal(parsePayload(''), null);
assert.equal(parsePayload('{invalid'), null);
assert.equal(parsePayload('{"items":{}}'), null);
assert.deepEqual(parsePayload(JSON.stringify({ items: [{ id: 'safe' }] })), [{ id: 'safe' }]);

process.stdout.write('OK: filtros, paginación y fallback de payload JS.\n');
