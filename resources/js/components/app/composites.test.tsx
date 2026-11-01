import { renderToStaticMarkup } from 'react-dom/server';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vite-plus/test';
import { chooseLocale, LanguageSwitcher } from '@/components/app/language-switcher';
import { PageHeader } from '@/components/app/page-header';
import {
    EmptyState,
    ErrorState,
    ForbiddenState,
    NotFoundState,
} from '@/components/app/state-views';
import { StatusBadge, statusCategories } from '@/components/app/status-badge';
import { WorkspaceSwitcher } from '@/components/app/workspace-switcher';
import { Skeleton } from '@/components/ui/skeleton';
import i18n from '@/lib/i18n';
import ErrorPage from '@/pages/errors/show';
import { pageProps, router } from '@/test/inertia-mock';

vi.mock('@inertiajs/react', () => import('@/test/inertia-mock'));

beforeEach(async () => {
    await i18n.changeLanguage('id');
});

afterEach(() => {
    vi.clearAllMocks();
    pageProps.workspaces = [{ key: 'home', href: '/home' }];
});

describe('StatusBadge', () => {
    it.each(statusCategories)('shows %s as text and icon', (category) => {
        const html = renderToStaticMarkup(<StatusBadge category={category} />);

        expect(html).toContain(i18n.t(`states:category.${category}`));
        expect(html).toContain('<svg');
        expect(html).toContain('aria-hidden="true"');
    });

    it('prefers the exact label of a workflow state', () => {
        const html = renderToStaticMarkup(
            <StatusBadge category="waiting" label="Menunggu telaah" />,
        );

        expect(html).toContain('Menunggu telaah');
    });
});

describe('state views', () => {
    it('fall back to translated text in both languages', async () => {
        const render = () =>
            renderToStaticMarkup(
                <>
                    <EmptyState />
                    <ErrorState />
                    <ForbiddenState />
                    <NotFoundState />
                </>,
            );

        expect(render()).toContain('Halaman tidak ditemukan');
        await i18n.changeLanguage('en');
        expect(render()).toContain('Page not found');
    });

    it('announces an error and shows its request code', () => {
        const html = renderToStaticMarkup(<ErrorState requestId="req-1" />);

        expect(html).toContain('role="alert"');
        expect(html).toContain('Kode permintaan: req-1');
    });

    it('renders an action only when there is one', () => {
        expect(renderToStaticMarkup(<EmptyState />)).not.toContain('<button');
        expect(
            renderToStaticMarkup(<EmptyState action={<button type="button" />} />),
        ).toContain('<button');
    });

    it('keeps a skeleton available for loading', () => {
        expect(renderToStaticMarkup(<Skeleton />)).toContain('animate-pulse');
    });
});

describe('PageHeader', () => {
    it('shows the title and hides the action area without actions', () => {
        const html = renderToStaticMarkup(<PageHeader title="Paket" />);

        expect(html).toContain('<h1');
        expect(html).toContain('Paket');
        expect(html).not.toContain('shrink-0');
    });
});

describe('WorkspaceSwitcher', () => {
    it('is plain text with one allowed workspace, so no dead menu exists', () => {
        const html = renderToStaticMarkup(<WorkspaceSwitcher />);

        expect(html).toContain('Beranda');
        expect(html).not.toContain('<button');
    });

    it('offers a menu with more than one allowed workspace', () => {
        pageProps.workspaces = [
            { key: 'home', href: '/home' },
            { key: 'home', href: '/other' },
        ];

        expect(renderToStaticMarkup(<WorkspaceSwitcher />)).toContain('<button');
    });

    it('renders nothing without an allowed workspace', () => {
        pageProps.workspaces = [];

        expect(renderToStaticMarkup(<WorkspaceSwitcher />)).toBe('');
    });
});

describe('language switch (TAC-03)', () => {
    it('marks the current language', () => {
        const html = renderToStaticMarkup(<LanguageSwitcher />);

        expect(html).toContain('aria-pressed="true"');
        expect(html).toContain('aria-label="Bahasa Indonesia"');
    });

    it('asks the server to change language and keeps the page state', () => {
        chooseLocale('en', 'id');

        expect(router.post).toHaveBeenCalledWith(
            expect.stringContaining('/locale'),
            { locale: 'en' },
            { preserveScroll: true, preserveState: true },
        );
    });

    it('sends nothing for the language already in use', () => {
        chooseLocale('id', 'id');

        expect(router.post).not.toHaveBeenCalled();
    });
});

describe('error pages (TAC-05)', () => {
    it.each([403, 404, 419, 429, 500, 503] as const)(
        'renders %i in both languages',
        async (status) => {
            const id = renderToStaticMarkup(<ErrorPage status={status} />);

            await i18n.changeLanguage('en');
            const en = renderToStaticMarkup(<ErrorPage status={status} />);

            expect(id).toContain(
                i18n.t(`common:errors.${status}.title`, { lng: 'id' }),
            );
            expect(en).toContain(
                i18n.t(`common:errors.${status}.title`, { lng: 'en' }),
            );
            expect(en).not.toBe(id);
            expect(id).not.toContain(`common:errors`);
        },
    );

    it('hides the language switch where a POST cannot succeed', () => {
        const switchLabel = 'aria-label="Pilih bahasa"';

        expect(renderToStaticMarkup(<ErrorPage status={404} />)).toContain(
            switchLabel,
        );
        expect(renderToStaticMarkup(<ErrorPage status={419} />)).not.toContain(
            switchLabel,
        );
        expect(renderToStaticMarkup(<ErrorPage status={503} />)).not.toContain(
            switchLabel,
        );
    });
});
