<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChurchLocation;
use App\Models\Content;
use App\Models\Course;
use App\Models\Event;
use App\Models\LifeGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * One search box in the header for the whole site. Looks across everything a
 * visitor can already browse publicly, with the same visibility rules as each
 * list page, and groups the results by kind.
 */
final class SearchController extends Controller
{
    private const PER_GROUP = 5;

    public function __invoke(Request $request): View
    {
        $term = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $groups = [];

        if (mb_strlen($term) >= 2) {
            $like = '%'.addcslashes($term, '%_\\').'%';

            $groups = array_filter([
                'events' => $this->group(
                    'events',
                    fn () => Event::query()
                        ->where('status', 'published')
                        ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('venue', 'like', $like))
                        ->orderByRaw('case when starts_at >= ? then 0 else 1 end', [now()->subHours(3)])
                        ->orderBy('starts_at'),
                ),
                'courses' => $this->group(
                    'courses',
                    fn () => Course::query()
                        ->where('is_published', true)
                        ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
                        ->orderBy('title'),
                ),
                'life_groups' => $this->group(
                    'life_groups',
                    fn () => LifeGroup::query()
                        ->where('is_active', true)
                        ->with('organisationUnit')
                        ->where(fn (Builder $q) => $q->where('name', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhere('meeting_location', 'like', $like)
                            ->orWhereHas('organisationUnit', fn (Builder $unit) => $unit->where('name', 'like', $like)))
                        ->orderBy('name'),
                ),
                'stories' => $this->group(
                    'contents',
                    fn () => Content::query()
                        ->where('status', 'published')
                        ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('summary', 'like', $like))
                        ->latest(),
                ),
                'churches' => $this->group(
                    'church_locations',
                    fn () => ChurchLocation::query()
                        ->with('organisationUnit')
                        ->where(fn (Builder $q) => $q->where('name', 'like', $like)
                            ->orWhere('address', 'like', $like)
                            ->orWhereHas('organisationUnit', fn (Builder $unit) => $unit->where('name', 'like', $like)))
                        ->orderBy('name'),
                ),
            ], fn (?array $group): bool => $group !== null && $group['total'] > 0);
        }

        return view('public.search', [
            'term' => $term,
            'groups' => $groups,
            'total' => array_sum(array_column($groups, 'total')),
        ]);
    }

    /**
     * @param  callable(): Builder  $query
     * @return array{total:int,items:\Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>}|null
     */
    private function group(string $table, callable $query): ?array
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        return [
            'total' => $query()->count(),
            'items' => $query()->limit(self::PER_GROUP)->get(),
        ];
    }
}
