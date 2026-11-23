const { expect } = require('@playwright/test');

class FormBuilderPage {
  constructor(page) {
    this.page = page;
    this.heading = page.getByRole('heading', { name: 'Form Builder' });
    this.categoryIcons = page.locator('.tool-icon');
    // Only items the user can actually see. Hidden categories left in the DOM are ignored.
    this.visibleItems = page.locator('[draggable="true"]:visible');
  }

  async openFromScratch() {
    await this.page.getByRole('button', { name: '+ Create Form' }).click();
    await this.page.getByText('Start From Scratch', { exact: true }).first().click();
    await expect(this.heading).toBeVisible();
  }

  item(name) {
    return this.page.locator('[draggable="true"]').filter({
      has: this.page.getByText(name, { exact: true }),
    });
  }

  // Click the icon AND wait until the category's first field is on screen,
  // so no assertion ever runs against the previous category.
  async openCategory(category) {
    await this.categoryIcons.nth(category.iconIndex).click();
    await expect(this.item(category.fields[0])).toBeVisible();
  }
}

module.exports = { FormBuilderPage };
