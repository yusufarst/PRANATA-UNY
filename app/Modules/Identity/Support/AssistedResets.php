<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Models\PasswordReset;

/**
 * The assisted resets of an account as other modules ask about them (SECURITY_MODEL section 3.1, SEC-21).
 */
final class AssistedResets
{
    /**
     * Whether one administrator alone issued the account a temporary password that has not been replaced yet: its holder
     * may still be whoever knows that password, so the account must not become privileged before the owner changed it.
     */
    public function hasUnconfirmedTemporaryPassword(User $user): bool
    {
        return PasswordReset::query()
            ->where('user_id', $user->getKey())
            ->where('channel', PasswordReset::ADMIN_ASSISTED)
            ->where('status', PasswordReset::ISSUED)
            ->whereNull('approved_by')
            ->when(! $user->must_change_password, fn ($query) => $query->where('expires_at', '>', now()))
            ->exists();
    }
}
