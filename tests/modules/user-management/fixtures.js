// Fixtures for the User Management module.
// Adds this module's page objects on top of the shared fixtures in tests/common/fixtures.js.
// Specs in this module import test and expect from here, not from @playwright/test.

const { test: base, expect } = require('#common/fixtures');
const { UserManagementPage } = require('./pages/user-management.page');

const test = base.extend({
  userManagementPage: async ({ page }, use) => {
    await use(new UserManagementPage(page));
  },
});

module.exports = { test, expect };
