<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationCampaign extends Model
{
    protected $fillable = [
        'title','slug','description','featured_image','target_amount','currency',
        'starts_at','ends_at','status','allow_anonymous','created_by',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'allow_anonymous' => 'boolean',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }
}
