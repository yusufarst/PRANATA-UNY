<?php

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Models\OrgUnit;

/**
 * TR-281 from entry: a new draft change request with its family, scope, reason, source and proposed effective date. Nothing
 * changes in the organization yet.
 */
final class DraftChangeRequest extends ChangeRequestAction
{
    /**
     * @param  array{kind: string, payload: array<string, mixed>, reason: string, source_ref: ?string, proposed_effective_from: string}  $details
     */
    public function handle(User $actor, ?OrgUnit $unit, array $details): ChangeRequest
    {
        return $this->transaction(function () use ($actor, $unit, $details): ChangeRequest {
            $request = ChangeRequest::query()->create([
                'family' => ChangeRequest::familyOf($details['kind']),
                'kind' => $details['kind'],
                'org_unit_id' => $unit?->id,
                'status' => ChangeRequest::DRAFT,
                'payload' => $details['payload'],
                'reason' => $details['reason'],
                'source_ref' => $details['source_ref'],
                'proposed_effective_from' => $details['proposed_effective_from'],
                'created_by' => $actor->id,
            ]);

            $request->recordTransition('TR-281', null, $actor);

            return $request;
        });
    }
}
