<?php

namespace App\Modules\Access\Actions;

use App\Models\User;
use App\Modules\Access\Events\AssignmentClosed;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;
use InvalidArgumentException;

/**
 * End an active assignment because its task is over (ENDED), or revoke it because it was wrong (REVOKED), with a reason. The
 * caller may assign on the object ({@see \App\Modules\Access\Support\AssignmentSubjects::mayAssign()}). The row stays as
 * history, and the next request of its holder is checked without it (TAC-03).
 */
final class CloseAssignment extends Action
{
    /**
     * A {@see StaleVersionException} reaches the caller when someone changed the assignment first.
     */
    public function handle(User $actor, ProcessAssignment $assignment, string $status, string $reason, int $seenVersion): void
    {
        if (! in_array($status, [ProcessAssignment::ENDED, ProcessAssignment::REVOKED], true)) {
            throw new InvalidArgumentException("An assignment closes as ENDED or REVOKED, not {$status}.");
        }

        $this->transaction(function () use ($actor, $assignment, $status, $reason, $seenVersion): void {
            $assignment->refresh();

            if ($assignment->status !== ProcessAssignment::ACTIVE) {
                throw DomainRuleViolation::because('access.assignment_not_active');
            }

            $assignment->forceFill([
                'status' => $status,
                'closed_by' => $actor->id,
                'closed_at' => now(),
                'close_reason' => $reason,
            ])->saveAtVersion($seenVersion);

            // Inside the transaction, so the change and its audit record commit or roll back together (fail closed).
            AssignmentClosed::dispatch($assignment);
        });
    }
}
