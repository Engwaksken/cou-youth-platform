@extends('admin.layout')
@section('title','Guardian Consents')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-shield-heart"></i> Guardian Consents</h1><p>Review and manage safeguarding consent submissions.</p></div></div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><div class="stat-icon"><i class="fas fa-file-signature"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Total consents</span></div></div>
    <div class="card stat-card stat-orange"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div><div><strong>{{ number_format($stats['pending']) }}</strong><span>Pending</span></div></div>
    <div class="card stat-card stat-green"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div><strong>{{ number_format($stats['approved']) }}</strong><span>Approved</span></div></div>
    <div class="card stat-card stat-red"><div class="stat-icon"><i class="fas fa-circle-xmark"></i></div><div><strong>{{ number_format($stats['rejected']) }}</strong><span>Rejected</span></div></div>
</div>

<div class="card chart-card">
    <h3>Consent status distribution</h3>
    <div class="mini-bars">
        @php $max = max(1, (int) collect($chart)->max()); @endphp
        @forelse($chart as $label => $value)
            <div class="mini-row"><span>{{ ucfirst($label) }}</span><div class="mini-track"><i style="width:{{ ($value/$max)*100 }}%"></i></div><strong>{{ $value }}</strong></div>
        @empty <div class="empty">No consent data yet.</div> @endforelse
    </div>
</div>

<div class="card table-card">
    <form class="filters" method="get" style="padding:15px">
        <input name="q" value="{{ request('q') }}" placeholder="Search guardian, youth, email or phone">
        <select name="status"><option value="">All statuses</option>@foreach(['pending','approved','rejected'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <button class="btn btn-primary">Filter</button><a class="btn btn-light" href="{{ route('admin.guardian-consents.index') }}">Reset</a>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Youth</th><th>Guardian</th><th>Contact</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead><tbody>
    @forelse($consents as $c)
        <tr>
            <td><strong>{{ $c->user?->name ?? 'User' }}</strong><small>{{ $c->user?->email }}</small></td>
            <td>{{ $c->guardian_name }}</td>
            <td>{{ $c->guardian_phone ?? '—' }}<small>{{ $c->guardian_email }}</small></td>
            <td><span class="badge {{ $c->status==='approved' ? 'badge-success' : ($c->status==='rejected' ? 'badge-danger' : 'badge-warning') }}">{{ ucfirst($c->status) }}</span></td>
            <td>{{ optional($c->created_at)->format('d M Y H:i') }}</td>
            <td>@if($c->status==='pending')<div style="display:flex;gap:7px"><form method="post" action="{{ route('admin.guardian-consents.approve',$c) }}">@csrf<button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Approve</button></form><form method="post" action="{{ route('admin.guardian-consents.reject',$c) }}">@csrf<button class="btn btn-danger" type="submit"><i class="fas fa-xmark"></i> Reject</button></form></div>@else — @endif</td>
        </tr>
    @empty<tr><td colspan="6" class="empty">No guardian consents found.</td></tr>@endforelse
    </tbody></table></div><div class="pagination">{{ $consents->links() }}</div>
</div>
@endsection

@push('styles')
<style>
.stat-card{border-left:5px solid var(--primary)}.stat-purple{border-left-color:#7c3aed}.stat-orange{border-left-color:#ea580c}.stat-green{border-left-color:#16a34a}.stat-red{border-left-color:#dc2626}.chart-card{margin-bottom:16px}.chart-card h3{margin-top:0}.mini-bars{display:grid;gap:12px}.mini-row{display:grid;grid-template-columns:110px 1fr 45px;gap:10px;align-items:center}.mini-track{height:10px;background:#eef2f7;border-radius:99px;overflow:hidden}.mini-track i{display:block;height:100%;background:var(--primary);border-radius:99px}
</style>
@endpush
