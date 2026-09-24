<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\OrganisationUnit;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CourseController extends Controller
{
    public function __construct(private PlatformUpdateNotificationService $updates) {}

    public function index(Request $request): View
    {
        $query = Course::with('lessons')->withCount('lessons');
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('age_category', 'like', "%{$search}%");
            });
        }

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }

        return view('admin.courses.index', [
            'courses' => $query->latest()->paginate(12)->withQueryString(),
            'units' => OrganisationUnit::orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => Course::count(),
                'published' => Course::where('is_published', true)->count(),
                'draft' => Course::where('is_published', false)->count(),
                'lessons' => Lesson::count(),
            ],
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (($data['visual_type'] ?? 'icon') === 'image' && ! $request->hasFile('image')) {
            throw ValidationException::withMessages(['image' => ['Upload a card image when Image is selected.']]);
        }
        $data = $this->storeVisualFiles($request, $data);
        $course = Course::create([...$data, 'is_published' => $request->boolean('is_published')]);

        if ($course->is_published) {
            $this->updates->notifyYouth(
                'New course: '.$course->title,
                $course->description ?: 'A new learning course is available on the Church of Uganda Youth Platform.',
                route('public.courses.show', $course),
                $course->organisation_unit_id,
                $course->age_category,
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Course created.');
    }

    public function update(Request $request, Course $course)
    {
        $data = $this->validated($request);
        if (($data['visual_type'] ?? 'icon') === 'image' && ! $request->hasFile('image') && ! $course->image_path) {
            throw ValidationException::withMessages(['image' => ['Upload a card image when Image is selected.']]);
        }
        $wasPublished = (bool) $course->is_published;
        $data = $this->storeVisualFiles($request, $data, $course);
        $course->update([...$data, 'is_published' => $request->boolean('is_published')]);

        if ($course->is_published) {
            $this->updates->notifyYouth(
                $wasPublished ? 'Course updated: '.$course->title : 'New course: '.$course->title,
                $course->description ?: 'Course information has been updated on the Church of Uganda Youth Platform.',
                route('public.courses.show', $course),
                $course->organisation_unit_id,
                $course->age_category,
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Course updated.');
    }

    public function destroy(Request $request, Course $course)
    {
        $title = $course->title;
        $wasPublished = (bool) $course->is_published;
        $unitId = $course->organisation_unit_id;
        $ageCategory = $course->age_category;

        foreach ([$course->image_path, $course->banner_path] as $path) {
            if ($path) Storage::disk('public')->delete($path);
        }
        $course->delete();

        if ($wasPublished) {
            $this->updates->notifyYouth(
                'Course removed: '.$title,
                'This course is no longer available on the Church of Uganda Youth Platform.',
                null,
                $unitId,
                $ageCategory,
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Course deleted.');
    }

    public function storeLesson(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'body' => 'nullable|string',
            'media_url' => 'nullable|url|max:2048',
            'position' => 'nullable|integer|min:1',
            'is_published' => 'sometimes|boolean',
        ]);
        $lesson = $course->lessons()->create([
            ...$data,
            'position' => $data['position'] ?? ($course->lessons()->max('position') + 1),
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($course->is_published && $lesson->is_published) {
            $this->updates->notifyYouth(
                'New lesson in '.$course->title,
                'A new lesson, “'.$lesson->title.'”, is now available.',
                route('public.courses.show', $course),
                $course->organisation_unit_id,
                $course->age_category,
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Lesson added.');
    }

    public function destroyLesson(Course $course, Lesson $lesson)
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $lesson->delete();
        return back()->with('success', 'Lesson deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'age_category' => 'required|in:teen,youth,young_adult,all',
            'is_published' => 'sometimes|boolean',
            'visual_type' => 'required|in:icon,image',
            'icon_class' => 'nullable|string|max:100|required_if:visual_type,icon',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
            'remove_image' => 'sometimes|boolean',
            'remove_banner' => 'sometimes|boolean',
        ]);
    }

    private function storeVisualFiles(Request $request, array $data, ?Course $course = null): array
    {
        unset($data['image'], $data['banner'], $data['remove_image'], $data['remove_banner']);

        if ($request->boolean('remove_image') && $course?->image_path) {
            Storage::disk('public')->delete($course->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($course?->image_path) Storage::disk('public')->delete($course->image_path);
            $data['image_path'] = $request->file('image')->store('courses/cards', 'public');
        }

        if ($request->boolean('remove_banner') && $course?->banner_path) {
            Storage::disk('public')->delete($course->banner_path);
            $data['banner_path'] = null;
        }

        if ($request->hasFile('banner')) {
            if ($course?->banner_path) Storage::disk('public')->delete($course->banner_path);
            $data['banner_path'] = $request->file('banner')->store('courses/banners', 'public');
        }

        return $data;
    }
}
