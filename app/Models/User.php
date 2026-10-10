<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const CMS_ROLES = [
        'super_admin',
        'provincial_admin',
        'diocesan_admin',
        'archdeaconry_admin',
        'parish_admin',
        'local_church_admin',
        'content_manager',
        'events_manager',
        'safeguarding_officer',
        'finance_admin',
        'donations_manager',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organisationRoles(): HasMany
    {
        return $this->hasMany(UserOrganisationRole::class);
    }

    public function hasCmsAccess(): bool
    {
        return $this->organisationRoles()
            ->where('is_active', true)
            ->whereIn('role', self::CMS_ROLES)
            ->exists();
    }

    /**
     * Check whether the user has an active CMS role in the requested scope.
     * A null organisation scope checks for any active CMS assignment.
     */
    public function hasCmsAccessIn(?int $organisationUnitId = null): bool
    {
        return $this->organisationRoles()
            ->where('is_active', true)
            ->whereIn('role', self::CMS_ROLES)
            ->when($organisationUnitId !== null, function ($query) use ($organisationUnitId): void {
                $query->where(function ($scope) use ($organisationUnitId): void {
                    $scope->whereNull('organisation_unit_id')
                        ->orWhere('organisation_unit_id', $organisationUnitId);
                });
            })
            ->exists();
    }

    /**
     * Check a permission on an active assignment. Global assignments also
     * apply when a specific organisation is supplied.
     */
    public function hasOrganisationPermission(string $permission, ?int $organisationUnitId = null): bool
    {
        return $this->organisationRoles()
            ->where('is_active', true)
            ->when($organisationUnitId !== null, function ($query) use ($organisationUnitId): void {
                $query->where(function ($scope) use ($organisationUnitId): void {
                    $scope->whereNull('organisation_unit_id')
                        ->orWhere('organisation_unit_id', $organisationUnitId);
                });
            })
            ->get()
            ->contains(fn (UserOrganisationRole $assignment): bool => $assignment->hasPermission($permission));
    }
}
