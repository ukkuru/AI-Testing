// Fixtures for the Workflow Builder module.
// Adds this module's page objects on top of the shared fixtures in tests/common/fixtures.js.
// Specs in this module import test and expect from here, not from @playwright/test.

const { test: base, expect } = require('#common/fixtures');
const { WorkflowBuilderPage } = require('./pages/workflow-builder.page');

const test = base.extend({
  workflowBuilderPage: async ({ page }, use) => {
    await use(new WorkflowBuilderPage(page));
  },
});

module.exports = { test, expect };
