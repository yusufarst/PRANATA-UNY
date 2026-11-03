<?php

namespace App\Modules\Identity\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Tells the owner of an account in the app that its password was reset (SEC-20, TASK-011), so a reset they did not ask for
 * does not go unseen. It carries how, never the password; PG-006 (TASK-019) words it in the reader's language.
 */
final class PasswordWasReset extends Notification
{
    public const ASSISTED = 'assisted';

    public const EMAIL = 'email';

    /**
     * @param  string  $via  self::ASSISTED or self::EMAIL
     */
    public function __construct(public readonly string $via) {}

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
     * @return array{kind: string, via: string}
     */
    public function toArray(object $notifiable): array
    {
        return ['kind' => 'identity.password_reset', 'via' => $this->via];
    }
}
