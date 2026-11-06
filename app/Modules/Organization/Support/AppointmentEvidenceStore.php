<?php

namespace App\Modules\Organization\Support;

use App\Models\User;
use App\Modules\Organization\Contracts\AppointmentEvidence;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use Illuminate\Support\Facades\DB;

/**
 * The decree evidence of appointments as Organization exposes it ({@see AppointmentEvidence}). The column evidence_file_id
 * is Organization's own and follows the newest active link the Files module keeps for the appointment.
 */
final class AppointmentEvidenceStore implements AppointmentEvidence
{
    public function find(int $appointmentId): ?array
    {
        $appointment = OrgAppointment::query()
            ->with(['user:id,name', 'position:id,name,org_unit_id', 'unit:id,public_id,name', 'position.unit:id,public_id,name'])
            ->find($appointmentId);

        if ($appointment === null) {
            return null;
        }

        $unit = $appointment->unit ?? $appointment->position->unit;

        return [
            'id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'person' => $appointment->user->name,
            'position' => $appointment->position->name,
            'unit_public_id' => $unit?->public_id,
            'unit_name' => $unit?->name,
            'decree_ref' => $appointment->decree_ref,
            'evidence_file_id' => $appointment->evidence_file_id,
        ];
    }

    public function mayManage(User $actor): bool
    {
        return $actor->can(OrganizationServiceProvider::MANAGE_ORGANIZATION);
    }

    public function referencingFile(int $fileId): array
    {
        return array_map(intval(...), OrgAppointment::query()->where('evidence_file_id', $fileId)->pluck('id')->all());
    }

    public function recordEvidence(int $appointmentId, ?int $fileId, User $actor): void
    {
        DB::table('org_appointments')
            ->where('id', $appointmentId)
            ->update(['evidence_file_id' => $fileId, 'updated_by' => $actor->id, 'updated_at' => now()]);
    }
}
