<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator suspended an account: every session of it ended until it is reinstated.
 */
final class AccountSuspended
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $reason,
        public readonly int $endedSessions,
    ) {}
}
