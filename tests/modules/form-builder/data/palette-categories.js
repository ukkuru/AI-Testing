// Single source of truth for what each palette category must show.
// Order matters: the spec asserts the exact ordered list, so a renamed,
// missing, extra or reordered field fails with one clear diff.
module.exports = [
  {
    key: 'basic',
    label: 'Basic',
    iconIndex: 0, // TODO: replace with data-testid once dev adds one
    fields: [
      'Text Field', 'Number', 'Textarea', 'Select', 'Checkbox', 'Radio',
      'Checkbox Group', 'Date & Time', 'Password', 'Button', 'HTML Element', 'Image',
    ],
  },
  {
    key: 'advanced',
    label: 'Advanced',
    iconIndex: 1,
    fields: [
      'File Upload', 'Image Upload', 'Signature', 'Address', 'Data Grid', 'Data Table',
      'Sub Form', 'Modal', 'Hidden Field', 'Generator', 'Scanner', 'Map', 'Recaptcha',
    ],
  },
  {
    key: 'layout',
    label: 'Layout',
    iconIndex: 2,
    fields: ['Tabs', 'Accordion', 'Custom Grid', 'Section'],
  },
];
