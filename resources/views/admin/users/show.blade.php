@extends('admin.layout')
@section('title', $isSelf ? 'My profile' : $user->name)

@section('content')
@php
    $diocese = $user->diocese?->name ?? $user->youthProfile?->diocese?->name;
    $activeRoles = $user->organisationRoles->where('is_active', true);
@endphp

<div class="page-head">
    <div>
        <h1><i class="fas fa-id-badge" aria-hidden="true"></i> {{ $isSelf ? 'My profile' : 'User profile' }}</h1>
        <p>{{ $isSelf ? 'Update your details, photo and password.' : 'View and update this account’s details and photo.' }}</p>
    </div>
    @unless($isSelf)<a class="btn btn-light" href="{{ route('admin.users.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> All users</a>@endunless
</div>

<div class="profile-layout">
    <aside class="card profile-summary">
        @if($user->photoUrl())
            <img class="profile-photo" src="{{ $user->photoUrl() }}" alt="{{ $user->name }}’s profile photo">
        @else
            <span class="profile-photo profile-photo-initials" aria-hidden="true">{{ $user->initials() }}</span>
        @endif
        <h2>{{ $user->name }}</h2>
        <p class="profile-muted">{{ $user->email }}</p>
        <dl class="profile-facts">
            <div><dt>Phone</dt><dd>{{ $user->phone ?: '—' }}</dd></div>
            <div><dt>Location</dt><dd>{{ $user->location ?: '—' }}</dd></div>
            <div><dt>Diocese</dt><dd>{{ $diocese ?: '—' }}</dd></div>
            <div><dt>Joined</dt><dd>{{ $user->created_at?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt>Roles</dt><dd>
                @forelse($activeRoles as $assignment)
                    <span class="badge">{{ \App\Support\AssignableRoles::label($assignment->role) }}@if($assignment->organisationUnit) · {{ $assignment->organisationUnit->name }}@endif</span>
                @empty
                    Regular user
                @endforelse
            </dd></div>
        </dl>
    </aside>

    <section class="card">
        <h2 class="profile-section-title">Edit details</h2>
        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-grid">
                <label class="span-2" for="profile-photo">Profile photo
                    <input id="profile-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" @error('photo') aria-invalid="true" aria-describedby="profile-photo-error" @enderror>
                    <span class="profile-muted">JPG, PNG or WebP, up to 2 MB.</span>
                    @error('photo')<span class="user-field-error" id="profile-photo-error">{{ $message }}</span>@enderror
                </label>
                @if($user->profile_photo)
                    <label class="checkbox span-2"><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label>
                @endif
                @foreach([
                    ['name', 'Name', 'text', 'name', true],
                    ['email', 'Email', 'email', 'email', true],
                    ['phone', 'Phone', 'tel', 'tel', false],
                    ['location', 'Location', 'text', 'address-level2', false],
                ] as [$field, $label, $type, $autocomplete, $required])
                    <label for="profile-{{ $field }}">{{ $label }}
                        <input id="profile-{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $user->{$field}) }}" maxlength="{{ $field === 'phone' ? 40 : 255 }}" autocomplete="{{ $autocomplete }}" @required($required) @error($field) aria-invalid="true" aria-describedby="profile-{{ $field }}-error" @enderror>
                        @error($field)<span class="user-field-error" id="profile-{{ $field }}-error">{{ $message }}</span>@enderror
                    </label>
                @endforeach
                <label for="profile-diocese">Diocese
                    <select id="profile-diocese" name="diocese_id" @error('diocese_id') aria-invalid="true" aria-describedby="profile-diocese-error" @enderror>
                        <option value="">No diocese</option>
                        @foreach($dioceses as $option)
                            <option value="{{ $option->id }}" @selected((string) old('diocese_id', $user->diocese_id) === (string) $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('diocese_id')<span class="user-field-error" id="profile-diocese-error">{{ $message }}</span>@enderror
                </label>
            </div>

            <h2 class="profile-section-title profile-password-title">{{ $isSelf ? 'Change password' : 'Set a new password' }}</h2>
            <div class="form-grid">
                @if($isSelf)
                    <label class="span-2" for="profile-current-password">Current password
                        <input id="profile-current-password" name="current_password" type="password" autocomplete="current-password" @error('current_password') aria-invalid="true" aria-describedby="profile-current-password-error" @enderror>
                        @error('current_password')<span class="user-field-error" id="profile-current-password-error">{{ $message }}</span>@enderror
                    </label>
                @endif
                <label for="profile-password">New password
                    <input id="profile-password" name="password" type="password" autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="profile-password-error" @enderror>
                    @error('password')<span class="user-field-error" id="profile-password-error">{{ $message }}</span>@enderror
                </label>
                <label for="profile-password-confirmation">Confirm new password
                    <input id="profile-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                </label>
            </div>
            <p class="user-password-help">Leave the password fields blank to keep the current password.</p>

            <div class="modal-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Save changes</button></div>
        </form>
    </section>
</div>
@endsection

@push('styles')
<style>
    .profile-layout{display:grid;grid-template-columns:300px minmax(0,1fr);gap:16px;align-items:start}
    .profile-summary{text-align:center}.profile-summary h2{margin:12px 0 4px;font-size:1.25rem}
    .profile-photo{display:block;width:112px;height:112px;margin:0 auto;border-radius:50%;object-fit:cover;border:3px solid var(--primary-light)}
    .profile-photo-initials{display:grid;place-items:center;background:var(--primary-light);color:var(--primary);font-size:2.5rem;font-weight:800}
    .profile-muted{display:block;color:var(--muted);font-size:0.8125rem;margin:4px 0 0;font-weight:400;overflow-wrap:anywhere}
    .profile-facts{text-align:left;margin:18px 0 0;display:grid;gap:10px}.profile-facts div{border-top:1px solid var(--border);padding-top:10px}
    .profile-facts dt{font-size:0.6875rem;text-transform:uppercase;font-weight:800;color:var(--muted)}.profile-facts dd{margin:3px 0 0;overflow-wrap:anywhere}.profile-facts .badge{margin:2px 4px 2px 0}
    .profile-section-title{font-size:1.0625rem;margin:0 0 14px}.profile-password-title{margin-top:24px}
    .user-field-error{display:block;color:var(--danger);font-size:0.8125rem;margin-top:5px}.user-password-help{color:var(--muted);font-size:0.875rem;margin:10px 0 0}
    @media(max-width:900px){.profile-layout{grid-template-columns:1fr}}
</style>
@endpush
