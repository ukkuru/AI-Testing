// WHAT:    Checks that each palette category shows the right fields,
//          and that switching between categories works.
// RUN:     npm run test:form-builder
// DATA:    ../data/palette-categories.js. Add a field or category there. No code changes needed.
// PLAN:    docs/test-plans/form-builder.md (section 3, Field Palette)
// TAGS:    @smoke runs on every pull request. @regression runs every night.

const { test, expect } = require('../fixtures');
const { labelTest } = require('#common/reporting');
const MODULES = require('#common/modules');
const CATEGORIES = require('../data/palette-categories');

test.describe('Field Palette', () => {
  // Runs before every test: open the Form List and create a blank form.
  test.beforeEach(async ({ formBuilderPage }) => {
    await labelTest({ module: MODULES.FORM_BUILDER, feature: 'Field Palette', severity: 'critical' });

    await formBuilderPage.goToFormList();
    await formBuilderPage.createBlankForm();
  });

  // This loop creates one test for each category in the data file.
  for (const category of CATEGORIES) {
    test(`${category.label} category shows the right fields`, { tag: '@smoke' }, async ({ formBuilderPage }) => {
      await formBuilderPage.openCategory(category);

      // toHaveText with a list checks three things at once:
      // the number of fields, their names, and their order.
      await expect(
        formBuilderPage.visibleFields,
        `The ${category.label} category should show: ${category.fields.join(', ')}`,
      ).toHaveText(category.fields);
    });
  }

  test('Only one category is shown at a time', { tag: '@regression' }, async ({ formBuilderPage }) => {
    // Visit every category, then go back to the first one
    // to check that switching back also works.
    const visitOrder = [...CATEGORIES, CATEGORIES[0]];

    for (const category of visitOrder) {
      await formBuilderPage.openCategory(category);

      await expect(
        formBuilderPage.visibleFields,
        `The ${category.label} category should show exactly ${category.fields.length} fields`,
      ).toHaveCount(category.fields.length);

      // Make sure fields from the other categories are hidden.
      const otherCategories = CATEGORIES.filter((other) => other !== category);

      for (const otherCategory of otherCategories) {
        const fieldFromOtherCategory = formBuilderPage.paletteField(otherCategory.fields[0]);

        // expect.soft records the failure but keeps going,
        // so one run shows every problem instead of stopping at the first.
        await expect
          .soft(
            fieldFromOtherCategory,
            `"${otherCategory.fields[0]}" (${otherCategory.label}) should be hidden while ${category.label} is open`,
          )
          .toBeHidden();
      }
    }
  });
});
