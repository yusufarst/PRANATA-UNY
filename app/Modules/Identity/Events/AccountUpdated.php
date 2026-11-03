<?php

namespace App\Modules\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An administrator changed the details of an account.
 */
final class AccountUpdated
{
    use Dispatchable;

    /**
     * @param  array<string, array{before: mixed, after: mixed}>  $changes
     */
    public function __construct(
        public readonly User $user,
        public readonly array $changes,
    ) {}
}
