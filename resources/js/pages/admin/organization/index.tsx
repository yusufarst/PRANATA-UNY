import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import ChangeRequestController from '@/actions/App/Modules/Organization/Http/Controllers/ChangeRequestController';
import OrganizationController from '@/actions/App/Modules/Organization/Http/Controllers/OrganizationController';
import type {
    RequestSummary,
    UnitStatus,
    UnitSummary,
} from '@/components/admin/organization';
import {
    RequestStatusBadge,
    SimulationBadge,
    UnitStatusBadge,
    useDate,
} from '@/components/admin/organization';
import { useDateTime } from '@/components/admin/users';
import { PageHeader } from '@/components/app/page-header';
import { EmptyState } from '@/components/app/state-views';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type TreeUnit = {
    public_id: string;
    code: string;
    name: string;
    short_name: string | null;
    unit_type: string;
    parent: string | null;
    depth: number;
    status: UnitStatus;
};

type Snapshot = Partial<{
    code: string;
    name: string;
    short_name: string | null;
    unit_type: string;
    parent_public_id: string | null;
    status: UnitStatus;
    effective_from: string;
}>;

type Detail = {
    unit: UnitSummary & {
        path: string;
        depth: number;
        effective_from: string;
        effective_to: string | null;
        lock_version: number;
    };
    ancestors: { public_id: string; name: string }[];
    history: {
        id: number;
        change_type: string;
        before: Snapshot | null;
        after: Snapshot | null;
        effective_from: string;
        decision_ref: string | null;
        is_simulation: boolean;
        changed_by: string | null;
        changed_at: string;
    }[];
    positions: {
        code: string;
        name: string;
        description: string | null;
        status: UnitStatus;
    }[];
    appointments: {
        id: number;
        person: string;
        username: string;
        position: string;
        position_code: string;
        effective_from: string;
        effective_to: string | null;
        decree_ref: string | null;
        status: 'ACTIVE' | 'ENDED';
    }[];
    requests: RequestSummary[];
};

type Props = {
    units: TreeUnit[];
    selected: Detail | null;
    openRequests: RequestSummary[];
};

// The unit fields a history entry compares, each labelled by admin:organization.fields.<field>.
const comparedFields = [
    'code',
    'name',
    'short_name',
    'unit_type',
    'parent_public_id',
    'status',
] as const;

const sectionClassName =
    'rounded-lg border border-border bg-card p-4 shadow-card sm:p-6';

