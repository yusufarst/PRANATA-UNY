<?php

use App\Models\User;
use App\Modules\Access\Actions\ApproveGrant;
use App\Modules\Access\Actions\GrantAccess;
use App\Modules\Access\Actions\RevokeGrant;
use App\Modules\Access\Models\AccessGrant;
use App\Modules\Access\Models\AccessProfile;
use App\Modules\Access\Queries\VisibleTo;
use App\Modules\Access\Support\AccessEvaluator;
use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\ScopeType;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessProfileSeeder;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\Support\DatabaseSession;

/*
 * The access model of TASK-014: the capability catalog, the profiles of ACCESS_CONTROL section 9, scoped grants with the
 * maker-checker of SOD-05, the evaluator behind every Gate ability and the visibleTo condition of the lists (TAC-02 to TAC-07).
 * The scope mechanism is proven on a probe table here; the real packages and assets prove it again in TASK-027 and TASK-059.
 */

beforeEach(function () {
    $this->seed([OrganizationSeeder::class, AccessProfileSeeder::class]);
});

function accessUnit(string $code): int
{
    return (int) OrgUnit::query()->where('code', $code)->value('id');
}

function accessProfile(string $code): AccessProfile
{
    return AccessProfile::query()->where('code', $code)->firstOrFail();
}

function accessGranter(): User
{
    return User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create();
}

/**
 * Give the user a profile or a capability through the Action, as PG-503 does.
 */
function accessGrant(
    User $actor,
    User $user,
    ?string $profile,
    ?string $capability,
    ScopeType $scope,
    ?int $scopeId = null,
    ?string $scopeKey = null,
    ?CarbonImmutable $effectiveTo = null,
): AccessGrant {
    return app(GrantAccess::class)->handle(
        $actor,
        $user,
        $profile === null ? null : accessProfile($profile),
        $capability,
        $scope,
        $scopeId,
        $scopeKey,
        BusinessDate::today(),
        $effectiveTo,
        'Penugasan staf sesuai surat tugas',
    );
}

/**
 * The ids of the probe rows the user sees with the capability, through visibleTo.
 *
 * @return list<int>
 */
function visibleProbeIds(User $user, string $capability): array
{
    return VisibleTo::apply(DB::table('access_probe'), $user, $capability, ['unit' => 'unit_id', 'warehouse' => 'warehouse_id'])
        ->orderBy('id')
        ->pluck('id')
        ->map(fn (mixed $id): int => (int) $id)
        ->all();
}

function accessDomainReason(Closure $command): ?string
{
    try {
        $command();
    } catch (DomainRuleViolation $violation) {
        return $violation->reasonKey;
    }

    return null;
}

describe('the capability catalog', function () {
    test('every capability names a module and only scopes of the catalog letters', function () {
        foreach (CapabilityCatalog::all() as $capability) {
            expect($capability)->toMatch('/^[a-z][a-z_]*(\.[a-z][a-z_]*)+$/');

            foreach (CapabilityCatalog::scopes($capability) as $scope) {
                expect($scope)->toBeInstanceOf(ScopeType::class);
            }
        }

        expect(CapabilityCatalog::scopes('asset.view'))->toBe([ScopeType::OrgUnitSubtree, ScopeType::Global])
            ->and(CapabilityCatalog::isGrantable('procurement.method.determine'))->toBeFalse()
            ->and(CapabilityCatalog::exists('procurement.unknown'))->toBeFalse();
    });

    test('the initial profiles of section 9 list only grantable capabilities, and only the admin profile is privileged', function () {
        $profiles = AccessProfile::query()->orderBy('code')->get();

        expect($profiles->pluck('code')->all())->toEqualCanonicalizing(array_keys(AccessProfileSeeder::PROFILES));

        foreach ($profiles as $profile) {
            expect($profile->capabilities())->toEqualCanonicalizing(AccessProfileSeeder::PROFILES[$profile->code][2])
                ->and($profile->is_privileged)->toBe($profile->code === 'ACCESS_ADMIN');

            foreach ($profile->capabilities() as $capability) {
                expect(CapabilityCatalog::isGrantable($capability))->toBeTrue();
            }
        }
    });

    test('seeding again keeps a profile that a Super Admin changed', function () {
        accessProfile('PROCUREMENT_STAFF')->forceFill(['name' => 'Staf Pengadaan (diubah)'])->save();

        $this->seed(AccessProfileSeeder::class);

        expect(accessProfile('PROCUREMENT_STAFF')->name)->toBe('Staf Pengadaan (diubah)')
            ->and(AccessProfile::query()->count())->toBe(count(AccessProfileSeeder::PROFILES));
    });
});

