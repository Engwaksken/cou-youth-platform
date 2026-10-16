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

        <h1>About COU Youth</h1>

        <p>
            COU Youth is the Church of Uganda's online home for young people: a place to grow in faith, make friends, find support and discover where you can serve.
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
            <h2>What you'll find here</h2>
            <p class="muted">
                Everything here is built to help you grow, belong and make a difference.
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
                Courses, Bible studies and devotionals to help you know God better, one step at a time.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-people-group"></i>
            </span>

            <h3>Community</h3>

            <p class="muted">
                Events, life groups and fellowships where you can meet people who will walk the journey with you.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-shield-heart"></i>
            </span>

            <h3>Safeguarding</h3>

            <p class="muted">
                Your safety matters. We ask for a parent or guardian's consent where it's needed, and our team keeps an eye on what's shared so everyone can take part safely.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-location-dot"></i>
            </span>

            <h3>Church Locator</h3>

            <p class="muted">
                Moving to a new town or just visiting? Find a church nearby and see when the youth meet.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-hand-holding-heart"></i>
            </span>

            <h3>Giving</h3>

            <p class="muted">
                Give safely and securely to youth ministry campaigns you care about.
            </p>
        </article>

        <article class="card about-feature">
            <span class="about-icon">
                <i class="fas fa-mobile-screen-button"></i>
            </span>

            <h3>Mobile Access</h3>

            <p class="muted">
                Use the mobile app or the website on any phone, tablet or computer, wherever you are.
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
                    More about youth ministry in the Church of Uganda.
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
                Create a free account to save your progress, sign up for events and find your place in the church family.
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

    .about-intro-card h2,
    .about-intro-card p {
        color: #fff;
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