export default function OrganizationIndex({
    units,
    selected,
    openRequests,
}: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState('');
    const selectedId = selected?.unit.public_id ?? null;

    const byId = useMemo(
        () => new Map(units.map((unit) => [unit.public_id, unit])),
        [units],
    );

    // A search shows every matching unit with the units above it, so a match is never out of context (TAC-01).
    const visible = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (term === '') {
            return units;
        }

        const keep = new Set<string>();

        for (const unit of units) {
            const text =
                `${unit.name} ${unit.code} ${unit.short_name ?? ''} ${unit.unit_type}`.toLowerCase();

            if (!text.includes(term)) {
                continue;
            }

            let current: TreeUnit | undefined = unit;

            while (current && !keep.has(current.public_id)) {
                keep.add(current.public_id);
                current = current.parent ? byId.get(current.parent) : undefined;
            }
        }

        return units.filter((unit) => keep.has(unit.public_id));
    }, [units, search, byId]);

    const requestAction = (
        <Button asChild>
            <Link
                href={ChangeRequestController.create({
                    query: selectedId ? { unit: selectedId } : {},
                })}
                data-test="new-change-request"
            >
                <Plus aria-hidden="true" />
                {t('admin:organization.request_change')}
            </Link>
        </Button>
    );

    return (
        <>
            <Head title={t('admin:organization.title')} />
            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={t('admin:organization.title')}
                    description={t('admin:organization.intro')}
                    action={requestAction}
                />

                {units.length === 0 ? (
                    <EmptyState
                        title={t('admin:organization.empty_title')}
                        description={t('admin:organization.empty_description')}
                        action={
                            <Button asChild>
                                <Link
                                    href={ChangeRequestController.create({
                                        query: { kind: 'CREATE_UNIT' },
                                    })}
                                >
                                    <Plus aria-hidden="true" />
                                    {t('admin:organization.first_unit')}
                                </Link>
                            </Button>
                        }
                    />
                ) : (
                    <div className="grid gap-6 lg:grid-cols-[minmax(18rem,26rem)_1fr] lg:items-start">
                        <nav
                            aria-label={t('admin:organization.tree.label')}
                            className={cn(
                                sectionClassName,
                                'flex flex-col gap-4',
                                selected && 'order-2 lg:order-none',
                            )}
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="unit-search">
                                    {t('admin:organization.tree.search')}
                                </Label>
                                <Input
                                    id="unit-search"
                                    type="search"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder={t(
                                        'admin:organization.tree.search_placeholder',
                                    )}
                                />
                            </div>
                            {visible.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    {t('admin:organization.tree.no_match')}
                                </p>
                            ) : (
                                <ul className="grid gap-1" data-test="unit-tree">
                                    {visible.map((unit) => (
                                        <li key={unit.public_id}>
                                            <Link
                                                href={OrganizationController.index(
                                                    {
                                                        query: {
                                                            unit: unit.public_id,
                                                        },
                                                    },
                                                )}
                                                preserveScroll
                                                aria-current={
                                                    unit.public_id ===
                                                    selectedId
                                                        ? 'page'
                                                        : undefined
                                                }
                                                style={{
                                                    paddingLeft: `${0.75 + Math.min(unit.depth, 8) * 1}rem`,
                                                }}
                                                className={cn(
                                                    'flex min-h-11 flex-col justify-center rounded-md py-2 pr-3 text-sm hover:bg-accent',
                                                    unit.public_id ===
                                                        selectedId &&
                                                        'bg-accent font-semibold',
                                                )}
                                            >
                                                <span className="text-foreground">
                                                    {unit.name}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {[
                                                        unit.code,
                                                        unit.unit_type,
                                                        ...(unit.status ===
                                                        'INACTIVE'
                                                            ? [
                                                                  t(
                                                                      'admin:organization.unit_status.INACTIVE',
                                                                  ),
                                                              ]
                                                            : []),
                                                    ].join(' · ')}
                                                </span>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </nav>

                        {selected ? (
                            <UnitDetail detail={selected} byId={byId} />
                        ) : (
                            <OpenRequests requests={openRequests} />
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

function UnitDetail({
    detail,
    byId,
}: {
    detail: Detail;
    byId: Map<string, TreeUnit>;
}) {
    const { t } = useTranslation();
    const date = useDate();
    const dateTime = useDateTime();
    const { unit } = detail;
    const none = t('admin:organization.none');

    const unitName = (publicId: string | null | undefined) =>
        publicId ? (byId.get(publicId)?.name ?? publicId) : none;

    const fieldValue = (
        field: (typeof comparedFields)[number],
        snapshot: Snapshot,
    ): string => {
        const value = snapshot[field];

        if (field === 'parent_public_id') {
            return unitName(value);
        }

        if (field === 'status' && value) {
            return t(`admin:organization.unit_status.${value}`);
        }

        return value ?? none;
    };

    const details: [string, string][] = [
        [t('admin:organization.fields.code'), unit.code],
        [t('admin:organization.fields.short_name'), unit.short_name ?? none],
        [t('admin:organization.fields.unit_type'), unit.unit_type],
        [t('admin:organization.fields.parent_public_id'), unit.parent_name ?? none],
        [
            t('admin:organization.fields.effective_from'),
            date(unit.effective_from) ?? none,
        ],
    ];

    return (
        <div className="order-1 flex min-w-0 flex-col gap-6 lg:order-none">
            <section
                aria-labelledby="unit-name"
                className={cn(sectionClassName, 'flex flex-col gap-4')}
                data-test="unit-detail"
            >
                <div>
                    <Button variant="ghost" size="sm" asChild className="-ml-2">
                        <Link href={OrganizationController.index()}>
                            <ArrowLeft aria-hidden="true" />
                            {t('admin:organization.back_to_tree')}
                        </Link>
                    </Button>
                </div>
                {detail.ancestors.length > 0 && (
                    <nav aria-label={t('admin:organization.ancestors')}>
                        <ol className="flex flex-wrap gap-1 text-sm text-muted-foreground">
                            {detail.ancestors.map((ancestor) => (
                                <li
                                    key={ancestor.public_id}
                                    className="after:ml-1 after:content-['/'] last:after:content-['']"
                                >
                                    <Link
                                        href={OrganizationController.index({
                                            query: {
                                                unit: ancestor.public_id,
                                            },
                                        })}
                                        preserveScroll
                                        className="underline-offset-4 hover:underline"
                                    >
                                        {ancestor.name}
                                    </Link>
                                </li>
                            ))}
                        </ol>
                    </nav>
                )}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex flex-wrap items-center gap-3">
                        <h2
                            id="unit-name"
                            className="text-xl font-semibold text-foreground"
                        >
                            {unit.name}
                        </h2>
                        <UnitStatusBadge status={unit.status} />
                    </div>
                    {unit.status === 'ACTIVE' && (
                        <Button variant="outline" asChild>
                            <Link
                                href={ChangeRequestController.create({
                                    query: {
                                        kind: 'CREATE_UNIT',
                                        unit: unit.public_id,
                                    },
                                })}
                                data-test="new-sub-unit"
                            >
                                {t('admin:organization.new_sub_unit')}
                            </Link>
                        </Button>
                    )}
                </div>
                <dl className="grid gap-4 sm:grid-cols-2">
                    {details.map(([label, value]) => (
                        <div key={label} className="grid gap-1">
                            <dt className="text-sm text-muted-foreground">
                                {label}
                            </dt>
                            <dd className="break-words text-foreground">
                                {value}
                            </dd>
                        </div>
                    ))}
                </dl>
            </section>

            <section
                aria-labelledby="unit-history"
                className={sectionClassName}
                data-test="unit-history"
            >
                <h2
                    id="unit-history"
                    className="mb-1 text-lg font-semibold text-foreground"
                >
                    {t('admin:organization.history.section')}
                </h2>
                <p className="mb-4 text-sm text-muted-foreground">
                    {t('admin:organization.history.intro')}
                </p>
                {detail.history.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('admin:organization.history.empty')}
                    </p>
                ) : (
                    <ol className="grid gap-3">
                        {detail.history.map((entry) => {
                            const changed = comparedFields.filter(
                                (field) =>
                                    entry.after &&
                                    (entry.before === null ||
                                        entry.before[field] !==
                                            entry.after[field]),
                            );

                            return (
                                <li
                                    key={entry.id}
                                    className="grid gap-2 rounded-md border border-border p-3"
                                >
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="font-medium text-foreground">
                                            {t(
                                                `admin:organization.change_type.${entry.change_type}`,
                                            )}
                                        </span>
                                        {entry.is_simulation && (
                                            <SimulationBadge />
                                        )}
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {t('admin:organization.history.meta', {
                                            date: date(entry.effective_from),
                                            decision:
                                                entry.decision_ref ?? none,
                                            name: entry.changed_by ?? none,
                                            time: dateTime(entry.changed_at),
                                        })}
                                    </p>
                                    {changed.length > 0 && entry.after && (
                                        <dl className="grid gap-1 text-sm">
                                            {changed.map((field) => (
                                                <div
                                                    key={field}
                                                    className="flex flex-wrap gap-x-2"
                                                >
                                                    <dt className="text-muted-foreground">
                                                        {t(
                                                            `admin:organization.fields.${field}`,
                                                        )}
                                                    </dt>
                                                    <dd className="break-words text-foreground">
                                                        {entry.before &&
                                                            `${fieldValue(field, entry.before)} → `}
                                                        {fieldValue(
                                                            field,
                                                            entry.after,
                                                        )}
                                                    </dd>
                                                </div>
                                            ))}
                                        </dl>
                                    )}
                                </li>
                            );
                        })}
                    </ol>
                )}
            </section>

            <section
                aria-labelledby="unit-positions"
                className={sectionClassName}
            >
                <h2
                    id="unit-positions"
                    className="mb-4 text-lg font-semibold text-foreground"
                >
                    {t('admin:organization.positions.section')}
                </h2>
                {detail.positions.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('admin:organization.positions.empty')}
                    </p>
                ) : (
                    <ul className="grid gap-3">
                        {detail.positions.map((position) => (
                            <li key={position.code} className="grid gap-1">
                                <span className="font-medium text-foreground">
                                    {position.name}
                                </span>
                                <span className="font-mono text-xs text-muted-foreground">
                                    {position.code}
                                </span>
                                {position.description && (
                                    <span className="text-sm text-muted-foreground">
                                        {position.description}
                                    </span>
                                )}
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <section
                aria-labelledby="unit-appointments"
                className={sectionClassName}
                data-test="unit-appointments"
            >
                <h2
                    id="unit-appointments"
                    className="mb-1 text-lg font-semibold text-foreground"
                >
                    {t('admin:organization.appointments.section')}
                </h2>
                <p className="mb-4 text-sm text-muted-foreground">
                    {t('admin:organization.appointments.no_permission')}
                </p>
                {detail.appointments.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('admin:organization.appointments.empty')}
                    </p>
                ) : (
                    <ul className="grid gap-3">
                        {detail.appointments.map((appointment) => (
                            <li
                                key={appointment.id}
                                className="grid gap-1 rounded-md border border-border p-3"
                            >
                                <span className="flex flex-wrap items-center gap-2">
                                    <span className="font-medium text-foreground">
                                        {appointment.person}
                                    </span>
                                    <span className="text-sm text-muted-foreground">
                                        {t(
                                            `admin:organization.appointment_status.${appointment.status}`,
                                        )}
                                    </span>
                                </span>
                                <span className="text-sm text-foreground">
                                    {appointment.position}
                                </span>
                                <span className="text-sm text-muted-foreground">
                                    {t('admin:organization.appointments.term', {
                                        from: date(appointment.effective_from),
                                        to:
                                            date(appointment.effective_to) ??
                                            t(
                                                'admin:organization.appointments.open_end',
                                            ),
                                    })}
                                </span>
                                <span className="text-sm text-muted-foreground">
                                    {t('admin:organization.appointments.decree', {
                                        ref: appointment.decree_ref ?? none,
                                    })}
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <section
                aria-labelledby="unit-requests"
                className={sectionClassName}
            >
                <h2
                    id="unit-requests"
                    className="mb-4 text-lg font-semibold text-foreground"
                >
                    {t('admin:organization.requests.unit_section')}
                </h2>
                <RequestList
                    requests={detail.requests}
                    empty={t('admin:organization.requests.unit_empty')}
                />
            </section>
        </div>
    );
}

function OpenRequests({ requests }: { requests: RequestSummary[] }) {
    const { t } = useTranslation();

    return (
        <section
            aria-labelledby="open-requests"
            className={cn(sectionClassName, 'flex flex-col gap-4')}
        >
            <div className="grid gap-1">
                <h2
                    id="open-requests"
                    className="text-lg font-semibold text-foreground"
                >
                    {t('admin:organization.requests.open_section')}
                </h2>
                <p className="text-sm text-muted-foreground">
                    {t('admin:organization.choose_unit')}
                </p>
            </div>
            <RequestList
                requests={requests}
                empty={t('admin:organization.requests.open_empty')}
                showUnit
            />
        </section>
    );
}

function RequestList({
    requests,
    empty,
    showUnit = false,
}: {
    requests: RequestSummary[];
    empty: string;
    showUnit?: boolean;
}) {
    const { t } = useTranslation();
    const date = useDate();

    if (requests.length === 0) {
        return <p className="text-sm text-muted-foreground">{empty}</p>;
    }

    return (
        <ul className="grid gap-3">
            {requests.map((request) => (
                <li key={request.public_id}>
                    <Link
                        href={ChangeRequestController.show(request.public_id)}
                        className="flex flex-col gap-2 rounded-md border border-border p-3 hover:bg-accent"
                    >
                        <span className="flex flex-wrap items-center justify-between gap-2">
                            <span className="font-medium text-foreground">
                                {t(`admin:organization.kinds.${request.kind}`)}
                                {request.label && ` · ${request.label}`}
                            </span>
                            <span className="flex flex-wrap gap-2">
                                {request.is_simulation && <SimulationBadge />}
                                <RequestStatusBadge status={request.status} />
                            </span>
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {showUnit && request.unit && `${request.unit} · `}
                            {t('admin:organization.requests.effective', {
                                date: date(
                                    request.effective_from ??
                                        request.proposed_effective_from,
                                ),
                            })}
                        </span>
                    </Link>
                </li>
            ))}
        </ul>
    );
}

OrganizationIndex.layout = {
    breadcrumbs: [
        {
            title: 'admin:organization.title',
            href: OrganizationController.index(),
        },
    ],
};
