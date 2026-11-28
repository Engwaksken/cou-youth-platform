<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\OrganisationRole;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Which role names can be assigned in a given organisation scope: catalog
 * roles that are global or belong to that unit, plus the historical CMS roles
 * that have no catalog entry.
 */
final class AssignableRoles
{
    /** Roles that always apply platform-wide rather than to one diocese. */
    public const GLOBAL_ROLES = ['super_admin', 'provincial_admin'];

    public static function isAssignable(string $name, ?int $organisationUnitId): bool
    {
        if (self::catalogRoleId($name, $organisationUnitId) !== null) {
            return true;
        }

        return in_array($name, User::CMS_ROLES, true)
            && ! OrganisationRole::query()->where('name', $name)->exists();
    }

    /** The catalog role to link, preferring one scoped to the unit over a global one. */
    public static function catalogRoleId(string $name, ?int $organisationUnitId): ?int
    {
        $role = OrganisationRole::query()->where('name', $name)
            ->where(function ($query) use ($organisationUnitId): void {
                $query->whereNull('organisation_unit_id');
                if ($organisationUnitId !== null) {
                    $query->orWhere('organisation_unit_id', $organisationUnitId);
                }
            })
            ->orderByRaw('organisation_unit_id IS NULL')
            ->first();

        return $role?->id;
    }

    /** Role names for the "new user" select: every catalog role plus legacy CMS roles. */
    public static function options(): Collection
    {
        return OrganisationRole::query()->distinct()->pluck('name')
            ->merge(User::CMS_ROLES)
            ->unique()
            ->sort()
            ->values();
    }

    public static function label(string $name): string
    {
        return ucwords(str_replace('_', ' ', $name));
    }
}
