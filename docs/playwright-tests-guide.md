# View 360 Playwright Tests: Scripts and Setup

This guide explains how the View 360 test project is organised, how to set it up, and how to write and run tests. It is written for developers who are new to Playwright with JavaScript. Every script is copied in full at the end.

## How the project is organised

The tests are split by **module**, one folder for each part of View 360: User Management, Form Builder, Workflow Builder, Templates and Notifications. Code that every module needs lives in one shared folder called **common**.

1. **`tests/common/`** is the shared library: login, settings, roles, fixtures, API calls, test data, report labels.
2. **`tests/modules/<module-name>/`** holds everything for one part of the app: its pages, data, paths, fixtures and tests.
3. **`tests/setup/`** logs in once for each role before any test runs.
4. **`playwright.config.js`** holds the shared Playwright settings.
5. **`.env` files** hold website addresses and logins. They are never committed.
6. **`.github/workflows/`** runs the tests on GitHub automatically.
7. **`docs/`** holds this guide and one test plan per module.

## Project layout

```
view-360/                             Your project folder
  README.md                           Short intro and quick start
  .env.example                        Template for your .env files (safe to commit)
  .env                                Your real settings (never commit)
  .env.qa, .env.staging               Settings for other servers (never commit)
  .nvmrc                              The Node.js version everyone uses
  .prettierrc.json                    Formatting rules
  eslint.config.js                    Code checker rules
  package.json                        Packages and run commands
  playwright.config.js                Playwright settings
  .github/
    workflows/
      playwright.yml                  Runs tests on GitHub
  docs/
    playwright-tests-guide.md         This guide
    test-plans/                       One test plan per module
  tools/
    build-guide.js                    Copies the code into this guide
  tests/
    common/                           Shared library, used by every module
      api/
        api-client.js                 Calls the View 360 API
      auth.js                         Fills in the login form
      env.js                          Reads settings from .env files
      fixtures.js                     Shared fixtures: role, api, cleanup
      modules.js                      Module names, spelled the same everywhere
      reporting.js                    Allure labels (module, feature, severity)
      roles.js                        User roles and where their sessions are saved
      routes.js                       App wide paths, such as the page after login
      test-data.js                    Unique names for test data
    setup/
      auth.setup.js                   Logs in once per role
    modules/
      user-management/
      form-builder/                   The first module with real tests
        fixtures.js                   This module's page objects as fixtures
        routes.js                     Paths for this module only
        data/
          palette-categories.js       Test data for this module
        pages/
          form-builder.page.js        How to find and use things on this module's pages
        specs/
          field-palette.spec.js       The tests
      workflow-builder/
      templates/
      notifications/
```

**Module folder names** are lower case with hyphens (`user-management`), because spaces in folder names break commands. **Module display names** in reports use normal spelling (`User Management`) and come from `tests/common/modules.js`.

The four modules without tests yet are starters. Each has a `fixtures.js`, a `routes.js`, a page object, and empty `data/` and `specs/` folders.

## Rules for modules and the common library

These rules keep the project tidy as it grows. Break them and the common folder turns into a junk drawer.

1. **Start inside the module.** New code always goes in the module that needs it.
2. **Move to common only when a second module needs it.** Not before.
3. **A module never imports from another module.** If two modules need the same thing, it belongs in common.
4. **Common never imports from a module.** Common must work without knowing any module exists.
5. **Common holds tools, not tests.** No `.spec.js` files in `tests/common/`.
6. **One job per common file.** Do not create a `utils.js` that does everything.

### What goes where

- **Login, settings, roles, fixtures, API calls, report labels:** `tests/common/`
- **A UI part used on many pages, such as a date picker:** `tests/common/components/` (create it when you need it)
- **App wide paths:** `tests/common/routes.js`
- **Paths for one module:** `tests/modules/<module>/routes.js`
- **Test data for one module:** `tests/modules/<module>/data/`
- **Website address, usernames, passwords:** `.env` files and GitHub Secrets

### Short import paths for common

`package.json` has a shortcut so files deep inside a module do not need paths like `../../../common/auth`:

```js
const { labelTest } = require('#common/reporting');
const MODULES = require('#common/modules');
```

