<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;

/**
 * TR-281 from S1 or S2: correct the draft, which takes it back to S1 and clears the earlier validation. The kind and the
 * unit stay; a different change is a new request.
 */
final class CorrectChangeRequest extends ChangeRequestAction
{
    /**
     * @param  array{payload: array<string, mixed>, reason: string, source_ref: ?string, proposed_effective_from: string}  $details
     */
    public function handle(User $actor, ChangeRequest $request, array $details, int $seenVersion): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $request, $details, $seenVersion): ChangeRequest {
            $current = $this->locked($request, [ChangeRequest::DRAFT, ChangeRequest::VALIDATED]);
            $from = $current->status;

            $current->forceFill([
                ...$details,
                'status' => ChangeRequest::DRAFT,
                'validation' => null,
            ])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-281', $from, $actor);

            return $current;
        });
    }
}
