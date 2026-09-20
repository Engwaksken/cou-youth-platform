@extends('admin.layout')
@section('title','Prayer & Pastoral Support')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-hands-praying"></i> Prayer & Pastoral Support</h1>
        <p>Review prayer requests, pastoral follow-up and safeguarding referrals.</p>
    </div>
</div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><div class="stat-icon"><i class="fas fa-inbox"></i></div><div><strong>{{ number_format($stats['total']) }}</strong><span>Total requests</span></div></div>
    <div class="card stat-card stat-blue"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div><div><strong>{{ number_format($stats['open']) }}</strong><span>Open cases</span></div></div>
    <div class="card stat-card stat-green"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div><strong>{{ number_format($stats['resolved']) }}</strong><span>Resolved / closed</span></div></div>
    <div class="card stat-card stat-red"><div class="stat-icon"><i class="fas fa-shield-heart"></i></div><div><strong>{{ number_format($stats['safeguarding']) }}</strong><span>Safeguarding review</span></div></div>
</div>

<div class="card" style="margin-bottom:16px">
    <h3 style="margin-top:0"><i class="fas fa-chart-bar"></i> Case status distribution</h3>
    @php $maxStatus=max(1,(int)collect($statusCounts)->max()); @endphp
    <div class="mini-chart">
        @foreach(['submitted'=>'Submitted','under_review'=>'Under review','referred'=>'Referred','resolved'=>'Resolved','closed'=>'Closed'] as $key=>$label)
            @php $count=(int)($statusCounts[$key]??0); $width=($count/$maxStatus)*100; @endphp
            <div class="chart-row"><span>{{ $label }}</span><div class="chart-track"><div class="chart-bar" style="width:{{ $width }}%"></div></div><strong>{{ $count }}</strong></div>
        @endforeach
    </div>
</div>

<form method="get" class="filters">
    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search subject, request or notes">
    <select name="status">
        <option value="">All statuses</option>
        @foreach(['submitted'=>'Submitted','under_review'=>'Under review','referred'=>'Referred','resolved'=>'Resolved','closed'=>'Closed'] as $value=>$label)<option value="{{ $value }}" @selected($filters['status']===$value)>{{ $label }}</option>@endforeach
    </select>
    <select name="safeguarding"><option value="">All cases</option><option value="1" @selected($filters['safeguarding'])>Safeguarding review only</option></select>
    <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Search</button>
    <a class="btn btn-light" href="{{ route('admin.prayer.index') }}">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Request</th><th>Visibility</th><th>Status</th><th>Safeguarding</th><th>Submitted</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><strong>{{ $item->subject }}</strong><small>{{ \Illuminate\Support\Str::limit($item->message,110) }}</small></td>
                    <td>{{ ucwords(str_replace('_',' ',$item->visibility)) }}</td>
                    <td><span class="badge {{ in_array($item->status,['resolved','closed'],true) ? 'badge-success' : ($item->status==='referred' ? 'badge-warning' : '') }}">{{ ucwords(str_replace('_',' ',$item->status)) }}</span></td>
                    <td>@if($item->requires_safeguarding_review)<span class="badge badge-danger"><i class="fas fa-shield-heart"></i> Review required</span>@else<span class="badge">No flag</span>@endif</td>
                    <td>{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                    <td><button class="btn btn-light" type="button" onclick="document.getElementById('case-{{ $item->id }}').classList.add('open')"><i class="fas fa-pen"></i> Review</button></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No prayer or pastoral requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $items->links() }}</div>
</div>

@foreach($items as $item)
<div class="modal" id="case-{{ $item->id }}">
    <div class="modal-card">
        <div class="modal-head"><div><h2>{{ $item->subject }}</h2><small>{{ optional($item->created_at)->format('d M Y H:i') }}</small></div><button class="icon-btn" type="button" onclick="document.getElementById('case-{{ $item->id }}').classList.remove('open')"><i class="fas fa-xmark"></i></button></div>
        <div class="card" style="margin-bottom:14px"><p style="white-space:pre-wrap;margin:0">{{ $item->message }}</p></div>
        @if($item->requires_safeguarding_review)<div class="alert alert-error"><i class="fas fa-shield-heart"></i> This request requires safeguarding review. Handle restricted notes carefully.</div>@endif
        <form method="post" action="{{ route('admin.prayer.update',$item) }}" class="form-grid">@csrf @method('PUT')
            <label>Status<select name="status" required>@foreach(['submitted'=>'Submitted','under_review'=>'Under review','referred'=>'Referred','resolved'=>'Resolved','closed'=>'Closed'] as $value=>$label)<option value="{{ $value }}" @selected($item->status===$value)>{{ $label }}</option>@endforeach</select></label>
            <label class="span-2">Restricted pastoral notes<textarea name="pastoral_notes" rows="7" placeholder="Pastoral follow-up notes">{{ $item->pastoral_notes }}</textarea></label>
            <div class="span-2 modal-actions"><button type="button" class="btn btn-light" onclick="document.getElementById('case-{{ $item->id }}').classList.remove('open')">Cancel</button><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save update</button></div>
        </form>
    </div>
</div>
@endforeach
@endsection

@push('styles')
<style>
    .stat-card{border-left:4px solid var(--primary)}.stat-purple{border-left-color:#7c3aed}.stat-blue{border-left-color:#2563eb}.stat-green{border-left-color:#16a34a}.stat-red{border-left-color:#dc2626}
    .mini-chart{display:grid;gap:12px}.chart-row{display:grid;grid-template-columns:120px 1fr 42px;gap:10px;align-items:center}.chart-track{height:12px;background:#eef2f7;border-radius:99px;overflow:hidden}.chart-bar{height:100%;background:var(--primary);border-radius:99px}.chart-row strong{text-align:right}
</style>
@endpush
