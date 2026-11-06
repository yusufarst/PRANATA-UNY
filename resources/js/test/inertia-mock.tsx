import type { PropsWithChildren, ReactNode } from 'react';
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
        can: {
            manageUsers: false,
            manageOrganization: false,
            grantAccess: false,
            approveAccess: false,
        },
    },
    sidebarOpen: true,
    locale: 'id',
    menu: [{ key: 'home', workspace: 'home', href: '/home' }],
    workspaces: [{ key: 'home', href: '/home' }],
};

export const router = {
    post: vi.fn(),
    on: vi.fn(),
    flushAll: vi.fn(),
    visit: vi.fn(),
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

type FormRenderProps = {
    processing: boolean;
    progress: null;
    errors: Record<string, string>;
};

/** A plain form at rest: nothing is processing, no progress, no errors. */
export function Form({
    children,
    action,
    method,
    className,
}: {
    children: ReactNode | ((props: FormRenderProps) => ReactNode);
    action?: string;
    method?: string;
    className?: string;
    [key: string]: unknown;
}) {
    return (
        <form action={action} method={method} className={className}>
            {typeof children === 'function'
                ? children({ processing: false, progress: null, errors: {} })
                : children}
        </form>
    );
}
