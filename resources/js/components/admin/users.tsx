import { useTranslation } from 'react-i18next';
import type { StatusCategory } from '@/components/app/status-badge';
import { StatusBadge } from '@/components/app/status-badge';

export const accountStatuses = [
    'ACTIVE',
    'SUSPENDED',
    'LOCKED',
    'DEACTIVATED',
] as const;

export const roleGroups = ['SUPER_ADMIN', 'INTERNAL', 'VENDOR'] as const;

/** The role groups an administrator may give a new account (TASK-011 TAC-01). */
export const creatableRoleGroups = ['INTERNAL', 'VENDOR'] as const;

export type AccountStatus = (typeof accountStatuses)[number];

export type RoleGroup = (typeof roleGroups)[number];

export type AccountSummary = {
    public_id: string;
    username: string;
    name: string;
    email: string | null;
    role_group: RoleGroup;
    status: AccountStatus;
    last_login_at: string | null;
};

export type Account = AccountSummary & {
    staff_identifier: string | null;
    locale: 'id' | 'en';
    must_change_password: boolean;
    lock_version: number;
};

/** A native select that looks like the Input primitive, so it also submits with a plain form. */
export const selectClassName =
    'flex h-9 w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm';

const statusCategory: Record<AccountStatus, StatusCategory> = {
    ACTIVE: 'completed',
    SUSPENDED: 'waiting',
    LOCKED: 'blocked',
    DEACTIVATED: 'rejected',
};

// DESIGN_SYSTEM section 8: text, icon and color together, with the account status as the text.
export function AccountStatusBadge({ status }: { status: AccountStatus }) {
    const { t } = useTranslation();

    return (
        <StatusBadge
            category={statusCategory[status]}
            label={t(`admin:users.status.${status}`)}
        />
    );
}

/** A moment from the server in the language of the page, or null when there is none. */
export function useDateTime(): (value: string | null) => string | null {
    const { i18n } = useTranslation();
    const format = new Intl.DateTimeFormat(
        i18n.language === 'en' ? 'en-GB' : 'id-ID',
        { dateStyle: 'medium', timeStyle: 'short' },
    );

    return (value) => (value === null ? null : format.format(new Date(value)));
}

const listQueryKey = 'pranata.admin.users.query';

/** The list keeps its filters in the address; the detail leads back to the same filtered list (TASK-011 navigation). */
export function rememberListQuery(search: string): void {
    try {
        window.sessionStorage.setItem(listQueryKey, search);
    } catch {
        // Storage may be off; the way back then shows the whole list.
    }
}

export function rememberedListQuery(): Record<string, string> {
    try {
        return Object.fromEntries(
            new URLSearchParams(
                window.sessionStorage.getItem(listQueryKey) ?? '',
            ),
        );
    } catch {
        return {};
    }
}
