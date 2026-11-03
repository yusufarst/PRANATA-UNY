<?php

use App\Models\User;
use App\Modules\Organization\Contracts\MaintenancePolicy;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Models\OrgUnitHistory;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Organization\Support\ChangeApplier;
use App\Modules\Organization\Support\UnresolvedMaintenancePolicy;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\IntegrityTriggers;
use Carbon\CarbonImmutable;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\DatabaseSession;
use Tests\TestCase;

/*
 * PG-505 Organisasi (TASK-013): the unit tree with its ltree paths, the history that never overwrites a version, positions
 * and appointments, and the change requests of WF-029 F1/F2 whose decision (TR-285) stays behind the policy gate (TAC-04).
 */

function organizationAdmin(): User
{
    // Administration needs confirmed TOTP of a privileged account (TASK-012 TAC-02).
    return User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create(['name' => 'Admin Organisasi']);
}

/**
 * @return array<string, string>
 */
function organizationKey(): array
{
    return ['X-Idempotency-Key' => (string) Str::uuid()];
}

function seededUnit(string $code): OrgUnit
{
    return OrgUnit::query()->where('code', $code)->firstOrFail();
}

function businessToday(): string
{
    return BusinessDate::today()->toDateString();
}

/**
 * TR-281: draft a change request through PG-505 and give it back.
 *
 * @param  array<string, mixed>  $scope
 */
function draftOrganizationChange(TestCase $test, string $kind, ?OrgUnit $unit, array $scope): ChangeRequest
{
    $test->withHeaders(organizationKey())
        ->post(route('organization.admin.requests.store'), [
            'kind' => $kind,
            'unit' => $unit?->public_id,
            'reason' => 'Penataan organisasi sesuai statuta',
            'proposed_effective_from' => businessToday(),
            ...$scope,
        ])
        ->assertSessionHasNoErrors();

    return ChangeRequest::query()->latest('id')->firstOrFail();
}

/**
 * One transition of the request at the version it has now.
 *
 * @param  array<string, mixed>  $data
 */
function organizationStep(TestCase $test, ChangeRequest $request, string $step, array $data = []): TestResponse
{
    return $test->withHeaders(organizationKey())
        ->post(route("organization.admin.requests.{$step}", $request->public_id), [
            'lock_version' => $request->refresh()->lock_version,
            ...$data,
        ]);
}

/**
 * TR-282, TR-284 and TR-285: from draft to a decision effective today.
 */
function approveOrganizationChange(TestCase $test, ChangeRequest $request): ChangeRequest
{
    organizationStep($test, $request, 'submit')->assertSessionHasNoErrors();
    organizationStep($test, $request, 'escalate')->assertSessionHasNoErrors();
    organizationStep($test, $request, 'decide', [
        'decision' => 'APPROVE',
        'effective_from' => businessToday(),
        'decision_ref' => 'SK/ORG/2026/001',
    ])->assertSessionHasNoErrors();

    return $request->refresh();
}

/**
 * The whole way to S5: the change is now in the organization.
 *
 * @param  array<string, mixed>  $scope
 */
function applyOrganizationChange(TestCase $test, string $kind, ?OrgUnit $unit, array $scope): ChangeRequest
{
    $request = approveOrganizationChange($test, draftOrganizationChange($test, $kind, $unit, $scope));

    organizationStep($test, $request, 'apply')->assertSessionHasNoErrors();

    return $request->refresh();
}

// Authorization: only admin.org.manage (ACCESS_CONTROL section 4.1).
test('a guest is sent to the sign-in page', function () {
    $this->get(route('organization.admin.index'))->assertRedirect(route('login'));
});

