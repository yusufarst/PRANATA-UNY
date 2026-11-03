<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\TwoFactorResetApproved;
use App\Modules\Identity\Models\TwoFactorReset;
use App\Modules\Identity\Notifications\TwoFactorWasReset;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Str;

/**
 * The second administrator approves the request, so the TOTP and recovery codes of the account are gone (SECURITY_MODEL
 * section 3.1, TASK-012 TAC-03). Every session and remember cookie of the account ends; at its next sign-in the password
 * alone lets it in, and an account that must have TOTP sets it up again before Administration (TAC-02). The approver is
 * neither the requester nor the account itself; the database refuses that too.
 */
final class ApproveTwoFactorReset extends Action
{
    public function handle(User $approver, TwoFactorReset $reset): void
    {
        $this->transaction(function () use ($approver, $reset): void {
            $reset = TwoFactorReset::query()->whereKey($reset->getKey())->lockForUpdate()->firstOrFail();

            if (! $reset->isOpen()) {
                throw DomainRuleViolation::because('identity.two_factor_reset_not_pending');
            }

            if ($approver->getKey() === $reset->requested_by || $approver->getKey() === $reset->user_id) {
                throw DomainRuleViolation::because('identity.reset_needs_second_admin');
            }

            $user = User::query()->whereKey($reset->user_id)->lockForUpdate()->firstOrFail();

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => Str::random(60),
            ])->save();

            $reset->forceFill([
                'status' => TwoFactorReset::APPROVED,
                'approved_by' => $approver->getKey(),
                'decided_at' => now(),
            ])->save();

            $ended = $this->connection()
                ->table((string) config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();

            // Inside the transaction, so the reset and its audit record commit or roll back together (fail closed).
            TwoFactorResetApproved::dispatch($reset, $ended);

            // Once committed, so a reset that rolls back tells nobody.
            $this->connection()->afterCommit(fn () => $user->notify(new TwoFactorWasReset));
        });
    }
}
