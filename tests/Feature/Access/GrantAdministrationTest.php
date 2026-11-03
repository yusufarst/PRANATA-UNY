<?php

use App\Models\User;
use App\Modules\Access\Actions\ApproveGrant;
use App\Modules\Access\Actions\GrantAccess;
use App\Modules\Access\Models\AccessGrant;
use App\Modules\Access\Models\AccessProfile;
use App\Modules\Access\Support\ScopeType;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Support\BusinessDate;
use Database\Seeders\AccessProfileSeeder;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/*
 * PG-503 Akses (TASK-014, WF-002 TR-011 to TR-014): the list, detail and form of grants, the approval by a second person and
 * the revocation, and the menu and workspaces that follow the grants (TAC-01, TAC-03 to TAC-05).
 */

beforeEach(function () {
    $this->seed([OrganizationSeeder::class, AccessProfileSeeder::class]);
});

function grantAdmin(string $name = 'Admin Akses'): User
{
    // Administration needs confirmed TOTP of a privileged account (TASK-012 TAC-02).
    return User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create(['name' => $name]);
}

/**
 * @return array<string, string>
 */
function grantKey(): array
{
    return ['X-Idempotency-Key' => (string) Str::uuid()];
}

/**
 * An INTERNAL user who holds the capability globally, approved by a second Super Admin when it is privileged.
 */
function delegatedAdmin(string $capability): User
{
    $user = User::factory()->withTwoFactor()->create();
    $grant = app(GrantAccess::class)->handle(
        grantAdmin(),
        $user,
        null,
        $capability,
        ScopeType::Global,
        null,
        null,
        BusinessDate::today(),
        null,
        'Admin akses terdelegasi',
    );

    if ($grant->status === AccessGrant::PENDING_APPROVAL) {
        app(ApproveGrant::class)->handle(grantAdmin(), $grant, $grant->lock_version);
    }

    return $user;
}

/**
 * Whether the shared menu or workspaces hold exactly these keys, in this order.
 *
 * @param  list<string>  $keys
 */
function keysAre(array $keys): Closure
{
    return fn ($entries) => collect($entries)->pluck('key')->all() === $keys;
}

/**
 * TR-011 through PG-503.
 *
 * @param  array<string, mixed>  $data
 */
function postGrant(TestCase $test, array $data): TestResponse
{
    return $test->withHeaders(grantKey())->post(route('access.admin.grants.store'), [
        'target' => 'capability',
        'scope_type' => ScopeType::Global->value,
        'effective_from' => BusinessDate::today()->toDateString(),
        'reason' => 'Penugasan staf sesuai surat tugas',
        ...$data,
    ]);
}

/**
 * @param  array<string, mixed>  $data
 */
function grantStep(TestCase $test, AccessGrant $grant, string $step, array $data = []): TestResponse
{
    return $test->withHeaders(grantKey())->post(route("access.admin.grants.{$step}", $grant->public_id), [
        'lock_version' => $grant->refresh()->lock_version,
        ...$data,
    ]);
}

