@extends('admin.layout')

@section('title', 'AI Settings')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-robot"></i> AI Settings</h1>
        <p>Configure the central AI service used by chatbot, recommendations, drafting and other platform modules.</p>
    </div>
    <form method="POST" action="{{ route('admin.ai.test') }}">
        @csrf
        <button class="btn btn-light" type="submit"><i class="fas fa-plug-circle-check"></i> Test Connection</button>
    </form>
</div>

<div class="grid stats-grid">
    <div class="card stat-card"><i class="fas fa-bolt stat-icon"></i><div><strong>{{ number_format($usage['today_requests']) }}</strong><span>Requests Today</span></div></div>
    <div class="card stat-card"><i class="fas fa-calendar-days stat-icon"></i><div><strong>{{ number_format($usage['month_requests']) }}</strong><span>This Month</span></div></div>
    <div class="card stat-card"><i class="fas fa-circle-check stat-icon"></i><div><strong>{{ number_format($usage['successful']) }}</strong><span>Successful</span></div></div>
    <div class="card stat-card"><i class="fas fa-triangle-exclamation stat-icon"></i><div><strong>{{ number_format($usage['failed']) }}</strong><span>Failed</span></div></div>
</div>

<div class="card" style="margin-bottom:18px">
    <form method="POST" action="{{ route('admin.ai.update') }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <label>Provider
                <input name="provider" value="{{ old('provider', $setting->provider ?? 'openai') }}" placeholder="openai" required>
            </label>

            <label>Model
                <input name="model" value="{{ old('model', $setting->model ?? '') }}" placeholder="Model name" required>
            </label>

            <label class="span-2">API Key
                <input name="api_key" type="password" autocomplete="new-password" placeholder="{{ $setting ? 'Leave blank to keep current key' : 'Enter API key' }}">
                <small style="display:block;color:var(--muted);margin-top:5px">Stored encrypted in the database and never displayed after saving.</small>
            </label>

            <label class="span-2">API Endpoint
                <input name="api_endpoint" type="url" value="{{ old('api_endpoint', $setting->api_endpoint ?? 'https://api.openai.com/v1') }}" placeholder="https://api.openai.com/v1">
            </label>

            <label>Temperature
                <input name="temperature" type="number" min="0" max="2" step="0.1" value="{{ old('temperature', $setting->temperature ?? 0.3) }}" required>
            </label>

            <label>Max Output Tokens
                <input name="max_tokens" type="number" min="64" max="100000" value="{{ old('max_tokens', $setting->max_tokens ?? 1200) }}" required>
            </label>

            <label>Daily Platform Limit
                <input name="daily_limit" type="number" min="0" value="{{ old('daily_limit', $setting->daily_limit ?? 0) }}">
                <small style="display:block;color:var(--muted);margin-top:5px">0 means unlimited.</small>
            </label>

            <label>Monthly Platform Limit
                <input name="monthly_limit" type="number" min="0" value="{{ old('monthly_limit', $setting->monthly_limit ?? 0) }}">
            </label>

            <label>Per-user Daily Limit
                <input name="per_user_daily_limit" type="number" min="0" value="{{ old('per_user_daily_limit', $setting->per_user_daily_limit ?? 0) }}">
            </label>

            <label>Timeout (seconds)
                <input name="timeout_seconds" type="number" min="5" max="180" value="{{ old('timeout_seconds', $setting->timeout_seconds ?? 30) }}" required>
            </label>

            <label>Retry Count
                <input name="retry_count" type="number" min="0" max="5" value="{{ old('retry_count', $setting->retry_count ?? 1) }}" required>
            </label>

            <label class="checkbox" style="align-self:end;padding-bottom:10px">
                <input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $setting->is_enabled ?? false))>
                Enable AI platform-wide
            </label>

            <label class="span-2">System Prompt
                <textarea name="system_prompt" rows="5" placeholder="Platform-wide assistant instructions">{{ old('system_prompt', $setting->system_prompt ?? '') }}</textarea>
            </label>

            <label class="span-2">Safety Prompt
                <textarea name="safety_prompt" rows="5" placeholder="Safeguarding, pastoral and theological boundaries">{{ old('safety_prompt', $setting->safety_prompt ?? '') }}</textarea>
            </label>
        </div>

        <div class="modal-actions">
            <button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save AI Settings</button>
        </div>
    </form>
</div>

<div class="card table-card">
    <div style="padding:17px 17px 0"><h2 style="margin:0"><i class="fas fa-chart-line"></i> Recent AI Usage</h2><p style="color:var(--muted)">Token counts: {{ number_format($usage['input_tokens']) }} input / {{ number_format($usage['output_tokens']) }} output.</p></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>User</th><th>Module</th><th>Provider / Model</th><th>Tokens</th><th>Duration</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($recentUsage as $log)
                <tr>
                    <td>{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                    <td>{{ optional($log->user)->name ?: 'System/Guest' }}<small>{{ optional($log->user)->email }}</small></td>
                    <td>{{ ucfirst(str_replace('_',' ', $log->module)) }}</td>
                    <td>{{ $log->provider ?: '—' }}<small>{{ $log->model ?: '—' }}</small></td>
                    <td>{{ number_format((int)$log->input_tokens) }} / {{ number_format((int)$log->output_tokens) }}</td>
                    <td>{{ $log->duration_ms ? number_format($log->duration_ms).' ms' : '—' }}</td>
                    <td><span class="badge badge-{{ $log->successful ? 'success' : 'danger' }}">{{ $log->successful ? 'Successful' : 'Failed' }}</span>@if(!$log->successful)<small>{{ $log->error_code ?: 'Provider error' }}</small>@endif</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="7">No AI usage has been recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $recentUsage->links() }}</div>
</div>
@endsection
