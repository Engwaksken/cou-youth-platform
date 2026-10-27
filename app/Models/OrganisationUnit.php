<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganisationUnit extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'type', 'name', 'code', 'email', 'phone', 'address', 'latitude', 'longitude', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(OrganisationRole::class);
    }
}
