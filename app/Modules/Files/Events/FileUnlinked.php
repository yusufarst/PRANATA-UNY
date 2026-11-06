<?php

namespace App\Modules\Files\Events;

use App\Models\User;
use App\Modules\Files\Models\FileLink;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A link that was not sealed was released by someone the owning module allowed to; the file and the history stay.
 */
final class FileUnlinked
{
    use Dispatchable;

    public function __construct(public readonly FileLink $link, public readonly User $actor) {}
}
