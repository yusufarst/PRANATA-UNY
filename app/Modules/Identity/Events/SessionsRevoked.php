<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Sessions of an account were ended on request, by the account itself or by an administrator (TASK-011 TAC-04).
 */
final class SessionsRevoked
{
    use Dispatchable;

    /**
     * One session the account named, from its own sessions page.
     */
    public const ONE = 'one';

    /**
     * Every session of the account but the one that asked, from its own sessions page.
     */
    public const OTHERS = 'others';

    /**
     * Every session of the account, by an administrator.
     */
    public const ALL = 'all';

    /**
     * @param  string  $scope  self::ONE, self::OTHERS or self::ALL
     */
    public function __construct(
        public readonly User $user,
        public readonly string $scope,
        public readonly int $endedSessions,
    ) {}
}