Files inside the same module use short relative paths such as `../fixtures`.

## Setup instructions

### Step 1: Install Node.js

Install the version listed in `.nvmrc` (Node.js 22) from nodejs.org. Check it with `node -v`. If you use nvm, just run `nvm use` in the project folder.

### Step 2: Install the packages

From the project folder, run these two commands:

```
npm install
npx playwright install chromium
```

**What these do:**

1. `npm install` reads `package.json` and installs every package listed under `devDependencies` into a `node_modules/` folder. That includes Playwright, ESLint, Prettier and Allure. You do not install them one by one.
2. `npx playwright install chromium` downloads the Chromium browser that Playwright drives. Playwright does not include browsers, so this step is separate.

The exact versions are locked in `package-lock.json`, so everyone on the team gets the same versions. Always commit `package-lock.json`.

### What gets installed

**Testing**

- `@playwright/test`: runs the tests and drives the browser.
- `dotenv`: reads settings from the `.env` files.
- `cross-env`: lets `npm run test:qa` and `npm run test:staging` work on Windows, Mac and Linux.

**Code checker (ESLint)**

- `eslint`: the code checker.
- `@eslint/js`: the standard JavaScript rules.
- `eslint-plugin-playwright`: Playwright rules, such as catching a missing `await`.
- `globals`: tells ESLint about Node.js names like `process` and `__dirname`.
- `eslint-config-prettier`: turns off ESLint style rules that would clash with Prettier.

**Formatting**

- `prettier`: formats the code the same way for everyone.

**Allure report**

- `allure-playwright`: collects results while tests run and saves them in `allure-results/`.
- `allure-js-commons`: lets tests add labels such as module, feature and severity.
- `allure`: turns the results into a report you can open. It runs on Node.js, so you do not need Java.

### Check everything installed

Run each command. Each one should print a version number:

```
node -v
npx playwright --version
npx eslint --version
npx prettier --version
npx allure --version
```

Then run the code checker on the whole project:

```
npm run check
```

It should finish with `All matched files use Prettier code style!` and no errors.

### Editor setup (VS Code)

Install these extensions so problems show up while you type, not only when you run `npm run check`:

- **ESLint** (by Microsoft): underlines code checker problems in red.
- **Prettier, Code formatter** (by Prettier): formats the file when you save.
- **Playwright Test for VS Code** (by Microsoft): run and debug single tests from the editor.

To format on save, open Settings, search for **Format On Save** and tick it, then set **Default Formatter** to Prettier.

### Starting a new project from nothing

You only need this if you are building a new test project instead of cloning this one. It installs the same packages this project uses:

```
npm init -y
npm install -D @playwright/test dotenv cross-env
npm install -D eslint @eslint/js eslint-plugin-playwright globals eslint-config-prettier prettier
npm install -D allure allure-playwright allure-js-commons
npx playwright install chromium
```

Then copy these files from this project, since they hold the settings: `playwright.config.js`, `eslint.config.js`, `.prettierrc.json`, `.prettierignore`, `.gitignore`, `.env.example`, `.nvmrc`, and the `imports` and `scripts` sections of `package.json`.

### Adding a new package later

```
npm install -D package-name
```

The `-D` saves it under `devDependencies`, which is right for anything used only by tests. Commit both `package.json` and `package-lock.json` afterwards.

### Install problems

- **`npm` is not recognised:** Node.js is not installed, or the terminal was open before you installed it. Close and reopen the terminal.
- **`Executable doesn't exist` when running tests:** the browser is missing. Run `npx playwright install chromium`.
- **On Linux or CI, the browser fails to start:** it is missing system libraries. Run `npx playwright install --with-deps chromium` instead.
- **Strange errors after pulling new code:** someone added or changed a package. Run `npm install` again.
- **Still broken:** delete the `node_modules/` folder and run `npm install` again.

### Step 3: Create your `.env` file

```
cp .env.example .env
```

Fill in the website address and one login per role. Use test accounts only, never a personal account. A role you leave empty is skipped.

### Step 4: Check the login selectors

Open `tests/common/auth.js`. The labels `Email`, `Password` and the `Log in` button are a starting point. Change them to match the real View 360 login page.

