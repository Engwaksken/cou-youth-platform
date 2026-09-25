<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrganisationRole extends Model
{
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
}
