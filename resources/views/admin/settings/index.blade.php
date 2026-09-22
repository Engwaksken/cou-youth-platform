@extends('admin.layout')
@section('title','Settings')
@section('content')
<div class="page-head"><h1><i class="fas fa-gear"></i> Site Settings</h1><p>Branding, SMS, mail & backups.</p></div>
<div class="card"><form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"><div class="form-grid">@csrf @method('PUT')
<label>System name<input name="system_name" placeholder="COU Youth Platform" value="{{ $settings['system_name'] ?? '' }}"></label>
<label>Logo<input type="file" name="logo" accept="image/*"></label>
<label>Favicon<input type="file" name="favicon" accept="image/*"></label>
<label>Preview @if(!empty($settings['logo']))<img src="{{ asset('storage/'.($settings['logo']??'')) }}" width="100" alt="logo">@endif @if(!empty($settings['favicon']))<img src="{{ asset('storage/'.($settings['favicon']??'')) }}" width="32" alt="favicon">@endif</label>
<label>SMS provider username (Africa's Talking)<input name="sms_username" placeholder="e.g. myusername" value="{{ $settings['sms_username'] ?? '' }}"></label>
<label>SMS API key<input name="sms_api_key" placeholder="Paste Africa's Talking API key" value="{{ $settings['sms_api_key'] ?? '' }}"></label>
<label>SMS sender ID<input name="sms_sender" placeholder="e.g. COUYOUTH" value="{{ $settings['sms_sender'] ?? '' }}"></label>
<label>Mail host<input name="mail_host" placeholder="e.g. smtp.mailtrap.io" value="{{ $settings['mail_host'] ?? '' }}"></label>
<label>Mail port<input name="mail_port" placeholder="e.g. 2525" value="{{ $settings['mail_port'] ?? '' }}"></label>
<label>Mail username<input name="mail_username" placeholder="e.g. user@example.com" value="{{ $settings['mail_username'] ?? '' }}"></label>
<label>Mail password<input type="password" name="mail_password" placeholder="••••••••" value=""></label>
</div><div class="modal-actions"><button class="btn btn-primary" type="submit">Save settings</button></div></form></div>
<div class="card" style="margin-top:14px"><h3>Backups</h3><div class="actions"><a class="btn btn-light" href="{{ route('admin.settings.backup') }}"><i class="fas fa-download"></i> Download backup (zip)</a><form method="POST" action="{{ route('admin.settings.backup-remote') }}">@csrf<button class="btn btn-light" type="submit"><i class="fas fa-cloud-arrow-up"></i> Remote backup (S3/local)</button></form></div></div>
@endsection
