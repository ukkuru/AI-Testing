// Playwright settings for the whole View 360 project.

const { defineConfig } = require('@playwright/test');

// Settings from .env (or .env.qa, .env.staging). Fails early with a clear message if BASE_URL is missing.
const env = require('./tests/common/env');

module.exports = defineConfig({
  testDir: './tests',

  // How long one test may run in total (90 seconds).
  timeout: 90000,

  // How long each check waits before failing (45 seconds). The app is slow to load.
  expect: { timeout: 45000 },

  // On the CI server, retry a failed test twice. On your laptop, never retry.
  retries: process.env.CI ? 2 : 0,

  // Stop the CI run if someone leaves test.only in the code by mistake.
  forbidOnly: Boolean(process.env.CI),

  // Reports:
  // list    = prints results in the terminal while tests run
  // html    = Playwright's own report (npm run report)
  // allure  = writes raw results to allure-results/ (npm run allure:generate turns them into a report)
  reporter: [
    ['list'],
    ['html', { open: 'never' }],
    ['allure-playwright', { resultsDir: 'allure-results', environmentInfo: { Environment: env.envName } }],
  ],

  use: {
    // Every page.goto('/something') starts from this address.
    baseURL: env.baseUrl(),
    viewport: { width: 1440, height: 900 },

    // Keep a trace and a screenshot only when a test fails, to help debugging.
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },

  projects: [
    // Step 1: log in once for each role and save the sessions.
    { name: 'setup', testMatch: /.*\.setup\.js/ },

    // Step 2: run the tests. Each test loads the saved session for its role
    // (see the storageState fixture in tests/common/fixtures.js).
    {
      name: 'chromium',
      dependencies: ['setup'],
    },
  ],
});