test('an account without admin.org.manage cannot open or change the organization', function () {
    $this->seed(OrganizationSeeder::class);
    $admin = organizationAdmin();
    $this->actingAs($admin);
    $request = draftOrganizationChange($this, ChangeRequest::UPDATE_UNIT, seededUnit('FT'), [
        'name' => 'Fakultas Teknik dan Sains',
        'unit_type' => 'FAKULTAS',
    ]);

    foreach ([User::factory()->create(), User::factory()->inRoleGroup(User::VENDOR)->create()] as $user) {
        $this->actingAs($user);

        $this->get(route('organization.admin.index'))->assertForbidden();
        $this->get(route('organization.admin.requests.create'))->assertForbidden();
        $this->get(route('organization.admin.requests.show', $request->public_id))->assertForbidden();
        $this->get(route('organization.admin.requests.edit', $request->public_id))->assertForbidden();
        $this->withHeaders(organizationKey())
            ->post(route('organization.admin.requests.store'), [
                'kind' => ChangeRequest::CREATE_UNIT,
                'code' => 'LIAR',
                'name' => 'Unit Liar',
                'unit_type' => 'BIRO',
                'reason' => 'Tanpa izin',
                'proposed_effective_from' => businessToday(),
            ])
            ->assertForbidden();

        foreach (['submit', 'escalate', 'apply'] as $step) {
            organizationStep($this, $request, $step)->assertForbidden();
        }

        organizationStep($this, $request, 'decide', ['decision' => 'APPROVE', 'effective_from' => businessToday(), 'decision_ref' => 'X'])
            ->assertForbidden();
        organizationStep($this, $request, 'withdraw', ['reason' => 'Tanpa izin'])->assertForbidden();
    }

    expect($request->refresh()->status)->toBe(ChangeRequest::DRAFT)
        ->and(OrgUnit::query()->where('code', 'LIAR')->exists())->toBeFalse()
        ->and(ChangeRequest::query()->count())->toBe(1);
});

test('the menu offers Organisasi only to an account that may manage the organization', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.can.manageOrganization', false));

    $this->actingAs(organizationAdmin())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.can.manageOrganization', true));
});

test('every organization route carries admin.org.manage', function () {
    $routes = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'organization.admin.'));

    expect($routes)->not->toBeEmpty();

    foreach ($routes as $route) {
        expect($route->gatherMiddleware())->toContain('auth', 'can:admin.org.manage', 'two-factor');
    }
});

// TAC-01: a tree of several levels and types, not only faculties (OD-04).
test('the seeder builds a synthetic tree whose paths follow the parents and is safe to repeat', function () {
    $this->seed(OrganizationSeeder::class);
    $this->seed(OrganizationSeeder::class);

    expect(OrgUnit::query()->count())->toBe(count(OrganizationSeeder::UNITS))
        ->and(OrgPosition::query()->count())->toBe(count(OrganizationSeeder::POSITIONS))
        ->and(OrgUnit::query()->distinct()->count('unit_type'))->toBeGreaterThan(4)
        ->and(OrgUnitHistory::query()->count())->toBe(count(OrganizationSeeder::UNITS));

    $univ = seededUnit('UNIV');
    $ft = seededUnit('FT');
    $ti = seededUnit('FT.TI');

    expect($univ->depth)->toBe(0)
        ->and($ti->depth)->toBe(2)
        ->and($ti->path)->toBe("{$univ->public_id}.{$ft->public_id}.{$ti->public_id}")
        ->and(OrgUnit::query()->withinSubtreeOf($ft)->pluck('code')->sort()->values()->all())->toBe(['FT', 'FT.TI', 'FT.TS']);
});

