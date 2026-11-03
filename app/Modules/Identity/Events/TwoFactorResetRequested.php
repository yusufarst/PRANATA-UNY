<?php

namespace App\Modules\Identity\Events;

use App\Modules\Identity\Models\TwoFactorReset;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator asked to remove the TOTP of an account; it waits for a second administrator (TASK-012 TAC-03).
 */
final class TwoFactorResetRequested
{
    use Dispatchable;

    public function __construct(public readonly TwoFactorReset $reset) {}
}
