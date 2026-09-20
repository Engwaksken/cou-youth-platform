@extends('admin.layout')
@section('title', 'System Health')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-heart-pulse"></i> System Health</h1><p>Release {{ $release['version'] ?? '—' }} · Build {{ $release['build'] ?? '—' }}</p></div></div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><div class="stat-icon"><i class="fas fa-list-check"></i></div><div><strong>{{ $stats['total'] }}</strong><span>Total checks</span></div></div>
    <div class="card stat-card stat-green"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div><strong>{{ $stats['healthy'] }}</strong><span>Healthy</span></div></div>
    <div class="card stat-card stat-red"><div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div><div><strong>{{ $stats['attention'] }}</strong><span>Need attention</span></div></div>
    <div class="card stat-card stat-blue"><div class="stat-icon"><i class="fas fa-gauge-high"></i></div><div><strong>{{ $stats['health_percent'] }}%</strong><span>Health score</span></div></div>
</div>

<div class="card health-chart">
    <div class="health-chart-head"><div><h3>Platform readiness</h3><p>Current result across database, environment, queue, storage and schema checks.</p></div><strong>{{ $stats['health_percent'] }}%</strong></div>
    <div class="health-track"><span style="width:{{ $stats['health_percent'] }}%"></span></div>
</div>

<div class="health-grid">
@foreach($checks as $name => $check)
    <div class="card health-card {{ $check['ok'] ? 'health-ok' : 'health-bad' }}">
        <div class="health-icon"><i class="fas {{ $check['ok'] ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i></div>
        <div><div class="health-title"><strong>{{ ucwords(str_replace('_',' ', $name)) }}</strong><span class="badge {{ $check['ok'] ? 'badge-success' : 'badge-danger' }}">{{ $check['ok'] ? 'Healthy' : 'Action needed' }}</span></div><p>{{ $check['message'] }}</p></div>
    </div>
@endforeach
</div>
@endsection

@push('styles')<style>
.stat-card{border-left:5px solid var(--primary)}.stat-purple{border-left-color:#7c3aed}.stat-green{border-left-color:#16a34a}.stat-red{border-left-color:#dc2626}.stat-blue{border-left-color:#2563eb}.health-chart{margin-bottom:18px}.health-chart-head{display:flex;justify-content:space-between;align-items:center;gap:16px}.health-chart-head h3{margin:0}.health-chart-head p{margin:5px 0 0;color:var(--muted)}.health-chart-head>strong{font-size:30px;color:var(--primary)}.health-track{height:14px;background:#eef2f7;border-radius:99px;overflow:hidden;margin-top:15px}.health-track span{display:block;height:100%;background:var(--primary);border-radius:99px}.health-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.health-card{display:flex;gap:13px;border-left:5px solid #16a34a}.health-card.health-bad{border-left-color:#dc2626}.health-icon{font-size:22px;color:#16a34a;padding-top:2px}.health-bad .health-icon{color:#dc2626}.health-title{display:flex;gap:10px;align-items:center;justify-content:space-between}.health-card p{margin:8px 0 0;color:var(--muted);line-height:1.45}@media(max-width:1000px){.health-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.health-grid{grid-template-columns:1fr}}
</style>@endpush
