<?php

namespace App\Modules\Access\Support;

use App\Models\User;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Access\Support\Separation\SeparationOfDuties;
use App\Modules\Identity\Support\TwoFactorPolicy;
use App\Modules\Organization\Queries\UnitScope;
use App\Modules\Organization\Support\BusinessDate;
use Illuminate\Support\Facades\DB;

/**
 * can(actor, capability, subject?) of ACCESS_CONTROL section 1, layers 1 to 4: an active account, a role group that may hold
 * the capability, an ACTIVE grant in force today whose scope covers the subject or a process assignment in force on it
 * (TASK-015), and the separation of duties on the subject. The workflow and policy guards are later layers of the modules
 * that own them.
 *
 * Every check reads the grants again: nothing is cached across requests, so a revoked or expired grant stops at the next
 * request (AUTHZ-22, TAC-04). Without a subject the question is whether the actor holds the capability anywhere, as a menu
 * asks; a check on an object passes the object.
 */
final class AccessEvaluator
{
    public function __construct(
        private readonly SeparationOfDuties $separation,
        private readonly TwoFactorPolicy $twoFactor,
    ) {}

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
            $decision = AccessDecision::allow();
        } else {
            $byGrant = $this->decideByGrant($user, $capability, $subject);
            $decision = $byGrant->allowed ? $byGrant : $this->decideByAssignment($user, $capability, $subject, $byGrant);
        }

        // The holder of a decision role waits for their credentials on every decision, also one a grant allows (AUTHZ-24).
        if ($decision->allowed && CapabilityCatalog::isDecision($capability) && $this->decisionsWait($user) && self::holdsDecisionRole($user)) {
            return AccessDecision::deny('access.denied.credentials_pending');
        }

        if (! $decision->allowed || $subject === null) {
            return $decision;
        }

        // Layer 4: the separation of duties holds at the moment of the action too, not only when someone was assigned.
        if (! $this->separation->forAction($user, $capability, $subject)->passed()) {
            return AccessDecision::deny('access.denied.separation_of_duties');
        }

        return $decision;
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

    /**
     * Layer 3 by a grant: an ACTIVE grant in force today whose scope covers the subject.
     */
    private function decideByGrant(User $user, string $capability, ?AccessSubject $subject): AccessDecision
    {
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

    /**
     * Layer 3 by a process assignment: a capability marked "Penugasan" holds on an object whose assignment of a role that
     * gives it is in force for the user (ACCESS_CONTROL sections 4 and 5, TAC-03). Without a subject the question is whether
     * the user holds such an assignment anywhere. When no assignment gives it, the refusal of the grant stands, except that a
     * capability only an assignment gives is refused on an object for holding no assignment there.
     *
     * A decision role waits after an assisted reset until the holder changed the password, and while TOTP is required but not
     * set up (AUTHZ-24, SEC-21), unless a role held there that decides nothing gives the capability by itself.
     */
    private function decideByAssignment(User $user, string $capability, ?AccessSubject $subject, AccessDecision $byGrant): AccessDecision
    {
        $roles = AssignmentRoles::givingCapability($capability);

        if ($roles === [] || ($subject !== null && ! $subject->hasObject())) {
            return $byGrant;
        }

        $query = ProcessAssignment::query()->inForce()->where('user_id', $user->id)->whereIn('assignment_role', $roles);

        if ($subject !== null) {
            $query->onObject((string) $subject->objectType, (int) $subject->objectId);
        }

        $held = array_map('strval', $query->pluck('assignment_role')->all());

        if ($held === []) {
            return CapabilityCatalog::isGrantable($capability) || $subject === null
                ? $byGrant
                : AccessDecision::deny('access.denied.no_assignment');
        }

        $deciding = array_filter($held, AssignmentRoles::isDecision(...));

        if (count($deciding) === count($held) && $this->decisionsWait($user)) {
            return AccessDecision::deny('access.denied.credentials_pending');
        }

        return AccessDecision::allow();
    }

    /**
     * Whether the user holds a decision role that has not ended, in force or waiting for its first day: a privileged account
     * for TOTP and an assisted reset (SEC-21, AUTHZ-24).
     */
    public static function holdsDecisionRole(User $user): bool
    {
        return ProcessAssignment::query()
            ->current()
            ->where('user_id', $user->id)
            ->whereIn('assignment_role', AssignmentRoles::decisionRoles())
            ->exists();
    }

    private function decisionsWait(User $user): bool
    {
        return $user->must_change_password || $this->twoFactor->isMissingFor($user);
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