test('the tree lists every unit in path order and a selected unit shows its ancestors, history, positions and requests', function () {
    $this->seed(OrganizationSeeder::class);
    $ti = seededUnit('FT.TI');

    $this->actingAs(organizationAdmin())
        ->get(route('organization.admin.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/organization/index')
            ->has('units', count(OrganizationSeeder::UNITS))
            ->where('units.0.code', 'UNIV')
            ->where('selected', null));

    $this->get(route('organization.admin.index', ['unit' => $ti->public_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/organization/index')
            ->where('selected.unit.public_id', $ti->public_id)
            ->where('selected.unit.parent_name', 'Fakultas Teknik')
            ->where('selected.ancestors.0.name', 'Universitas Contoh')
            ->where('selected.ancestors.1.name', 'Fakultas Teknik')
            ->has('selected.history', 1)
            ->where('selected.history.0.change_type', OrgUnitHistory::CREATE)
            ->has('selected.positions', 1)
            ->where('selected.positions.0.code', 'KAPRODI_TI'));

    $this->get(route('organization.admin.index', ['unit' => 'BUKAN-UNIT']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', null));
});

test('the form opened from a unit proposes a sub-unit below it, and a move never offers the unit or its subtree', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $this->actingAs(organizationAdmin());

    $this->get(route('organization.admin.requests.create', ['kind' => ChangeRequest::CREATE_UNIT, 'unit' => $ft->public_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/organization/request-form')
            ->where('kind', ChangeRequest::CREATE_UNIT)
            ->where('unit.public_id', $ft->public_id)
            ->has('options.units', count(OrganizationSeeder::UNITS)));

    $this->get(route('organization.admin.requests.create', ['kind' => ChangeRequest::MOVE_UNIT, 'unit' => $ft->public_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('kind', ChangeRequest::MOVE_UNIT)
            ->has('options.units', count(OrganizationSeeder::UNITS) - 3));

    $this->get(route('organization.admin.requests.create', ['kind' => 'BOGUS']))
        ->assertInertia(fn (Assert $page) => $page->where('kind', ChangeRequest::CREATE_UNIT)->where('unit', null));
});

// WF-029: nothing changes the organization before S5.
test('a new unit goes through draft, validation, decision and application, and only then exists', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::CREATE_UNIT, null, [
        'code' => 'FT.TE',
        'name' => 'Program Studi Teknik Elektro',
        'short_name' => 'TE',
        'unit_type' => 'PRODI',
        'parent' => $ft->public_id,
    ]);

    expect($request->status)->toBe(ChangeRequest::DRAFT)
        ->and($request->family)->toBe(ChangeRequest::F1)
        ->and($request->org_unit_id)->toBeNull();

    $request = approveOrganizationChange($this, $request);

    expect($request->status)->toBe(ChangeRequest::APPROVED)
        ->and($request->is_simulation)->toBeTrue()
        ->and(OrgUnit::query()->where('code', 'FT.TE')->exists())->toBeFalse();

    $this->get(route('organization.admin.requests.show', $request->public_id))
        ->assertInertia(fn (Assert $page) => $page->component('admin/organization/request')
            ->where('can.apply', true)
            ->where('can.decide', false)
            ->has('transitions', 4));

    organizationStep($this, $request, 'apply')
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('organization.admin.index', ['unit' => OrgUnit::query()->where('code', 'FT.TE')->value('public_id')]));

    $unit = seededUnit('FT.TE');

    expect($request->refresh()->status)->toBe(ChangeRequest::EFFECTIVE)
        ->and($request->org_unit_id)->toBe($unit->id)
        ->and($unit->parent_id)->toBe($ft->id)
        ->and($unit->path)->toBe("{$ft->path}.{$unit->public_id}")
        ->and($unit->depth)->toBe(2)
        ->and($unit->effective_from->toDateString())->toBe(businessToday())
        ->and($request->transitions()->pluck('transition_code')->all())->toBe(['TR-281', 'TR-282', 'TR-284', 'TR-285', 'TR-286']);

    $history = $unit->history()->sole();

    expect($history->change_type)->toBe(OrgUnitHistory::CREATE)
        ->and($history->before)->toBeNull()
        ->and($history->after['name'])->toBe('Program Studi Teknik Elektro')
        ->and($history->decision_ref)->toBe('SK/ORG/2026/001')
        ->and($history->is_simulation)->toBeTrue()
        ->and($history->change_request_id)->toBe($request->id);
});

// TAC-01: a move rewrites the path of the whole subtree consistently.
test('moving a unit moves its whole subtree and keeps the paths and depths consistent', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $lppm = seededUnit('LPPM');
    $this->actingAs(organizationAdmin());

    applyOrganizationChange($this, ChangeRequest::MOVE_UNIT, $ft, ['parent' => $lppm->public_id]);

    $ft->refresh();

    expect($ft->parent_id)->toBe($lppm->id)
        ->and($ft->path)->toBe("{$lppm->path}.{$ft->public_id}")
        ->and($ft->depth)->toBe(2);

    foreach (['FT.TI', 'FT.TS'] as $code) {
        $child = seededUnit($code);

        expect($child->path)->toBe("{$ft->path}.{$child->public_id}")
            ->and($child->depth)->toBe(3)
            ->and($child->parent_id)->toBe($ft->id);
    }

    expect(OrgUnit::query()->withinSubtreeOf($lppm)->count())->toBe(4)
        ->and(OrgUnit::query()->whereRaw('depth <> nlevel(path) - 1')->exists())->toBeFalse();

    $move = $ft->history()->where('change_type', OrgUnitHistory::MOVE)->sole();

    expect($move->before['parent_public_id'])->toBe(seededUnit('UNIV')->public_id)
        ->and($move->after['parent_public_id'])->toBe($lppm->public_id);

    // A move to the top level is a move too.
    applyOrganizationChange($this, ChangeRequest::MOVE_UNIT, $ft, ['parent' => null]);

    expect($ft->refresh()->path)->toBe($ft->public_id)
        ->and($ft->depth)->toBe(0)
        ->and(seededUnit('FT.TI')->depth)->toBe(1);
});

test('a unit cannot move below itself or below one of its own units', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $before = OrgUnit::query()->orderBy('id')->pluck('path', 'code')->all();
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::MOVE_UNIT, $ft, ['parent' => seededUnit('FT.TI')->public_id]);
    organizationStep($this, $request, 'submit')->assertSessionHasNoErrors();

    expect($request->refresh()->status)->toBe(ChangeRequest::VALIDATED)
        ->and($request->validation['issues'])->toContain(['code' => 'move_into_own_subtree', 'blocking' => true])
        ->and($request->hasBlockingIssues())->toBeTrue();

    organizationStep($this, $request, 'escalate')->assertSessionHasErrors('_domain');

    expect($request->refresh()->status)->toBe(ChangeRequest::VALIDATED)
        ->and(OrgUnit::query()->orderBy('id')->pluck('path', 'code')->all())->toBe($before);
});

test('two approved moves that cross each other never make a cycle, whichever is applied second', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $lppm = seededUnit('LPPM');
    $admin = organizationAdmin();
    $this->actingAs($admin);

    // Both are checked against the same tree and both pass; together they would put each unit below the other.
    $ftIntoLppm = approveOrganizationChange($this, draftOrganizationChange($this, ChangeRequest::MOVE_UNIT, $ft, ['parent' => $lppm->public_id]));
    $lppmIntoFt = approveOrganizationChange($this, draftOrganizationChange($this, ChangeRequest::MOVE_UNIT, $lppm, ['parent' => $ft->public_id]));

    organizationStep($this, $ftIntoLppm, 'apply')->assertSessionHasNoErrors();
    $after = OrgUnit::query()->orderBy('id')->pluck('path', 'code')->all();

    // The write itself refuses a cycle on the locked rows, even when the earlier checks were passed.
    expect(fn () => DB::transaction(fn () => app(ChangeApplier::class)->apply($lppmIntoFt->refresh(), $admin)))
        ->toThrow(DomainRuleViolation::class);

    organizationStep($this, $lppmIntoFt, 'apply')->assertSessionHasErrors('_domain');

    expect($lppmIntoFt->refresh()->status)->toBe(ChangeRequest::APPROVED)
        ->and(OrgUnit::query()->orderBy('id')->pluck('path', 'code')->all())->toBe($after)
        ->and($ft->refresh()->parent_id)->toBe($lppm->id)
        ->and($lppm->refresh()->parent_id)->not->toBe($ft->id);
});

