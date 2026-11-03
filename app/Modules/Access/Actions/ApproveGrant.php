<?php

namespace App\Modules\Access\Actions;

use App\Models\User;
use App\Modules\Access\Events\GrantApproved;
use App\Modules\Access\Models\AccessGrant;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;

/**
 * A second person approves a privileged grant, which then takes effect (SOD-05, TAC-03). The caller holds
 * admin.access.approve; neither the granter nor the receiver may approve it, and the database refuses the granter too (TAC-06).
 * A grant whose end has come, or whose receiver was deactivated meanwhile, is not approved.
 */
final class ApproveGrant extends Action
{
    /**
     * A {@see StaleVersionException} reaches the caller when someone changed the grant first.
     */
    public function handle(User $actor, AccessGrant $grant, int $seenVersion): void
    {
        $this->transaction(function () use ($actor, $grant, $seenVersion): void {
            $grant->refresh();

            if ($grant->statusOn(BusinessDate::today()->toDateString()) === AccessGrant::EXPIRED) {
                throw DomainRuleViolation::because('access.grant_lapsed');
            }

            if ($grant->status !== AccessGrant::PENDING_APPROVAL) {
                throw DomainRuleViolation::because('access.grant_not_pending');
            }

            if ($grant->granted_by === $actor->id) {
                throw DomainRuleViolation::because('access.cannot_approve_own_grant');
            }

            if ($grant->user_id === $actor->id) {
                throw DomainRuleViolation::because('access.cannot_grant_self');
            }

            if ($grant->user()->value('status') === User::DEACTIVATED) {
                throw DomainRuleViolation::because('access.account_deactivated');
            }

            $grant->forceFill([
                'status' => AccessGrant::ACTIVE,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ])->saveAtVersion($seenVersion);

            // Inside the transaction, so the approval and its audit record commit or roll back together (fail closed).
            GrantApproved::dispatch($grant);
        });
    }
}
