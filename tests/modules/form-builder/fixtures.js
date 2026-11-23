// Fixtures for the Form Builder module.
// Adds this module's page objects on top of the shared fixtures in tests/common/fixtures.js.
// Specs in this module import test and expect from here, not from @playwright/test.

const { test: base, expect } = require('#common/fixtures');
const { FormBuilderPage } = require('./pages/form-builder.page');

const test = base.extend({
  formBuilderPage: async ({ page }, use) => {
    await use(new FormBuilderPage(page));
  },
});

module.exports = { test, expect };
