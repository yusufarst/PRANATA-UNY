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
        foreach ($this->references($file) as $reference) {
            $owner = $this->owners->find($reference['owner_type']);

            if ($owner !== null && $owner->allowsRead($actor, $reference['owner_id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * The rows that reference the file: through an active link, or through the evidence column of a registered owner type.
     * Also what an integrity incident names as its subjects.
     *
     * @return list<array{owner_type: string, owner_id: int}>
     */
    public function references(FileObject $file): array
    {
        $references = [];

        foreach (FileLink::query()->where('file_id', $file->id)->active()->get(['owner_type', 'owner_id']) as $link) {
            $references["{$link->owner_type}:{$link->owner_id}"] = ['owner_type' => $link->owner_type, 'owner_id' => $link->owner_id];
        }

        foreach ($this->owners->all() as $owner) {
            foreach ($owner->referencing($file->id) as $ownerId) {
                $references["{$owner->ownerType()}:{$ownerId}"] = ['owner_type' => $owner->ownerType(), 'owner_id' => $ownerId];
            }
        }

        return array_values($references);
    }
}
