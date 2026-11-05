import type { Page } from '@playwright/test';
import { axeViolations, expect, test } from '../support/fixtures';
import { storageStateOf } from '../support/profiles';

// TASK-015 PG-504 "Penugasan": an access administrator opens the list from the menu and the page that opens one object, and an
// account without admin.access.grant is refused by the server. No module registers a kind of object yet (packages and
// warehouse requests come later), so the object page explains that instead of showing a dead form; assigning through the panel
// is covered by tests/Feature/Access/ProcessAssignmentTest.php.

// On a phone the menu is in the drawer; on a wide screen it is the sidebar.
async function openMenu(page: Page): Promise<void> {
    if ((page.viewportSize()?.width ?? 0) < 640) {
        await page.locator('[data-sidebar="trigger"]').first().click();
        await expect(page.getByRole('dialog')).toBeVisible();
    }
}

test.describe('an account without a grant', () => {
    test.use({ storageState: storageStateOf('internal') });

    // By href: other specs switch the language of this account.
    test('sees no assignment entry and cannot open PG-504 directly', async ({
        page,
        criticalErrors,
    }) => {
        await page.goto('/home');
        await openMenu(page);
        await expect(page.locator('a[href="/home"]').first()).toBeAttached();
        await expect(page.locator('a[href="/admin/assignments"]')).toHaveCount(
            0,
        );

        const response = await page.goto('/admin/assignments');

        expect(response?.status()).toBe(403);

        // The refused request is the point of this test, and the browser logs it as a console error.
        criticalErrors.length = 0;
    });
});

test.describe('an access administrator', () => {
    test.use({ storageState: storageStateOf('super-admin') });

    test('opens Penugasan from the menu and the page that opens an object, and returns to the list', async ({
        page,
    }) => {
        await page.goto('/home');
        await openMenu(page);
        await page
            .getByRole('link', { name: 'Penugasan', exact: true })
            .click();

        await expect(page).toHaveURL(/\/admin\/assignments$/);
        await expect(
            page.getByRole('heading', { name: 'Penugasan', level: 1 }),
        ).toBeVisible();
        expect(await axeViolations(page)).toEqual([]);

        await page.locator('#filter-status').selectOption('ACTIVE');
        await page.getByRole('button', { name: 'Terapkan' }).click();
        await expect(page).toHaveURL(/[?&]status=ACTIVE/);

        await page
            .locator('[data-test="open-assignment-object"]')
            .first()
            .click();
        await expect(page).toHaveURL(/\/admin\/assignments\/object$/);
        await expect(
            page.getByRole('heading', { name: 'Buka objek', level: 1 }),
        ).toBeVisible();
        await expect(page.getByText('Belum ada jenis objek')).toBeVisible();
        expect(await axeViolations(page)).toEqual([]);

        await page
            .getByRole('link', { name: 'Kembali ke daftar penugasan' })
            .click();
        await expect(page).toHaveURL(/\/admin\/assignments$/);
    });
});
