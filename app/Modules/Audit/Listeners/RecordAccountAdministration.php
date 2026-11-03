<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Support\AuditLogger;
use App\Modules\Identity\Events\AccountCreated;
use App\Modules\Identity\Events\AccountDeactivated;
use App\Modules\Identity\Events\AccountUpdated;
use Illuminate\Events\Dispatcher;

/**
 * The audit trail of what administrators do to accounts (TASK-011: every action is audited). The administrator is the actor.
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
}
