// Fixtures for the Templates module.
// Adds this module's page objects on top of the shared fixtures in tests/common/fixtures.js.
// Specs in this module import test and expect from here, not from @playwright/test.

const { test: base, expect } = require('#common/fixtures');
const { TemplatesPage } = require('./pages/templates.page');

const test = base.extend({
  templatesPage: async ({ page }, use) => {
    await use(new TemplatesPage(page));
  },
});

module.exports = { test, expect };
