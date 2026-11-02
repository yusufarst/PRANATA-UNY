import type { Page } from '@playwright/test';
import { axeViolations, expect, test } from '../support/fixtures';
import { emailOf, PASSWORD } from '../support/profiles';

// The language lives in the session, so parallel tests sharing a saved login would switch it under each other.
async function signIn(page: Page): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Alamat email').fill(emailOf('internal'));
    await page.locator('#password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Masuk' }).click();

    await expect(page).toHaveURL(/\/home$/);
}

const LANGUAGES = [
    {
        code: 'id',
        option: 'Bahasa Indonesia',
        email: 'Alamat email',
        submit: 'Masuk',
        home: 'Beranda',
        empty: 'Belum ada pekerjaan',
    },
    {
        code: 'en',
        option: 'Bahasa Inggris',
        email: 'Email address',
        submit: 'Log in',
        home: 'Home',
        empty: 'No work yet',
    },
] as const;

for (const language of LANGUAGES) {
    test.describe(`language ${language.code}`, () => {
        test('the login page is usable and has no axe violation', async ({
            page,
        }) => {
            await page.goto('/login');
            await page.getByLabel(language.option).click();

            await expect(page.getByLabel(language.email)).toBeVisible();
            await expect(
                page.getByRole('button', { name: language.submit }),
            ).toBeVisible();
            expect(await axeViolations(page)).toEqual([]);
        });

        test('the empty shell is usable and has no axe violation', async ({
            page,
        }) => {
            await signIn(page);
            await page.getByLabel(language.option).first().click();

            await expect(
                page.getByRole('heading', { name: language.home }),
            ).toBeVisible();
            await expect(page.getByText(language.empty)).toBeVisible();
            expect(await axeViolations(page)).toEqual([]);
        });
    });
}

test('axe reports a violation that is really there', async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => {
        const image = document.createElement('img');

        image.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACw=';
        document.body.append(image);
    });

    const violations = await axeViolations(page);

    expect(violations.map(({ id }) => id)).toContain('image-alt');
});
