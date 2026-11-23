// Shared fixtures for every module.
//
// A fixture is something a test can ask for by name, and Playwright prepares it.
// Instead of setting things up in beforeEach, a test just lists what it needs:
//
//   test('...', async ({ page, api, cleanup }) => { ... });
//
// Each module has its own fixtures.js that adds its page objects on top of these.

const fs = require('fs');
const base = require('@playwright/test');
const env = require('./env');
const { ApiClient } = require('./api/api-client');
const { DEFAULT_ROLE, ROLES, sessionFile } = require('./roles');

const test = base.test.extend({
  // Which user the test runs as. Change it in a spec with: test.use({ role: 'viewer' });
  role: [DEFAULT_ROLE, { option: true }],

  // Loads the saved login for the chosen role.
  storageState: async ({ role }, use) => {
    if (!ROLES[role]) {
      throw new Error(`Unknown role "${role}". Known roles: ${Object.keys(ROLES).join(', ')}.`);
    }
    const file = sessionFile(role);
    if (!fs.existsSync(file)) {
      throw new Error(
        `No saved login for ${ROLES[role].label}. Add ${ROLES[role].envPrefix}_USER and ` +
          `${ROLES[role].envPrefix}_PASSWORD to your .env file, then run the tests again.`,
      );
    }
    await use(file);
  },

  // Calls the View 360 API as the same user the test runs as.
  api: async ({ playwright, storageState }, use) => {
    const request = await playwright.request.newContext({ baseURL: env.apiUrl(), storageState });
    await use(new ApiClient(request));
    await request.dispose();
  },

  // Collects clean-up steps and runs them after the test, even when the test fails.
  // Usage inside a test:
  //   cleanup.add('Delete the test form', () => api.delete(`/api/forms/${id}`));
  cleanup: async ({}, use) => {
    const steps = [];
    await use({ add: (description, action) => steps.push({ description, action }) });

    // Run newest first, so things are removed in the reverse order they were created.
    for (const step of steps.reverse()) {
      await base.test.step(`Clean up: ${step.description}`, async () => {
        try {
          await step.action();
        } catch (error) {
          // Report it, but keep going so one failed clean-up does not leave the rest behind.
          console.warn(`Clean-up failed: ${step.description}. ${error.message}`);
        }
      });
    }
  },
});

module.exports = { test, expect: base.expect };
