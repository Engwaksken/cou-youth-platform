@extends('admin.layout')
@section('title','Notifications')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-bell"></i> Notifications</h1><p>Create and monitor youth platform notifications.</p></div></div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><div class="stat-icon"><i class="fas fa-bell"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Total notifications</span></div></div>
    <div class="card stat-card stat-green"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div><strong>{{ number_format($stats['active']) }}</strong><span>Active</span></div></div>
    <div class="card stat-card stat-orange"><div class="stat-icon"><i class="fas fa-clock"></i></div><div><strong>{{ number_format($stats['scheduled']) }}</strong><span>Scheduled</span></div></div>
    <div class="card stat-card stat-blue"><div class="stat-icon"><i class="fas fa-layer-group"></i></div><div><strong>{{ number_format($stats['all_channel']) }}</strong><span>All-channel messages</span></div></div>
</div>

<div class="card chart-card">
    <h3>Notifications by channel</h3>
    <div class="mini-bars">
        @php $max = max(1, (int) collect($chart)->max()); @endphp
        @forelse($chart as $label => $value)
            <div class="mini-row"><span>{{ ucwords(str_replace('_',' ',$label)) }}</span><div class="mini-track"><i style="width:{{ ($value/$max)*100 }}%"></i></div><strong>{{ $value }}</strong></div>
        @empty <div class="empty">No notification data yet.</div> @endforelse
    </div>
</div>

<div class="card" style="margin-top:16px">
    <h3>Queue notification</h3>
    <form class="form-grid" method="post" action="{{ route('admin.notifications.store') }}">@csrf
        <label>Title<input name="title" value="{{ old('title') }}" required></label>
        <label>Channel<select name="channel">@foreach(['in_app','push','email','all'] as $c)<option value="{{ $c }}">{{ ucwords(str_replace('_',' ',$c)) }}</option>@endforeach</select></label>
        <label class="span-2">Message<textarea name="body" rows="3" required>{{ old('body') }}</textarea></label>
        <label>Age category<select name="age_category">@foreach(['all','teen','youth','young_adult'] as $a)<option value="{{ $a }}">{{ ucwords(str_replace('_',' ',$a)) }}</option>@endforeach</select></label>
        <label>Target unit ID<input name="organisation_unit_id" value="{{ old('organisation_unit_id') }}" placeholder="Optional"></label>
        <label>Schedule<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"></label>
        <label>Action URL<input name="action_url" value="{{ old('action_url') }}" placeholder="Optional"></label>
        <div class="span-2"><button class="btn btn-primary"><i class="fas fa-paper-plane"></i> Queue Notification</button></div>
    </form>
</div>

<div class="card table-card">
    <form class="filters" method="get" style="padding:15px">
        <input name="q" value="{{ request('q') }}" placeholder="Search title or message">
        <select name="channel"><option value="">All channels</option>@foreach(['in_app','push','email','all'] as $c)<option value="{{ $c }}" @selected(request('channel')===$c)>{{ ucwords(str_replace('_',' ',$c)) }}</option>@endforeach</select>
        <select name="age_category"><option value="">All ages</option>@foreach(['teen','youth','young_adult','all'] as $a)<option value="{{ $a }}" @selected(request('age_category')===$a)>{{ ucwords(str_replace('_',' ',$a)) }}</option>@endforeach</select>
        <button class="btn btn-primary">Filter</button><a class="btn btn-light" href="{{ route('admin.notifications.index') }}">Reset</a>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Notification</th><th>Channel</th><th>Audience</th><th>Schedule</th><th>Status</th></tr></thead><tbody>
    @forelse($items as $n)<tr><td><strong>{{ $n->title }}</strong><small>{{ Str::limit($n->body,90) }}</small></td><td><span class="badge">{{ ucwords(str_replace('_',' ',$n->channel)) }}</span></td><td>{{ ucwords(str_replace('_',' ',$n->age_category)) }}</td><td>{{ $n->scheduled_at ? \Illuminate\Support\Carbon::parse($n->scheduled_at)->format('d M Y H:i') : 'Immediate' }}</td><td><span class="badge {{ $n->is_active ? 'badge-success' : '' }}">{{ $n->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
    @empty<tr><td colspan="5" class="empty">No notifications found.</td></tr>@endforelse
    </tbody></table></div><div class="pagination">{{ $items->links() }}</div>
</div>
@endsection

@push('styles')
<style>
.stat-card{border-left:5px solid var(--primary)}.stat-green{border-left-color:#16a34a}.stat-orange{border-left-color:#ea580c}.stat-blue{border-left-color:#2563eb}.stat-purple{border-left-color:#7c3aed}
.chart-card h3{margin-top:0}.mini-bars{display:grid;gap:12px}.mini-row{display:grid;grid-template-columns:120px 1fr 45px;gap:10px;align-items:center}.mini-track{height:10px;background:#eef2f7;border-radius:99px;overflow:hidden}.mini-track i{display:block;height:100%;background:var(--primary);border-radius:99px}
</style>
@endpush
