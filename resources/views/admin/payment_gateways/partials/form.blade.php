@php
    $editing = isset($gateway) && $gateway;
    $settings = is_array($settings ?? null) ? $settings : [];
    $credentials = $editing && is_array($gateway->credentials ?? null) ? $gateway->credentials : [];
    $credentialsConfigured = $editing && ! empty($credentials);
    $webhookSecretConfigured = $editing && (
        ! empty($credentials['webhook_secret'] ?? null)
        || ! empty($settings['webhook_secret'] ?? null)
    );
@endphp

<div class="form-grid gateway-form-grid">
    <label>
        Gateway name
        <input type="text" name="name" value="{{ old('name', $editing ? $gateway->name : '') }}" maxlength="120" required placeholder="e.g. MTN Mobile Money">
    </label>

    <label>
        Provider
        <select name="provider" required>
            @php $provider = old('provider', $editing ? $gateway->provider : 'mtn_momo'); @endphp
            <option value="mtn_momo" @selected($provider === 'mtn_momo')>MTN MoMo</option>
            <option value="airtel_money" @selected($provider === 'airtel_money')>Airtel Money</option>
            <option value="flutterwave" @selected($provider === 'flutterwave')>Flutterwave</option>
            <option value="pesapal" @selected($provider === 'pesapal')>Pesapal</option>
            <option value="http" @selected($provider === 'http')>Generic HTTP</option>
        </select>
    </label>

    <label>
        Slug
        <input type="text" name="slug" value="{{ old('slug', $editing ? $gateway->slug : '') }}" maxlength="120" placeholder="Generated from name when blank">
    </label>

    <label>
        Currency
        <input type="text" name="currency" value="{{ old('currency', $editing ? strtoupper($gateway->currency) : 'UGX') }}" maxlength="3" minlength="3" required placeholder="UGX">
    </label>

    <label>
        Country code
        <input type="text" name="country" value="{{ old('country', $settings['country'] ?? 'UG') }}" maxlength="2" minlength="2" placeholder="UG">
    </label>

    <label>
        Sort order
        <input type="number" name="sort_order" value="{{ old('sort_order', $editing ? (int) $gateway->sort_order : 0) }}" min="0" max="65535" step="1">
    </label>

    <label>
        Timeout (seconds)
        <input type="number" name="timeout" value="{{ old('timeout', $settings['timeout'] ?? 30) }}" min="5" max="120" step="1">
    </label>

    <label>
        Target environment
        <input type="text" name="target_environment" value="{{ old('target_environment', $settings['target_environment'] ?? ($editing && !$gateway->is_test_mode ? '' : 'sandbox')) }}" placeholder="sandbox or provider live environment">
    </label>

    <div class="span-2 gateway-form-section">
        <h3><i class="fas fa-server"></i> Provider base URL</h3>
        <p>Sandbox defaults are used by MTN and Airtel when this field is blank in test mode. Configure the production base URL before enabling live mode.</p>
    </div>

    <label class="span-2">
        Base URL
        <input type="url" name="base_url" value="{{ old('base_url', $settings['base_url'] ?? '') }}" placeholder="https://provider-api.example.com">
    </label>

    <div class="span-2 gateway-form-section">
        <h3><i class="fas fa-key"></i> General / generic API credentials</h3>
        <p>{{ $editing ? 'Leave secret fields blank to keep credentials already saved.' : 'Credentials are encrypted before they are stored.' }}</p>
    </div>

    <label>
        API / Public key
        <input type="password" name="api_key" autocomplete="new-password" placeholder="{{ $editing ? 'Leave blank to keep existing key' : 'Enter API or public key' }}">
    </label>

    <label>
        API secret
        <input type="password" name="api_secret" autocomplete="new-password" placeholder="{{ $editing ? 'Leave blank to keep existing secret' : 'Enter API secret' }}">
    </label>

    <div class="span-2 gateway-form-section provider-credentials">
        <h3><i class="fas fa-mobile-screen-button"></i> MTN MoMo credentials</h3>
        <p>Use the Collections subscription key plus API User and API Key issued through the MTN MoMo portal.</p>
    </div>

    <label>
        MTN subscription key
        <input type="password" name="subscription_key" autocomplete="new-password" placeholder="{{ $editing ? 'Leave blank to keep saved key' : 'Collections subscription key' }}">
    </label>

    <label>
        MTN API User
        <input type="password" name="api_user" autocomplete="new-password" placeholder="{{ $editing ? 'Leave blank to keep saved API user' : 'API User UUID' }}">
    </label>

    <div class="span-2 gateway-form-section provider-credentials">
        <h3><i class="fas fa-signal"></i> Airtel Money credentials</h3>
        <p>Use the Client ID and Client Secret from your Airtel Africa Developer Portal application.</p>
    </div>

    <label>
        Airtel Client ID
        <input type="password" name="client_id" autocomplete="new-password" placeholder="{{ $editing ? 'Leave blank to keep saved client ID' : 'Client ID' }}">
    </label>

    <label>
        Airtel Client Secret
        <input type="password" name="client_secret" autocomplete="new-password" placeholder="{{ $editing ? 'Leave blank to keep saved client secret' : 'Client Secret' }}">
    </label>

    @if($credentialsConfigured)
        <div class="span-2 gateway-secret-hint"><i class="fas fa-lock"></i> Encrypted provider credentials are already configured. Blank secret fields do not erase them.</div>
    @endif

    <div class="span-2 gateway-form-section">
        <h3><i class="fas fa-link"></i> Integration endpoints</h3>
        <p>Use HTTPS callback URLs. The platform webhook path should normally be /api/v1/payments/webhooks/{gateway-slug}.</p>
    </div>

    <label class="span-2">
        Initialise / checkout URL
        <input type="url" name="initialize_url" value="{{ old('initialize_url', $settings['initialize_url'] ?? '') }}" placeholder="Optional for generic HTTP gateways">
    </label>

    <label class="span-2">
        Callback URL
        <input type="url" name="callback_url" value="{{ old('callback_url', $settings['callback_url'] ?? '') }}" placeholder="https://couyp.kemmytech.com/api/v1/payments/webhooks/mtn-mobile-money">
    </label>

    <label class="span-2">
        Webhook URL
        <input type="url" name="webhook_url" value="{{ old('webhook_url', $settings['webhook_url'] ?? '') }}" placeholder="https://couyp.kemmytech.com/api/v1/payments/webhooks/provider">
    </label>

    <label class="span-2">
        Webhook secret
        <input type="password" name="webhook_secret" autocomplete="new-password" placeholder="{{ $webhookSecretConfigured ? 'Leave blank to keep the encrypted webhook secret' : 'Optional webhook signing secret' }}">
        @if($webhookSecretConfigured)
            <small class="gateway-secret-hint"><i class="fas fa-lock"></i> An encrypted webhook secret is configured. Saving this gateway will also migrate any legacy plaintext secret into encrypted storage.</small>
        @endif
    </label>

    <div class="span-2 gateway-toggle-row">
        <label class="gateway-toggle">
            <input type="checkbox" name="is_test_mode" value="1" @checked(old('is_test_mode', $editing ? $gateway->is_test_mode : true))>
            <span><strong>Test mode</strong><small>Use sandbox or non-production credentials.</small></span>
        </label>

        <label class="gateway-toggle">
            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $editing ? $gateway->is_enabled : false))>
            <span><strong>Enabled</strong><small>Allow this gateway to be used by the platform.</small></span>
        </label>
    </div>
