@extends('public.layout')

@section('title', 'Church of Uganda Youth Platform')

@section('content')
@php
    $slides = $slides ?? collect();
    $cards = $cards ?? collect();
@endphp

@if($slides->isNotEmpty())
<section class="slides-banner" aria-label="Featured highlights">
    @foreach($slides as $slide)
        <article class="slide-card">
            @if(!empty($slide->media_path))
                <img src="{{ asset('storage/' . $slide->media_path) }}" alt="{{ $slide->title ?? 'Highlight' }}">
            @endif
            <div class="slide-body">
                @if(!empty($slide->title))<h2>{{ $slide->title }}</h2>@endif
                @if(!empty($slide->subtitle))<p class="muted">{{ $slide->subtitle }}</p>@endif
                @if(!empty($slide->link_url))
                    <a class="btn btn-primary" href="{{ $slide->link_url }}">Learn more</a>
                @endif
            </div>
        </article>
    @endforeach
</section>
@endif

<section class="hero home-hero">
    <div class="hero-copy">
        <span class="badge"><i class="fas fa-cross"></i> Church of Uganda Youth Ministry</span>
        <h1>Faith. Community. Opportunity.</h1>
        <p>A welcoming digital home for young people to grow in faith, join events, discover opportunities, find churches and connect with youth ministry.</p>
        <div class="hero-actions">
            @guest
                <a class="btn btn-primary" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Create Youth Account</a>
                <a class="btn" href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> Youth Login</a>
            @else
                <a class="btn btn-primary" href="{{ route('public.courses') }}"><i class="fas fa-book-bible"></i> Explore Courses</a>
            @endguest
        </div>
    </div>

    <aside class="card explore-card" aria-label="Explore the platform">
        <span class="section-kicker">START HERE</span>
        <h2>Explore COU Youth</h2>
        <p>Choose what matters to you today.</p>
        <div class="explore-links">
            <a href="{{ route('public.news') }}"><span><i class="fas fa-newspaper"></i> News & Resources</span><i class="fas fa-arrow-right"></i></a>
            <a href="{{ route('public.events') }}"><span><i class="fas fa-calendar-days"></i> Events</span><i class="fas fa-arrow-right"></i></a>
            <a href="{{ route('public.courses') }}"><span><i class="fas fa-book-bible"></i> Courses</span><i class="fas fa-arrow-right"></i></a>
            <a href="{{ route('public.churches') }}"><span><i class="fas fa-location-dot"></i> Church Locator</span><i class="fas fa-arrow-right"></i></a>
            <a href="{{ route('public.donate') }}"><span><i class="fas fa-hand-holding-heart"></i> Donate</span><i class="fas fa-arrow-right"></i></a>
        </div>
    </aside>
</section>

@if($annualTheme)
<section class="annual-theme" aria-labelledby="annual-theme-title">
    @if(!empty($annualTheme->image_path))
        <div class="annual-theme-media">
            <img src="{{ asset('storage/' . $annualTheme->image_path) }}" alt="{{ $annualTheme->theme }}">
        </div>
    @endif
    <div class="annual-theme-copy">
        <span class="section-kicker">{{ $annualTheme->year }} ANNUAL THEME</span>
        <h2 id="annual-theme-title">{{ $annualTheme->theme }}</h2>
        @if(!empty($annualTheme->scripture_reference))
            <p class="theme-scripture"><i class="fas fa-book-bible"></i> {{ $annualTheme->scripture_reference }}</p>
        @endif
        @if(!empty($annualTheme->description))
            <p>{{ $annualTheme->description }}</p>
        @endif
    </div>
</section>
@endif

<section class="home-section">
    <div class="section-head">
        <div>
            <span class="section-kicker">DISCOVER</span>
            <h2>COU Youth at a glance</h2>
            <p class="muted">See what is happening across the Church of Uganda youth ministry.</p>
        </div>
    </div>
    <div class="grid">
        <a class="card glance-card" href="{{ route('public.events') }}">
            <span class="glance-icon"><i class="fas fa-calendar-days"></i></span>
            <div><h3>{{ number_format($stats['events'] ?? 0) }} Events</h3><p class="muted">Worship, fellowship, missions and youth programmes.</p></div>
        </a>
        <a class="card glance-card" href="{{ route('public.courses') }}">
            <span class="glance-icon"><i class="fas fa-book-bible"></i></span>
            <div><h3>{{ number_format($stats['courses'] ?? 0) }} Courses</h3><p class="muted">Bible study and discipleship resources for young people.</p></div>
        </a>
        <a class="card glance-card" href="{{ route('public.churches') }}">
            <span class="glance-icon"><i class="fas fa-church"></i></span>
            <div><h3>{{ number_format($stats['church_locations'] ?? 0) }} Church Locations</h3><p class="muted">Find congregations and youth fellowship information.</p></div>
        </a>
    </div>
</section>

@if($latestNews->isNotEmpty())
<section class="home-section">
    <div class="section-head">
        <div><span class="section-kicker">LATEST</span><h2>News & opportunities</h2><p class="muted">Recent updates, opportunities and devotionals.</p></div>
        <a class="btn" href="{{ route('public.news') }}">View all</a>
    </div>
    <div class="grid">
        @foreach($latestNews->take(3) as $item)
            <article class="card update-card">
                <span class="badge">{{ ucwords(str_replace('_', ' ', $item->type ?? 'update')) }}</span>
                <h3>{{ $item->title }}</h3>
                <p class="muted">{{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->body ?? ''), 130) }}</p>
            </article>
        @endforeach
    </div>
