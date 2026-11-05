<?php

use App\Models\User;
use App\Modules\Access\Actions\AssignProcessRole;
use App\Modules\Access\Actions\CloseAssignment;
use App\Modules\Access\Contracts\AssignmentSubjectType;
use App\Modules\Access\Contracts\SodRuleSource;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Access\Support\AccessEvaluator;
use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\AssignmentRoles;
use App\Modules\Access\Support\AssignmentSubjects;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\Separation\SeparationOfDuties;
use App\Modules\Access\Support\Separation\SodRule;
use App\Modules\Identity\Actions\RequestAssistedReset;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessProfileSeeder;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\DatabaseSession;
use Tests\TestCase;

/*
 * Process assignments and the separation of duties (TASK-015, ACCESS_CONTROL sections 5 and 6): the role catalog, assigning,
 * ending and revoking through PG-504 and the panel, the rules SOD-04 to SOD-09 and rules from a policy, the evaluator layers 3
 * and 4 by assignment, the suggestions from appointments and the decision holder as a privileged account (TAC-01 to TAC-06).
 *
 * The modules that own packages, contracts and warehouse requests come later, so a probe type stands in for them here: packages
 * 1 and 3 and contract 4 of package 1 lie in FT.TI, and warehouse request 2, requested by the user named in $requester, in FT.
 */

beforeEach(function () {
    $this->seed([OrganizationSeeder::class, AccessProfileSeeder::class]);
    $this->requester = User::factory()->withTwoFactor()->create(['name' => 'Pemohon Barang']);
    $this->packagePpk = User::factory()->withTwoFactor()->create(['name' => 'PPK Paket']);

    $units = OrgUnit::query()->whereIn('code', ['FT.TI', 'FT'])->pluck('id', 'code');
    $objects = [
        'probe.package' => [AssignmentRoles::PACKAGE, [
            1 => ['PKT-1', 'Pengadaan laptop', (int) $units['FT.TI'], []],
            3 => ['PKT-3', 'Pengadaan proyektor', (int) $units['FT.TI'], []],
        ]],
        'probe.request' => [AssignmentRoles::WAREHOUSE_REQUEST, [2 => ['PB-2', 'Permintaan kertas', (int) $units['FT'], ['requester' => $this->requester->id]]]],
        // A contract of package 1, which names the PPK of its package as a participant for a rule across objects.
        'probe.contract' => [AssignmentRoles::CONTRACT, [4 => ['KTR-4', 'Kontrak laptop', (int) $units['FT.TI'], ['package_ppk' => $this->packagePpk->id]]]],
    ];

    foreach ($objects as $key => [$kind, $rows]) {
        app(AssignmentSubjects::class)->register(new class($key, $kind, $rows) implements AssignmentSubjectType
        {
            /**
             * @param  array<int, array{string, string, int, array<string, int>}>  $rows
             */
            public function __construct(private string $key, private string $kind, private array $rows) {}

            public function key(): string
            {
                return $this->key;
            }

            public function kind(): string
            {
                return $this->kind;
            }

            public function assignCapability(): string
            {
                return 'procurement.package.manage';
            }

            public function idOf(string $reference): ?int
            {
                foreach ($this->rows as $id => $row) {
                    if ($row[0] === $reference) {
                        return $id;
                    }
                }

                return null;
            }

            public function accessSubject(int $id): ?AccessSubject
            {
                $row = $this->rows[$id] ?? null;

                return $row === null ? null : new AccessSubject(unitId: $row[2], participants: $row[3]);
            }

            public function describe(int $id): ?array
            {
                $row = $this->rows[$id] ?? null;

                return $row === null ? null : ['reference' => $row[0], 'label' => $row[1]];
            }
        });
    }
});

function assignmentAdmin(string $name = 'Admin Penugasan'): User
{
    return User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create(['name' => $name]);
}

function assignmentSubject(string $type = 'probe.package', int $id = 1): AccessSubject
{
    return app(AssignmentSubjects::class)->accessSubject($type, $id) ?? throw new LogicException("No probe object {$type} {$id}.");
}

function assignRole(
    User $actor,
    User $user,
    string $role,
    string $type = 'probe.package',
    int $id = 1,
    ?CarbonImmutable $from = null,
    ?CarbonImmutable $to = null,
): ProcessAssignment {
    return app(AssignProcessRole::class)->handle($actor, $type, $id, $role, $user, $from ?? BusinessDate::today(), $to, 'SK/REK/2026/101');
}

