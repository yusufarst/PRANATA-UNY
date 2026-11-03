import type { Browser, Page } from '@playwright/test';
import {
    axeViolations,
    collectCriticalErrors,
    expect,
    test,
} from '../support/fixtures';
import { runId, storageStateOf } from '../support/profiles';
import { nextTotpCode, totpCode } from '../support/totp';

// TASK-012 TAC-01: an account sets up TOTP from the manual key, confirms it with a code from the app, then signs in with a
// fresh code and once with a recovery code, which is then used up. Every browser project makes its own account. Each
// account signs in four times, under the login limit of five a minute.

const NEW_PASSWORD = 'Dua-Langkah-2026!a';

test.use({ storageState: storageStateOf('super-admin') });

async function signIn(page: Page, login: string, password: string) {
    await page.goto('/login');
    await page.locator('#login').fill(login);
    await page.locator('#password').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

// The owner's own browser, without the administrator's cookies.
async function ownerPage(browser: Browser, baseURL: string | undefined) {
    const context = await browser.newContext({
        baseURL,
        storageState: { cookies: [], origins: [] },
    });
    const page = await context.newPage();

    return { context, page, errors: collectCriticalErrors(page) };
}

test('an account sets up TOTP and signs in with a code or a one-time recovery code', async ({
    page,
    browser,
    baseURL,
}) => {
    // A second sign-in may wait up to one TOTP step for a fresh code.
    test.setTimeout(120_000);

    const suffix = `${test.info().project.name.replaceAll('-', '_')}_${Date.now()}`;
    const username = `tfa_${suffix}`;
    const name = `E2E 2FA ${suffix} ${runId()}`;

    await page.goto('/admin/users/create');
    await page.locator('#name').fill(name);
    await page.locator('#username').fill(username);
    await page.locator('[data-test="save-account-button"]').click();

    const activation = page.locator('[data-test="issued-secret-value"]');

    await expect(activation).toBeVisible();
    const code = (await activation.textContent())?.trim() ?? '';

    expect(code).not.toBe('');

    const owner = await ownerPage(browser, baseURL);

    try {
        await signIn(owner.page, username, code);
        await expect(owner.page).toHaveURL(/\/password\/change$/);
        await owner.page.locator('#current_password').fill(code);
        await owner.page.locator('#password').fill(NEW_PASSWORD);
        await owner.page.locator('#password_confirmation').fill(NEW_PASSWORD);
        await owner.page
            .locator('[data-test="change-password-button"]')
            .click();
        await expect(owner.page).toHaveURL(/\/home$/);

        // The security page asks for the password again first.
        await owner.page.goto('/settings/security');
        await expect(owner.page).toHaveURL(/\/user\/confirm-password$/);
        await owner.page.locator('#password').fill(NEW_PASSWORD);
        await owner.page
            .locator('[data-test="confirm-password-button"]')
            .click();
        await expect(owner.page).toHaveURL(/\/settings\/security$/);

        await owner.page
            .getByRole('button', { name: 'Aktifkan 2FA', exact: true })
            .click();

        const dialog = owner.page.getByRole('dialog');
        const manualKey = dialog.locator('input[readonly]');

        await expect(manualKey).toBeVisible();
        const secret = (await manualKey.inputValue()).trim();

        expect(secret).toMatch(/^[A-Z2-7]{16,}$/);
        expect(await axeViolations(owner.page)).toEqual([]);

        await dialog
            .getByRole('button', { name: 'Lanjutkan', exact: true })
            .click();

        const setupCode = totpCode(secret);

        await dialog.locator('input[name="code"]').pressSequentially(setupCode);
        await dialog
            .getByRole('button', { name: 'Konfirmasi', exact: true })
            .click();
        await expect(dialog).toBeHidden();

        // Confirmed: optional for this account, so it may also be turned off.
        await expect(
            owner.page.getByRole('button', {
                name: 'Nonaktifkan 2FA',
                exact: true,
            }),
        ).toBeVisible();

        await owner.page
            .getByRole('button', { name: 'Lihat kode pemulihan', exact: true })
            .click();

        const recoveryCodes = owner.page
            .getByRole('list', { name: 'Kode pemulihan', exact: true })
            .getByRole('listitem');

        await expect(recoveryCodes).toHaveCount(8);
        const recoveryCode =
            (await recoveryCodes.first().textContent())?.trim() ?? '';

        expect(recoveryCode).not.toBe('');
        expect(await axeViolations(owner.page)).toEqual([]);

        // The password alone no longer signs in; the challenge takes a fresh code from the app.
        await owner.context.clearCookies();
        await signIn(owner.page, username, NEW_PASSWORD);
        await expect(owner.page).toHaveURL(/\/two-factor-challenge$/);
        expect(await axeViolations(owner.page)).toEqual([]);
        await owner.page
            .locator('input[name="code"]')
            .pressSequentially(await nextTotpCode(secret, setupCode));
        await owner.page
            .getByRole('button', { name: 'Lanjutkan', exact: true })
            .click();
        await expect(owner.page).toHaveURL(/\/home$/);

        // A recovery code signs in once.
        const signInWithRecoveryCode = async () => {
            await owner.context.clearCookies();
            await signIn(owner.page, username, NEW_PASSWORD);
            await expect(owner.page).toHaveURL(/\/two-factor-challenge$/);
            await owner.page
                .getByRole('button', { name: 'masuk dengan kode pemulihan' })
                .click();
            await owner.page
                .locator('input[name="recovery_code"]')
                .fill(recoveryCode);
            const answered = owner.page.waitForResponse(
                (response) =>
                    response.request().method() === 'POST' &&
                    new URL(response.url()).pathname === '/two-factor-challenge',
            );

            await owner.page
                .getByRole('button', { name: 'Lanjutkan', exact: true })
                .click();
            await answered;
        };

        await signInWithRecoveryCode();
        await expect(owner.page).toHaveURL(/\/home$/);

        // Used up: the same code is refused and the account stays at the challenge.
        await signInWithRecoveryCode();
        await expect(owner.page).toHaveURL(/\/two-factor-challenge$/);

        expect(owner.errors, 'critical console or network errors').toEqual([]);
    } finally {
        await owner.context.close();
    }
});
