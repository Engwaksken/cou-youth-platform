@php
    $current = $setting;
@endphp

<div class="form-grid">
    <label>
        Provider
        <input name="provider" value="{{ old('provider', $current->provider ?? 'openai') }}" placeholder="openai" required>
    </label>

    <label>
        Model
        <input name="model" value="{{ old('model', $current->model ?? '') }}" placeholder="gpt-5.6" required>
    </label>

    <label class="span-2">
        API Key
        <input
            name="api_key"
            type="password"
            autocomplete="new-password"
            placeholder="{{ $creating ? 'Enter API key' : 'Leave blank to keep the current key' }}"
            @required($creating)
        >
        <small class="field-help">Stored encrypted and never displayed after saving.</small>
    </label>

    <label class="span-2">
        API Endpoint
        <input
            name="api_endpoint"
            type="url"
            value="{{ old('api_endpoint', $current->api_endpoint ?? 'https://api.openai.com/v1') }}"
            placeholder="https://api.openai.com/v1"
        >
    </label>

    <label>
        Temperature
        <input name="temperature" type="number" min="0" max="2" step="0.1" value="{{ old('temperature', $current->temperature ?? 0.3) }}" required>
    </label>

    <label>
        Max Output Tokens
        <input name="max_tokens" type="number" min="64" max="100000" value="{{ old('max_tokens', $current->max_tokens ?? 1200) }}" required>
    </label>

    <label>
        Daily Platform Limit
        <input name="daily_limit" type="number" min="0" value="{{ old('daily_limit', $current->daily_limit ?? 0) }}">
        <small class="field-help">Use 0 for unlimited.</small>
    </label>

    <label>
        Monthly Platform Limit
        <input name="monthly_limit" type="number" min="0" value="{{ old('monthly_limit', $current->monthly_limit ?? 0) }}">
        <small class="field-help">Use 0 for unlimited.</small>
    </label>

    <label>
        Per-user Daily Limit
        <input name="per_user_daily_limit" type="number" min="0" value="{{ old('per_user_daily_limit', $current->per_user_daily_limit ?? 0) }}">
        <small class="field-help">Use 0 for unlimited.</small>
    </label>

    <label>
        Timeout (seconds)
        <input name="timeout_seconds" type="number" min="5" max="180" value="{{ old('timeout_seconds', $current->timeout_seconds ?? 30) }}" required>
    </label>

    <label>
        Retry Count
        <input name="retry_count" type="number" min="0" max="5" value="{{ old('retry_count', $current->retry_count ?? 1) }}" required>
    </label>

    <label class="checkbox span-2">
        <input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $current->is_enabled ?? false))>
        <span>Make this the active AI setting</span>
    </label>

    <div class="span-2 ai-active-note">
        <i class="fas fa-circle-info"></i>
        Activating this setting automatically disables every other AI setting. Only one active configuration is used by the platform.
    </div>

    <label class="span-2">
        System Prompt
        <textarea name="system_prompt" rows="5" placeholder="Platform-wide system instructions">{{ old('system_prompt', $current->system_prompt ?? '') }}</textarea>
    </label>

    <label class="span-2">
        Safety Prompt
        <textarea name="safety_prompt" rows="5" placeholder="Safety and safeguarding instructions">{{ old('safety_prompt', $current->safety_prompt ?? '') }}</textarea>
    </label>
</div>

<style>
    .ai-active-note{display:flex;align-items:flex-start;gap:8px;padding:11px 12px;border-radius:10px;background:#f5f3ff;color:#5b21b6;font-size:.9rem;line-height:1.45}
</style>
