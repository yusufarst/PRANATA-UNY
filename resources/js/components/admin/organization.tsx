import { useTranslation } from 'react-i18next';
import type { StatusCategory } from '@/components/app/status-badge';
import { StatusBadge } from '@/components/app/status-badge';

/** The kinds of a WF-029 change request: F1 the unit structure, F2 positions and appointments. */
export const unitKinds = [
    'CREATE_UNIT',
    'UPDATE_UNIT',
    'MOVE_UNIT',
    'DEACTIVATE_UNIT',
    'ACTIVATE_UNIT',
] as const;

export const staffKinds = [
    'CREATE_POSITION',
    'APPOINT',
    'END_APPOINTMENT',
] as const;

export type ChangeKind =
    (typeof unitKinds)[number] | (typeof staffKinds)[number];

export type RequestStatus =
    | 'DRAFT'
    | 'VALIDATED'
    | 'AWAITING_DECISION'
    | 'APPROVED'
    | 'EFFECTIVE'
    | 'SUPERSEDED'
    | 'WITHDRAWN'
    | 'REJECTED';

export type UnitStatus = 'ACTIVE' | 'INACTIVE';

export type UnitSummary = {
    public_id: string;
    code: string;
    name: string;
    short_name: string | null;
    unit_type: string;
    parent: string | null;
    parent_name: string | null;
    status: UnitStatus;
};

export type RequestSummary = {
    public_id: string;
    family: 'F1' | 'F2';
    kind: ChangeKind;
    status: RequestStatus;
    unit: string | null;
    label: string | null;
    proposed_effective_from: string;
    effective_from: string | null;
    is_simulation: boolean;
    created_at: string | null;
};

export type Option = { value: string; label: string };

const requestCategory: Record<RequestStatus, StatusCategory> = {
    DRAFT: 'draft',
    VALIDATED: 'action',
    AWAITING_DECISION: 'waiting',
    APPROVED: 'approved',
    EFFECTIVE: 'completed',
    SUPERSEDED: 'draft',
    WITHDRAWN: 'rejected',
    REJECTED: 'rejected',
};

// DESIGN_SYSTEM section 8: text, icon and color together, with the WF-029 state as the text.
export function RequestStatusBadge({ status }: { status: RequestStatus }) {
    const { t } = useTranslation();

    return (
        <StatusBadge
            category={requestCategory[status]}
            label={t(`admin:organization.request_status.${status}`)}
        />
    );
}

export function UnitStatusBadge({ status }: { status: UnitStatus }) {
    const { t } = useTranslation();

    return (
        <StatusBadge
            category={status === 'ACTIVE' ? 'completed' : 'rejected'}
            label={t(`admin:organization.unit_status.${status}`)}
        />
    );
}

/** A decision recorded without an official policy version (TASK-013 TAC-04) is marked as a simulation everywhere. */
export function SimulationBadge() {
    const { t } = useTranslation();

    return (
        <StatusBadge
            category="simulation"
            label={t('admin:organization.simulation')}
        />
    );
}

/** A calendar date from the server (Y-m-d) in the language of the page. */
export function useDate(): (value: string | null) => string | null {
    const { i18n } = useTranslation();
    const format = new Intl.DateTimeFormat(
        i18n.language === 'en' ? 'en-GB' : 'id-ID',
        { dateStyle: 'medium', timeZone: 'UTC' },
    );

    return (value) =>
        value === null ? null : format.format(new Date(`${value}T00:00:00Z`));
}

/** Today in the browser as Y-m-d, the default of a date the user still checks. */
export function localToday(): string {
    const now = new Date();

    return [
        now.getFullYear(),
        String(now.getMonth() + 1).padStart(2, '0'),
        String(now.getDate()).padStart(2, '0'),
    ].join('-');
}

export const textareaClassName =
    'w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm';
