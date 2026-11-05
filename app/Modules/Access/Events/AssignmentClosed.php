<?php

namespace App\Modules\Access\Events;

use App\Models\User;
use App\Modules\Access\Models\ProcessAssignment;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An assignment was ended, because its task is over, or revoked, because it was wrong; its status says which. The actor is
 * the administrator who closed it.
 */
final class AssignmentClosed
{
    use Dispatchable;

    public function __construct(public readonly ProcessAssignment $assignment, public readonly User $actor) {}
}
