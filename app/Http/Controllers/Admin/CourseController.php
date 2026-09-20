<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\OrganisationUnit;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CourseController extends Controller
{
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
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'age_category' => 'required|in:teen,youth,young_adult,all',
            'is_published' => 'sometimes|boolean',
        ]);
        Course::create([...$data, 'is_published' => $request->boolean('is_published')]);
        return back()->with('success', 'Course created.');
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'organisation_unit_id' => 'nullable|exists:organisation_units,id',
            'age_category' => 'required|in:teen,youth,young_adult,all',
            'is_published' => 'sometimes|boolean',
        ]);
        $course->update([...$data, 'is_published' => $request->boolean('is_published')]);
        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
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
        $course->lessons()->create([
            ...$data,
            'position' => $data['position'] ?? ($course->lessons()->max('position') + 1),
            'is_published' => $request->boolean('is_published'),
        ]);
        return back()->with('success', 'Lesson added.');
    }

    public function destroyLesson(Course $course, Lesson $lesson)
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $lesson->delete();
        return back()->with('success', 'Lesson deleted.');
    }
}
