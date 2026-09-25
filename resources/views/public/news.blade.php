@extends('public.layout')

@section('title', 'News & Resources | COU Youth')

@section('content')

@php
    $slides = $slides ?? collect();
    $cards = $cards ?? collect();
@endphp

@if($slides->isNotEmpty())
    <section class="slides-banner" aria-label="News highlights">
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
                            <a class="btn btn-primary" href="{{ $slide->link_url }}">
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
    <h1>News & Resources</h1>
    <p>
        Latest announcements, devotions, opportunities and youth ministry updates.
    </p>
</section>


<form class="filters" method="GET">
    <input
        name="q"
        value="{{ request('q') }}"
        placeholder="Search news and resources"
    >

    <button class="btn btn-primary" type="submit">
        <i class="fas fa-search"></i>
        Search
    </button>
</form>


<div class="grid">
    @forelse($items as $item)
        <article class="card">
            <span class="badge">
                {{ ucwords(str_replace('_', ' ', $item->type ?? 'update')) }}
            </span>

            <h3 style="margin-top:12px">
                {{ $item->title }}
            </h3>

            <p class="muted">
                {{
                    $item->summary
                        ?: \Illuminate\Support\Str::limit(
                            strip_tags($item->body ?? ''),
                            160
                        )
                }}
            </p>

            <small class="muted">
                {{
                    optional(
                        $item->published_at ?: $item->created_at
                    )->format('d M Y')
                }}
            </small>
        </article>
    @empty
        <div class="card">
            No published content found.
        </div>
    @endforelse
</div>


<div class="pagination">
    {{ $items->links() }}
</div>


@if($cards->isNotEmpty())
    <section class="page-cards-grid" aria-label="Featured">
        <div class="grid">
            @foreach($cards as $card)
                <article class="card">
                    @if(!empty($card->image_path))
                        <img
                            class="page-card-img"
                            src="{{ asset('storage/' . $card->image_path) }}"
                            alt="{{ $card->title ?? 'Highlight' }}"
                        >
                    @elseif(!empty($card->icon))
                        <p>
                            <i class="fas {{ $card->icon }}"></i>
                        </p>
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

    .page-cards-grid {
        margin-top: 36px;
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
    }
</style>

@endsection