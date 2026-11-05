import { Head, Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import AssignmentController from '@/actions/App/Modules/Access/Http/Controllers/AssignmentController';
import { selectClassName, useDateTime } from '@/components/admin/users';
import type { AssignmentRow } from '@/components/app/AssignmentPanel';
import {
    AssignmentBadge,
    assignmentStatuses,
} from '@/components/app/AssignmentPanel';
import { PageHeader } from '@/components/app/page-header';
import { EmptyState } from '@/components/app/state-views';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Filters = { q: string; status: string; role: string };

type Row = AssignmentRow & {
    subject: { type: string; reference: string | null; label: string | null };
};

// The table columns, each labelled by admin:assignments.fields.<column>.
const columns = ['user', 'object', 'role', 'period', 'status'] as const;

type Props = {
    assignments: {
        data: Row[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: Filters;
    roles: { value: string; decision: boolean }[];
    types: { key: string; kind: string }[];
};

export default function AssignmentIndex({
    assignments,
    filters,
    roles,
    types,
}: Props) {
    const { t } = useTranslation();
    const dateTime = useDateTime();
    const filtered =
        filters.q !== '' || filters.status !== '' || filters.role !== '';

    const applyFilters = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = Object.fromEntries(
            [...new FormData(event.currentTarget)].filter(
                ([, value]) => value !== '',
            ),
        );

        router.get(AssignmentController.index.url(), data, {
            preserveState: true,
            replace: true,
        });
    };

    // PG-504 has one primary action: open the object on which people are assigned.
    const openAction = (
        <Button asChild>
            <Link
                href={AssignmentController.create()}
                data-test="open-assignment-object"
            >
                <Search aria-hidden="true" />
                {t('admin:assignments.open_object')}
            </Link>
        </Button>
    );

    const kindOf = (type: string) =>
        types.find((each) => each.key === type)?.kind;

    // An object whose module is not installed shows by its type and id only.
    const object = (row: Row) => {
        const kind = kindOf(row.subject.type);
        const name = kind ? t(`admin:assignments.kinds.${kind}`) : null;

        if (row.subject.reference === null || name === null) {
            return row.subject.type;
        }

        return `${name} ${row.subject.reference}${row.subject.label ? ` · ${row.subject.label}` : ''}`;
    };

    const objectLink = (row: Row) =>
        row.subject.reference === null
            ? null
            : AssignmentController.create({
                  query: {
                      type: row.subject.type,
                      reference: row.subject.reference,
                  },
              });

    const period = (row: Row) =>
        row.effective_to === null
            ? t('admin:assignments.period_open', {
                  from: dateTime(row.effective_from),
              })
            : t('admin:assignments.period', {
                  from: dateTime(row.effective_from),
                  to: dateTime(row.effective_to),
              });

    const objectCell = (row: Row) => {
        const href = objectLink(row);

        return href === null ? (
            <span className="break-words">{object(row)}</span>
        ) : (
            <Link
                href={href}
                className="break-words text-foreground underline-offset-4 hover:underline"
            >
                {object(row)}
            </Link>
        );
    };

    return (
        <>
            <Head title={t('admin:assignments.title')} />
            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={t('admin:assignments.title')}
                    description={t('admin:assignments.intro')}
                    action={openAction}
                />

                <form
                    role="search"
                    aria-label={t('admin:assignments.filters.label')}
                    onSubmit={applyFilters}
                    className="grid gap-4 rounded-lg border border-border bg-card p-4 shadow-card sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end"
                >
                    <div className="grid gap-2 sm:col-span-2 lg:col-span-1">
                        <Label htmlFor="filter-q">
                            {t('admin:assignments.filters.search')}
                        </Label>
                        <Input
                            id="filter-q"
                            name="q"
                            type="search"
                            defaultValue={filters.q}
                            placeholder={t(
                                'admin:assignments.filters.search_placeholder',
                            )}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="filter-status">
                            {t('admin:assignments.filters.status')}
                        </Label>
                        <select
                            id="filter-status"
                            name="status"
                            defaultValue={filters.status}
                            className={selectClassName}
                        >
                            <option value="">
                                {t('admin:assignments.filters.all')}
                            </option>
                            {assignmentStatuses.map((status) => (
                                <option key={status} value={status}>
                                    {t(`admin:assignments.status.${status}`)}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="filter-role">
                            {t('admin:assignments.filters.role')}
                        </Label>
                        <select
                            id="filter-role"
                            name="role"
                            defaultValue={filters.role}
                            className={selectClassName}
                        >
                            <option value="">
                                {t('admin:assignments.filters.all')}
                            </option>
                            {roles.map((role) => (
                                <option key={role.value} value={role.value}>
                                    {role.value}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-1">
                        <Button type="submit" variant="secondary">
                            {t('admin:assignments.filters.apply')}
                        </Button>
                        {filtered && (
                            <Button variant="ghost" asChild>
                                <Link href={AssignmentController.index()}>
                                    {t('admin:assignments.filters.reset')}
                                </Link>
                            </Button>
                        )}
                    </div>
                </form>

                {assignments.data.length === 0 ? (
                    filtered ? (
                        <EmptyState
                            title={t('admin:assignments.empty_filtered_title')}
                            description={t(
                                'admin:assignments.empty_filtered_description',
                            )}
                        />
                    ) : (
                        <EmptyState
                            title={t('admin:assignments.empty_title')}
                            description={t(
                                'admin:assignments.empty_description',
                            )}
                            action={openAction}
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
                                                    `admin:assignments.fields.${column}`,
                                                )}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {assignments.data.map((row) => (
                                        <tr
                                            key={row.public_id}
                                            className="border-b border-border last:border-0"
                                        >
                                            <td className="px-4 py-3">
                                                <p className="font-medium text-foreground">
                                                    {row.user.name}
                                                </p>
                                                <p className="font-mono text-muted-foreground">
                                                    {row.user.username}
                                                </p>
                                            </td>
                                            <td className="px-4 py-3">
                                                {objectCell(row)}
                                            </td>
                                            <td className="px-4 py-3 font-mono">
                                                {row.assignment_role}
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">
                                                {period(row)}
                                            </td>
                                            <td className="px-4 py-3">
                                                <AssignmentBadge row={row} />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Narrow screens: cards. */}
                        <ul className="grid gap-3 md:hidden">
                            {assignments.data.map((row) => (
                                <li
                                    key={row.public_id}
                                    className="flex flex-col gap-2 rounded-lg border border-border bg-card p-4 shadow-card"
                                >
                                    <span className="flex flex-wrap items-center justify-between gap-2">
                                        <span className="font-medium text-foreground">
                                            {row.user.name}
                                        </span>
                                        <AssignmentBadge row={row} />
                                    </span>
                                    <span className="font-mono text-sm">
                                        {row.assignment_role}
                                    </span>
                                    <span className="text-sm">
                                        {objectCell(row)}
                                    </span>
                                    <span className="text-sm text-muted-foreground">
                                        {period(row)}
                                    </span>
                                </li>
                            ))}
                        </ul>

                        <nav
                            aria-label={t('admin:assignments.pagination.label')}
                            className="flex flex-wrap items-center justify-between gap-3"
                        >
                            <p className="text-sm text-muted-foreground">
                                {t('admin:assignments.pagination.summary', {
                                    current: assignments.current_page,
                                    last: assignments.last_page,
                                    total: assignments.total,
                                })}
                            </p>
                            {assignments.last_page > 1 && (
                                <div className="flex gap-2">
                                    {assignments.prev_page_url && (
                                        <Button variant="outline" asChild>
                                            <Link
                                                href={assignments.prev_page_url}
                                                preserveScroll
                                            >
                                                {t(
                                                    'admin:assignments.pagination.previous',
                                                )}
                                            </Link>
                                        </Button>
                                    )}
                                    {assignments.next_page_url && (
                                        <Button variant="outline" asChild>
                                            <Link
                                                href={assignments.next_page_url}
                                                preserveScroll
                                            >
                                                {t(
                                                    'admin:assignments.pagination.next',
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

AssignmentIndex.layout = {
    breadcrumbs: [
        {
            title: 'admin:assignments.title',
            href: AssignmentController.index(),
        },
    ],
};
