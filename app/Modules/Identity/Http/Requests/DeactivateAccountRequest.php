<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A deactivation states its reason (OPERATIONS_RUNBOOK RB-13) and the version the administrator saw.
 */
class DeactivateAccountRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ];
    }
}
