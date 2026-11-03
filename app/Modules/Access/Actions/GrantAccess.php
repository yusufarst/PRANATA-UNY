<?php

namespace App\Modules\Access\Actions;

use App\Models\User;
use App\Modules\Access\Events\GrantGiven;
use App\Modules\Access\Models\AccessGrant;
use App\Modules\Access\Models\AccessProfile;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\ScopeType;
use App\Modules\Organization\Queries\UnitScope;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Give a user one profile or one capability in one scope (PG-503). The caller holds admin.access.grant.
 *
 * Nobody grants to themselves (AUTHZ-20). A privileged grant, of an admin.* capability or of a decision in the GLOBAL scope, or of
 * a privileged profile, waits in PENDING_APPROVAL for a second person (TAC-03); any other grant is ACTIVE at once. A role group
 * never receives what it may not hold, so a SUPER_ADMIN never receives a business capability (INV-021).
 */
final class GrantAccess extends Action
{
    public const POLICY_FAMILY_PATTERN = '/^[a-z][a-z_]*(\.[a-z][a-z_]*)*\z/';

    public function handle(
        User $actor,
        User $user,
        ?AccessProfile $profile,
        ?string $capability,
        ScopeType $scope,
        ?int $scopeId,
        ?string $scopeKey,
        CarbonImmutable $effectiveFrom,
        ?CarbonImmutable $effectiveTo,
        string $reason,
    ): AccessGrant {
        if (($profile === null) === ($capability === null)) {
            throw new InvalidArgumentException('A grant gives either one profile or one capability.');
        }

        return $this->transaction(function () use ($actor, $user, $profile, $capability, $scope, $scopeId, $scopeKey, $effectiveFrom, $effectiveTo, $reason): AccessGrant {
            if ($user->is($actor)) {
                throw DomainRuleViolation::because('access.cannot_grant_self');
            }

            if ($user->refresh()->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('access.account_deactivated');
            }

            $capabilities = $profile === null ? [(string) $capability] : $this->profileCapabilities($profile->refresh());
            $given = array_values(array_filter($capabilities, fn (string $each): bool => CapabilityCatalog::allows($each, $scope)));

            if ($given === []) {
                throw DomainRuleViolation::because('access.scope_not_allowed');
            }

            foreach ($capabilities as $each) {
                if (! CapabilityCatalog::roleGroupAllows($user->role_group, $each)) {
                    throw DomainRuleViolation::because('access.role_group_not_allowed', ['capability' => $each]);
                }
            }

            $this->assertScopeTarget($scope, $scopeId, $scopeKey);

            if ($effectiveFrom->lt(BusinessDate::today())) {
                throw DomainRuleViolation::because('access.effective_from_in_past');
            }

            if ($effectiveTo !== null && $effectiveTo->lte($effectiveFrom)) {
                throw DomainRuleViolation::because('access.effective_to_before_start');
            }

            $privileged = ($profile !== null && $profile->is_privileged)
                || array_filter($given, fn (string $each): bool => CapabilityCatalog::isPrivileged($each, $scope)) !== [];

            $grant = AccessGrant::query()->create([
                'user_id' => $user->id,
                'profile_id' => $profile?->id,
                'capability' => $capability,
                'scope_type' => $scope,
                'scope_id' => $scope->needsId() ? $scopeId : null,
                'scope_key' => $scope === ScopeType::PolicyFamily ? $scopeKey : null,
                'requires_approval' => $privileged,
                'effective_from' => $effectiveFrom->toDateString(),
                'effective_to' => $effectiveTo?->toDateString(),
                'reason' => $reason,
                'granted_by' => $actor->id,
                'status' => $privileged ? AccessGrant::PENDING_APPROVAL : AccessGrant::ACTIVE,
            ]);

            // Inside the transaction, so the grant and its audit record commit or roll back together (fail closed).
            GrantGiven::dispatch($grant);

            return $grant;
        });
    }

    /**
     * @return list<string>
     */
    private function profileCapabilities(AccessProfile $profile): array
    {
        if ($profile->status !== AccessProfile::ACTIVE) {
            throw DomainRuleViolation::because('access.profile_inactive');
        }

        return $profile->capabilities();
    }

    private function assertScopeTarget(ScopeType $scope, ?int $scopeId, ?string $scopeKey): void
    {
        $known = match ($scope) {
            ScopeType::Global, ScopeType::Workspace => true,
            ScopeType::OrgUnit, ScopeType::OrgUnitSubtree => $scopeId !== null && UnitScope::isActive($scopeId),
            // The warehouses arrive with the Inventory module, which then names them here; until then none can be checked.
            ScopeType::Warehouse => false,
            ScopeType::PolicyFamily => $scopeKey !== null && preg_match(self::POLICY_FAMILY_PATTERN, $scopeKey) === 1,
        };

        if (! $known) {
            throw DomainRuleViolation::because('access.scope_unknown');
        }
    }
}
