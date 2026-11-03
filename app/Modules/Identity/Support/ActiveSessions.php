<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * The database sessions of an account (TASK-011 TAC-04).
 *
 * A session id is the key to a signed-in session, so it never leaves the server: a page sees a keyed hash of it instead,
 * and a command names a session by that hash.
 */
final class ActiveSessions
{
    /**
     * The sessions of the account, the most recently used first.
     *
     * @return list<array{key: string, ip_address: string|null, user_agent: string|null, last_active_at: string, current: bool}>
     */
    public static function of(User $user, ?string $currentId = null): array
    {
        return self::query(DB::connection(), $user)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn (stdClass $session): array => [
                'key' => self::key((string) $session->id),
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent === null ? null : mb_substr($session->user_agent, 0, 255),
                'last_active_at' => CarbonImmutable::createFromTimestamp((int) $session->last_activity)->toIso8601String(),
                'current' => $currentId !== null && hash_equals((string) $session->id, $currentId),
            ])
            ->values()
            ->all();
    }

    /**
     * The sessions of the account in the sessions table.
     */
    public static function query(ConnectionInterface $connection, User $user): Builder
    {
        return $connection->table((string) config('session.table', 'sessions'))->where('user_id', $user->getKey());
    }

    /**
     * The id of the session of the account that the page named by its key, if it still exists.
     */
    public static function idOf(ConnectionInterface $connection, User $user, string $key): ?string
    {
        foreach (self::query($connection, $user)->pluck('id') as $id) {
            if (hash_equals(self::key((string) $id), $key)) {
                return (string) $id;
            }
        }

        return null;
    }

    /**
     * The name of a session shown to a page: a keyed hash, so the page cannot learn the session id from it.
     */
    public static function key(string $id): string
    {
        return hash_hmac('sha256', 'identity.session|'.$id, (string) config('app.key'));
    }
}
