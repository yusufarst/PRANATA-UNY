<?php

namespace App\Modules\Identity\Events;

use App\Modules\Identity\Models\TwoFactorReset;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A second administrator approved the request, so the TOTP of the account is gone and its sessions ended (TASK-012 TAC-03).
 */
final class TwoFactorResetApproved
{
    use Dispatchable;

    public function __construct(public readonly TwoFactorReset $reset, public readonly int $endedSessions) {}
}
