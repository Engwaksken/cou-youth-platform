<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Models\OrganisationRole;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserOrganisationRoleRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('userOrganisationRole')) ?? false; }

    public function rules(): array
    {
        return [
            'organisation_unit_id' => ['sometimes', 'nullable', 'exists:organisation_units,id'],
            'role' => ['sometimes', 'required', 'string', function ($attribute, $value, $fail): void {
                $unitId = $this->input('organisation_unit_id', $this->route('userOrganisationRole')?->organisation_unit_id);
                $query = OrganisationRole::query()->where('name', $value)->where(function ($query) use ($unitId): void {
                    $query->whereNull('organisation_unit_id');
                    if ($unitId !== null) $query->orWhere('organisation_unit_id', (int) $unitId);
                });
                if ($query->exists()) return;
                // Preserve legacy built-ins only if they have not entered the catalog.
                if (! OrganisationRole::query()->where('name', $value)->exists() && in_array($value, User::CMS_ROLES, true)) return;
                $fail('The selected role is not available for this organisation.');
            }],
            'organisation_role_id' => ['prohibited'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function catalogRoleId(): ?int
    {
        $assignment = $this->route('userOrganisationRole');
        $unitId = $this->input('organisation_unit_id', $assignment?->organisation_unit_id);
        $roleName = $this->input('role', $assignment?->role);
        $role = OrganisationRole::query()->where('name', $roleName)
            ->where(function ($query) use ($unitId): void {
                $query->whereNull('organisation_unit_id');
                if ($unitId !== null) $query->orWhere('organisation_unit_id', (int) $unitId);
            })
            ->orderByRaw('organisation_unit_id IS NULL')
            ->first();

        return $role?->id;
    }
}
