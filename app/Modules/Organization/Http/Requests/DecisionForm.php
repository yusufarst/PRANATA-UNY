<?php

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Actions\DecideChangeRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * TR-285: approve with an effective date, or reject with a reason; both cite the decision.
 */
class DecisionForm extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([DecideChangeRequest::APPROVE, DecideChangeRequest::REJECT])],
            'effective_from' => ['required_if:decision,'.DecideChangeRequest::APPROVE, 'nullable', 'date_format:Y-m-d'],
            'decision_ref' => ['required', 'string', 'max:150'],
            'reason' => ['required_if:decision,'.DecideChangeRequest::REJECT, 'nullable', 'string', 'min:5', 'max:2000'],
            'lock_version' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array{decision: string, effective_from: ?string, decision_ref: string, reason: ?string}
     */
    public function details(): array
    {
        $approve = $this->input('decision') === DecideChangeRequest::APPROVE;
        $reason = trim($this->string('reason')->value());

        return [
            'decision' => $this->string('decision')->value(),
            'effective_from' => $approve ? $this->string('effective_from')->value() : null,
            'decision_ref' => trim($this->string('decision_ref')->value()),
            'reason' => $reason === '' ? null : $reason,
        ];
    }
}
