// WHAT:    Checks that each palette category shows the right fields,
//          and that switching between categories works.
// RUN:     npx playwright test field-palette
// LOGIN:   Happens once in tests/setup/auth.setup.js and is reused here.
// ADD A CATEGORY OR FIELD: Edit ../data/palette-categories.js. No code changes needed.
// TEST PLAN: tests/formbuilder-testplan.md (section 3)

const { test, expect } = require('@playwright/test');
const { labelTest } = require('#common/reporting');
const { FormBuilderPage } = require('../pages/form-builder.page');
const CATEGORIES = require('../data/palette-categories');

test.describe('Field Palette', () => {
  let formBuilder;

  // Runs before every test: open the Form List and create a blank form.
  test.beforeEach(async ({ page }) => {
    await labelTest({ module: 'Form Builder', feature: 'Field Palette', severity: 'critical' });

    formBuilder = new FormBuilderPage(page);
    await formBuilder.goToFormList();
    await formBuilder.createBlankForm();
  });

  // This loop creates one test for each category in the data file.
  for (const category of CATEGORIES) {
    test(`${category.label} category shows the right fields`, async () => {
      await formBuilder.openCategory(category);

      // toHaveText with a list checks three things at once:
      // the number of fields, their names, and their order.
      await expect(
        formBuilder.visibleFields,
        `The ${category.label} category should show: ${category.fields.join(', ')}`,
      ).toHaveText(category.fields);
    });
  }

  test('Only one category is shown at a time', async () => {
    // Visit every category, then go back to the first one
    // to check that switching back also works.
    const visitOrder = [...CATEGORIES, CATEGORIES[0]];

    for (const category of visitOrder) {
      await formBuilder.openCategory(category);

      await expect(
        formBuilder.visibleFields,
        `The ${category.label} category should show exactly ${category.fields.length} fields`,
      ).toHaveCount(category.fields.length);

      // Make sure fields from the other categories are hidden.
      const otherCategories = CATEGORIES.filter((other) => other !== category);

      for (const otherCategory of otherCategories) {
        const fieldFromOtherCategory = formBuilder.paletteField(otherCategory.fields[0]);

        // expect.soft records the failure but keeps going,
        // so one run shows every problem instead of stopping at the first.
        await expect.soft(
          fieldFromOtherCategory,
          `"${otherCategory.fields[0]}" (${otherCategory.label}) should be hidden while ${category.label} is open`,
        ).toBeHidden();
      }
    }
  });
});
