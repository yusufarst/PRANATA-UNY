import { resolve } from 'node:path';
import type { Page } from '@playwright/test';
import { axeViolations, expect, test } from '../support/fixtures';
import { emailOf, PASSWORD } from '../support/profiles';
import type { Profile } from '../support/profiles';

const MENU_TOGGLE = 'Buka atau tutup menu';

async function signIn(page: Page, profile: Profile): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Alamat email').fill(emailOf(profile));
    await page.locator('#password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Masuk' }).click();

    await expect(page).toHaveURL(/\/home$/);
}

function sessionOf(project: string): string {
    return resolve(
        import.meta.dirname,
        '..',
        '.auth',
        `session-${project}.json`,
    );
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
    });
}

// The language lives in the session, and the login limit allows five attempts a minute per address.
// So each browser project signs in once and runs these tests in order on its own session.
test.describe('signed in', () => {
    test.describe.configure({ mode: 'serial' });
    test.use({
        // eslint-disable-next-line no-empty-pattern
        storageState: async ({}, use, testInfo) => {
            await use(sessionOf(testInfo.project.name));
        },
    });

    test.beforeAll(async ({ browser, baseURL }, testInfo) => {
        // The runner applies the storageState of this group to every new context, and its file does not exist yet.
        const context = await browser.newContext({
            baseURL,
            storageState: { cookies: [], origins: [] },
        });

        try {
            await signIn(await context.newPage(), 'internal');
            await context.storageState({
                path: sessionOf(testInfo.project.name),
            });
        } finally {
            await context.close();
        }
    });

    // First: its labels are Indonesian, and the tests below switch the language of this session.
    test('the drawer opens on a phone and has no axe violation', async ({
        page,
    }) => {
        test.skip(
            (page.viewportSize()?.width ?? 0) >= 640,
            'The drawer exists only on a phone.',
        );

        await page.goto('/home');
        await page.getByRole('button', { name: MENU_TOGGLE }).click();

        await expect(
            page
                .getByRole('dialog', { name: 'Menu' })
                .getByRole('link', { name: 'Beranda' }),
        ).toBeVisible();
        expect(await axeViolations(page)).toEqual([]);
    });

    for (const language of LANGUAGES) {
        test.describe(`language ${language.code}`, () => {
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
    }

    // TASK-009 TAC-01 on the sample command. The labels depend on the language left by the tests above, so ids select.
    test('a double click saves the profile once under one idempotency key', async ({
        page,
    }) => {
        const name = `Pengguna ${test.info().project.name} ${Date.now()}`;
        const keys: string[] = [];

        page.on('request', (request) => {
            if (
                request.method() === 'PATCH' &&
                request.url().endsWith('/settings/profile')
            ) {
                keys.push(request.headers()['x-idempotency-key'] ?? '');
            }
        });

        await page.goto('/settings/profile');
        await page.locator('#name').fill(name);
        await page.locator('[data-test="update-profile-button"]').dblclick();

        await expect.poll(() => keys.length).toBeGreaterThan(0);
        await expect(
            page.locator('[data-test="update-profile-button"]'),
        ).toBeEnabled();
        expect(new Set(keys).size).toBe(1);
        expect(keys[0]).toMatch(
            /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/,
        );

        await page.reload();
        await expect(page.locator('#name')).toHaveValue(name);
    });

    test('a resend with the same key gets the answer of the first', async ({
        page,
    }) => {
        await page.goto('/settings/profile');

        // Two requests at once, as from a double click the browser does not stop; the second waits for the first.
        const answers = await page.evaluate(async () => {
            const email = (
                document.querySelector('#email') as HTMLInputElement
            ).value;
            const key = crypto.randomUUID();
            const send = () =>
                fetch('/settings/profile', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'text/html',
                        'X-Idempotency-Key': key,
                    },
                    body: JSON.stringify({ name: 'Pengguna Ulang', email }),
                }).then((response) => ({
                    status: response.status,
                    url: new URL(response.url).pathname,
                }));

            return Promise.all([send(), send()]);
        });

        expect(answers).toEqual([
            { status: 200, url: '/settings/profile' },
            { status: 200, url: '/settings/profile' },
        ]);
    });
});

// A same-origin browser request passes the forgery check without a token, so a 419 cannot be provoked here.
// The login limit gives a real failed mutation instead, on an address no profile uses.
test('a refused sign-in leads back to where it came from', async ({
    page,
    criticalErrors,
}) => {
    const address = `limit.${test.info().project.name}@e2e.pranata.test`;
    const submit = page.getByRole('button', { name: 'Masuk' });
    const refused = page.getByRole('heading', {
        name: 'Terlalu banyak permintaan',
    });

    await page.goto('/login');
    await expect(async () => {
        await page.getByLabel('Alamat email').fill(address);
        await page.locator('#password').fill('not-the-password');
        await submit.click();
        await expect(refused).toBeVisible({ timeout: 2_000 });
    }).toPass({ timeout: 20_000 });

    await page.getByRole('button', { name: 'Coba lagi' }).click();

    await expect(page).toHaveURL(/\/login$/);
    await expect(submit).toBeVisible();

    // The refused request is the point of this test, and the browser logs it as a console error.
    criticalErrors.length = 0;
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
