@extends('public.layout')
@section('title','Courses | COU Youth')
@section('content')
<section class="hero"><h1>Discipleship Courses</h1><p>Grow in faith through structured Bible study and youth discipleship courses.</p></section>
<form class="filters" method="get"><input name="q" value="{{ request('q') }}" placeholder="Search courses"><button class="btn btn-primary"><i class="fas fa-search"></i> Search</button></form>
<div class="grid">@forelse($items as $course)<article class="card course-card">
@if($course->visual_type==='image' && $course->image_path)
<img class="course-card-image" src="{{ asset('storage/'.$course->image_path) }}" alt="{{ $course->title }}">
@else
<div class="course-card-icon" aria-hidden="true"><i class="fas {{ $course->icon_class ?: 'fa-book-bible' }}"></i></div>
@endif
<div class="course-card-body"><span class="badge">{{ ucwords(str_replace('_',' ',$course->age_category)) }}</span><h3>{{ $course->title }}</h3><p class="muted">{{ $course->description ?: 'Discipleship course' }}</p><strong>{{ $course->lessons_count }} lessons</strong></div>
</article>@empty<div class="card">No published courses found.</div>@endforelse</div><div class="pagination">{{ $items->links() }}</div>
<style>.course-card{padding:0;overflow:hidden}.course-card-image{display:block;width:100%;height:190px;object-fit:cover}.course-card-icon{height:150px;display:grid;place-items:center;background:linear-gradient(135deg,#f0ebff,#e7f0f8);color:var(--primary);font-size:3rem}.course-card-body{padding:18px}.course-card-body h3{margin:12px 0 8px}</style>
@endsection