describe('the evaluator', function () {
    test('the matrix of profile, capability and scope follows the catalog', function () {
        $admin = accessGranter();
        $evaluator = app(AccessEvaluator::class);
        $unit = accessUnit('FT');

        foreach (AccessProfileSeeder::PROFILES as $code => [, , $capabilities]) {
            if ($code === 'ACCESS_ADMIN') {
                continue;
            }

            foreach ([ScopeType::Global, ScopeType::OrgUnit, ScopeType::OrgUnitSubtree] as $scope) {
                $given = array_values(array_filter($capabilities, fn (string $each): bool => CapabilityCatalog::allows($each, $scope)));

                if ($given === []) {
                    continue;
                }

                $user = User::factory()->create();
                $grant = accessGrant($admin, $user, $code, null, $scope, $scope->needsId() ? $unit : null);
                $subject = AccessSubject::forUnit($unit);

                if ($grant->status === AccessGrant::PENDING_APPROVAL) {
                    app(ApproveGrant::class)->handle(accessGranter(), $grant, $grant->lock_version);
                }

                foreach (CapabilityCatalog::all() as $capability) {
                    expect($evaluator->allows($user, $capability, $subject))
                        ->toBe(in_array($capability, $given, true), "{$code} {$scope->value} {$capability}");
                }
            }
        }
    });

    test('a subtree grant covers the unit and those below it but not a sibling (TAC-02)', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, null, 'asset.view', ScopeType::OrgUnitSubtree, accessUnit('FT'));

        $evaluator = app(AccessEvaluator::class);

        expect($evaluator->allows($user, 'asset.view', AccessSubject::forUnit(accessUnit('FT'))))->toBeTrue()
            ->and($evaluator->allows($user, 'asset.view', AccessSubject::forUnit(accessUnit('FT.TI'))))->toBeTrue()
            ->and($evaluator->decide($user, 'asset.view', AccessSubject::forUnit(accessUnit('LPPM')))->reasonKey)->toBe('access.denied.out_of_scope')
            ->and($evaluator->allows($user, 'asset.view', AccessSubject::forUnit(accessUnit('UNIV'))))->toBeFalse()
            ->and($evaluator->allows($user, 'asset.view'))->toBeTrue()
            ->and($evaluator->decide($user, 'procurement.package.view')->reasonKey)->toBe('access.denied.no_grant');
    });

    test('a unit grant covers its own unit only', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, 'UNIT_NEED_OPERATOR', null, ScopeType::OrgUnit, accessUnit('FT'));

        $evaluator = app(AccessEvaluator::class);

        expect($evaluator->allows($user, 'procurement.need.create', AccessSubject::forUnit(accessUnit('FT'))))->toBeTrue()
            ->and($evaluator->allows($user, 'procurement.need.create', AccessSubject::forUnit(accessUnit('FT.TI'))))->toBeFalse();
    });

    test('a policy family grant covers its own family only', function () {
        $user = User::factory()->create();
        $grant = accessGrant(accessGranter(), $user, null, 'admin.policy.activate', ScopeType::PolicyFamily, null, 'asset.depreciation');
        app(ApproveGrant::class)->handle(accessGranter(), $grant, $grant->lock_version);

        $evaluator = app(AccessEvaluator::class);

        expect($evaluator->allows($user, 'admin.policy.activate', AccessSubject::forPolicyFamily('asset.depreciation')))->toBeTrue()
            ->and($evaluator->allows($user, 'admin.policy.activate', AccessSubject::forPolicyFamily('procurement.authority')))->toBeFalse();
    });

    test('a Super Admin holds the global administration but no business capability, even with a grant row (INV-021)', function () {
        $superAdmin = accessGranter();
        $evaluator = app(AccessEvaluator::class);

        expect($evaluator->allows($superAdmin, 'admin.users.manage'))->toBeTrue()
            ->and($evaluator->allows($superAdmin, 'admin.org.manage'))->toBeTrue()
            ->and($evaluator->decide($superAdmin, 'admin.policy.activate')->reasonKey)->toBe('access.denied.no_grant')
            ->and($evaluator->decide($superAdmin, 'asset.view')->reasonKey)->toBe('access.denied.role_group')
            ->and(accessDomainReason(fn () => accessGrant(accessGranter(), $superAdmin, null, 'asset.view', ScopeType::Global)))
            ->toBe('access.role_group_not_allowed');

        DB::table('access_grants')->insert([
            'public_id' => strtolower((string) Str::ulid()),
            'user_id' => $superAdmin->id,
            'capability' => 'asset.view',
            'scope_type' => 'GLOBAL',
            'requires_approval' => false,
            'effective_from' => BusinessDate::today()->toDateString(),
            'reason' => 'Baris yang tidak boleh berlaku',
            'granted_by' => accessGranter()->id,
            'status' => 'ACTIVE',
        ]);

        expect($evaluator->allows($superAdmin, 'asset.view'))->toBeFalse();
    });

    test('an inactive account and a vendor hold nothing, and an assignment capability needs an assignment', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, null, 'asset.view', ScopeType::Global);
        $evaluator = app(AccessEvaluator::class);

        $user->forceFill(['status' => User::SUSPENDED])->save();

        expect($evaluator->decide($user, 'asset.view')->reasonKey)->toBe('access.denied.inactive_account')
            ->and($evaluator->decide(User::factory()->inRoleGroup(User::VENDOR)->create(), 'asset.view')->reasonKey)->toBe('access.denied.role_group')
            ->and($evaluator->decide(User::factory()->create(), 'procurement.method.determine')->reasonKey)->toBe('access.denied.assignment_required')
            ->and($evaluator->decide(User::factory()->create(), 'asset.unknown')->reasonKey)->toBe('access.denied.unknown_capability');
    });

    test('every capability is a Gate ability decided by the evaluator', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, null, 'asset.view', ScopeType::OrgUnitSubtree, accessUnit('FT'));

        expect(Gate::forUser($user)->allows('asset.view', AccessSubject::forUnit(accessUnit('FT.TS'))))->toBeTrue()
            ->and(Gate::forUser($user)->allows('asset.view', AccessSubject::forUnit(accessUnit('BUK'))))->toBeFalse()
            ->and($user->can('admin.users.manage'))->toBeFalse()
            ->and(accessGranter()->can('admin.users.manage'))->toBeTrue()
            ->and(fn () => Gate::forUser($user)->allows('asset.view', new stdClass))->toThrow(InvalidArgumentException::class);
    });
});

