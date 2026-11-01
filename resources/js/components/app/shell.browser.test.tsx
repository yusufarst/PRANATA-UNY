import type { ChildProcess } from 'node:child_process';
import { spawn } from 'node:child_process';
import {
    existsSync,
    mkdtempSync,
    readdirSync,
    readFileSync,
    rmSync,
} from 'node:fs';
import { createServer } from 'node:http';
import type { Server } from 'node:http';
import { createRequire } from 'node:module';
import type { AddressInfo } from 'node:net';
import { tmpdir } from 'node:os';
import { extname, join, normalize, resolve } from 'node:path';
import { renderToStaticMarkup } from 'react-dom/server';
import { afterAll, beforeAll, describe, expect, it, vi } from 'vite-plus/test';
import { PageHeader } from '@/components/app/page-header';
import {
    EmptyState,
    ErrorState,
    ForbiddenState,
    NotFoundState,
} from '@/components/app/state-views';
import { StatusBadge, statusCategories } from '@/components/app/status-badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { TooltipProvider } from '@/components/ui/tooltip';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import i18n from '@/lib/i18n';

vi.mock('@inertiajs/react', () => import('@/test/inertia-mock'));

// A real browser on the runner: jsdom has no layout and cannot measure overlap, fonts or contrast (TAC-02, axe, tabular figures).
// The runner image ships Chrome, and CI fails rather than skips when Chrome or the build is missing.
const chrome = process.env.CHROME_BIN ?? '/usr/bin/google-chrome';
const buildFolder = resolve('public/build');
const assetFolder = join(buildFolder, 'assets');
const cssFiles = existsSync(assetFolder)
    ? readdirSync(assetFolder).filter((name) => name.endsWith('.css'))
    : [];
const available = existsSync(chrome) && cssFiles.length > 0;

if (process.env.CI && !available) {
    throw new Error(`Chrome (${chrome}) or the production build is missing.`);
}

const viewports = [
    { width: 390, height: 844 },
    { width: 1440, height: 900 },
];
const locales = ['id', 'en'] as const;

type Report = {
    locale: string;
    width: number;
    horizontalOverflow: boolean;
    overlaps: string[];
    sidebarVisible: boolean;
    sansLoaded: boolean;
    monoLoaded: boolean;
    tabularDifference: number;
    violations: { id: string; targets: string[] }[];
};

function Fixture() {
    return (
        <TooltipProvider>
            <AppSidebarLayout
                breadcrumbs={[{ title: 'common:nav.home', href: '/home' }]}
            >
                <div data-shell-content className="flex flex-col gap-6 p-4">
                    <PageHeader
                        title={i18n.t('common:home.title')}
                        description={i18n.t('common:home.description')}
                        status={<StatusBadge category="action" />}
                        action={
                            <Button type="button">
                                {i18n.t('common:actions.save')}
                            </Button>
                        }
                    />
                    <div className="flex flex-wrap gap-2">
                        {statusCategories.map((category) => (
                            <StatusBadge key={category} category={category} />
                        ))}
                    </div>
                    <p className="font-mono text-sm text-muted-foreground tabular-nums">
                        {i18n.t('common:states.request_id', {
                            id: '1234567890',
                        })}
                    </p>
                    <EmptyState
                        description={i18n.t('common:home.description')}
                    />
                    <ErrorState requestId="req-1" />
                    <ForbiddenState />
                    <NotFoundState />
                    <Skeleton className="h-8 w-full" />
                </div>
            </AppSidebarLayout>
        </TooltipProvider>
    );
}

// Runs inside each frame. Plain JavaScript, sent as text.
const probe = `(async () => {
    const box = (element) => element.getBoundingClientRect();
    const shown = (element) => {
        const rect = box(element);
        return rect.width > 0 && rect.height > 0 && getComputedStyle(element).visibility !== 'hidden';
    };
    const crosses = (a, b) =>
        a.left < b.right - 1 && b.left < a.right - 1 && a.top < b.bottom - 1 && b.top < a.bottom - 1;

    await Promise.all([
        document.fonts.load('600 16px "Plus Jakarta Sans Variable"', '0123456789'),
        document.fonts.load('16px "JetBrains Mono Variable"', '0123456789'),
    ]);
    await document.fonts.ready;

    const overlaps = [];
    const regions = {
        sidebar: document.querySelector('[data-sidebar="sidebar"]'),
        header: document.querySelector('header'),
        content: document.querySelector('[data-shell-content]'),
    };
    const names = Object.keys(regions).filter((name) => regions[name] && shown(regions[name]));
    names.forEach((first, index) => {
        names.slice(index + 1).forEach((second) => {
            if (crosses(box(regions[first]), box(regions[second]))) overlaps.push(first + ' x ' + second);
        });
    });
    const controls = [...document.querySelectorAll('header a, header button')].filter(shown);
    controls.forEach((first, index) => {
        controls.slice(index + 1).forEach((second) => {
            if (!first.contains(second) && !second.contains(first) && crosses(box(first), box(second))) {
                overlaps.push('header: ' + first.textContent + ' x ' + second.textContent);
            }
        });
    });

    const digits = (text) => {
        const span = document.createElement('span');
        span.textContent = text;
        span.style.cssText = 'font-family: "Plus Jakarta Sans Variable"; font-size: 40px; font-variant-numeric: tabular-nums; position: absolute;';
        document.body.append(span);
        const width = box(span).width;
        span.remove();
        return width;
    };

    const axeResult = await axe.run(document, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'] });

    await fetch('/report', {
        method: 'POST',
        body: JSON.stringify({
            locale: document.documentElement.lang,
            width: innerWidth,
            horizontalOverflow: document.documentElement.scrollWidth > innerWidth,
            overlaps,
            sidebarVisible: Boolean(regions.sidebar && shown(regions.sidebar)),
            sansLoaded: document.fonts.check('600 16px "Plus Jakarta Sans Variable"'),
            monoLoaded: document.fonts.check('16px "JetBrains Mono Variable"'),
            tabularDifference: Math.abs(digits('1111111111') - digits('8888888888')),
            violations: axeResult.violations.map((item) => ({
                id: item.id,
                targets: item.nodes.map((node) => node.target.join(' ')),
            })),
        }),
    });
})().catch((error) =>
    fetch('/report', { method: 'POST', body: JSON.stringify({ failure: String(error) }) }),
);`;

