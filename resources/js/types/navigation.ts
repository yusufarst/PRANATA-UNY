import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    /** A catalog key: static layout properties cannot translate. */
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    /** A catalog key: static layout properties cannot translate. */
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};