function assignmentReason(Closure $command): ?string
{
    try {
        $command();
    } catch (DomainRuleViolation $violation) {
        return $violation->reasonKey;
    }

    return null;
}

/**
 * @param  array<string, mixed>  $data
 */
function postAssignment(TestCase $test, array $data): TestResponse
{
    return $test->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])->post(route('access.admin.assignments.store'), [
        'subject_type' => 'probe.package',
        'subject' => 'PKT-1',
        'effective_from' => BusinessDate::today()->toDateString(),
        ...$data,
    ]);
}

/**
 * @param  array<string, mixed>  $data
 */
function closeAssignment(TestCase $test, ProcessAssignment $assignment, array $data): TestResponse
{
    return $test->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])->post(route('access.admin.assignments.close', $assignment->public_id), [
        'lock_version' => $assignment->refresh()->lock_version,
        ...$data,
    ]);
}

describe('the role catalog', function () {
    test('every role binds to a known kind of object, and every capability it gives is a capability of the catalog marked Penugasan', function () {
        $kinds = [AssignmentRoles::PACKAGE, AssignmentRoles::CONTRACT, AssignmentRoles::WAREHOUSE_REQUEST, AssignmentRoles::RETURN, AssignmentRoles::ASSET_CASE, AssignmentRoles::RECON_CASE];

        foreach (AssignmentRoles::all() as $role) {
            expect($role)->toMatch('/^[A-Z][A-Z0-9_]*$/')
                ->and(AssignmentRoles::kindsOf($role))->not->toBeEmpty()
                ->and(array_diff(AssignmentRoles::kindsOf($role), $kinds))->toBe([]);
        }

        foreach (AssignmentRoles::capabilitiesWithRoles() as $capability) {
            expect(CapabilityCatalog::exists($capability))->toBeTrue("{$capability} is not in the catalog")
                ->and(array_diff(AssignmentRoles::givingCapability($capability), AssignmentRoles::all()))->toBe([]);
        }

        expect(AssignmentRoles::isDecision('PPK'))->toBeTrue()
            ->and(AssignmentRoles::isDecision('PLANNER'))->toBeFalse()
            ->and(AssignmentRoles::forKind(AssignmentRoles::WAREHOUSE_REQUEST))->toBe(['REQUEST_APPROVER']);
    });

    test('a capability that is not grantable comes only from an assignment', function () {
        foreach (CapabilityCatalog::all() as $capability) {
            if (! CapabilityCatalog::isGrantable($capability)) {
                expect(AssignmentRoles::givingCapability($capability))->not->toBeEmpty("{$capability} has no role that gives it");
            }
        }
    });

    // The evaluator lets a role that decides nothing give its capability while decisions wait, so no such role gives one.
    test('a decision capability is given only by decision roles', function () {
        foreach (AssignmentRoles::capabilitiesWithRoles() as $capability) {
            if (CapabilityCatalog::isDecision($capability)) {
                expect(array_filter(AssignmentRoles::givingCapability($capability), fn (string $role) => ! AssignmentRoles::isDecision($role)))
                    ->toBe([], "{$capability} is given by a role that decides nothing");
            }
        }
    });
});

