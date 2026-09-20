@extends('admin.layout')
@section('title','Moderation')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-shield-halved"></i> Moderation & Safety Reports</h1><p>Review community reports and track resolutions.</p></div></div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><div class="stat-icon"><i class="fas fa-flag"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Total reports</span></div></div>
    <div class="card stat-card stat-red"><div class="stat-icon"><i class="fas fa-circle-exclamation"></i></div><div><strong>{{ number_format($stats['open']) }}</strong><span>Open</span></div></div>
    <div class="card stat-card stat-orange"><div class="stat-icon"><i class="fas fa-magnifying-glass"></i></div><div><strong>{{ number_format($stats['reviewing']) }}</strong><span>Reviewing</span></div></div>
    <div class="card stat-card stat-green"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div><strong>{{ number_format($stats['resolved']) }}</strong><span>Resolved / dismissed</span></div></div>
</div>

<div class="card chart-card"><h3>Reports by status</h3><div class="mini-bars">@php $max=max(1,(int)collect($chart)->max()); @endphp @forelse($chart as $label=>$value)<div class="mini-row"><span>{{ ucfirst($label) }}</span><div class="mini-track"><i style="width:{{ ($value/$max)*100 }}%"></i></div><strong>{{ $value }}</strong></div>@empty<div class="empty">No reports yet.</div>@endforelse</div></div>

<div class="card table-card">
<form class="filters" method="get" style="padding:15px"><input name="q" value="{{ request('q') }}" placeholder="Search reason, details or reporter"><select name="status"><option value="">All statuses</option>@foreach(['open','reviewing','resolved','dismissed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="btn btn-primary">Filter</button><a class="btn btn-light" href="{{ route('admin.moderation.index') }}">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Report</th><th>Reporter</th><th>Status</th><th>Submitted</th><th>Resolution</th></tr></thead><tbody>
@forelse($reports as $report)<tr><td><strong>{{ $report->reason }}</strong><small>{{ Str::limit($report->details,100) }}</small></td><td>{{ optional($report->reporter)->name ?? 'User' }}</td><td><span class="badge {{ $report->status==='resolved' ? 'badge-success' : ($report->status==='open' ? 'badge-danger' : 'badge-warning') }}">{{ ucfirst($report->status) }}</span></td><td>{{ $report->created_at->format('d M Y H:i') }}</td><td><form method="POST" action="{{ route('admin.moderation.resolve',$report) }}" class="resolution-form">@csrf @method('PUT')<select name="status"><option value="reviewing" @selected($report->status==='reviewing')>Reviewing</option><option value="resolved" @selected($report->status==='resolved')>Resolved</option><option value="dismissed" @selected($report->status==='dismissed')>Dismissed</option></select><input name="resolution_notes" value="{{ $report->resolution_notes }}" placeholder="Resolution notes"><button class="btn btn-primary">Update</button></form></td></tr>@empty<tr><td colspan="5" class="empty">No moderation reports found.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $reports->links() }}</div></div>
@endsection

@push('styles')<style>
.stat-card{border-left:5px solid var(--primary)}.stat-purple{border-left-color:#7c3aed}.stat-red{border-left-color:#dc2626}.stat-orange{border-left-color:#ea580c}.stat-green{border-left-color:#16a34a}.chart-card{margin-bottom:16px}.chart-card h3{margin-top:0}.mini-bars{display:grid;gap:12px}.mini-row{display:grid;grid-template-columns:100px 1fr 45px;gap:10px;align-items:center}.mini-track{height:10px;background:#eef2f7;border-radius:99px;overflow:hidden}.mini-track i{display:block;height:100%;background:var(--primary);border-radius:99px}.resolution-form{display:grid;grid-template-columns:130px minmax(180px,1fr) auto;gap:7px}.resolution-form select,.resolution-form input{border:1px solid #d1d5db;border-radius:8px;padding:8px}
</style>@endpush