</div>

<div class="modal-actions">
    <button type="button" class="btn btn-light" data-close-modal><i class="fas fa-xmark"></i> Cancel</button>
    <button type="submit" class="btn btn-primary"><i class="fas {{ $submitIcon ?? 'fa-floppy-disk' }}"></i> {{ $submitLabel ?? 'Save Gateway' }}</button>
</div>

<style>
    .gateway-form-section { border-top:1px solid var(--border); padding-top:14px; margin-top:4px; }
    .gateway-form-section h3 { margin:0; font-size:15px; color:var(--text); }
    .gateway-form-section h3 i { color:var(--primary); margin-right:6px; }
    .gateway-form-section p { margin:5px 0 0; color:var(--muted); font-size:12px; font-weight:400; }
    .gateway-toggle-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .gateway-toggle { display:flex; align-items:flex-start; gap:10px; border:1px solid var(--border); border-radius:10px; padding:12px; background:#fafafa; cursor:pointer; }
    .gateway-toggle input { width:auto !important; margin:3px 0 0 !important; }
    .gateway-toggle strong,.gateway-toggle small { display:block; }
    .gateway-toggle small { color:var(--muted); font-weight:400; margin-top:3px; line-height:1.4; }
    .gateway-secret-hint { display:block; margin-top:6px; color:var(--muted); font-weight:400; }
    @media(max-width:700px){ .gateway-toggle-row{grid-template-columns:1fr;} }
</style>
