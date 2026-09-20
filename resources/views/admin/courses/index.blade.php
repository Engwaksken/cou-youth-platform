@extends('admin.layout')
@section('title','Courses')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-book-bible"></i> Discipleship Courses</h1><p>Manage courses, lessons and publishing status.</p></div></div>

<div class="grid stats-grid course-stats">
    @foreach([
        ['Total courses',$stats['total'],'fa-book','#4b2e83'],
        ['Published',$stats['published'],'fa-circle-check','#15803d'],
        ['Draft',$stats['draft'],'fa-file-pen','#b45309'],
        ['Lessons',$stats['lessons'],'fa-list-check','#2563eb'],
    ] as [$label,$value,$icon,$tone])
        <div class="card stat-card course-stat" style="--tone:{{ $tone }}"><div class="stat-icon"><i class="fas {{ $icon }}"></i></div><div><strong>{{ number_format((int)$value) }}</strong><span>{{ $label }}</span></div></div>
    @endforeach
</div>

<div class="card">
    <form method="get" class="filters">
        <input name="q" value="{{ $filters['q'] }}" placeholder="Search courses, description or age category">
        <select name="status"><option value="">All statuses</option><option value="published" @selected($filters['status']==='published')>Published</option><option value="draft" @selected($filters['status']==='draft')>Draft</option></select>
        <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        <a class="btn btn-light" href="{{ route('admin.courses.index') }}">Reset</a>
    </form>
</div>

<div class="card" style="margin-top:16px">
    <h2 style="margin-top:0">Add Course</h2>
    <form method="post" action="{{ route('admin.courses.store') }}" class="form-grid">@csrf
        <label>Course title<input name="title" required></label>
        <label>Age category<select name="age_category"><option value="all">All</option><option value="teen">Teen</option><option value="youth">Youth</option><option value="young_adult">Young Adult</option></select></label>
        <label class="span-2">Description<textarea name="description" rows="3"></textarea></label>
        <label>Church unit<select name="organisation_unit_id"><option value="">All Church</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></label>
        <label class="checkbox"><input type="checkbox" name="is_published" value="1"> Published</label>
        <div class="span-2"><button class="btn btn-primary"><i class="fas fa-plus"></i> Create Course</button></div>
    </form>
</div>

<div class="course-grid">
@forelse($courses as $course)
    <section class="card course-card">
        <div class="course-card-head"><div><h2>{{ $course->title }}</h2><span class="badge {{ $course->is_published ? 'badge-success' : 'badge-warning' }}">{{ $course->is_published ? 'Published' : 'Draft' }}</span></div><strong>{{ $course->lessons_count }} lessons</strong></div>
        <p>{{ $course->description ?: 'No description provided.' }}</p>
        <div class="course-meta"><span><i class="fas fa-users"></i> {{ ucwords(str_replace('_',' ',$course->age_category)) }}</span></div>
        <details><summary>Add lesson</summary><form method="post" action="{{ route('admin.courses.lessons.store',$course) }}" class="lesson-form">@csrf<input name="title" placeholder="Lesson title" required><textarea name="body" placeholder="Lesson content"></textarea><input name="media_url" placeholder="Media URL"><label><input type="checkbox" name="is_published" value="1"> Published</label><button class="btn btn-primary">Add lesson</button></form></details>
        <ul class="lesson-list">@forelse($course->lessons as $lesson)<li><span>{{ $lesson->position }}. {{ $lesson->title }}</span><form method="post" action="{{ route('admin.courses.lessons.destroy',[$course,$lesson]) }}">@csrf @method('DELETE')<button class="icon-btn" title="Delete"><i class="fas fa-trash"></i></button></form></li>@empty<li class="empty">No lessons yet.</li>@endforelse</ul>
    </section>
@empty
    <div class="card empty">No courses match your filters.</div>
@endforelse
</div>
<div class="pagination">{{ $courses->links() }}</div>

<style>
.course-stats{grid-template-columns:repeat(4,minmax(0,1fr))}.course-stat{border-left:5px solid var(--tone)}.course-stat .stat-icon{color:var(--tone);background:#f6f4fb}.course-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:16px}.course-card-head{display:flex;justify-content:space-between;gap:12px}.course-card-head h2{margin:0 0 8px;font-size:18px}.course-card>p{color:var(--muted);line-height:1.5}.course-meta{font-size:13px;color:var(--muted);margin:12px 0}.lesson-form{display:grid;gap:9px;margin-top:12px}.lesson-form input,.lesson-form textarea{width:100%;padding:9px;border:1px solid var(--border);border-radius:8px}.lesson-list{list-style:none;padding:0;margin:14px 0 0}.lesson-list li{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:9px 0;border-top:1px solid var(--border)}.lesson-list form{margin:0}@media(max-width:1100px){.course-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.course-stats,.course-grid{grid-template-columns:1fr}}
</style>
@endsection