### Step 5: Check the roles

Open `tests/common/roles.js`. It lists Admin, Editor and Viewer. Change the list to match the real roles in View 360.

### Step 6: Run the tests

```
npm run test:smoke
```

## Running tests

### By tag

- `npm run test:smoke`: quick checks of the most important features. Runs on every pull request.
- `npm run test:regression`: the deeper checks.
- `npm test`: everything. Runs every night.

### By module

```
npm run test:user-management
npm run test:form-builder
npm run test:workflow-builder
npm run test:templates
npm run test:notifications
```

### By environment

- `npm test` uses `.env`.
- `npm run test:qa` uses `.env.qa`.
- `npm run test:staging` uses `.env.staging`.

Create each file by copying `.env.example`. If you ask for an environment with no file, the run stops with a message telling you which file to create.

## Tags: smoke and regression

Every test should have exactly one tag:

```js
test('Basic category shows the right fields', { tag: '@smoke' }, async ({ formBuilderPage }) => { ... });
```

- **`@smoke`:** the checks that tell you the feature works at all. Keep the whole smoke run under 10 minutes.
- **`@regression`:** everything else.

Ask yourself: "If this broke, should we stop the release?" If yes, it is smoke.

## Fixtures

A fixture is something a test asks for by name, and Playwright prepares it. Instead of setting things up by hand, the test lists what it needs:

```js
test('...', async ({ formBuilderPage, api, cleanup }) => { ... });
```

### Shared fixtures (every module has these)

- **`role`:** which user the test runs as. Admin unless you change it.
- **`api`:** calls the View 360 API as the same user.
- **`cleanup`:** removes test data after the test, even when the test fails.

### Module fixtures

Each module's `fixtures.js` adds its page objects, for example `formBuilderPage`. **Specs always import from their module's fixtures**, never from `@playwright/test`:

```js
const { test, expect } = require('../fixtures');
```

## User roles

Each role logs in once before the tests start, and its session is saved in `.auth/`. Tests run as Admin unless they ask for another role:

```js
test.describe('Viewer permissions', () => {
  test.use({ role: 'viewer' });

  test('Viewer cannot delete a form', { tag: '@regression' }, async ({ formBuilderPage }) => {
    // ...
  });
});
```

If a role has no login in `.env`, any test that uses it fails with a message telling you which settings to add.

### Add a role

1. Add it to `tests/common/roles.js`, for example `manager: { label: 'Manager', envPrefix: 'MANAGER' }`.
2. Add `MANAGER_USER` and `MANAGER_PASSWORD` to your `.env` files and to the GitHub Secrets.
3. Add the two secrets to the `env:` list in `.github/workflows/playwright.yml`.

## Test data and clean-up

Two rules stop tests from clashing with each other and filling the app with junk.

### 1. Give every record a unique name

```js
const { uniqueName } = require('#common/test-data');

const formName = uniqueName('Form'); // AUTO Form 20261123-142501-w0-4821
```

Names start with `AUTO`, so leftover test data is easy to find and delete by hand.

### 2. Register the clean-up as soon as you create something

```js
test('Rename a form', { tag: '@regression' }, async ({ api, cleanup }) => {
  const form = await api.post('/api/forms', { name: uniqueName('Form') });
  cleanup.add('Delete the test form', () => api.delete(`/api/forms/${form.id}`));

  // ... the actual test
});
```

Clean-up steps run after the test, newest first, even when the test fails. They appear in the report as "Clean up: Delete the test form".

### Use the API for setup, the UI for the thing you are testing

Creating data by clicking through the UI is slow and is itself a source of failures. If you are testing "rename a form", create the form through the API and only use the UI for the rename. The API paths above are examples. Ask the developers for the real ones.

## Code checker and formatting

```
npm run check
```

Run this before every commit. It does two things:

- **ESLint** finds bugs, above all a missing `await`. A missing `await` makes a test pass without checking anything, so the checker treats it as an error.
- **Prettier** checks that the code is formatted the same way everywhere.

To fix most problems automatically:

```
npm run lint:fix
npm run format
```

The CI server runs `npm run check` first. If it fails, the tests do not run.

