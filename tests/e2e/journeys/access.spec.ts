import type { Page } from '@playwright/test';
import { axeViolations, expect, test } from '../support/fixtures';
import { storageStateOf, usernameOf } from '../support/profiles';

// TASK-014 PG-503 "Akses": the menu follows the grants (TAC-01), and an administrator gives an ordinary grant, finds it in the
// list and revokes it with a reason. Every browser project grants on its own unit of OrganizationSeeder, so the projects never
// revoke each other's grant.

const UNIT_OF_PROJECT: Record<string, string> = {
    'chromium-mobile': 'FT.TI',
    'chromium-desktop': 'FT.TS',
    'webkit-mobile': 'LPPM',
    'webkit-desktop': 'BUK.PBJ',
};

// On a phone the menu is in the drawer; on a wide screen it is the sidebar. By attribute: the toggle's name follows the
// language of the account.
async function openMenu(page: Page): Promise<void> {
    if ((page.viewportSize()?.width ?? 0) < 640) {
        await page.locator('[data-sidebar="trigger"]').first().click();
        await expect(page.getByRole('dialog')).toBeVisible();
    }
}

test.describe('an account without a grant', () => {
    test.use({ storageState: storageStateOf('internal') });

    // By href: other specs switch the language of this account.
    test('sees no administration entry and cannot open PG-503 directly', async ({
        page,
        criticalErrors,
    }) => {
        await page.goto('/home');
        await openMenu(page);
        await expect(page.locator('a[href="/home"]').first()).toBeAttached();
        await expect(page.locator('a[href="/admin/access"]')).toHaveCount(0);
        await expect(page.locator('a[href="/admin/users"]')).toHaveCount(0);

        // TAC-05: the server refuses the route itself.
        const response = await page.goto('/admin/access');

        expect(response?.status()).toBe(403);

        // The refused request is the point of this test, and the browser logs it as a console error.
        criticalErrors.length = 0;
    });
});

test.describe('an access administrator', () => {
    test.use({ storageState: storageStateOf('super-admin') });

    test('opens Akses from the menu, gives an ordinary grant and revokes it with a reason', async ({
        page,
    }) => {
        const unit = UNIT_OF_PROJECT[test.info().project.name] ?? 'FT';
        const username = usernameOf('internal');

        await page.goto('/home');
        await openMenu(page);
        await page.getByRole('link', { name: 'Akses', exact: true }).click();

        await expect(page).toHaveURL(/\/admin\/access$/);
        await expect(
            page.getByRole('heading', { name: 'Akses', level: 1 }),
        ).toBeVisible();
        expect(await axeViolations(page)).toEqual([]);

        await page.locator('[data-test="grant-access"]').first().click();
        await expect(page).toHaveURL(/\/admin\/access\/create$/);

        await page.locator('#username').fill(username);
        await page.getByLabel('Satu kemampuan').check();
        await page.locator('#capability').selectOption('asset.view');
        await page.locator('#scope_type').selectOption('ORG_UNIT_SUBTREE');

        // The option shows the unit indented by its depth, so its value selects it.
        const option = page
            .locator('#unit option')
            .filter({ hasText: `(${unit})` });
        const value = (await option.getAttribute('value')) ?? '';

        await page.locator('#unit').selectOption(value);
        await page
            .locator('#reason')
            .fill('Penugasan staf aset sesuai surat tugas');
        // An ordinary grant applies at once, so no approval notice.
        await expect(
            page.locator('[data-test="privileged-notice"]'),
        ).toHaveCount(0);
        expect(await axeViolations(page)).toEqual([]);
        await page.locator('[data-test="save-grant-button"]').click();

        // Success lands on the list with a toast; the filters stay in the URL.
        await expect(page).toHaveURL(/\/admin\/access$/);
        await expect(
            page.getByText('Akses sudah diberikan dan langsung berlaku.'),
        ).toBeVisible();

        await page.locator('#filter-q').fill(username);
        await page.locator('#filter-status').selectOption('ACTIVE');
        await page.getByRole('button', { name: 'Terapkan' }).click();
        await expect(page).toHaveURL(/[?&]status=ACTIVE/);
        await expect(page).toHaveURL(/[?&]q=/);

        // A table on a wide screen, cards on a phone.
        const entry = page
            .locator('tr:visible, li:visible')
            .filter({ hasText: `(${unit})` })
            .first();

        await expect(entry).toContainText('asset.view');
        await entry.getByRole('link').first().click();

        await expect(page).toHaveURL(/\/admin\/access\/[0-9a-z]{26}$/);
        await expect(
            page.getByText('Aktif', { exact: true }).first(),
        ).toBeVisible();
        expect(await axeViolations(page)).toEqual([]);

        await page.locator('[data-test="revoke-grant-button"]').click();

        const dialog = page.getByRole('dialog');

        await expect(dialog).toBeVisible();
        await dialog.locator('#revoke_reason').fill('Masa tugas sudah berakhir');
        expect(await axeViolations(page)).toEqual([]);
        await dialog
            .locator('[data-test="confirm-revoke-grant-button"]')
            .click();
        await expect(dialog).toBeHidden();

        // The grant stays as history with its reason, and cannot be revoked again.
        await expect(
            page.getByText('Dicabut', { exact: true }).first(),
        ).toBeVisible();
        await expect(page.getByText('Masa tugas sudah berakhir')).toBeVisible();
        await expect(
            page.locator('[data-test="revoke-grant-button"]'),
        ).toHaveCount(0);

        await page
            .getByRole('link', { name: 'Kembali ke daftar akses' })
            .click();
        await expect(page).toHaveURL(/\/admin\/access$/);
    });
});
