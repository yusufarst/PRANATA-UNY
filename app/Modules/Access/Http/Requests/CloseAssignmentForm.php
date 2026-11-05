<?php

namespace App\Modules\Access\Http\Requests;

use App\Modules\Access\Models\ProcessAssignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ending or revoking an assignment states which, its reason and the version of the assignment the user saw.
 */
class CloseAssignmentForm extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([ProcessAssignment::ENDED, ProcessAssignment::REVOKED])],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $text = __('access.fields.close_reason');

        return ['reason' => is_string($text) ? $text : 'reason'];
    }
}
