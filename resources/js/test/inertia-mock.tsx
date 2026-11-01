import type { PropsWithChildren } from 'react';
import { vi } from 'vite-plus/test';

// A stand-in for @inertiajs/react, so components render on the server without an Inertia page.
export const pageProps = {
    name: 'PRANATA UNY',
    auth: {
        user: {
            id: 1,
            name: 'Pengguna Uji',
            email: 'pengguna@example.test',
            email_verified_at: '2026-01-01T00:00:00Z',
            two_factor_enabled: false,
            created_at: '2026-01-01T00:00:00Z',
            updated_at: '2026-01-01T00:00:00Z',
        },
    },
    sidebarOpen: true,
    locale: 'id',
    workspaces: [{ key: 'home', href: '/home' }],
};

export const router = {
    post: vi.fn(),
    on: vi.fn(),
    flushAll: vi.fn(),
};

export function usePage() {
    return { url: '/home', props: pageProps };
}

export function Head() {
    return null;
}

export function Link({
    href,
    children,
    className,
}: PropsWithChildren<{
    href: string | { url: string };
    className?: string;
}>) {
    return (
        <a
            href={typeof href === 'string' ? href : href.url}
            className={className}
        >
            {children}
        </a>
    );
}
