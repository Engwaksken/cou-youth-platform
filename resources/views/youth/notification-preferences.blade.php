@extends('youth.layout')
@section('title', 'Settings | COU Youth')
@section('youth_content')
<section class="hero">
    <h1>How should we <span class="scribble">reach you?</span></h1>
    <p>Choose what you hear about and where. You can change this any time.</p>
</section>

<form class="pref-form" method="POST" action="{{ route('youth.notification-preferences.update') }}">
    @csrf @method('PUT')

    <section class="card pref-group">
        <h2>Where</h2>
        @foreach([
            'in_app' => ['In the app', 'Updates show up in your feed here.', 'fa-bell'],
            'push' => ['Phone notifications', 'A ping on your phone, if your browser allows it.', 'fa-mobile-screen'],
            'email' => ['Email', 'Sent to the email address on your account.', 'fa-envelope'],
        ] as $field => [$label, $help, $icon])
            <label class="toggle-row">
                <span class="toggle-icon" aria-hidden="true"><i class="fas {{ $icon }}"></i></span>
                <span class="toggle-copy"><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                <input class="switch" type="checkbox" name="{{ $field }}" value="1" @checked($preferences->{$field})>
            </label>
        @endforeach
    </section>

    <section class="card pref-group">
        <h2>What</h2>
        @foreach([
            'events' => ['Events', 'New events, reminders and changes.', 'fa-ticket'],
            'discipleship' => ['Courses & Bible study', 'New courses and nudges to keep going.', 'fa-book-open'],
            'opportunities' => ['Opportunities', 'Ways to serve, lead and get involved.', 'fa-lightbulb'],
            'life_groups' => ['Life groups', 'News from the groups you belong to.', 'fa-people-group'],
            'donations' => ['Giving', 'Receipts and updates on what your giving supports.', 'fa-hand-holding-heart'],
        ] as $field => [$label, $help, $icon])
            <label class="toggle-row">
                <span class="toggle-icon" aria-hidden="true"><i class="fas {{ $icon }}"></i></span>
                <span class="toggle-copy"><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                <input class="switch" type="checkbox" name="{{ $field }}" value="1" @checked($preferences->{$field})>
            </label>
        @endforeach
    </section>

    <div class="form-actions">
        <a class="btn btn-ghost" href="{{ route('youth.notifications') }}">Cancel</a>
        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Save</button>
    </div>
</form>

<style>
.pref-form{max-width:46rem;display:grid;gap:18px}
.pref-group{padding:8px 20px}
.pref-group h2{font-size:1.2rem;margin:14px 0 4px}
.toggle-row{display:flex;align-items:center;gap:14px;padding:14px 0;cursor:pointer}
.toggle-row+.toggle-row{border-top:1px solid var(--line)}
.toggle-icon{width:40px;height:40px;flex:0 0 40px;border-radius:12px;display:grid;place-items:center;background:var(--paper-2);color:var(--primary)}
.toggle-copy{flex:1;min-width:0}
.toggle-copy strong,.toggle-copy small{display:block}
.toggle-copy small{color:var(--muted);margin-top:2px}
.switch{appearance:none;-webkit-appearance:none;flex:0 0 auto;width:52px!important;height:30px!important;min-height:0!important;margin:0;padding:0!important;border-radius:999px;border:1.5px solid var(--ink);background:var(--paper-2);position:relative;cursor:pointer;transition:background .2s ease}
.switch::after{content:'';position:absolute;top:3px;left:3px;width:21px;height:21px;border-radius:50%;background:#fff;border:1.5px solid var(--ink);transition:transform .2s cubic-bezier(.22,1,.36,1)}
.switch:checked{background:var(--leaf)}
.switch:checked::after{transform:translateX(22px)}
.switch:focus-visible{outline:3px solid var(--sun);outline-offset:2px}
.form-actions{display:flex;justify-content:flex-end;gap:10px}
@media(max-width:560px){.form-actions .btn{flex:1}}
</style>
@endsection
