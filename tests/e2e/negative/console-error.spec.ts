import { test } from '../support/fixtures';

// Must fail. Only the CI step that proves TAC-02 runs it, with E2E_NEGATIVE set, and it refuses a pass.
test('an injected console error fails the test', async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => console.error('injected console error'));
});
