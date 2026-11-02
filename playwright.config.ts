import { defineConfig, devices } from '@playwright/test';

const baseURL = 'http://127.0.0.1:8010';
const mobile = { width: 390, height: 844 };
const desktop = { width: 1440, height: 900 };

export default defineConfig({
    testDir: 'tests/e2e',
    outputDir: 'tests/e2e/.output/results',
    globalSetup: './tests/e2e/global-setup.ts',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: 0,
    reporter: [
        ['list'],
        ['html', { open: 'never', outputFolder: 'tests/e2e/.output/report' }],
    ],
    use: {
        baseURL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    // serve drops the environment without --no-reload, and the server would then read the database of .env.
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8010 --no-reload',
        url: `${baseURL}/up`,
        reuseExistingServer: false,
        env: { PHP_CLI_SERVER_WORKERS: '4' },
    },
    projects: [
        {
            name: 'fixtures',
            testMatch: '**/smoke/fixtures.spec.ts',
            use: { ...devices['Desktop Chrome'], viewport: desktop },
        },
        ...(
            [
                ['chromium', 'Desktop Chrome'],
                ['webkit', 'Desktop Safari'],
            ] as const
        ).flatMap(([browser, device]) =>
            (
                [
                    ['mobile', mobile],
                    ['desktop', desktop],
                ] as const
            ).map(([size, viewport]) => ({
                name: `${browser}-${size}`,
                // The negative spec fails on purpose, so only its own CI step runs it.
                testIgnore: [
                    '**/smoke/fixtures.spec.ts',
                    ...(process.env.E2E_NEGATIVE ? [] : ['**/negative/**']),
                ],
                dependencies: ['fixtures'],
                use: { ...devices[device], viewport },
            })),
        ),
    ],
});
