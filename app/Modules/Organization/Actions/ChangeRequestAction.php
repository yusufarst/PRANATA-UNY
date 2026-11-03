<?php

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;

/**
 * The shared guard of the WF-029 transitions: the request is read again under its row lock and must be in a state the
 * transition starts from. The version the user saw is checked when the request is written (saveAtVersion).
 */
abstract class ChangeRequestAction extends Action
{
    /**
     * @param  list<string>  $from
     */
    protected function locked(ChangeRequest $request, array $from): ChangeRequest
    {
        $current = ChangeRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();

        if (! in_array($current->status, $from, true)) {
            throw DomainRuleViolation::because('organization.transition_not_allowed');
        }

        return $current;
    }
}
