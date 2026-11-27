@extends('public.layout')

@section('title', 'Church of Uganda Youth Platform')

@section('content')
@php
    $slides = $slides ?? collect();
    $cards = $cards ?? collect();
    $verse = app(\App\Services\Engagement\DailyEngagementService::class)->verseOfTheDay();
    $nextEvent = $upcomingEvents->first();
@endphp

<section class="home-hero">
    <div class="hero-copy">
        <span class="hello">Church of Uganda Youth</span>
        <h1>Faith that fits <span class="scribble">your life.</span></h1>
        <p>Daily Scripture on your phone. Courses you can finish between classes. Events worth showing up for, and a life group where people actually know your name.</p>
        <div class="hero-actions">
            @guest
                <a class="btn btn-sun btn-lg" href="{{ route('register') }}">Create Youth Account <i class="fas fa-arrow-right"></i></a>
                <a class="btn btn-ghost btn-lg" href="{{ route('login') }}">I already have one</a>
            @else
                <a class="btn btn-sun btn-lg" href="{{ route('youth.dashboard') }}">Go to my space <i class="fas fa-arrow-right"></i></a>
                <a class="btn btn-ghost btn-lg" href="{{ route('public.courses') }}">Browse courses</a>
            @endguest
        </div>
        <p class="proof">
            <span><b>{{ number_format($stats['events'] ?? 0) }}</b> events</span>
            <span><b>{{ number_format($stats['courses'] ?? 0) }}</b> courses</span>
            <span><b>{{ number_format($stats['church_locations'] ?? 0) }}</b> churches</span>
        </p>
    </div>

    <div class="collage" aria-hidden="true">
        @if($verse)
            <div class="sticker s-verse">
                <span class="tag">Today's word</span>
                <p>{{ \Illuminate\Support\Str::limit($verse['text'], 120) }}</p>
                <cite>{{ $verse['ref'] }}</cite>
            </div>
        @endif
        @if($nextEvent)
            <div class="sticker s-event">
                <span class="date-tile"><b>{{ optional($nextEvent->starts_at)->format('j') }}</b><small>{{ optional($nextEvent->starts_at)->format('M') }}</small></span>
                <span><small>Next up</small><strong>{{ \Illuminate\Support\Str::limit($nextEvent->title, 34) }}</strong></span>
            </div>
        @else
            <div class="sticker s-event">
                <span class="date-tile"><b><i class="fas fa-people-group"></i></b></span>
                <span><small>This week</small><strong>Life groups near you</strong></span>
            </div>
        @endif
        <div class="sticker s-streak"><i class="fas fa-fire"></i> 7-day streak</div>
        <div class="sticker s-badge"><i class="fas fa-award"></i></div>
    </div>
</section>

@if($slides->isNotEmpty())
<section class="slides-banner" aria-label="Featured highlights">
    @foreach($slides as $slide)
        <article class="slide-card card">
            @if(!empty($slide->media_path))
                <img src="{{ asset('storage/' . $slide->media_path) }}" alt="{{ $slide->title ?? 'Highlight' }}">
            @endif
            <div class="slide-body">
                @if(!empty($slide->title))<h2>{{ $slide->title }}</h2>@endif
                @if(!empty($slide->subtitle))<p class="muted">{{ $slide->subtitle }}</p>@endif
                @if(!empty($slide->link_url))
                    <a class="btn" href="{{ $slide->link_url }}">Find out more <i class="fas fa-arrow-right"></i></a>
                @endif
            </div>
        </article>
    @endforeach
</section>
@endif

<section class="home-section">
    <div class="section-head"><h2>Start wherever you are</h2></div>
    <div class="paths">
        <a class="path p-sun" href="{{ route('public.events') }}">
            <span class="path-icon"><i class="fas fa-ticket"></i></span>
            <h3>Show up</h3>
            <p>Worship nights, camps, conferences and missions. Come along, and bring a friend.</p>
            <span class="path-go">See events <i class="fas fa-arrow-right"></i></span>
        </a>
        <a class="path p-leaf" href="{{ route('public.courses') }}">
            <span class="path-icon"><i class="fas fa-book-open"></i></span>
            <h3>Go deeper</h3>
            <p>Short Bible studies and life-skills courses. Do a lesson on the taxi home.</p>
            <span class="path-go">Browse courses <i class="fas fa-arrow-right"></i></span>
        </a>
        <a class="path p-coral" href="{{ route('public.churches') }}">
            <span class="path-icon"><i class="fas fa-location-dot"></i></span>
            <h3>Find your church</h3>
            <p>See which churches near you have a youth fellowship, and when they meet.</p>
            <span class="path-go">Search the map <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
</section>

