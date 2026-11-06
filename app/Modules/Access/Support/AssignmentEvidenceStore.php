<?php

namespace App\Modules\Access\Support;

use App\Models\User;
use App\Modules\Access\Contracts\AssignmentEvidence;
use App\Modules\Access\Models\ProcessAssignment;
use Illuminate\Support\Facades\DB;

/**
 * The decree evidence of process assignments as Access exposes it ({@see AssignmentEvidence}). The column evidence_file_id
 * is Access's own and follows the newest active link the Files module keeps for the assignment.
 */
final class AssignmentEvidenceStore implements AssignmentEvidence
{
    public function __construct(private readonly AssignmentSubjects $subjects) {}

    public function find(int $assignmentId): ?array
    {
        $assignment = ProcessAssignment::query()->with('user:id,name')->find($assignmentId);

        if ($assignment === null) {
            return null;
        }

        $described = $this->subjects->find($assignment->subject_type)?->describe($assignment->subject_id);

        return [
            'id' => $assignment->id,
            'public_id' => $assignment->public_id,
            'user_id' => $assignment->user_id,
            'holder' => $assignment->user->name,
            'assignment_role' => $assignment->assignment_role,
            'subject_type' => $assignment->subject_type,
            'subject_id' => $assignment->subject_id,
            'reference' => $described['reference'] ?? null,
            'label' => $described['label'] ?? null,
            'decree_ref' => $assignment->decree_ref,
            'evidence_file_id' => $assignment->evidence_file_id,
        ];
    }

    public function mayRead(User $actor, int $assignmentId): bool
    {
        $assignment = ProcessAssignment::query()->find($assignmentId);

        if ($assignment === null) {
            return false;
        }

        return $assignment->user_id === $actor->id || $this->subjects->mayAssign($actor, $assignment->subject_type, $assignment->subject_id);
    }

    public function mayManage(User $actor, int $assignmentId): bool
    {
        $assignment = ProcessAssignment::query()->find($assignmentId);

        return $assignment !== null && $this->subjects->mayAssign($actor, $assignment->subject_type, $assignment->subject_id);
    }

    public function referencingFile(int $fileId): array
    {
        return array_map(intval(...), ProcessAssignment::query()->where('evidence_file_id', $fileId)->pluck('id')->all());
    }

    public function recordEvidence(int $assignmentId, ?int $fileId): void
    {
        // Not a user edit of the assignment, so its lock_version stays as the panel saw it.
        DB::table('process_assignments')->where('id', $assignmentId)->update(['evidence_file_id' => $fileId, 'updated_at' => now()]);
    }
}
