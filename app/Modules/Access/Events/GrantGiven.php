<?php

namespace App\Modules\Access\Events;

use App\Modules\Access\Models\AccessGrant;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator gave a grant: ACTIVE at once, or PENDING_APPROVAL when it is privileged (WF-002, TAC-03).
 */
final class GrantGiven
{
    use Dispatchable;

    public function __construct(public readonly AccessGrant $grant) {}
}
