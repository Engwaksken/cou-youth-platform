<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class UserOrganisationRole extends Model
{
    protected static function booted(): void
    {
        static::saving(function (self $assignment): void {
            $catalogRole = OrganisationRole::query()
                ->where('name', $assignment->role)
                ->where(function ($query) use ($assignment): void {
                    $query->whereNull('organisation_unit_id')
                        ->orWhere('organisation_unit_id', $assignment->organisation_unit_id);
                })
                ->orderByRaw('CASE WHEN organisation_unit_id = ? THEN 0 ELSE 1 END', [$assignment->organisation_unit_id])
                ->first();

            // Legacy built-in roles may not yet have catalog entries. Keep their
            // existing per-assignment permissions until a catalog role exists.
            if ($catalogRole !== null) {
                $assignment->permissions = $catalogRole->permissions;
            }
        });

        static::updating(function (self $assignment): void {
            if (! $assignment->isDirty(['role', 'is_active', 'user_id'])) {
                return;
            }

            self::guardLastActiveSuperAdmin($assignment);
        });

        static::deleting(function (self $assignment): void {
            self::guardLastActiveSuperAdmin($assignment);
        });
    }

    private static function guardLastActiveSuperAdmin(self $assignment): void
    {
        if ($assignment->role !== 'super_admin' || ! $assignment->is_active) {
            return;
        }

        $anotherUserHasActiveAssignment = self::query()
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->where('user_id', '<>', $assignment->user_id)
            ->exists();

        if (! $anotherUserHasActiveAssignment) {
            throw new RuntimeException('The last active super-admin assignment cannot be removed or deactivated.');
        }
    }
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'organisation_unit_id',
        'role',
        'permissions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organisationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganisationUnit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->is_active
            && in_array($permission, $this->permissions ?? [], true);
    }
}
