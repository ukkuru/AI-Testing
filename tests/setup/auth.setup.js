// Logs in once per run and saves the session. Every spec reuses it.
const { test: setup, expect } = require('@playwright/test');
const { login } = require('#common/auth');
const ROUTES = require('#common/routes');

setup('Log in once and save the session for all tests', async ({ page }) => {
  await setup.step('Log in with the test account', async () => {
    await page.goto(ROUTES.home);
    await login(page);
  });

  await setup.step('Confirm login worked and save the session', async () => {
    await page.goto(ROUTES.home);
    await expect(
      page.getByRole('heading', { name: 'Form List' }),
      'Login failed or did not land on Form List. Check the test credentials in the environment variables and that the app is up. Every other test depends on this step.',
    ).toBeVisible();
    await page.context().storageState({ path: '.auth/user.json' });
  });
});
