# Field Palette Tests: Scripts and Setup

This file holds every script for the field palette tests plus the steps to set them up and run them. It is written for developers who are new to Playwright with JavaScript.

## How the pieces fit together

1. **`.env`** holds the website address and login. It changes per server and is never committed.
2. **`playwright.config.js`** reads `.env` and sets the shared settings.
3. **`tests/setup/auth.setup.js`** logs in once and saves the session.
4. **`tests/data/`** holds what the app should show (fields) and where pages live (paths).
5. **`tests/pages/form-builder.page.js`** knows how to find and click things on the page.
6. **`tests/field-palette.spec.js`** holds the tests. It reads the data and uses the page object.

**Simple rule:** if it changes between servers or is a secret, it goes in `.env`. If it is the same everywhere, it goes in a code file.

## Project layout

```
AI-Testing/
  .env.example                  Template for your .env file (safe to commit)
  .env                          Your real settings (never commit)
  .gitignore
  package.json
  playwright.config.js
  helpers/
    auth.js                     Your login function (you add this)
  tests/
    field-palette.spec.js
    data/
      palette-categories.js
      routes.js
    pages/
      form-builder.page.js
    setup/
      auth.setup.js
```

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

### Step 4: Add the login helper
Create `helpers/auth.js` with a `login(page)` function. It must read `process.env.TEST_USER` and `process.env.TEST_PASSWORD`. It must not hold the website address.

### Step 5: Run the tests
```
npm run test:palette
```
To run against another server without editing any file:
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

### Optional: group tests by feature
Allure can group tests under an epic and feature. Add these lines at the start of a test:
```js
const allure = require('allure-js-commons');

test('Basic category shows the right fields', async () => {
  await allure.epic('Form Builder');
  await allure.feature('Field Palette');
  await allure.severity('critical');
  // ... rest of the test
});
```
`allure-js-commons` comes with `allure-playwright`, so no extra install is needed.

### On a CI server
Run the same four commands, but skip `allure:open`. Save the `allure-report/` folder as a build artifact so the team can download and open it.

## The scripts

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
```

### `package.json`

```json
{
  "name": "ai-testing",
  "private": true,
  "scripts": {
    "test": "playwright test",
    "test:palette": "playwright test field-palette",
    "report": "playwright show-report",
    "allure:clean": "node -e \"require('fs').rmSync('allure-results',{recursive:true,force:true})\"",
    "allure:generate": "allure generate ./allure-results --clean",
    "allure:open": "allure open ./allure-report"
  },
  "devDependencies": {
    "@playwright/test": "^1.49.0",
    "allure": "^3.33.0",
    "allure-playwright": "^3.15.0",
    "dotenv": "^16.4.5"
  }
}
```

### `playwright.config.js`

```js
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
```

### `tests/data/palette-categories.js`

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

### `tests/data/routes.js`

```js
// Page paths in the app.
// The website address (https://...) is NOT here. It comes from BASE_URL in the .env file,
// so the same tests can run on dev, QA or staging without changing any code.
module.exports = {
  formList: '/formbuilder-v2',
};
```

### `tests/pages/form-builder.page.js`

```js
// Page object for the Form Builder screen.
// It keeps all the "how to find things on the page" code in one place,
// so the test file only has to describe what we are checking.

const { test, expect } = require('@playwright/test');
const ROUTES = require('../data/routes');

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

### `tests/setup/auth.setup.js`

```js
// Logs in once per run and saves the session. Every spec reuses it.
const { test: setup, expect } = require('@playwright/test');
const { login } = require('../../helpers/auth');
const ROUTES = require('../data/routes');

setup('Log in once and save the session for all tests', async ({ page }) => {
  await setup.step('Log in with the test account', async () => {
    await page.goto(ROUTES.formList);
    await login(page); // must read TEST_USER and TEST_PASSWORD from process.env
  });

  await setup.step('Confirm login worked and save the session', async () => {
    await page.goto(ROUTES.formList);
    await expect(
      page.getByRole('heading', { name: 'Form List' }),
      'Login failed or did not land on Form List. Check the test credentials in the environment variables and that the app is up. Every other test depends on this step.',
    ).toBeVisible();
    await page.context().storageState({ path: '.auth/user.json' });
  });
});
```

### `tests/field-palette.spec.js`

```js
// WHAT:    Checks that each palette category shows the right fields,
//          and that switching between categories works.
// RUN:     npx playwright test field-palette
// LOGIN:   Happens once in tests/setup/auth.setup.js and is reused here.
// ADD A CATEGORY OR FIELD: Edit tests/data/palette-categories.js. No code changes needed.
// TEST PLAN: tests/formbuilder-testplan.md (section 3)

const { test, expect } = require('@playwright/test');
const { FormBuilderPage } = require('./pages/form-builder.page');
const CATEGORIES = require('./data/palette-categories');

test.describe('Field Palette', () => {
  let formBuilder;

  // Runs before every test: open the Form List and create a blank form.
  test.beforeEach(async ({ page }) => {
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

## Common tasks

### Add a field to a category
Open `tests/data/palette-categories.js` and add the name to the right list, in the order it appears on screen.

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
