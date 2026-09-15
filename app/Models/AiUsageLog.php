<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id','module','provider','model','input_tokens','output_tokens',
        'successful','error_code','duration_ms',
    ];

    protected $casts = ['successful' => 'boolean'];
}
