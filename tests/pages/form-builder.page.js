const { test, expect } = require('@playwright/test');

class FormBuilderPage {
  constructor(page) {
    this.page = page;
    this.formListHeading = page.getByRole('heading', { name: 'Form List' });
    this.builderHeading = page.getByRole('heading', { name: 'Form Builder' });
    this.categoryIcons = page.locator('.tool-icon');
    // Only items the user can actually see. Hidden categories left in the DOM are ignored.
    this.visibleItems = page.locator('[draggable="true"]:visible');
  }

  async gotoFormList() {
    await test.step('Open the Form List page', async () => {
      await this.page.goto('/formbuilder-v2');
      await expect(
        this.formListHeading,
        'The Form List page should load after login. If this fails, the user may be logged out or the page is down.',
      ).toBeVisible();
    });
  }

  async openFromScratch() {
    await test.step('Create a new blank form ("+ Create Form" then "Start From Scratch")', async () => {
      await this.page.getByRole('button', { name: '+ Create Form' }).click();
      await this.page.getByText('Start From Scratch', { exact: true }).first().click();
      await expect(
        this.builderHeading,
        'The Form Builder should open after choosing "Start From Scratch".',
      ).toBeVisible();
    });
  }

  item(name) {
    return this.page.locator('[draggable="true"]').filter({
      has: this.page.getByText(name, { exact: true }),
    });
  }

  // Click the icon AND wait until the category's first field is on screen,
  // so no assertion ever runs against the previous category.
  async openCategory(category) {
    await test.step(`Open the ${category.label} category in the field palette`, async () => {
      await this.categoryIcons.nth(category.iconIndex).click();
      await expect(
        this.item(category.fields[0]),
        `Clicking the ${category.label} icon should show the ${category.label} fields, starting with "${category.fields[0]}". ` +
          'If this fails, the icon order may have changed or the palette did not switch.',
      ).toBeVisible();
    });
  }
}

module.exports = { FormBuilderPage };
