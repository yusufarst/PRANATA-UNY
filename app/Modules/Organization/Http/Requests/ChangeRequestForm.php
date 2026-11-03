<?php

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Models\ChangeRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The fields of a WF-029 change request (TR-281): the scope of its kind, the reason, the source and the proposed effective
 * date. On a correction the kind and the unit come from the stored request.
 */
class ChangeRequestForm extends FormRequest
{
    private const UNIT_CODE = 'regex:/^[A-Z0-9][A-Z0-9_.\-]*$/';

    private const TYPE_CODE = 'regex:/^[A-Z][A-Z0-9_]*$/';

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kind = $this->kind();

        return [
            ...($this->stored() === null ? [
                'kind' => ['required', Rule::in([...ChangeRequest::KINDS[ChangeRequest::F1], ...ChangeRequest::KINDS[ChangeRequest::F2]])],
                'unit' => [Rule::requiredIf($kind !== ChangeRequest::CREATE_UNIT), 'nullable', 'string', 'size:26'],
            ] : [
                'lock_version' => ['required', 'integer', 'min:0'],
            ]),
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'source_ref' => ['nullable', 'string', 'max:150'],
            'proposed_effective_from' => ['required', 'date_format:Y-m-d'],
            ...$this->scopeRules($kind),
        ];
    }

    public function kind(): string
    {
        return $this->stored()->kind ?? $this->string('kind')->value();
    }

    /**
     * The scope of the change as stored in the request; only the fields of its kind.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $fields = array_keys($this->scopeRules($this->kind()));
        $payload = [];

        foreach ($fields as $field) {
            $value = $this->input($field);
            $payload[$field] = is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value;
        }

        if (isset($payload['appointment'])) {
            $payload['appointment'] = (int) $payload['appointment'];
        }

        return $payload;
    }

    /**
     * @return array{payload: array<string, mixed>, reason: string, source_ref: ?string, proposed_effective_from: string}
     */
    public function details(): array
    {
        $source = trim($this->string('source_ref')->value());

        return [
            'payload' => $this->payload(),
            'reason' => trim($this->string('reason')->value()),
            'source_ref' => $source === '' ? null : $source,
            'proposed_effective_from' => $this->string('proposed_effective_from')->value(),
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    private function scopeRules(string $kind): array
    {
        return match ($kind) {
            ChangeRequest::CREATE_UNIT => [
                'code' => ['required', 'string', 'max:40', self::UNIT_CODE],
                'name' => ['required', 'string', 'max:200'],
                'short_name' => ['nullable', 'string', 'max:80'],
                'unit_type' => ['required', 'string', 'max:40', self::TYPE_CODE],
                'parent' => ['nullable', 'string', 'size:26'],
            ],
            ChangeRequest::UPDATE_UNIT => [
                'name' => ['required', 'string', 'max:200'],
                'short_name' => ['nullable', 'string', 'max:80'],
                'unit_type' => ['required', 'string', 'max:40', self::TYPE_CODE],
            ],
            ChangeRequest::MOVE_UNIT => [
                'parent' => ['nullable', 'string', 'size:26'],
            ],
            ChangeRequest::CREATE_POSITION => [
                'code' => ['required', 'string', 'max:60', self::TYPE_CODE],
                'name' => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            ChangeRequest::APPOINT => [
                'user' => ['required', 'string', 'size:26'],
                'position' => ['required', 'string', 'max:60'],
                'decree_ref' => ['required', 'string', 'max:150'],
                'effective_to' => ['nullable', 'date_format:Y-m-d', 'after:proposed_effective_from'],
            ],
            ChangeRequest::END_APPOINTMENT => [
                'appointment' => ['required', 'integer', 'min:1'],
            ],
            default => [],
        };
    }

    private function stored(): ?ChangeRequest
    {
        $stored = $this->route('changeRequest');

        return $stored instanceof ChangeRequest ? $stored : null;
    }
}
