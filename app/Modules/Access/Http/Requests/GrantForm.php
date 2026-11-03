<?php

namespace App\Modules\Access\Http\Requests;

use App\Modules\Access\Actions\GrantAccess;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\ScopeType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A new grant (PG-503): the user by username, one profile or one capability, the scope with its unit or policy family, the
 * period and the reason. GrantAccess checks the rules of the access model itself.
 */
class GrantForm extends FormRequest
{
    public const PROFILE = 'profile';

    public const CAPABILITY = 'capability';

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $scope = $this->scope();

        return [
            'username' => ['required', 'string', 'max:60'],
            'target' => ['required', Rule::in([self::PROFILE, self::CAPABILITY])],
            'profile' => ['exclude_unless:target,'.self::PROFILE, 'required', 'string', 'max:60'],
            'capability' => ['exclude_unless:target,'.self::CAPABILITY, 'required', 'string', Rule::in(CapabilityCatalog::all())],
            'scope_type' => ['required', Rule::enum(ScopeType::class)],
            'unit' => [Rule::requiredIf(in_array($scope, [ScopeType::OrgUnit, ScopeType::OrgUnitSubtree], true)), 'nullable', 'string', 'size:26'],
            'scope_key' => [Rule::requiredIf($scope === ScopeType::PolicyFamily), 'nullable', 'string', 'max:80', 'regex:'.GrantAccess::POLICY_FAMILY_PATTERN],
            'effective_from' => ['required', 'date_format:Y-m-d'],
            'effective_to' => ['nullable', 'date_format:Y-m-d', 'after:effective_from'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (['username', 'target', 'profile', 'capability', 'scope_type', 'unit', 'scope_key', 'effective_from', 'effective_to', 'reason'] as $field) {
            $text = __("access.fields.{$field}");
            $attributes[$field] = is_string($text) ? $text : $field;
        }

        return $attributes;
    }

    public function scope(): ?ScopeType
    {
        return ScopeType::tryFrom($this->string('scope_type')->value());
    }
}
