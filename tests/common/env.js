// Reads settings in one place. Every other file asks this file for settings.
//
// Which settings file is used:
//   npm test                    -> .env
//   TEST_ENV=qa npm test        -> .env.qa
//   TEST_ENV=staging npm test   -> .env.staging
//
// On the CI server there is no file. Settings come from GitHub Secrets instead,
// and values that are already set are never overwritten by a file.

const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

const envName = process.env.TEST_ENV;
const fileName = envName ? `.env.${envName}` : '.env';
const filePath = path.resolve(__dirname, '..', '..', fileName);

if (fs.existsSync(filePath)) {
  dotenv.config({ path: filePath, quiet: true });
} else if (envName && !process.env.CI) {
  // On a laptop, asking for an environment that has no file is always a mistake, so stop here.
  // On CI there is no file on purpose: settings come from GitHub Secrets.
  throw new Error(`TEST_ENV is "${envName}" but ${fileName} does not exist. Copy .env.example to ${fileName}.`);
}

function required(name) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`${name} is missing. Add it to ${fileName} (copy .env.example) or to the CI secrets.`);
  }
  return value;
}

module.exports = {
  envName: envName || 'default',
  baseUrl: () => required('BASE_URL'),
  // API address. Most apps serve the API from the same address, so this falls back to BASE_URL.
  apiUrl: () => process.env.API_URL || required('BASE_URL'),
  // Reads one role's login, for example credentialsFor('ADMIN') reads ADMIN_USER and ADMIN_PASSWORD.
  credentialsFor: (envPrefix) => ({
    username: required(`${envPrefix}_USER`),
    password: required(`${envPrefix}_PASSWORD`),
  }),
  hasCredentialsFor: (envPrefix) => Boolean(process.env[`${envPrefix}_USER`] && process.env[`${envPrefix}_PASSWORD`]),
};
