@extends('admin.layout')
@section('content')
<h1>Discipleship Courses</h1>
@if(session('success'))<div>{{ session('success') }}</div>@endif
<form method="post" action="{{ route('admin.courses.store') }}">@csrf
<input name="title" placeholder="Course title" required><textarea name="description" placeholder="Description"></textarea>
<select name="age_category"><option value="all">All</option><option value="teen">Teen</option><option value="youth">Youth</option><option value="young_adult">Young Adult</option></select>
<select name="organisation_unit_id"><option value="">All Church</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select>
<label><input type="checkbox" name="is_published" value="1"> Published</label><button>Create course</button></form>
@foreach($courses as $course)<section><h2>{{ $course->title }}</h2><p>{{ $course->description }}</p><strong>{{ $course->age_category }}</strong>
<form method="post" action="{{ route('admin.courses.lessons.store',$course) }}">@csrf<input name="title" placeholder="Lesson title" required><textarea name="body" placeholder="Lesson content"></textarea><input name="media_url" placeholder="Media URL"><label><input type="checkbox" name="is_published" value="1"> Published</label><button>Add lesson</button></form>
<ul>@foreach($course->lessons as $lesson)<li>{{ $lesson->position }}. {{ $lesson->title }} <form style="display:inline" method="post" action="{{ route('admin.courses.lessons.destroy',[$course,$lesson]) }}">@csrf @method('DELETE')<button>Delete</button></form></li>@endforeach</ul></section>@endforeach
{{ $courses->links() }}
@endsection
