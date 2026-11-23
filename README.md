# View 360 Playwright Tests

Automated tests for the View 360 application, written with Playwright and JavaScript.
Tests are organised by module: User Management, Form Builder, Workflow Builder, Templates and Notifications.

## Quick start

```
npm install
npx playwright install chromium
cp .env.example .env
```

Fill in `.env` with the website address and test logins, then run:

```
npm run test:smoke
```

## Common commands

- `npm test`: run every test
- `npm run test:smoke`: quick checks, a few minutes
- `npm run test:form-builder`: one module (same for every module)
- `npm run test:staging`: run against staging (needs `.env.staging`)
- `npm run check`: code checker and formatting check, run before every commit
- `npm run report`: open the Playwright report
- `npm run allure:generate` then `npm run allure:open`: open the Allure report

## Full guide

Setup, folder rules, roles, test data, CI and every script: [docs/playwright-tests-guide.md](docs/playwright-tests-guide.md)

Test plans for each module: [docs/test-plans/](docs/test-plans/)
