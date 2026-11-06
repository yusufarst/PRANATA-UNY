<?php

namespace App\Modules\Files\Support;

use App\Models\User;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Models\FileObject;

/**
 * Who may download a file (COMMAND_AND_API_CONTRACTS section 7, AUTHZ-44): someone the owning module of a row that references
 * the file, through an active link or its own evidence column, lets read that row. A file nobody references, or whose owner
 * type no module registered, is read by nobody (fail closed). The frontend only mirrors this; the route checks it again.
 */
final class FileAccess
{
    public function __construct(private readonly FileOwnerTypes $owners) {}

    public function allowsDownload(User $actor, FileObject $file): bool
    {
        $references = FileLink::query()
            ->where('file_id', $file->id)
            ->active()
            ->get(['owner_type', 'owner_id'])
            ->map(fn (FileLink $link): array => [$link->owner_type, $link->owner_id])
            ->all();

        foreach ($this->owners->all() as $owner) {
            foreach ($owner->referencing($file->id) as $ownerId) {
                $references[] = [$owner->ownerType(), $ownerId];
            }
        }

        foreach ($references as [$ownerType, $ownerId]) {
            $owner = $this->owners->find($ownerType);

            if ($owner !== null && $owner->allowsRead($actor, $ownerId)) {
                return true;
            }
        }

        return false;
    }
}