@if($annualTheme)
<section class="annual-theme" aria-labelledby="annual-theme-title">
    @if(!empty($annualTheme->image_path))
        <div class="annual-theme-media"><img src="{{ asset('storage/' . $annualTheme->image_path) }}" alt=""></div>
    @endif
    <div class="annual-theme-copy">
        <span class="theme-year">Our {{ $annualTheme->year }} theme</span>
        <h2 id="annual-theme-title">{{ $annualTheme->theme }}</h2>
        @if(!empty($annualTheme->scripture_reference))<p class="theme-scripture"><i class="fas fa-book-bible"></i> {{ $annualTheme->scripture_reference }}</p>@endif
        @if(!empty($annualTheme->description))<p class="theme-desc">{{ $annualTheme->description }}</p>@endif
    </div>
</section>
@endif

@if($upcomingEvents->isNotEmpty())
<section class="home-section">
    <div class="section-head"><div><h2>Coming up</h2><p class="muted">Save the date.</p></div><a class="btn" href="{{ route('public.events') }}">All events</a></div>
    <div class="event-list">
        @foreach($upcomingEvents->take(4) as $event)
            <a class="card event-row" href="{{ route('public.events.show', $event) }}">
                <span class="date-tile"><b>{{ optional($event->starts_at)->format('j') }}</b><small>{{ optional($event->starts_at)->format('M') }}</small></span>
                <span class="event-copy">
                    <strong>{{ $event->title }}</strong>
                    <small>{{ optional($event->starts_at)->format('l, g:i A') ?: 'Date to be confirmed' }}, {{ $event->venue ?: 'Venue to be confirmed' }}</small>
                </span>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        @endforeach
    </div>
</section>
@endif

@if($latestNews->isNotEmpty())
<section class="home-section">
    <div class="section-head"><div><h2>Stories &amp; devotionals</h2><p class="muted">From young people and leaders around the church.</p></div><a class="btn" href="{{ route('public.news') }}">Read more</a></div>
    <div class="grid">
        @foreach($latestNews->take(3) as $item)
            <article class="card story-card">
                <span class="badge badge-sun">{{ ucwords(str_replace('_', ' ', $item->type ?? 'story')) }}</span>
                <h3>{{ $item->title }}</h3>
                <p class="muted">{{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->body ?? ''), 130) }}</p>
            </article>
        @endforeach
    </div>
</section>
@endif

@if($cards->isNotEmpty())
<section class="home-section page-cards-grid" aria-label="Highlights">
    <div class="section-head"><h2>Don't miss</h2></div>
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
                @if(!empty($card->link_url))<a class="btn" href="{{ $card->link_url }}">Find out more</a>@endif
            </article>
        @endforeach
    </div>
</section>
@endif

<section class="home-section">
    <div class="join-band">
        <div>
            <h2>There's a seat saved for you.</h2>
            <p>It's free. Make an account to keep your courses, sign up for events and get a fresh verse every morning.</p>
        </div>
        @guest
            <a class="btn btn-lg join-btn" href="{{ route('register') }}">Join free <i class="fas fa-arrow-right"></i></a>
        @else
            <a class="btn btn-lg join-btn" href="{{ route('youth.dashboard') }}">Open my space <i class="fas fa-arrow-right"></i></a>
        @endguest
    </div>
</section>

<style>
body .btn.btn-lg{min-height:52px;padding:12px 24px;font-size:1.02rem}
.home-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr);gap:40px;align-items:center;padding:28px 0 18px}
.hello{display:inline-flex;align-items:center;gap:8px;font-weight:700;color:var(--ink-soft)}
.hello::before{content:'';width:28px;height:3px;border-radius:3px;background:var(--coral)}
.hero-copy h1{font-size:clamp(2.6rem,7vw,5rem);line-height:.98;letter-spacing:-.045em;margin:14px 0 18px;font-weight:800}
.hero-copy>p{font-size:1.15rem;color:var(--ink-soft);max-width:34rem;margin:0}
.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:26px}
.proof{display:flex;gap:8px 22px;flex-wrap:wrap;margin:26px 0 0;color:var(--muted);font-weight:600}
.proof b{font-family:var(--font-display);font-size:1.25rem;color:var(--ink);margin-right:3px}

