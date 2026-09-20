@extends('admin.layout')
@section('title','Church Structure')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-sitemap"></i> Church Structure</h1><p>Manage the organisational hierarchy used across the youth platform.</p></div></div>

<div class="grid stats-grid org-stats">
    @foreach([
        ['Total units',$stats['total'],'fa-sitemap','#4b2e83'],
        ['Active units',$stats['active'],'fa-circle-check','#15803d'],
        ['Dioceses',$stats['dioceses'],'fa-landmark','#2563eb'],
        ['Local churches',$stats['local_churches'],'fa-church','#b45309'],
    ] as [$label,$value,$icon,$tone])
        <div class="card stat-card org-stat" style="--tone:{{ $tone }}"><div class="stat-icon"><i class="fas {{ $icon }}"></i></div><div><strong>{{ number_format((int)$value) }}</strong><span>{{ $label }}</span></div></div>
    @endforeach
</div>

<div class="card">
    <form method="get" class="filters">
        <input name="q" value="{{ $filters['q'] }}" placeholder="Search name, code, email or phone">
        <select name="type"><option value="">All types</option>@foreach(['province','diocese','archdeaconry','parish','local_church','chaplaincy','institution'] as $type)<option value="{{ $type }}" @selected($filters['type']===$type)>{{ ucwords(str_replace('_',' ',$type)) }}</option>@endforeach</select>
        <select name="status"><option value="">All statuses</option><option value="active" @selected($filters['status']==='active')>Active</option><option value="inactive" @selected($filters['status']==='inactive')>Inactive</option></select>
        <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        <a class="btn btn-light" href="{{ route('admin.organisation-units.index') }}">Reset</a>
    </form>
</div>

<div class="card" style="margin-top:16px">
    <h2 style="margin-top:0">Add Church Unit</h2>
    <form method="post" action="{{ route('admin.organisation-units.store') }}" class="form-grid">@csrf
        <label>Name<input name="name" required></label>
        <label>Type<select name="type" required>@foreach(['province','diocese','archdeaconry','parish','local_church','chaplaincy','institution'] as $type)<option value="{{ $type }}">{{ ucwords(str_replace('_',' ',$type)) }}</option>@endforeach</select></label>
        <label>Parent ID<input name="parent_id" type="number"></label>
        <label>Code<input name="code"></label>
        <label>Email<input name="email" type="email"></label>
        <label>Phone<input name="phone"></label>
        <div class="span-2"><button class="btn btn-primary"><i class="fas fa-plus"></i> Add Unit</button></div>
    </form>
</div>

<div class="card table-card"><div class="table-wrap"><table><thead><tr><th>Name</th><th>Type</th><th>Parent</th><th>Code</th><th>Status</th></tr></thead><tbody>
@forelse($units as $unit)
<tr><td><strong>{{ $unit->name }}</strong><small>{{ $unit->email ?: '' }} {{ $unit->phone ? ' · '.$unit->phone : '' }}</small></td><td>{{ ucwords(str_replace('_',' ',$unit->type)) }}</td><td>{{ $unit->parent?->name ?: '—' }}</td><td>{{ $unit->code ?: '—' }}</td><td><span class="badge {{ $unit->is_active ? 'badge-success' : 'badge-warning' }}">{{ $unit->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
@empty<tr><td colspan="5" class="empty">No church units match your filters.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $units->links() }}</div></div>

<style>
.org-stats{grid-template-columns:repeat(4,minmax(0,1fr))}.org-stat{border-left:5px solid var(--tone)}.org-stat .stat-icon{color:var(--tone);background:#f6f4fb}@media(max-width:900px){.org-stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.org-stats{grid-template-columns:1fr}}
</style>
@endsection