## Running on GitHub (CI)

The file `.github/workflows/playwright.yml` runs the tests on GitHub:

- **Every pull request:** code check, then `@smoke` tests on QA.
- **Every night:** code check, then all tests on QA.
- **By hand:** open the **Actions** tab, choose **Playwright Tests**, click **Run workflow**, then pick the suite and the environment.

### One time setup on GitHub

1. Go to **Settings**, **Environments**, and create two environments: `qa` and `staging`.
2. In each one, add these **Environment secrets**: `BASE_URL`, `API_URL`, `ADMIN_USER`, `ADMIN_PASSWORD`, `EDITOR_USER`, `EDITOR_PASSWORD`, `VIEWER_USER`, `VIEWER_PASSWORD`.
3. Leave `API_URL` empty if the API is on the same address as the website.

### Reading the results

Open the run in the **Actions** tab. At the bottom, under **Artifacts**, download `allure-report` or `playwright-report`. Unzip it and open the report as described below. Reports are kept for 14 days.

## Reports

### Playwright report

```
npm run report
```

Click a failed test to see the step that failed, a plain message explaining what should have happened, a screenshot and a trace.

### Allure report

Allure adds test history, charts and grouping by module. It runs on Node.js, so you do not need Java.

1. Clear old results: `npm run allure:clean`
2. Run the tests: `npm test`
3. Build the report: `npm run allure:generate`
4. Open it: `npm run allure:open`

Always clear old results first. Otherwise results from old runs mix with new ones.

### Group tests by module in Allure

Call `labelTest` in `beforeEach`:

```js
const { labelTest } = require('#common/reporting');
const MODULES = require('#common/modules');

test.beforeEach(async () => {
  await labelTest({ module: MODULES.FORM_BUILDER, feature: 'Field Palette', severity: 'critical' });
});
```

Always use `MODULES.SOMETHING` instead of typing the name. A typo would create a second, separate group. In the report, open the **Behaviors** tab to see tests grouped by module, then by feature.

## Test plans

Each module has a test plan in `docs/test-plans/`, for example `docs/test-plans/form-builder.md`. Each scenario has an ID, the expected result and its tag. Put the plan path at the top of each spec, so anyone can go from a failing test to the scenario it covers.

## Common tasks

### Write the first test for an empty module

1. Add the module's paths to its `routes.js`, for example `list: '/users'`.
2. Add headings, buttons and fields to its page object in `pages/`.
3. Put test data in `data/`.
4. Write the scenarios in its test plan in `docs/test-plans/`.
5. Create a file in `specs/` ending in `.spec.js`. Import `test` and `expect` from `../fixtures`, add a tag to each test, and call `labelTest` in `beforeEach`.
6. Delete the `.gitkeep` file in any folder that now has real files.
7. Run `npm run check`.

### Add a new module

1. Create a folder under `tests/modules/` in lower case with hyphens, for example `tests/modules/reports/`.
2. Copy `fixtures.js`, `routes.js`, `pages/`, `data/` and `specs/` from `form-builder`, and rename the page object.
3. Add its display name to `tests/common/modules.js`, for example `REPORTS: 'Reports'`.
4. Add a run command to `package.json`, for example `"test:reports": "playwright test tests/modules/reports"`.
5. Add a test plan in `docs/test-plans/reports.md`.

### Add a field to a palette category

Open `tests/modules/form-builder/data/palette-categories.js` and add the name to the right list, in the order it appears on screen.

### A test fails after a UI change

1. Read the message in the report. It says what was expected.
2. Look at the diff or screenshot to see what changed.
3. If the app changed on purpose, update the data file or page object. If not, report a bug.

### Update the scripts in this guide

After changing any code, run:

```
npm run docs:build
```

This copies the current code into the section below, so the guide never goes out of date.

## Still to do

1. **Ask developers for test ids on the category icons.** Finding icons by position is the weakest part of these tests.
2. **Check the login selectors and the roles** in `tests/common/auth.js` and `tests/common/roles.js`.
3. **Get the real API paths** for creating and deleting forms, then add clean-up to `createBlankForm`.
4. **Check exact text matching** in the palette tests. If items show extra text, match only the label element.
5. **Add drag and drop tests** and **test the Template category**.
6. **Set up the GitHub environments and secrets** so CI can run.

