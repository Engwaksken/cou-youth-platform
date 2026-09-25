@extends('admin.layout')
@section('title','Events')
@section('content')
@php $maxStatus=max(1,(int)collect($statusCounts)->max()); @endphp

<div class="page-head">
    <div>
        <h1><i class="fas fa-calendar-days"></i> Events</h1>
        <p>Manage youth events, registration and attendance.</p>
    </div>
    <button class="btn btn-primary" data-open-modal="event-create"><i class="fas fa-plus"></i> Add Event</button>
</div>

<div data-tabs>
    <div class="tabs">
        <button class="tab active" data-tab-target="events-overview"><i class="fas fa-chart-column"></i> Overview</button>
        <button class="tab" data-tab-target="events-list"><i class="fas fa-list"></i> Events <span class="tab-count">{{ $events->total() }}</span></button>
    </div>

    <section id="events-overview" class="tab-panel active">
        <div class="grid stats-grid event-stats">
            @foreach([['Total events',$stats['total'],'fa-calendar','#4b2e83'],['Published',$stats['published'],'fa-circle-check','#15803d'],['Upcoming',$stats['upcoming'],'fa-clock','#2563eb'],['Completed',$stats['completed'],'fa-flag-checkered','#b45309']] as [$label,$value,$icon,$tone])
                <div class="card stat-card event-stat" style="--tone:{{ $tone }}">
                    <div class="stat-icon"><i class="fas {{ $icon }}"></i></div>
                    <div><strong>{{ number_format((int)$value) }}</strong><span>{{ $label }}</span></div>
                </div>
            @endforeach
        </div>
        <div class="card">
            <h2 style="margin-top:0">Events by status</h2>
            <div class="event-bars">
                @forelse($statusCounts as $status=>$total)
                    <div class="event-bar-row"><span>{{ ucfirst($status) }}</span><div class="event-bar-track"><span style="width:{{ round(((int)$total/$maxStatus)*100) }}%"></span></div><strong>{{ $total }}</strong></div>
                @empty
                    <div class="empty">No event data yet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="events-list" class="tab-panel">
        <div class="card">
            <form method="get" class="filters">
                <input name="q" value="{{ $filters['q'] }}" placeholder="Search event, venue, category or speaker">
                <select name="status"><option value="">All statuses</option>@foreach(['draft','published','cancelled','completed'] as $status)<option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
                <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <a class="btn btn-light" href="{{ route('admin.events.index') }}#events-list">Reset</a>
            </form>
        </div>

        <div class="card table-card">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Event</th><th>Date</th><th>Venue</th><th>Attendance</th><th>Church Unit</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td><strong>{{ $event->title }}</strong><small>{{ $event->category ?: 'Uncategorised' }}</small></td>
                            <td>{{ optional($event->starts_at)->format('d M Y H:i') }}</td>
                            <td>{{ $event->venue ?: '—' }}</td>
                            <td>
                                <strong>{{ (int)$event->attended_count }}/{{ (int)$event->registrations_count }}</strong>
                                <small>{{ (int)$event->absent_count }} absent</small>
                            </td>
                            <td>{{ $event->organisationUnit?->name ?: 'All Church' }}</td>
                            <td><span class="badge {{ $event->status==='published'?'badge-success':($event->status==='cancelled'?'badge-danger':'badge-warning') }}">{{ ucfirst($event->status) }}</span></td>
                            <td><div class="actions"><button class="icon-btn" title="View event, QR and attendance" data-open-modal="event-view-{{ $event->id }}"><i class="fas fa-eye"></i></button><button class="icon-btn" title="Edit" data-open-modal="event-edit-{{ $event->id }}"><i class="fas fa-pen"></i></button><button class="icon-btn" title="Delete" data-open-modal="event-delete-{{ $event->id }}"><i class="fas fa-trash"></i></button></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">No events match your filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $events->withQueryString()->links() }}</div>
        </div>
    </section>
</div>

<div id="event-create" class="modal"><div class="modal-card"><div class="modal-head"><h2><i class="fas fa-plus"></i> Add Event</h2><button class="icon-btn" data-close-modal><i class="fas fa-xmark"></i></button></div><form method="post" action="{{ route('admin.events.store') }}">@csrf<div class="form-grid"><label>Event name<input name="title" required placeholder="e.g. Youth Conference 2026"></label><label>Category<input name="category" placeholder="e.g. Conference"></label><label>Start date/time<input name="starts_at" type="datetime-local" required></label><label>End date/time<input name="ends_at" type="datetime-local"></label><label>Venue<input name="venue" placeholder="e.g. Main Hall"></label><label>Church unit ID<input name="organisation_unit_id" type="number" placeholder="e.g. 1"></label><label>Theme<input name="theme" placeholder="e.g. Rooted in Faith"></label><label>Speaker<input name="speaker" placeholder="e.g. John Doe"></label><label>Organizer<input name="organizer" placeholder="e.g. Youth Ministry"></label><label>Capacity<input name="capacity" type="number" min="1" placeholder="e.g. 200"></label><label>Fee<input name="fee" type="number" step="0.01" value="0" placeholder="e.g. 10000"></label><label>Currency<input name="currency" value="UGX" maxlength="3" required placeholder="UGX"></label><label>Status<select name="status"><option value="draft">Draft</option><option value="published">Published</option><option value="cancelled">Cancelled</option><option value="completed">Completed</option></select></label><label>External registration URL<input name="external_registration_url" placeholder="https://..."></label><label><input type="checkbox" name="allow_external_registration" value="1"> Allow external registration</label><label class="span-2">Description<textarea name="description" rows="4" placeholder="Event details..."></textarea></label></div><div class="modal-actions"><button type="button" class="btn btn-light" data-close-modal>Cancel</button><button class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Event</button></div></form></div></div>

