<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'reference','campaign_id','user_id','payment_gateway_id','amount','currency',
        'status','is_anonymous','donor_name','donor_email','donor_phone',
        'external_transaction_id','receipt_number','gateway_response','paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];
}
