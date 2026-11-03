<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An account got a new password, and every other session of it ended (SECURITY_MODEL section 3).
 */
final class PasswordChanged
{
    use Dispatchable;

    public const FORCED = 'forced';

    public const SETTINGS = 'settings';

    public const RESET = 'reset';

    /**
     * @param  string  $via  self::FORCED, self::SETTINGS or self::RESET
     */
    public function __construct(
        public readonly User $user,
        public readonly string $via,
        public readonly int $endedSessions,
    ) {}
}
