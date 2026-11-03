<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator deactivated an account: every session of it ended and its open one-time secrets were revoked (OD-38).
 */
final class AccountDeactivated
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $reason,
        public readonly int $endedSessions,
        public readonly int $revokedSecrets,
    ) {}
}
