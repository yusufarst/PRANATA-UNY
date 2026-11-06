import { axeViolations, expect, test } from '../support/fixtures';
import { storageStateOf } from '../support/profiles';

// TASK-016 PG-012 "Bukti objek" (RT-031, IX-011): the organization administrator opens the decree evidence of the synthetic
// appointment of ScenarioSeeder from the unit on PG-505, uploads a small PNG, downloads it through the verified route and
// returns to the exact unit. An account that neither manages the organization nor holds the appointment is refused by the
// server. Every browser project uploads its own file, so the projects never release each other's.

// A 1x1 PNG: real image bytes, so the server's signature check passes.
const PNG = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
    'base64',
);

test.describe('an account without a right on the appointment', () => {
    test.use({ storageState: storageStateOf('vendor') });

    test('cannot open the evidence page', async ({ page, criticalErrors }) => {
        const response = await page.goto('/files/evidence/org_appointments/1');

        expect(response?.status()).toBe(403);

        // The refused request is the point of this test, and the browser logs it as a console error.
        criticalErrors.length = 0;
    });
});

test.describe('the organization administrator', () => {
    test.use({ storageState: storageStateOf('super-admin') });

    test('uploads the decree of an appointment, downloads it verified and returns to the unit', async ({
        page,
    }) => {
        const name = `sk-${test.info().project.name}-${Date.now()}.png`;

        await page.goto('/admin/organization');
        await page.locator('#unit-search').fill('Teknik Informatika');
        await page
            .locator('[data-test="unit-tree"]')
            .getByRole('link', { name: /Program Studi Teknik Informatika/ })
            .click();
        await expect(page).toHaveURL(
            /\/admin\/organization\?unit=[0-9a-z]{26}$/,
        );

        const unitUrl = page.url();
        const appointments = page.locator('[data-test="unit-appointments"]');

        // The decree reference names the synthetic appointment; the person's name is changed by another journey.
        await expect(appointments).toContainText('SK/SINTETIS/2026/001');
        await appointments
            .locator('[data-test="appointment-evidence"]')
            .first()
            .click();

        await expect(page).toHaveURL(
            /\/files\/evidence\/org_appointments\/\d+$/,
        );
        await expect(
            page.getByRole('heading', { name: 'Bukti objek', level: 1 }),
        ).toBeVisible();
        await expect(
            page.locator('[data-test="evidence-owner"]'),
        ).toContainText('SK/SINTETIS/2026/001');
        expect(await axeViolations(page)).toEqual([]);

        await page.locator('[data-test="upload-evidence-button"]').click();

        const dialog = page.getByRole('dialog');

        await expect(dialog).toBeVisible();
        await dialog
            .locator('[data-test="evidence-file-input"]')
            .setInputFiles({ name, mimeType: 'image/png', buffer: PNG });
        await expect(
            dialog.locator('[data-test="selected-file"]'),
        ).toContainText(name);
        await dialog.locator('#evidence_label').fill('SK pengangkatan');
        expect(await axeViolations(page)).toEqual([]);
        await dialog.locator('[data-test="upload-button"]').click();
        await expect(dialog).toBeHidden();

        // The file is listed with its fingerprint, not yet scanned, and the toast confirms the upload.
        await expect(
            page.getByText('Bukti sudah diunggah dan tersimpan.'),
        ).toBeVisible();

        const row = page
            .locator('[data-test="evidence-row"]')
            .filter({ hasText: name });

        await expect(row).toContainText('Belum dipindai');
        await expect(row).toContainText('SK pengangkatan');
        expect(await axeViolations(page)).toEqual([]);

        // The download goes through the verified route only: an attachment with nosniff and the exact bytes.
        const href = await row
            .locator('[data-test="download-link"]')
            .getAttribute('href');

        expect(href).toMatch(/^\/files\/[0-9a-z]{26}$/);

        const download = await page.request.get(href ?? '');

        expect(download.status()).toBe(200);
        expect(download.headers()['content-disposition']).toContain(
            'attachment',
        );
        expect(download.headers()['x-content-type-options']).toBe('nosniff');
        expect(download.headers()['content-type']).toBe('image/png');
        expect(Buffer.compare(await download.body(), PNG)).toBe(0);

        // Back to the exact parent, which now says the appointment has its decree.
        await page.locator('[data-test="evidence-back"]').click();
        await expect(page).toHaveURL(unitUrl);
        await expect(appointments).toContainText('Ada bukti SK');
    });
});