describe('assigning', function () {
    test('an assignment records its object, role, period, decree, its separation check and an audit event', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->withTwoFactor()->create();

        $assignment = assignRole($admin, $user, 'HPS_SETTER');
        $audit = DB::table('audit_events')->where('action', 'access.assignment_given')->sole();

        expect($assignment->status)->toBe(ProcessAssignment::ACTIVE)
            ->and($assignment->subject_type)->toBe('probe.package')
            ->and($assignment->subject_id)->toBe(1)
            ->and($assignment->assigned_by)->toBe($admin->id)
            ->and($assignment->decree_ref)->toBe('SK/REK/2026/101')
            ->and($assignment->isInForce())->toBeTrue()
            ->and($assignment->sod_check['passed'])->toBeTrue()
            ->and(collect($assignment->sod_check['rules'])->pluck('id')->all())->toBe(['SOD-08'])
            ->and($audit->subject_type)->toBe('process_assignments')
            ->and($audit->actor_user_id)->toBe($admin->id);
    });

    test('nobody assigns themselves, a vendor or a deactivated account, nor a role the object does not carry or twice', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->withTwoFactor()->create();
        assignRole($admin, $user, 'PLANNER');

        expect(assignmentReason(fn () => assignRole($admin, $admin, 'PLANNER')))->toBe('access.cannot_assign_self')
            ->and(assignmentReason(fn () => assignRole($admin, User::factory()->inRoleGroup(User::VENDOR)->create(), 'PLANNER')))->toBe('access.assignment_role_group')
            ->and(assignmentReason(fn () => assignRole($admin, User::factory()->withStatus(User::DEACTIVATED)->create(), 'PLANNER')))->toBe('access.account_deactivated')
            ->and(assignmentReason(fn () => assignRole($admin, $user, 'REQUEST_APPROVER')))->toBe('access.assignment_role_not_for_object')
            ->and(assignmentReason(fn () => assignRole($admin, $user, 'PLANNER', id: 99)))->toBe('access.assignment_object_unknown')
            ->and(assignmentReason(fn () => assignRole($admin, $user, 'PLANNER')))->toBe('access.assignment_exists')
            ->and(assignmentReason(fn () => assignRole($admin, $user, 'PPK', from: BusinessDate::today()->subDay())))->toBe('access.effective_from_in_past')
            ->and(ProcessAssignment::query()->count())->toBe(1);
    });

    test('the same role given again after its assignment lapsed closes the lapsed one as ENDED, with its audit event', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->withTwoFactor()->create();
        $lapsed = assignRole($admin, $user, 'PLANNER', to: BusinessDate::today()->addDay());

        $this->travelTo(BusinessDate::today()->addDays(2)->setTime(9, 0));

        expect($lapsed->refresh()->isLapsed())->toBeTrue();

        $again = assignRole($admin, $user, 'PLANNER');

        expect($lapsed->refresh()->status)->toBe(ProcessAssignment::ENDED)
            ->and($lapsed->closed_by)->toBe($admin->id)
            ->and($lapsed->close_reason)->toBe(AssignProcessRole::LAPSED)
            ->and($again->status)->toBe(ProcessAssignment::ACTIVE)
            ->and(DB::table('audit_events')->where('action', 'access.assignment_ended')->count())->toBe(1)
            ->and(assignmentReason(fn () => assignRole($admin, $user, 'PLANNER')))->toBe('access.assignment_exists');
    });

    test('the table refuses a self-assignment, a second active row and a closed row without who closed it', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->create();
        $row = [
            'public_id' => strtolower((string) Str::ulid()),
            'subject_type' => 'probe.package',
            'subject_id' => 1,
            'assignment_role' => 'PLANNER',
            'user_id' => $user->id,
            'effective_from' => now(),
            'sod_check' => '{}',
            'assigned_by' => $admin->id,
            'status' => 'ACTIVE',
        ];

        expect(DatabaseSession::sqlStateAs(null, fn () => DB::table('process_assignments')->insert([...$row, 'assigned_by' => $user->id])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('process_assignments')->insert([...$row, 'status' => 'ENDED'])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('process_assignments')->insert([...$row, 'sod_check' => '[]'])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('process_assignments')->insert($row)))->toBeNull();

        // sqlStateAs always rolls back, so the first active row is kept outside it before the second one is refused.
        DB::table('process_assignments')->insert($row);

        expect(DatabaseSession::sqlStateAs(null, fn () => DB::table('process_assignments')->insert([...$row, 'public_id' => strtolower((string) Str::ulid())])))->toBe('23505');
    });
});

