<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * The application ended a signed-in session on its own (SECURITY_MODEL section 4), just before signing it out.
 */
final class SessionEnded
{
    use Dispatchable;

    public const IDLE = 'idle';

    public const ABSOLUTE = 'absolute';

    public const ACCOUNT_STATUS = 'account_status';

    /**
     * @param  string  $reason  self::IDLE, self::ABSOLUTE or self::ACCOUNT_STATUS
     */
    public function __construct(
        public readonly User $user,
        public readonly string $reason,
    ) {}
}
