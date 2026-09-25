<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Services\Access\HierarchyScopeService;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function __construct(
        private HierarchyScopeService $scope,
        private PlatformUpdateNotificationService $updates,
    ) {}

    public function index(Request $request)
    {
        $query = Content::query()->latest();
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }
        if ($type !== '') $query->where('type', $type);
        if ($status !== '') $query->where('status', $status);

        return view('admin.content.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'stats' => [
                'total' => Content::count(),
                'published' => Content::where('status', 'published')->count(),
                'draft' => Content::where('status', 'draft')->count(),
                'pending' => Content::where('status', 'pending')->count(),
            ],
            'typeCounts' => Content::query()->selectRaw('type, COUNT(*) total')->groupBy('type')->pluck('total', 'type'),
            'filters' => ['q' => $search, 'type' => $type, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (! empty($data['organisation_unit_id']) && ! $this->scope->canManage($request->user(), (int) $data['organisation_unit_id'])) abort(403);
        $data['created_by'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        if (($data['status'] ?? 'draft') === 'published') $data['published_at'] = now();

        $content = Content::create($data);

        if ($content->status === 'published') {
            $this->updates->notifyYouth(
                'New '.str_replace('_', ' ', $content->type).': '.$content->title,
                $content->summary ?: 'New content has been published on the Church of Uganda Youth Platform.',
                route('public.news'),
                $content->organisation_unit_id,
                $content->target_age_categories,
                $request->user()->id,
            );
        }

        return back()->with('success', 'Content saved successfully.');
    }

    public function update(Request $request, Content $content)
    {
        if ($content->organisation_unit_id && ! $this->scope->canManage($request->user(), $content->organisation_unit_id)) abort(403);
        $data = $this->validated($request);
        if (($data['title'] ?? null) !== $content->title) $data['slug'] = $this->uniqueSlug($data['title'], $content->id);
        if (($data['status'] ?? null) === 'published' && ! $content->published_at) $data['published_at'] = now();
        $content->update($data);

        if ($content->status === 'published') {
            $this->updates->notifyYouth(
                'Updated '.str_replace('_', ' ', $content->type).': '.$content->title,
                $content->summary ?: 'Published content has been updated. Open the platform to view the latest information.',
                route('public.news'),
                $content->organisation_unit_id,
                $content->target_age_categories,
                $request->user()->id,
            );
        }

        return back()->with('success', 'Content updated successfully.');
    }

    public function destroy(Request $request, Content $content)
    {
        if ($content->organisation_unit_id && ! $this->scope->canManage($request->user(), $content->organisation_unit_id)) abort(403);
        $content->delete();
        return back()->with('success', 'Content deleted.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'content';
        $slug = $base;
        $suffix = 2;
        while (Content::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }
        return $slug;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:news,announcement,devotion,bible_study,resource,opportunity,mission,talent,youth_business',
            'title' => 'required|max:200',
            'summary' => 'nullable|max:500',
            'body' => 'required',
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'target_age_categories' => 'nullable|array',
            'target_age_categories.*' => 'in:teen,youth,young_adult',
            'status' => 'required|in:draft,pending,published,archived',
            'is_official' => 'boolean',
        ]);
    }
}
