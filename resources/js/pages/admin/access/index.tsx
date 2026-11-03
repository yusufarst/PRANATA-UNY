import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import GrantController from '@/actions/App/Modules/Access/Http/Controllers/GrantController';
import { selectClassName } from '@/components/admin/users';
import { PageHeader } from '@/components/app/page-header';
import { EmptyState } from '@/components/app/state-views';
import { StatusBadge } from '@/components/app/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { GrantSummary } from '@/lib/access';
import {
    grantStatusCategory,
    grantStatuses,
    grantSubject,
    scopeTarget,
} from '@/lib/access';

type Filters = { q: string; status: string };

// The table columns, each labelled by admin:access.fields.<column>.
const columns = ['user', 'subject', 'scope', 'period', 'status'] as const;

type Props = {
    grants: {
        data: GrantSummary[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: Filters;
    pendingCount: number;
    can: { grant: boolean };
};

export default function AccessIndex({
    grants,
    filters,
    pendingCount,
    can,
}: Props) {
    const { t } = useTranslation();
    const filtered = filters.q !== '' || filters.status !== '';

    const applyFilters = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = Object.fromEntries(
            [...new FormData(event.currentTarget)].filter(
                ([, value]) => value !== '',
            ),
        );

        router.get(GrantController.index.url(), data, {
            preserveState: true,
            replace: true,
        });
    };

    const createAction = can.grant && (
        <Button asChild>
            <Link href={GrantController.create()} data-test="grant-access">
                <Plus aria-hidden="true" />
                {t('admin:access.create')}
            </Link>
        </Button>
    );

    const scope = (grant: GrantSummary) => {
        const target = scopeTarget(grant);
        const kind = t(`admin:access.scope.${grant.scope_type}`);

        return target === null ? kind : `${kind}: ${target}`;
    };

    const period = (grant: GrantSummary) =>
        grant.effective_to === null
            ? t('admin:access.period_open', { from: grant.effective_from })
            : t('admin:access.period', {
                  from: grant.effective_from,
                  to: grant.effective_to,
              });

    const badge = (grant: GrantSummary) => (
        <StatusBadge
            category={grantStatusCategory[grant.status]}
            label={t(`admin:access.status.${grant.status}`)}
        />
    );

    return (
        <>
            <Head title={t('admin:access.title')} />
            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={t('admin:access.title')}
                    description={t('admin:access.intro')}
                    action={createAction}
                />

                {pendingCount > 0 && filters.status !== 'PENDING_APPROVAL' && (
                    <p className="rounded-lg border border-border bg-warning-surface p-4 text-sm text-foreground">
                        {t('admin:access.pending_notice', {
                            count: pendingCount,
                        })}{' '}
                        <Link
                            href={GrantController.index({
                                query: { status: 'PENDING_APPROVAL' },
                            })}
                            className="font-medium underline underline-offset-4"
                        >
                            {t('admin:access.pending_show')}
                        </Link>
                    </p>
                )}

                <form
                    role="search"
                    aria-label={t('admin:access.filters.label')}
                    onSubmit={applyFilters}
                    className="grid gap-4 rounded-lg border border-border bg-card p-4 shadow-card sm:grid-cols-2 lg:grid-cols-[2fr_1fr_auto] lg:items-end"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="filter-q">
                            {t('admin:access.filters.search')}
                        </Label>
                        <Input
                            id="filter-q"
                            name="q"
                            type="search"
                            defaultValue={filters.q}
                            placeholder={t(
                                'admin:access.filters.search_placeholder',
                            )}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="filter-status">
                            {t('admin:access.filters.status')}
                        </Label>
                        <select
                            id="filter-status"
                            name="status"
                            defaultValue={filters.status}
                            className={selectClassName}
                        >
                            <option value="">
                                {t('admin:access.filters.all')}
                            </option>
                            {grantStatuses.map((status) => (
                                <option key={status} value={status}>
                                    {t(`admin:access.status.${status}`)}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-1">
                        <Button type="submit" variant="secondary">
                            {t('admin:access.filters.apply')}
                        </Button>
                        {filtered && (
                            <Button variant="ghost" asChild>
                                <Link href={GrantController.index()}>
                                    {t('admin:access.filters.reset')}
                                </Link>
                            </Button>
                        )}
                    </div>
                </form>

                {grants.data.length === 0 ? (
                    filtered ? (
                        <EmptyState
                            title={t('admin:access.empty_filtered_title')}
                            description={t(
                                'admin:access.empty_filtered_description',
                            )}
                        />
                    ) : (
                        <EmptyState
                            title={t('admin:access.empty_title')}
                            description={t('admin:access.empty_description')}
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
                                                    `admin:access.fields.${column}`,
                                                )}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {grants.data.map((grant) => (
                                        <tr
                                            key={grant.public_id}
                                            className="border-b border-border last:border-0"
                                        >
                                            <td className="px-4 py-3">
                                                <Link
                                                    href={GrantController.show(
                                                        grant.public_id,
                                                    )}
                                                    className="font-medium text-foreground underline-offset-4 hover:underline"
                                                >
                                                    {grant.user.name}
                                                </Link>
                                                <p className="font-mono text-muted-foreground">
                                                    {grant.user.username}
                                                </p>
                                            </td>
                                            <td className="px-4 py-3 break-words">
                                                {grantSubject(grant)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {scope(grant)}
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">
                                                {period(grant)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {badge(grant)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Narrow screens: cards. */}
                        <ul className="grid gap-3 md:hidden">
                            {grants.data.map((grant) => (
                                <li key={grant.public_id}>
                                    <Link
                                        href={GrantController.show(
                                            grant.public_id,
                                        )}
                                        className="flex flex-col gap-2 rounded-lg border border-border bg-card p-4 shadow-card"
                                    >
                                        <span className="flex flex-wrap items-center justify-between gap-2">
                                            <span className="font-medium text-foreground">
                                                {grant.user.name}
                                            </span>
                                            {badge(grant)}
                                        </span>
                                        <span className="text-sm break-words text-foreground">
                                            {grantSubject(grant)}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            {scope(grant)}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            {period(grant)}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        <nav
                            aria-label={t('admin:access.pagination.label')}
                            className="flex flex-wrap items-center justify-between gap-3"
                        >
                            <p className="text-sm text-muted-foreground">
                                {t('admin:access.pagination.summary', {
                                    current: grants.current_page,
                                    last: grants.last_page,
                                    total: grants.total,
                                })}
                            </p>
                            {grants.last_page > 1 && (
                                <div className="flex gap-2">
                                    {grants.prev_page_url && (
                                        <Button variant="outline" asChild>
                                            <Link
                                                href={grants.prev_page_url}
                                                preserveScroll
                                            >
                                                {t(
                                                    'admin:access.pagination.previous',
                                                )}
                                            </Link>
                                        </Button>
                                    )}
                                    {grants.next_page_url && (
                                        <Button variant="outline" asChild>
                                            <Link
                                                href={grants.next_page_url}
                                                preserveScroll
                                            >
                                                {t(
                                                    'admin:access.pagination.next',
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

AccessIndex.layout = {
    breadcrumbs: [
        {
            title: 'admin:access.title',
            href: GrantController.index(),
        },
    ],
};
