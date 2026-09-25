@extends('public.layout')
@section('title', 'My Profile | COU Youth')
@section('content')
<section class="hero"><span class="section-kicker">MY PROFILE</span><h1>Youth profile</h1><p>Keep your information up to date so your learning, groups and opportunities can be more relevant to you.</p></section>

@if($errors->any())<div class="form-errors" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form class="card profile-form" method="POST" action="{{ route('youth.profile.update') }}">
@csrf @method('PUT')
<div class="form-grid">
<label>Date of birth<input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required></label>
<label>Age category<select name="age_category" required><option value="">Select category</option>@foreach(['teen'=>'Teen','youth'=>'Youth','young_adult'=>'Young adult'] as $value=>$label)<option value="{{ $value }}" @selected(old('age_category',$profile?->age_category)===$value)>{{ $label }}</option>@endforeach</select></label>
<label>Church / organisation unit<select name="organisation_unit_id"><option value="">Select church / unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string)old('organisation_unit_id',$profile?->organisation_unit_id)===(string)$unit->id)>{{ $unit->name }}</option>@endforeach</select></label>
<label>School / institution<input type="text" name="school_institution" maxlength="190" value="{{ old('school_institution',$profile?->school_institution) }}" placeholder="School, university, workplace or institution"></label>
<label class="span-2">Interests<input type="text" name="interests" value="{{ old('interests', implode(', ', $profile?->interests ?? [])) }}" placeholder="Music, entrepreneurship, technology, sports"></label>
<label class="span-2">Talents<input type="text" name="talents" value="{{ old('talents', implode(', ', $profile?->talents ?? [])) }}" placeholder="Singing, leadership, public speaking"></label>
<label class="span-2">Skills<input type="text" name="skills" value="{{ old('skills', implode(', ', $profile?->skills ?? [])) }}" placeholder="Coding, design, photography, farming"></label>
<label class="span-2">Ministry interests<input type="text" name="ministry_interests" value="{{ old('ministry_interests', implode(', ', $profile?->ministry_interests ?? [])) }}" placeholder="Worship, outreach, children, media"></label>
</div>
<label class="privacy-option"><input type="checkbox" name="profile_public" value="1" @checked(old('profile_public',$profile?->profile_public))><span><strong>Allow my profile to be visible in approved youth community features</strong><small>Your account and sensitive information remain protected.</small></span></label>
<div class="form-actions"><a class="btn" href="{{ route('youth.dashboard') }}">Back to dashboard</a><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save profile</button></div>
</form>
<style>
.section-kicker{font-size:.74rem;font-weight:900;letter-spacing:.12em;color:var(--primary)}.profile-form{max-width:900px;margin:auto}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.form-grid label{font-size:.84rem;font-weight:800;color:#344054}.form-grid input,.form-grid select{width:100%;margin-top:6px;border:1px solid #d0d5dd;border-radius:10px;padding:12px;background:#fff;color:var(--text)}.span-2{grid-column:span 2}.privacy-option{display:flex;gap:12px;align-items:flex-start;margin:20px 0;padding:14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-soft)}.privacy-option input{margin-top:5px}.privacy-option strong,.privacy-option small{display:block}.privacy-option small{color:var(--muted);margin-top:3px}.form-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap}.form-errors{max-width:900px;margin:0 auto 18px;padding:14px 16px;border:1px solid #f5b7b1;background:#fff1f0;color:#8a1c16;border-radius:10px}.form-errors ul{margin-bottom:0}@media(max-width:700px){.form-grid{grid-template-columns:1fr}.span-2{grid-column:auto}}
</style>
@endsection
