<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSlide extends Model
{
    protected $fillable = ['page', 'title', 'subtitle', 'media_type', 'media_path', 'link_url', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];
}
