<?php

namespace App\Modules\Identity\Events;

use App\Modules\Identity\Models\PasswordReset;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator withdrew or turned down an assisted reset that waited for a second administrator (TASK-011 TAC-03).
 */
final class PasswordResetRevoked
{
    use Dispatchable;

    public function __construct(public readonly PasswordReset $reset) {}
}
