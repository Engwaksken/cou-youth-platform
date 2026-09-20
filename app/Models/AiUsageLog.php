<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'successful',
        'error_code',
        'duration_ms',
    ];

    protected $casts = [
        'successful' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
