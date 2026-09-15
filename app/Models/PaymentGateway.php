<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name','slug','provider','currency','credentials','settings',
        'is_test_mode','is_enabled','sort_order',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_test_mode' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    protected $hidden = ['credentials'];
}
