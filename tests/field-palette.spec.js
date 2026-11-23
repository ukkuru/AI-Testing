// spec: tests/formbuilder-testplan.md (3. Field Palette and Field Types Testing)
const { test, expect } = require('@playwright/test');
const { FormBuilderPage } = require('./pages/form-builder.page');
const CATEGORIES = require('./data/palette-categories');

test.describe('Field Palette', { tag: '@palette' }, () => {
  let builder;

  test.beforeEach(async ({ page }) => {
    await page.goto('/formbuilder-v2');
    await expect(page.getByRole('heading', { name: 'Form List' })).toBeVisible();
    builder = new FormBuilderPage(page);
    await builder.openFromScratch();
  });

  // One test per category, generated from data. Add a category = add a row.
  for (const category of CATEGORIES) {
    test(`${category.label} category lists the expected fields in order`, { tag: '@smoke' }, async () => {
      await builder.openCategory(category);
      // Retrying, all at once: catches missing, extra, renamed and reordered items.
      await expect(builder.visibleItems).toHaveText(category.fields);
    });
  }

  test('switching categories shows exactly one category at a time', async () => {
    // Visit every category, then come back to the first, to prove switching both ways.
    for (const active of [...CATEGORIES, CATEGORIES[0]]) {
      await test.step(`open ${active.label}`, async () => {
        await builder.openCategory(active);
        await expect(builder.visibleItems).toHaveCount(active.fields.length);
        for (const other of CATEGORIES.filter((c) => c !== active)) {
          await expect.soft(builder.item(other.fields[0]), `${other.label} should be hidden`).toBeHidden();
        }
      });
    }
  });
});