// TAC-02: a change never overwrites an earlier version.
test('a rename appends a version and keeps the earlier one, and history cannot be changed', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $this->actingAs(organizationAdmin());

    $first = applyOrganizationChange($this, ChangeRequest::UPDATE_UNIT, $ft, [
        'name' => 'Fakultas Teknik dan Sains',
        'short_name' => 'FTS',
        'unit_type' => 'FAKULTAS',
    ]);
    $second = applyOrganizationChange($this, ChangeRequest::UPDATE_UNIT, $ft, [
        'name' => 'Fakultas Teknik dan Sains',
        'short_name' => 'FTS',
        'unit_type' => 'FAKULTAS_TEKNIK',
    ]);

    $history = $ft->history()->orderBy('id')->get();

    expect($ft->refresh()->name)->toBe('Fakultas Teknik dan Sains')
        ->and($history->pluck('change_type')->all())->toBe([OrgUnitHistory::CREATE, OrgUnitHistory::RENAME, OrgUnitHistory::CORRECT])
        ->and($history[0]->after['name'])->toBe('Fakultas Teknik')
        ->and($history[1]->before['name'])->toBe('Fakultas Teknik')
        ->and($history[1]->after['name'])->toBe('Fakultas Teknik dan Sains')
        ->and($history[2]->before['unit_type'])->toBe('FAKULTAS')
        ->and($history[2]->after['unit_type'])->toBe('FAKULTAS_TEKNIK');

    // TR-287: the later change supersedes the earlier request; the earlier request and its history stay.
    expect($first->refresh()->status)->toBe(ChangeRequest::SUPERSEDED)
        ->and($second->refresh()->status)->toBe(ChangeRequest::EFFECTIVE)
        ->and($first->transitions()->latest('id')->value('transition_code'))->toBe('TR-287');

    expect(DatabaseSession::sqlStateAs(null, fn () => DB::table('org_unit_history')->where('id', $history[0]->id)->update(['decision_ref' => 'UBAH'])))
        ->toBe(IntegrityTriggers::APPEND_ONLY)
        ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('org_unit_history')->where('id', $history[0]->id)->delete()))
        ->toBe(IntegrityTriggers::APPEND_ONLY);

    $this->get(route('organization.admin.index', ['unit' => $ft->public_id]))
        ->assertInertia(fn (Assert $page) => $page->has('selected.history', 3)
            ->where('selected.history.0.change_type', OrgUnitHistory::CORRECT)
            ->where('selected.history.2.change_type', OrgUnitHistory::CREATE));
});