@foreach($events as $event)
@php $vUrl = $event->qr_token ? route('public.event.register', $event->qr_token) : null; @endphp
<div id="event-view-{{ $event->id }}" class="modal">
    <div class="modal-card large">
        <div class="modal-head"><div><h2>{{ $event->title }}</h2><p>Event details, registration QR and attendance.</p></div><button class="icon-btn" data-close-modal><i class="fas fa-xmark"></i></button></div>

        <div class="attendance-summary">
            <div><i class="fas fa-users"></i><strong>{{ (int)$event->registrations_count }}</strong><span>Registered</span></div>
            <div><i class="fas fa-user-check"></i><strong>{{ (int)$event->attended_count }}</strong><span>Attended</span></div>
            <div><i class="fas fa-user-xmark"></i><strong>{{ (int)$event->absent_count }}</strong><span>Absent</span></div>
            <div><i class="fas fa-hourglass-half"></i><strong>{{ max(0,(int)$event->registrations_count-(int)$event->attended_count-(int)$event->absent_count) }}</strong><span>Unmarked</span></div>
        </div>

        <div class="detail-grid">
            <div class="detail-item"><span>Status</span><strong>{{ ucfirst($event->status) }}</strong></div><div class="detail-item"><span>Category</span><strong>{{ $event->category ?: '—' }}</strong></div><div class="detail-item"><span>Starts</span><strong>{{ optional($event->starts_at)->format('d M Y H:i') ?: '—' }}</strong></div><div class="detail-item"><span>Ends</span><strong>{{ optional($event->ends_at)->format('d M Y H:i') ?: '—' }}</strong></div><div class="detail-item"><span>Venue</span><strong>{{ $event->venue ?: '—' }}</strong></div><div class="detail-item"><span>Church Unit</span><strong>{{ $event->organisationUnit?->name ?: 'All Church' }}</strong></div><div class="detail-item"><span>Speaker</span><strong>{{ $event->speaker ?: '—' }}</strong></div><div class="detail-item"><span>Organizer</span><strong>{{ $event->organizer ?: '—' }}</strong></div><div class="detail-item"><span>Fee</span><strong>{{ $event->currency }} {{ number_format((float)$event->fee,2) }}</strong></div><div class="detail-item"><span>Capacity</span><strong>{{ $event->capacity ?: '—' }}</strong></div><div class="detail-item span-2"><span>Description</span><strong>{{ $event->description ?: 'No description.' }}</strong></div>
        </div>

        @if($vUrl)
            <div class="event-qr-card">
                <div><span>Registration QR</span><strong style="word-break:break-all">{{ $vUrl }}</strong><button type="button" class="btn btn-light" onclick="navigator.clipboard.writeText('{{ $vUrl }}')"><i class="fas fa-copy"></i> Copy link</button></div>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($vUrl) }}" width="140" height="140" alt="Registration QR for {{ $event->title }}">
            </div>
        @endif

        <h3 class="attendance-title"><i class="fas fa-clipboard-check"></i> Attendance</h3>
        <div class="table-wrap attendance-table"><table><thead><tr><th>Participant</th><th>Contact</th><th>Registration</th><th>Attendance</th><th>Update</th></tr></thead><tbody>
            @forelse($event->registrations as $registration)
                <tr>
                    <td><strong>{{ $registration->name ?: $registration->user?->name ?: 'Youth member' }}</strong></td>
                    <td>{{ $registration->phone ?: $registration->email ?: '—' }}</td>
                    <td>{{ ucfirst($registration->status ?: 'registered') }}</td>
                    <td><span class="badge {{ $registration->attendance_status==='attended'?'badge-success':($registration->attendance_status==='absent'?'badge-danger':'badge-warning') }}">{{ ucfirst($registration->attendance_status ?: 'registered') }}</span>@if($registration->attended_at)<small>{{ $registration->attended_at->format('d M Y H:i') }}</small>@endif</td>
                    <td><form method="post" action="{{ route('admin.events.attendance.update',[$event,$registration]) }}" class="attendance-form">@csrf @method('PUT')<select name="attendance_status"><option value="registered" @selected(($registration->attendance_status?:'registered')==='registered')>Unmarked</option><option value="attended" @selected($registration->attendance_status==='attended')>Attended</option><option value="absent" @selected($registration->attendance_status==='absent')>Absent</option></select><button class="btn btn-primary" title="Save attendance"><i class="fas fa-check"></i></button></form></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No registrations yet.</td></tr>
            @endforelse
        </tbody></table></div>

        <div class="modal-actions"><button class="btn btn-light" data-close-modal>Close</button><button class="btn btn-primary" data-close-modal data-open-modal="event-edit-{{ $event->id }}"><i class="fas fa-pen"></i> Edit</button></div>
    </div>