const contentTypes: Record<string, string> = {
    '.css': 'text/css',
    '.woff2': 'font/woff2',
    '.woff': 'font/woff',
};

describe.skipIf(!available)('the shell in a real browser', () => {
    const reports: Report[] = [];
    const fixtures = new Map<string, string>();
    let server: Server;
    let browser: ChildProcess | undefined;
    let profile: string;

    beforeAll(async () => {
        const axeSource = readFileSync(
            createRequire(import.meta.url).resolve('axe-core/axe.min.js'),
            'utf8',
        );
        const links = cssFiles
            .map(
                (name) =>
                    `<link rel="stylesheet" href="/build/assets/${name}">`,
            )
            .join('');

        for (const locale of locales) {
            await i18n.changeLanguage(locale);
            fixtures.set(
                locale,
                `<!doctype html><html lang="${locale}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>${i18n.t('common:home.title')}</title>${links}</head><body class="font-sans antialiased">${renderToStaticMarkup(<Fixture />)}<script src="/axe.js"></script><script>${probe}</script></body></html>`,
            );
        }

        await i18n.changeLanguage('id');

        const frames = locales.flatMap((locale) =>
            viewports.map(
                ({ width, height }) =>
                    `<iframe title="${locale}-${width}" src="/fixture/${locale}" width="${width}" height="${height}"></iframe>`,
            ),
        );

        await new Promise<void>((done, fail) => {
            const expected = frames.length;

            server = createServer((request, response) => {
                const url = request.url ?? '/';

                if (url === '/report') {
                    let body = '';
                    request.on('data', (chunk) => (body += chunk));
                    request.on('end', () => {
                        const report = JSON.parse(body);

                        response.end();

                        if (report.failure) {
                            fail(new Error(report.failure));

                            return;
                        }

                        reports.push(report);

                        if (reports.length === expected) {
                            done();
                        }
                    });

                    return;
                }

                if (url === '/run') {
                    response.setHeader('Content-Type', 'text/html');
                    response.end(
                        `<!doctype html><title>run</title>${frames.join('')}`,
                    );

                    return;
                }

                if (url === '/axe.js') {
                    response.setHeader('Content-Type', 'text/javascript');
                    response.end(axeSource);

                    return;
                }

                const fixture = /^\/fixture\/(\w+)$/.exec(url);

                if (fixture) {
                    response.setHeader('Content-Type', 'text/html');
                    response.end(fixtures.get(fixture[1]));

                    return;
                }

                const file = normalize(
                    join(buildFolder, url.replace(/^\/build/, '')),
                );

                if (file.startsWith(buildFolder) && existsSync(file)) {
                    response.setHeader(
                        'Content-Type',
                        contentTypes[extname(file)] ??
                            'application/octet-stream',
                    );
                    response.end(readFileSync(file));

                    return;
                }

                response.statusCode = 404;
                response.end();
            });

            server.listen(0, '127.0.0.1', () => {
                const { port } = server.address() as AddressInfo;

                profile = mkdtempSync(join(tmpdir(), 'pranata-chrome-'));
                browser = spawn(chrome, [
                    '--headless=new',
                    '--no-sandbox',
                    '--disable-gpu',
                    '--disable-dev-shm-usage',
                    `--user-data-dir=${profile}`,
                    `http://127.0.0.1:${port}/run`,
                ]);
                browser.on('error', fail);
            });
        });
    }, 120_000);

    afterAll(() => {
        browser?.kill();
        server?.close();

        if (profile) {
            rmSync(profile, { recursive: true, force: true });
        }
    });

    it('measured every viewport in both languages', () => {
        expect(
            reports.map((report) => `${report.locale}-${report.width}`).sort(),
        ).toEqual(['en-1440', 'en-390', 'id-1440', 'id-390']);
    });

    it('has no overlap and no horizontal overflow (TAC-02)', () => {
        for (const report of reports) {
            expect(report.overlaps, `${report.locale}-${report.width}`).toEqual(
                [],
            );
            expect(
                report.horizontalOverflow,
                `${report.locale}-${report.width}`,
            ).toBe(false);
        }
    });

    it('shows the sidebar on the desktop and keeps it as a drawer on the phone', () => {
        for (const report of reports) {
            expect(
                report.sidebarVisible,
                `${report.locale}-${report.width}`,
            ).toBe(report.width === 1440);
        }
    });

    it('loads the self-hosted fonts, with tabular figures', () => {
        for (const report of reports) {
            expect(report.sansLoaded).toBe(true);
            expect(report.monoLoaded).toBe(true);
            expect(report.tabularDifference).toBeLessThan(0.01);
        }
    });

    it('passes axe, including color contrast', () => {
        for (const report of reports) {
            expect(
                report.violations,
                `${report.locale}-${report.width}`,
            ).toEqual([]);
        }
    });
});
