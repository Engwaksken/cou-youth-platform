<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaAsset::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, ['image', 'audio', 'video', 'document'], true)) {
            $query->where('type', $type);
        }

        $status = (string) $request->query('status', '');
        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }

        $stats = [
            'total' => MediaAsset::count(),
            'published' => MediaAsset::where('is_published', true)->count(),
            'images' => MediaAsset::where('type', 'image')->count(),
            'documents' => MediaAsset::where('type', 'document')->count(),
        ];

        $typeCounts = MediaAsset::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('admin.media.index', [
            'items' => $query->latest()->paginate(12)->withQueryString(),
            'stats' => $stats,
            'typeCounts' => $typeCounts,
            'filters' => [
                'q' => $search,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'type' => 'required|in:image,audio,video,document',
            'alt_text' => 'nullable|string|max:255',
            'file' => 'required|file|max:51200',
            'is_published' => 'sometimes|boolean',
        ]);

        $path = $request->file('file')->store('media', 'public');

        MediaAsset::create([
            'title' => $data['title'],
            'type' => $data['type'],
            'alt_text' => $data['alt_text'] ?? null,
            'file_path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Media uploaded.');
    }

    public function destroy(MediaAsset $media)
    {
        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Media deleted.');
    }
}
