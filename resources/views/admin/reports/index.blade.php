@extends('admin.layout')
@section('title','Reports')
@section('content')
@php
    $tones = ['#4b2e83','#2563eb','#15803d','#b45309','#7c3aed','#0f766e','#dc2626'];
    $icons = ['fa-users','fa-user-shield','fa-calendar-days','fa-ticket','fa-people-group','fa-newspaper','fa-hand-holding-heart'];
    $maxSummary = max(1, (float) collect($summary)->max());
@endphp
<div class="page-head"><div><h1><i class="fas fa-chart-column"></i> Reporting & Monitoring</h1><p>Monitor youth participation, ministry activity, content and donations.</p></div></div>

<div class="card">
    <form method="get" class="filters" style="grid-template-columns:2fr auto auto">
        <select name="organisation_unit_id"><option value="">All Church units</option>@foreach($units as $u)<option value="{{ $u->id }}" @selected(request('organisation_unit_id')==$u->id)>{{ $u->name }}</option>@endforeach</select>
        <button class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filter</button>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-light">Reset</a>
    </form>
</div>

<div class="grid stats-grid report-stats" style="margin-top:16px">
@foreach($summary as $label=>$value)
    @php $index=$loop->index; $tone=$tones[$index % count($tones)]; @endphp
    <div class="card stat-card report-stat" style="--tone:{{ $tone }}"><div class="stat-icon"><i class="fas {{ $icons[$index % count($icons)] }}"></i></div><div><strong>{{ number_format((float)$value, str_contains($label,'donation') ? 0 : 0) }}</strong><span>{{ ucwords(str_replace('_',' ',$label)) }}</span></div></div>
@endforeach
</div>

<section class="card report-chart-card">
    <div class="chart-head"><div><h2>Platform activity overview</h2><p>Relative comparison of the current reporting totals.</p></div><i class="fas fa-chart-bar"></i></div>
    <div class="report-bars">
        @foreach($summary as $label=>$value)
            <div class="report-row"><span>{{ ucwords(str_replace('_',' ',$label)) }}</span><div class="report-track"><span style="width:{{ max(2,round(((float)$value/$maxSummary)*100)) }}%"></span></div><strong>{{ number_format((float)$value,0) }}</strong></div>
        @endforeach
    </div>
</section>

<style>
.report-stats{grid-template-columns:repeat(4,minmax(0,1fr))}.report-stat{border-left:5px solid var(--tone)}.report-stat .stat-icon{color:var(--tone);background:#f6f4fb}.report-chart-card{margin-top:16px}.chart-head{display:flex;justify-content:space-between;gap:12px}.chart-head h2{margin:0;font-size:18px}.chart-head p{margin:5px 0 0;color:var(--muted)}.chart-head>i{font-size:24px;color:var(--primary)}.report-bars{display:grid;gap:14px;margin-top:20px}.report-row{display:grid;grid-template-columns:190px 1fr 90px;gap:12px;align-items:center}.report-row>span{font-size:13px;font-weight:700}.report-track{height:12px;background:#f3f4f6;border-radius:99px;overflow:hidden}.report-track span{height:100%;display:block;background:var(--primary);border-radius:99px}.report-row strong{text-align:right}@media(max-width:1100px){.report-stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.report-stats{grid-template-columns:1fr}.report-row{grid-template-columns:110px 1fr 70px}}
</style>
@endsection
