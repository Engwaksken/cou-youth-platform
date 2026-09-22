@extends('admin.layout')
@section('title','Settings')
@section('content')
<div class="page-head"><div><h1><i class="fas fa-gear"></i> Site Settings</h1><p>Manage branding, communication providers and backups from organised tabs.</p></div></div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div data-tabs class="settings-tabs-wrap">
    <div class="tabs settings-tabs" role="tablist" aria-label="Site settings sections">
        <button type="button" class="tab active" data-tab-target="settings-branding"><i class="fas fa-palette"></i> Branding</button>
        <button type="button" class="tab" data-tab-target="settings-sms"><i class="fas fa-comment-sms"></i> SMS</button>
        <button type="button" class="tab" data-tab-target="settings-mail"><i class="fas fa-envelope"></i> Email</button>
        <button type="button" class="tab" data-tab-target="settings-backups"><i class="fas fa-database"></i> Backups</button>
    </div>

    <section id="settings-branding" class="tab-panel active">
        <div class="card settings-card">
            <div class="settings-section-head"><div><h2><i class="fas fa-palette"></i> Branding</h2><p>Control the name and visual identity shown across the public site, admin area, emails and notifications.</p></div></div>
            <div class="form-grid">
                <label>System name<input name="system_name" placeholder="COU Youth Platform" value="{{ old('system_name', $settings['system_name'] ?? '') }}"></label>
                <label>Logo<input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"><small>Recommended: transparent PNG/WebP or SVG.</small></label>
                <label>Favicon<input type="file" name="favicon" accept="image/png,image/x-icon,image/svg+xml,image/webp"><small>Recommended square image, at least 32×32.</small></label>
                <div class="brand-preview">
                    <span>Current branding preview</span>
                    <div class="preview-row">
                        @if(!empty($settings['logo']))<div class="preview-box preview-logo"><img src="{{ asset('storage/'.$settings['logo']) }}" alt="Current logo"></div>@else<div class="preview-box preview-logo preview-empty"><i class="fas fa-image"></i><small>No logo</small></div>@endif
                        @if(!empty($settings['favicon']))<div class="preview-box preview-favicon"><img src="{{ asset('storage/'.$settings['favicon']) }}" alt="Current favicon"></div>@else<div class="preview-box preview-favicon preview-empty"><i class="fas fa-star"></i></div>@endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="settings-sms" class="tab-panel">
        <div class="card settings-card">
            <div class="settings-section-head"><div><h2><i class="fas fa-comment-sms"></i> SMS Configuration</h2><p>Configure Africa's Talking for bulk SMS and platform messaging.</p></div></div>
            <div class="form-grid">
                <label>SMS provider username<input name="sms_username" placeholder="e.g. myusername" value="{{ old('sms_username', $settings['sms_username'] ?? '') }}"></label>
                <label>SMS sender ID<input name="sms_sender" placeholder="e.g. COUYOUTH" value="{{ old('sms_sender', $settings['sms_sender'] ?? '') }}"></label>
                <label class="span-2">SMS API key<input type="password" name="sms_api_key" placeholder="Enter a new key only when changing it" autocomplete="new-password"><small>For security, the existing API key is not displayed.</small></label>
            </div>
        </div>
    </section>

    <section id="settings-mail" class="tab-panel">
        <div class="card settings-card">
            <div class="settings-section-head"><div><h2><i class="fas fa-envelope"></i> Email Configuration</h2><p>Configure the SMTP server used for branded platform emails and notifications.</p></div></div>
            <div class="form-grid">
                <label>Mail host<input name="mail_host" placeholder="e.g. smtp.example.com" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}"></label>
                <label>Mail port<input name="mail_port" inputmode="numeric" placeholder="e.g. 587" value="{{ old('mail_port', $settings['mail_port'] ?? '') }}"></label>
                <label>Mail username<input name="mail_username" autocomplete="username" placeholder="e.g. notifications@example.com" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"></label>
                <label>Mail password<input type="password" name="mail_password" autocomplete="new-password" placeholder="Enter only when changing password"><small>Leave blank to keep the current password.</small></label>
            </div>
        </div>
    </section>

    <section id="settings-backups" class="tab-panel">
        <div class="card settings-card">
            <div class="settings-section-head"><div><h2><i class="fas fa-database"></i> Backups</h2><p>Create a downloadable system backup or send a backup to the configured remote storage.</p></div></div>
            <div class="backup-grid">
                <div class="backup-option"><span class="backup-icon"><i class="fas fa-file-zipper"></i></span><div><strong>Download backup</strong><p>Create and download a ZIP backup to this device.</p><a class="btn btn-light" href="{{ route('admin.settings.backup') }}"><i class="fas fa-download"></i> Download backup</a></div></div>
                <div class="backup-option"><span class="backup-icon"><i class="fas fa-cloud-arrow-up"></i></span><div><strong>Remote backup</strong><p>Send the backup to the configured S3 or remote/local backup destination.</p><button class="btn btn-light" type="submit" formaction="{{ route('admin.settings.backup-remote') }}" formmethod="POST" name="_backup_remote" value="1"><i class="fas fa-cloud-arrow-up"></i> Run remote backup</button></div></div>
            </div>
        </div>
    </section>
</div>

<div class="settings-savebar"><span><i class="fas fa-circle-info"></i> Save changes made in Branding, SMS or Email tabs.</span><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save settings</button></div>
</form>

<style>
.settings-tabs-wrap{min-width:0}.settings-tabs{margin-top:0;position:sticky;top:68px;background:var(--bg);z-index:20;padding-top:4px}.settings-card{max-width:1000px}.settings-section-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px}.settings-section-head h2{margin:0;font-size:20px}.settings-section-head h2 i{color:var(--primary);margin-right:8px}.settings-section-head p{margin:6px 0 0;color:var(--muted);max-width:720px}.form-grid small{display:block;margin-top:6px;color:var(--muted);font-weight:400;line-height:1.4}.brand-preview{border:1px solid var(--border);border-radius:11px;padding:12px;background:#fafafa}.brand-preview>span{display:block;font-size:12px;font-weight:800;color:#4b5563;margin-bottom:9px}.preview-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.preview-box{display:grid;place-items:center;border:1px solid var(--border);background:#fff;border-radius:11px;overflow:hidden}.preview-logo{width:150px;height:76px}.preview-favicon{width:52px;height:52px}.preview-box img{display:block;max-width:100%;max-height:100%;object-fit:contain;padding:5px}.preview-empty{color:#9ca3af;gap:4px}.preview-empty small{margin:0}.backup-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.backup-option{display:flex;gap:14px;border:1px solid var(--border);border-radius:12px;padding:16px;background:#fafafa}.backup-icon{width:46px;height:46px;min-width:46px;border-radius:11px;display:grid;place-items:center;background:#ede9fe;color:var(--primary);font-size:19px}.backup-option strong{display:block;margin-bottom:4px}.backup-option p{margin:0 0 13px;color:var(--muted);line-height:1.5}.settings-savebar{position:sticky;bottom:0;z-index:30;margin-top:16px;padding:12px 14px;background:rgba(255,255,255,.96);backdrop-filter:blur(8px);border:1px solid var(--border);border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 -6px 24px rgba(15,23,42,.06)}.settings-savebar span{color:var(--muted);font-size:13px}.settings-savebar span i{color:var(--primary);margin-right:5px}@media(max-width:800px){.settings-tabs{top:68px}.backup-grid{grid-template-columns:1fr}.settings-savebar{align-items:stretch;flex-direction:column}.settings-savebar .btn{width:100%}.preview-logo{width:120px;height:66px}}
</style>
@endsection
