<?php

namespace App\Modules\Organization\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * TR-283 and TR-288 state their reason and the version the user saw.
 */
class ReasonForm extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ];
    }
}
