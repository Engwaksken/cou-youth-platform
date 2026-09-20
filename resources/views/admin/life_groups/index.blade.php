@extends('admin.layout')
@section('title','Life Groups')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-people-group"></i> Life Groups</h1><p>Manage active groups, membership capacity and meeting details.</p></div></div>

<div class="grid stats-grid group-stats">
    @foreach([
        ['Total groups',$stats['total'],'fa-people-group','#4b2e83'],
        ['Active',$stats['active'],'fa-circle-check','#15803d'],
        ['Inactive',$stats['inactive'],'fa-circle-pause','#b45309'],
        ['Members',$stats['members'],'fa-users','#2563eb'],
    ] as [$label,$value,$icon,$tone])
        <div class="card stat-card group-stat" style="--tone:{{ $tone }}"><div class="stat-icon"><i class="fas {{ $icon }}"></i></div><div><strong>{{ number_format((int)$value) }}</strong><span>{{ $label }}</span></div></div>
    @endforeach
</div>

<div class="card">
    <form method="get" class="filters">
        <input name="q" value="{{ $filters['q'] }}" placeholder="Search group, meeting day or location">
        <select name="status"><option value="">All statuses</option><option value="active" @selected($filters['status']==='active')>Active</option><option value="inactive" @selected($filters['status']==='inactive')>Inactive</option></select>
        <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        <a class="btn btn-light" href="{{ route('admin.life-groups.index') }}">Reset</a>
    </form>
</div>

<div class="group-layout">
    <section class="card">
        <h2 style="margin-top:0">Create Life Group</h2>
        <form method="post" action="{{ route('admin.life-groups.store') }}" class="form-grid">@csrf
            <label>Group name<input name="name" required></label>
            <label>Church unit ID<input name="organisation_unit_id" type="number"></label>
            <label>Leader user ID<input name="leader_user_id" type="number"></label>
            <label>Member limit<input name="member_limit" type="number" value="12" min="2" max="12"></label>
            <label>Meeting day<input name="meeting_day" placeholder="e.g. Saturday"></label>
            <label>Meeting time<input name="meeting_time" type="time"></label>
            <label class="span-2">Meeting location<input name="meeting_location"></label>
            <div class="span-2"><button class="btn btn-primary"><i class="fas fa-plus"></i> Create Life Group</button></div>
        </form>
    </section>

    <section class="card">
        <h2 style="margin-top:0">Capacity overview</h2>
        <div class="capacity-list">
            @forelse($groups->take(6) as $group)
                @php $pct = min(100, $group->member_limit > 0 ? round(($group->members_count/$group->member_limit)*100) : 0); @endphp
                <div class="capacity-row"><div><strong>{{ $group->name }}</strong><small>{{ $group->members_count }}/{{ $group->member_limit }} members</small></div><div class="capacity-track"><span style="width:{{ $pct }}%"></span></div></div>
            @empty<div class="empty">No groups to display.</div>@endforelse
        </div>
    </section>
</div>

<div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Life Group</th><th>Members</th><th>Meeting</th><th>Church Unit</th><th>Status</th></tr></thead><tbody>
@forelse($groups as $group)
<tr><td><strong>{{ $group->name }}</strong><small>{{ $group->description ?: 'No description' }}</small></td><td>{{ $group->members_count }}/{{ $group->member_limit }}</td><td>{{ $group->meeting_day ?: '—' }} {{ $group->meeting_time ? ' · '.$group->meeting_time : '' }}<small>{{ $group->meeting_location ?: '' }}</small></td><td>{{ $group->organisationUnit?->name ?: 'All Church' }}</td><td><span class="badge {{ $group->is_active ? 'badge-success' : 'badge-warning' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
@empty<tr><td colspan="5" class="empty">No life groups match your filters.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $groups->links() }}</div></div>

<style>
.group-stats{grid-template-columns:repeat(4,minmax(0,1fr))}.group-stat{border-left:5px solid var(--tone)}.group-stat .stat-icon{color:var(--tone);background:#f6f4fb}.group-layout{display:grid;grid-template-columns:1.2fr 1fr;gap:16px;margin-top:16px}.capacity-list{display:grid;gap:14px}.capacity-row{display:grid;gap:7px}.capacity-row>div:first-child{display:flex;justify-content:space-between;gap:12px}.capacity-row small{color:var(--muted)}.capacity-track{height:10px;background:#f3f4f6;border-radius:99px;overflow:hidden}.capacity-track span{display:block;height:100%;background:var(--primary);border-radius:99px}@media(max-width:900px){.group-layout{grid-template-columns:1fr}.group-stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.group-stats{grid-template-columns:1fr}}
</style>
@endsection
