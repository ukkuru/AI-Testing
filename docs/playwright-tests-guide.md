# View 360 Playwright Tests: Scripts and Setup

This file holds every script for the View 360 Playwright tests plus the steps to set them up and run them. It is written for developers who are new to Playwright with JavaScript.

## How the project is organised

The tests are split by **module**, one folder for each part of View 360: User Management, Form Builder, Workflow Builder, Templates and Notifications. Code that every module needs lives in one shared folder called **common**.

1. **`tests/common/`** is the shared library: login, settings, report labels and app wide paths.
2. **`tests/modules/<module-name>/`** holds everything for one part of the app: its pages, data, paths and tests.
3. **`tests/setup/`** logs in once before any test runs.
4. **`playwright.config.js`** holds the shared Playwright settings.
5. **`.env`** holds the website address and login. It changes per server and is never committed.

## Project layout

```
view-360/                         Your project folder
  .env.example                    Template for your .env file (safe to commit)
  .env                            Your real settings (never commit)
  .gitignore
  package.json
  playwright.config.js
  tests/
    common/                       Shared library, used by every module
      auth.js                     Logs a user in
      env.js                      Reads settings from .env in one place
      modules.js                  Module names, spelled the same everywhere
      reporting.js                Allure labels (module, feature, severity)
      routes.js                   App wide paths, such as the page after login
    setup/
      auth.setup.js               Logs in once before all tests
    modules/
      user-management/
      form-builder/               The first module with real tests
        routes.js                 Paths for this module only
        data/
          palette-categories.js   Test data for this module
        pages/
          form-builder.page.js    How to find and use things on this module's pages
        specs/
          field-palette.spec.js   The tests
      workflow-builder/
      templates/
      notifications/
```

**Module folder names** are lower case with hyphens (`user-management`), because spaces in folder names break commands. **Module display names** in reports use normal spelling (`User Management`) and come from `tests/common/modules.js`.

The four new modules (User Management, Workflow Builder, Templates, Notifications) are empty starters. Each has a `routes.js`, a page object, and empty `data/` and `specs/` folders ready for tests.

Every module has the **same four parts**. Once you know one module, you know them all.

## Rules for modules and the common library

These rules keep the project tidy as it grows. Break them and the common folder turns into a junk drawer.

1. **Start inside the module.** New code always goes in the module that needs it.
2. **Move to common only when a second module needs it.** Not before. Guessing what might be shared leads to code nobody uses.
3. **A module never imports from another module.** If Form Builder needs something from User Management, that thing belongs in common.
4. **Common never imports from a module.** Common must work without knowing any module exists.
5. **Common holds tools, not tests.** No `.spec.js` files in `tests/common/`.
6. **One job per common file.** `auth.js` logs in. `env.js` reads settings. Do not create a `utils.js` that does everything.

### What goes where

* **Login, settings, report labels, module names:** `tests/common/`
* **A UI part used on many pages, such as a date picker or a confirm dialog:** `tests/common/components/` (create it when you need it)
* **The page after login and other app wide paths:** `tests/common/routes.js`
* **Paths for one module:** `tests/modules/<module>/routes.js`
* **Fields, names, expected values for one module:** `tests/modules/<module>/data/`
* **Website address, username, password:** `.env`

### Short import paths for common

Files deep inside a module would normally need long paths like `../../../common/auth`. To avoid that, `package.json` has a shortcut:
```json
"imports": {
  "#common/*": "./tests/common/*.js"
}
```
So from any file you can write:
```js
const { login } = require('#common/auth');
const { labelTest } = require('#common/reporting');
```
Files inside the same module still use short relative paths such as `../pages/form-builder.page`.

## Setup instructions

### Step 1: Install Node.js
Install Node.js 18 or newer from nodejs.org. Check it with `node -v`.

### Step 2: Install the packages
From the project folder:
```
npm install
npx playwright install chromium
```

### Step 3: Create your `.env` file
```
cp .env.example .env
```
Open `.env` and fill in the website address and a test account. Never use your personal account.

