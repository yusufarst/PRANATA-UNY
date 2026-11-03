<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A sign-in was refused (SECURITY_MODEL section 3: every failure is recorded). The caller only ever sees the generic message.
 */
final class SignInFailed
{
    use Dispatchable;

    public const UNKNOWN_IDENTITY = 'unknown_identity';

    public const WRONG_PASSWORD = 'wrong_password';

    public const ACCOUNT_LOCKED = 'account_locked';

    public const NOT_ACTIVE = 'not_active';

    /**
     * @param  User|null  $user  the account the identity names, null when it names none
     */
    public function __construct(
        public readonly ?User $user,
        public readonly string $reason,
    ) {}
}