## Quick check for clarity

Give this project to a junior developer and ask them to add one test to an empty module and run it. If they can do it using only this guide, the setup is clear enough.

## The scripts

These are copied from the real files by `npm run docs:build`. Do not edit them here.

<!-- SCRIPTS:START -->

### `.env.example`

```text
# Copy this file to .env (or .env.qa, .env.staging) and fill in the values.
# Never commit the real files. They hold passwords.

# Website address
BASE_URL=https://qa.yourapp.com

# API address. Leave empty if the API is on the same address as the website.
API_URL=

# One login per role. A role with no login is skipped.
# Use test accounts only, never a personal account.
ADMIN_USER=
ADMIN_PASSWORD=
EDITOR_USER=
EDITOR_PASSWORD=
VIEWER_USER=
VIEWER_PASSWORD=
```

### `.gitignore`

```text
# Saved login sessions
.auth/

# Settings files hold passwords. Only the example is committed.
.env
.env.*
!.env.example

# Created on every run
node_modules/
test-results/
playwright-report/
allure-results/
allure-report/
```

### `package.json`

```json
{
  "name": "view-360",
  "description": "Playwright tests for the View 360 application",
  "private": true,
  "engines": {
    "node": ">=20"
  },
  "imports": {
    "#common/*": "./tests/common/*.js"
  },
  "scripts": {
    "test": "playwright test",
    "test:smoke": "playwright test --grep @smoke",
    "test:regression": "playwright test --grep @regression",
    "test:qa": "cross-env TEST_ENV=qa playwright test",
    "test:staging": "cross-env TEST_ENV=staging playwright test",
    "test:user-management": "playwright test tests/modules/user-management",
    "test:form-builder": "playwright test tests/modules/form-builder",
    "test:workflow-builder": "playwright test tests/modules/workflow-builder",
    "test:templates": "playwright test tests/modules/templates",
    "test:notifications": "playwright test tests/modules/notifications",
    "test:palette": "playwright test field-palette",
    "lint": "eslint .",
    "lint:fix": "eslint . --fix",
    "format": "prettier --write .",
    "format:check": "prettier --check .",
    "check": "npm run lint && npm run format:check",
    "report": "playwright show-report",
    "allure:clean": "node -e \"require('fs').rmSync('allure-results',{recursive:true,force:true})\"",
    "allure:generate": "allure generate ./allure-results --clean",
    "allure:open": "allure open ./allure-report",
    "docs:build": "node tools/build-guide.js"
  },
  "devDependencies": {
    "@eslint/js": "^9.39.5",
    "@playwright/test": "^1.65.1",
    "allure": "^3.33.0",
    "allure-js-commons": "^3.15.0",
    "allure-playwright": "^3.15.0",
    "cross-env": "^10.1.0",
    "dotenv": "^16.4.5",
    "eslint": "^9.39.5",
    "eslint-config-prettier": "^10.1.8",
    "eslint-plugin-playwright": "^2.12.2",
    "globals": "^17.14.0",
    "prettier": "^3.10.1"
  }
}
```

### `playwright.config.js`

```js
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
```

### `eslint.config.js`

```js
// Code checker settings. Run with: npm run lint
// The Playwright plugin catches the most common test bugs, such as a missing await.

const js = require('@eslint/js');
const globals = require('globals');
const playwright = require('eslint-plugin-playwright');
const prettier = require('eslint-config-prettier');

module.exports = [
  { ignores: ['node_modules/', 'playwright-report/', 'test-results/', 'allure-results/', 'allure-report/'] },

  js.configs.recommended,

  {
    languageOptions: { ecmaVersion: 2022, sourceType: 'commonjs', globals: globals.node },
  },

  {
    files: ['tests/**/*.js'],
    ...playwright.configs['flat/recommended'],
    // The login setup file names its test "setup". Tell the checker it is a test.
    settings: { playwright: { globalAliases: { test: ['setup'] } } },
    rules: {
      ...playwright.configs['flat/recommended'].rules,
      // Playwright fixtures need ({}) when they use no other fixture.
      'no-empty-pattern': 'off',
      // Our checks live in page objects and loops, which this rule cannot see.
      'playwright/expect-expect': 'off',
      // Loops that create one test per data row are on purpose.
      'playwright/valid-title': 'off',
      // A missing await is the most common Playwright bug. Make it an error, not a warning.
      'playwright/missing-playwright-await': 'error',
      'playwright/no-focused-test': 'error',
    },
  },

  // Turns off style rules that Prettier handles, so the two never disagree.
  prettier,
];
```

