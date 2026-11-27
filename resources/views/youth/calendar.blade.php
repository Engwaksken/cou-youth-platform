@extends('youth.layout')
@section('title', 'Calendar | COU Youth')
@section('youth_content')
<section class="hero">
    <h1>What's <span class="scribble">on</span></h1>
    <p>Events and online services, in date order. Tap one for the details.</p>
</section>

<div class="calendar-tabs" role="tablist" aria-label="Calendar view">
    <button class="tab active" data-tab="events" type="button" role="tab" aria-selected="true" aria-controls="tab-events"><i class="fas fa-calendar-days"></i> Events <span>{{ $events->count() }}</span></button>
    <button class="tab" data-tab="services" type="button" role="tab" aria-selected="false" aria-controls="tab-services"><i class="fas fa-circle-play"></i> Online <span>{{ $services->count() }}</span></button>
</div>

<section class="tab-panel active" id="tab-events" role="tabpanel">
    @php($lastMonth = null)
    <div class="calendar-list">
        @forelse($events as $event)
            @php($starts = \Illuminate\Support\Carbon::parse($event->starts_at))
            @if($lastMonth !== $starts->format('Y-m'))
                @php($lastMonth = $starts->format('Y-m'))
                <h2 class="month">{{ $starts->format('F Y') }}</h2>
            @endif
            <a class="card calendar-item" href="{{ route('public.events.show', $event->id) }}">
                <span class="date-tile"><b>{{ $starts->format('j') }}</b><small>{{ $starts->format('D') }}</small></span>
                <span class="item-copy">
                    <strong>{{ $event->title }}</strong>
                    <small>{{ $starts->format('g:i A') }}@if(!empty($event->venue)), {{ $event->venue }}@endif</small>
                </span>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        @empty
            <div class="card empty-card"><span class="empty-emoji" aria-hidden="true"><i class="fas fa-calendar-days"></i></span><h3>The calendar's clear</h3><p class="muted">New events show up here as soon as they're announced.</p></div>
        @endforelse
    </div>
</section>

<section class="tab-panel" id="tab-services" role="tabpanel">
    @php($lastMonth = null)
    <div class="calendar-list">
        @forelse($services as $service)
            @php($starts = \Illuminate\Support\Carbon::parse($service->starts_at))
            @if($lastMonth !== $starts->format('Y-m'))
                @php($lastMonth = $starts->format('Y-m'))
                <h2 class="month">{{ $starts->format('F Y') }}</h2>
            @endif
            <a class="card calendar-item {{ ($service->status ?? '') === 'live' ? 'is-live' : '' }}" href="{{ $service->stream_url }}" target="_blank" rel="noopener noreferrer">
                <span class="date-tile"><b>{{ $starts->format('j') }}</b><small>{{ $starts->format('D') }}</small></span>
                <span class="item-copy">
                    <strong>{{ $service->title }}</strong>
                    <small>{{ $starts->format('g:i A') }}@if(!empty($service->speaker)), {{ $service->speaker }}@endif</small>
                </span>
                <i class="fas fa-play" aria-hidden="true"></i>
            </a>
        @empty
            <div class="card empty-card"><span class="empty-emoji" aria-hidden="true"><i class="fas fa-video"></i></span><h3>No online services scheduled</h3><p class="muted">Check the Watch &amp; listen page for past services in the meantime.</p><a class="btn" href="{{ route('youth.media') }}">Watch &amp; listen</a></div>
        @endforelse
    </div>
</section>

<style>
.calendar-tabs{display:flex;margin-bottom:18px;overflow-x:auto}
.calendar-tabs .tab{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;background:#fff;cursor:pointer;white-space:nowrap;border:1.5px solid var(--line)!important}
.calendar-tabs .tab span{display:inline-grid;place-items:center;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:var(--paper-2);color:var(--ink);font-size:.75rem}
.calendar-tabs .tab.active span{background:var(--sun)}
.tab-panel{display:none}.tab-panel.active{display:block}
.calendar-list{display:grid;gap:10px;max-width:52rem}
.month{font-size:1.1rem;margin:16px 0 2px;color:var(--ink-soft)}
.month:first-child{margin-top:0}
.calendar-item{display:flex;align-items:center;gap:16px;padding:12px 18px 12px 12px}
.item-copy{flex:1;min-width:0}
.item-copy strong,.item-copy small{display:block}
.item-copy strong{font-size:1.05rem}
.item-copy small{color:var(--muted);margin-top:2px}
.calendar-item>i{color:var(--muted)}
.calendar-item.is-live{border-color:var(--coral)!important}
.calendar-item.is-live .date-tile{background:var(--coral);color:#fff}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.calendar-tabs .tab');
    tabs.forEach(btn => btn.addEventListener('click', () => {
        tabs.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        document.getElementById('tab-' + btn.dataset.tab)?.classList.add('active');
    }));
});
</script>
@endsection
