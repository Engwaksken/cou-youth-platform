<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Models\OrganisationRole;
use App\Models\UserOrganisationRole;
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
        $unitId = $this->input('organisation_unit_id');
        $matching = OrganisationRole::query()->where('name', $value)->where(function ($query) use ($unitId): void {
            $query->whereNull('organisation_unit_id');
            if ($unitId !== null) $query->orWhere('organisation_unit_id', (int) $unitId);
        })->exists();
        if ($matching) return;

        // Historical CMS role assignments predate the role catalog; retain these only
        // when no catalog entry with that name exists anywhere.
        if (! OrganisationRole::query()->where('name', $value)->exists() && in_array($value, User::CMS_ROLES, true)) return;
        $fail('The selected role is not available for this organisation.');
    }

    public function catalogRoleId(): ?int
    {
        $unitId = $this->input('organisation_unit_id');
        $role = OrganisationRole::query()->where('name', $this->input('role'))
            ->where(function ($query) use ($unitId): void {
                $query->whereNull('organisation_unit_id');
                if ($unitId !== null) $query->orWhere('organisation_unit_id', (int) $unitId);
            })
            ->orderByRaw('organisation_unit_id IS NULL')
            ->first();

        return $role?->id;
    }
}
