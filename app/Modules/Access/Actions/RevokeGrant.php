<?php

namespace App\Modules\Access\Actions;

use App\Models\User;
use App\Modules\Access\Models\AccessGrant;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;

/**
 * Revoke a pending or active grant, with a reason. The caller holds admin.access.grant. The grant stays as
 * history, and the next request of its holder is checked without it (TAC-04, AUTHZ-22).
 */
final class RevokeGrant extends Action
{
    /**
     * A {@see StaleVersionException} reaches the caller when someone changed the grant first.
     */
    public function handle(User $actor, AccessGrant $grant, string $reason, int $seenVersion): void
    {
        $this->transaction(function () use ($actor, $grant, $reason, $seenVersion): void {
            $grant->refresh();

            if (! in_array($grant->status, [AccessGrant::PENDING_APPROVAL, AccessGrant::ACTIVE], true)) {
                throw DomainRuleViolation::because('access.grant_not_revocable');
            }

            $grant->forceFill([
                'status' => AccessGrant::REVOKED,
                'revoked_by' => $actor->id,
                'revoked_at' => now(),
                'revoke_reason' => $reason,
            ])->saveAtVersion($seenVersion);
        });
    }
}
