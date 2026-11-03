import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
import type { AccountSummary } from '@/components/admin/users';
import {
    AccountStatusBadge,
    accountStatuses,
    rememberListQuery,
    roleGroups,
    selectClassName,
    useDateTime,
} from '@/components/admin/users';
import { PageHeader } from '@/components/app/page-header';
import { EmptyState } from '@/components/app/state-views';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Filters = { q: string; status: string; role_group: string };

// The table columns, each labelled by admin:users.fields.<column>.
const columns = [
    'name',
    'username',
    'role_group',
    'status',
    'last_login_at',
] as const;

type Props = {
    users: {
        data: AccountSummary[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: Filters;
};

export default function UserIndex({ users, filters }: Props) {
    const { t } = useTranslation();
    const dateTime = useDateTime();
    const filtered =
        filters.q !== '' || filters.status !== '' || filters.role_group !== '';

    // The detail leads back to this list with the same filters and page.
    useEffect(() => rememberListQuery(window.location.search));

    const applyFilters = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = Object.fromEntries(
            [...new FormData(event.currentTarget)].filter(
                ([, value]) => value !== '',
            ),
        );

        router.get(UserAdministrationController.index.url(), data, {
            preserveState: true,
            replace: true,
        });
    };

    const createAction = (
        <Button asChild>
            <Link href={UserAdministrationController.create()}>
                <Plus aria-hidden="true" />
                {t('admin:users.create')}
            </Link>
        </Button>
    );

    return (
        <>
            <Head title={t('admin:users.title')} />
            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={t('admin:users.title')}
                    description={t('admin:users.intro')}
                    action={createAction}
                />

                <form
                    role="search"
                    aria-label={t('admin:users.filters.label')}
                    onSubmit={applyFilters}
                    className="grid gap-4 rounded-lg border border-border bg-card p-4 shadow-card sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="filter-q">
                            {t('admin:users.filters.search')}
                        </Label>
                        <Input
                            id="filter-q"
                            name="q"
                            type="search"
                            defaultValue={filters.q}
                            placeholder={t(
                                'admin:users.filters.search_placeholder',
                            )}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="filter-status">
                            {t('admin:users.filters.status')}
                        </Label>
                        <select
                            id="filter-status"
                            name="status"
                            defaultValue={filters.status}
                            className={selectClassName}
                        >
                            <option value="">
                                {t('admin:users.filters.all')}
                            </option>
                            {accountStatuses.map((status) => (
                                <option key={status} value={status}>
                                    {t(`admin:users.status.${status}`)}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="filter-role-group">
                            {t('admin:users.filters.role_group')}
                        </Label>
                        <select
                            id="filter-role-group"
                            name="role_group"
                            defaultValue={filters.role_group}
                            className={selectClassName}
                        >
                            <option value="">
                                {t('admin:users.filters.all')}
                            </option>
                            {roleGroups.map((group) => (
                                <option key={group} value={group}>
                                    {t(`admin:users.role_group.${group}`)}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-1">
                        <Button type="submit" variant="secondary">
                            {t('admin:users.filters.apply')}
                        </Button>
                        {filtered && (
                            <Button variant="ghost" asChild>
                                <Link
                                    href={UserAdministrationController.index()}
                                >
                                    {t('admin:users.filters.reset')}
                                </Link>
                            </Button>
                        )}
                    </div>
                </form>

                {users.data.length === 0 ? (
                    filtered ? (
                        <EmptyState
                            title={t('admin:users.empty_filtered_title')}
                            description={t(
                                'admin:users.empty_filtered_description',
                            )}
                        />
                    ) : (
                        <EmptyState
                            title={t('admin:users.empty_title')}
                            description={t('admin:users.empty_description')}
                            action={createAction}
                        />
                    )
                ) : (
                    <>
                        {/* Wide screens: a table. */}
                        <div className="hidden overflow-x-auto rounded-lg border border-border bg-card shadow-card md:block">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b border-border text-muted-foreground">
                                    <tr>
                                        {columns.map((column) => (
                                            <th
                                                key={column}
                                                scope="col"
                                                className="px-4 py-3 font-medium"
                                            >
                                                {t(
                                                    `admin:users.fields.${column}`,
                                                )}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {users.data.map((user) => (
                                        <tr
                                            key={user.public_id}
                                            className="border-b border-border last:border-0"
                                        >
                                            <td className="px-4 py-3">
                                                <Link
                                                    href={UserAdministrationController.show(
                                                        user.public_id,
                                                    )}
                                                    className="font-medium text-foreground underline-offset-4 hover:underline"
                                                >
                                                    {user.name}
                                                </Link>
                                                {user.email && (
                                                    <p className="text-muted-foreground">
                                                        {user.email}
                                                    </p>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 font-mono">
                                                {user.username}
                                            </td>
                                            <td className="px-4 py-3">
                                                {t(
                                                    `admin:users.role_group.${user.role_group}`,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <AccountStatusBadge
                                                    status={user.status}
                                                />
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">
                                                {dateTime(user.last_login_at) ??
                                                    t(
                                                        'admin:users.fields.never',
                                                    )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Narrow screens: cards. */}
                        <ul className="grid gap-3 md:hidden">
                            {users.data.map((user) => (
                                <li key={user.public_id}>
                                    <Link
                                        href={UserAdministrationController.show(
                                            user.public_id,
                                        )}
                                        className="flex flex-col gap-2 rounded-lg border border-border bg-card p-4 shadow-card"
                                    >
                                        <span className="flex flex-wrap items-center justify-between gap-2">
                                            <span className="font-medium text-foreground">
                                                {user.name}
                                            </span>
                                            <AccountStatusBadge
                                                status={user.status}
                                            />
                                        </span>
                                        <span className="font-mono text-sm text-muted-foreground">
                                            {user.username}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            {t(
                                                `admin:users.role_group.${user.role_group}`,
                                            )}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        <nav
                            aria-label={t('admin:users.pagination.label')}
                            className="flex flex-wrap items-center justify-between gap-3"
                        >
                            <p className="text-sm text-muted-foreground">
                                {t('admin:users.pagination.summary', {
                                    current: users.current_page,
                                    last: users.last_page,
                                    total: users.total,
                                })}
                            </p>
                            {users.last_page > 1 && (
                                <div className="flex gap-2">
                                    {users.prev_page_url && (
                                        <Button variant="outline" asChild>
                                            <Link
                                                href={users.prev_page_url}
                                                preserveScroll
                                            >
                                                {t(
                                                    'admin:users.pagination.previous',
                                                )}
                                            </Link>
                                        </Button>
                                    )}
                                    {users.next_page_url && (
                                        <Button variant="outline" asChild>
                                            <Link
                                                href={users.next_page_url}
                                                preserveScroll
                                            >
                                                {t(
                                                    'admin:users.pagination.next',
                                                )}
                                            </Link>
                                        </Button>
                                    )}
                                </div>
                            )}
                        </nav>
                    </>
                )}
            </div>
        </>
    );
}

UserIndex.layout = {
    breadcrumbs: [
        {
            title: 'admin:users.title',
            href: UserAdministrationController.index(),
        },
    ],
};