describe('the separation of duties', function () {
    // TAC-01, SOD-06.
    test('the requester of goods cannot be assigned to approve their own request, and the refusal names the rule', function () {
        $admin = assignmentAdmin();

        try {
            assignRole($admin, $this->requester, 'REQUEST_APPROVER', 'probe.request', 2);
            $this->fail('The assignment was not refused.');
        } catch (DomainRuleViolation $violation) {
            expect($violation->reasonKey)->toBe('access.sod_violation')
                ->and($violation->getMessage().implode(' ', $violation->errors()['_domain']))->toContain('SOD-06');
        }

        expect(ProcessAssignment::query()->count())->toBe(0);

        $approver = assignRole($admin, User::factory()->withTwoFactor()->create(), 'REQUEST_APPROVER', 'probe.request', 2);

        expect(collect($approver->sod_check['rules'])->where('id', 'SOD-06')->first()['passed'])->toBeTrue();
    });

    // TAC-02, SOD-08.
    test('a Super Admin cannot hold a decision role, but may hold a role that decides nothing', function () {
        $admin = assignmentAdmin();
        $otherAdmin = assignmentAdmin('Admin Kedua');

        foreach (['PPK', 'HPS_SETTER', 'WINNER_DETERMINER'] as $role) {
            expect(assignmentReason(fn () => assignRole($admin, $otherAdmin, $role)))->toBe('access.sod_violation');
        }

        expect(assignRole($admin, $otherAdmin, 'PLANNER')->status)->toBe(ProcessAssignment::ACTIVE);
    });

    // TAC-05: a rule from a policy is data, decided by the same evaluator, when assigning and at the action.
    test('rules from a policy are applied without code for each rule, also at the action for roles held before the rule came', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->withTwoFactor()->create();
        assignRole($admin, $user, 'HPS_SETTER');
        assignRole($admin, $user, 'WINNER_DETERMINER');

        expect($user->can('procurement.hps.set', assignmentSubject()))->toBeTrue();

        app(SeparationOfDuties::class)->addSource(new class implements SodRuleSource
        {
            public function rules(): iterable
            {
                yield SodRule::fromArray([
                    'id' => 'SOD-02',
                    'kind' => 'exclusive_roles',
                    'roles' => ['HPS_SETTER'],
                    'other_roles' => ['WINNER_DETERMINER', 'POKJA_MEMBER'],
                    'description' => ['id' => 'Penyusun HPS tidak boleh menetapkan pemenang.', 'en' => 'The HPS setter may not determine the winner.'],
                ], 'procurement.authority@v-test', true);
            }
        });

        $other = User::factory()->withTwoFactor()->create();
        assignRole($admin, $other, 'HPS_SETTER');

        try {
            assignRole($admin, $other, 'POKJA_MEMBER');
            $this->fail('The assignment was not refused.');
        } catch (DomainRuleViolation $violation) {
            expect($violation->errors()['_domain'][0])->toContain('SOD-02')->toContain('Penyusun HPS tidak boleh menetapkan pemenang.');
        }

        $decision = app(AccessEvaluator::class)->decide($user, 'procurement.hps.set', assignmentSubject());
        $result = app(SeparationOfDuties::class)->forAssignment($other, 'PLANNER', assignmentSubject())->toArray();

        expect($decision->allowed)->toBeFalse()
            ->and($decision->reasonKey)->toBe('access.denied.separation_of_duties')
            ->and($other->can('procurement.hps.set', assignmentSubject()))->toBeTrue()
            ->and($result['passed'])->toBeTrue();

        $rule = collect(app(SeparationOfDuties::class)->forAction($user, 'procurement.hps.set', assignmentSubject())->toArray()['rules'])->firstWhere('id', 'SOD-02');

        expect($rule)->toBe(['id' => 'SOD-02', 'source' => 'procurement.authority@v-test', 'simulation' => true, 'passed' => false]);
    });

    test('a rule that misses what its kind compares is refused when it is read', function () {
        expect(fn () => SodRule::fromArray(['id' => 'SOD-01', 'kind' => 'exclusive_roles', 'roles' => ['PPK']], 'x', false))->toThrow(InvalidArgumentException::class)
            ->and(fn () => SodRule::fromArray(['id' => 'SOD-01', 'kind' => 'unknown', 'roles' => ['PPK']], 'x', false))->toThrow(InvalidArgumentException::class);
    });

    test('at the action the actor is compared with the people who took part in the object', function () {
        $admin = assignmentAdmin();
        $approver = User::factory()->withTwoFactor()->create();
        assignRole($admin, $approver, 'REQUEST_APPROVER', 'probe.request', 2);

        $subject = assignmentSubject('probe.request', 2);

        expect($approver->can('inventory.request.decide', $subject))->toBeTrue()
            ->and($approver->can('inventory.request.decide', $subject->withParticipants(['requester' => [$approver->id]])))->toBeFalse();
    });

    // SOD-02 across objects: the PPK of the package against the PjPHP of its contract, through the participants.
    test('a rule on a role binds the actor only through that role, not through another role giving the same action', function () {
        $admin = assignmentAdmin();
        app(SeparationOfDuties::class)->addSource(new class implements SodRuleSource
        {
            public function rules(): iterable
            {
                yield SodRule::fromArray([
                    'id' => 'SOD-02',
                    'kind' => SodRule::DISTINCT_PARTICIPANTS,
                    'roles' => ['PJPHP'],
                    'participants' => ['package_ppk'],
                    'description' => ['id' => 'PPK paket tidak boleh menjadi PjPHP kontraknya.'],
                ], 'procurement.authority@v-test', false);
            }
        });

        expect(assignmentReason(fn () => assignRole($admin, $this->packagePpk, 'PJPHP', 'probe.contract', 4)))->toBe('access.sod_violation');

        assignRole($admin, $this->packagePpk, 'TECHNICAL_TEAM', 'probe.contract', 4);
        $pjphp = User::factory()->withTwoFactor()->create();
        assignRole($admin, $pjphp, 'PJPHP', 'probe.contract', 4);

        expect($this->packagePpk->can('procurement.execution.record', assignmentSubject('probe.contract', 4)))->toBeTrue()
            ->and($pjphp->can('procurement.execution.record', assignmentSubject('probe.contract', 4)))->toBeTrue();
    });
});

