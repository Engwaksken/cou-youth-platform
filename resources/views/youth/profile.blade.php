@extends('youth.layout')
@section('title', 'My Profile | COU Youth')
@section('youth_content')
@php
    $ageLabels = ['teen' => 'Teens', 'youth' => 'Youth', 'young_adult' => 'Young adults'];
    $initials = \Illuminate\Support\Str::of(auth()->user()->name)->trim()->explode(' ')->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    // Open the tab that has a problem after a failed save.
    $tabErrors = [
        'basics' => $errors->hasAny(['date_of_birth', 'diocese_id', 'organisation_unit_id', 'school_institution']),
        'interests' => $errors->hasAny(['interests', 'talents', 'skills', 'ministry_interests']),
        'privacy' => $errors->has('profile_public'),
    ];
    $openTab = array_key_first(array_filter($tabErrors)) ?? 'basics';
@endphp
<section class="profile-hero">
    <span class="big-avatar" aria-hidden="true">@if($initials){{ $initials }}@else<i class="fas fa-user"></i>@endif</span>
    <div>
        <h1>All about <span class="scribble">you</span></h1>
        <p>{{ auth()->user()->name }}@if($profile?->age_category), {{ $ageLabels[$profile->age_category] ?? ucfirst(str_replace('_', ' ', $profile->age_category)) }}@endif @if($profile?->organisationUnit), {{ $profile->organisationUnit->name }}@endif @if($profile?->diocese), {{ $profile->diocese->name }}@endif</p>
    </div>
</section>

@if($errors->any())
    <div class="form-errors" role="alert"><strong>A couple of things to fix:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form class="profile-form" method="POST" action="{{ route('youth.profile.update') }}">
    @csrf @method('PUT')

    <div data-tabset>
    <div class="tabset-list" role="tablist" aria-label="Profile sections">
        @foreach(['basics' => ['fa-id-card', 'The basics'], 'interests' => ['fa-heart', "What you're into"], 'privacy' => ['fa-lock', 'Privacy']] as $key => [$icon, $label])
            <button type="button" role="tab" id="profile-tab-{{ $key }}" data-tab="{{ $key }}" aria-controls="profile-{{ $key }}" aria-selected="{{ $openTab === $key ? 'true' : 'false' }}">
                <i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                @if($tabErrors[$key])<span class="tab-flag" aria-label="Needs attention"></span>@endif
            </button>
        @endforeach
    </div>

    <section class="card form-block tabset-panel" role="tabpanel" id="profile-basics" aria-labelledby="profile-tab-basics">
        <h2>The basics</h2>
        <div class="form-grid">
            <label>Birthday
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required>
                <small>We use this to show you the right age group. It's never shown publicly.</small>
            </label>
            <label>Your diocese
                <select name="diocese_id" required>
                    <option value="">Choose your Church of Uganda diocese</option>
                    @foreach($dioceses as $diocese)<option value="{{ $diocese->id }}" @selected((string) old('diocese_id', $profile?->diocese_id) === (string) $diocese->id)>{{ $diocese->name }}</option>@endforeach
                </select>
            </label>
            <label>Your church
                <select name="organisation_unit_id">
                    <option value="">Choose your church</option>
                    @foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('organisation_unit_id', $profile?->organisation_unit_id) === (string) $unit->id)>{{ $unit->name }}</option>@endforeach
                </select>
            </label>
            <label class="span-2">School, college or work
                <input type="text" name="school_institution" maxlength="190" value="{{ old('school_institution', $profile?->school_institution) }}" placeholder="e.g. Makerere University, Gayaza High School, or where you work">
            </label>
        </div>
    </section>

    <section class="card form-block tabset-panel" role="tabpanel" id="profile-interests" aria-labelledby="profile-tab-interests">
        <h2>What you're into</h2>
        <p class="muted">Separate things with commas. This helps us point you to the right groups, courses and ways to serve.</p>
        <div class="form-grid">
            <label class="span-2">Things you love
                <input type="text" name="interests" value="{{ old('interests', implode(', ', $profile?->interests ?? [])) }}" placeholder="Music, football, tech, fashion, business">
            </label>
            <label>Gifts &amp; talents
                <input type="text" name="talents" value="{{ old('talents', implode(', ', $profile?->talents ?? [])) }}" placeholder="Singing, drumming, speaking">
            </label>
            <label>Skills
                <input type="text" name="skills" value="{{ old('skills', implode(', ', $profile?->skills ?? [])) }}" placeholder="Design, coding, photography">
            </label>
            <label class="span-2">Where you'd like to serve
                <input type="text" name="ministry_interests" value="{{ old('ministry_interests', implode(', ', $profile?->ministry_interests ?? [])) }}" placeholder="Worship team, outreach, Sunday school, media">
            </label>
        </div>
    </section>

    <section class="card form-block tabset-panel" role="tabpanel" id="profile-privacy" aria-labelledby="profile-tab-privacy">
        <h2>Privacy</h2>
        <label class="privacy-option">
            <input class="switch" type="checkbox" name="profile_public" value="1" @checked(old('profile_public', $profile?->profile_public))>
            <span><strong>Let other members see my profile</strong><small>Only in approved community spaces. Your birthday, email and phone always stay private.</small></span>
        </label>
    </section>
    </div>

    <div class="form-actions">
        <a class="btn btn-ghost" href="{{ route('youth.dashboard') }}">Cancel</a>
        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Save changes</button>
    </div>
