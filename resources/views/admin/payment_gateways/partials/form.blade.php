@php
    $editing = isset($gateway) && $gateway;
    $settings = is_array($settings ?? null) ? $settings : [];
@endphp

<div class="form-grid gateway-form-grid">
    <label>
        Gateway name
        <input
            type="text"
            name="name"
            value="{{ old('name', $editing ? $gateway->name : '') }}"
            maxlength="120"
            required
            placeholder="e.g. MTN Mobile Money"
        >
    </label>

    <label>
        Provider
        <input
            type="text"
            name="provider"
            value="{{ old('provider', $editing ? $gateway->provider : '') }}"
            maxlength="80"
            required
            placeholder="e.g. MTN, Airtel, Flutterwave, Pesapal"
        >
    </label>

    <label>
        Slug
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $editing ? $gateway->slug : '') }}"
            maxlength="120"
            placeholder="Generated from name when blank"
        >
    </label>

    <label>
        Currency
        <input
            type="text"
            name="currency"
            value="{{ old('currency', $editing ? strtoupper($gateway->currency) : 'UGX') }}"
            maxlength="3"
            minlength="3"
            required
            placeholder="UGX"
        >
    </label>

    <label>
        Sort order
        <input
            type="number"
            name="sort_order"
            value="{{ old('sort_order', $editing ? (int) $gateway->sort_order : 0) }}"
            min="0"
            max="65535"
            step="1"
        >
    </label>

    <label>
        Timeout (seconds)
        <input
            type="number"
            name="timeout"
            value="{{ old('timeout', $settings['timeout'] ?? 30) }}"
            min="5"
            max="120"
            step="1"
        >
    </label>

    <div class="span-2 gateway-form-section">
        <h3><i class="fas fa-key"></i> API credentials</h3>
        <p>{{ $editing ? 'Leave these fields blank to keep the encrypted credentials already saved.' : 'Credentials are encrypted before they are stored.' }}</p>
    </div>

    <label>
        API / Public key
        <input
            type="password"
            name="api_key"
            autocomplete="new-password"
            placeholder="{{ $editing ? 'Leave blank to keep existing key' : 'Enter API or public key' }}"
        >
    </label>

    <label>
        API secret
        <input
            type="password"
            name="api_secret"
            autocomplete="new-password"
            placeholder="{{ $editing ? 'Leave blank to keep existing secret' : 'Enter API secret' }}"
        >
    </label>

    <div class="span-2 gateway-form-section">
        <h3><i class="fas fa-link"></i> Integration endpoints</h3>
        <p>Use HTTPS production URLs when switching a gateway to live mode.</p>
    </div>

    <label class="span-2">
        Initialise / checkout URL
        <input
            type="url"
            name="initialize_url"
            value="{{ old('initialize_url', $settings['initialize_url'] ?? '') }}"
            placeholder="https://provider.example.com/payments/initialize"
        >
    </label>

    <label class="span-2">
        Callback URL
        <input
            type="url"
            name="callback_url"
            value="{{ old('callback_url', $settings['callback_url'] ?? '') }}"
            placeholder="https://couyp.kemmytech.com/payment/callback"
        >
    </label>

    <label class="span-2">
        Webhook URL
        <input
            type="url"
            name="webhook_url"
            value="{{ old('webhook_url', $settings['webhook_url'] ?? '') }}"
            placeholder="https://couyp.kemmytech.com/api/v1/payments/webhooks/provider"
        >
    </label>

    <label class="span-2">
        Webhook secret
        <input
            type="password"
            name="webhook_secret"
            value=""
            autocomplete="new-password"
            placeholder="{{ $editing && !empty($settings['webhook_secret']) ? 'Enter a value to replace the saved webhook secret' : 'Optional webhook signing secret' }}"
        >
        @if($editing && !empty($settings['webhook_secret']))
            <small class="gateway-secret-hint"><i class="fas fa-lock"></i> A webhook secret is already configured.</small>
        @endif
    </label>

    <div class="span-2 gateway-toggle-row">
        <label class="gateway-toggle">
            <input
                type="checkbox"
                name="is_test_mode"
                value="1"
                @checked(old('is_test_mode', $editing ? $gateway->is_test_mode : true))
            >
            <span>
                <strong>Test mode</strong>
                <small>Use sandbox or non-production credentials.</small>
            </span>
        </label>

        <label class="gateway-toggle">
            <input
                type="checkbox"
                name="is_enabled"
                value="1"
                @checked(old('is_enabled', $editing ? $gateway->is_enabled : false))
            >
            <span>
                <strong>Enabled</strong>
                <small>Allow this gateway to be used by the platform.</small>
            </span>
        </label>
    </div>
</div>

<div class="modal-actions">
    <button type="button" class="btn btn-light" data-close-modal>
        <i class="fas fa-xmark"></i> Cancel
    </button>
    <button type="submit" class="btn btn-primary">
        <i class="fas {{ $submitIcon ?? 'fa-floppy-disk' }}"></i> {{ $submitLabel ?? 'Save Gateway' }}
    </button>
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
