<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Support\AuditLogger;
use App\Modules\Identity\Events\AccountCreated;
use App\Modules\Identity\Events\AccountDeactivated;
use App\Modules\Identity\Events\AccountReinstated;
use App\Modules\Identity\Events\AccountSuspended;
use App\Modules\Identity\Events\AccountUpdated;
use App\Modules\Identity\Events\PasswordResetIssued;
use App\Modules\Identity\Events\PasswordResetRequested;
use App\Modules\Identity\Events\PasswordResetRevoked;
use App\Modules\Identity\Events\SessionsRevoked;
use App\Modules\Identity\Events\TwoFactorResetApproved;
use App\Modules\Identity\Events\TwoFactorResetRequested;
use App\Modules\Identity\Events\TwoFactorResetRevoked;
use Illuminate\Events\Dispatcher;

/**
 * The audit trail of what administrators do to accounts (TASK-011: every action is audited). The administrator is the actor,
 * or the account itself when it ends its own sessions.
 *
 * Synchronous, so a failed write fails the Action that caused the event. No activation code or temporary password is carried.
 */
final class RecordAccountAdministration
{
    private const MODULE = 'identity';

    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            AccountCreated::class => 'accountCreated',
            AccountUpdated::class => 'accountUpdated',
            AccountDeactivated::class => 'accountDeactivated',
            AccountSuspended::class => 'accountSuspended',
            AccountReinstated::class => 'accountReinstated',
            PasswordResetRequested::class => 'passwordResetRequested',
            PasswordResetIssued::class => 'passwordResetIssued',
            PasswordResetRevoked::class => 'passwordResetRevoked',
            SessionsRevoked::class => 'sessionsRevoked',
            TwoFactorResetRequested::class => 'twoFactorResetRequested',
            TwoFactorResetApproved::class => 'twoFactorResetApproved',
            TwoFactorResetRevoked::class => 'twoFactorResetRevoked',
        ];
    }

    public function accountCreated(AccountCreated $event): void
    {
        $this->audit->record('identity.account_created', self::MODULE, $event->user, metadata: [
            'role_group' => $event->user->role_group,
            'activation_expires_at' => $event->codeExpiresAt,
        ]);
    }

    public function accountUpdated(AccountUpdated $event): void
    {
        $this->audit->record('identity.account_updated', self::MODULE, $event->user, changes: $event->changes);
    }

    public function accountDeactivated(AccountDeactivated $event): void
    {
        $this->audit->record('identity.account_deactivated', self::MODULE, $event->user, reason: $event->reason, metadata: [
            'ended_sessions' => $event->endedSessions,
            'revoked_secrets' => $event->revokedSecrets,
        ]);
    }

    public function accountSuspended(AccountSuspended $event): void
    {
        $this->audit->record('identity.account_suspended', self::MODULE, $event->user, reason: $event->reason, metadata: [
            'ended_sessions' => $event->endedSessions,
        ]);
    }

    public function accountReinstated(AccountReinstated $event): void
    {
        $this->audit->record('identity.account_reinstated', self::MODULE, $event->user, reason: $event->reason);
    }

    public function passwordResetRequested(PasswordResetRequested $event): void
    {
        $this->audit->record('identity.reset_requested', self::MODULE, $event->reset->user, metadata: [
            ...self::reset($event),
            'awaits_second_admin' => true,
        ]);
    }

    /**
     * Recorded with the identity check, and with the second administrator when the account is privileged (TASK-011 TAC-02).
     */
    public function passwordResetIssued(PasswordResetIssued $event): void
    {
        $this->audit->record('identity.reset_issued', self::MODULE, $event->reset->user, metadata: [
            ...self::reset($event),
            'approved_by' => $event->reset->approved_by,
            'ended_sessions' => $event->endedSessions,
        ]);
    }

    public function passwordResetRevoked(PasswordResetRevoked $event): void
    {
        $this->audit->record('identity.reset_revoked', self::MODULE, $event->reset->user, metadata: self::reset($event));
    }

    /**
     * The actor is the signed-in user: the account itself on its own sessions page, or an administrator (TASK-011 TAC-04).
     */
    public function sessionsRevoked(SessionsRevoked $event): void
    {
        $this->audit->record('identity.sessions_revoked', self::MODULE, $event->user, metadata: [
            'scope' => $event->scope,
            'ended_sessions' => $event->endedSessions,
        ]);
    }

    public function twoFactorResetRequested(TwoFactorResetRequested $event): void
    {
        $this->audit->record('identity.two_factor_reset_requested', self::MODULE, $event->reset->user, metadata: self::twoFactorReset($event));
    }

    /**
     * Recorded with the identity check and the second administrator (TASK-012 TAC-03).
     */
    public function twoFactorResetApproved(TwoFactorResetApproved $event): void
    {
        $this->audit->record('identity.two_factor_reset_approved', self::MODULE, $event->reset->user, metadata: [
            ...self::twoFactorReset($event),
            'approved_by' => $event->reset->approved_by,
            'ended_sessions' => $event->endedSessions,
        ]);
    }

    public function twoFactorResetRevoked(TwoFactorResetRevoked $event): void
    {
        $this->audit->record('identity.two_factor_reset_revoked', self::MODULE, $event->reset->user, metadata: self::twoFactorReset($event));
    }

    /**
     * @return array<string, mixed>
     */
    private static function twoFactorReset(TwoFactorResetRequested|TwoFactorResetApproved|TwoFactorResetRevoked $event): array
    {
        $reset = $event->reset;

        return [
            'two_factor_reset_id' => $reset->id,
            'requested_by' => $reset->requested_by,
            'identity_check_method' => $reset->identity_check_method,
            'identity_check_note' => $reset->identity_check_note,
            'expires_at' => $reset->expires_at->toIso8601String(),
        ];
    }

    /**
     * Takes the event rather than the reset, so Audit reaches Identity through its events only (ARCHITECTURE section 5).
     *
     * @return array<string, mixed>
     */
    private static function reset(PasswordResetRequested|PasswordResetIssued|PasswordResetRevoked $event): array
    {
        $reset = $event->reset;

        return [
            'reset_id' => $reset->id,
            'requested_by' => $reset->requested_by,
            'identity_check_method' => $reset->identity_check_method,
            'identity_check_note' => $reset->identity_check_note,
            'expires_at' => $reset->expires_at->toIso8601String(),
        ];
    }
}
