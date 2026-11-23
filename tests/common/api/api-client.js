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
