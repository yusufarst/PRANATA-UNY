<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator lifted the suspension of an account.
 */
final class AccountReinstated
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $reason,
    ) {}
}
