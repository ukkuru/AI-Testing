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