</form>

<style>
.profile-hero{display:flex;align-items:center;gap:16px;margin:2px 0 18px}
.big-avatar{width:60px;height:60px;flex:0 0 60px;border-radius:20px;display:grid;place-items:center;background:var(--sun);border:var(--bw) solid var(--edge);font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:var(--ink);transform:rotate(calc(-5deg * var(--tilt)));box-shadow:var(--pop)}
.profile-hero h1{font-size:clamp(1.6rem,3.6vw,2.3rem);margin:0 0 4px;letter-spacing:-.035em;line-height:1.05}
.profile-hero p{margin:0;color:var(--ink-soft);font-weight:600}
.profile-form{max-width:52rem;display:grid;gap:18px}
.profile-form [data-tabset]{min-width:0}
.form-block{padding:18px}
.form-block h2{font-size:1.1rem;margin:0 0 4px}
.form-block>p{margin:0 0 14px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px}
.form-grid label{display:flex;flex-direction:column;gap:6px;font-weight:700;color:var(--ink)}
.form-grid small{font-weight:500;color:var(--muted)}
.form-grid input,.form-grid select{border:1.5px solid var(--line);border-radius:12px!important;background:#fff;color:var(--ink);padding-inline:12px}
.form-grid input:focus,.form-grid select:focus{border-color:var(--edge-strong);box-shadow:0 0 0 4px var(--sun-soft);outline:none}
.span-2{grid-column:span 2}
.privacy-option{display:flex;gap:14px;align-items:center;margin-top:12px;padding:16px;border-radius:16px;background:var(--surface-soft);cursor:pointer}
.privacy-option strong,.privacy-option small{display:block}
.privacy-option small{color:var(--muted);margin-top:2px}
.switch{appearance:none;-webkit-appearance:none;flex:0 0 auto;width:52px!important;height:30px!important;min-height:0!important;margin:0;padding:0!important;border-radius:999px;border:var(--bw) solid var(--edge);background:var(--paper-2);position:relative;cursor:pointer;transition:background .2s ease}
.switch::after{content:'';position:absolute;top:3px;left:3px;width:21px;height:21px;border-radius:50%;background:#fff;border:var(--bw) solid var(--edge);transition:transform .2s cubic-bezier(.22,1,.36,1)}
.switch:checked{background:var(--leaf)}
.switch:checked::after{transform:translateX(22px)}
.switch:focus-visible{outline:3px solid var(--sun);outline-offset:2px}
.form-actions{display:flex;justify-content:flex-end;gap:10px}
.form-errors{max-width:52rem;margin:0 0 18px;padding:14px 18px;border:1.5px solid var(--coral);background:var(--coral-soft);color:var(--ink);border-radius:16px}
.form-errors ul{margin:6px 0 0}
@media(max-width:700px){.form-grid{grid-template-columns:1fr}.span-2{grid-column:auto}.profile-hero{gap:14px}.big-avatar{width:64px;height:64px;flex-basis:64px;font-size:1.5rem;border-radius:20px}.form-actions .btn{flex:1}}
</style>
@include('partials.tabs')
@endsection
