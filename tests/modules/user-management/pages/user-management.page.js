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
