@extends('public.layout')

@section('title', 'Donate | COU Youth')

@section('content')

@php
    $slides = $slides ?? collect();
    $cards = $cards ?? collect();
@endphp

@if($slides->isNotEmpty())
    <section class="slides-banner" aria-label="Giving highlights">
        @foreach($slides as $slide)
            <article class="slide-card">
                @if(!empty($slide->media_path))
                    <img
                        src="{{ asset('storage/' . $slide->media_path) }}"
                        alt="{{ $slide->title ?? 'Giving highlight' }}"
                    >
                @endif

                <div class="slide-body">
                    @if(!empty($slide->title))
                        <h2>{{ $slide->title }}</h2>
                    @endif

                    @if(!empty($slide->subtitle))
                        <p class="muted">
                            {{ $slide->subtitle }}
                        </p>
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


<section class="hero donate-hero">
    <div>
        <span class="badge">
            <i class="fas fa-hand-holding-heart"></i>
            Giving
        </span>

        <h1>Support Youth Ministry</h1>

        <p>
            Give towards discipleship, mission, outreach, ministry programmes
            and youth development initiatives across the Church of Uganda.
        </p>
    </div>

    <div class="card giving-message">
        <span class="giving-icon">
            <i class="fas fa-heart"></i>
        </span>

        <h2>Your support makes a difference</h2>

        <p>
            Every contribution helps strengthen youth discipleship,
            participation, outreach and ministry opportunities.
        </p>
    </div>
</section>


<section class="donation-section">
    <div class="section-head">
        <div>
            <h2>Active Campaigns</h2>
            <p class="muted">
                Choose a ministry campaign you would like to support.
            </p>
        </div>
    </div>

    <div class="grid donation-grid">
        @forelse($campaigns as $campaign)
            @php
                $raised = (float) ($campaign->amount_raised ?? 0);
                $target = (float) ($campaign->target_amount ?? 0);

                $percentage = $target > 0
                    ? min(100, round(($raised / $target) * 100))
                    : 0;

                $currency = $campaign->currency ?: 'UGX';
            @endphp

            <article class="card donation-card">
                @if(!empty($campaign->image_path))
                    <img
                        class="campaign-image"
                        src="{{ asset('storage/' . $campaign->image_path) }}"
                        alt="{{ $campaign->title }}"
                    >
                @elseif(!empty($campaign->icon_class))
                    <span class="campaign-icon">
                        <i class="{{ $campaign->icon_class }}"></i>
                    </span>
                @else
                    <span class="campaign-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </span>
                @endif

                <div class="campaign-top">
                    <span class="badge">
                        {{ ucfirst($campaign->status ?? 'active') }}
                    </span>

                    @if(!empty($campaign->ends_at))
                        <small class="muted">
                            <i class="fas fa-calendar"></i>
                            Ends {{ optional($campaign->ends_at)->format('d M Y') }}
                        </small>
                    @endif
                </div>

                <h3>
                    {{ $campaign->title }}
                </h3>

                @if(!empty($campaign->description))
                    <p class="muted">
                        {{
                            \Illuminate\Support\Str::limit(
                                strip_tags($campaign->description),
                                170
                            )
                        }}
                    </p>
                @endif

                <div class="campaign-amounts">
                    <strong>
                        {{ $currency }}
                        {{ number_format($raised, 0) }}
                    </strong>

                    @if($target > 0)
                        <span class="muted">
                            of {{ $currency }}
                            {{ number_format($target, 0) }}
                        </span>
                    @endif
                </div>

                @if($target > 0)
                    <div
                        class="progress"
                        role="progressbar"
                        aria-label="Campaign fundraising progress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $percentage }}"
                    >
                        <span style="width:{{ $percentage }}%"></span>
                    </div>

                    <div class="progress-meta">
                        <span>{{ $percentage }}% raised</span>

                        <span>
                            {{ $currency }}
                            {{ number_format(max(0, $target - $raised), 0) }}
                            remaining
                        </span>
                    </div>
                @endif

                <div class="campaign-actions">
                    @auth
                        <a
                            class="btn btn-primary"
                            href="{{ route('login') }}"
                        >
                            <i class="fas fa-hand-holding-dollar"></i>
                            Continue to Give
                        </a>
                    @else
                        <a
                            class="btn btn-primary"
                            href="{{ route('login') }}"
                        >
                            <i class="fas fa-right-to-bracket"></i>
                            Sign in to Donate
                        </a>
                    @endauth
                </div>
            </article>
        @empty
            <div class="card empty-state">
                <span class="empty-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </span>

                <h3>No active donation campaigns</h3>

                <p class="muted">
                    There are currently no active donation campaigns available.
                </p>
            </div>
        @endforelse
    </div>

    <div class="pagination">
        {{ $campaigns->links() }}
    </div>
