@extends('public.layout')
@section('title','Courses | COU Youth')
@section('content')
<section class="hero"><h1>Discipleship Courses</h1><p>Grow in faith through structured Bible study and youth discipleship courses.</p></section>
<form class="filters" method="get"><input name="q" value="{{ request('q') }}" placeholder="Search courses"><button class="btn btn-primary"><i class="fas fa-search"></i> Search</button></form>
<div class="grid">@forelse($items as $course)<article class="card"><span class="badge">{{ ucwords(str_replace('_',' ',$course->age_category)) }}</span><h3 style="margin-top:12px">{{ $course->title }}</h3><p class="muted">{{ $course->description ?: 'Discipleship course' }}</p><strong>{{ $course->lessons_count }} lessons</strong></article>@empty<div class="card">No published courses found.</div>@endforelse</div><div class="pagination">{{ $items->links() }}</div>
@endsection
