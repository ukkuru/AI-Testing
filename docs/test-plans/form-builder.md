# Test Plan: Form Builder

Module folder: `tests/modules/form-builder/`

## 1. Form List

TODO

## 2. Create a Form

TODO

## 3. Field Palette

Spec: `tests/modules/form-builder/specs/field-palette.spec.js`
Data: `tests/modules/form-builder/data/palette-categories.js`

| ID     | Scenario                                      | Expected result                                                                                                                                              | Tag         |
| ------ | --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------- |
| FB-3.1 | Open the Basic category                       | Shows Text Field, Number, Textarea, Select, Checkbox, Radio, Checkbox Group, Date & Time, Password, Button, HTML Element, Image, in that order               | @smoke      |
| FB-3.2 | Open the Advanced category                    | Shows File Upload, Image Upload, Signature, Address, Data Grid, Data Table, Sub Form, Modal, Hidden Field, Generator, Scanner, Map, Recaptcha, in that order | @smoke      |
| FB-3.3 | Open the Layout category                      | Shows Tabs, Accordion, Custom Grid, Section, in that order                                                                                                   | @smoke      |
| FB-3.4 | Switch between all categories and back        | Only the open category's fields are visible                                                                                                                  | @regression |
| FB-3.5 | Open the Template category                    | Not covered yet                                                                                                                                              |             |
| FB-3.6 | Drag a field from each category onto the form | Not covered yet                                                                                                                                              |             |
