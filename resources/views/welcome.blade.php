@extends('public.layout')
@section('title','Church of Uganda Youth Platform')
@section('content')
<section class="hero" style="display:grid;grid-template-columns:1.4fr 1fr;gap:28px;align-items:center">
    <div>
        <span class="badge"><i class="fas fa-cross"></i>&nbsp; Church of Uganda Youth Ministry</span>
        <h1 style="margin-top:16px">Connecting young people to faith, community and opportunity.</h1>
        <p>The COU Youth Platform brings discipleship, events, church discovery, opportunities, safeguarding support and giving into one connected digital experience.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px">
            @guest<a class="btn btn-primary" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Create Youth Account</a><a class="btn" href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> Youth Login</a>@else<a class="btn btn-primary" href="{{ route('public.courses') }}"><i class="fas fa-book-bible"></i> Explore Courses</a>@endguest
        </div>
    </div>
    <div class="card" style="background:#4b2e83;color:#fff;border:0;padding:26px">
        <h3>Explore the platform</h3>
        <p style="opacity:.85">The website now uses dedicated pages instead of placing every service on one long page.</p>
        <div style="display:grid;gap:10px;margin-top:18px">
            <a class="btn" href="{{ route('public.news') }}"><i class="fas fa-newspaper"></i> News & Resources</a>
            <a class="btn" href="{{ route('public.events') }}"><i class="fas fa-calendar-days"></i> Events</a>
            <a class="btn" href="{{ route('public.churches') }}"><i class="fas fa-location-dot"></i> Church Locator</a>
        </div>
    </div>
</section>

<section style="margin-top:24px"><h2>Platform at a glance</h2><div class="grid">
    <a class="card" href="{{ route('public.events') }}"><h3><i class="fas fa-calendar-days"></i> {{ number_format($stats['events'] ?? 0) }} Events</h3><p class="muted">Youth worship, fellowship, missions and programmes.</p></a>
    <a class="card" href="{{ route('public.courses') }}"><h3><i class="fas fa-book-bible"></i> {{ number_format($stats['courses'] ?? 0) }} Courses</h3><p class="muted">Structured discipleship and Bible study content.</p></a>
    <a class="card" href="{{ route('public.churches') }}"><h3><i class="fas fa-church"></i> {{ number_format($stats['church_locations'] ?? 0) }} Church Locations</h3><p class="muted">Find congregations and youth fellowship information.</p></a>
</div></section>

@if($latestNews->isNotEmpty())
<section style="margin-top:36px"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px"><div><h2 style="margin-bottom:4px">Latest Updates</h2><p class="muted">Recent news, opportunities and devotionals.</p></div><a class="btn" href="{{ route('public.news') }}">View all</a></div><div class="grid">@foreach($latestNews->take(3) as $item)<article class="card"><span class="badge">{{ ucwords(str_replace('_',' ',$item->type)) }}</span><h3 style="margin-top:12px">{{ $item->title }}</h3><p class="muted">{{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->body),130) }}</p></article>@endforeach</div></section>
@endif

@if($upcomingEvents->isNotEmpty())
<section style="margin-top:36px"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px"><div><h2 style="margin-bottom:4px">Upcoming Events</h2><p class="muted">Join upcoming youth programmes.</p></div><a class="btn" href="{{ route('public.events') }}">View all</a></div><div class="grid">@foreach($upcomingEvents->take(3) as $event)<article class="card"><h3>{{ $event->title }}</h3><p><i class="fas fa-calendar"></i> {{ optional($event->starts_at)->format('d M Y H:i') ?: 'Date to be confirmed' }}</p><p class="muted"><i class="fas fa-location-dot"></i> {{ $event->venue ?: 'Venue to be confirmed' }}</p></article>@endforeach</div></section>
@endif

<section style="margin-top:36px"><div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap"><div><h2 style="margin:0 0 8px">Ready to connect?</h2><p class="muted" style="margin:0">Create a youth account or use the mobile app to access personalised participation features.</p></div>@guest<a class="btn btn-primary" href="{{ route('register') }}">Sign Up</a>@else<a class="btn btn-primary" href="{{ route('public.courses') }}">Continue Exploring</a>@endguest</div></section>

<style>@media(max-width:800px){.hero{grid-template-columns:1fr!important}}</style>
@endsection
