import AxeBuilder from '@axe-core/playwright';
import { test as base, expect } from '@playwright/test';
import type { Page } from '@playwright/test';

// A navigation cancels its pending requests; the browsers word that differently and it is no defect.
const CANCELLED = /ERR_ABORTED|NS_BINDING_ABORTED|cancelled|Load request cancelled/i;

export type AxeViolation = {
    id: string;
    impact?: string | null;
    targets: string[];
    // What axe measured, such as the colours of a contrast failure, so a failure in CI reads without the trace.
    summaries: string[];
};

export function collectCriticalErrors(page: Page): string[] {
    const errors: string[] = [];

    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push(`console: ${message.text()}`);
        }
    });
    page.on('pageerror', (error) => errors.push(`page: ${error.message}`));
    page.on('requestfailed', (request) => {
        const failure = request.failure()?.errorText ?? 'unknown';

        if (!CANCELLED.test(failure)) {
            errors.push(`request: ${request.url()} ${failure}`);
        }
    });
    page.on('response', (response) => {
        if (response.status() >= 500) {
            errors.push(`response: ${response.status()} ${response.url()}`);
        }
    });

    return errors;
}

export async function axeViolations(page: Page): Promise<AxeViolation[]> {
    // A dialog fading in or out is half transparent, and axe would measure its contrast mid-way. Endless animations,
    // such as a spinner, never settle and are left alone.
    await page.waitForFunction(() =>
        document
            .getAnimations()
            .every(
                (animation) =>
                    animation.playState !== 'running' ||
                    animation.effect?.getComputedTiming().endTime === Infinity,
            ),
    );

    const { violations } = await new AxeBuilder({ page }).analyze();

    return violations.map(({ id, impact, nodes }) => ({
        id,
        impact,
        targets: nodes.map(({ target }) => target.join(' ')),
        summaries: nodes.map(({ failureSummary }) => failureSummary ?? ''),
    }));
}

export const test = base.extend<{ criticalErrors: string[] }>({
    criticalErrors: [
        async ({ page }, use) => {
            const errors = collectCriticalErrors(page);

            await use(errors);

            expect(errors, 'critical console or network errors').toEqual([]);
        },
        { auto: true },
    ],
});

export { expect };
