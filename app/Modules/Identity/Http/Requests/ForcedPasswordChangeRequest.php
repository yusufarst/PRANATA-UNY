<?php

namespace App\Modules\Identity\Http\Requests;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use App\Modules\Identity\Support\OneTimeSecret;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ForcedPasswordChangeRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * The temporary password is the current one, and the new one may not repeat it.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => $this->currentPasswordRules(),
            'password' => [...$this->passwordRules($this->user()), 'different:current_password'],
        ];
    }

    /**
     * A one-time secret serves the change only within its life (SECURITY_MODEL section 3.1), also in a session that began
     * before it closed.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->user();

                if (! $validator->errors()->has('current_password') && $user instanceof User && OneTimeSecret::isClosedFor($user)) {
                    $validator->errors()->add('current_password', __('auth.secret_expired'));
                }
            },
        ];
    }
}
