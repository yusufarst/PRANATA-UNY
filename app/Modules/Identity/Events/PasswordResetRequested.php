<?php

namespace App\Modules\Identity\Events;

use App\Modules\Identity\Models\PasswordReset;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator asked for the assisted reset of a privileged account; it waits for a second administrator (TASK-011 TAC-03).
 */
final class PasswordResetRequested
{
    use Dispatchable;

    public function __construct(public readonly PasswordReset $reset) {}
}