describe('granting', function () {
    test('nobody grants to themselves (AUTHZ-20)', function () {
        $admin = accessGranter();

        expect(accessDomainReason(fn () => accessGrant($admin, $admin, null, 'admin.audit.read', ScopeType::Global)))
            ->toBe('access.cannot_grant_self')
            ->and(AccessGrant::query()->count())->toBe(0);
    });

    test('a privileged grant waits for a second person and then takes effect (TAC-03)', function () {
        $granter = accessGranter();
        $user = User::factory()->create();
        $grant = accessGrant($granter, $user, 'ACCESS_ADMIN', null, ScopeType::Global);

        expect($grant->status)->toBe(AccessGrant::PENDING_APPROVAL)
            ->and($grant->requires_approval)->toBeTrue()
            ->and(app(AccessEvaluator::class)->allows($user, 'admin.access.grant'))->toBeFalse()
            ->and(accessDomainReason(fn () => app(ApproveGrant::class)->handle($granter, $grant, $grant->lock_version)))
            ->toBe('access.cannot_approve_own_grant')
            ->and(accessDomainReason(fn () => app(ApproveGrant::class)->handle($user, $grant, $grant->lock_version)))
            ->toBe('access.cannot_grant_self');

        $approver = accessGranter();
        app(ApproveGrant::class)->handle($approver, $grant, $grant->lock_version);

        expect($grant->refresh()->status)->toBe(AccessGrant::ACTIVE)
            ->and($grant->approved_by)->toBe($approver->id)
            ->and(app(AccessEvaluator::class)->allows($user, 'admin.access.grant'))->toBeTrue()
            ->and(app(PrivilegedAccounts::class)->isPrivileged($user))->toBeTrue()
            ->and(accessDomainReason(fn () => app(ApproveGrant::class)->handle($approver, $grant, $grant->lock_version)))
            ->toBe('access.grant_not_pending');
    });

    test('a decision capability is privileged only in the global scope', function () {
        $admin = accessGranter();

        expect(accessGrant($admin, User::factory()->create(), null, 'intake.batch.decide', ScopeType::Global)->status)
            ->toBe(AccessGrant::PENDING_APPROVAL)
            ->and(accessGrant($admin, User::factory()->create(), null, 'asset.mutation.decide', ScopeType::OrgUnitSubtree, accessUnit('FT'))->status)
            ->toBe(AccessGrant::ACTIVE)
            ->and(accessGrant($admin, User::factory()->create(), 'PROCUREMENT_STAFF', null, ScopeType::Global)->status)
            ->toBe(AccessGrant::ACTIVE);
    });

    test('a grant refuses a scope the catalog does not allow, an unknown scope target and a deactivated account', function () {
        $admin = accessGranter();
        $user = User::factory()->create();

        expect(accessDomainReason(fn () => accessGrant($admin, $user, null, 'asset.view', ScopeType::OrgUnit, accessUnit('FT'))))
            ->toBe('access.scope_not_allowed')
            ->and(accessDomainReason(fn () => accessGrant($admin, $user, null, 'asset.view', ScopeType::OrgUnitSubtree, 999_999)))
            ->toBe('access.scope_unknown')
            ->and(accessDomainReason(fn () => accessGrant($admin, $user, null, 'inventory.view', ScopeType::Warehouse, 1)))
            ->toBe('access.scope_unknown')
            ->and(accessDomainReason(fn () => accessGrant($admin, $user, null, 'admin.policy.author', ScopeType::PolicyFamily, null, 'Bukan Kunci')))
            ->toBe('access.scope_unknown')
            ->and(accessDomainReason(fn () => accessGrant($admin, $user, null, 'procurement.method.determine', ScopeType::Global)))
            ->toBe('access.scope_not_allowed')
            ->and(accessDomainReason(fn () => accessGrant($admin, User::factory()->withStatus(User::DEACTIVATED)->create(), null, 'asset.view', ScopeType::Global)))
            ->toBe('access.account_deactivated')
            ->and(AccessGrant::query()->count())->toBe(0);
    });

    test('the database refuses an active privileged grant without a second approver (TAC-06)', function () {
        $granter = accessGranter();
        $user = User::factory()->create();
        $row = [
            'public_id' => strtolower((string) Str::ulid()),
            'user_id' => $user->id,
            'capability' => 'admin.audit.read',
            'scope_type' => 'GLOBAL',
            'requires_approval' => true,
            'effective_from' => BusinessDate::today()->toDateString(),
            'reason' => 'Uji constraint',
            'granted_by' => $granter->id,
            'status' => 'ACTIVE',
        ];

        expect(DatabaseSession::sqlStateAs(null, fn () => DB::table('access_grants')->insert($row)))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('access_grants')->insert([...$row, 'approved_by' => $granter->id])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('access_grants')->insert([...$row, 'approved_by' => accessGranter()->id])))->toBeNull()
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('access_grants')->insert([...$row, 'profile_id' => accessProfile('ACCESS_ADMIN')->id, 'approved_by' => accessGranter()->id])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('access_grants')->insert([...$row, 'scope_type' => 'POLICY_FAMILY', 'approved_by' => accessGranter()->id])))->toBe('23514');
    });
});

