<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Support\ChangeValidator;
use App\Modules\Platform\Exceptions\DomainRuleViolation;

/**
 * TR-284 (S2 to S3): send the validated request for the authority's decision. The checks run again, since the organization
 * may have changed since TR-282; a blocking issue keeps the request in S2.
 */
final class RequestDecision extends ChangeRequestAction
{
    public function __construct(private readonly ChangeValidator $validator) {}

    public function handle(User $actor, ChangeRequest $request, int $seenVersion): ChangeRequest
    {
        $result = $this->transaction(function () use ($actor, $request, $seenVersion): ChangeRequest {
            $current = $this->locked($request, [ChangeRequest::VALIDATED]);
            $validation = ['checked_at' => now()->toIso8601String(), 'issues' => $this->validator->issues($current)];
            $current->validation = $validation;

            if ($current->hasBlockingIssues()) {
                // The fresh result is kept, so the reviewer sees why; the state stays S2.
                $current->saveAtVersion($seenVersion);

                return $current;
            }

            $current->forceFill(['status' => ChangeRequest::AWAITING_DECISION])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-284', ChangeRequest::VALIDATED, $actor);

            return $current;
        });

        if ($result->status === ChangeRequest::VALIDATED) {
            throw DomainRuleViolation::because('organization.blocking_issues');
        }

        return $result;
    }
}