describe('authorization', function () {
    test('a guest is sent to the sign-in page', function () {
        $this->get(route('access.admin.grants.index'))->assertRedirect(route('login'));
    });

    // TAC-05: a direct visit without the capability is refused, whatever the route.
    test('an account without admin.access.grant or admin.access.approve cannot open or change any grant', function () {
        $this->actingAs(grantAdmin());
        postGrant($this, ['username' => User::factory()->create()->username, 'capability' => 'asset.view'])->assertSessionHasNoErrors();
        $grant = AccessGrant::query()->sole();

        foreach ([User::factory()->create(), User::factory()->inRoleGroup(User::VENDOR)->create(), delegatedAdmin('asset.view')] as $user) {
            $this->actingAs($user);

            $this->get(route('access.admin.grants.index'))->assertForbidden();
            $this->get(route('access.admin.grants.create'))->assertForbidden();
            $this->get(route('access.admin.grants.show', $grant->public_id))->assertForbidden();
            postGrant($this, ['username' => $user->username, 'capability' => 'asset.view'])->assertForbidden();
            grantStep($this, $grant, 'approve')->assertForbidden();
            grantStep($this, $grant, 'revoke', ['reason' => 'Tanpa izin'])->assertForbidden();
        }

        expect($grant->refresh()->status)->toBe(AccessGrant::ACTIVE)
            ->and(AccessGrant::query()->count())->toBe(2);
    });

    test('an approver who may not grant opens the list and the detail but not the form', function () {
        $this->actingAs(grantAdmin());
        postGrant($this, ['username' => User::factory()->create()->username, 'capability' => 'asset.view'])->assertSessionHasNoErrors();
        $grant = AccessGrant::query()->latest('id')->firstOrFail();

        $this->actingAs(delegatedAdmin('admin.access.approve'));

        $this->get(route('access.admin.grants.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/access/index')->where('can.grant', false));
        $this->get(route('access.admin.grants.show', $grant->public_id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('can.revoke', false)->where('can.approve', false));
        $this->get(route('access.admin.grants.create'))->assertForbidden();
        grantStep($this, $grant, 'revoke', ['reason' => 'Tanpa izin'])->assertForbidden();
    });

    test('an unknown grant is not found', function () {
        $this->actingAs(grantAdmin())
            ->get(route('access.admin.grants.show', strtolower((string) Str::ulid())))
            ->assertNotFound();
    });

    test('every access route carries the access administration gate and confirmed TOTP', function () {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'access.admin.grants.'));

        expect($routes->map(fn ($route) => $route->getName())->values()->all())->toEqualCanonicalizing([
            'access.admin.grants.index',
            'access.admin.grants.create',
            'access.admin.grants.store',
            'access.admin.grants.show',
            'access.admin.grants.approve',
            'access.admin.grants.revoke',
        ]);

        foreach ($routes as $route) {
            expect($route->gatherMiddleware())->toContain('auth', 'can:access.administer', 'two-factor');
        }
    });
});

describe('the menu and the workspaces (TAC-01)', function () {
    test('a user without a grant sees only Beranda', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('menu', [['key' => 'home', 'workspace' => 'home', 'href' => '/home']])
                ->where('workspaces', [['key' => 'home', 'href' => '/home']])
                ->where('auth.can.grantAccess', false)
                ->where('auth.can.approveAccess', false));
    });

    test('a Super Admin sees the administration workspace with its three entries', function () {
        $this->actingAs(grantAdmin())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('menu', keysAre(['home', 'users', 'organization', 'access']))
                ->where('menu.3', ['key' => 'access', 'workspace' => 'administration', 'href' => '/admin/access'])
                ->where('workspaces', [
                    ['key' => 'home', 'href' => '/home'],
                    ['key' => 'administration', 'href' => '/admin/users'],
                ])
                ->where('auth.can.grantAccess', true)
                ->where('auth.can.approveAccess', true));
    });

    test('a business grant without a page of its own adds no entry, so the menu never leads to a dead end', function () {
        $this->actingAs(delegatedAdmin('asset.view'))
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('menu', keysAre(['home']))->where('workspaces', keysAre(['home'])));
    });

    // TAC-04: the menu is read on every request, so a revoked grant leaves it at the next one.
    test('a delegated access administrator gets the Akses entry with the grant and loses it on the next request after revocation', function () {
        $admin = grantAdmin();
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($admin);
        postGrant($this, ['username' => $user->username, 'capability' => 'admin.access.grant'])->assertSessionHasNoErrors();
        $grant = AccessGrant::query()->sole();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('menu', keysAre(['home'])));

        $this->actingAs(grantAdmin('Admin Kedua'));
        grantStep($this, $grant, 'approve')->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('menu', keysAre(['home', 'access']))
                ->where('workspaces', keysAre(['home', 'administration'])));
        $this->get(route('access.admin.grants.index'))->assertOk();

        $this->actingAs($admin);
        grantStep($this, $grant, 'revoke', ['reason' => 'Masa tugas berakhir'])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('menu', keysAre(['home']))->where('workspaces', keysAre(['home'])));
        $this->get(route('access.admin.grants.index'))->assertForbidden();
    });
});