test('a change that changes nothing is blocked at validation', function () {
    $this->seed(OrganizationSeeder::class);
    $ft = seededUnit('FT');
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::UPDATE_UNIT, $ft, [
        'name' => $ft->name,
        'short_name' => $ft->short_name,
        'unit_type' => $ft->unit_type,
    ]);
    organizationStep($this, $request, 'submit');

    expect($request->refresh()->validation['issues'])->toContain(['code' => 'nothing_changes', 'blocking' => true]);
});

test('a unit with active units below it cannot be deactivated, and a leaf unit can and is shown as inactive', function () {
    $this->seed(OrganizationSeeder::class);
    $this->actingAs(organizationAdmin());

    $blocked = draftOrganizationChange($this, ChangeRequest::DEACTIVATE_UNIT, seededUnit('FT'), []);
    organizationStep($this, $blocked, 'submit');

    expect($blocked->refresh()->validation['issues'])->toContain(['code' => 'has_active_children', 'blocking' => true]);

    $ts = seededUnit('FT.TS');
    applyOrganizationChange($this, ChangeRequest::DEACTIVATE_UNIT, $ts, []);

    expect($ts->refresh()->status)->toBe(OrgUnit::INACTIVE)
        ->and($ts->history()->latest('id')->value('change_type'))->toBe(OrgUnitHistory::DEACTIVATE);

    applyOrganizationChange($this, ChangeRequest::ACTIVATE_UNIT, $ts, []);

    expect($ts->refresh()->status)->toBe(OrgUnit::ACTIVE);
});

// TAC-03: an appointment has a term and a decree, and grants nothing.
test('an appointment has a term and a decree reference, and grants no permission', function () {
    $this->seed(OrganizationSeeder::class);
    $ti = seededUnit('FT.TI');
    $person = User::factory()->create(['name' => 'Dosen Contoh']);
    $this->actingAs(organizationAdmin());

    $request = applyOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/045',
        'effective_to' => CarbonImmutable::parse(businessToday())->addYears(4)->toDateString(),
    ]);

    $appointment = OrgAppointment::query()->sole();

    expect($request->family)->toBe(ChangeRequest::F2)
        ->and($appointment->user_id)->toBe($person->id)
        ->and($appointment->org_unit_id)->toBe($ti->id)
        ->and($appointment->status)->toBe(OrgAppointment::ACTIVE)
        ->and($appointment->decree_ref)->toBe('SK/REK/2026/045')
        ->and($appointment->effective_from->toDateString())->toBe(businessToday())
        ->and($appointment->effective_to->toDateString())->toBe(CarbonImmutable::parse(businessToday())->addYears(4)->toDateString())
        ->and($appointment->change_request_id)->toBe($request->id);

    $this->get(route('organization.admin.index', ['unit' => $ti->public_id]))
        ->assertInertia(fn (Assert $page) => $page->has('selected.appointments', 1)
            ->where('selected.appointments.0.person', 'Dosen Contoh')
            ->where('selected.appointments.0.decree_ref', 'SK/REK/2026/045')
            ->where('selected.appointments.0.status', OrgAppointment::ACTIVE));

    // Holding a position opens nothing: the appointed person still has no admin.org.manage.
    expect($person->refresh()->can('admin.org.manage'))->toBeFalse();

    $this->actingAs($person)->get(route('organization.admin.index'))->assertForbidden();
});

