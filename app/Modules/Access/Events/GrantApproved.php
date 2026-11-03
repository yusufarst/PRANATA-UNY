<?php

namespace App\Modules\Access\Events;

use App\Modules\Access\Models\AccessGrant;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A second person approved a privileged grant, which now applies (SOD-05, TAC-03).
 */
final class GrantApproved
{
    use Dispatchable;

    public function __construct(public readonly AccessGrant $grant) {}
}