### Step 4: Check the login selectors
Open `tests/common/auth.js`. The labels `Email`, `Password` and the `Log in` button are a starting point. Change them to match your real login page.

### Step 5: Run the tests
Run everything:
```
npm test
```
Run one module:
```
npm run test:user-management
npm run test:form-builder
npm run test:workflow-builder
npm run test:templates
npm run test:notifications
```
Run one test file:
```
npm run test:palette
```
Run against another server without editing any file:
```
BASE_URL=https://staging.yourapp.com npm test
```

### Step 6: Read the Playwright report
```
npm run report
```
Click a failed test to see the step that failed, a plain message explaining what should have happened, a screenshot and a trace.

For the Allure report, see the next section.

## Allure report

Allure gives you a richer report than Playwright's own: test history, charts, grouping by feature, and every step with its plain message. The tests already write Allure results. You only need to turn them into a report.

### What you need
Nothing extra. Allure 3 runs on Node.js, so you do not need Java. The two packages are already in `package.json`:
* **`allure-playwright`** collects results while the tests run and saves them in `allure-results/`.
* **`allure`** turns those results into a report you can open in the browser.

### Step 1: Clear old results
```
npm run allure:clean
```
Do this before each run. Otherwise results from old runs mix with new ones and the report shows tests that no longer exist.

### Step 2: Run the tests
```
npm test
```
This fills the `allure-results/` folder.

### Step 3: Build the report
```
npm run allure:generate
```
This creates the `allure-report/` folder. It replaces the old report each time.

### Step 4: Open the report
```
npm run allure:open
```
The report opens in your browser. Click a failed test to see each step, the plain message for the check that failed, the screenshot and the trace.

### Where Allure is switched on
In `playwright.config.js`, the `reporter` list has this line:
```js
['allure-playwright', { resultsDir: 'allure-results' }],
```
Remove it to switch Allure off. The terminal output and the Playwright HTML report keep working either way.

### Keep reports out of Git
`allure-results/` and `allure-report/` are in `.gitignore`. They are created fresh on every run and can be large, so never commit them.

### Group tests by module and feature
The common library has a helper that labels each test, so Allure groups them by module. Call it in `beforeEach`:
```js
const { labelTest } = require('#common/reporting');
const MODULES = require('#common/modules');

test.beforeEach(async () => {
  await labelTest({ module: MODULES.FORM_BUILDER, feature: 'Field Palette', severity: 'critical' });
});
```
Always use `MODULES.SOMETHING` instead of typing the name. A typo such as `'Form builder'` would create a second, separate group in the report.
In the report, open the **Behaviors** tab to see tests grouped by module, then by feature.

### On a CI server
Run the same four commands, but skip `allure:open`. Save the `allure-report/` folder as a build artifact so the team can download and open it.

## The scripts

These are the files with real code. The four empty modules follow the same shape as `form-builder`.

### `.env.example`

```text
# Copy this file to .env and fill in the values.
# Never commit the real .env file. It holds your password.
BASE_URL=https://qa.yourapp.com
TEST_USER=
TEST_PASSWORD=
```

### `.gitignore`

```text
.auth/
.env
allure-results/
allure-report/
node_modules/
test-results/
playwright-report/
```

### `package.json`

```json
{
  "name": "view-360",
  "description": "Playwright tests for the View 360 application",
  "private": true,
  "scripts": {
    "test": "playwright test",
    "test:palette": "playwright test field-palette",
    "report": "playwright show-report",
    "allure:clean": "node -e \"require('fs').rmSync('allure-results',{recursive:true,force:true})\"",
    "allure:generate": "allure generate ./allure-results --clean",
    "allure:open": "allure open ./allure-report",
    "test:user-management": "playwright test tests/modules/user-management",
    "test:form-builder": "playwright test tests/modules/form-builder",
    "test:workflow-builder": "playwright test tests/modules/workflow-builder",
    "test:templates": "playwright test tests/modules/templates",
    "test:notifications": "playwright test tests/modules/notifications"
  },
  "devDependencies": {
    "@playwright/test": "^1.49.0",
    "allure": "^3.33.0",
    "allure-playwright": "^3.15.0",
    "dotenv": "^16.4.5",
    "allure-js-commons": "^3.15.0"
  },
  "imports": {
    "#common/*": "./tests/common/*.js"
  }
}
```

