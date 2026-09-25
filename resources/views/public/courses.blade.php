@extends('public.layout')
@section('title', 'Courses | COU Youth')
@section('content')
@php $slides = $slides ?? collect(); $cards = $cards ?? collect(); @endphp

@if($slides->isNotEmpty())
<section class="slides-banner" aria-label="Courses highlights">
@foreach($slides as $slide)
<article class="slide-card">
@if(!empty($slide->media_path))<img src="{{ asset('storage/'.$slide->media_path) }}" alt="{{ $slide->title ?? 'Highlight' }}">@endif
<div class="slide-body">
@if(!empty($slide->title))<h2>{{ $slide->title }}</h2>@endif
@if(!empty($slide->subtitle))<p class="muted">{{ $slide->subtitle }}</p>@endif
@if(!empty($slide->link_url))<a class="btn btn-primary" href="{{ $slide->link_url }}">Learn more</a>@endif
</div>
</article>
@endforeach
</section>
@endif

<section class="hero"><span class="section-kicker">GROW IN FAITH</span><h1>Discipleship Courses</h1><p>Structured Bible study and youth discipleship designed to help young people grow in faith and service.</p></section>
<form class="filters" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Search courses"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button></form>

<div class="grid">
@forelse($items as $course)
<a class="card course-card" href="{{ route('public.courses.show', $course) }}" aria-label="View {{ $course->title }} course details">
@if(!empty($course->image_path))<img class="course-card-image" src="{{ asset('storage/'.$course->image_path) }}" alt="{{ $course->title }}">@else<span class="course-icon"><i class="fas fa-book-bible"></i></span>@endif
@if(!empty($course->age_category))<span class="badge">{{ ucwords(str_replace('_',' ',$course->age_category)) }}</span>@endif
<h3>{{ $course->title }}</h3>
<p class="muted">{{ \Illuminate\Support\Str::limit($course->description ?: 'Discipleship course', 150) }}</p>
<div class="course-meta"><span><i class="fas fa-list"></i> {{ number_format($course->lessons_count ?? 0) }} {{ ($course->lessons_count ?? 0) === 1 ? 'lesson' : 'lessons' }}</span><span class="detail-link">View course <i class="fas fa-arrow-right"></i></span></div>
</a>
@empty
<div class="card empty-card"><i class="fas fa-book-open"></i><h3>No published courses found</h3><p class="muted">New discipleship resources will appear here when available.</p></div>
@endforelse
</div>
<div class="pagination">{{ $items->links() }}</div>

@if($cards->isNotEmpty())
<section class="page-cards-grid"><div class="grid">@foreach($cards as $card)<article class="card">@if(!empty($card->image_path))<img class="page-card-img" src="{{ asset('storage/'.$card->image_path) }}" alt="{{ $card->title ?? 'Highlight' }}">@endif @if(!empty($card->title))<h3>{{ $card->title }}</h3>@endif @if(!empty($card->body))<p class="muted">{{ $card->body }}</p>@endif @if(!empty($card->link_url))<a class="btn" href="{{ $card->link_url }}">Learn more</a>@endif</article>@endforeach</div></section>
@endif

<style>
.card{border-radius:10px!important}.slides-banner{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:30px}.slide-card{overflow:hidden;background:#fff;border:1px solid var(--border);border-radius:10px}.slide-card img{width:100%;height:210px;object-fit:cover;display:block}.slide-body{padding:18px}.slide-body h2{margin:0 0 8px}.section-kicker{display:inline-block;color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em;margin-bottom:6px}.course-card{position:relative;display:block;color:inherit}.course-card-image{display:block;width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:14px}.course-icon{width:48px;height:48px;display:grid;place-items:center;border-radius:10px;background:color-mix(in srgb,var(--primary) 12%,#fff);color:var(--primary);margin-bottom:14px}.course-card h3{margin:12px 0 8px}.course-meta{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:var(--muted);font-size:.9rem;margin-top:14px}.detail-link{color:var(--primary);font-weight:800}.course-card:hover .detail-link{color:var(--primary-2)}.empty-card{text-align:center;grid-column:1/-1;padding:34px}.empty-card>i{font-size:2rem;color:var(--primary);margin-bottom:10px}.page-cards-grid{margin-top:36px}.page-card-img{width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:14px}@media(max-width:900px){.slides-banner{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.slides-banner{grid-template-columns:1fr}}
</style>
@endsection