// Playwright settings for the whole project.

const { defineConfig } = require('@playwright/test');

// Load settings from the .env file (website address, username, password).
require('dotenv').config();

// Stop early with a clear message if the website address is missing.
if (!process.env.BASE_URL) {
  throw new Error('BASE_URL is missing. Copy .env.example to .env and fill in the website address.');
}

module.exports = defineConfig({
  testDir: './tests',

  // How long one test may run in total (90 seconds).
  timeout: 90000,

  // How long each check waits before failing (45 seconds). The app is slow to load.
  expect: { timeout: 45000 },

  // On the CI server, retry a failed test twice. On your laptop, never retry.
  retries: process.env.CI ? 2 : 0,

  // Reports:
  // list    = prints results in the terminal while tests run
  // html    = Playwright's own report (npm run report)
  // allure  = writes raw results to allure-results/ (npm run allure:generate turns them into a report)
  reporter: [
    ['list'],
    ['html', { open: 'never' }],
    ['allure-playwright', { resultsDir: 'allure-results' }],
  ],

  use: {
    // Every page.goto('/something') starts from this address.
    baseURL: process.env.BASE_URL,
    viewport: { width: 1440, height: 900 },

    // Keep a trace and a screenshot only when a test fails, to help debugging.
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },

  projects: [
    // Step 1: log in once and save the session.
    { name: 'setup', testMatch: /.*\.setup\.js/ },

    // Step 2: run the tests using the saved session, so no test has to log in again.
    {
      name: 'chromium',
      dependencies: ['setup'],
      use: { storageState: '.auth/user.json' },
    },
  ],
});
