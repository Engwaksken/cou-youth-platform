<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Field contract (page_slides table):
 * - page: string, default 'home' (which page the slide belongs to)
 * - title: string, nullable
 * - subtitle: string, nullable
 * - media_type: string, default 'image' ('image'|'video')
 * - media_path: string, nullable (relative public path)
 * - link_url: string, nullable
 * - sort_order: unsigned int, default 0
 * - is_active: bool, default true
 */
class PageSlide extends Model
{
    protected $fillable = ['page', 'title', 'subtitle', 'media_type', 'media_path', 'link_url', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    /** @param Builder<PageSlide> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<PageSlide> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** @param Builder<PageSlide> $query */
    public function scopeForPage(Builder $query, string $page): Builder
    {
        return $query->where('page', $page);
    }
}
