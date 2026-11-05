<?php

namespace App\Modules\Access\Events;

use App\Modules\Access\Models\ProcessAssignment;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A user was assigned a role on an object, after the separation-of-duties rules held (TASK-015 TAC-01).
 */
final class AssignmentGiven
{
    use Dispatchable;

    public function __construct(public readonly ProcessAssignment $assignment) {}
}