test('an appointment needs a decree, an eligible staff account and a position not already held', function () {
    $this->seed(OrganizationSeeder::class);
    $ti = seededUnit('FT.TI');
    $vendor = User::factory()->inRoleGroup(User::VENDOR)->create();
    $this->actingAs(organizationAdmin());

    $this->withHeaders(organizationKey())
        ->post(route('organization.admin.requests.store'), [
            'kind' => ChangeRequest::APPOINT,
            'unit' => $ti->public_id,
            'user' => $vendor->public_id,
            'position' => 'KAPRODI_TI',
            'reason' => 'Tanpa SK',
            'proposed_effective_from' => businessToday(),
        ])
        ->assertSessionHasErrors('decree_ref');

    $request = draftOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $vendor->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/046',
    ]);
    organizationStep($this, $request, 'submit');

    expect($request->refresh()->validation['issues'])->toContain(['code' => 'person_not_eligible', 'blocking' => true]);

    $person = User::factory()->create();
    applyOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/047',
    ]);

    $again = draftOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/048',
    ]);
    organizationStep($this, $again, 'submit');

    expect($again->refresh()->validation['issues'])->toContain(['code' => 'already_appointed', 'blocking' => true]);
});

test('a position owned by one unit cannot be held in another, nor offered there', function () {
    $this->seed(OrganizationSeeder::class);
    $buk = seededUnit('BUK');
    $person = User::factory()->create();
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::APPOINT, $buk, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/049',
    ]);
    organizationStep($this, $request, 'submit');

    expect($request->refresh()->validation['issues'])->toContain(['code' => 'position_other_unit', 'blocking' => true]);

    $this->get(route('organization.admin.requests.create', ['kind' => ChangeRequest::APPOINT, 'unit' => $buk->public_id]))
        ->assertInertia(fn (Assert $page) => $page->where(
            'options.positions',
            fn ($positions) => collect($positions)->pluck('value')->doesntContain('KAPRODI_TI'),
        ));
});

test('a term that has run out neither blocks a new appointment nor keeps the unit from closing', function () {
    $this->seed(OrganizationSeeder::class);
    $ti = seededUnit('FT.TI');
    $person = User::factory()->create();
    $this->actingAs(organizationAdmin());

    applyOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/051',
    ]);
    OrgAppointment::query()->sole()->forceFill([
        'effective_from' => CarbonImmutable::parse(businessToday())->subYears(4),
        'effective_to' => CarbonImmutable::parse(businessToday())->subDay(),
    ])->save();

    $again = draftOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/052',
    ]);
    organizationStep($this, $again, 'submit');

    $close = draftOrganizationChange($this, ChangeRequest::DEACTIVATE_UNIT, $ti, []);
    organizationStep($this, $close, 'submit');

    expect(collect($again->refresh()->validation['issues'])->pluck('code'))->not->toContain('already_appointed')
        ->and(collect($close->refresh()->validation['issues'])->pluck('code'))->not->toContain('has_active_appointments');
});

test('ending an appointment that starts today is refused, so its term never ends before it starts', function () {
    $this->seed(OrganizationSeeder::class);
    $ti = seededUnit('FT.TI');
    $person = User::factory()->create();
    $this->actingAs(organizationAdmin());

    applyOrganizationChange($this, ChangeRequest::APPOINT, $ti, [
        'user' => $person->public_id,
        'position' => 'KAPRODI_TI',
        'decree_ref' => 'SK/REK/2026/050',
    ]);
    $appointment = OrgAppointment::query()->sole();

    $end = approveOrganizationChange($this, draftOrganizationChange($this, ChangeRequest::END_APPOINTMENT, $ti, ['appointment' => $appointment->id]));

    organizationStep($this, $end, 'apply')->assertSessionHasErrors('_domain');

    expect($end->refresh()->status)->toBe(ChangeRequest::APPROVED)
        ->and($appointment->refresh()->status)->toBe(OrgAppointment::ACTIVE);

    // An appointment that began earlier ends on the effective date of the decision.
    $appointment->forceFill(['effective_from' => CarbonImmutable::parse(businessToday())->subYear()])->save();

    organizationStep($this, $end, 'apply')->assertSessionHasNoErrors();

    expect($appointment->refresh()->status)->toBe(OrgAppointment::ENDED)
        ->and($appointment->effective_to->toDateString())->toBe(businessToday());
});

