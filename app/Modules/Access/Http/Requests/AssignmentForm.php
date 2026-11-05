<?php

namespace App\Modules\Access\Http\Requests;

use App\Modules\Access\Support\AssignmentRoles;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A new process assignment (AssignmentPanel, PG-504): the object by its type and public reference, the role, the user by
 * username, the period and the decree reference when there is one. AssignProcessRole checks the rules itself.
 */
class AssignmentForm extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_type' => ['required', 'string', 'max:80'],
            'subject' => ['required', 'string', 'max:80'],
            'assignment_role' => ['required', 'string', Rule::in(AssignmentRoles::all())],
            'username' => ['required', 'string', 'max:60'],
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'effective_to' => ['nullable', 'date_format:Y-m-d', 'after:effective_from'],
            'decree_ref' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (['subject_type', 'subject', 'assignment_role', 'username', 'effective_from', 'effective_to', 'decree_ref'] as $field) {
            $text = __("access.fields.{$field}");
            $attributes[$field] = is_string($text) ? $text : $field;
        }

        return $attributes;
    }
}
