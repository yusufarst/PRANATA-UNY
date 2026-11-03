<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Support\ChangeValidator;

/**
 * TR-282 (S1 to S2): submit the draft for validation. The checks run now and their result is recorded with the request.
 */
final class SubmitChangeRequest extends ChangeRequestAction
{
    public function __construct(private readonly ChangeValidator $validator) {}

    public function handle(User $actor, ChangeRequest $request, int $seenVersion): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $request, $seenVersion): ChangeRequest {
            $current = $this->locked($request, [ChangeRequest::DRAFT]);

            $current->forceFill([
                'status' => ChangeRequest::VALIDATED,
                'validation' => ['checked_at' => now()->toIso8601String(), 'issues' => $this->validator->issues($current)],
                'submitted_by' => $actor->id,
                'submitted_at' => now(),
            ])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-282', ChangeRequest::DRAFT, $actor, metadata: ['issues' => $current->validation['issues'] ?? []]);

            return $current;
        });
    }
}
