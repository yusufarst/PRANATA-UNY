<?php

namespace App\Modules\Access\Contracts;

use App\Models\User;

/**
 * What another module may know and do about the decree evidence of a process assignment (ARCHITECTURE section 5 rule 1):
 * the Files module registers process_assignments as an owner of files through this contract, and Access keeps its own
 * column evidence_file_id in step. Access never depends on Files.
 */
interface AssignmentEvidence
{
    /**
     * The assignment as the evidence page shows it, or null when there is none. reference and label are those of its
     * object when the object's module is installed.
     *
     * @return array{id: int, public_id: string, user_id: int, holder: string, assignment_role: string, subject_type: string, subject_id: int, reference: string|null, label: string|null, decree_ref: string|null, evidence_file_id: int|null}|null
     */
    public function find(int $assignmentId): ?array;

    /**
     * Whether the actor may see the decree evidence: whoever may assign on the object, or the holder.
     */
    public function mayRead(User $actor, int $assignmentId): bool;

    /**
     * Whether the actor may attach and release the decree evidence: whoever may assign on the object.
     */
    public function mayManage(User $actor, int $assignmentId): bool;

    /**
     * The assignments whose evidence column points at the file.
     *
     * @return list<int>
     */
    public function referencingFile(int $fileId): array;

    /**
     * Point the evidence column of the assignment at the file, or at nothing; called inside the caller's transaction.
     */
    public function recordEvidence(int $assignmentId, ?int $fileId): void;
}
