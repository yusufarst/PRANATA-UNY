<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator created an account and issued its activation code (TASK-011 TAC-01). The code itself is never carried.
 */
final class AccountCreated
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $codeExpiresAt,
    ) {}
}
