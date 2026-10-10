<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserOrganisationRole extends Model
{
    /**
     * Serialize reference changes against catalog-role deletion. Acquire both
     * old and new role rows in ascending ID order to avoid lock-order cycles.
     */
    public function save(array $options = []): bool
    {
        if (DB::transactionLevel() > 0) {
            $this->lockReferencedRoles();

            return parent::save($options);
        }

        return DB::transaction(function () use ($options): bool {
            $this->lockReferencedRoles();

            return parent::save($options);
        });
    }

    private function lockReferencedRoles(): void
    {
        $roleIds = array_filter([
            $this->exists ? $this->getRawOriginal('organisation_role_id') : null,
            $this->getAttribute('organisation_role_id'),
        ], static fn ($id): bool => $id !== null && $id !== '');
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        sort($roleIds, SORT_NUMERIC);

        foreach ($roleIds as $roleId) {
            OrganisationRole::query()->whereKey($roleId)->lockForUpdate()->firstOrFail();
        }
    }

    protected static function booted(): void
    {
        static::saving(function (self $assignment): void {
            $catalogRole = $assignment->organisationRole;

            // Legacy assignments without a catalog reference retain their
            // existing per-assignment permissions.
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
        'organisation_role_id',
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

    public function organisationRole(): BelongsTo
    {
        return $this->belongsTo(OrganisationRole::class);
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