describe('the evaluator and assignments (TAC-03)', function () {
    test('a capability marked Penugasan holds only on the object of an assignment in force, for its holder', function () {
        $admin = assignmentAdmin();
        $holder = User::factory()->withTwoFactor()->create();
        $other = User::factory()->withTwoFactor()->create();
        $assignment = assignRole($admin, $holder, 'HPS_SETTER');

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeTrue()
            ->and($holder->can('procurement.hps.set'))->toBeTrue()
            ->and($holder->can('procurement.method.determine', assignmentSubject()))->toBeFalse()
            ->and(app(AccessEvaluator::class)->decide($other, 'procurement.hps.set', assignmentSubject())->reasonKey)->toBe('access.denied.no_assignment')
            ->and($admin->can('procurement.hps.set', assignmentSubject()))->toBeFalse();

        app(CloseAssignment::class)->handle($admin, $assignment, ProcessAssignment::ENDED, 'Tugas sudah selesai', $assignment->lock_version);

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeFalse()
            ->and($assignment->refresh()->status)->toBe(ProcessAssignment::ENDED)
            ->and(DB::table('audit_events')->where('action', 'access.assignment_ended')->count())->toBe(1);
    });

    test('an assignment from a later day and one past its end give nothing', function () {
        $admin = assignmentAdmin();
        $holder = User::factory()->withTwoFactor()->create();
        assignRole($admin, $holder, 'HPS_SETTER', from: BusinessDate::today()->addDay());

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeFalse();

        $this->travelTo(BusinessDate::today()->addDays(2)->setTime(9, 0));

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeTrue();

        ProcessAssignment::query()->update(['effective_to' => now()->subMinute()]);

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeFalse();
    });

    // AUTHZ-24, SEC-21: decisions wait until the holder changed the password and set up the required TOTP.
    test('a decision role waits for a changed password and the required TOTP, a role that decides nothing does not', function () {
        $admin = assignmentAdmin();
        $holder = User::factory()->create();
        assignRole($admin, $holder, 'HPS_SETTER');
        assignRole($admin, $holder, 'PLANNER');

        expect(app(AccessEvaluator::class)->decide($holder, 'procurement.hps.set', assignmentSubject())->reasonKey)->toBe('access.denied.credentials_pending')
            ->and($holder->can('procurement.activity.manage', assignmentSubject()))->toBeTrue();

        $holder = User::factory()->withTwoFactor()->create(['must_change_password' => true]);
        assignRole($admin, $holder, 'HPS_SETTER');

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeFalse();

        $holder->forceFill(['must_change_password' => false])->save();

        expect($holder->can('procurement.hps.set', assignmentSubject()))->toBeTrue();
    });

    test('a decision a grant allows waits for the credentials of a decision holder too', function () {
        $admin = assignmentAdmin();
        $holder = User::factory()->withTwoFactor()->create(['must_change_password' => true]);
        assignRole($admin, $holder, 'HPS_SETTER');
        DB::table('access_grants')->insert([
            'public_id' => strtolower((string) Str::ulid()),
            'user_id' => $holder->id,
            'capability' => 'recon.case.accept',
            'scope_type' => 'ORG_UNIT_SUBTREE',
            'scope_id' => OrgUnit::query()->where('code', 'FT')->value('id'),
            'effective_from' => BusinessDate::today()->toDateString(),
            'reason' => 'Penerima rekonsiliasi fakultas',
            'requires_approval' => false,
            'granted_by' => $admin->id,
            'status' => 'ACTIVE',
        ]);

        expect(app(AccessEvaluator::class)->decide($holder, 'recon.case.accept')->reasonKey)->toBe('access.denied.credentials_pending');

        $holder->forceFill(['must_change_password' => false])->save();

        expect($holder->can('recon.case.accept'))->toBeTrue();
    });

    test('a role that decides nothing keeps giving its capability while a decision role on the object waits', function () {
        $admin = assignmentAdmin();
        $holder = User::factory()->create();
        assignRole($admin, $holder, 'PLANNER');
        assignRole($admin, $holder, 'POKJA_CHAIR');

        expect($holder->can('procurement.selection.manage', assignmentSubject()))->toBeTrue()
            ->and(app(AccessEvaluator::class)->decide($holder, 'procurement.evaluation_scheme.manage', assignmentSubject())->reasonKey)->toBe('access.denied.credentials_pending');
    });
});

