<?php

namespace App\Modules\Identity\Events;

use App\Modules\Identity\Models\PasswordReset;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An assisted reset gave the account a temporary password and ended its sessions (TASK-011 TAC-02). The password itself is
 * never carried.
 */
final class PasswordResetIssued
{
    use Dispatchable;

    public function __construct(
        public readonly PasswordReset $reset,
        public readonly int $endedSessions,
    ) {}
}
