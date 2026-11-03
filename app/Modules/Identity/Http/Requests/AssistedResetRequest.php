<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Models\PasswordReset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * An assisted reset names how the administrator checked who asked (OPERATIONS_RUNBOOK RB-08), with an optional note.
 */
class AssistedResetRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identity_check_method' => ['required', 'string', Rule::in(PasswordReset::IDENTITY_CHECKS)],
            'identity_check_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function note(): ?string
    {
        $note = $this->string('identity_check_note')->trim()->value();

        return $note === '' ? null : $note;
    }
}