// TAC-06, AUTHZ-24, SEC-21.
describe('the holder of a decision role is a privileged account', function () {
    test('one administrator alone cannot reset the password of an active decision holder; after the assignment ended they can', function () {
        $admin = assignmentAdmin();
        $holder = User::factory()->withTwoFactor()->create();
        $planner = User::factory()->withTwoFactor()->create();
        $assignment = assignRole($admin, $holder, 'PPK', from: BusinessDate::today()->addDay());
        assignRole($admin, $planner, 'PLANNER');

        expect(app(PrivilegedAccounts::class)->isPrivileged($holder))->toBeTrue()
            ->and(app(PrivilegedAccounts::class)->isPrivileged($planner))->toBeFalse();

        $reset = app(RequestAssistedReset::class)->handle($admin, $holder, 'STAFF_CARD', 'Kartu pegawai diperiksa');

        expect($reset['reset']->status)->toBe(PasswordReset::PENDING_APPROVAL)
            ->and($reset['password'])->toBeNull();

        app(CloseAssignment::class)->handle($admin, $assignment, ProcessAssignment::REVOKED, 'Salah orang', $assignment->lock_version);
        $reset['reset']->forceFill(['status' => PasswordReset::REVOKED])->save();

        expect(app(PrivilegedAccounts::class)->isPrivileged($holder))->toBeFalse()
            ->and(app(RequestAssistedReset::class)->handle($admin, $holder, 'STAFF_CARD', null)['password'])->not->toBeNull();
    });

    test('a temporary password one administrator issued alone keeps its holder out of a decision role until it is replaced', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->withTwoFactor()->create();
        $reset = app(RequestAssistedReset::class)->handle($admin, $user, 'STAFF_CARD', null)['reset'];

        expect(assignmentReason(fn () => assignRole($admin, $user, 'PPK')))->toBe('access.decision_role_reset_open')
            ->and(assignRole($admin, $user, 'PLANNER')->status)->toBe(ProcessAssignment::ACTIVE);

        $reset->forceFill(['status' => PasswordReset::REVOKED])->save();

        expect(assignRole($admin, $user, 'PPK')->status)->toBe(ProcessAssignment::ACTIVE);
    });
});

