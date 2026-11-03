<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use Closure;

/**
 * Which accounts are privileged, so that their assisted reset needs a second administrator (SECURITY_MODEL section 3.1).
 *
 * A SUPER_ADMIN always is. Later modules add their own rules, such as the holders of an active decision assignment
 * (TASK-015) or of a privileged access profile (TASK-014), through extend().
 */
final class PrivilegedAccounts
{
    /**
     * @var list<Closure(User): bool>
     */
    private array $rules = [];

    /**
     * @param  Closure(User): bool  $rule
     */
    public function extend(Closure $rule): void
    {
        $this->rules[] = $rule;
    }

    public function isPrivileged(User $user): bool
    {
        if ($user->role_group === User::SUPER_ADMIN) {
            return true;
        }

        foreach ($this->rules as $rule) {
            if ($rule($user)) {
                return true;
            }
        }

        return false;
    }
}
