<?php

namespace App\Modules\Access\Events;

use App\Modules\Access\Models\AccessGrant;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator revoked a pending or active grant, with a reason (TAC-04).
 */
final class GrantRevoked
{
    use Dispatchable;

    public function __construct(public readonly AccessGrant $grant) {}
}
