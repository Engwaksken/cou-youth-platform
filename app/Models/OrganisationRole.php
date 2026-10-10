<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrganisationRole extends Model
{
    public function newEloquentBuilder($query): Builder
    {
        return new class($query) extends Builder {
            public function update(array $values)
            {
                if (array_key_exists('permissions', $values)) {
                    throw new LogicException('Organisation role permissions must be changed through model saves.');
                }

                return parent::update($values);
            }
        };
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
}