describe('revoking and expiry', function () {
    test('a revoked grant no longer applies on the next request (TAC-04, AUTHZ-22)', function () {
        $user = User::factory()->create();
        $grant = accessGrant(accessGranter(), $user, null, 'asset.view', ScopeType::Global);

        expect($user->can('asset.view'))->toBeTrue();

        app(RevokeGrant::class)->handle(accessGranter(), $grant, 'Pindah tugas', $grant->lock_version);

        expect($grant->refresh()->status)->toBe(AccessGrant::REVOKED)
            ->and($user->can('asset.view'))->toBeFalse()
            ->and(accessDomainReason(fn () => app(RevokeGrant::class)->handle(accessGranter(), $grant, 'Lagi', $grant->lock_version)))
            ->toBe('access.grant_not_revocable');
    });

    test('a grant applies from its first day until the day before its end (AUTHZ-22)', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, null, 'asset.view', ScopeType::Global, effectiveTo: BusinessDate::today()->addDays(2));

        expect($user->can('asset.view'))->toBeTrue();

        $this->travel(1)->days();
        expect($user->can('asset.view'))->toBeTrue();

        $this->travel(1)->days();
        expect($user->can('asset.view'))->toBeFalse();
    });

    test('an inactive profile gives nothing', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, 'PROCUREMENT_STAFF', null, ScopeType::Global);

        expect($user->can('procurement.package.view'))->toBeTrue();

        accessProfile('PROCUREMENT_STAFF')->forceFill(['status' => AccessProfile::INACTIVE])->save();

        expect($user->can('procurement.package.view'))->toBeFalse();
    });
});

