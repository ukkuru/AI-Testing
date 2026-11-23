// spec: tests/formbuilder-testplan.md (3. Field Palette and Field Types Testing)
const { test, expect } = require('@playwright/test');
const { FormBuilderPage } = require('./pages/form-builder.page');
const CATEGORIES = require('./data/palette-categories');

test.describe('Field Palette', { tag: '@palette' }, () => {
  let builder;

  test.beforeEach(async ({ page }) => {
    builder = new FormBuilderPage(page);
    await builder.gotoFormList();
    await builder.openFromScratch();
  });

  // One test per category, generated from data. Add a category = add a row.
  for (const category of CATEGORIES) {
    test(`${category.label} category lists the expected fields in order`, { tag: '@smoke' }, async () => {
      await builder.openCategory(category);

      await test.step(`Check the ${category.label} palette shows exactly: ${category.fields.join(', ')}`, async () => {
        await expect(
          builder.visibleItems,
          `The ${category.label} palette should show these ${category.fields.length} fields in this order: ` +
            `${category.fields.join(', ')}. The diff below shows which fields are missing, extra, renamed or out of order.`,
        ).toHaveText(category.fields);
      });
    });
  }

  test('switching categories shows exactly one category at a time', async () => {
    // Visit every category, then come back to the first, to prove switching both ways.
    for (const active of [...CATEGORIES, CATEGORIES[0]]) {
      await test.step(`Switch to ${active.label} and confirm only its fields are shown`, async () => {
        await builder.openCategory(active);

        await expect(
          builder.visibleItems,
          `With ${active.label} selected, the palette should show ${active.fields.length} fields. ` +
            'A different number means fields from another category are leaking through, or some are missing.',
        ).toHaveCount(active.fields.length);

        for (const other of CATEGORIES.filter((c) => c !== active)) {
          await expect.soft(
            builder.item(other.fields[0]),
            `"${other.fields[0]}" belongs to ${other.label} and should be hidden while ${active.label} is selected.`,
          ).toBeHidden();
        }
      });
    }
  });
});
