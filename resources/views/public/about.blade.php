@extends('public.layout')

@section('title', 'About | COU Youth Platform')

@section('content')

@php
    $slides = $slides ?? collect();
    $cards = $cards ?? collect();
@endphp

@if($slides->isNotEmpty())
    <section class="about-slides" aria-label="About page highlights">
        @foreach($slides as $slide)
            <article class="about-slide">
                @if(!empty($slide->media_path))
                    <img
                        src="{{ asset('storage/' . $slide->media_path) }}"
                        alt="{{ $slide->title ?? 'About highlight' }}"
                    >
                @endif

                <div class="about-slide-body">
                    @if(!empty($slide->title))
                        <h2>{{ $slide->title }}</h2>
                    @endif

                    @if(!empty($slide->subtitle))
                        <p>{{ $slide->subtitle }}</p>
                    @endif

                    @if(!empty($slide->link_url))
                        <a
                            class="btn btn-primary"
                            href="{{ $slide->link_url }}"
                        >
                            Learn more
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </section>
@endif


<section class="hero about-hero">
    <div>
        <span class="badge">
            <i class="fas fa-cross"></i>
            Church of Uganda Youth Ministry
        </span>

        <h1>About the COU Youth Platform</h1>

        <p>
            A connected digital platform supporting discipleship, community,
            participation, opportunities, safeguarding and service for young
            people across the Church of Uganda.
        </p>

        <div class="about-actions">
            <a class="btn btn-primary" href="{{ route('public.courses') }}">
                <i class="fas fa-book-bible"></i>
                Explore Courses
            </a>

            <a class="btn" href="{{ route('public.events') }}">
                <i class="fas fa-calendar-days"></i>
                View Events
            </a>
        </div>
    </div>

    <div class="card about-intro-card">
        <span class="about-intro-icon">
            <i class="fas fa-people-group"></i>
        </span>

        <h2>Connecting young people</h2>

        <p>
            The platform helps young people discover church programmes,
            discipleship resources, events, opportunities, pastoral support
            and safe ways to participate in ministry.
        </p>
    </div>
</section>


<section class="about-section">
    <div class="section-head">
        <div>
            <h2>What the platform provides</h2>
            <p class="muted">
                A digital ecosystem designed around faith, participation,
                inclusion and youth development.
            </p>
        </div>
    </div>

    <div class="grid">
        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-book-bible"></i>
            </span>

            <h3>Discipleship</h3>

            <p class="muted">
                Access courses, Bible study materials, devotionals and
                faith-building resources.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-people-group"></i>
            </span>

            <h3>Community</h3>

            <p class="muted">
                Connect with events, life groups, ministries and youth
                fellowships across the Church of Uganda.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-shield-heart"></i>
            </span>

            <h3>Safeguarding</h3>

            <p class="muted">
                Age-aware consent, moderation and support workflows help
                provide safer participation for young people.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-location-dot"></i>
            </span>

            <h3>Church Locator</h3>

            <p class="muted">
                Discover Church of Uganda congregations and youth activities
                in different locations.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-hand-holding-heart"></i>
            </span>

            <h3>Giving</h3>

            <p class="muted">
                Support approved youth ministry campaigns through secure
                giving and payment channels.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-mobile-screen-button"></i>
            </span>

            <h3>Mobile Access</h3>

            <p class="muted">
                Access personalised youth services through the Flutter mobile
                application and responsive website.
            </p>
        </article>
    </div>
</section>


@if($cards->isNotEmpty())
    <section class="about-section" aria-label="About page highlights">
        <div class="section-head">
            <div>
                <h2>Highlights</h2>
                <p class="muted">
                    Learn more about the Church of Uganda youth ministry.
                </p>
            </div>
        </div>

        <div class="grid">
            @foreach($cards as $card)
                <article class="card about-highlight">
                    @if(!empty($card->image_path))
                        <img
                            class="about-card-image"
                            src="{{ asset('storage/' . $card->image_path) }}"
                            alt="{{ $card->title ?? 'Highlight' }}"
                        >
                    @elseif(!empty($card->icon))
                        <span class="about-icon">
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
                        <a
                            class="btn"
                            href="{{ $card->link_url }}"
                        >
                            Learn more
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif


<section class="about-section">
    <div class="card about-cta">
        <div>
            <h2>Be part of the COU Youth community</h2>

            <p class="muted">
                Create an account to access personalised opportunities,
                discipleship, events and ministry participation.
            </p>
        </div>

        @guest
            <a class="btn btn-primary" href="{{ route('register') }}">
                <i class="fas fa-user-plus"></i>
                Create Youth Account
            </a>
        @else
            <a class="btn btn-primary" href="{{ route('public.events') }}">
                <i class="fas fa-calendar-days"></i>
                Explore Events
            </a>
        @endguest
    </div>
</section>


<style>
    .about-slides {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .about-slide {
        overflow: hidden;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 18px;
    }

    .about-slide img {
        display: block;
        width: 100%;
        height: 210px;
        object-fit: cover;
    }

    .about-slide-body {
        padding: 18px;
    }

    .about-slide-body h2 {
        margin: 0 0 8px;
        font-size: 1.2rem;
    }

    .about-slide-body p {
        margin: 0 0 14px;
        color: var(--muted);
    }

    .about-hero {
        display: grid;
        grid-template-columns: 1.35fr .9fr;
        gap: 28px;
        align-items: center;
    }

    .about-hero h1 {
        margin-top: 16px;
    }

    .about-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .about-intro-card {
        background: var(--primary);
        color: #fff;
        border: 0;
        padding: 28px;
    }

    .about-intro-card p {
        opacity: .88;
    }

    .about-intro-icon,
    .about-icon {
        width: 48px;
        height: 48px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        background: color-mix(
            in srgb,
            var(--primary) 12%,
            #fff 88%
        );
        color: var(--primary);
        font-size: 1.15rem;
        margin-bottom: 14px;
    }

    .about-intro-icon {
        background: rgba(255, 255, 255, .16);
        color: #fff;
    }

    .about-section {
        margin-top: 38px;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        margin-bottom: 16px;
    }

    .section-head h2 {
        margin: 0 0 5px;
    }

    .section-head p {
        margin: 0;
    }

    .about-feature {
        border-top: 4px solid var(--primary);
    }

    .about-feature h3,
    .about-highlight h3 {
        margin-bottom: 7px;
    }

    .about-card-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 15px;
    }

    .about-cta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }

    .about-cta h2 {
        margin: 0 0 7px;
    }

    .about-cta p {
        margin: 0;
    }

    @media (max-width: 900px) {
        .about-slides {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .about-hero {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .about-slides {
            grid-template-columns: 1fr;
        }

        .section-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .about-cta {
            align-items: stretch;
        }

        .about-cta .btn {
            width: 100%;
        }
    }
</style>

@endsection