describe('visibleTo', function () {
    beforeEach(function () {
        DB::statement('create temporary table access_probe (id bigint primary key, unit_id bigint, warehouse_id bigint, policy_family varchar(80))');
        DB::table('access_probe')->insert([
            ['id' => 1, 'unit_id' => accessUnit('FT'), 'warehouse_id' => null, 'policy_family' => null],
            ['id' => 2, 'unit_id' => accessUnit('FT.TI'), 'warehouse_id' => null, 'policy_family' => null],
            ['id' => 3, 'unit_id' => accessUnit('LPPM'), 'warehouse_id' => null, 'policy_family' => null],
            ['id' => 4, 'unit_id' => accessUnit('BUK.PBJ'), 'warehouse_id' => null, 'policy_family' => null],
        ]);
    });

    test('the list sees the same rows as the detail check (TAC-02, TAC-07)', function () {
        $user = User::factory()->create();
        $admin = accessGranter();
        accessGrant($admin, $user, null, 'asset.view', ScopeType::OrgUnitSubtree, accessUnit('FT'));
        accessGrant($admin, $user, null, 'asset.view', ScopeType::OrgUnitSubtree, accessUnit('BUK.PBJ'));

        expect(visibleProbeIds($user, 'asset.view'))->toBe([1, 2, 4]);

        foreach (DB::table('access_probe')->get() as $row) {
            expect($user->can('asset.view', AccessSubject::forUnit((int) $row->unit_id)))
                ->toBe(in_array((int) $row->id, [1, 2, 4], true));
        }
    });

    test('a unit grant sees its own unit, a global grant sees every row, and no grant sees nothing', function () {
        $admin = accessGranter();
        $unitUser = User::factory()->create();
        $globalUser = User::factory()->create();
        accessGrant($admin, $unitUser, null, 'procurement.need.create', ScopeType::OrgUnit, accessUnit('FT'));
        accessGrant($admin, $globalUser, null, 'asset.view', ScopeType::Global);

        expect(visibleProbeIds($unitUser, 'procurement.need.create'))->toBe([1])
            ->and(visibleProbeIds($globalUser, 'asset.view'))->toBe([1, 2, 3, 4])
            ->and(visibleProbeIds($globalUser, 'procurement.package.view'))->toBe([])
            ->and(visibleProbeIds(User::factory()->create(), 'asset.view'))->toBe([]);
    });

    test('a scope without its column shows nothing (fail closed)', function () {
        $user = User::factory()->create();
        accessGrant(accessGranter(), $user, null, 'asset.view', ScopeType::OrgUnitSubtree, accessUnit('FT'));

        expect(VisibleTo::apply(DB::table('access_probe'), $user, 'asset.view', ['warehouse' => 'warehouse_id'])->count())->toBe(0);
    });
});
