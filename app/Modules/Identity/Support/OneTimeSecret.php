<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Models\PasswordReset;

/**
 * The secrets an administrator hands over in person (SECURITY_MODEL section 3.1): easy to read aloud and to type, with no
 * characters that look alike, and random enough not to be guessed within their short life and the sign-in limits.
 */
final class OneTimeSecret
{
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * An activation code such as K7MQ-4XTR-9PZD.
     */
    public static function activationCode(): string
    {
        return self::groups(3);
    }

    /**
     * A temporary password such as K7MQ-4XTR-9PZD-HW3N.
     */
    public static function temporaryPassword(): string
    {
        return self::groups(4);
    }

    /**
     * Whether the password of the account is a one-time secret from an administrator that no longer serves: past its life,
     * or already marked expired or revoked. Such a secret is the password only until the forced change, and its record
     * holds the same hash; the record is found by that hash whatever its status, so a refusal stays a refusal. A secret
     * found past its life is marked expired.
     */
    public static function isClosedFor(User $user): bool
    {
        if (! $user->must_change_password) {
            return false;
        }

        $secret = PasswordReset::query()
            ->where('user_id', $user->getKey())
            ->where('temp_secret_hash', $user->password)
            ->latest('id')
            ->first();

        if ($secret === null) {
            return false;
        }

        if ($secret->status === PasswordReset::ISSUED && $secret->expires_at->isFuture()) {
            return false;
        }

        if ($secret->status === PasswordReset::ISSUED) {
            $secret->forceFill(['status' => PasswordReset::EXPIRED])->save();
        }

        return true;
    }

    private static function groups(int $count): string
    {
        $groups = [];
        $last = strlen(self::ALPHABET) - 1;

        for ($group = 0; $group < $count; $group++) {
            $characters = '';

            for ($i = 0; $i < 4; $i++) {
                $characters .= self::ALPHABET[random_int(0, $last)];
            }

            $groups[] = $characters;
        }

        return implode('-', $groups);
    }
}
