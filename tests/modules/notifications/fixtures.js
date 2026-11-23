// Fixtures for the Notifications module.
// Adds this module's page objects on top of the shared fixtures in tests/common/fixtures.js.
// Specs in this module import test and expect from here, not from @playwright/test.

const { test: base, expect } = require('#common/fixtures');
const { NotificationsPage } = require('./pages/notifications.page');

const test = base.extend({
  notificationsPage: async ({ page }, use) => {
    await use(new NotificationsPage(page));
  },
});

module.exports = { test, expect };
