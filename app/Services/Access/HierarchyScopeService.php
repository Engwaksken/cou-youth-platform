<?php

declare(strict_types=1);

namespace App\Services\Access;

use App\Models\OrganisationUnit;
use Illuminate\Support\Collection;

final class HierarchyScopeService
{
    /** @return Collection<int, int> */
    public function allowedUnitIds($user): Collection
    {
        if (! $user) {
            return collect();
        }

        if ($this->isSuperAdmin($user)) {
            return OrganisationUnit::query()->pluck('id')->map(fn ($id): int => (int) $id);
        }

        $direct = $user->organisationRoles()
            ->where('is_active', true)
            ->whereNotNull('organisation_unit_id')
            ->pluck('organisation_unit_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $all = $direct;
        $frontier = $direct;

        while ($frontier->isNotEmpty()) {
            $children = OrganisationUnit::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);

            $new = $children->diff($all)->values();
            if ($new->isEmpty()) {
                break;
            }

            $all = $all->merge($new)->unique()->values();
            $frontier = $new;
        }

        return $all->values();
    }

    public function scopeQuery($query, $user, string $column = 'organisation_unit_id')
    {
        if ($this->isSuperAdmin($user)) {
            return $query;
        }

        return $query->whereIn($column, $this->allowedUnitIds($user));
    }

    public function canManage($user, ?int $unitId): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $unitId !== null && $this->allowedUnitIds($user)->contains($unitId);
    }

    public function isSuperAdmin($user): bool
    {
        return (bool) $user?->organisationRoles()
            ->where('is_active', true)
            ->where('role', 'super_admin')
            ->exists();
    }
}
