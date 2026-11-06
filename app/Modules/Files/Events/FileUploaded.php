<?php

namespace App\Modules\Files\Events;

use App\Models\User;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Models\FileObject;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Bytes were stored as a new file object and linked to their owner, inside the transaction of the owner's command.
 */
final class FileUploaded
{
    use Dispatchable;

    public function __construct(public readonly FileObject $file, public readonly FileLink $link, public readonly User $actor) {}
}
