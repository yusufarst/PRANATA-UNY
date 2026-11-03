<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Access\Events\GrantApproved;
use App\Modules\Access\Events\GrantGiven;
use App\Modules\Access\Events\GrantRevoked;
use App\Modules\Audit\Support\AuditLogger;
use Illuminate\Events\Dispatcher;

/**
 * The audit trail of grant changes on PG-503 (SECURITY_MODEL section 9 "perubahan grant", T-04). The administrator is the actor
 * and the grant the subject.
 *
 * Synchronous, so a failed write fails the Action that caused the event.
 */
final class RecordAccessAdministration
{
    private const MODULE = 'access';

    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            GrantGiven::class => 'grantGiven',
            GrantApproved::class => 'grantApproved',
            GrantRevoked::class => 'grantRevoked',
        ];
    }

    public function grantGiven(GrantGiven $event): void
    {
        $this->audit->record('access.grant_given', self::MODULE, $event->grant, reason: $event->grant->reason, metadata: self::grant($event));
    }

    public function grantApproved(GrantApproved $event): void
    {
        $this->audit->record('access.grant_approved', self::MODULE, $event->grant, metadata: self::grant($event));
    }

    public function grantRevoked(GrantRevoked $event): void
    {
        $this->audit->record('access.grant_revoked', self::MODULE, $event->grant, reason: $event->grant->revoke_reason, metadata: self::grant($event));
    }

    /**
     * Takes the event rather than the grant, so Audit reaches Access through its events only (ARCHITECTURE section 5).
     *
     * @return array<string, mixed>
     */
    private static function grant(GrantGiven|GrantApproved|GrantRevoked $event): array
    {
        $grant = $event->grant;

        return [
            'user_id' => $grant->user_id,
            'profile_id' => $grant->profile_id,
            'capability' => $grant->capability,
            'scope_type' => $grant->scope_type->value,
            'scope_id' => $grant->scope_id,
            'scope_key' => $grant->scope_key,
            'requires_approval' => $grant->requires_approval,
            'status' => $grant->status,
            'effective_from' => $grant->effective_from->toDateString(),
            'effective_to' => $grant->effective_to?->toDateString(),
            'granted_by' => $grant->granted_by,
            'approved_by' => $grant->approved_by,
        ];
    }
}