</section>


@if($cards->isNotEmpty())
    <section class="page-cards-grid" aria-label="Featured giving information">
        <div class="section-head">
            <div>
                <h2>Featured</h2>
                <p class="muted">
                    Learn more about giving and youth ministry initiatives.
                </p>
            </div>
        </div>

        <div class="grid">
            @foreach($cards as $card)
                <article class="card featured-card">
                    @if(!empty($card->image_path))
                        <img
                            class="page-card-img"
                            src="{{ asset('storage/' . $card->image_path) }}"
                            alt="{{ $card->title ?? 'Highlight' }}"
                        >
                    @elseif(!empty($card->icon))
                        <span class="featured-icon">
                            <i class="fas {{ $card->icon }}"></i>
                        </span>
                    @endif

                    @if(!empty($card->title))
                        <h3>
                            {{ $card->title }}
                        </h3>
                    @endif

                    @if(!empty($card->body))
                        <p class="muted">
                            {{ $card->body }}
                        </p>
                    @endif

                    @if(!empty($card->link_url))
                        <p>
                            <a
                                class="btn"
                                href="{{ $card->link_url }}"
                            >
                                Learn more
                            </a>
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif


<section class="donation-section">
    <div class="card giving-note">
        <div>
            <h2>Give securely</h2>

            <p class="muted">
                Sign in to your youth account to continue with available
                secure payment options and receive transaction confirmation.
            </p>
        </div>

        @guest
            <a
                class="btn btn-primary"
                href="{{ route('login') }}"
            >
                <i class="fas fa-lock"></i>
                Youth Login
            </a>
        @else
            <a
                class="btn btn-primary"
                href="{{ route('home') }}"
            >
                <i class="fas fa-user"></i>
                Youth Account
            </a>
        @endguest
    </div>
</section>


<style>
    .slides-banner {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .slide-card {
        overflow: hidden;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 18px;
    }

    .slide-card img {
        display: block;
        width: 100%;
        height: 210px;
        object-fit: cover;
    }

    .slide-body {
        padding: 18px;
    }

    .slide-body h2 {
        margin: 0 0 6px;
        font-size: 1.2rem;
    }

    .slide-body p {
        margin: 0 0 14px;
    }

    .donate-hero {
        display: grid;
        grid-template-columns: 1.35fr .9fr;
        gap: 28px;
        align-items: center;
    }

    .donate-hero h1 {
        margin-top: 16px;
    }

    .giving-message {
        background: var(--primary);
        color: #fff;
        border: 0;
        padding: 26px;
    }

    .giving-message p {
        opacity: .88;
    }

    .giving-icon,
    .campaign-icon,
    .featured-icon,
    .empty-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: color-mix(
            in srgb,
            var(--primary) 12%,
            #fff 88%
        );
        color: var(--primary);
        font-size: 1.15rem;
        margin-bottom: 14px;
    }

    .giving-icon {
        background: rgba(255, 255, 255, .16);
        color: #fff;
    }

    .donation-section,
    .page-cards-grid {
        margin-top: 38px;
    }

    .section-head {
        margin-bottom: 16px;
    }

    .section-head h2 {
        margin: 0 0 5px;
    }

    .section-head p {
        margin: 0;
    }

    .donation-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .campaign-image {
        width: 100%;
        height: 190px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 14px;
    }

    .campaign-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .donation-card h3 {
        margin: 14px 0 8px;
    }

    .campaign-amounts {
        display: flex;
        align-items: baseline;
        gap: 7px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .campaign-amounts strong {
        font-size: 1.15rem;
        color: var(--primary);
    }

    .progress {
        height: 9px;
        background: color-mix(
            in srgb,
            var(--primary) 12%,
            #fff 88%
        );
        border-radius: 999px;
        overflow: hidden;
        margin-top: 14px;
    }

    .progress span {
        display: block;
        height: 100%;
        background: var(--primary);
        border-radius: inherit;
    }

    .progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 7px;
        font-size: .8rem;
        color: var(--muted);
    }

    .campaign-actions {
        margin-top: auto;
        padding-top: 18px;
    }

    .campaign-actions .btn {
        width: 100%;
    }

    .empty-state {
        text-align: center;
        grid-column: 1 / -1;
    }

    .empty-icon {
        margin-inline: auto;
    }

    .page-card-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 14px;
    }

    .giving-note {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .giving-note h2 {
        margin: 0 0 7px;
    }

    .giving-note p {
        margin: 0;
    }

    @media (max-width: 900px) {
        .slides-banner {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .donate-hero {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .slides-banner {
            grid-template-columns: 1fr;
        }

        .giving-note {
            align-items: stretch;
        }

        .giving-note .btn {
            width: 100%;
        }
    }
</style>

@endsection