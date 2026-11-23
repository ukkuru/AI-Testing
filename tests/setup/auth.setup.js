// Logs in once per run and saves the session. Every spec reuses it.
const { test: setup, expect } = require('@playwright/test');
const { login } = require('../../helpers/auth');

setup('authenticate', async ({ page }) => {
  await page.goto('/formbuilder-v2');
  await login(page); // reads credentials from process.env inside the helper
  await page.goto('/formbuilder-v2');
  await expect(page.getByRole('heading', { name: 'Form List' })).toBeVisible();
  await page.context().storageState({ path: '.auth/user.json' });
});
