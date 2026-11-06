<?php

namespace App\Modules\Files\Owners;

use App\Models\User;
use App\Modules\Access\Contracts\AssignmentEvidence;
use App\Modules\Files\Contracts\FileOwnerType;
use App\Modules\Files\Data\OwnerDescription;
use App\Modules\Files\Models\FileObject;

/**
 * process_assignments as an owner of files (TASK-016): the decree (SK) of an assignment, managed by whoever may assign on the
 * object and readable by the holder too. Access keeps evidence_file_id in step through its contract; the parent of the
 * evidence page is the object on PG-504 (RT-031).
 */
final class AssignmentEvidenceOwner implements FileOwnerType
{
    public const OWNER_TYPE = 'process_assignments';

    public const DECREE = 'DECREE';

    public function __construct(private readonly AssignmentEvidence $assignments) {}

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

    public function describe(int $ownerId): ?OwnerDescription
    {
        $assignment = $this->assignments->find($ownerId);

        if ($assignment === null) {
            return null;
        }

        $parent = $assignment['reference'] === null
            ? route('access.admin.assignments.index', absolute: false)
            : route('access.admin.assignments.create', ['type' => $assignment['subject_type'], 'reference' => $assignment['reference']], absolute: false);
        $object = $assignment['reference'] === null
            ? $assignment['subject_type']
            : trim($assignment['reference'].' '.($assignment['label'] ?? ''));

        return new OwnerDescription(
            title: __('files.owners.assignment', ['role' => $assignment['assignment_role'], 'person' => $assignment['holder']]),
            subtitle: trim($object.($assignment['decree_ref'] === null ? '' : ' · '.__('files.owners.decree', ['ref' => $assignment['decree_ref']]))),
            parentUrl: $parent,
            parentTitle: __('files.parents.assignment'),
        );
    }

    public function allowsRead(User $actor, int $ownerId): bool
    {
        return $this->assignments->mayRead($actor, $ownerId);
    }

    public function allowsManage(User $actor, int $ownerId): bool
    {
        return $this->assignments->mayManage($actor, $ownerId);
    }

    public function referencing(int $fileId): array
    {
        return $this->assignments->referencingFile($fileId);
    }

    public function evidenceChanged(int $ownerId, ?int $fileId, User $actor): void
    {
        $this->assignments->recordEvidence($ownerId, $fileId);
    }
}
