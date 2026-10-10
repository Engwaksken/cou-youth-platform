<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

final class UpdateOrganisationRoleRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('organisationRole')) ?? false; }

    public function rules(): array
    {
        $role = $this->route('organisationRole');
        return [
            'organisation_unit_id' => ['sometimes', 'required', 'integer', 'exists:organisation_units,id'],
            'name' => ['sometimes', 'required', 'string', 'max:100', function ($attribute, $value, $fail): void {
                $builtInNames = array_map(fn (string $name): string => strtolower(trim($name)), User::CMS_ROLES);
                if (in_array(strtolower(trim((string) $value)), $builtInNames, true)) {
                    $fail('The selected name is reserved for a built-in role.');
                }
            }, Rule::unique('organisation_roles', 'name')->where(fn ($query) => $query->where('organisation_unit_id', $this->input('organisation_unit_id', $role?->organisation_unit_id)))->ignore($role?->id)],
            'permissions' => ['sometimes', 'nullable', 'array', 'max:100'],
            'permissions.*' => ['required', 'string', Rule::in(OrganisationRolePermissions::allowed())],
        ];
    }
}
