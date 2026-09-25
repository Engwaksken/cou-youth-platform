<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Field contract (page_cards table):
 * - page: string, default 'home'
 * - title: string, nullable (fixup migration; original required)
 * - body: text, nullable
 * - icon: string, nullable (icon class/name)
 * - image_path: string, nullable (relative public path)
 * - link_url: string, nullable
 * - sort_order: unsigned int, default 0
 * - is_active: bool, default true
 */
class PageCard extends Model
{
    protected $fillable = ['page', 'title', 'body', 'icon', 'image_path', 'link_url', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    /** @param Builder<PageCard> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<PageCard> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** @param Builder<PageCard> $query */
    public function scopeForPage(Builder $query, string $page): Builder
    {
        return $query->where('page', $page);
    }
}
