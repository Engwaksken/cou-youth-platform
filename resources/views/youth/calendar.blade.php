@extends('youth.layout')
@section('title', 'Calendar | COU Youth')
@section('youth_content')
@php
    $eventCount = $events->count();
    $serviceCount = $services->count();
    $thisMonthEvents = $events->filter(fn($event) => \Illuminate\Support\Carbon::parse($event->starts_at)->isSameMonth(now()))->count();
    $thisMonthServices = $services->filter(fn($service) => \Illuminate\Support\Carbon::parse($service->starts_at)->isSameMonth(now()))->count();
@endphp
<section class="hero"><span class="section-kicker">CALENDAR</span><h1>Youth calendar</h1><p>See upcoming youth events and online services in one place.</p></section>
<div class="page-stats">
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-calendar-days"></i></span><div><strong>{{ $eventCount }}</strong><span>Events listed</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-video"></i></span><div><strong>{{ $serviceCount }}</strong><span>Online services</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-calendar-check"></i></span><div><strong>{{ $thisMonthEvents }}</strong><span>Events this month</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-clock"></i></span><div><strong>{{ $thisMonthServices }}</strong><span>Services this month</span></div></div>
</div>
<div class="calendar-tabs" role="tablist"><button class="tab active" data-tab="events" type="button"><i class="fas fa-calendar-days"></i> Events <span>{{ $events->count() }}</span></button><button class="tab" data-tab="services" type="button"><i class="fas fa-video"></i> Online Services <span>{{ $services->count() }}</span></button></div>
<section class="tab-panel active" id="tab-events"><div class="calendar-list">
@forelse($events as $event)
<article class="card calendar-item"><div class="date-box"><strong>{{ \Illuminate\Support\Carbon::parse($event->starts_at)->format('d') }}</strong><span>{{ \Illuminate\Support\Carbon::parse($event->starts_at)->format('M') }}</span></div><div><h3>{{ $event->title }}</h3><p class="muted"><i class="fas fa-clock"></i> {{ \Illuminate\Support\Carbon::parse($event->starts_at)->format('D, d M Y · H:i') }}</p>@if(!empty($event->venue))<p class="muted"><i class="fas fa-location-dot"></i> {{ $event->venue }}</p>@endif</div><a class="btn" href="{{ route('public.events.show',$event->id) }}">View</a></article>
@empty
<div class="card empty-card">No published events are available.</div>
@endforelse
</div></section>
<section class="tab-panel" id="tab-services"><div class="calendar-list">
@forelse($services as $service)
<article class="card calendar-item"><div class="date-box"><strong>{{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('d') }}</strong><span>{{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('M') }}</span></div><div><h3>{{ $service->title }}</h3><p class="muted"><i class="fas fa-clock"></i> {{ \Illuminate\Support\Carbon::parse($service->starts_at)->format('D, d M Y · H:i') }}</p>@if(!empty($service->speaker))<p class="muted"><i class="fas fa-user"></i> {{ $service->speaker }}</p>@endif</div><a class="btn btn-primary" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer">Open</a></article>
@empty
<div class="card empty-card">No scheduled online services are available.</div>
@endforelse
</div></section>
<style>.calendar-tabs{display:flex;gap:6px;border-bottom:1px solid var(--border);margin-bottom:18px;overflow-x:auto}.tab{border:0;background:transparent;padding:11px 14px;font-weight:800;color:var(--muted);border-bottom:3px solid transparent;cursor:pointer;white-space:nowrap}.tab:hover,.tab.active{color:var(--primary);background:var(--primary-soft)}.tab.active{border-color:var(--primary)}.tab span{display:inline-flex;min-width:22px;height:22px;align-items:center;justify-content:center;border-radius:999px;background:var(--primary-light);font-size:.75rem;margin-left:5px}.tab-panel{display:none}.tab-panel.active{display:block}.calendar-list{display:grid;gap:12px}.calendar-item{display:grid;grid-template-columns:auto 1fr auto;gap:16px;align-items:center}.calendar-item h3{margin:0 0 5px}.calendar-item p{margin:3px 0}.date-box{width:60px;height:60px;border-radius:12px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;align-content:center}.date-box strong{font-size:1.3rem;line-height:1}.date-box span{font-size:.75rem;font-weight:800;text-transform:uppercase}.empty-card{text-align:center}@media(max-width:620px){.calendar-item{grid-template-columns:auto 1fr}.calendar-item>.btn{grid-column:1/-1;width:100%}}</style>
<script>document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.calendar-tabs .tab').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.calendar-tabs .tab').forEach(b=>b.classList.remove('active'));document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));btn.classList.add('active');document.getElementById('tab-'+btn.dataset.tab)?.classList.add('active')}))});</script>
@endsection
