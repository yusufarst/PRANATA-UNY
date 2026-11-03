<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Repeated failed sign-ins locked an account for config('auth.lockout.lock_minutes') (SECURITY_MODEL section 3).
 */
final class AccountLocked
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly int $minutes,
    ) {}
}
