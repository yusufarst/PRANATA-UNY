<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Models\PasswordReset;
use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Issue the temporary password of an assisted reset (SECURITY_MODEL section 3.1, OPERATIONS_RUNBOOK RB-08, TASK-011 TAC-02).
 *
 * The password becomes the account's password at once, lives config('auth.one_time_secret_hours') hours and must be changed
 * on the next sign-in. Every session and remember cookie of the account ends, and any other secret still open for it is
 * revoked. Only the hash is stored. Runs inside the transaction of the calling Action, with the account row locked.
 */
final class TemporaryPassword
{
    /**
     * @return array{password: string, expires_at: CarbonImmutable, ended_sessions: int}
     */
    public static function issue(Connection $connection, User $user, PasswordReset $reset): array
    {
        $password = OneTimeSecret::temporaryPassword();
        $hash = Hash::make($password);
        $expiresAt = CarbonImmutable::now()->addHours((int) config('auth.one_time_secret_hours'));

        $user->forceFill([
            'password' => $hash,
            'must_change_password' => true,
            'remember_token' => Str::random(60),
        ])->save();

        PasswordReset::query()
            ->where('user_id', $user->getKey())
            ->whereIn('status', [PasswordReset::PENDING_APPROVAL, PasswordReset::ISSUED])
            ->when($reset->exists, fn ($query) => $query->whereKeyNot($reset->getKey()))
            ->update(['status' => PasswordReset::REVOKED]);

        $reset->forceFill([
            'temp_secret_hash' => $hash,
            'status' => PasswordReset::ISSUED,
            'expires_at' => $expiresAt,
        ])->save();

        $ended = $connection
            ->table((string) config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->delete();

        return ['password' => $password, 'expires_at' => $expiresAt, 'ended_sessions' => $ended];
    }
}
