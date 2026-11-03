<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;

/**
 * TR-288 (S1 to S4, to S7): withdraw the request with its reason. A version already in force is never withdrawn this way;
 * changing it takes a new request.
 */
final class WithdrawChangeRequest extends ChangeRequestAction
{
    public function handle(User $actor, ChangeRequest $request, string $reason, int $seenVersion): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $request, $reason, $seenVersion): ChangeRequest {
            $current = $this->locked($request, ChangeRequest::OPEN);
            $from = $current->status;

            $current->forceFill(['status' => ChangeRequest::WITHDRAWN, 'closed_reason' => $reason])->saveAtVersion($seenVersion);

            $current->recordTransition('TR-288', $from, $actor, $reason);

            return $current;
        });
    }
}
