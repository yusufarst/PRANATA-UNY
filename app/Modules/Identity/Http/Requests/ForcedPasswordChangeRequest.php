<?php

namespace App\Modules\Identity\Http\Requests;

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
}