.collage{position:relative;min-height:420px}
.sticker{position:absolute;background:#fff;border:var(--bw) solid var(--edge);border-radius:22px;box-shadow:var(--pop)}
.s-verse{top:6%;left:4%;right:10%;padding:24px;background:var(--primary);color:#fff;transform:rotate(calc(-3deg * var(--tilt)))}
.s-verse .tag{display:inline-block;padding:2px 10px;border-radius:6px;background:var(--sun);color:var(--ink);font-weight:800;font-size:.85rem}
.s-verse p{font-family:var(--font-display);font-size:1.3rem;line-height:1.3;font-weight:600;margin:12px 0 8px;color:#fff}
.s-verse cite{font-style:normal;font-weight:800;color:var(--sun)}
.s-event{bottom:12%;right:0;display:flex;align-items:center;gap:12px;padding:12px 18px 12px 12px;transform:rotate(calc(2.5deg * var(--tilt)));max-width:80%}
.s-event small{display:block;color:var(--muted);font-weight:700}
.s-event strong{display:block;font-size:1.02rem;line-height:1.2}
.s-streak{bottom:2%;left:2%;padding:10px 18px;background:var(--coral);color:#fff;font-weight:800;font-size:1.05rem;transform:rotate(calc(-6deg * var(--tilt)))}
.s-badge{top:0;right:2%;width:64px;height:64px;border-radius:50%;display:grid;place-items:center;background:var(--sun);font-size:1.6rem;color:var(--ink);transform:rotate(calc(12deg * var(--tilt)))}
@media(prefers-reduced-motion:no-preference){
    .sticker{animation:drop .6s cubic-bezier(.22,1.4,.36,1) both}
    .s-event{animation-delay:.12s}.s-streak{animation-delay:.24s}.s-badge{animation-delay:.36s}
    @keyframes drop{from{opacity:0;translate:0 -18px}to{opacity:1;translate:0 0}}
}

.slides-banner{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:34px 0 0}
.slide-card{overflow:hidden;padding:0}
.slide-card img{width:100%;height:220px;object-fit:cover;display:block;border-radius:0}
.slide-body{padding:18px}
.slide-body h2{margin:0 0 6px;font-size:1.3rem}
.slide-body p{margin:0 0 14px}

.home-section{margin-top:54px}
.paths{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.path{display:flex;flex-direction:column;gap:6px;padding:26px;border-radius:var(--r-card);border:var(--bw) solid var(--edge);transition:transform .18s ease,box-shadow .18s ease}
.path:hover{transform:translate(-3px,-3px);box-shadow:var(--pop)}
.path h3{font-size:1.6rem;margin:10px 0 2px;letter-spacing:-.03em}
.path p{margin:0 0 12px;color:var(--ink-soft)}
.path-icon{width:52px;height:52px;border-radius:16px;display:grid;place-items:center;background:#fff;border:var(--bw) solid var(--edge);font-size:1.25rem;color:var(--ink)}
.path-go{margin-top:auto;font-weight:800}
.p-sun{background:var(--sun-soft)}.p-leaf{background:var(--leaf-soft)}.p-coral{background:var(--coral-soft)}

.annual-theme{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr);margin-top:54px;border-radius:var(--r-card);overflow:hidden;background:var(--ink);color:#fff}
.annual-theme:not(:has(.annual-theme-media)){grid-template-columns:1fr}
.annual-theme-media img{width:100%;height:100%;min-height:260px;object-fit:cover;display:block}
.annual-theme-copy{padding:clamp(24px,4vw,46px);align-self:center}
.theme-year{display:inline-block;padding:4px 12px;border-radius:999px;background:var(--sun);color:var(--ink);font-weight:800}
.annual-theme-copy h2{color:#fff;font-size:clamp(1.8rem,4vw,2.8rem);line-height:1.08;letter-spacing:-.03em;margin:16px 0 10px}
.theme-scripture{font-weight:800;color:var(--sun)}
.theme-desc{color:rgba(255,255,255,.8);max-width:38rem}

.event-list{display:grid;gap:10px}
.event-row{display:flex;align-items:center;gap:16px;padding:12px 18px 12px 12px}
.event-copy{flex:1;min-width:0}
.event-copy strong,.event-copy small{display:block}
.event-copy strong{font-size:1.08rem}
.event-copy small{color:var(--muted);margin-top:2px}
.event-row>i{color:var(--muted)}
.story-card{display:flex;flex-direction:column;align-items:flex-start;gap:4px}
.story-card h3{margin:8px 0 4px}
.highlight-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:var(--sun-soft);color:var(--ink);font-size:1.2rem;margin-bottom:12px}
.page-card-img{width:100%;height:180px;object-fit:cover;border-radius:14px;margin-bottom:14px}

.join-band{display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;padding:clamp(26px,5vw,48px);border-radius:28px;background:var(--sun);border:var(--bw) solid var(--edge);box-shadow:var(--pop)}
.join-band h2{font-size:clamp(1.8rem,4vw,2.6rem);letter-spacing:-.035em;margin:0 0 8px}
.join-band p{margin:0;max-width:36rem;color:var(--ink);font-size:1.05rem}
body .btn.join-btn{background:var(--ink);border-color:var(--edge-strong);color:#fff}
body .btn.join-btn:hover{background:#000;color:#fff}

@media(max-width:900px){
    .home-hero{grid-template-columns:1fr;gap:10px}
    .collage{min-height:360px;max-width:520px}
    .paths,.slides-banner{grid-template-columns:1fr}
    .annual-theme{grid-template-columns:1fr}.annual-theme-media img{max-height:260px;min-height:0}
}
@media(max-width:640px){
    .collage{min-height:330px}
    .s-verse{left:0;right:4%;padding:18px}.s-verse p{font-size:1.1rem}
    .hero-actions .btn{flex:1 1 100%}
    .join-band .btn{width:100%}
}
</style>
@endsection
