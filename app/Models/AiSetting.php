<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'provider','model','api_key','api_endpoint','temperature','max_tokens',
        'daily_limit','monthly_limit','per_user_daily_limit','timeout_seconds',
        'retry_count','system_prompt','safety_prompt','is_enabled',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'temperature' => 'decimal:2',
        'is_enabled' => 'boolean',
    ];

    protected $hidden = ['api_key'];
}