describe('granting through PG-503', function () {
    test('the form offers the active profiles, the grantable capabilities with their scopes and the units', function () {
        $user = User::factory()->create();
        AccessProfile::query()->where('code', 'PROCUREMENT_STAFF')->update(['status' => AccessProfile::INACTIVE]);

        $this->actingAs(grantAdmin())
            ->get(route('access.admin.grants.create', ['user' => $user->public_id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/access/form')
                ->where('user.username', $user->username)
                ->where('today', BusinessDate::today()->toDateString())
                ->has('options.profiles', count(AccessProfileSeeder::PROFILES) - 1)
                ->where('options.profiles', fn ($profiles) => collect($profiles)->doesntContain('code', 'PROCUREMENT_STAFF'))
                ->where('options.capabilities', fn ($capabilities) => collect($capabilities)->contains(
                    fn ($each) => $each['value'] === 'asset.view' && $each['scopes'] === ['ORG_UNIT_SUBTREE', 'GLOBAL'] && $each['privileged'] === [],
                ) && collect($capabilities)->doesntContain('value', 'procurement.method.determine'))
                ->has('options.units', count(OrganizationSeeder::UNITS)));
    });

    test('the detail of a user offers Beri akses only to a granter, never for the own or a deactivated account', function () {
        $admin = grantAdmin();
        $user = User::factory()->create();
        $gone = User::factory()->withStatus(User::DEACTIVATED)->create();
        $vendor = User::factory()->inRoleGroup(User::VENDOR)->create();

        $this->actingAs($admin);
        $this->get(route('identity.admin.users.show', $user->public_id))
            ->assertInertia(fn (Assert $page) => $page->where('can.grantAccess', true));
        $this->get(route('identity.admin.users.show', $admin->public_id))
            ->assertInertia(fn (Assert $page) => $page->where('can.grantAccess', false));
        $this->get(route('identity.admin.users.show', $gone->public_id))
            ->assertInertia(fn (Assert $page) => $page->where('can.grantAccess', false));
        // A vendor account holds no capability of the catalog, so the form would only refuse it.
        $this->get(route('identity.admin.users.show', $vendor->public_id))
            ->assertInertia(fn (Assert $page) => $page->where('can.grantAccess', false));
        // A malformed user parameter opens the form for no one rather than failing.
        $this->get(route('access.admin.grants.create', ['user' => ['x']]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('user', null));

        $this->actingAs(delegatedAdmin('admin.users.manage'))
            ->get(route('identity.admin.users.show', $user->public_id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('can.grantAccess', false));
    });

    test('an ordinary grant applies at once and lands on the list', function () {
        $user = User::factory()->create();
        $ft = OrgUnit::query()->where('code', 'FT')->firstOrFail();

        $this->actingAs(grantAdmin());
        postGrant($this, [
            'username' => $user->username,
            'target' => 'profile',
            'profile' => 'UNIT_NEED_OPERATOR',
            'scope_type' => ScopeType::OrgUnit->value,
            'unit' => $ft->public_id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('access.admin.grants.index'));

        $grant = AccessGrant::query()->sole();

        expect($grant->status)->toBe(AccessGrant::ACTIVE)
            ->and($grant->scope_id)->toBe($ft->id)
            ->and($grant->profile_id)->toBe(AccessProfile::query()->where('code', 'UNIT_NEED_OPERATOR')->value('id'))
            ->and($user->can('procurement.need.create'))->toBeTrue();

        $this->get(route('access.admin.grants.index'))
            ->assertInertia(fn (Assert $page) => $page->component('admin/access/index')
                ->has('grants.data', 1)
                ->where('grants.data.0.user.username', $user->username)
                ->where('grants.data.0.scope_unit.code', 'FT')
                ->where('grants.data.0.status', AccessGrant::ACTIVE)
                ->where('pendingCount', 0)
                ->where('can.grant', true));
    });

    // TAC-03: a privileged grant waits for a second person; neither its granter nor its receiver approves it.
    test('a privileged grant waits until another administrator approves it', function () {
        $granter = grantAdmin();
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($granter);
        postGrant($this, ['username' => $user->username, 'target' => 'profile', 'profile' => 'ACCESS_ADMIN'])->assertSessionHasNoErrors();
        $grant = AccessGrant::query()->sole();

        expect($grant->status)->toBe(AccessGrant::PENDING_APPROVAL)
            ->and($user->can('admin.access.grant'))->toBeFalse();

        $this->get(route('access.admin.grants.show', $grant->public_id))
            ->assertInertia(fn (Assert $page) => $page->component('admin/access/show')
                ->where('grant.status', AccessGrant::PENDING_APPROVAL)
                ->where('grant.capabilities', fn ($capabilities) => collect($capabilities)->sort()->values()->all()
                    === collect(AccessProfileSeeder::PROFILES['ACCESS_ADMIN'][2])->sort()->values()->all())
                ->where('can.approve', false)
                ->where('can.revoke', true)
                ->where('approvalBlocked', true));
        grantStep($this, $grant, 'approve')->assertSessionHasErrors('_domain');

        $this->get(route('access.admin.grants.index', ['status' => AccessGrant::PENDING_APPROVAL]))
            ->assertInertia(fn (Assert $page) => $page->has('grants.data', 1)->where('pendingCount', 1));

        $approver = grantAdmin('Admin Kedua');
        $this->actingAs($approver);
        $this->get(route('access.admin.grants.show', $grant->public_id))
            ->assertInertia(fn (Assert $page) => $page->where('can.approve', true)->where('approvalBlocked', false));
        grantStep($this, $grant, 'approve')
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('access.admin.grants.show', $grant->public_id));

        expect($grant->refresh()->status)->toBe(AccessGrant::ACTIVE)
            ->and($grant->approved_by)->toBe($approver->id)
            ->and($user->can('admin.access.grant'))->toBeTrue();

        $this->get(route('access.admin.grants.show', $grant->public_id))
            ->assertInertia(fn (Assert $page) => $page
                ->where('grant.approved_by', 'Admin Kedua')
                ->where('grant.granted_by', $granter->name)
                ->where('can.approve', false));
    });

    // AUTHZ-20: nobody grants to themselves, not even through the form.
    test('an administrator cannot grant to themselves', function () {
        $admin = grantAdmin();

        $this->actingAs($admin);
        postGrant($this, ['username' => $admin->username, 'capability' => 'admin.audit.read'])->assertSessionHasErrors('_domain');

        expect(AccessGrant::query()->count())->toBe(0);
    });

    test('the form names each field it refuses and keeps the grant unsaved', function () {
        $user = User::factory()->create();

        $this->actingAs(grantAdmin());

        postGrant($this, ['username' => 'tidak.ada', 'capability' => 'asset.view'])->assertSessionHasErrors('username');
        postGrant($this, ['username' => $user->username, 'target' => 'profile', 'profile' => 'TIDAK_ADA'])->assertSessionHasErrors('profile');
        postGrant($this, ['username' => $user->username, 'capability' => 'asset.tidak_ada'])->assertSessionHasErrors('capability');
        postGrant($this, ['username' => $user->username, 'capability' => 'asset.view', 'scope_type' => ScopeType::OrgUnitSubtree->value])
            ->assertSessionHasErrors('unit');
        postGrant($this, ['username' => $user->username, 'capability' => 'admin.policy.author', 'scope_type' => ScopeType::PolicyFamily->value])
            ->assertSessionHasErrors('scope_key');
        postGrant($this, ['username' => $user->username, 'capability' => 'asset.view', 'effective_to' => BusinessDate::today()->toDateString()])
            ->assertSessionHasErrors('effective_to');
        postGrant($this, ['username' => $user->username, 'capability' => 'asset.view', 'reason' => ''])->assertSessionHasErrors('reason');
        postGrant($this, ['username' => $user->username, 'capability' => 'asset.view', 'scope_type' => ScopeType::OrgUnit->value, 'unit' => OrgUnit::query()->where('code', 'FT')->value('public_id')])
            ->assertSessionHasErrors('_domain');

        expect(AccessGrant::query()->count())->toBe(0);
    });
});

describe('the list', function () {
    test('the list filters by user and status and keeps the filters in the URL', function () {
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi.santoso']);
        $sari = User::factory()->create(['name' => 'Sari Wulandari', 'username' => 'sari.w']);

        $this->actingAs(grantAdmin());
        postGrant($this, ['username' => $budi->username, 'capability' => 'asset.view'])->assertSessionHasNoErrors();
        postGrant($this, ['username' => $sari->username, 'capability' => 'admin.audit.read'])->assertSessionHasNoErrors();

        $this->get(route('access.admin.grants.index', ['q' => 'BUDI']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('grants.data', 1)
                ->where('grants.data.0.user.name', 'Budi Santoso')
                ->where('filters', ['q' => 'BUDI', 'status' => '']));

        $this->get(route('access.admin.grants.index', ['status' => AccessGrant::PENDING_APPROVAL]))
            ->assertInertia(fn (Assert $page) => $page->has('grants.data', 1)->where('grants.data.0.user.name', 'Sari Wulandari'));

        $this->get(route('access.admin.grants.index', ['status' => 'BOGUS', 'q' => '%']))
            ->assertInertia(fn (Assert $page) => $page->has('grants.data', 0)->where('filters.status', ''));
    });

    test('a grant past its end shows Kedaluwarsa, under the expired filter, without Cabut', function () {
        $grant = app(GrantAccess::class)->handle(
            grantAdmin(),
            User::factory()->create(),
            null,
            'asset.view',
            ScopeType::Global,
            null,
            null,
            BusinessDate::today(),
            BusinessDate::today()->addDay(),
            'Penugasan staf sesuai surat tugas',
        );

        // The session starts after the day has passed, so the idle limit does not end it.
        $this->travel(1)->days();
        $this->actingAs(grantAdmin());

        $this->get(route('access.admin.grants.index', ['status' => AccessGrant::EXPIRED]))
            ->assertInertia(fn (Assert $page) => $page->has('grants.data', 1)->where('grants.data.0.status', AccessGrant::EXPIRED));
        $this->get(route('access.admin.grants.index', ['status' => AccessGrant::ACTIVE]))
            ->assertInertia(fn (Assert $page) => $page->has('grants.data', 0));
        $this->get(route('access.admin.grants.show', $grant->public_id))
            ->assertInertia(fn (Assert $page) => $page->where('grant.status', AccessGrant::EXPIRED)->where('can.revoke', false));
    });
});

describe('revoking through PG-503', function () {
    test('a revocation states its reason, stays as history and refuses a stale version', function () {
        $user = User::factory()->create();

        $this->actingAs(grantAdmin());
        postGrant($this, ['username' => $user->username, 'capability' => 'asset.view'])->assertSessionHasNoErrors();
        $grant = AccessGrant::query()->sole();
        $seen = $grant->lock_version;

        // Another command changed the grant after this user opened it.
        AccessGrant::query()->whereKey($grant->id)->increment('lock_version');

        $this->withHeaders(grantKey())
            ->post(route('access.admin.grants.revoke', $grant->public_id), ['reason' => 'Pindah tugas', 'lock_version' => $seen])
            ->assertStatus(409);

        expect($grant->refresh()->status)->toBe(AccessGrant::ACTIVE);

        grantStep($this, $grant, 'revoke', ['reason' => ''])->assertSessionHasErrors('reason');
        grantStep($this, $grant, 'revoke', ['reason' => 'Pindah tugas'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('access.admin.grants.show', $grant->public_id));

        expect($grant->refresh()->status)->toBe(AccessGrant::REVOKED)
            ->and($grant->revoke_reason)->toBe('Pindah tugas')
            ->and($user->can('asset.view'))->toBeFalse();

        grantStep($this, $grant, 'revoke', ['reason' => 'Sekali lagi'])->assertSessionHasErrors('_domain');

        $this->get(route('access.admin.grants.show', $grant->public_id))
            ->assertInertia(fn (Assert $page) => $page
                ->where('grant.status', AccessGrant::REVOKED)
                ->where('grant.revoke_reason', 'Pindah tugas')
                ->where('can.revoke', false)
                ->where('can.approve', false));
    });
});
