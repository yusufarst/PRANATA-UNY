<?php

namespace App\Modules\Files\Events;

use App\Models\User;
use App\Modules\Files\Models\FileObject;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A CONFIDENTIAL or RESTRICTED file is about to be sent to a user who may read it (SECURITY_MODEL section 6); recorded
 * before the first byte goes out.
 */
final class FileDownloaded
{
    use Dispatchable;

    public function __construct(public readonly FileObject $file, public readonly User $actor, public readonly bool $inline) {}
}
