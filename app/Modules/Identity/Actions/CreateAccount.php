<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\AccountCreated;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Identity\Support\OneTimeSecret;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Create an INTERNAL or VENDOR account whose password the administrator never knows (TASK-011 TAC-01).
 *
 * The account starts with a one-time activation code as its password and must change it on its first sign-in
 * (SECURITY_MODEL section 3). The code is shown once to the administrator, who hands it over in person; only its hash
 * is stored, and the sign-in refuses it once it expired.
 */
final class CreateAccount extends Action
{
    /**
     * @param  array{role_group: string, username: string, name: string, email: ?string, staff_identifier: ?string, locale: string}  $details
     * @return array{user: User, code: string, expires_at: Carbon}
     */
    public function handle(User $actor, array $details): array
    {
        $code = OneTimeSecret::activationCode();
        $hash = Hash::make($code);
        $expiresAt = now()->addHours((int) config('auth.one_time_secret_hours'));

        $user = $this->transaction(function () use ($actor, $details, $hash, $expiresAt): User {
            $user = new User;

            $user->forceFill([
                ...$details,
                'password' => $hash,
                'status' => User::ACTIVE,
                'must_change_password' => true,
                // The administrator enters the address the person gave in person; no mail is needed to use the account.
                'email_verified_at' => $details['email'] === null ? null : now(),
                'created_by' => $actor->getKey(),
            ])->save();

            PasswordReset::query()->create([
                'user_id' => $user->getKey(),
                'channel' => PasswordReset::ACTIVATION,
                'requested_by' => $actor->getKey(),
                'temp_secret_hash' => $hash,
                'status' => PasswordReset::ISSUED,
                'expires_at' => $expiresAt,
            ]);

            // Inside the transaction, so the account and its audit record commit or roll back together (fail closed).
            AccountCreated::dispatch($user, $expiresAt->toIso8601String());

            return $user;
        });

        return ['user' => $user, 'code' => $code, 'expires_at' => $expiresAt];
    }
}
