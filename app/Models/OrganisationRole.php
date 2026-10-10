<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

class OrganisationRole extends Model
{
    private bool $savingPermissions = false;

    /** Run role catalog changes under the same row lock used by assignments. */
    public static function withRoleLock(int $roleId, callable $callback): mixed
    {
        // MySQL/MariaDB use an InnoDB row lock, held until the outermost
        // transaction commits. On SQLite lockForUpdate is a no-op; SQLite's
        // write serialization is only meaningful on a single host.
        return DB::transaction(function () use ($roleId, $callback) {
            $role = self::query()->whereKey($roleId)->lockForUpdate()->firstOrFail();

            return $callback($role);
        });
    }

    public function save(array $options = []): bool
    {
        if (! $this->exists || $this->getKey() === null) {
            // Keep trigger-driven uniqueness locks within an explicit
            // transaction too (nested calls participate in the outer one).
            return DB::transaction(fn (): bool => parent::save($options));
        }

        $roleId = (int) $this->getKey();
        $persist = fn (): bool => self::withRoleLock($roleId, function () use ($options): bool {
            return parent::save($options);
        });

        return DB::transaction(fn (): bool => $persist());
    }

    public function delete(): ?bool
    {
        if (! $this->exists || $this->getKey() === null) {
            return parent::delete();
        }

        return self::withRoleLock((int) $this->getKey(), function (self $lockedRole): ?bool {
            if ($lockedRole->assignments()->exists()) {
                throw new RuntimeException('Cannot delete an organisation role while assignments reference it.');
            }

            return parent::delete();
        });
    }

    public function newEloquentBuilder($query): Builder
    {
        return new class($query) extends Builder {
            public function update(array $values)
            {
                if (array_key_exists('permissions', $values) && ! $this->getModel()->isSavingPermissions()) {
                    throw new LogicException('Organisation role permissions must be changed through model saves.');
                }

                return parent::update($values);
            }
        };
    }

    public function isSavingPermissions(): bool
    {
        return $this->savingPermissions;
    }

    protected function performUpdate(Builder $query)
    {
        $this->savingPermissions = true;

        try {
            return parent::performUpdate($query);
        } finally {
            $this->savingPermissions = false;
        }
    }

    /** @var list<string> */
    protected $fillable = [
        'organisation_unit_id',
        'name',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function organisationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganisationUnit::class);
    }

    /** Assignments explicitly associated with this catalog role. */
    public function assignments(): HasMany
    {
        return $this->hasMany(UserOrganisationRole::class);
    }
}
