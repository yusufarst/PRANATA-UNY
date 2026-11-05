<?php

namespace App\Modules\Access\Actions;

use App\Models\User;
use App\Modules\Access\Events\AssignmentClosed;
use App\Modules\Access\Events\AssignmentGiven;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Access\Support\AssignmentRoles;
use App\Modules\Access\Support\AssignmentSubjects;
use App\Modules\Access\Support\Separation\SeparationOfDuties;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Assign a user a role on one object (ACCESS_CONTROL section 5), with the decree that appoints them when there is one. The
 * caller may assign on the object ({@see AssignmentSubjects::mayAssign()}).
 *
 * Nobody assigns themselves. The separation-of-duties rules decide before anything is written: a broken rule refuses the
 * assignment with its reason (TAC-01), so a SUPER_ADMIN never holds a decision role (SOD-08, TAC-02), and the rules that held
 * are kept as sod_check. A suggestion from an appointment is never an assignment until someone makes it here (TAC-04).
 *
 * The assignments of one object are decided one at a time, so two people never pass the same rule against each other.
 */
final class AssignProcessRole extends Action
{
    /**
     * The reason kept on an assignment that lapsed and was closed when the same role was given again.
     */
    public const LAPSED = 'Masa penugasan sudah berakhir';

    public function __construct(
        private readonly AssignmentSubjects $subjects,
        private readonly SeparationOfDuties $separation,
    ) {}

    public function handle(
        User $actor,
        string $type,
        int $objectId,
        string $role,
        User $user,
        CarbonImmutable $effectiveFrom,
        ?CarbonImmutable $effectiveTo,
        ?string $decreeRef,
    ): ProcessAssignment {
        return $this->transaction(function () use ($actor, $type, $objectId, $role, $user, $effectiveFrom, $effectiveTo, $decreeRef): ProcessAssignment {
            DB::select('select pg_advisory_xact_lock(hashtext(?))', ["process_assignments:{$type}:{$objectId}"]);

            if ($user->is($actor)) {
                throw DomainRuleViolation::because('access.cannot_assign_self');
            }

            // The user row is locked as an assisted reset locks it, so a reset never passes as unprivileged while this user
            // becomes the holder of a decision role (SEC-21).
            $user = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('access.account_deactivated');
            }

            if ($user->role_group === User::VENDOR) {
                throw DomainRuleViolation::because('access.assignment_role_group');
            }

            $kind = $this->subjects->find($type)?->kind();
            $subject = $kind === null ? null : $this->subjects->accessSubject($type, $objectId);

            if ($subject === null) {
                throw DomainRuleViolation::because('access.assignment_object_unknown');
            }

            if (! in_array($kind, AssignmentRoles::kindsOf($role), true)) {
                throw DomainRuleViolation::because('access.assignment_role_not_for_object');
            }

            $today = BusinessDate::today();

            if ($effectiveFrom->lt($today)) {
                throw DomainRuleViolation::because('access.effective_from_in_past');
            }

            if ($effectiveTo !== null && $effectiveTo->lte($effectiveFrom)) {
                throw DomainRuleViolation::because('access.effective_to_before_start');
            }

            $same = fn () => ProcessAssignment::query()->onObject($type, $objectId)->where('assignment_role', $role)->where('user_id', $user->id);

            if ($same()->current()->exists()) {
                throw DomainRuleViolation::because('access.assignment_exists');
            }

            $sod = $this->separation->forAssignment($user, $role, $subject);

            if (! $sod->passed()) {
                throw DomainRuleViolation::because('access.sod_violation', ['rules' => $sod->explanation()]);
            }

            // The same role past its end is over: it is recorded as ENDED by whoever assigns again, with its audit event.
            foreach ($same()->lapsed()->get() as $lapsed) {
                $lapsed->forceFill([
                    'status' => ProcessAssignment::ENDED,
                    'closed_by' => $actor->id,
                    'closed_at' => now(),
                    'close_reason' => self::LAPSED,
                ])->saveAtVersion($lapsed->lock_version);

                AssignmentClosed::dispatch($lapsed, $actor);
            }

            $assignment = ProcessAssignment::query()->create([
                'subject_type' => $type,
                'subject_id' => $objectId,
                'assignment_role' => $role,
                'user_id' => $user->id,
                // An assignment from today holds at once; a later one from the start of its day in Asia/Jakarta. The columns
                // are written in UTC, the time zone of the connection.
                'effective_from' => $effectiveFrom->equalTo($today) ? now() : $effectiveFrom->utc(),
                'effective_to' => $effectiveTo?->utc(),
                'decree_ref' => $decreeRef,
                'sod_check' => $sod->toArray(),
                'assigned_by' => $actor->id,
                'status' => ProcessAssignment::ACTIVE,
            ]);

            // Inside the transaction, so the assignment and its audit record commit or roll back together (fail closed).
            AssignmentGiven::dispatch($assignment, $actor);

            return $assignment;
        });
    }
}