</div>

<div id="event-edit-{{ $event->id }}" class="modal"><div class="modal-card"><div class="modal-head"><h2>Edit Event</h2><button class="icon-btn" data-close-modal><i class="fas fa-xmark"></i></button></div><form method="post" action="{{ route('admin.events.update',$event) }}">@csrf @method('PUT')<div class="form-grid"><label>Event name<input name="title" value="{{ $event->title }}" required></label><label>Category<input name="category" value="{{ $event->category }}"></label><label>Start date/time<input name="starts_at" type="datetime-local" value="{{ optional($event->starts_at)->format('Y-m-d\TH:i') }}" required></label><label>End date/time<input name="ends_at" type="datetime-local" value="{{ optional($event->ends_at)->format('Y-m-d\TH:i') }}"></label><label>Venue<input name="venue" value="{{ $event->venue }}"></label><label>Church unit ID<input name="organisation_unit_id" type="number" value="{{ $event->organisation_unit_id }}"></label><label>Theme<input name="theme" value="{{ $event->theme }}"></label><label>Speaker<input name="speaker" value="{{ $event->speaker }}"></label><label>Organizer<input name="organizer" value="{{ $event->organizer }}"></label><label>Capacity<input name="capacity" type="number" min="1" value="{{ $event->capacity }}"></label><label>Fee<input name="fee" type="number" step="0.01" value="{{ $event->fee }}"></label><label>Currency<input name="currency" value="{{ $event->currency }}" maxlength="3" required></label><label>Status<select name="status">@foreach(['draft','published','cancelled','completed'] as $status)<option value="{{ $status }}" @selected($event->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select></label><label>External registration URL<input name="external_registration_url" value="{{ $event->external_registration_url }}" placeholder="https://..."></label><label><input type="checkbox" name="allow_external_registration" value="1" @checked($event->allow_external_registration)> Allow external registration</label><label class="span-2">Description<textarea name="description" rows="4" placeholder="Event details...">{{ $event->description }}</textarea></label></div><div class="modal-actions"><button type="button" class="btn btn-light" data-close-modal>Cancel</button><button class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Update Event</button></div></form></div></div>

<div id="event-delete-{{ $event->id }}" class="modal"><div class="modal-card small"><div class="modal-head"><h2>Delete Event?</h2><button class="icon-btn" data-close-modal><i class="fas fa-xmark"></i></button></div><p class="danger-copy">Delete <strong>{{ $event->title }}</strong>? Youth will be notified if this published event is removed.</p><form method="post" action="{{ route('admin.events.destroy',$event) }}">@csrf @method('DELETE')<div class="modal-actions"><button type="button" class="btn btn-light" data-close-modal>Cancel</button><button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button></div></form></div></div>
@endforeach

<style>
.event-stats{grid-template-columns:repeat(4,minmax(0,1fr))}.event-stat{border-left:5px solid var(--tone)}.event-stat .stat-icon{color:var(--tone);background:#f6f4fb}.event-bars{display:grid;gap:12px}.event-bar-row{display:grid;grid-template-columns:90px 1fr 40px;gap:10px;align-items:center}.event-bar-row>span{font-size:13px;font-weight:700}.event-bar-track{height:12px;background:#f3f4f6;border-radius:99px;overflow:hidden}.event-bar-track span{display:block;height:100%;background:var(--primary);border-radius:99px}.attendance-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px}.attendance-summary>div{display:grid;grid-template-columns:36px 1fr;grid-template-rows:auto auto;gap:0 10px;align-items:center;padding:12px;border:1px solid #e5e7eb;border-radius:10px}.attendance-summary i{grid-row:1/3;width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#f2edfa;color:var(--primary)}.attendance-summary strong{font-size:20px;line-height:1}.attendance-summary span{font-size:12px;color:var(--muted)}.event-qr-card{display:flex;justify-content:space-between;align-items:center;gap:18px;margin:18px 0;padding:16px;border:1px solid #e5e7eb;border-radius:10px;background:#faf9fc}.event-qr-card>div{display:grid;gap:8px}.event-qr-card span{font-size:12px;color:var(--muted);font-weight:700}.event-qr-card img{border-radius:10px}.attendance-title{margin:22px 0 10px}.attendance-form{display:flex;gap:7px;align-items:center}.attendance-form select{min-width:110px;border:1px solid #d1d5db;border-radius:8px;padding:8px;background:#fff}.attendance-form .btn{min-height:38px;padding:8px 10px}.attendance-table small{display:block;color:var(--muted);margin-top:3px}@media(max-width:900px){.event-stats,.attendance-summary{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.event-stats,.attendance-summary{grid-template-columns:1fr}.event-qr-card{flex-direction:column;align-items:flex-start}.attendance-form{min-width:180px}}
</style>
@endsection
