@php
    $current = $setting;
    $provider = old('provider', $current->provider ?? 'openai');
@endphp

<div class="form-grid">
    <label>
        Provider
        <select name="provider" id="ai-provider" required>
            <option value="openai" @selected($provider === 'openai')>OpenAI</option>
            <option value="deepseek" @selected(in_array($provider, ['deepseek', 'deep_seek'], true))>DeepSeek</option>
            <option value="gemini" @selected(in_array($provider, ['gemini', 'google', 'google_gemini'], true))>Google Gemini</option>
            <option value="openai_compatible" @selected(in_array($provider, ['openai_compatible', 'custom', 'compatible'], true))>OpenAI Compatible / Custom</option>
        </select>
    </label>

    <label>
        Model
        <input name="model" value="{{ old('model', $current->model ?? '') }}" placeholder="e.g. gpt-5.6, deepseek-chat, gemini-2.5-flash" required>
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
        <small class="field-help">Stored encrypted and never displayed after saving. Leaving this blank while editing keeps the existing key.</small>
    </label>

    <label class="span-2">
        API Endpoint
        <input
            name="api_endpoint"
            id="ai-endpoint"
            type="url"
            value="{{ old('api_endpoint', $current->api_endpoint ?? 'https://api.openai.com/v1') }}"
            placeholder="https://api.openai.com/v1"
        >
        <small class="field-help" id="ai-endpoint-help">OpenAI default: https://api.openai.com/v1</small>
    </label>

    <label>
        Temperature
        <input name="temperature" type="number" min="0" max="2" step="0.1" value="{{ old('temperature', $current->temperature ?? 0.3) }}" required>
        <small class="field-help">Some reasoning models ignore or do not accept temperature; the platform handles those automatically.</small>
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
        <div>
            <strong>Only one provider is active at a time.</strong><br>
            The connection test uses the saved active provider, model, endpoint and encrypted API key. If the provider rejects the request, the page will now show the actual reason without displaying the secret key.
        </div>
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

<script>
(() => {
    const provider = document.getElementById('ai-provider');
    const endpoint = document.getElementById('ai-endpoint');
    const help = document.getElementById('ai-endpoint-help');

    if (!provider || !endpoint || !help) return;

    const defaults = {
        openai: {
            endpoint: 'https://api.openai.com/v1',
            help: 'OpenAI default: https://api.openai.com/v1',
        },
        deepseek: {
            endpoint: 'https://api.deepseek.com',
            help: 'DeepSeek default: https://api.deepseek.com',
        },
        gemini: {
            endpoint: 'https://generativelanguage.googleapis.com/v1beta',
            help: 'Gemini default: https://generativelanguage.googleapis.com/v1beta',
        },
        openai_compatible: {
            endpoint: '',
            help: 'Enter the provider base endpoint. The platform will call /chat/completions.',
        },
    };

    let previousProvider = provider.value;

    provider.addEventListener('change', () => {
        const previousDefault = defaults[previousProvider]?.endpoint ?? '';
        const next = defaults[provider.value] ?? defaults.openai_compatible;
        const current = endpoint.value.trim();

        if (current === '' || current === previousDefault) {
            endpoint.value = next.endpoint;
        }

        help.textContent = next.help;
        previousProvider = provider.value;
    });

    help.textContent = (defaults[provider.value] ?? defaults.openai_compatible).help;
})();
</script>
