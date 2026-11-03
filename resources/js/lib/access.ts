import type { StatusCategory } from '@/components/app/status-badge';

// The access model as the pages of PG-503 see it. The server decides every grant; these only mirror it (ACCESS_CONTROL section 8).

export const grantStatuses = [
    'PENDING_APPROVAL',
    'ACTIVE',
    'REVOKED',
    'EXPIRED',
] as const;

export type GrantStatus = (typeof grantStatuses)[number];

export const scopeTypes = [
    'GLOBAL',
    'ORG_UNIT',
    'ORG_UNIT_SUBTREE',
    'WAREHOUSE',
    'WORKSPACE',
    'POLICY_FAMILY',
] as const;

export type ScopeType = (typeof scopeTypes)[number];

export const grantStatusCategory: Record<GrantStatus, StatusCategory> = {
    PENDING_APPROVAL: 'waiting',
    ACTIVE: 'completed',
    REVOKED: 'rejected',
    EXPIRED: 'blocked',
};

export type GrantSummary = {
    public_id: string;
    user: { public_id: string; username: string; name: string };
    profile: { code: string; name: string } | null;
    capability: string | null;
    scope_type: ScopeType;
    scope_unit: { public_id: string; code: string; name: string } | null;
    scope_id: number | null;
    scope_key: string | null;
    requires_approval: boolean;
    status: GrantStatus;
    effective_from: string;
    effective_to: string | null;
    created_at: string | null;
};

/** Whether a scope names a unit, chosen from the organization tree. */
export function scopeNeedsUnit(scope: string): boolean {
    return scope === 'ORG_UNIT' || scope === 'ORG_UNIT_SUBTREE';
}

/** The target of a scope, to show after its kind: the unit, the warehouse or the policy family. */
export function scopeTarget(grant: GrantSummary): string | null {
    if (grant.scope_unit !== null) {
        return `${grant.scope_unit.name} (${grant.scope_unit.code})`;
    }

    if (grant.scope_key !== null) {
        return grant.scope_key;
    }

    return grant.scope_id === null ? null : `#${grant.scope_id}`;
}

/** What the grant gives: the profile name, or the capability code itself. */
export function grantSubject(grant: GrantSummary): string {
    return grant.profile?.name ?? grant.capability ?? '';
}
