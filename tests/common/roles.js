// The user roles in View 360. Each role logs in once and gets its own saved session.
//
// To add a role:
//   1. Add it below.
//   2. Add <PREFIX>_USER and <PREFIX>_PASSWORD to your .env file and to the CI secrets.
//
// TODO: Check these match the real roles in View 360.

const path = require('path');

const ROLES = {
  admin: { label: 'Admin', envPrefix: 'ADMIN' },
  editor: { label: 'Editor', envPrefix: 'EDITOR' },
  viewer: { label: 'Viewer', envPrefix: 'VIEWER' },
};

// The role every test uses unless it asks for another one.
const DEFAULT_ROLE = 'admin';

// Where a role's saved login session is stored.
function sessionFile(roleName) {
  return path.resolve(__dirname, '..', '..', '.auth', `${roleName}.json`);
}

module.exports = { ROLES, DEFAULT_ROLE, sessionFile };