### `playwright.config.js`

```js
// Playwright settings for the whole project.

const { defineConfig } = require('@playwright/test');

// Settings from the .env file. Fails early with a clear message if BASE_URL is missing.
const env = require('./tests/common/env');

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
    baseURL: env.baseUrl(),
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
```

### `tests/common/env.js`

```js
// Reads settings from the .env file in one place.
// Every other file asks this file for settings instead of reading process.env directly,
// so a missing value fails early with a clear message.

require('dotenv').config();

function required(name) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`${name} is missing. Copy .env.example to .env and fill it in.`);
  }
  return value;
}

module.exports = {
  baseUrl: () => required('BASE_URL'),
  testUser: () => required('TEST_USER'),
  testPassword: () => required('TEST_PASSWORD'),
};
```

### `tests/common/auth.js`

```js
// Logs a user into the app. Used by tests/setup/auth.setup.js before any test runs.
// TODO: The selectors below are a starting point. Check them against your real login page.

const { test, expect } = require('@playwright/test');
const env = require('./env');

async function login(page) {
  await test.step('Fill in the login form and sign in', async () => {
    await page.getByLabel('Email').fill(env.testUser());
    await page.getByLabel('Password').fill(env.testPassword());
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(
      page.getByLabel('Password'),
      'The login form should close after signing in. If it is still showing, the username or password is wrong.',
    ).toBeHidden();
  });
}

module.exports = { login };
```

### `tests/common/modules.js`

```js
// The app's modules, spelled the same way everywhere.
// Use these in labelTest() so the Allure report groups tests correctly.
// A typo like 'Form builder' would create a second, separate group.
module.exports = {
  USER_MANAGEMENT: 'User Management',
  FORM_BUILDER: 'Form Builder',
  WORKFLOW_BUILDER: 'Workflow Builder',
  TEMPLATES: 'Templates',
  NOTIFICATIONS: 'Notifications',
};
```

### `tests/common/reporting.js`

```js
// Labels for the Allure report, so tests are grouped by module and feature.
// Call this at the start of a test or in beforeEach.

const allure = require('allure-js-commons');

async function labelTest({ module, feature, severity = 'normal' }) {
  await allure.epic(module);
  await allure.feature(feature);
  await allure.severity(severity);
}

module.exports = { labelTest };
```

### `tests/common/routes.js`

```js
// App-wide paths that every module uses, such as where the user lands after login.
// Paths that belong to one module live in that module's own routes.js.
module.exports = {
  home: '/formbuilder-v2',
};
```

### `tests/setup/auth.setup.js`

```js
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
```

### `tests/modules/form-builder/routes.js`

```js
// Page paths in the app.
// The website address (https://...) is NOT here. It comes from BASE_URL in the .env file,
// so the same tests can run on dev, QA or staging without changing any code.
module.exports = {
  formList: '/formbuilder-v2',
};
```

### `tests/modules/form-builder/data/palette-categories.js`

```js
// Single source of truth for what each palette category must show.
// Order matters: the spec asserts the exact ordered list, so a renamed,
// missing, extra or reordered field fails with one clear diff.
module.exports = [
  {
    key: 'basic',
    label: 'Basic',
    iconIndex: 0, // TODO: replace with data-testid once dev adds one
    fields: [
      'Text Field', 'Number', 'Textarea', 'Select', 'Checkbox', 'Radio',
      'Checkbox Group', 'Date & Time', 'Password', 'Button', 'HTML Element', 'Image',
    ],
  },
  {
    key: 'advanced',
    label: 'Advanced',
    iconIndex: 1,
    fields: [
      'File Upload', 'Image Upload', 'Signature', 'Address', 'Data Grid', 'Data Table',
      'Sub Form', 'Modal', 'Hidden Field', 'Generator', 'Scanner', 'Map', 'Recaptcha',
    ],
  },
  {
    key: 'layout',
    label: 'Layout',
    iconIndex: 2,
    fields: ['Tabs', 'Accordion', 'Custom Grid', 'Section'],
  },
];
```

