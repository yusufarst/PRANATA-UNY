<?php

namespace App\Concerns;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * The profile is Password::defaults() (SECURITY_MODEL section 3); a password that equals the account's username or
     * email, in any case, is refused.
     *
     * @return array<int, Password|ValidationRule|Closure|array<mixed>|string>
     */
    protected function passwordRules(?User $user = null): array
    {
        return ['required', 'string', Password::default(), 'confirmed', function (string $attribute, mixed $value, Closure $fail) use ($user): void {
            $password = is_string($value) ? mb_strtolower($value) : null;

            foreach ([$user?->username, $user?->email] as $identity) {
                if ($password !== null && is_string($identity) && $password === mb_strtolower($identity)) {
                    $fail(__('auth.password_is_identity'));

                    return;
                }
            }
        }];
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, Password|ValidationRule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
