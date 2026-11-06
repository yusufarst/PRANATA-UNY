<?php

namespace App\Modules\Files\Events;

use App\Models\User;
use App\Modules\Files\Models\FileObject;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * The stored bytes of a file were missing or did not match its identity when the application went to read them
 * (SEC-22, DI-33): an integrity incident. The file is never replaced or released because of it.
 */
final class FileIntegrityFailed
{
    use Dispatchable;

    /**
     * @param  string  $reason  MISSING or MISMATCH
     * @param  array{size_bytes: int|null, sha256: string|null}  $found  what the disk held
     * @param  string  $during  what was being done: download, preview, read or verify
     * @param  list<array{owner_type: string, owner_id: int}>  $references  the rows that reference the file (the subjects of the incident)
     */
    public function __construct(
        public readonly FileObject $file,
        public readonly string $reason,
        public readonly array $found,
        public readonly string $during,
        public readonly ?User $actor,
        public readonly array $references = [],
    ) {}
}
