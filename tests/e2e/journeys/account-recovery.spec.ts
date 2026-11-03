import type { Browser, Page } from '@playwright/test';
import {
    axeViolations,
    collectCriticalErrors,
    expect,
    test,
} from '../support/fixtures';
import { runId, storageStateOf } from '../support/profiles';

// J-18 (USER_JOURNEYS, E2E-18): an administrator resets a forgotten password after checking who asked. The temporary
// password is shown once, ends every session of the account, works once and must be changed at the next sign-in.
// Every browser project makes its own account, so the projects never reset each other's. Each account signs in four
// times, under the login limit of five a minute.

const NEW_PASSWORD = 'Pulih-2026!a';
const RECOVERED_PASSWORD = 'Pulih-2026!b';
const REFUSED = 'Data masuk tidak cocok dengan catatan kami.';
const NO_SESSION = 'Akun ini tidak sedang masuk di perangkat mana pun.';

test.use({ storageState: storageStateOf('super-admin') });

async function signIn(page: Page, login: string, password: string) {
    await page.goto('/login');
    await page.locator('#login').fill(login);
    await page.locator('#password').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

async function changePassword(page: Page, current: string, next: string) {
    await expect(page).toHaveURL(/\/password\/change$/);
    await page.locator('#current_password').fill(current);
    await page.locator('#password').fill(next);
    await page.locator('#password_confirmation').fill(next);
    await page.locator('[data-test="change-password-button"]').click();

    await expect(page).toHaveURL(/\/home$/);
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

test('an administrator recovers a forgotten password with a one-time temporary password', async ({
    page,
    browser,
    baseURL,
}) => {
    const suffix = `${test.info().project.name.replaceAll('-', '_')}_${Date.now()}`;
    const username = `j18_${suffix}`;
    const name = `E2E J-18 ${suffix} ${runId()}`;

    // The administrator creates the account and reads out its activation code.
    await page.goto('/admin/users/create');
    await page.locator('#name').fill(name);
    await page.locator('#username').fill(username);
    await page.locator('[data-test="save-account-button"]').click();

    const activation = page.locator('[data-test="issued-secret-value"]');

    await expect(activation).toBeVisible();
    const code = (await activation.textContent())?.trim() ?? '';

    expect(code).not.toBe('');

    await page.goto(`/admin/users?q=${username}`);
    await page.getByRole('link', { name }).first().click();
    await expect(page).toHaveURL(/\/admin\/users\/[^/?]+$/);
    const detail = page.url();

    // The owner activates the account and stays signed in on their own device.
    const owner = await ownerPage(browser, baseURL);

    try {
        await signIn(owner.page, username, code);
        await changePassword(owner.page, code, NEW_PASSWORD);

        // The password is forgotten; the owner asks the administrator, who checks their identity first.
        await page.goto(detail);
        await expect(
            page.locator('[data-test="account-sessions"]'),
        ).not.toContainText(NO_SESSION);
        await page.locator('[data-test="reset-password-button"]').click();

        const dialog = page.getByRole('dialog');

        await expect(dialog).toBeVisible();
        expect(await axeViolations(page)).toEqual([]);

        await dialog
            .locator('#identity_check_method')
            .selectOption('IN_PERSON');
        await dialog
            .locator('#identity_check_note')
            .fill('Datang ke ruang admin dengan kartu pegawai');
        await dialog.locator('[data-test="confirm-reset-button"]').click();

        const secret = page.locator('[data-test="issued-secret-value"]');

        await expect(secret).toBeVisible();
        const temporary = (await secret.textContent())?.trim() ?? '';

        expect(temporary).toMatch(/^[A-Z0-9]{4}(-[A-Z0-9]{4}){3}$/);
        expect(await axeViolations(page)).toEqual([]);

        // Shown once: going Back to the answer that issued it does not bring it back from the browser history.
        await page
            .getByRole('link', { name: 'Daftar pengguna', exact: true })
            .first()
            .click();
        await expect(page).toHaveURL(/\/admin\/users(\?.*)?$/);
        await page.goBack();
        await expect(page).toHaveURL(detail);
        await expect(
            page.locator('[data-test="account-sessions"]'),
        ).toBeVisible();
        await expect(
            page.locator('[data-test="issued-secret"]'),
        ).toHaveCount(0);

        // Shown once: the detail opened again has no secret, every session ended and a change is owed.
        await page.goto(detail);
        await expect(
            page.locator('[data-test="issued-secret"]'),
        ).toHaveCount(0);
        await expect(
            page.locator('[data-test="account-sessions"]'),
        ).toContainText(NO_SESSION);

        // The reset ended the owner's session.
        await owner.page.goto('/home');
        await expect(owner.page).toHaveURL(/\/login$/);

        // The forgotten password no longer works.
        await signIn(owner.page, username, NEW_PASSWORD);
        await expect(owner.page.getByText(REFUSED)).toBeVisible();

        // The temporary password works once and leads straight to the forced change.
        await signIn(owner.page, username, temporary);
        await expect(owner.page).toHaveURL(/\/password\/change$/);
        expect(await axeViolations(owner.page)).toEqual([]);
        await changePassword(owner.page, temporary, RECOVERED_PASSWORD);

        await owner.context.clearCookies();
        await signIn(owner.page, username, temporary);
        await expect(owner.page.getByText(REFUSED)).toBeVisible();

        expect(owner.errors, 'critical console or network errors').toEqual([]);
    } finally {
        await owner.context.close();
    }
});
