<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\AccountUpdated;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;

/**
 * Change the details of an account at the version the administrator saw (COMMAND_AND_API_CONTRACTS section 5).
 *
 * The role group is not among them: it decides what the account may ever hold, so it is set once, at creation.
 */
final class UpdateAccount extends Action
{
    /**
     * @param  array{username: string, name: string, email: ?string, staff_identifier: ?string, locale: string}  $details
     *
     * @throws StaleVersionException when someone changed the account first
     */
    public function handle(User $user, array $details, int $seenVersion): void
    {
        $this->transaction(function () use ($user, $details, $seenVersion): void {
            $user->refresh();

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $user->forceFill($details);

            if ($user->isDirty('email')) {
                // As at creation: the administrator enters the address the person gave.
                $user->forceFill(['email_verified_at' => $details['email'] === null ? null : now()]);
            }

            $changes = [];

            foreach (array_keys($details) as $field) {
                if ($user->isDirty($field)) {
                    $changes[$field] = ['before' => $user->getOriginal($field), 'after' => $user->getAttribute($field)];
                }
            }

            if ($changes === []) {
                return;
            }

            $user->saveAtVersion($seenVersion);

            AccountUpdated::dispatch($user, $changes);
        });
    }
}
