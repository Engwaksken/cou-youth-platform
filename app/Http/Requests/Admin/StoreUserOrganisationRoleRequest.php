<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\UserOrganisationRole;
use App\Support\AssignableRoles;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserOrganisationRoleRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', UserOrganisationRole::class) ?? false; }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'organisation_unit_id' => ['nullable', 'exists:organisation_units,id'],
            'role' => ['required', 'string', function ($attribute, $value, $fail): void {
                $this->validateCatalogRole($value, $fail);
            }],
            'organisation_role_id' => ['prohibited'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function validateCatalogRole(string $value, callable $fail): void
    {
        if (! AssignableRoles::isAssignable($value, $this->unitId())) {
            $fail('The selected role is not available for this organisation.');
        }
    }

    public function catalogRoleId(): ?int
    {
        return AssignableRoles::catalogRoleId((string) $this->input('role'), $this->unitId());
    }

    private function unitId(): ?int
    {
        $unitId = $this->input('organisation_unit_id');

        return $unitId === null || $unitId === '' ? null : (int) $unitId;
    }
}
