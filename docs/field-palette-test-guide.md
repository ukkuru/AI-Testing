# Field Palette Tests: Review and Setup Guide

> **Note:** The file paths and setup steps in Parts 4 to 6 are out of date. For the current layout and setup steps, see `docs/playwright-tests-guide.md`. This file is kept as a record of why each change was made.

This guide explains what was wrong with the original field palette script, what we changed, and how to set up and run the new version. It is written for developers who are new to Playwright with JavaScript.

## Part 1: What was wrong with the original script

### 1. Code was copied instead of reused

The Layout test typed out the "create a form" steps again instead of calling the helper function. It also created its own copy of the Layout field list inside the test. Two copies of the same list will drift apart over time.

### 2. Category icons were found by position only

`page.locator('.tool-icon').nth(2)` means "the third icon". If someone adds or moves an icon, the test clicks the wrong one. The click also did not wait for the category to change, so a check could run against the old category and pass by accident.

### 3. Checking fields one by one hid problems

The loop stopped at the first missing field, so you never saw the rest. It also could not catch an extra field, a renamed field, or a wrong order.

### 4. The Advanced test could not fail

It only checked that each field existed on the page (`toHaveCount(1)`), not that the user could see it. A hidden field still passed.

### 5. Smaller issues

- Every test logged in again, which is slow and often flaky.
- The 45 second wait was repeated in many places instead of being set once.
- The screen size was set inside the test file instead of the config.
- The Template category was never tested.
- The website address was imported from the login helper, which mixes two jobs in one file.

## Part 2: What we changed

### Test data lives in one file

All categories and their fields are in `tests/data/palette-categories.js`. The tests read this file and create one test per category. To add a field or a category, you edit the list. No test code changes.

### One check for the whole list

```js
await expect(formBuilder.visibleFields).toHaveText(category.fields);
```

This checks the number of fields, their names and their order in one go. If it fails, the report shows exactly which field is wrong.

### Clicking a category waits for it to open

`openCategory()` clicks the icon, then waits until the first field of that category is visible. No check runs too early.

### Login happens once

`tests/setup/auth.setup.js` logs in one time and saves the session. Every test reuses it, so tests are faster and more stable.

### Every check has a plain message

Each `expect` has a message that says what should happen, for example:

> The Basic category should show: Text Field, Number, Textarea, ...

Anyone reading the report can understand the failure without opening the code.

### Clear names and helpful comments

- `item()` became `paletteField()`.
- `openFromScratch()` became `createBlankForm()`.
- No one letter variable names.
- Comments explain why the code does something, not what it does.
- Every test file starts with a short note: what it tests, how to run it, where the data lives.

## Part 3: Where each kind of information goes

Use this rule: **if it changes between servers or it is a secret, it goes in `.env`. If it is the same everywhere, it goes in a code file.**

- **Field names and categories:** `tests/data/palette-categories.js`. Same on every server.
- **Page paths such as `/formbuilder-v2`:** `tests/data/routes.js`. Same on every server, used in many places.
- **Website address (`https://...`):** `.env` file. Changes between dev, QA and staging.
- **Username and password:** `.env` file only. Never save these in Git.

## Part 4: Project files

```
AI-Testing/
  .env.example                  Template for your .env file (safe to commit)
  .env                          Your real settings (never commit, ignored by Git)
  package.json                  Project packages and run commands
  playwright.config.js          Playwright settings, reads .env
  helpers/
    auth.js                     Your login function (see step 4 below)
  tests/
    field-palette.spec.js       The tests
    data/
      palette-categories.js     Categories and their fields
      routes.js                 Page paths
    pages/
      form-builder.page.js      How to find and use things on the Form Builder page
    setup/
      auth.setup.js             Logs in once before all tests
  docs/
    field-palette-test-guide.md This guide
```

## Part 5: Setup instructions

### Step 1: Install Node.js

Install Node.js version 18 or newer from nodejs.org. Check it worked:

```
node --version
```

### Step 2: Install the project packages

From the project folder, run:

```
npm install
npx playwright install chromium
```

The first command installs Playwright and dotenv. The second downloads the browser Playwright uses.

### Step 3: Create your `.env` file

Copy the template:

```
cp .env.example .env
```

Open `.env` and fill in your values:

```
BASE_URL=https://qa.yourapp.com
TEST_USER=your.test.user@example.com
TEST_PASSWORD=your-password
```

Use a test account, never your personal account.

### Step 4: Check the login helper

`tests/setup/auth.setup.js` calls `login(page)` from `helpers/auth.js`. That file is not in this repo yet. Make sure it:

- Reads the username from `process.env.TEST_USER`.
- Reads the password from `process.env.TEST_PASSWORD`.
- Does not hold the website address. That now comes from `BASE_URL` in `.env`.

### Step 5: Run the tests

Run only the palette tests:

```
npm run test:palette
```

Run everything:

```
npm test
```

Run against a different server without editing any file:

```
BASE_URL=https://staging.yourapp.com npm test
```

### Step 6: Read the report

```
npm run report
```

This opens the HTML report in your browser. Click a failed test to see:

- The step that failed, in plain words.
- The message explaining what should have happened.
- A screenshot and a trace of the failure.

## Part 6: Common tasks

### Add a field to a category

Open `tests/data/palette-categories.js` and add the field name to the right list, in the order it appears on screen:

```js
fields: ['Text Field', 'Number', 'My New Field'],
```

### Add a new category

Add a new block to the same file:

```js
{
  label: 'Template',
  iconIndex: 3, // position of the icon, counting from 0
  fields: ['Field One', 'Field Two'],
},
```

A new test for it appears automatically.

### A category test fails after a UI change

1. Open the report and read the message. It tells you which fields were expected.
2. Look at the diff to see which field is missing, extra, renamed or moved.
3. If the app changed on purpose, update the data file. If not, report a bug.

## Part 7: Things still to do

1. **Ask developers for test ids on the category icons.** Finding icons by position is the weakest part of these tests. A `data-testid="palette-basic"` on each icon would fix it for good.
2. **Check exact text matching.** If palette items show extra text, such as an icon label, the list check may fail. Switch to matching only the label element if that happens.
3. **Confirm whether field order matters.** If it does not, sort both lists before comparing.
4. **Add drag and drop tests.** The palette exists so users can drag fields onto the form. One test per category that drags the first field onto the canvas would cover the real user goal.
5. **Clean up test forms.** If "Start From Scratch" saves a draft, the tests will slowly fill the form list. Add a step to delete the form after each test.

## Quick check for clarity

Give this project to a junior developer and ask them to add one field to the Basic list and run the tests. If they can do it using only this guide, the setup is clear enough.