### `.github/workflows/playwright.yml`

```yaml
# Runs the View 360 tests on GitHub.
#
#   Pull request  -> code check + @smoke tests (fast feedback before merging)
#   Every night   -> code check + all tests (full regression)
#   By hand       -> Actions tab, "Playwright Tests", "Run workflow", pick the suite and environment
#
# Logins and addresses come from GitHub Secrets, set per environment:
#   Settings > Environments > qa (or staging) > Environment secrets
#   BASE_URL, API_URL, ADMIN_USER, ADMIN_PASSWORD, EDITOR_USER, EDITOR_PASSWORD, VIEWER_USER, VIEWER_PASSWORD

name: Playwright Tests

on:
  pull_request:
  schedule:
    # 01:17 UTC every day
    - cron: '17 1 * * *'
  workflow_dispatch:
    inputs:
      suite:
        description: Which tests to run
        type: choice
        options: [smoke, regression, all]
        default: smoke
      environment:
        description: Which server to test
        type: choice
        options: [qa, staging]
        default: qa

jobs:
  check:
    name: Code check
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version-file: .nvmrc
          cache: npm
      - run: npm ci
      - run: npm run check

  test:
    name: Tests (${{ inputs.suite || (github.event_name == 'schedule' && 'all') || 'smoke' }} on ${{ inputs.environment || 'qa' }})
    needs: check
    runs-on: ubuntu-latest
    timeout-minutes: 60
    environment: ${{ inputs.environment || 'qa' }}
    env:
      CI: true
      TEST_ENV: ${{ inputs.environment || 'qa' }}
      BASE_URL: ${{ secrets.BASE_URL }}
      API_URL: ${{ secrets.API_URL }}
      ADMIN_USER: ${{ secrets.ADMIN_USER }}
      ADMIN_PASSWORD: ${{ secrets.ADMIN_PASSWORD }}
      EDITOR_USER: ${{ secrets.EDITOR_USER }}
      EDITOR_PASSWORD: ${{ secrets.EDITOR_PASSWORD }}
      VIEWER_USER: ${{ secrets.VIEWER_USER }}
      VIEWER_PASSWORD: ${{ secrets.VIEWER_PASSWORD }}
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version-file: .nvmrc
          cache: npm
      - run: npm ci
      - run: npx playwright install --with-deps chromium

      - name: Run tests
        run: |
          SUITE="${{ inputs.suite || (github.event_name == 'schedule' && 'all') || 'smoke' }}"
          case "$SUITE" in
            smoke) npm run test:smoke ;;
            regression) npm run test:regression ;;
            all) npm test ;;
          esac

      - name: Build Allure report
        if: always()
        run: npm run allure:generate

      - name: Save Allure report
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: allure-report
          path: allure-report/
          retention-days: 14

      - name: Save Playwright report
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: playwright-report
          path: playwright-report/
          retention-days: 14
```

### `tests/common/env.js`

