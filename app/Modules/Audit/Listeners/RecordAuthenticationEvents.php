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
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;

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
            TwoFactorAuthenticationEnabled::class => 'twoFactorEnabled',
            TwoFactorAuthenticationConfirmed::class => 'twoFactorConfirmed',
            TwoFactorAuthenticationDisabled::class => 'twoFactorDisabled',
            RecoveryCodesGenerated::class => 'recoveryCodesGenerated',
            RecoveryCodeReplaced::class => 'recoveryCodeUsed',
            TwoFactorAuthenticationFailed::class => 'twoFactorFailed',
        ];
    }

    /**
     * The account started its TOTP setup; it counts only once confirmed (TASK-012 TAC-01).
     */
    public function twoFactorEnabled(TwoFactorAuthenticationEnabled $event): void
    {
        $this->ownTwoFactorChange('identity.two_factor_setup_started', $event->user);
    }

    public function twoFactorConfirmed(TwoFactorAuthenticationConfirmed $event): void
    {
        $this->ownTwoFactorChange('identity.two_factor_confirmed', $event->user);
    }

    public function twoFactorDisabled(TwoFactorAuthenticationDisabled $event): void
    {
        $this->ownTwoFactorChange('identity.two_factor_disabled', $event->user);
    }

    public function recoveryCodesGenerated(RecoveryCodesGenerated $event): void
    {
        $this->ownTwoFactorChange('identity.two_factor_recovery_regenerated', $event->user);
    }

    /**
     * A recovery code let the account through the challenge and was replaced; the code itself is never recorded.
     */
    public function recoveryCodeUsed(RecoveryCodeReplaced $event): void
    {
        $this->ownTwoFactorChange('identity.two_factor_recovery_used', $event->user);
    }

    /**
     * A wrong code at the challenge, before the account is signed in.
     */
    public function twoFactorFailed(TwoFactorAuthenticationFailed $event): void
    {
        $this->audit->record('identity.two_factor_failed', self::MODULE, $event->user instanceof User ? $event->user : null);
    }

    /**
     * The account is the actor: at the challenge it is not signed in yet.
     */
    private function ownTwoFactorChange(string $action, mixed $user): void
    {
        $user = $user instanceof User ? $user : null;

        $this->audit->record($action, self::MODULE, $user, actor: $user);
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
