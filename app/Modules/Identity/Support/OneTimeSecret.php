<?php

namespace App\Modules\Identity\Support;

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
