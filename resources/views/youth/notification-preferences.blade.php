@extends('youth.layout')
@section('title', 'Notification Preferences | COU Youth')
@section('youth_content')
@php
    $enabledChannels = collect(['in_app','push','email'])->filter(fn($field) => (bool) $preferences->{$field})->count();
    $enabledTopics = collect(['events','discipleship','opportunities','donations','life_groups'])->filter(fn($field) => (bool) $preferences->{$field})->count();
@endphp
<section class="hero"><span class="section-kicker">PREFERENCES</span><h1>Notification preferences</h1><p>Choose how you want to receive updates and the topics you want to hear about.</p></section>
<div class="page-stats">
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-tower-broadcast"></i></span><div><strong>{{ $enabledChannels }}</strong><span>Enabled channels</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-list-check"></i></span><div><strong>{{ $enabledTopics }}</strong><span>Enabled topics</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-mobile-screen"></i></span><div><strong>{{ $preferences->push ? 'On' : 'Off' }}</strong><span>Push notifications</span></div></div>
    <div class="page-stat"><span class="page-stat-icon"><i class="fas fa-envelope"></i></span><div><strong>{{ $preferences->email ? 'On' : 'Off' }}</strong><span>Email notifications</span></div></div>
</div>
<section class="card preference-card">
<form method="POST" action="{{ route('youth.notification-preferences.update') }}">@csrf @method('PUT')
<div class="preference-group"><span class="section-kicker">CHANNELS</span><h2>Delivery channels</h2><div class="preference-grid">
@foreach(['in_app'=>'In-app notifications','push'=>'Push notifications','email'=>'Email notifications'] as $field=>$label)
<label class="preference-option"><span><strong>{{ $label }}</strong><small>{{ $field==='in_app' ? 'See alerts inside your COU Youth account.' : ($field==='push' ? 'Receive device notifications when supported.' : 'Receive updates at your account email address.') }}</small></span><input type="checkbox" name="{{ $field }}" value="1" @checked($preferences->{$field})></label>
@endforeach
</div></div>
<div class="preference-group"><span class="section-kicker">TOPICS</span><h2>Topics</h2><div class="preference-grid">
@foreach(['events'=>'Events','discipleship'=>'Discipleship & learning','opportunities'=>'Youth opportunities','donations'=>'Giving & donations','life_groups'=>'Life Groups'] as $field=>$label)
<label class="preference-option"><span><strong>{{ $label }}</strong><small>Receive relevant {{ strtolower($label) }} updates.</small></span><input type="checkbox" name="{{ $field }}" value="1" @checked($preferences->{$field})></label>
@endforeach
</div></div>
<div class="form-actions"><a class="btn" href="{{ route('youth.notifications') }}">Back to notifications</a><button class="btn btn-primary" type="submit">Save preferences</button></div>
</form>
</section>
<style>.preference-card{max-width:900px;margin:0 auto;padding:24px}.preference-group+ .preference-group{margin-top:28px}.preference-group h2{margin:3px 0 14px}.preference-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.preference-option{display:flex;align-items:center;justify-content:space-between;gap:18px;border:1px solid var(--border);border-radius:10px;padding:14px;background:#fff;cursor:pointer}.preference-option strong,.preference-option small{display:block}.preference-option small{color:var(--muted);font-size:.8rem;margin-top:3px}.preference-option input{width:20px;height:20px;accent-color:var(--primary)}.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:24px}@media(max-width:650px){.preference-grid{grid-template-columns:1fr}.form-actions{flex-direction:column}.form-actions .btn{width:100%}}</style>
@endsection
