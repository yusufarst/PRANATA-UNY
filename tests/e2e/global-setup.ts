import { execFileSync } from 'node:child_process';
import { chromium } from '@playwright/test';
import type { FullConfig } from '@playwright/test';
import {
    emailOf,
    PASSWORD,
    PROFILES,
    storageStateOf,
} from './support/profiles';

const E2E_DATABASE = /^pranata_e2e_[a-z0-9_]+$/;

// One clear failure that names the step and stops the run, never a wall of failed UI tests (TEST_STRATEGY section 9).
async function step(name: string, action: () => unknown): Promise<void> {
    try {
        await action();
    } catch (error) {
        const output = (error as { stdout?: Buffer }).stdout?.toString() ?? '';

        throw new Error(
            `SETUP FAILURE [${name}]: ${(error as Error).message}\n${output}`,
        );
    }
}

function artisan(...args: string[]): void {
    execFileSync('php', ['artisan', ...args], { stdio: 'pipe' });
}

export default async function globalSetup(config: FullConfig): Promise<void> {
    const baseURL = config.projects[0].use.baseURL;

    await step('database name', () => {
        const database = process.env.DB_DATABASE ?? '';

        if (!E2E_DATABASE.test(database)) {
            throw new Error(`[${database}] is not an E2E database`);
        }
    });

    await step('migrations', () => artisan('migrate:fresh', '--force'));
    await step('ScenarioSeeder', () =>
        artisan('db:seed', '--class=ScenarioSeeder', '--force'),
    );

    await step('login state per profile', async () => {
        const browser = await chromium.launch();

        try {
            for (const profile of PROFILES) {
                const context = await browser.newContext({ baseURL });
                const page = await context.newPage();

                await page.goto('/login');
                await page.locator('#email').fill(emailOf(profile));
                await page.locator('#password').fill(PASSWORD);
                await page.locator('[data-test="login-button"]').click();
                await page.waitForURL('**/home');
                await context.storageState({ path: storageStateOf(profile) });
                await context.close();
            }
        } finally {
            await browser.close();
        }
    });
}
