<?php

namespace App\Modules\Identity\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The details an administrator enters for an account (PG-502, DATA_MODEL section 5). The role group is chosen only at creation,
 * and only INTERNAL or VENDOR (TASK-011 TAC-01); a change needs the version the administrator saw.
 */
class AccountRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $creating = $this->route('user') === null;

        return [
            ...($creating ? ['role_group' => ['required', 'string', Rule::in([User::INTERNAL, User::VENDOR])]] : []),
            'username' => ['bail', 'required', 'string', 'max:60', 'regex:/^[A-Za-z0-9._]+$/', $this->uniqueIgnoringCase('username')],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['bail', 'nullable', 'string', 'email', 'max:255', $this->uniqueIgnoringCase('email')],
            'staff_identifier' => ['nullable', 'string', 'max:40'],
            'locale' => ['required', 'string', Rule::in(['id', 'en'])],
            ...($creating ? [] : ['lock_version' => ['required', 'integer', 'min:0']]),
        ];
    }

    /**
     * @return array{username: string, name: string, email: ?string, staff_identifier: ?string, locale: string}
     */
    public function details(): array
    {
        return [
            'username' => $this->string('username')->value(),
            'name' => $this->string('name')->trim()->value(),
            'email' => $this->filled('email') ? $this->string('email')->trim()->value() : null,
            'staff_identifier' => $this->filled('staff_identifier') ? $this->string('staff_identifier')->trim()->value() : null,
            'locale' => $this->string('locale')->value(),
        ];
    }

    /**
     * Sign-in matches both columns without regard to case, so they are unique that way too (users_*_lower_unique).
     */
    private function uniqueIgnoringCase(string $column): Closure
    {
        $user = $this->route('user');

        return function (string $attribute, mixed $value, Closure $fail) use ($column, $user): void {
            $taken = User::query()
                ->whereRaw("lower({$column}) = lower(?)", [(string) $value])
                ->when($user instanceof User, fn ($query) => $query->whereKeyNot($user->getKey()))
                ->exists();

            if ($taken) {
                $fail(__("identity.{$column}_taken"));
            }
        };
    }
}
