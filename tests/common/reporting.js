// Labels for the Allure report, so tests are grouped by module and feature.
// Call this at the start of a test or in beforeEach.

const allure = require('allure-js-commons');

async function labelTest({ module, feature, severity = 'normal' }) {
  await allure.epic(module);
  await allure.feature(feature);
  await allure.severity(severity);
}

module.exports = { labelTest };
