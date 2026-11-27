@extends('public.layout')
@section('title', 'Events | COU Youth')
@section('content')
@php $slides = $slides ?? collect(); $cards = $cards ?? collect(); @endphp

@if($slides->isNotEmpty())
<section class="slides-banner" aria-label="Events highlights">
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

<section class="hero page-hero"><span class="section-kicker">@pageText('events.hero_kicker')</span><h1>@pageText('events.hero_title') <span class="scribble">@pageText('events.hero_title_highlight')</span></h1><p>@pageText('events.hero_intro')</p></section>

@include('partials.active-search', ['route' => 'public.events'])

<div class="grid">
@forelse($items as $event)
<a class="card event-list-card" href="{{ route('public.events.show', $event) }}" aria-label="View {{ $event->title }} details">
    @if(!empty($event->poster_path))<img class="event-poster" src="{{ asset('storage/'.$event->poster_path) }}" alt="{{ $event->title }}">@endif
    <div class="event-card-body">
        <div class="event-card-top"><span class="badge">{{ ucfirst($event->status ?? 'published') }}</span><span class="view-arrow"><i class="fas fa-arrow-right"></i></span></div>
        <h3>{{ $event->title }}</h3>
        <p><i class="fas fa-calendar"></i> {{ optional($event->starts_at)->format('d M Y, H:i') ?: 'Date to be confirmed' }}</p>
        <p class="muted"><i class="fas fa-location-dot"></i> {{ $event->venue ?: 'Venue to be confirmed' }}</p>
        @if(!empty($event->speaker))<p class="muted"><i class="fas fa-microphone"></i> {{ $event->speaker }}</p>@endif
        <span class="detail-link">View full details</span>
    </div>
</a>
@empty
<div class="card empty-card"><i class="fas fa-calendar-xmark"></i><h3>@pageText('events.empty_title')</h3><p class="muted">@pageText('events.empty_body')</p></div>
@endforelse
</div>
<div class="pagination">{{ $items->links() }}</div>

@if($cards->isNotEmpty())
<section class="page-cards-grid" aria-label="Featured"><div class="grid">
@foreach($cards as $card)
<article class="card featured-card">
@if(!empty($card->image_path))<img class="page-card-img" src="{{ asset('storage/'.$card->image_path) }}" alt="{{ $card->title ?? 'Highlight' }}">@endif
@if(!empty($card->title))<h3>{{ $card->title }}</h3>@endif
@if(!empty($card->body))<p class="muted">{{ $card->body }}</p>@endif
@if(!empty($card->link_url))<a class="btn" href="{{ $card->link_url }}">Learn more</a>@endif
</article>
@endforeach
</div></section>
@endif

<style>
.slides-banner{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:30px}.slide-card{overflow:hidden;background:var(--surface);border:1px solid var(--border);border-radius:10px}.slide-card img{display:block;width:100%;height:210px;object-fit:cover}.slide-body{padding:18px}.slide-body h2{margin:0 0 8px}.section-kicker{display:inline-block;color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em;margin-bottom:6px}.event-list-card{padding:0;overflow:hidden;color:inherit;display:block}.event-poster{width:100%;height:190px;object-fit:cover;border-radius:0}.event-card-body{padding:18px}.event-card-top{display:flex;justify-content:space-between;align-items:center}.view-arrow{width:34px;height:34px;border-radius:8px;background:#f1ecf8;color:var(--primary);display:grid;place-items:center}.event-list-card:hover .view-arrow{background:var(--primary);color:#fff}.event-list-card h3{margin:13px 0 10px}.event-list-card p{margin:7px 0}.detail-link{display:inline-block;color:var(--primary);font-weight:800;margin-top:8px}.empty-card{text-align:center;grid-column:1/-1;padding:34px}.empty-card>i{font-size:2rem;color:var(--primary);margin-bottom:10px}.page-cards-grid{margin-top:36px}.page-card-img{width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:14px}
@media(max-width:900px){.slides-banner{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.slides-banner{grid-template-columns:1fr}}
</style>
@endsection