import { axeViolations, expect, test } from '../support/fixtures';
import { storageStateOf } from '../support/profiles';

// TASK-013 "Kelola unit" (PG-505, WF-029 F1): an administrator finds a unit in the searchable tree, proposes a sub-unit below
// it, takes the request through validation and a decision, applies it and lands on the new unit with its history. Without an
// official organization.maintenance version the decision is a simulation (TAC-04), and both the request and the history say
// so. Every browser project makes its own unit below the synthetic Fakultas Teknik of OrganizationSeeder.

test.use({ storageState: storageStateOf('super-admin') });

test('an administrator proposes, decides and applies a new unit and sees it in the tree with its history', async ({
    page,
}) => {
    const suffix = `${test.info().project.name.replaceAll('-', '_').toUpperCase()}_${Date.now()}`;
    const code = `E2E.${suffix}`;
    const name = `Unit Uji ${suffix}`;

    await page.goto('/admin/organization');
    await expect(
        page.getByRole('heading', { name: 'Organisasi', level: 1 }),
    ).toBeVisible();
    expect(await axeViolations(page)).toEqual([]);

    // The search keeps a match with the units above it.
    const tree = page.locator('[data-test="unit-tree"]');

    await page.locator('#unit-search').fill('Teknik Informatika');
    await expect(
        tree.getByRole('link', { name: /Program Studi Teknik Informatika/ }),
    ).toBeVisible();
    await expect(
        tree.getByRole('link', { name: /^Fakultas Teknik/ }),
    ).toBeVisible();
    await expect(
        tree.getByRole('link', { name: /Biro Umum dan Keuangan/ }),
    ).toHaveCount(0);

    // The selected unit is in the URL.
    await tree.getByRole('link', { name: /^Fakultas Teknik/ }).click();
    await expect(page).toHaveURL(/\/admin\/organization\?unit=[0-9a-z]{26}$/);
    await expect(page.locator('[data-test="unit-detail"]')).toContainText(
        'Fakultas Teknik',
    );
    expect(await axeViolations(page)).toEqual([]);

    // A sub-unit is proposed below the selected unit.
    await page.locator('[data-test="new-sub-unit"]').click();
    await expect(page).toHaveURL(/\/admin\/organization\/requests\/create\?/);
    await expect(page.locator('#parent option:checked')).toContainText(
        'Fakultas Teknik',
    );
    await page.locator('#code').fill(code);
    await page.locator('#name').fill(name);
    await page.locator('#unit_type').fill('PRODI');
    await page
        .locator('#reason')
        .fill('Pembukaan program studi baru sesuai rencana strategis');
    expect(await axeViolations(page)).toEqual([]);
    await page.locator('[data-test="save-request-button"]').click();

    // Nothing changes before the request is in force: it is a draft first.
    await expect(page).toHaveURL(
        /\/admin\/organization\/requests\/[0-9a-z]{26}$/,
    );
    await expect(page.getByText('Draf', { exact: true }).first()).toBeVisible();

    await page.locator('[data-test="submit-request-button"]').click();
    await expect(page.locator('[data-test="request-issues"]')).toContainText(
        'Tidak ada masalah ditemukan.',
    );

    await page.locator('[data-test="escalate-request-button"]').click();
    await expect(page.locator('[data-test="policy-simulation"]')).toBeVisible();
    expect(await axeViolations(page)).toEqual([]);

    await page.locator('[data-test="decide-request-button"]').click();

    const dialog = page.getByRole('dialog');

    await expect(dialog).toBeVisible();
    await dialog.locator('#decision_ref').fill('SK/E2E/2026/001');
    expect(await axeViolations(page)).toEqual([]);
    await dialog.locator('[data-test="confirm-decision-button"]').click();
    await expect(dialog).toBeHidden();

    await page.locator('[data-test="apply-request-button"]').click();

    // Success lands on the new unit with its history, marked as a simulation.
    await expect(page).toHaveURL(/\/admin\/organization\?unit=[0-9a-z]{26}$/);
    await expect(
        page.getByRole('heading', { name, level: 2 }),
    ).toBeVisible();

    const history = page.locator('[data-test="unit-history"]');

    await expect(history).toContainText('Unit dibentuk');
    await expect(history).toContainText('SK/E2E/2026/001');
    await expect(history).toContainText('Simulasi');
    await expect(page.locator('[data-test="unit-detail"]')).toContainText(
        'Fakultas Teknik',
    );
    expect(await axeViolations(page)).toEqual([]);

    // The new unit is in the tree below its parent, and the back action returns to the tree.
    await page.locator('#unit-search').fill(code);
    await expect(tree.getByRole('link', { name: new RegExp(name) })).toBeVisible();
    await page
        .getByRole('link', { name: 'Daftar unit', exact: true })
        .click();
    await expect(page).toHaveURL(/\/admin\/organization$/);
});