// TAC-04: TR-285 needs organization.maintenance officially in force on the decision date.
test('without an official policy and without simulation the decision is not available and is refused', function () {
    config(['policy.allow_draft' => false]);
    $this->seed(OrganizationSeeder::class);
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::UPDATE_UNIT, seededUnit('FT'), [
        'name' => 'Fakultas Teknik Baru',
        'unit_type' => 'FAKULTAS',
    ]);
    organizationStep($this, $request, 'submit')->assertSessionHasNoErrors();
    organizationStep($this, $request, 'escalate')->assertSessionHasNoErrors();

    $this->get(route('organization.admin.requests.show', $request->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('changeRequest.status', ChangeRequest::AWAITING_DECISION)
            ->where('decision.available', false)
            ->where('can.decide', false)
            ->where('can.withdraw', true));

    organizationStep($this, $request, 'decide', [
        'decision' => 'APPROVE',
        'effective_from' => businessToday(),
        'decision_ref' => 'SK/ORG/2026/009',
    ])->assertSessionHasErrors('_domain');

    expect($request->refresh()->status)->toBe(ChangeRequest::AWAITING_DECISION)
        ->and($request->decided_at)->toBeNull()
        ->and(seededUnit('FT')->name)->toBe('Fakultas Teknik');

    // The request stays open and can still be withdrawn (TR-288).
    organizationStep($this, $request, 'withdraw', ['reason' => 'Menunggu kebijakan'])->assertSessionHasNoErrors();

    expect($request->refresh()->status)->toBe(ChangeRequest::WITHDRAWN)
        ->and($request->closed_reason)->toBe('Menunggu kebijakan');
});

test('production never records a simulated decision, even when draft policies are allowed', function () {
    config(['policy.allow_draft' => true]);
    $policy = new UnresolvedMaintenancePolicy;

    expect($policy->allowsSimulation())->toBeTrue()
        ->and($policy->isOfficialOn(BusinessDate::today()))->toBeFalse();

    $environment = app()->environment();
    app()->detectEnvironment(fn () => 'production');

    try {
        expect($policy->allowsSimulation())->toBeFalse();
    } finally {
        app()->detectEnvironment(fn () => $environment);
    }
});

test('with an official policy version the decision is recorded as official, not as a simulation', function () {
    app()->bind(MaintenancePolicy::class, fn () => new class implements MaintenancePolicy
    {
        public function isOfficialOn(CarbonImmutable $referenceDate): bool
        {
            return true;
        }

        public function allowsSimulation(): bool
        {
            return false;
        }
    });

    $this->seed(OrganizationSeeder::class);
    $this->actingAs(organizationAdmin());

    $request = applyOrganizationChange($this, ChangeRequest::UPDATE_UNIT, seededUnit('LPPM'), [
        'name' => 'Lembaga Riset dan Inovasi',
        'unit_type' => 'LEMBAGA',
    ]);

    expect($request->is_simulation)->toBeFalse()
        ->and($request->transitions()->where('is_simulation', true)->exists())->toBeFalse()
        ->and(seededUnit('LPPM')->history()->latest('id')->value('is_simulation'))->toBeFalse();
});