</section>
@endif

@if($upcomingEvents->isNotEmpty())
<section class="home-section">
    <div class="section-head">
        <div><span class="section-kicker">COMING UP</span><h2>Upcoming Events</h2><p class="muted">Join the next youth programmes and gatherings.</p></div>
        <a class="btn" href="{{ route('public.events') }}">View all</a>
    </div>
    <div class="grid">
        @foreach($upcomingEvents->take(3) as $event)
            <a class="card event-card clickable-card" href="{{ route('public.events.show', $event) }}">
                <span class="event-icon"><i class="fas fa-calendar-days"></i></span>
                <h3>{{ $event->title }}</h3>
                <p><i class="fas fa-calendar"></i> {{ optional($event->starts_at)->format('d M Y H:i') ?: 'Date to be confirmed' }}</p>
                <p class="muted"><i class="fas fa-location-dot"></i> {{ $event->venue ?: 'Venue to be confirmed' }}</p>
                <span class="detail-link">View event details <i class="fas fa-arrow-right"></i></span>
            </a>
        @endforeach
    </div>
</section>
@endif

@if($cards->isNotEmpty())
<section class="home-section page-cards-grid" aria-label="Highlights">
    <div class="section-head"><div><span class="section-kicker">FEATURED</span><h2>Highlights</h2><p class="muted">Featured ministry content and opportunities.</p></div></div>
    <div class="grid">
        @foreach($cards as $card)
            <article class="card highlight-card">
                @if(!empty($card->image_path))
                    <img class="page-card-img" src="{{ asset('storage/' . $card->image_path) }}" alt="{{ $card->title ?? 'Highlight' }}">
                @elseif(!empty($card->icon))
                    <span class="highlight-icon"><i class="fas {{ $card->icon }}"></i></span>
                @endif
                @if(!empty($card->title))<h3>{{ $card->title }}</h3>@endif
                @if(!empty($card->body))<p class="muted">{{ $card->body }}</p>@endif
                @if(!empty($card->link_url))<a class="btn" href="{{ $card->link_url }}">Learn more</a>@endif
            </article>
        @endforeach
    </div>
</section>
@endif

<section class="home-section">
    <div class="card connect-card">
        <div><h2>Ready to connect?</h2><p class="muted">Create a youth account to access personalised ministry and participation features.</p></div>
        @guest
            <a class="btn btn-primary" href="{{ route('register') }}">Sign Up</a>
        @else
            <a class="btn btn-primary" href="{{ route('public.courses') }}">Continue Exploring</a>
        @endguest
    </div>
</section>

<style>
.slides-banner{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:28px}.slide-card{overflow:hidden;border-radius:10px;background:#fff;border:1px solid var(--border);box-shadow:var(--shadow-sm)}.slide-card img{width:100%;height:220px;object-fit:cover;display:block}.slide-body{padding:18px}.slide-body h2{margin:0 0 6px}.slide-body p{margin:0 0 12px}
.home-hero{display:grid;grid-template-columns:1.35fr .9fr;gap:28px;align-items:center}.hero-copy h1{margin:14px 0 12px}.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}.explore-card{background:var(--primary);color:#fff;border:0;padding:24px;border-radius:10px}.explore-card h2,.explore-card p{color:#fff}.explore-links{display:grid;gap:8px;margin-top:16px}.explore-links a{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 12px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:8px;color:#fff;font-weight:800}.explore-links a:hover{background:#fff;color:var(--primary)}
.section-kicker{display:inline-block;color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em;margin-bottom:6px}.annual-theme{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.4fr);gap:0;margin:10px 0 38px;background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:var(--shadow-sm)}.annual-theme-media img{width:100%;height:100%;min-height:250px;object-fit:cover;display:block}.annual-theme-copy{padding:30px;align-self:center}.annual-theme-copy h2{font-size:clamp(1.7rem,4vw,2.5rem);margin:0 0 10px;color:#1e1830}.annual-theme-copy p{color:var(--muted)}.theme-scripture{font-weight:800;color:var(--primary)!important}
.home-section{margin-top:38px}.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:16px}.section-head h2{margin:0 0 4px}.section-head p{margin:0}.glance-card{display:flex;gap:14px;align-items:flex-start}.glance-icon,.event-icon,.highlight-icon{width:46px;height:46px;min-width:46px;border-radius:10px;display:grid;place-items:center;background:color-mix(in srgb,var(--primary) 12%,#fff 88%);color:var(--primary);font-size:1.1rem}.event-card .event-icon,.highlight-icon{margin-bottom:14px}.clickable-card{display:block;color:inherit}.detail-link{display:inline-flex;align-items:center;gap:8px;margin-top:10px;color:var(--primary);font-weight:800}.clickable-card:hover .detail-link{color:var(--primary-2)}.page-card-img{width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:14px}.connect-card{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}.connect-card h2{margin:0 0 8px}.connect-card p{margin:0}
.card{border-radius:10px!important}
@media(max-width:900px){.slides-banner{grid-template-columns:repeat(2,minmax(0,1fr))}.annual-theme{grid-template-columns:1fr}.annual-theme-media img{max-height:300px}.home-hero{grid-template-columns:1fr}}@media(max-width:640px){.slides-banner{grid-template-columns:1fr}.section-head{align-items:flex-start;flex-direction:column}.connect-card{align-items:stretch}.connect-card .btn{width:100%}.annual-theme-copy{padding:22px}}
</style>
@endsection