@extends('admin.layout')
@section('title', 'AI Settings')
@section('content')
<div class="page-head">
    <div><h1><i class="fas fa-robot"></i> AI Settings</h1><p>Configure the central AI service and monitor usage across platform modules.</p></div>
    <form method="POST" action="{{ route('admin.ai.test') }}">@csrf<button class="btn btn-light" type="submit"><i class="fas fa-plug-circle-check"></i> Test Connection</button></form>
</div>

<div class="grid stats-grid">
    <div class="card stat-card stat-purple"><i class="fas fa-bolt stat-icon"></i><div><strong>{{ number_format($usage['today_requests']) }}</strong><span>Requests today</span></div></div>
    <div class="card stat-card stat-blue"><i class="fas fa-calendar-days stat-icon"></i><div><strong>{{ number_format($usage['month_requests']) }}</strong><span>This month</span></div></div>
    <div class="card stat-card stat-green"><i class="fas fa-circle-check stat-icon"></i><div><strong>{{ number_format($usage['successful']) }}</strong><span>Successful</span></div></div>
    <div class="card stat-card stat-red"><i class="fas fa-triangle-exclamation stat-icon"></i><div><strong>{{ number_format($usage['failed']) }}</strong><span>Failed</span></div></div>
</div>

<div class="card chart-card">
    <h3>AI requests — last 7 days</h3>
    <div class="mini-bars">@php $max=max(1,(int)$chart->max('total')); @endphp @foreach($chart as $point)<div class="mini-row"><span>{{ $point['label'] }}</span><div class="mini-track"><i style="width:{{ ($point['total']/$max)*100 }}%"></i></div><strong>{{ $point['total'] }}</strong></div>@endforeach</div>
</div>

<div class="card" style="margin:18px 0">
<form method="POST" action="{{ route('admin.ai.update') }}">@csrf @method('PUT')
<div class="form-grid">
<label>Provider<input name="provider" value="{{ old('provider', $setting->provider ?? 'openai') }}" placeholder="openai" required></label>
<label>Model<input name="model" value="{{ old('model', $setting->model ?? '') }}" placeholder="Model name" required></label>
<label class="span-2">API Key<input name="api_key" type="password" autocomplete="new-password" placeholder="{{ $setting ? 'Leave blank to keep current key' : 'Enter API key' }}"><small class="field-help">Stored encrypted and never displayed after saving.</small></label>
<label class="span-2">API Endpoint<input name="api_endpoint" type="url" value="{{ old('api_endpoint', $setting->api_endpoint ?? 'https://api.openai.com/v1') }}"></label>
<label>Temperature<input name="temperature" type="number" min="0" max="2" step="0.1" value="{{ old('temperature', $setting->temperature ?? 0.3) }}" required></label>
<label>Max Output Tokens<input name="max_tokens" type="number" min="64" max="100000" value="{{ old('max_tokens', $setting->max_tokens ?? 1200) }}" required></label>
<label>Daily Platform Limit<input name="daily_limit" type="number" min="0" value="{{ old('daily_limit', $setting->daily_limit ?? 0) }}"><small class="field-help">0 means unlimited.</small></label>
<label>Monthly Platform Limit<input name="monthly_limit" type="number" min="0" value="{{ old('monthly_limit', $setting->monthly_limit ?? 0) }}"></label>
<label>Per-user Daily Limit<input name="per_user_daily_limit" type="number" min="0" value="{{ old('per_user_daily_limit', $setting->per_user_daily_limit ?? 0) }}"></label>
<label>Timeout (seconds)<input name="timeout_seconds" type="number" min="5" max="180" value="{{ old('timeout_seconds', $setting->timeout_seconds ?? 30) }}" required></label>
<label>Retry Count<input name="retry_count" type="number" min="0" max="5" value="{{ old('retry_count', $setting->retry_count ?? 1) }}" required></label>
<label class="checkbox" style="align-self:end;padding-bottom:10px"><input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $setting->is_enabled ?? false))> Enable AI platform-wide</label>
<label class="span-2">System Prompt<textarea name="system_prompt" rows="5">{{ old('system_prompt', $setting->system_prompt ?? '') }}</textarea></label>
<label class="span-2">Safety Prompt<textarea name="safety_prompt" rows="5">{{ old('safety_prompt', $setting->safety_prompt ?? '') }}</textarea></label>
</div><div class="modal-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save AI Settings</button></div></form>
</div>

<div class="card table-card">
<div style="padding:17px"><h2 style="margin:0"><i class="fas fa-chart-line"></i> Recent AI Usage</h2><p style="color:var(--muted)">Token counts: {{ number_format($usage['input_tokens']) }} input / {{ number_format($usage['output_tokens']) }} output.</p></div>
<form class="filters" method="get" style="padding:0 17px 17px"><input name="q" value="{{ request('q') }}" placeholder="Search user, module, provider or model"><select name="status"><option value="">All statuses</option><option value="successful" @selected(request('status')==='successful')>Successful</option><option value="failed" @selected(request('status')==='failed')>Failed</option></select><select name="module"><option value="">All modules</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(request('module')===$module)>{{ ucwords(str_replace('_',' ',$module)) }}</option>@endforeach</select><button class="btn btn-primary">Filter</button><a class="btn btn-light" href="{{ route('admin.ai.index') }}">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Date</th><th>User</th><th>Module</th><th>Provider / Model</th><th>Tokens</th><th>Duration</th><th>Status</th></tr></thead><tbody>
@forelse($recentUsage as $log)<tr><td>{{ optional($log->created_at)->format('d M Y H:i') }}</td><td>{{ optional($log->user)->name ?: 'System/Guest' }}<small>{{ optional($log->user)->email }}</small></td><td>{{ ucfirst(str_replace('_',' ', $log->module)) }}</td><td>{{ $log->provider ?: '—' }}<small>{{ $log->model ?: '—' }}</small></td><td>{{ number_format((int)$log->input_tokens) }} / {{ number_format((int)$log->output_tokens) }}</td><td>{{ $log->duration_ms ? number_format($log->duration_ms).' ms' : '—' }}</td><td><span class="badge {{ $log->successful ? 'badge-success' : 'badge-danger' }}">{{ $log->successful ? 'Successful' : 'Failed' }}</span>@if(!$log->successful)<small>{{ $log->error_code ?: 'Provider error' }}</small>@endif</td></tr>@empty<tr><td class="empty" colspan="7">No AI usage found.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $recentUsage->links() }}</div></div>
@endsection

@push('styles')<style>
.stat-card{border-left:5px solid var(--primary)}.stat-purple{border-left-color:#7c3aed}.stat-blue{border-left-color:#2563eb}.stat-green{border-left-color:#16a34a}.stat-red{border-left-color:#dc2626}.chart-card h3{margin-top:0}.mini-bars{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:12px;align-items:end}.mini-row{display:grid;grid-template-rows:auto 85px auto;gap:7px;text-align:center}.mini-track{height:85px;background:#eef2f7;border-radius:9px;overflow:hidden;display:flex;align-items:flex-end}.mini-track i{display:block;width:100%;background:var(--primary);border-radius:9px 9px 0 0}.field-help{display:block;color:var(--muted);margin-top:5px}@media(max-width:800px){.mini-bars{grid-template-columns:repeat(4,1fr)}}
</style>@endpush
