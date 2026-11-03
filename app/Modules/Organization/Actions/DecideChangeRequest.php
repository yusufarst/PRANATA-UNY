<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Contracts\MaintenancePolicy;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\DomainRuleViolation;

/**
 * TR-285 (S3 to S4 or S7): record the authority's decision. It is BLOCKED_BY_GAP-021: an official decision needs a version
 * of organization.maintenance officially in force on the decision date, the reference date of this Action (TASK-013 TAC-04,
 * POLICY_REGISTRY section 3.1). Without one, a non-production environment that allows draft policies records it as a
 * simulation; production refuses it.
 *
 * An approval sets the effective date, never before the decision date (no backdating, TR-286).
 */
final class DecideChangeRequest extends ChangeRequestAction
{
    public const APPROVE = 'APPROVE';

    public const REJECT = 'REJECT';

    public function __construct(private readonly MaintenancePolicy $policy) {}

    /**
     * Whether a decision may be recorded today, and whether it would be a simulation; null when it may not.
     */
    public function availability(): ?bool
    {
        if ($this->policy->isOfficialOn(BusinessDate::today())) {
            return false;
        }

        return $this->policy->allowsSimulation() ? true : null;
    }

    /**
     * @param  array{decision: string, effective_from: ?string, decision_ref: string, reason: ?string}  $details
     */
    public function handle(User $actor, ChangeRequest $request, array $details, int $seenVersion): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $request, $details, $seenVersion): ChangeRequest {
            $current = $this->locked($request, [ChangeRequest::AWAITING_DECISION]);

            // Resolved inside the transaction, so a version that ends at midnight is not used after it (section 3.1 rule 2).
            $simulation = $this->availability();

            if ($simulation === null) {
                throw DomainRuleViolation::because('organization.policy_unavailable', ['gap' => 'GAP-021']);
            }

            $approve = $details['decision'] === self::APPROVE;
            $effectiveFrom = $details['effective_from'];

            if ($approve && ($effectiveFrom === null || $effectiveFrom < BusinessDate::today()->toDateString())) {
                throw DomainRuleViolation::because('organization.effective_date_in_past');
            }

            $current->forceFill([
                'status' => $approve ? ChangeRequest::APPROVED : ChangeRequest::REJECTED,
                'effective_from' => $approve ? $effectiveFrom : null,
                'decision_ref' => $details['decision_ref'],
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'closed_reason' => $approve ? null : $details['reason'],
                'is_simulation' => $simulation,
            ])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-285', ChangeRequest::AWAITING_DECISION, $actor, $details['reason'], [
                'decision' => $details['decision'],
                'decision_ref' => $details['decision_ref'],
                'effective_from' => $current->effective_from?->toDateString(),
                'policy_family' => 'organization.maintenance',
            ]);

            return $current;
        });
    }
}
