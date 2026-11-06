<?php

namespace App\Modules\Files\Contracts;

use App\Models\User;
use App\Modules\Files\Data\OwnerDescription;

/**
 * One kind of row that files are linked to, as its owning module registers it with the Files module (ARCHITECTURE section 5):
 * the table that names it in file_links.owner_type, what may be uploaded for it, who may read and manage its files, and how
 * the owner keeps its own evidence column in step. Files never reads the owner's tables itself.
 */
interface FileOwnerType
{
    /**
     * The name of the owning table, as file_links.owner_type carries it (the enforced morph map).
     */
    public function ownerType(): string;

    /**
     * The purposes a file may be linked to a row for, such as DECREE; each is an upload profile of config/files.php.
     *
     * @return list<string>
     */
    public function purposes(): array;

    /**
     * The classification of a file uploaded for this owner (INTERNAL, CONFIDENTIAL or RESTRICTED).
     */
    public function classification(): string;

    /**
     * The row as the evidence page shows it, with a parent the actor may open (the administration page of the row for
     * whoever manages it, otherwise the dashboard); null when the row does not exist.
     */
    public function describe(int $ownerId, User $actor): ?OwnerDescription;

    /**
     * Whether the actor may open and download the files of the row.
     */
    public function allowsRead(User $actor, int $ownerId): bool;

    /**
     * Whether the actor may attach a file to the row and release one from it.
     */
    public function allowsManage(User $actor, int $ownerId): bool;

    /**
     * The rows of this type whose own evidence column points at the file, besides any link.
     *
     * @return list<int>
     */
    public function referencing(int $fileId): array;

    /**
     * Keep the row's own evidence column in step with its links: the file of its newest active link, or null. Called inside
     * the transaction of the Files command, so the column and the links commit together.
     */
    public function evidenceChanged(int $ownerId, ?int $fileId, User $actor): void;
}
