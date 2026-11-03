<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\AccountUpdated;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;

/**
 * Change the details of an account at the version the administrator saw (COMMAND_AND_API_CONTRACTS section 5).
 *
 * The role group is not among them: it decides what the account may ever hold, so it is set once, at creation.
 *
 * Nor is the email of another privileged account: a reset by email goes to that address, so one administrator who changed
 * it could take the account over without the second administrator its assisted reset needs (TASK-011 TAC-03).
 */
final class UpdateAccount extends Action
{
    public function __construct(private readonly PrivilegedAccounts $privileged) {}

    /**
     * @param  array{username: string, name: string, email: ?string, staff_identifier: ?string, locale: string}  $details
     *
     * A {@see StaleVersionException} from inside the transaction reaches the caller when someone changed the account first.
     */
    public function handle(User $actor, User $user, array $details, int $seenVersion): void
    {
        $this->transaction(function () use ($actor, $user, $details, $seenVersion): void {
            $user->refresh();

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $user->forceFill($details);

            if ($user->isDirty('email') && ! $user->is($actor) && $this->privileged->isPrivileged($user)) {
                throw DomainRuleViolation::because('identity.privileged_email_locked');
            }

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
