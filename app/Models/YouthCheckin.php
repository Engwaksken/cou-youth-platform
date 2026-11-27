<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class YouthCheckin extends Model
{
    protected $fillable = ['user_id', 'checked_in_on'];

    protected $casts = ['checked_in_on' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
