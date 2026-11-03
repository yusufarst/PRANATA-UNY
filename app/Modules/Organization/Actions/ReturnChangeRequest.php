<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;

/**
 * TR-283 (S2 to S1): the reviewer asks for a correction with an explicit reason; the request keeps its content.
 */
final class ReturnChangeRequest extends ChangeRequestAction
{
    public function handle(User $actor, ChangeRequest $request, string $reason, int $seenVersion): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $request, $reason, $seenVersion): ChangeRequest {
            $current = $this->locked($request, [ChangeRequest::VALIDATED]);

            $current->forceFill(['status' => ChangeRequest::DRAFT])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-283', ChangeRequest::VALIDATED, $actor, $reason);

            return $current;
        });
    }
}
