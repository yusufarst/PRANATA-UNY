import { axeViolations, expect, test } from '../support/fixtures';
import { emailOf, PASSWORD, storageStateOf } from '../support/profiles';

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

        test.describe('signed in', () => {
            test.use({ storageState: storageStateOf('internal') });

            test('the empty shell is usable and has no axe violation', async ({
                page,
            }) => {
                await page.goto('/home');
                await page.getByLabel(language.option).first().click();

                await expect(
                    page.getByRole('heading', { name: language.home }),
                ).toBeVisible();
                await expect(page.getByText(language.empty)).toBeVisible();
                expect(await axeViolations(page)).toEqual([]);
            });
        });
    });
}

test('a full sign-in reaches the shell', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Alamat email').fill(emailOf('internal'));
    await page.locator('#password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Masuk' }).click();

    await expect(page).toHaveURL(/\/home$/);
});

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
