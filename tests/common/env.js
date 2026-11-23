// Reads settings from the .env file in one place.
// Every other file asks this file for settings instead of reading process.env directly,
// so a missing value fails early with a clear message.

require('dotenv').config();

function required(name) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`${name} is missing. Copy .env.example to .env and fill it in.`);
  }
  return value;
}

module.exports = {
  baseUrl: () => required('BASE_URL'),
  testUser: () => required('TEST_USER'),
  testPassword: () => required('TEST_PASSWORD'),
};
