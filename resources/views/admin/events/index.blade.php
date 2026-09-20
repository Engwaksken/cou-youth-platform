@extends('admin.layout')
@section('title','Events')
@section('content')
@php
    $maxStatus = max(1, (int) collect($statusCounts)->max());
@endphp
<div class="page-head"><div><h1><i class="fas fa-calendar-days"></i> Events</h1><p>Manage youth events, publishing status and upcoming programmes.</p></div></div>

<div class="grid stats-grid event-stats">
    @foreach([
        ['Total events',$stats['total'],'fa-calendar','#4b2e83'],
        ['Published',$stats['published'],'fa-circle-check','#15803d'],
        ['Upcoming',$stats['upcoming'],'fa-clock','#2563eb'],
        ['Completed',$stats['completed'],'fa-flag-checkered','#b45309'],
    ] as [$label,$value,$icon,$tone])
        <div class="card stat-card event-stat" style="--tone:{{ $tone }}"><div class="stat-icon"><i class="fas {{ $icon }}"></i></div><div><strong>{{ number_format((int)$value) }}</strong><span>{{ $label }}</span></div></div>
    @endforeach
</div>

<div class="event-layout">
    <section class="card">
        <h2 style="margin-top:0">Events by status</h2>
        <div class="event-bars">
            @forelse($statusCounts as $status => $total)
                <div class="event-bar-row"><span>{{ ucfirst($status) }}</span><div class="event-bar-track"><span style="width:{{ round(((int)$total/$maxStatus)*100) }}%"></span></div><strong>{{ $total }}</strong></div>
            @empty<div class="empty">No event data yet.</div>@endforelse
        </div>
    </section>

    <section class="card">
        <h2 style="margin-top:0">Add Event</h2>
        <form method="post" action="{{ route('admin.events.store') }}" class="form-grid">@csrf
            <label>Event name<input name="title" required></label>
            <label>Start date/time<input name="starts_at" type="datetime-local" required></label>
            <label>Venue<input name="venue"></label>
            <label>Church unit ID<input name="organisation_unit_id" type="number"></label>
            <label>Fee<input name="fee" type="number" step="0.01" value="0"></label>
            <label>Currency<input name="currency" value="UGX" maxlength="3"></label>
            <label>Status<select name="status"><option value="draft">Draft</option><option value="published">Published</option><option value="cancelled">Cancelled</option><option value="completed">Completed</option></select></label>
            <div><button class="btn btn-primary"><i class="fas fa-plus"></i> Create Event</button></div>
        </form>
    </section>
</div>

<div class="card" style="margin-top:16px">
    <form method="get" class="filters">
        <input name="q" value="{{ $filters['q'] }}" placeholder="Search event, venue, category or speaker">
        <select name="status"><option value="">All statuses</option>@foreach(['draft','published','cancelled','completed'] as $status)<option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        <a class="btn btn-light" href="{{ route('admin.events.index') }}">Reset</a>
    </form>
</div>

<div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Event</th><th>Date</th><th>Venue</th><th>Church Unit</th><th>Status</th></tr></thead><tbody>
@forelse($events as $event)
<tr><td><strong>{{ $event->title }}</strong><small>{{ $event->category ?: 'Uncategorised' }}</small></td><td>{{ optional($event->starts_at)->format('d M Y H:i') }}</td><td>{{ $event->venue ?: '—' }}</td><td>{{ $event->organisationUnit?->name ?: 'All Church' }}</td><td><span class="badge {{ $event->status==='published' ? 'badge-success' : ($event->status==='cancelled' ? 'badge-danger' : 'badge-warning') }}">{{ ucfirst($event->status) }}</span></td></tr>
@empty<tr><td colspan="5" class="empty">No events match your filters.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $events->links() }}</div></div>

<style>
.event-stats{grid-template-columns:repeat(4,minmax(0,1fr))}.event-stat{border-left:5px solid var(--tone)}.event-stat .stat-icon{color:var(--tone);background:#f6f4fb}.event-layout{display:grid;grid-template-columns:1fr 1.2fr;gap:16px}.event-bars{display:grid;gap:14px}.event-bar-row{display:grid;grid-template-columns:90px 1fr 40px;gap:10px;align-items:center}.event-bar-row>span{font-size:13px;font-weight:700}.event-bar-track{height:12px;background:#f3f4f6;border-radius:99px;overflow:hidden}.event-bar-track span{display:block;height:100%;background:var(--primary);border-radius:99px}@media(max-width:900px){.event-layout{grid-template-columns:1fr}.event-stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.event-stats{grid-template-columns:1fr}}
</style>
@endsection
