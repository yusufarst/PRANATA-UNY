<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Access\Events\AssignmentClosed;
use App\Modules\Access\Events\AssignmentGiven;
use App\Modules\Access\Events\GrantApproved;
use App\Modules\Access\Events\GrantGiven;
use App\Modules\Access\Events\GrantRevoked;
use App\Modules\Audit\Support\AuditLogger;
use Illuminate\Events\Dispatcher;

/**
 * The audit trail of grant changes on PG-503 (SECURITY_MODEL section 9 "perubahan grant", T-04) and of process assignments
 * (TASK-015). The administrator is the actor and the grant or assignment the subject.
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
            AssignmentGiven::class => 'assignmentGiven',
            AssignmentClosed::class => 'assignmentClosed',
        ];
    }

    public function assignmentGiven(AssignmentGiven $event): void
    {
        $this->audit->record('access.assignment_given', self::MODULE, $event->assignment, metadata: self::assignment($event), actor: $event->actor);
    }

    public function assignmentClosed(AssignmentClosed $event): void
    {
        $action = $event->assignment->status === 'REVOKED' ? 'access.assignment_revoked' : 'access.assignment_ended';

        $this->audit->record($action, self::MODULE, $event->assignment, reason: $event->assignment->close_reason, metadata: self::assignment($event), actor: $event->actor);
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

    /**
     * @return array<string, mixed>
     */
    private static function assignment(AssignmentGiven|AssignmentClosed $event): array
    {
        $assignment = $event->assignment;

        return [
            'subject_type' => $assignment->subject_type,
            'subject_id' => $assignment->subject_id,
            'assignment_role' => $assignment->assignment_role,
            'user_id' => $assignment->user_id,
            'status' => $assignment->status,
            'effective_from' => $assignment->effective_from->toIso8601String(),
            'effective_to' => $assignment->effective_to?->toIso8601String(),
            'decree_ref' => $assignment->decree_ref,
            'sod_check' => $assignment->sod_check,
            'assigned_by' => $assignment->assigned_by,
            'closed_by' => $assignment->closed_by,
        ];
    }
}