```js
// Reads settings in one place. Every other file asks this file for settings.
//
// Which settings file is used:
//   npm test                    -> .env
//   TEST_ENV=qa npm test        -> .env.qa
//   TEST_ENV=staging npm test   -> .env.staging
//
// On the CI server there is no file. Settings come from GitHub Secrets instead,
// and values that are already set are never overwritten by a file.

const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

const envName = process.env.TEST_ENV;
const fileName = envName ? `.env.${envName}` : '.env';
const filePath = path.resolve(__dirname, '..', '..', fileName);

if (fs.existsSync(filePath)) {
  dotenv.config({ path: filePath, quiet: true });
} else if (envName && !process.env.CI) {
  // On a laptop, asking for an environment that has no file is always a mistake, so stop here.
  // On CI there is no file on purpose: settings come from GitHub Secrets.
  throw new Error(`TEST_ENV is "${envName}" but ${fileName} does not exist. Copy .env.example to ${fileName}.`);
}

function required(name) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`${name} is missing. Add it to ${fileName} (copy .env.example) or to the CI secrets.`);
  }
  return value;
}

module.exports = {
  envName: envName || 'default',
  baseUrl: () => required('BASE_URL'),
  // API address. Most apps serve the API from the same address, so this falls back to BASE_URL.
  apiUrl: () => process.env.API_URL || required('BASE_URL'),
  // Reads one role's login, for example credentialsFor('ADMIN') reads ADMIN_USER and ADMIN_PASSWORD.
  credentialsFor: (envPrefix) => ({
    username: required(`${envPrefix}_USER`),
    password: required(`${envPrefix}_PASSWORD`),
  }),
  hasCredentialsFor: (envPrefix) => Boolean(process.env[`${envPrefix}_USER`] && process.env[`${envPrefix}_PASSWORD`]),
};
```

### `tests/common/roles.js`

```js
// The user roles in View 360. Each role logs in once and gets its own saved session.
//
// To add a role:
//   1. Add it below.
//   2. Add <PREFIX>_USER and <PREFIX>_PASSWORD to your .env file and to the CI secrets.
//
// TODO: Check these match the real roles in View 360.

const path = require('path');

const ROLES = {
  admin: { label: 'Admin', envPrefix: 'ADMIN' },
  editor: { label: 'Editor', envPrefix: 'EDITOR' },
  viewer: { label: 'Viewer', envPrefix: 'VIEWER' },
};

// The role every test uses unless it asks for another one.
const DEFAULT_ROLE = 'admin';

// Where a role's saved login session is stored.
function sessionFile(roleName) {
  return path.resolve(__dirname, '..', '..', '.auth', `${roleName}.json`);
}

module.exports = { ROLES, DEFAULT_ROLE, sessionFile };
```

### `tests/common/auth.js`

```js
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
```

### `tests/common/fixtures.js`

```js
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
```

### `tests/common/api/api-client.js`

```js
// A small wrapper around Playwright's API requests.
// Use it to create and delete test data directly, which is much faster
// and more stable than clicking through the UI.
//
// Every call checks the response and fails with a plain message if the server says no.

const { expect } = require('@playwright/test');

class ApiClient {
  constructor(request) {
    this.request = request;
  }

  async get(url) {
    return this.#send('GET', url);
  }

  async post(url, data) {
    return this.#send('POST', url, data);
  }

  async put(url, data) {
    return this.#send('PUT', url, data);
  }

  async delete(url) {
    return this.#send('DELETE', url);
  }

  async #send(method, url, data) {
    const response = await this.request.fetch(url, { method, data });

    expect(
      response.ok(),
      `${method} ${url} should succeed but returned ${response.status()}: ${await response.text()}`,
    ).toBe(true);

    const body = await response.text();
    return body ? JSON.parse(body) : null;
  }
}

module.exports = { ApiClient };
```

### `tests/common/test-data.js`

```js
// Helpers for creating test data that never clashes with other tests or old runs.

// Makes a name that is unique to this moment and this test worker,
// for example uniqueName('Form') -> 'Form 20261123-142501-w0-4821'.
// The "AUTO" word at the start makes leftover test data easy to find and delete by hand.
function uniqueName(prefix) {
  const stamp = new Date().toISOString().replace(/[-:]/g, '').replace('T', '-').slice(0, 15);
  const worker = process.env.TEST_WORKER_INDEX || '0';
  const random = Math.floor(Math.random() * 10000);
  return `AUTO ${prefix} ${stamp}-w${worker}-${random}`;
}

module.exports = { uniqueName };
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
```

### `tests/modules/form-builder/fixtures.js`

