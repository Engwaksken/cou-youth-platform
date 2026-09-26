@extends('youth.layout')
@section('title', 'My Events | COU Youth')
@section('youth_content')
<section class="hero"><span class="section-kicker">EVENTS</span><h1>My events</h1><p>Track your registrations, attendance and upcoming Church of Uganda youth events.</p></section>

<section class="activity-section">
<div class="section-head"><div><span class="section-kicker">REGISTERED</span><h2>Your event registrations</h2></div><a class="btn" href="{{ route('public.events') }}">Browse all events</a></div>
<div class="event-grid">
@forelse($registrations as $registration)
@php($event=$registration->event)
<article class="card event-card">
<div class="event-icon"><i class="fas fa-calendar-check"></i></div>
<h3>{{ $event?->title ?? 'Event unavailable' }}</h3>
@if($event?->starts_at)<p class="event-meta"><i class="fas fa-clock"></i> {{ $event->starts_at->format('D, d M Y · H:i') }}</p>@endif
@if($event?->venue)<p class="event-meta"><i class="fas fa-location-dot"></i> {{ $event->venue }}</p>@endif
<div class="status-row"><span class="badge">{{ ucfirst($registration->status ?? 'registered') }}</span><span class="badge">Attendance: {{ ucfirst(str_replace('_',' ',$registration->attendance_status ?? 'pending')) }}</span></div>
@if($event)<a class="btn" href="{{ route('public.events.show',$event) }}">View event</a>@endif
</article>
@empty
<div class="card empty-card"><h3>No event registrations yet</h3><p class="muted">Browse upcoming events and register for the ones you want to attend.</p><a class="btn btn-primary" href="{{ route('public.events') }}">Explore events</a></div>
@endforelse
</div>
<div class="pagination">{{ $registrations->links() }}</div>
</section>

@if($upcomingEvents->isNotEmpty())
<section class="activity-section">
<div class="section-head"><div><span class="section-kicker">UPCOMING</span><h2>Events you can join</h2></div></div>
<div class="event-grid">
@foreach($upcomingEvents as $event)
<article class="card event-card">
<div class="event-icon"><i class="fas fa-calendar-days"></i></div>
<h3>{{ $event->title }}</h3>
<p class="event-meta"><i class="fas fa-clock"></i> {{ $event->starts_at?->format('D, d M Y · H:i') }}</p>
@if($event->venue)<p class="event-meta"><i class="fas fa-location-dot"></i> {{ $event->venue }}</p>@endif
@if($event->registration_required)
<form method="POST" action="{{ route('youth.events.register',$event) }}">@csrf<button class="btn btn-primary" type="submit">Register</button></form>
@else
<a class="btn" href="{{ route('public.events.show',$event) }}">View event</a>
@endif
</article>
@endforeach
</div>
</section>
@endif
<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}.activity-section{margin-bottom:34px}.section-head{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:15px}.section-head h2{margin:2px 0 0}.event-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.event-card{display:flex;flex-direction:column;gap:10px}.event-card h3{margin:0}.event-icon{width:46px;height:46px;border-radius:10px;background:#f0ebf8;color:var(--primary);display:grid;place-items:center}.event-meta{margin:0;color:var(--muted);font-size:.88rem}.status-row{display:flex;gap:7px;flex-wrap:wrap;margin-top:auto}.empty-card{grid-column:1/-1;text-align:center;padding:30px}@media(max-width:900px){.event-grid{grid-template-columns:1fr 1fr}}@media(max-width:620px){.event-grid{grid-template-columns:1fr}.section-head{align-items:flex-start;flex-direction:column}}
</style>
@endsection
