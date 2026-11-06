<?php

namespace App\Modules\Access\Support;

use App\Models\User;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Organization\Queries\Appointments;
use App\Modules\Organization\Support\BusinessDate;

/**
 * The props of the assignment panel on one object (AssignmentPanel.tsx): the object, the roles it may carry, its assignments
 * with their history, the people appointed in its unit as suggestions and whether the actor may assign there. A module page
 * passes these props for its own object; PG-504 passes them for the object it opened.
 *
 * A suggestion only fills in the form: nobody is assigned until the actor saves (ACCESS_CONTROL section 5, TAC-04).
 */
final class AssignmentPanel
{
    public function __construct(private readonly AssignmentSubjects $subjects) {}

    /**
     * @return array<string, mixed>|null null when the type is not registered or the object does not exist
     */
    public function for(User $actor, string $type, int $id): ?array
    {
        $kind = $this->subjects->find($type)?->kind();
        $subject = $this->subjects->accessSubject($type, $id);
        $described = $this->subjects->find($type)?->describe($id);

        if ($kind === null || $subject === null || $described === null) {
            return null;
        }

        $mayAssign = $this->subjects->mayAssign($actor, $type, $id);
        $assignments = ProcessAssignment::query()
            ->onObject($type, $id)
            ->with('user:id,public_id,username,name')
            ->orderByRaw("case when status = 'ACTIVE' and (effective_to is null or effective_to > ?) then 0 else 1 end", [now()])
            ->orderBy('assignment_role')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        return [
            'subject' => ['type' => $type, ...$described],
            'roles' => array_map(fn (string $role): array => [
                'value' => $role,
                'decision' => AssignmentRoles::isDecision($role),
            ], AssignmentRoles::forKind($kind)),
            'assignments' => self::rows(array_values($assignments->all()), $mayAssign),
            'suggestions' => $mayAssign && $subject->unitId !== null ? self::suggestions($subject->unitId) : [],
            'can' => ['assign' => $mayAssign],
            'today' => BusinessDate::today()->toDateString(),
        ];
    }

    /**
     * The assignments as the pages show them; closing one needs the right to assign on its object, checked by the caller.
     *
     * @param  list<ProcessAssignment>  $assignments
     * @return list<array<string, mixed>>
     */
    public static function rows(array $assignments, bool $mayClose): array
    {
        $people = User::query()
            ->whereIn('id', array_values(array_unique(array_filter(array_merge(
                array_map(fn (ProcessAssignment $assignment): int => $assignment->assigned_by, $assignments),
                array_map(fn (ProcessAssignment $assignment): ?int => $assignment->closed_by, $assignments),
            )))))
            ->pluck('name', 'id');

        return array_map(fn (ProcessAssignment $assignment): array => [
            // The id names the row on its evidence page (PG-012, files/evidence/process_assignments/{id}).
            'id' => $assignment->id,
            'public_id' => $assignment->public_id,
            'assignment_role' => $assignment->assignment_role,
            'decision' => AssignmentRoles::isDecision($assignment->assignment_role),
            'user' => [
                'public_id' => $assignment->user->public_id,
                'username' => $assignment->user->username,
                'name' => $assignment->user->name,
            ],
            'status' => $assignment->status,
            // ACTIVE but not yet begun: shown as waiting for its first day.
            'upcoming' => $assignment->status === ProcessAssignment::ACTIVE && $assignment->effective_from->isFuture(),
            // ACTIVE but past its end: shown as history; closing it records the end.
            'lapsed' => $assignment->isLapsed(),
            'effective_from' => $assignment->effective_from->toIso8601String(),
            'effective_to' => $assignment->effective_to?->toIso8601String(),
            'decree_ref' => $assignment->decree_ref,
            'has_evidence' => $assignment->evidence_file_id !== null,
            'sod_rules' => array_values(array_map(
                fn (array $rule): string => (string) $rule['id'],
                (array) ($assignment->sod_check['rules'] ?? []),
            )),
            'assigned_by' => $people[$assignment->assigned_by] ?? null,
            'closed_by' => $assignment->closed_by === null ? null : ($people[$assignment->closed_by] ?? null),
            'closed_at' => $assignment->closed_at?->toIso8601String(),
            'close_reason' => $assignment->close_reason,
            'lock_version' => $assignment->lock_version,
            'can_close' => $mayClose && $assignment->status === ProcessAssignment::ACTIVE,
        ], $assignments);
    }

    /**
     * The people appointed in the unit of the object or above it, with their position, as suggestions only.
     *
     * @return list<array{username: string, name: string, position_name: string, unit_name: string, decree_ref: string|null}>
     */
    private static function suggestions(int $unitId): array
    {
        $appointments = Appointments::suggestionsFor($unitId);
        $users = User::query()
            ->whereIn('id', array_map(fn (array $appointment): int => $appointment['user_id'], $appointments))
            ->where('role_group', User::INTERNAL)
            ->get(['id', 'username', 'name'])
            ->keyBy('id');
        $suggestions = [];

        foreach ($appointments as $appointment) {
            $user = $users->get($appointment['user_id']);

            if ($user !== null) {
                $suggestions[] = [
                    'username' => $user->username,
                    'name' => $user->name,
                    'position_name' => $appointment['position_name'],
                    'unit_name' => $appointment['unit_name'],
                    'decree_ref' => $appointment['decree_ref'],
                ];
            }
        }

        return $suggestions;
    }
}