```js
// Fixtures for the Form Builder module.
// Adds this module's page objects on top of the shared fixtures in tests/common/fixtures.js.
// Specs in this module import test and expect from here, not from @playwright/test.

const { test: base, expect } = require('#common/fixtures');
const { FormBuilderPage } = require('./pages/form-builder.page');

const test = base.extend({
  formBuilderPage: async ({ page }, use) => {
    await use(new FormBuilderPage(page));
  },
});

module.exports = { test, expect };
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
      'Text Field',
      'Number',
      'Textarea',
      'Select',
      'Checkbox',
      'Radio',
      'Checkbox Group',
      'Date & Time',
      'Password',
      'Button',
      'HTML Element',
      'Image',
    ],
  },
  {
    key: 'advanced',
    label: 'Advanced',
    iconIndex: 1,
    fields: [
      'File Upload',
      'Image Upload',
      'Signature',
      'Address',
      'Data Grid',
      'Data Table',
      'Sub Form',
      'Modal',
      'Hidden Field',
      'Generator',
      'Scanner',
      'Map',
      'Recaptcha',
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

  // TODO: If "Start From Scratch" saves a draft, every run leaves a form behind.
  // Once you know how to delete a form (API or UI), give the form a name with
  // uniqueName('Form') from #common/test-data and register its deletion with the cleanup fixture.
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
// RUN:     npm run test:form-builder
// DATA:    ../data/palette-categories.js. Add a field or category there. No code changes needed.
// PLAN:    docs/test-plans/form-builder.md (section 3, Field Palette)
// TAGS:    @smoke runs on every pull request. @regression runs every night.

const { test, expect } = require('../fixtures');
const { labelTest } = require('#common/reporting');
const MODULES = require('#common/modules');
const CATEGORIES = require('../data/palette-categories');

test.describe('Field Palette', () => {
  // Runs before every test: open the Form List and create a blank form.
  test.beforeEach(async ({ formBuilderPage }) => {
    await labelTest({ module: MODULES.FORM_BUILDER, feature: 'Field Palette', severity: 'critical' });

    await formBuilderPage.goToFormList();
    await formBuilderPage.createBlankForm();
  });

  // This loop creates one test for each category in the data file.
  for (const category of CATEGORIES) {
    test(`${category.label} category shows the right fields`, { tag: '@smoke' }, async ({ formBuilderPage }) => {
      await formBuilderPage.openCategory(category);

      // toHaveText with a list checks three things at once:
      // the number of fields, their names, and their order.
      await expect(
        formBuilderPage.visibleFields,
        `The ${category.label} category should show: ${category.fields.join(', ')}`,
      ).toHaveText(category.fields);
    });
  }

  test('Only one category is shown at a time', { tag: '@regression' }, async ({ formBuilderPage }) => {
    // Visit every category, then go back to the first one
    // to check that switching back also works.
    const visitOrder = [...CATEGORIES, CATEGORIES[0]];

    for (const category of visitOrder) {
      await formBuilderPage.openCategory(category);

      await expect(
        formBuilderPage.visibleFields,
        `The ${category.label} category should show exactly ${category.fields.length} fields`,
      ).toHaveCount(category.fields.length);

      // Make sure fields from the other categories are hidden.
      const otherCategories = CATEGORIES.filter((other) => other !== category);

      for (const otherCategory of otherCategories) {
        const fieldFromOtherCategory = formBuilderPage.paletteField(otherCategory.fields[0]);

        // expect.soft records the failure but keeps going,
        // so one run shows every problem instead of stopping at the first.
        await expect
          .soft(
            fieldFromOtherCategory,
            `"${otherCategory.fields[0]}" (${otherCategory.label}) should be hidden while ${category.label} is open`,
          )
          .toBeHidden();
      }
    }
  });
});
```

### `tests/modules/user-management/pages/user-management.page.js` (starter page object, same shape in every empty module)

```js
// Page object for the User Management module.
// Keep all the "how to find things on the page" code here,
// so the specs only describe what we are checking.

// When you add methods, bring in what they need, for example:
// const { test, expect } = require('@playwright/test');
// const ROUTES = require('../routes');

class UserManagementPage {
  constructor(page) {
    this.page = page;
    // TODO: Add the headings, buttons and fields this module's tests need.
  }
}

module.exports = { UserManagementPage };
```

<!-- SCRIPTS:END -->
