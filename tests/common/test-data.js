// Helpers for creating test data that never clashes with other tests or old runs.

// Makes a name that is unique to this moment and this test worker,
// for example uniqueName('Form') -> 'Form 20261123-142501-w0-4821'.
// The "AUTO" word at the start makes leftover test data easy to find and delete by hand.
function uniqueName(prefix) {
  const stamp = new Date().toISOString().replace(/[-:]/g, '').replace('T', '-').slice(0, 15);
  const worker = process.env.TEST_WORKER_INDEX || '0';
  const random = Math.floor(Math.random() * 10000);
  return `AUTO ${prefix} ${stamp}-w${worker}-${random}`;
}

module.exports = { uniqueName };
