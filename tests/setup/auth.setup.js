// Logs in once for each role and saves the session, so no test has to log in again.
// A role is skipped if its username and password are not set.

const { test: setup, expect } = require('@playwright/test');
const { login } = require('#common/auth');
const env = require('#common/env');
const ROUTES = require('#common/routes');
const { ROLES, sessionFile } = require('#common/roles');

for (const [roleName, role] of Object.entries(ROLES)) {
  setup(`Log in as ${role.label} and save the session`, async ({ page }) => {
    // Skipping is on purpose here: a role with no login set is simply not used.
    // eslint-disable-next-line playwright/no-skipped-test
    setup.skip(
      !env.hasCredentialsFor(role.envPrefix),
      `No login set for ${role.label}. Add ${role.envPrefix}_USER and ${role.envPrefix}_PASSWORD to use this role.`,
    );

    await page.goto(ROUTES.home);
    await login(page, env.credentialsFor(role.envPrefix));

    await page.goto(ROUTES.home);
    await expect(
      page.getByRole('heading', { name: 'Form List' }),
      `${role.label} login did not reach the home page. Check ${role.envPrefix}_USER and ${role.envPrefix}_PASSWORD, and that the app is up.`,
    ).toBeVisible();

    await page.context().storageState({ path: sessionFile(roleName) });
  });
}
