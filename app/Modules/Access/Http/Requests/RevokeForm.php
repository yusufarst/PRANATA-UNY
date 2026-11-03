<?php

namespace App\Modules\Access\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A revocation states its reason and the version of the grant the user saw.
 */
class RevokeForm extends FormRequest
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

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $text = __('access.fields.revoke_reason');

        return ['reason' => is_string($text) ? $text : 'reason'];
    }
}
