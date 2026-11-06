<?php

namespace App\Modules\Organization\Contracts;

use App\Models\User;

/**
 * What another module may know and do about the decree evidence of an appointment (ARCHITECTURE section 5 rule 1): the Files
 * module registers org_appointments as an owner of files through this contract, and Organization keeps its own column
 * evidence_file_id in step. Organization never depends on Files.
 */
interface AppointmentEvidence
{
    /**
     * The appointment as the evidence page shows it, or null when there is none.
     *
     * @return array{id: int, user_id: int, person: string, position: string, unit_public_id: string|null, unit_name: string|null, decree_ref: string|null, evidence_file_id: int|null}|null
     */
    public function find(int $appointmentId): ?array;

    /**
     * Whether the actor may attach and release the decree evidence of appointments (admin.org.manage).
     */
    public function mayManage(User $actor): bool;

    /**
     * The appointments whose evidence column points at the file.
     *
     * @return list<int>
     */
    public function referencingFile(int $fileId): array;

    /**
     * Point the evidence column of the appointment at the file, or at nothing; called inside the caller's transaction.
     */
    public function recordEvidence(int $appointmentId, ?int $fileId, User $actor): void;
}