### `tests/modules/form-builder/pages/form-builder.page.js`

```js
// Page object for the Form Builder screen.
// It keeps all the "how to find things on the page" code in one place,
// so the test file only has to describe what we are checking.

const { test, expect } = require('@playwright/test');
const ROUTES = require('../routes');

class FormBuilderPage {
  constructor(page) {
    this.page = page;

    this.formListHeading = page.getByRole('heading', { name: 'Form List' });
    this.formBuilderHeading = page.getByRole('heading', { name: 'Form Builder' });

    // The category icons on the left (Basic, Advanced, Layout...).
    // They have no name or label, so we find them by their position.
    this.categoryIcons = page.locator('.tool-icon');

    // Fields in the palette can be dragged, so we find them by draggable="true".
    // ":visible" means: only the fields the user can see right now.
    // Fields from other categories can still be on the page, just hidden.
    this.visibleFields = page.locator('[draggable="true"]:visible');
  }

  async goToFormList() {
    await test.step('Open the Form List page', async () => {
      await this.page.goto(ROUTES.formList);

      await expect(
        this.formListHeading,
        'The Form List page should load. If not, the user may be logged out or the app is down.',
      ).toBeVisible();
    });
  }

  async createBlankForm() {
    await test.step('Create a new blank form', async () => {
      await this.page.getByRole('button', { name: '+ Create Form' }).click();
      await this.page.getByText('Start From Scratch', { exact: true }).first().click();

      await expect(
        this.formBuilderHeading,
        'The Form Builder should open after clicking "Start From Scratch".',
      ).toBeVisible();
    });
  }

  // Finds one field in the palette by its name, for example "Text Field".
  paletteField(fieldName) {
    return this.page.locator('[draggable="true"]').filter({
      has: this.page.getByText(fieldName, { exact: true }),
    });
  }

  // Clicks a category icon and waits until that category's fields appear.
  async openCategory(category) {
    await test.step(`Open the ${category.label} category`, async () => {
      await this.categoryIcons.nth(category.iconIndex).click();

      // Wait for the first field to show up. The palette animates,
      // and without this wait the test would check the old category.
      const firstField = this.paletteField(category.fields[0]);

      await expect(
        firstField,
        `After clicking the ${category.label} icon, "${category.fields[0]}" should appear. ` +
          'If not, the icon order may have changed.',
      ).toBeVisible();
    });
  }
}

module.exports = { FormBuilderPage };
```

### `tests/modules/form-builder/specs/field-palette.spec.js`

