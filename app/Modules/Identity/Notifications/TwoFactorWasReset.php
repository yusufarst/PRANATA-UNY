<?php

namespace App\Modules\Identity\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Tells the owner of an account in the app that its TOTP was removed by two administrators (SECURITY_MODEL section 3.1,
 * TASK-012 TAC-03), so a reset they did not ask for does not go unseen. PG-006 (TASK-019) words it in the reader's language.
 */
final class TwoFactorWasReset extends Notification
{
    /**
     * In-app only: mail is optional and never the only channel (INTERACTION_PATTERNS section 8).
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{kind: string}
     */
    public function toArray(object $notifiable): array
    {
        return ['kind' => 'identity.two_factor_reset'];
    }
}
