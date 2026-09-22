@extends('public.layout')

@section('title', 'Church Locator | COU Youth')

@section('content')

@php
    $slides = $slides ?? collect();
    $cards = $cards ?? collect();
@endphp

@if($slides->isNotEmpty())
    <section class="slides-banner" aria-label="Church highlights">
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
                        <h2>
                            {{ $slide->title }}
                        </h2>
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


<section class="hero church-hero">
    <div>
        <span class="badge">
            <i class="fas fa-church"></i>
            Church of Uganda
        </span>

        <h1>Church Locator</h1>

        <p>
            Find Church of Uganda congregations, contact information,
            service locations and youth fellowship details.
        </p>
    </div>
</section>


<form class="filters church-search" method="GET">
    <input
        type="search"
        name="q"
        value="{{ request('q') }}"
        placeholder="Search church or location"
        aria-label="Search church or location"
    >

    <button class="btn btn-primary" type="submit">
        <i class="fas fa-search"></i>
        Search
    </button>

    @if(request()->filled('q'))
        <a class="btn" href="{{ route('public.churches') }}">
            <i class="fas fa-xmark"></i>
            Clear
        </a>
    @endif
</form>


<div class="grid church-grid">
    @forelse($items as $church)
        @php
            $churchName = $church->name
                ?: $church->organisationUnit?->name
                ?: 'Church of Uganda';

            $phone = $church->phone
                ?: $church->contact_phone
                ?: $church->organisationUnit?->phone;

            $email = $church->email
                ?: $church->contact_email
                ?: $church->organisationUnit?->email;

            $address = $church->address
                ?: $church->location
                ?: $church->organisationUnit?->address;
        @endphp

        <article class="card church-card">
            @if(!empty($church->image_path))
                <img
                    class="church-card-image"
                    src="{{ asset('storage/' . $church->image_path) }}"
                    alt="{{ $churchName }}"
                >
            @elseif(!empty($church->icon_class))
                <span class="church-icon">
                    <i class="{{ $church->icon_class }}"></i>
                </span>
            @else
                <span class="church-icon">
                    <i class="fas fa-church"></i>
                </span>
            @endif

            <h3>
                {{ $churchName }}
            </h3>

            @if(
                !empty($church->organisationUnit?->name)
                && $church->organisationUnit?->name !== $churchName
            )
                <p class="church-unit muted">
                    <i class="fas fa-sitemap"></i>
                    {{ $church->organisationUnit->name }}
                </p>
            @endif

            <div class="church-details">
                <p>
                    <i class="fas fa-location-dot"></i>

                    <span>
                        {{ $address ?: 'Address not yet provided' }}
                    </span>
                </p>

                <p>
                    <i class="fas fa-people-group"></i>

                    <span>
                        {{
                            $church->youth_fellowship_times
                                ?: 'Youth fellowship times to be confirmed'
                        }}
                    </span>
                </p>

                @if(!empty($church->service_times))
                    <p>
                        <i class="fas fa-clock"></i>
                        <span>{{ $church->service_times }}</span>
                    </p>
                @endif

                @if(!empty($phone))
                    <p>
                        <i class="fas fa-phone"></i>

                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">
                            {{ $phone }}
                        </a>
                    </p>
                @endif

                @if(!empty($email))
                    <p>
                        <i class="fas fa-envelope"></i>

                        <a href="mailto:{{ $email }}">
                            {{ $email }}
                        </a>
                    </p>
                @endif
            </div>

            @if(
                !empty($church->latitude)
                && !empty($church->longitude)
            )
                <div class="church-actions">
                    <a
                        class="btn"
                        href="https://www.google.com/maps/search/?api=1&query={{ urlencode($church->latitude . ',' . $church->longitude) }}"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="fas fa-map-location-dot"></i>
                        View Map
                    </a>
                </div>
            @endif
        </article>
    @empty
        <div class="card empty-state">
            <span class="empty-icon">
                <i class="fas fa-location-dot"></i>
            </span>

            <h3>No church locations found</h3>

            <p class="muted">
                Try another church name or location.
            </p>
        </div>
    @endforelse
</div>


<div class="pagination">
    {{ $items->links() }}
</div>


@if($cards->isNotEmpty())
    <section class="page-cards-grid" aria-label="Featured">
        <div class="section-head">
            <div>
                <h2>Featured</h2>
                <p class="muted">
                    Helpful church and youth ministry information.
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

    .church-hero {
        padding-bottom: 18px;
    }

    .church-search {
        margin-bottom: 24px;
    }

    .church-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .church-card-image {
        display: block;
        width: 100%;
        height: 190px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 14px;
    }

    .church-icon,
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

    .church-card h3 {
        margin-bottom: 8px;
    }

    .church-unit {
        margin-top: 0;
    }

    .church-details {
        margin-top: 8px;
    }

    .church-details p {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 10px 0;
        color: var(--muted);
    }

    .church-details i {
        width: 18px;
        margin-top: 4px;
        color: var(--primary);
        text-align: center;
    }

    .church-details a {
        color: inherit;
    }

    .church-details a:hover {
        color: var(--primary);
        text-decoration: underline;
    }

    .church-actions {
        margin-top: auto;
        padding-top: 12px;
    }

    .empty-state {
        text-align: center;
        grid-column: 1 / -1;
    }

    .empty-state .empty-icon {
        margin-inline: auto;
    }

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

    .page-card-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 14px;
    }

    @media (max-width: 900px) {
        .slides-banner {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .slides-banner {
            grid-template-columns: 1fr;
        }

        .church-search {
            display: grid;
            grid-template-columns: 1fr;
        }

        .church-search input,
        .church-search .btn {
            width: 100%;
        }
    }
</style>

@endsection