```js
// WHAT:    Checks that each palette category shows the right fields,
//          and that switching between categories works.
// RUN:     npx playwright test field-palette
// LOGIN:   Happens once in tests/setup/auth.setup.js and is reused here.
// ADD A CATEGORY OR FIELD: Edit ../data/palette-categories.js. No code changes needed.
// TEST PLAN: tests/formbuilder-testplan.md (section 3)

const { test, expect } = require('@playwright/test');
const { labelTest } = require('#common/reporting');
const MODULES = require('#common/modules');
const { FormBuilderPage } = require('../pages/form-builder.page');
const CATEGORIES = require('../data/palette-categories');

test.describe('Field Palette', () => {
  let formBuilder;

  // Runs before every test: open the Form List and create a blank form.
  test.beforeEach(async ({ page }) => {
    await labelTest({ module: MODULES.FORM_BUILDER, feature: 'Field Palette', severity: 'critical' });

    formBuilder = new FormBuilderPage(page);
    await formBuilder.goToFormList();
    await formBuilder.createBlankForm();
  });

  // This loop creates one test for each category in the data file.
  for (const category of CATEGORIES) {
    test(`${category.label} category shows the right fields`, async () => {
      await formBuilder.openCategory(category);

      // toHaveText with a list checks three things at once:
      // the number of fields, their names, and their order.
      await expect(
        formBuilder.visibleFields,
        `The ${category.label} category should show: ${category.fields.join(', ')}`,
      ).toHaveText(category.fields);
    });
  }

  test('Only one category is shown at a time', async () => {
    // Visit every category, then go back to the first one
    // to check that switching back also works.
    const visitOrder = [...CATEGORIES, CATEGORIES[0]];

    for (const category of visitOrder) {
      await formBuilder.openCategory(category);

      await expect(
        formBuilder.visibleFields,
        `The ${category.label} category should show exactly ${category.fields.length} fields`,
      ).toHaveCount(category.fields.length);

      // Make sure fields from the other categories are hidden.
      const otherCategories = CATEGORIES.filter((other) => other !== category);

      for (const otherCategory of otherCategories) {
        const fieldFromOtherCategory = formBuilder.paletteField(otherCategory.fields[0]);

        // expect.soft records the failure but keeps going,
        // so one run shows every problem instead of stopping at the first.
        await expect.soft(
          fieldFromOtherCategory,
          `"${otherCategory.fields[0]}" (${otherCategory.label}) should be hidden while ${category.label} is open`,
        ).toBeHidden();
      }
    }
  });
});
```

### `tests/modules/user-management/pages/user-management.page.js` (starter, same for every empty module)

```js
// Page object for the User Management module.
// Keep all the "how to find things on the page" code here,
// so the specs only describe what we are checking.

const { test, expect } = require('@playwright/test');
const ROUTES = require('../routes');

class UserManagementPage {
  constructor(page) {
    this.page = page;
    // TODO: Add the headings, buttons and fields this module's tests need.
  }
}

module.exports = { UserManagementPage };
```

## Common tasks

### Write the first test for an empty module
1. Add the module's paths to its `routes.js`, for example `list: '/users'`.
2. Add headings, buttons and fields to its page object in `pages/`.
3. Put test data in `data/`.
4. Create a file in `specs/` ending in `.spec.js`. Call `labelTest({ module: MODULES.USER_MANAGEMENT, ... })` in `beforeEach`.
5. Delete the `.gitkeep` file in any folder that now has real files.

### Add a new module
1. Create a folder under `tests/modules/` in lower case with hyphens, for example `tests/modules/reports/`.
2. Inside it, create `pages/`, `data/`, `specs/` and a `routes.js` file. Copy the shape of `form-builder`.
3. Add its display name to `tests/common/modules.js`, for example `REPORTS: 'Reports'`.
4. Add a run command to `package.json`, for example `"test:reports": "playwright test tests/modules/reports"`.

Login and settings already work for the new module. You do not need to touch the setup or config.

### Add a field to a category
Open `tests/modules/form-builder/data/palette-categories.js` and add the name to the right list, in the order it appears on screen.

### Add a new category
Add a new block to the same file with `label`, `iconIndex` (icon position, counting from 0) and `fields`. A test for it appears automatically.

### A category test fails after a UI change
1. Read the message in the report. It lists the fields that were expected.
2. Look at the diff to see which field is missing, extra, renamed or moved.
3. If the app changed on purpose, update the data file. If not, report a bug.

## Still to do

1. **Ask developers for test ids on the category icons.** Finding icons by position is the weakest part of these tests.
2. **Check exact text matching.** If palette items show extra text, the list check may fail. Match only the label element if that happens.
3. **Confirm whether field order matters.** If not, sort both lists before comparing.
4. **Add drag and drop tests.** Drag the first field of each category onto the form and check it appears.
5. **Clean up test forms.** If "Start From Scratch" saves a draft, delete it after each test.
6. **Test the Template category.** It is not in the data file yet.

## Quick check for clarity

Give this project to a junior developer and ask them to add one field to the Basic list and run the tests. If they can do it using only this file, the setup is clear enough.
