@extends('public.layout')
@section('title', 'Life Groups | COU Youth')
@section('content')
@php $slides = $slides ?? collect(); $cards = $cards ?? collect(); @endphp

@if($slides->isNotEmpty())
<section class="slides-banner" aria-label="Life Groups highlights">
@foreach($slides as $slide)
<article class="slide-card">
@if(!empty($slide->media_path))<img src="{{ asset('storage/'.$slide->media_path) }}" alt="{{ $slide->title ?? 'Life Group highlight' }}">@endif
<div class="slide-body">
@if(!empty($slide->title))<h2>{{ $slide->title }}</h2>@endif
@if(!empty($slide->subtitle))<p class="muted">{{ $slide->subtitle }}</p>@endif
@if(!empty($slide->link_url))<a class="btn btn-primary" href="{{ $slide->link_url }}">Learn more</a>@endif
</div>
</article>
@endforeach
</section>
@endif

<section class="hero">
<span class="section-kicker">BELONG & GROW</span>
<h1>Life Groups</h1>
<p>Find a youth fellowship or small group where you can build friendships, study Scripture, pray together and serve your community.</p>
</section>

<form class="filters" method="GET" action="{{ route('public.life-groups') }}">
<input name="q" value="{{ request('q') }}" placeholder="Search by group, church or meeting location" aria-label="Search Life Groups">
<button class="btn btn-primary" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Search</button>
</form>

<div class="grid">
@forelse($items as $group)
<article class="card life-group-card">
<div class="group-icon"><i class="fas fa-people-group" aria-hidden="true"></i></div>
<div class="group-heading">
<div><h3>{{ $group->name }}</h3>@if($group->organisationUnit)<p class="muted group-unit"><i class="fas fa-church" aria-hidden="true"></i> {{ $group->organisationUnit->name }}</p>@endif</div>
<span class="badge">{{ number_format($group->members_count ?? 0) }} members</span>
</div>
@if(!empty($group->description))<p class="muted">{{ \Illuminate\Support\Str::limit($group->description, 180) }}</p>@endif
<div class="group-details">
@if(!empty($group->meeting_day))<span><i class="far fa-calendar" aria-hidden="true"></i> {{ $group->meeting_day }}</span>@endif
@if(!empty($group->meeting_time))<span><i class="far fa-clock" aria-hidden="true"></i> {{ \Illuminate\Support\Str::of($group->meeting_time)->substr(0, 5) }}</span>@endif
@if(!empty($group->meeting_location))<span><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $group->meeting_location }}</span>@endif
@if(!empty($group->member_limit))<span><i class="fas fa-user-group" aria-hidden="true"></i> Capacity {{ number_format($group->member_limit) }}</span>@endif
</div>
</article>
@empty
<div class="card empty-card"><i class="fas fa-people-group" aria-hidden="true"></i><h3>No active Life Groups found</h3><p class="muted">Try a different search or check again when new groups are published.</p></div>
@endforelse
</div>

<div class="pagination">{{ $items->links() }}</div>

@if($cards->isNotEmpty())
<section class="page-cards-grid" aria-label="Life Groups resources"><div class="grid">@foreach($cards as $card)<article class="card">@if(!empty($card->image_path))<img class="page-card-img" src="{{ asset('storage/'.$card->image_path) }}" alt="{{ $card->title ?? 'Life Groups resource' }}">@endif @if(!empty($card->title))<h3>{{ $card->title }}</h3>@endif @if(!empty($card->body))<p class="muted">{{ $card->body }}</p>@endif @if(!empty($card->link_url))<a class="btn" href="{{ $card->link_url }}">Learn more</a>@endif</article>@endforeach</div></section>
@endif

<style>
.section-kicker{display:inline-block;color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em;margin-bottom:6px}.slides-banner{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:30px}.slide-card{overflow:hidden;background:#fff;border:1px solid var(--border);border-radius:10px}.slide-card img{width:100%;height:210px;object-fit:cover;display:block}.slide-body{padding:18px}.slide-body h2{margin:0 0 8px}.life-group-card{display:flex;flex-direction:column;gap:14px}.group-icon{width:48px;height:48px;border-radius:10px;display:grid;place-items:center;background:color-mix(in srgb,var(--primary) 12%,#fff);color:var(--primary);font-size:1.2rem}.group-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.group-heading h3{margin:0}.group-unit{margin:5px 0 0;font-size:.9rem}.group-details{display:grid;gap:8px;padding-top:12px;border-top:1px solid var(--border);color:var(--muted);font-size:.9rem}.group-details span{display:flex;gap:8px;align-items:center}.group-details i{width:18px;color:var(--primary)}.empty-card{text-align:center;grid-column:1/-1;padding:34px}.empty-card>i{font-size:2rem;color:var(--primary);margin-bottom:10px}.page-cards-grid{margin-top:36px}.page-card-img{width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:14px}@media(max-width:900px){.slides-banner{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.slides-banner{grid-template-columns:1fr}.group-heading{flex-direction:column}}
</style>
@endsection