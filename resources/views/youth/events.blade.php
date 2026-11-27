@extends('youth.layout')
@section('title', 'My Events | COU Youth')
@section('youth_content')
<section class="hero">
    <h1>My <span class="scribble">events</span></h1>
    <p>Worship nights, camps, conferences and get-togethers. Everything you've signed up for, plus what's coming next.</p>
</section>

@if($upcomingEvents->isNotEmpty())
<section class="activity-section">
    <div class="section-head"><div><h2>Coming up near you</h2><p class="muted">Grab a spot, and bring a friend along.</p></div><a class="btn" href="{{ route('public.events') }}">See every event</a></div>
    <div class="event-list">
        @foreach($upcomingEvents as $event)
            <article class="card event-row">
                <span class="date-tile"><b>{{ $event->starts_at?->format('j') }}</b><small>{{ $event->starts_at?->format('M') }}</small></span>
                <div class="event-copy">
                    <h3><a href="{{ route('public.events.show', $event) }}">{{ $event->title }}</a></h3>
                    <p>{{ $event->starts_at?->format('l, g:i A') }}@if($event->venue), {{ $event->venue }}@endif</p>
                </div>
                @if($event->registration_required)
                    <form method="POST" action="{{ route('youth.events.register', $event) }}">@csrf<button class="btn btn-primary" type="submit"><i class="fas fa-plus"></i> Count me in</button></form>
                @else
                    <a class="btn" href="{{ route('public.events.show', $event) }}">Details</a>
                @endif
            </article>
        @endforeach
    </div>
</section>
@endif

<section class="activity-section">
    <div class="section-head"><h2>You signed up for</h2></div>
    <div class="ticket-grid">
        @forelse($registrations as $registration)
            @php($event = $registration->event)
            @php($attendance = $registration->attendance_status ?? 'pending')
            <article class="ticket {{ $event?->starts_at?->isPast() ? 'is-past' : '' }}">
                <div class="ticket-main">
                    <span class="ticket-when">{{ $event?->starts_at?->format('D j M, g:i A') ?? 'Date to be confirmed' }}</span>
                    <h3>{{ $event?->title ?? 'Event no longer listed' }}</h3>
                    @if($event?->venue)<p><i class="fas fa-location-dot"></i> {{ $event->venue }}</p>@endif
                </div>
                <div class="ticket-stub">
                    @if($attendance === 'attended')
                        <span class="badge badge-good"><i class="fas fa-check"></i> You were there</span>
                    @elseif($event?->starts_at?->isFuture())
                        <span class="badge badge-sun">{{ ucfirst($registration->status ?? 'registered') }}</span>
                    @else
                        <span class="badge">{{ ucfirst(str_replace('_', ' ', $attendance)) }}</span>
                    @endif
                    @if($event)<a class="btn btn-ghost" href="{{ route('public.events.show', $event) }}">Details <i class="fas fa-arrow-right"></i></a>@endif
                </div>
            </article>
        @empty
            <div class="card empty-card">
                <span class="empty-emoji" aria-hidden="true"><i class="fas fa-ticket"></i></span>
                <h3>No tickets yet</h3>
                <p class="muted">When you sign up for something it'll show up here, so you never forget where to be.</p>
                <a class="btn btn-primary" href="{{ route('public.events') }}">Find an event</a>
            </div>
        @endforelse
    </div>
    <div class="pagination">{{ $registrations->links() }}</div>
</section>

<style>
.activity-section{margin-bottom:38px}
.event-list{display:grid;gap:10px}
.event-row{display:flex;align-items:center;gap:16px;padding:12px 16px 12px 12px}
.event-copy{flex:1;min-width:0}
.event-copy h3{margin:0 0 2px;font-size:1.1rem}
.event-copy h3 a:hover{color:var(--primary)}
.event-copy p{margin:0;color:var(--muted)}
.event-row form{margin:0}
.ticket-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.ticket{display:grid;grid-template-columns:minmax(0,1fr) auto;background:#fff;border:var(--bw) solid var(--edge);border-radius:var(--r-card);overflow:hidden;position:relative}
.ticket::before,.ticket::after{content:'';position:absolute;right:calc(var(--stub-w,150px) - 11px);width:22px;height:22px;border-radius:50%;background:var(--paper);border:var(--bw) solid var(--edge)}
.ticket::before{top:-12px}.ticket::after{bottom:-12px}
.ticket-main{padding:18px 20px}
.ticket-when{font-size:.82rem;font-weight:800;color:var(--primary)}
.ticket-main h3{margin:4px 0 6px}
.ticket-main p{margin:0;color:var(--muted);font-size:.9rem}
.ticket-stub{width:var(--stub-w,150px);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:14px;border-left:2px dashed color-mix(in srgb,var(--ink) 30%,transparent);background:var(--sun-soft);text-align:center}
.ticket.is-past .ticket-stub{background:var(--paper-2)}
.ticket.is-past{opacity:.85}
.empty-card{grid-column:1/-1}
@media(max-width:900px){.ticket-grid{grid-template-columns:1fr}}
@media(max-width:560px){
    .event-row{flex-wrap:wrap}.event-row form,.event-row>.btn{width:100%}.event-row form .btn{width:100%}
    .ticket{grid-template-columns:1fr}.ticket::before,.ticket::after{display:none}
    .ticket-stub{width:auto;flex-direction:row;justify-content:space-between;border-left:0;border-top:2px dashed color-mix(in srgb,var(--ink) 30%,transparent)}
}
</style>
@endsection
