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
                    <img
                        src="{{ asset('storage/' . $slide->media_path) }}"
                        alt="{{ $slide->title ?? 'Highlight' }}"
                    >
                @endif

                <div class="slide-body">
                    @if(!empty($slide->title))
                        <h2 style="margin:0 0 6px">
                            {{ $slide->title }}
                        </h2>
                    @endif

                    @if(!empty($slide->subtitle))
                        <p class="muted" style="margin:0">
                            {{ $slide->subtitle }}
                        </p>
                    @endif

                    @if(!empty($slide->link_url))
                        <p style="margin:10px 0 0">
                            <a
                                class="btn btn-primary"
                                href="{{ $slide->link_url }}"
                            >
                                Learn more
                            </a>
                        </p>
                    @endif
                </div>
            </article>
        @endforeach
    </section>
@endif


<section class="hero">
    <div class="hero-copy">
        <span class="badge">
            <i class="fas fa-cross"></i>
            Church of Uganda Youth Ministry
        </span>

        <h1>
            Connecting young people to faith, community and opportunity.
        </h1>

        <p>
            The COU Youth Platform brings discipleship, events, church discovery,
            opportunities, safeguarding support and giving into one connected
            digital experience.
        </p>

        <div class="hero-actions">
            @guest
                <a class="btn btn-primary" href="{{ route('register') }}">
                    <i class="fas fa-user-plus"></i>
                    Create Youth Account
                </a>

                <a class="btn" href="{{ route('login') }}">
                    <i class="fas fa-right-to-bracket"></i>
                    Youth Login
                </a>
            @else
                <a class="btn btn-primary" href="{{ route('public.courses') }}">
                    <i class="fas fa-book-bible"></i>
                    Explore Courses
                </a>
            @endguest
        </div>
    </div>

    <div class="card explore-card">
        <h3>Explore the platform</h3>

        <p>
            Browse news, events, courses, churches and giving across dedicated pages.
        </p>

        <div class="explore-links">
            <a class="btn" href="{{ route('public.news') }}">
                <i class="fas fa-newspaper"></i>
                News & Resources
            </a>

            <a class="btn" href="{{ route('public.events') }}">
                <i class="fas fa-calendar-days"></i>
                Events
            </a>

            <a class="btn" href="{{ route('public.courses') }}">
                <i class="fas fa-book-bible"></i>
                Courses
            </a>

            <a class="btn" href="{{ route('public.churches') }}">
                <i class="fas fa-location-dot"></i>
                Church Locator
            </a>

            <a class="btn" href="{{ route('public.donate') }}">
                <i class="fas fa-hand-holding-heart"></i>
                Donate
            </a>
        </div>
    </div>
</section>


<section class="home-section">
    <div class="section-head">
        <div>
            <h2>Platform at a glance</h2>
            <p class="muted">
                Discover what is happening across the Church of Uganda youth ministry.
            </p>
        </div>
    </div>

    <div class="grid">
        <a class="card glance-card" href="{{ route('public.events') }}">
            <span class="glance-icon">
                <i class="fas fa-calendar-days"></i>
            </span>

            <div>
                <h3>{{ number_format($stats['events'] ?? 0) }} Events</h3>
                <p class="muted">
                    Youth worship, fellowship, missions and programmes.
                </p>
            </div>
        </a>

        <a class="card glance-card" href="{{ route('public.courses') }}">
            <span class="glance-icon">
                <i class="fas fa-book-bible"></i>
            </span>

            <div>
                <h3>{{ number_format($stats['courses'] ?? 0) }} Courses</h3>
                <p class="muted">
                    Structured discipleship and Bible study content.
                </p>
            </div>
        </a>

        <a class="card glance-card" href="{{ route('public.churches') }}">
            <span class="glance-icon">
                <i class="fas fa-church"></i>
            </span>

            <div>
                <h3>{{ number_format($stats['church_locations'] ?? 0) }} Church Locations</h3>
                <p class="muted">
                    Find congregations and youth fellowship information.
                </p>
            </div>
        </a>
    </div>
</section>


