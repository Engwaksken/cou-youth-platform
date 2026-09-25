@extends('public.layout')
@section('title', $event->title.' | COU Youth')
@section('content')
<nav class="detail-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('public.events') }}"><i class="fas fa-arrow-left"></i> Back to Events</a></nav>

<section class="detail-layout">
    <article class="detail-main">
        @if(!empty($event->poster_path))
            <img class="detail-hero-image" src="{{ asset('storage/'.$event->poster_path) }}" alt="{{ $event->title }}">
        @endif

        <div class="detail-heading">
            <div class="detail-badges">
                <span class="badge">{{ ucwords(str_replace('_',' ',$event->category ?: 'Youth Event')) }}</span>
                @if($event->registration_required)<span class="badge badge-soft">Registration required</span>@endif
            </div>
            <h1>{{ $event->title }}</h1>
            @if(!empty($event->theme))<p class="detail-lead">{{ $event->theme }}</p>@endif
        </div>

        @if(!empty($event->description))
        <section class="card detail-section">
            <h2>About this event</h2>
            <div class="prose">{!! nl2br(e($event->description)) !!}</div>
        </section>
        @endif

        <section class="card detail-section">
            <h2>Event information</h2>
            <div class="info-grid">
                <div class="info-item"><span class="info-icon"><i class="fas fa-calendar-days"></i></span><div><small>Date & time</small><strong>{{ optional($event->starts_at)->format('D, d M Y • H:i') ?: 'To be confirmed' }}</strong>@if($event->ends_at)<span>Until {{ optional($event->ends_at)->format('d M Y • H:i') }}</span>@endif</div></div>
                <div class="info-item"><span class="info-icon"><i class="fas fa-location-dot"></i></span><div><small>Venue</small><strong>{{ $event->venue ?: 'To be confirmed' }}</strong></div></div>
                @if(!empty($event->speaker))<div class="info-item"><span class="info-icon"><i class="fas fa-microphone"></i></span><div><small>Speaker</small><strong>{{ $event->speaker }}</strong></div></div>@endif
                @if(!empty($event->organizer))<div class="info-item"><span class="info-icon"><i class="fas fa-people-group"></i></span><div><small>Organiser</small><strong>{{ $event->organizer }}</strong></div></div>@endif
                @if($event->fee !== null)<div class="info-item"><span class="info-icon"><i class="fas fa-wallet"></i></span><div><small>Fee</small><strong>{{ (float)$event->fee > 0 ? ($event->currency.' '.number_format((float)$event->fee,0)) : 'Free' }}</strong></div></div>@endif
                @if(!empty($event->capacity))<div class="info-item"><span class="info-icon"><i class="fas fa-users"></i></span><div><small>Capacity</small><strong>{{ number_format((int)$event->capacity) }} participants</strong></div></div>@endif
            </div>
        </section>
    </article>

    <aside class="detail-sidebar">
        <div class="card action-card">
            <span class="action-icon"><i class="fas fa-calendar-check"></i></span>
            <h2>Join this event</h2>
            @if($event->registration_required)
                @if($event->registration_deadline)<p class="muted">Register by {{ optional($event->registration_deadline)->format('d M Y') }}.</p>@endif
                <a class="btn btn-primary btn-wide" href="{{ $event->registrationUrl() }}"><i class="fas fa-user-check"></i> Register now</a>
            @else
                <p class="muted">Registration is not required. You are welcome to attend.</p>
            @endif
        </div>
    </aside>
</section>

@if($relatedEvents->isNotEmpty())
<section class="related-section"><div class="section-head"><div><span class="section-kicker">MORE EVENTS</span><h2>You may also like</h2></div></div><div class="grid">@foreach($relatedEvents as $related)<a class="card related-card" href="{{ route('public.events.show',$related) }}"><span class="related-icon"><i class="fas fa-calendar-days"></i></span><h3>{{ $related->title }}</h3><p class="muted">{{ optional($related->starts_at)->format('d M Y • H:i') ?: 'Date to be confirmed' }}</p><span class="detail-link">View details <i class="fas fa-arrow-right"></i></span></a>@endforeach</div></section>
@endif

<style>
.card{border-radius:10px!important}.detail-breadcrumb{margin-bottom:20px}.detail-breadcrumb a{display:inline-flex;gap:8px;align-items:center;color:var(--primary);font-weight:800}.detail-layout{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(260px,.65fr);gap:24px;align-items:start}.detail-main{min-width:0}.detail-hero-image{width:100%;max-height:460px;object-fit:cover;border-radius:10px;display:block;margin-bottom:22px}.detail-heading{margin-bottom:22px}.detail-heading h1{font-size:clamp(2rem,5vw,3.2rem);line-height:1.08;margin:10px 0}.detail-lead{font-size:1.1rem;color:var(--muted)}.detail-badges{display:flex;gap:8px;flex-wrap:wrap}.badge-soft{background:#edf4f8;color:var(--primary-2)}.detail-section{margin-bottom:18px}.detail-section h2{margin:0 0 16px}.prose{color:var(--text);line-height:1.75}.info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.info-item{display:flex;gap:12px;align-items:center;padding:14px;background:#faf9fc;border:1px solid var(--border);border-radius:10px;min-height:104px}.info-item>div{min-width:0}.info-icon,.action-icon,.related-icon{width:44px;height:44px;min-width:44px;min-height:44px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:inline-flex;align-items:center;justify-content:center;line-height:1;flex:0 0 44px}.info-icon i,.action-icon i,.related-icon i{display:block;margin:0;line-height:1;font-size:1rem;text-align:center}.info-item small,.info-item span:not(.info-icon){display:block;color:var(--muted)}.info-item strong{display:block;margin:2px 0;color:var(--text)}.detail-sidebar{position:sticky;top:92px}.action-card{text-align:center;padding:24px}.action-icon{margin:0 auto 14px;width:52px;height:52px;min-width:52px;min-height:52px;flex-basis:52px}.action-card h2{margin:0 0 8px}.btn-wide{width:100%;margin-top:10px}.related-section{margin-top:36px}.section-head{margin-bottom:14px}.section-kicker{color:var(--primary);font-size:.72rem;font-weight:900;letter-spacing:.12em}.related-card{display:block;color:inherit}.related-icon{margin-bottom:12px}.detail-link{color:var(--primary);font-weight:800}@media(max-width:850px){.detail-layout{grid-template-columns:1fr}.detail-sidebar{position:static}.info-grid{grid-template-columns:1fr}}
</style>
@endsection