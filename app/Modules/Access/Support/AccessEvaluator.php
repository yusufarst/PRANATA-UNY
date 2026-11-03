<?php

namespace App\Modules\Access\Support;

use App\Models\User;
use App\Modules\Organization\Queries\UnitScope;
use App\Modules\Organization\Support\BusinessDate;
use Illuminate\Support\Facades\DB;

/**
 * can(actor, capability, subject?) of ACCESS_CONTROL section 1, layers 1 to 3: an active account, a role group that may hold
 * the capability, and an ACTIVE grant in force today whose scope covers the subject. Process assignments (TASK-015), the
 * separation of duties and the workflow and policy guards are later layers of the modules that own them.
 *
 * Every check reads the grants again: nothing is cached across requests, so a revoked or expired grant stops at the next
 * request (AUTHZ-22, TAC-04). Without a subject the question is whether the actor holds the capability anywhere, as a menu
 * asks; a check on an object passes the object.
 */
final class AccessEvaluator
{
    public function decide(User $user, string $capability, ?AccessSubject $subject = null): AccessDecision
    {
        if (! $user->mayHoldSession()) {
            return AccessDecision::deny('access.denied.inactive_account');
        }

        if (! CapabilityCatalog::exists($capability)) {
            return AccessDecision::deny('access.denied.unknown_capability');
        }

        if (! CapabilityCatalog::roleGroupAllows($user->role_group, $capability)) {
            return AccessDecision::deny('access.denied.role_group');
        }

        if ($user->role_group === User::SUPER_ADMIN && CapabilityCatalog::heldBySuperAdmin($capability)) {
            return AccessDecision::allow();
        }

        if (! CapabilityCatalog::isGrantable($capability)) {
            return AccessDecision::deny('access.denied.assignment_required');
        }

        $scopes = $this->scopes($user, $capability);

        if ($scopes === []) {
            return AccessDecision::deny('access.denied.no_grant');
        }

        if ($subject === null) {
            return AccessDecision::allow();
        }

        foreach ($scopes as $scope) {
            if (self::covers($scope, $subject)) {
                return AccessDecision::allow();
            }
        }

        return AccessDecision::deny('access.denied.out_of_scope');
    }

    public function allows(User $user, string $capability, ?AccessSubject $subject = null): bool
    {
        return $this->decide($user, $capability, $subject)->allowed;
    }

    /**
     * Whether the user holds the capability everywhere: by role group, or by a GLOBAL or WORKSPACE grant.
     */
    public function holdsEverywhere(User $user, string $capability): bool
    {
        if (! $user->mayHoldSession() || ! CapabilityCatalog::roleGroupAllows($user->role_group, $capability)) {
            return false;
        }

        if ($user->role_group === User::SUPER_ADMIN && CapabilityCatalog::heldBySuperAdmin($capability)) {
            return true;
        }

        foreach ($this->scopes($user, $capability) as $scope) {
            if ($scope->type->coversEverything()) {
                return true;
            }
        }

        return false;
    }

    /**
     * The scopes in which the user holds the capability by a grant in force today, directly or through an active profile.
     * A profile grant gives each of its capabilities only in a scope the catalog allows for it.
     *
     * @return list<GrantScope>
     */
    public function scopes(User $user, string $capability): array
    {
        if (! $user->mayHoldSession() || ! CapabilityCatalog::roleGroupAllows($user->role_group, $capability)) {
            return [];
        }

        $today = BusinessDate::today()->toDateString();

        $rows = DB::table('access_grants as g')
            ->select('g.scope_type', 'g.scope_id', 'g.scope_key')
            ->where('g.user_id', $user->id)
            ->where('g.status', 'ACTIVE')
            ->where('g.effective_from', '<=', $today)
            ->where(fn ($query) => $query->whereNull('g.effective_to')->orWhere('g.effective_to', '>', $today))
            ->where(fn ($query) => $query
                ->where('g.capability', $capability)
                ->orWhereExists(fn ($profiles) => $profiles
                    ->from('access_profiles as p')
                    ->join('access_profile_capabilities as c', 'c.profile_id', '=', 'p.id')
                    ->whereColumn('p.id', 'g.profile_id')
                    ->where('p.status', 'ACTIVE')
                    ->where('c.capability', $capability)))
            ->get();

        $scopes = [];

        foreach ($rows as $row) {
            $type = ScopeType::from((string) $row->scope_type);

            if (CapabilityCatalog::allows($capability, $type)) {
                $scopes[] = new GrantScope(
                    $type,
                    $row->scope_id === null ? null : (int) $row->scope_id,
                    $row->scope_key === null ? null : (string) $row->scope_key,
                );
            }
        }

        return $scopes;
    }

    private static function covers(GrantScope $scope, AccessSubject $subject): bool
    {
        return match ($scope->type) {
            ScopeType::Global, ScopeType::Workspace => true,
            ScopeType::OrgUnit => $subject->unitId !== null && $subject->unitId === $scope->id,
            ScopeType::OrgUnitSubtree => $subject->unitId !== null && $scope->id !== null && UnitScope::isWithin($subject->unitId, $scope->id),
            ScopeType::Warehouse => $subject->warehouseId !== null && $subject->warehouseId === $scope->id,
            ScopeType::PolicyFamily => $subject->policyFamily !== null && $subject->policyFamily === $scope->key,
        };
    }
}