@if($latestNews->isNotEmpty())
    <section class="home-section">
        <div class="section-head">
            <div>
                <h2>Latest Updates</h2>
                <p class="muted">
                    Recent news, opportunities and devotionals.
                </p>
            </div>

            <a class="btn" href="{{ route('public.news') }}">
                View all
            </a>
        </div>

        <div class="grid">
            @foreach($latestNews->take(3) as $item)
                <article class="card update-card">
                    <span class="badge">
                        {{ ucwords(str_replace('_', ' ', $item->type ?? 'update')) }}
                    </span>

                    <h3>
                        {{ $item->title }}
                    </h3>

                    <p class="muted">
                        {{
                            $item->summary
                                ?: \Illuminate\Support\Str::limit(
                                    strip_tags($item->body ?? ''),
                                    130
                                )
                        }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>
@endif


@if($upcomingEvents->isNotEmpty())
    <section class="home-section">
        <div class="section-head">
            <div>
                <h2>Upcoming Events</h2>
                <p class="muted">
                    Join upcoming youth programmes.
                </p>
            </div>

            <a class="btn" href="{{ route('public.events') }}">
                View all
            </a>
        </div>

        <div class="grid">
            @foreach($upcomingEvents->take(3) as $event)
                <article class="card event-card">
                    <span class="event-icon">
                        <i class="fas fa-calendar-days"></i>
                    </span>

                    <h3>
                        {{ $event->title }}
                    </h3>

                    <p>
                        <i class="fas fa-calendar"></i>

                        {{
                            optional($event->starts_at)->format('d M Y H:i')
                                ?: 'Date to be confirmed'
                        }}
                    </p>

                    <p class="muted">
                        <i class="fas fa-location-dot"></i>
                        {{ $event->venue ?: 'Venue to be confirmed' }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>
@endif


@if($cards->isNotEmpty())
    <section class="home-section page-cards-grid" aria-label="Highlights">
        <div class="section-head">
            <div>
                <h2>Highlights</h2>
                <p class="muted">
                    Featured ministry content and opportunities.
                </p>
            </div>
        </div>

        <div class="grid">
            @foreach($cards as $card)
                <article class="card highlight-card">
                    @if(!empty($card->image_path))
                        <img
                            class="page-card-img"
                            src="{{ asset('storage/' . $card->image_path) }}"
                            alt="{{ $card->title ?? 'Highlight' }}"
                        >
                    @elseif(!empty($card->icon))
                        <span class="highlight-icon">
                            <i class="fas {{ $card->icon }}"></i>
                        </span>
                    @endif

                    @if(!empty($card->title))
                        <h3>{{ $card->title }}</h3>
                    @endif

                    @if(!empty($card->body))
                        <p class="muted">
                            {{ $card->body }}
                        </p>
                    @endif

                    @if(!empty($card->link_url))
                        <p>
                            <a class="btn" href="{{ $card->link_url }}">
                                Learn more
                            </a>
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif


<section class="home-section">
    <div class="card connect-card">
        <div>
            <h2>Ready to connect?</h2>
            <p class="muted">
                Create a youth account or use the mobile app to access personalised
                participation features.
            </p>
        </div>

        @guest
            <a class="btn btn-primary" href="{{ route('register') }}">
                Sign Up
            </a>
        @else
            <a class="btn btn-primary" href="{{ route('public.courses') }}">
                Continue Exploring
            </a>
        @endguest
    </div>
</section>


<style>
    .slides-banner {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .slide-card {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        background: #fff;
        border: 1px solid var(--border);
        min-height: 220px;
    }

    .slide-card img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
    }

    .slide-body {
        padding: 18px;
    }

    .hero {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 28px;
        align-items: center;
    }

    .hero-copy h1 {
        margin: 16px 0 12px;
    }

    .hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .explore-card {
        background: var(--primary);
        color: #fff;
        border: 0;
        padding: 26px;
    }

    .explore-card p {
        opacity: .86;
    }

    .explore-links {
        display: grid;
        gap: 10px;
        margin-top: 18px;
    }

    .explore-card .btn {
        background: #fff;
        color: var(--primary);
        border-color: transparent;
    }

    .explore-card .btn:hover {
        background: var(--primary-2);
        color: #fff;
    }

    .home-section {
        margin-top: 36px;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
    }

    .section-head h2 {
        margin: 0 0 4px;
    }

    .section-head p {
        margin: 0;
    }

    .glance-card {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .glance-icon,
    .event-icon,
    .highlight-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: color-mix(in srgb, var(--primary) 12%, #fff 88%);
        color: var(--primary);
        font-size: 1.1rem;
    }

    .glance-card h3 {
        margin-bottom: 6px;
    }

    .update-card,
    .event-card,
    .highlight-card {
        position: relative;
    }

    .event-card .event-icon {
        margin-bottom: 14px;
    }

    .highlight-icon {
        margin-bottom: 14px;
    }

    .page-card-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 14px;
    }

    .connect-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .connect-card h2 {
        margin: 0 0 8px;
    }

    .connect-card p {
        margin: 0;
    }

    @media (max-width: 900px) {
        .slides-banner {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 800px) {
        .hero {
            grid-template-columns: 1fr;
        }

        .section-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 640px) {
        .slides-banner {
            grid-template-columns: 1fr;
        }

        .connect-card {
            align-items: stretch;
        }

        .connect-card .btn {
            width: 100%;
        }
    }
</style>

@endsection