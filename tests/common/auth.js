// Logs a user into View 360. Used by tests/setup/auth.setup.js before any test runs.
// TODO: The selectors below are a starting point. Check them against your real login page.

const { test, expect } = require('@playwright/test');

async function login(page, { username, password }) {
  await test.step('Fill in the login form and sign in', async () => {
    await page.getByLabel('Email').fill(username);
    await page.getByLabel('Password').fill(password);
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(
      page.getByLabel('Password'),
      'The login form should close after signing in. If it is still showing, the username or password is wrong.',
    ).toBeHidden();
  });
}

module.exports = { login };
