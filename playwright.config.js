const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 90_000,
  expect: { timeout: 45_000 }, // the app is slow to boot; set once, not per assertion
  retries: process.env.CI ? 2 : 0,
  reporter: [['html', { open: 'never' }], ['list']],
  use: {
    baseURL: process.env.BASE_URL,
    viewport: { width: 1440, height: 900 },
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'setup', testMatch: /.*\.setup\.js/ },
    {
      name: 'chromium',
      dependencies: ['setup'],
      use: { storageState: '.auth/user.json' },
    },
  ],
});