test('a decision cannot be backdated and an approval cannot be applied before its effective date', function () {
    $this->seed(OrganizationSeeder::class);
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::UPDATE_UNIT, seededUnit('BUK'), [
        'name' => 'Biro Umum, Keuangan dan Aset',
        'unit_type' => 'BIRO',
    ]);
    organizationStep($this, $request, 'submit');
    organizationStep($this, $request, 'escalate');

    organizationStep($this, $request, 'decide', [
        'decision' => 'APPROVE',
        'effective_from' => CarbonImmutable::parse(businessToday())->subDay()->toDateString(),
        'decision_ref' => 'SK/ORG/2026/010',
    ])->assertSessionHasErrors('_domain');

    $tomorrow = CarbonImmutable::parse(businessToday())->addDay()->toDateString();

    organizationStep($this, $request, 'decide', [
        'decision' => 'APPROVE',
        'effective_from' => $tomorrow,
        'decision_ref' => 'SK/ORG/2026/010',
    ])->assertSessionHasNoErrors();

    $this->get(route('organization.admin.requests.show', $request->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('changeRequest.effective_from', $tomorrow)->where('can.apply', false));

    organizationStep($this, $request, 'apply')->assertSessionHasErrors('_domain');

    expect($request->refresh()->status)->toBe(ChangeRequest::APPROVED)
        ->and(seededUnit('BUK')->name)->toBe('Biro Umum dan Keuangan');
});

test('a rejection needs a reason and closes the request without changing the organization', function () {
    $this->seed(OrganizationSeeder::class);
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::UPDATE_UNIT, seededUnit('BUK'), [
        'name' => 'Biro Keuangan',
        'unit_type' => 'BIRO',
    ]);
    organizationStep($this, $request, 'submit');
    organizationStep($this, $request, 'escalate');

    organizationStep($this, $request, 'decide', ['decision' => 'REJECT', 'decision_ref' => 'SK/ORG/2026/011'])
        ->assertSessionHasErrors('reason');

    organizationStep($this, $request, 'decide', [
        'decision' => 'REJECT',
        'decision_ref' => 'SK/ORG/2026/011',
        'reason' => 'Tidak sesuai statuta',
    ])->assertSessionHasNoErrors();

    expect($request->refresh()->status)->toBe(ChangeRequest::REJECTED)
        ->and($request->effective_from)->toBeNull()
        ->and($request->closed_reason)->toBe('Tidak sesuai statuta')
        ->and(seededUnit('BUK')->name)->toBe('Biro Umum dan Keuangan');
});

test('a validated request can go back to draft, be corrected and be submitted again', function () {
    $this->seed(OrganizationSeeder::class);
    $this->actingAs(organizationAdmin());

    $request = draftOrganizationChange($this, ChangeRequest::UPDATE_UNIT, seededUnit('FT.TS'), [
        'name' => 'Teknik Sipil',
        'unit_type' => 'PRODI',
    ]);
    organizationStep($this, $request, 'submit');
    organizationStep($this, $request, 'return', ['reason' => 'Nama belum lengkap'])->assertSessionHasNoErrors();

    expect($request->refresh()->status)->toBe(ChangeRequest::DRAFT);

    $this->get(route('organization.admin.requests.edit', $request->public_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/organization/request-form')
            ->where('changeRequest.payload.name', 'Teknik Sipil'));

    $this->withHeaders(organizationKey())
        ->put(route('organization.admin.requests.update', $request->public_id), [
            'lock_version' => $request->lock_version,
            'name' => 'Program Studi Teknik Sipil dan Perencanaan',
            'unit_type' => 'PRODI',
            'reason' => 'Penataan program studi',
            'proposed_effective_from' => businessToday(),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('organization.admin.requests.show', $request->public_id));

    expect($request->refresh()->payload['name'])->toBe('Program Studi Teknik Sipil dan Perencanaan')
        ->and($request->kind)->toBe(ChangeRequest::UPDATE_UNIT);

    // A stale version is refused and the change is kept as it was (IX-002).
    $this->withHeaders(organizationKey())
        ->put(route('organization.admin.requests.update', $request->public_id), [
            'lock_version' => 0,
            'name' => 'Nama Lama',
            'unit_type' => 'PRODI',
            'reason' => 'Penataan program studi',
            'proposed_effective_from' => businessToday(),
        ])
        ->assertStatus(409);

    expect($request->refresh()->payload['name'])->toBe('Program Studi Teknik Sipil dan Perencanaan');

    organizationStep($this, $request, 'submit')->assertSessionHasNoErrors();

    expect($request->refresh()->status)->toBe(ChangeRequest::VALIDATED);
});
