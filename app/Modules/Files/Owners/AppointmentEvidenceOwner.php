<?php

namespace App\Modules\Files\Owners;

use App\Models\User;
use App\Modules\Files\Contracts\FileOwnerType;
use App\Modules\Files\Data\OwnerDescription;
use App\Modules\Files\Models\FileObject;
use App\Modules\Organization\Contracts\AppointmentEvidence;

/**
 * org_appointments as an owner of files (TASK-016): the decree (SK) of an appointment, managed by whoever manages the
 * organization and readable by the appointed person too. Organization keeps evidence_file_id in step through its contract;
 * the parent of the evidence page is the unit on PG-505 (RT-031).
 */
final class AppointmentEvidenceOwner implements FileOwnerType
{
    public const OWNER_TYPE = 'org_appointments';

    public const DECREE = 'DECREE';

    public function __construct(private readonly AppointmentEvidence $appointments) {}

    public function ownerType(): string
    {
        return self::OWNER_TYPE;
    }

    public function purposes(): array
    {
        return [self::DECREE];
    }

    public function classification(): string
    {
        return FileObject::INTERNAL;
    }

    public function describe(int $ownerId, User $actor): ?OwnerDescription
    {
        $appointment = $this->appointments->find($ownerId);

        if ($appointment === null) {
            return null;
        }

        // The appointed person reads the decree but may not open PG-505, so their way back is the dashboard.
        if (! $this->appointments->mayManage($actor)) {
            $parent = route('dashboard', absolute: false);
            $parentTitle = (string) __('files.parents.home');
        } else {
            $parent = $appointment['unit_public_id'] === null
                ? route('organization.admin.index', absolute: false)
                : route('organization.admin.index', ['unit' => $appointment['unit_public_id']], absolute: false);
            $parentTitle = (string) __('files.parents.appointment', ['unit' => $appointment['unit_name'] ?? '']);
        }

        return new OwnerDescription(
            title: (string) __('files.owners.appointment', ['position' => $appointment['position'], 'person' => $appointment['person']]),
            subtitle: $appointment['decree_ref'] === null ? null : (string) __('files.owners.decree', ['ref' => $appointment['decree_ref']]),
            parentUrl: $parent,
            parentTitle: $parentTitle,
        );
    }

    public function allowsRead(User $actor, int $ownerId): bool
    {
        if ($this->appointments->mayManage($actor)) {
            return true;
        }

        // The appointed person may read their own decree.
        return ($this->appointments->find($ownerId)['user_id'] ?? null) === $actor->id;
    }

    public function allowsManage(User $actor, int $ownerId): bool
    {
        return $this->appointments->mayManage($actor) && $this->appointments->find($ownerId) !== null;
    }

    public function referencing(int $fileId): array
    {
        return $this->appointments->referencingFile($fileId);
    }

    public function evidenceChanged(int $ownerId, ?int $fileId, User $actor): void
    {
        $this->appointments->recordEvidence($ownerId, $fileId, $actor);
    }
}
