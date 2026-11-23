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