describe('PG-504 and the assignment panel', function () {
    test('a guest is sent to the sign-in page and an account without admin.access.grant cannot open or change assignments', function () {
        $this->get(route('access.admin.assignments.index'))->assertRedirect(route('login'));

        $assignment = assignRole(assignmentAdmin(), User::factory()->create(), 'PLANNER');

        foreach ([User::factory()->withTwoFactor()->create(), User::factory()->inRoleGroup(User::VENDOR)->create()] as $user) {
            $this->actingAs($user);

            $this->get(route('access.admin.assignments.index'))->assertForbidden();
            $this->get(route('access.admin.assignments.create'))->assertForbidden();
            postAssignment($this, ['assignment_role' => 'PLANNER', 'username' => User::factory()->create()->username])->assertForbidden();
            closeAssignment($this, $assignment, ['status' => ProcessAssignment::REVOKED, 'reason' => 'Tanpa izin'])->assertForbidden();
        }

        expect(ProcessAssignment::query()->count())->toBe(1)
            ->and($assignment->refresh()->status)->toBe(ProcessAssignment::ACTIVE);
    });

    test('every assignment route requires sign-in and confirmed TOTP, and the list and object page the assignment administration gate', function () {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'access.admin.assignments.'))
            ->keyBy(fn ($route) => $route->getName());

        expect($routes->keys()->all())->toEqualCanonicalizing([
            'access.admin.assignments.index',
            'access.admin.assignments.create',
            'access.admin.assignments.store',
            'access.admin.assignments.close',
        ]);

        foreach ($routes as $name => $route) {
            expect($route->gatherMiddleware())->toContain('auth', 'two-factor');

            if (in_array($name, ['access.admin.assignments.index', 'access.admin.assignments.create'], true)) {
                expect($route->gatherMiddleware())->toContain('can:access.administer_assignments');
            } else {
                expect($route->gatherMiddleware())->toContain('idempotent');
            }
        }
    });

    test('the list shows the assignments with their object and filters them', function () {
        $admin = assignmentAdmin();
        assignRole($admin, User::factory()->create(['name' => 'Siti Aminah', 'username' => 'siti']), 'PLANNER');
        assignRole($admin, User::factory()->withTwoFactor()->create(['name' => 'Budi', 'username' => 'budi']), 'PPK');

        $this->actingAs($admin)
            ->get(route('access.admin.assignments.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/assignments/index')
                ->has('assignments.data', 2)
                ->where('assignments.data.0.subject', ['type' => 'probe.package', 'reference' => 'PKT-1', 'label' => 'Pengadaan laptop'])
                ->where('assignments.data.0.can_close', false)
                ->where('types', [['key' => 'probe.package', 'kind' => 'package'], ['key' => 'probe.request', 'kind' => 'warehouse_request'], ['key' => 'probe.contract', 'kind' => 'contract']]));

        $this->get(route('access.admin.assignments.index', ['q' => 'SITI', 'role' => 'PLANNER', 'status' => 'ACTIVE']))
            ->assertInertia(fn (Assert $page) => $page->has('assignments.data', 1)->where('assignments.data.0.user.username', 'siti')
                ->where('filters', ['q' => 'SITI', 'status' => 'ACTIVE', 'role' => 'PLANNER']));

        $this->get(route('access.admin.assignments.index', ['q' => ['x'], 'role' => 'BOGUS', 'status' => 'BOGUS']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('assignments.data', 2)->where('filters', ['q' => '', 'status' => '', 'role' => '']));

        $this->get(route('access.admin.assignments.index', ['role' => ['PLANNER']]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('assignments.data', 2)->where('filters.role', ''));
    });

    test('an active assignment past its end is listed and shown with the ended ones', function () {
        $admin = assignmentAdmin();
        assignRole($admin, User::factory()->create(['username' => 'lewat']), 'PLANNER', to: BusinessDate::today()->addDay());
        assignRole($admin, User::factory()->create(['username' => 'berlaku']), 'PLANNER', id: 3);

        $this->travelTo(BusinessDate::today()->addDays(2)->setTime(9, 0));
        $this->actingAs($admin);

        $this->get(route('access.admin.assignments.index', ['status' => 'ACTIVE']))
            ->assertInertia(fn (Assert $page) => $page->has('assignments.data', 1)->where('assignments.data.0.user.username', 'berlaku'));
        $this->get(route('access.admin.assignments.index', ['status' => 'ENDED']))
            ->assertInertia(fn (Assert $page) => $page->has('assignments.data', 1)->where('assignments.data.0.user.username', 'lewat'));
        $this->get(route('access.admin.assignments.create', ['type' => 'probe.package', 'reference' => 'PKT-1']))
            ->assertInertia(fn (Assert $page) => $page->where('panel.assignments.0.status', ProcessAssignment::ACTIVE)
                ->where('panel.assignments.0.lapsed', true));
    });

    // TAC-04: an appointment only suggests.
    test('the object page shows the panel with the people appointed in its unit as suggestions, and assigns nobody by itself', function () {
        $admin = assignmentAdmin();
        $kaprodi = User::factory()->create(['name' => 'Kaprodi TI', 'username' => 'kaprodi.ti']);
        $positionId = DB::table('org_positions')->where('code', 'KAPRODI_TI')->value('id');
        DB::table('org_appointments')->insert([
            'user_id' => $kaprodi->id,
            'position_id' => $positionId,
            'effective_from' => BusinessDate::today()->toDateString(),
            'decree_ref' => 'SK/REK/2026/045',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($admin)
            ->get(route('access.admin.assignments.create', ['type' => 'probe.package', 'reference' => 'PKT-1']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/assignments/create')
                ->where('notFound', false)
                ->where('panel.subject', ['type' => 'probe.package', 'reference' => 'PKT-1', 'label' => 'Pengadaan laptop'])
                ->where('panel.can.assign', true)
                ->where('panel.assignments', [])
                ->has('panel.suggestions', 1)
                ->where('panel.suggestions.0.username', 'kaprodi.ti')
                ->where('panel.suggestions.0.decree_ref', 'SK/REK/2026/045')
                ->where('panel.roles', fn ($roles) => collect($roles)->pluck('value')->all() === AssignmentRoles::forKind(AssignmentRoles::PACKAGE)));

        expect(ProcessAssignment::query()->count())->toBe(0);

        $this->get(route('access.admin.assignments.create', ['type' => 'probe.package', 'reference' => 'PKT-404']))
            ->assertInertia(fn (Assert $page) => $page->where('panel', null)->where('notFound', true));
        $this->get(route('access.admin.assignments.create'))
            ->assertInertia(fn (Assert $page) => $page->where('panel', null)->where('notFound', false));
    });

    test('an administrator assigns through the panel and ends or revokes with a reason', function () {
        $admin = assignmentAdmin();
        $user = User::factory()->withTwoFactor()->create(['username' => 'budi.santoso']);
        $this->actingAs($admin);

        postAssignment($this, ['assignment_role' => 'PPK', 'username' => 'budi.santoso', 'decree_ref' => 'SK/1'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $assignment = ProcessAssignment::query()->sole();

        expect($assignment->user_id)->toBe($user->id)->and($assignment->decree_ref)->toBe('SK/1');

        postAssignment($this, ['assignment_role' => 'PPK', 'username' => 'nobody'])->assertSessionHasErrors('username');
        postAssignment($this, ['assignment_role' => 'PPK', 'username' => 'budi.santoso', 'subject' => 'PKT-404'])->assertNotFound();
        postAssignment($this, ['assignment_role' => 'PPK', 'username' => assignmentAdmin('Admin Lain')->username])->assertSessionHasErrors('_domain');
        closeAssignment($this, $assignment, ['status' => ProcessAssignment::ACTIVE, 'reason' => 'Bukan penutupan'])->assertSessionHasErrors('status');
        closeAssignment($this, $assignment, ['status' => ProcessAssignment::REVOKED, 'reason' => 'x'])->assertSessionHasErrors('reason');

        closeAssignment($this, $assignment, ['status' => ProcessAssignment::REVOKED, 'reason' => 'Salah orang'])->assertSessionHasNoErrors();

        expect($assignment->refresh()->status)->toBe(ProcessAssignment::REVOKED)
            ->and($assignment->closed_by)->toBe($admin->id)
            ->and($assignment->close_reason)->toBe('Salah orang')
            ->and(DB::table('audit_events')->where('action', 'access.assignment_revoked')->value('reason'))->toBe('Salah orang');

        closeAssignment($this, $assignment, ['status' => ProcessAssignment::ENDED, 'reason' => 'Tugas selesai'])->assertSessionHasErrors('_domain');
    });

    test('a module user who may manage the object assigns from its panel without opening PG-504', function () {
        $manager = User::factory()->withTwoFactor()->create();
        DB::table('access_grants')->insert([
            'public_id' => strtolower((string) Str::ulid()),
            'user_id' => $manager->id,
            'capability' => 'procurement.package.manage',
            'scope_type' => 'ORG_UNIT_SUBTREE',
            'scope_id' => OrgUnit::query()->where('code', 'FT')->value('id'),
            'effective_from' => BusinessDate::today()->toDateString(),
            'reason' => 'Pengelola paket fakultas',
            'requires_approval' => false,
            'granted_by' => assignmentAdmin()->id,
            'status' => 'ACTIVE',
        ]);
        User::factory()->create(['username' => 'perencana']);

        expect(app(AssignmentSubjects::class)->mayAssign($manager, 'probe.package', 1))->toBeTrue()
            ->and(app(AssignmentSubjects::class)->mayAssign(User::factory()->create(), 'probe.package', 1))->toBeFalse();

        $this->actingAs($manager);
        $this->get(route('access.admin.assignments.index'))->assertForbidden();
        // Whether a reference exists is not told to someone who may not open every object.
        postAssignment($this, ['assignment_role' => 'PLANNER', 'username' => 'perencana', 'subject' => 'PKT-404'])->assertForbidden();
        postAssignment($this, ['assignment_role' => 'PLANNER', 'username' => 'perencana'])->assertSessionHasNoErrors();

        expect(ProcessAssignment::query()->sole()->assigned_by)->toBe($manager->id);
    });
});
