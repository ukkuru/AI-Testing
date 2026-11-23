// Copies the current code into docs/playwright-tests-guide.md, so the guide never goes out of date.
// Run with: npm run docs:build
// It replaces everything between the SCRIPTS:START and SCRIPTS:END markers in the guide.

const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const guidePath = path.join(root, 'docs', 'playwright-tests-guide.md');

// The files shown in the guide, in reading order.
const FILES = [
  '.env.example',
  '.gitignore',
  'package.json',
  'playwright.config.js',
  'eslint.config.js',
  '.github/workflows/playwright.yml',
  'tests/common/env.js',
  'tests/common/roles.js',
  'tests/common/auth.js',
  'tests/common/fixtures.js',
  'tests/common/api/api-client.js',
  'tests/common/test-data.js',
  'tests/common/modules.js',
  'tests/common/reporting.js',
  'tests/common/routes.js',
  'tests/setup/auth.setup.js',
  'tests/modules/form-builder/fixtures.js',
  'tests/modules/form-builder/routes.js',
  'tests/modules/form-builder/data/palette-categories.js',
  'tests/modules/form-builder/pages/form-builder.page.js',
  'tests/modules/form-builder/specs/field-palette.spec.js',
  'tests/modules/user-management/pages/user-management.page.js',
];

const LANGUAGE = { '.js': 'js', '.json': 'json', '.yml': 'yaml' };

const sections = FILES.map((file) => {
  const code = fs.readFileSync(path.join(root, file), 'utf8').trimEnd();
  const language = LANGUAGE[path.extname(file)] || 'text';
  const note = file.includes('user-management') ? ' (starter page object, same shape in every empty module)' : '';
  return `### \`${file}\`${note}\n\n\`\`\`${language}\n${code}\n\`\`\``;
});

const START = '<!-- SCRIPTS:START -->';
const END = '<!-- SCRIPTS:END -->';
const guide = fs.readFileSync(guidePath, 'utf8');

if (!guide.includes(START) || !guide.includes(END)) {
  throw new Error(`The guide is missing the ${START} and ${END} markers.`);
}

const before = guide.split(START)[0];
const after = guide.split(END)[1];
fs.writeFileSync(guidePath, `${before}${START}\n\n${sections.join('\n\n')}\n\n${END}${after}`);
console.log(`Updated ${path.relative(root, guidePath)} with ${FILES.length} files.`);
