<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaAssetController extends Controller
{
    public function __construct(private PlatformUpdateNotificationService $updates) {}

    public function index(Request $request)
    {
        $query = MediaAsset::query();
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }
        if (in_array($type, ['image', 'audio', 'video', 'document'], true)) $query->where('type', $type);
        if ($status === 'published') $query->where('is_published', true);
        elseif ($status === 'draft') $query->where('is_published', false);

        return view('admin.media.index', [
            'items' => $query->latest()->paginate(12)->withQueryString(),
            'stats' => [
                'total' => MediaAsset::count(),
                'published' => MediaAsset::where('is_published', true)->count(),
                'images' => MediaAsset::where('type', 'image')->count(),
                'documents' => MediaAsset::where('type', 'document')->count(),
            ],
            'typeCounts' => MediaAsset::query()->selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type'),
            'filters' => ['q' => $search, 'type' => $type, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = MediaAsset::create([
            'title' => $data['title'],
            'type' => $data['type'],
            'alt_text' => $data['alt_text'] ?? null,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($media->is_published) {
            $this->updates->notifyYouth(
                'New media: '.$media->title,
                'New '.$media->type.' content is available on the Church of Uganda Youth Platform.',
                route('home'),
                null,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Media uploaded.');
    }

    public function update(Request $request, MediaAsset $media)
    {
        $data = $this->validated($request, false);
        $attributes = [
            'title' => $data['title'],
            'type' => $data['type'],
            'alt_text' => $data['alt_text'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('media', 'public');
            if ($media->file_path) Storage::disk('public')->delete($media->file_path);
            $attributes += [
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ];
        }

        $media->update($attributes);

        if ($media->is_published) {
            $this->updates->notifyYouth(
                'Media updated: '.$media->title,
                'Published media content has been updated. Open the app to view the latest version.',
                route('home'),
                null,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Media updated.');
    }

    public function destroy(Request $request, MediaAsset $media)
    {
        $title = $media->title;
        $wasPublished = (bool) $media->is_published;
        if ($media->file_path) Storage::disk('public')->delete($media->file_path);
        $media->delete();

        if ($wasPublished) {
            $this->updates->notifyYouth(
                'Media removed: '.$title,
                'This media item is no longer available on the Church of Uganda Youth Platform.',
                null,
                null,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Media deleted.');
    }

    private function validated(Request $request, bool $fileRequired): array
    {
        return $request->validate([
            'title' => 'required|string|max:190',
            'type' => 'required|in:image,audio,video,document',
            'alt_text' => 'nullable|string|max:255',
            'file' => ($fileRequired ? 'required' : 'nullable').'|file|max:51200',
            'is_published' => 'sometimes|boolean',
        ]);
    }
}
