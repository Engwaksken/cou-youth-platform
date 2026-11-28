<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\AssignableRoles;
use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class StoreUserRequest extends FormRequest
{
    public const PHONE_PATTERN = '/^\+?[0-9][0-9\s\-()]{6,19}$/';

    public function authorize(): bool { return $this->user()?->can('create', \App\Models\User::class) ?? false; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:'.self::PHONE_PATTERN],
            'location' => ['nullable', 'string', 'max:255'],
            'diocese_id' => ['nullable', 'integer', ChurchOfUgandaDioceses::existsRule()],
            'role' => ['nullable', 'string', function ($attribute, $value, $fail): void {
                // A diocese-level role without a diocese would become platform-wide.
                if ($this->roleUnitId() === null && ! in_array($value, AssignableRoles::GLOBAL_ROLES, true)) {
                    $fail('Choose a diocese for this role.');

                    return;
                }
                if (! AssignableRoles::isAssignable($value, $this->roleUnitId())) {
                    $fail('The selected role is not available for this diocese.');
                }
            }],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['diocese_id' => 'diocese'];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Enter a valid phone number, e.g. +256 772 123456.'];
    }

    /** Organisation scope for the role: none for platform-wide roles, otherwise the diocese. */
    public function roleUnitId(): ?int
    {
        if (in_array($this->input('role'), AssignableRoles::GLOBAL_ROLES, true)) {
            return null;
        }

        return $this->filled('diocese_id') ? (int) $this->input('diocese_id') : null;
    }
}
