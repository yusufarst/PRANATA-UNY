import { expect, test } from '../support/fixtures';
import { PROFILES, storageStateOf } from '../support/profiles';

// Runs first: the other projects depend on it, so a broken shared fixture stops them as one setup failure.
for (const profile of PROFILES) {
    test.describe(`profile ${profile}`, () => {
        test.use({ storageState: storageStateOf(profile) });

        test('its saved login opens the shell', async ({ page }) => {
            await page.goto('/home');

            await expect(page).toHaveURL(/\/home$/);
            await expect(page.locator('main')).toBeVisible();
        });
    });
}
