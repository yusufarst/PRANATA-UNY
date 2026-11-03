<?php

namespace App\Modules\Audit\Listeners;

use App\Models\User;
use App\Modules\Audit\Support\AuditLogger;
use App\Modules\Identity\Events\AccountLocked;
use App\Modules\Identity\Events\PasswordChanged;
use App\Modules\Identity\Events\SessionEnded;
use App\Modules\Identity\Events\SignInFailed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

/**
 * The audit trail of sign-in and account security (SECURITY_MODEL section 3, ARCHITECTURE section 6: login/security).
 *
 * Synchronous, so a failed write fails the request that caused the event. Nothing here carries the identity typed
 * at a failed sign-in, which may be a mistyped password.
 */
final class RecordAuthenticationEvents
{
    private const MODULE = 'identity';

    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'login',
            Logout::class => 'logout',
            SignInFailed::class => 'signInFailed',
            AccountLocked::class => 'accountLocked',
            SessionEnded::class => 'sessionEnded',
            PasswordChanged::class => 'passwordChanged',
        ];
    }

    public function login(Login $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        $this->audit->record('identity.login_succeeded', self::MODULE, $user, metadata: ['remembered' => $event->remember], actor: $event->user);
    }

    public function logout(Logout $event): void
    {
        if ($event->user === null) {
            return;
        }

        $user = $event->user instanceof User ? $event->user : null;

        $this->audit->record('identity.logout', self::MODULE, $user, actor: $event->user);
    }

    public function signInFailed(SignInFailed $event): void
    {
        $this->audit->record('identity.login_failed', self::MODULE, $event->user, metadata: ['reason' => $event->reason]);
    }

    public function accountLocked(AccountLocked $event): void
    {
        $this->audit->record('identity.account_locked', self::MODULE, $event->user, metadata: ['minutes' => $event->minutes]);
    }

    public function sessionEnded(SessionEnded $event): void
    {
        $this->audit->record('identity.session_ended', self::MODULE, $event->user, metadata: ['reason' => $event->reason], actor: $event->user);
    }

    public function passwordChanged(PasswordChanged $event): void
    {
        $this->audit->record(
            'identity.password_changed',
            self::MODULE,
            $event->user,
            metadata: ['via' => $event->via, 'ended_sessions' => $event->endedSessions],
            actor: $event->via === PasswordChanged::RESET ? null : $event->user,
        );
    }
}
