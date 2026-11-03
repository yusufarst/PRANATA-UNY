<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Organization\Support\ChangeApplier;
use App\Modules\Organization\Support\ChangeValidator;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Illuminate\Support\Facades\DB;

/**
 * TR-286 (S4 to S5): record that the approved change is in force, once its effective date is reached. The change is written
 * into the organization with its history now, and not earlier. For F1, the request that made the unit's earlier version
 * moves to S6 through TR-287 in the same transaction; its history stays.
 */
final class ApplyChangeRequest extends ChangeRequestAction
{
    public function __construct(
        private readonly ChangeValidator $validator,
        private readonly ChangeApplier $applier,
    ) {}

    public function handle(User $actor, ChangeRequest $request, int $seenVersion): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $request, $seenVersion): ChangeRequest {
            $current = $this->locked($request, [ChangeRequest::APPROVED]);

            if ($current->effective_from === null || $current->effective_from->toDateString() > BusinessDate::today()->toDateString()) {
                throw DomainRuleViolation::because('organization.not_yet_effective');
            }

            // Changes are applied one at a time, so the checks below read the tree that the change is written into. Without
            // this, two moves checked against the same old tree could each pass and together make a cycle (TAC-01).
            DB::statement("select pg_advisory_xact_lock(hashtext('organization.apply'))");

            // The organization may have changed since the decision; a request that no longer fits stays S4 for withdrawal.
            $current->validation = ['checked_at' => now()->toIso8601String(), 'issues' => $this->validator->issues($current)];

            if ($current->hasBlockingIssues()) {
                throw DomainRuleViolation::because('organization.blocking_issues');
            }

            $this->applier->apply($current, $actor);

            $current->forceFill([
                'status' => ChangeRequest::EFFECTIVE,
                'applied_by' => $actor->id,
                'applied_at' => now(),
            ])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-286', ChangeRequest::APPROVED, $actor);

            if ($current->family === ChangeRequest::F1) {
                $this->supersedePredecessors($current, $actor);
            }

            return $current;
        });
    }

    private function supersedePredecessors(ChangeRequest $successor, User $actor): void
    {
        $predecessors = ChangeRequest::query()
            ->where('family', ChangeRequest::F1)
            ->where('org_unit_id', $successor->org_unit_id)
            ->where('status', ChangeRequest::EFFECTIVE)
            ->whereKeyNot($successor->id)
            ->lockForUpdate()
            ->get();

        foreach ($predecessors as $predecessor) {
            $predecessor->forceFill(['status' => ChangeRequest::SUPERSEDED])->saveAtVersion($predecessor->lock_version);
            $predecessor->recordTransition('TR-287', ChangeRequest::EFFECTIVE, $actor, metadata: ['successor' => $successor->public_id]);
        }
    }
}
