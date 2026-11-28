@php
    $fieldValue = fn (string $field) => $restoreInput ? old($field) : $editingUser?->{$field};
    $selectedDiocese = (string) $fieldValue('diocese_id');
@endphp
<div class="form-grid">
    <label for="{{ $formId }}-name">Name
        <input id="{{ $formId }}-name" name="name" value="{{ $fieldValue('name') }}" maxlength="255" required autocomplete="name" autofocus @if($restoreInput && $errors->has('name')) aria-invalid="true" aria-describedby="{{ $formId }}-name-error" @endif>
        @if($restoreInput) @error('name')<span class="user-field-error" id="{{ $formId }}-name-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-email">Email
        <input id="{{ $formId }}-email" name="email" type="email" value="{{ $fieldValue('email') }}" maxlength="255" required autocomplete="email" @if($restoreInput && $errors->has('email')) aria-invalid="true" aria-describedby="{{ $formId }}-email-error" @endif>
        @if($restoreInput) @error('email')<span class="user-field-error" id="{{ $formId }}-email-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-phone">Phone
        <input id="{{ $formId }}-phone" name="phone" type="tel" value="{{ $fieldValue('phone') }}" maxlength="40" placeholder="+256 772 123456" autocomplete="tel" @if($restoreInput && $errors->has('phone')) aria-invalid="true" aria-describedby="{{ $formId }}-phone-error" @endif>
        @if($restoreInput) @error('phone')<span class="user-field-error" id="{{ $formId }}-phone-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-location">Location
        <input id="{{ $formId }}-location" name="location" value="{{ $fieldValue('location') }}" maxlength="255" placeholder="Town or parish" autocomplete="address-level2" @if($restoreInput && $errors->has('location')) aria-invalid="true" aria-describedby="{{ $formId }}-location-error" @endif>
        @if($restoreInput) @error('location')<span class="user-field-error" id="{{ $formId }}-location-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-diocese">Diocese
        <select id="{{ $formId }}-diocese" name="diocese_id" @if($restoreInput && $errors->has('diocese_id')) aria-invalid="true" aria-describedby="{{ $formId }}-diocese-error" @endif>
            <option value="">No diocese</option>
            @foreach($dioceses as $diocese)
                <option value="{{ $diocese->id }}" @selected($selectedDiocese === (string) $diocese->id)>{{ $diocese->name }}</option>
            @endforeach
        </select>
        @if($restoreInput) @error('diocese_id')<span class="user-field-error" id="{{ $formId }}-diocese-error">{{ $message }}</span>@enderror @endif
    </label>
    @unless($editingUser)
        <label for="{{ $formId }}-role">Role
            <select id="{{ $formId }}-role" name="role" @if($restoreInput && $errors->has('role')) aria-invalid="true" aria-describedby="{{ $formId }}-role-error" @endif>
                <option value="">No admin role (regular user)</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected($restoreInput && old('role') === $role)>{{ \App\Support\AssignableRoles::label($role) }}</option>
                @endforeach
            </select>
            @if($restoreInput) @error('role')<span class="user-field-error" id="{{ $formId }}-role-error">{{ $message }}</span>@enderror @endif
        </label>
    @endunless
    <label for="{{ $formId }}-password">{{ $editingUser ? 'New password (optional)' : 'Password' }}
        <input id="{{ $formId }}-password" name="password" type="password" @required(!$editingUser) autocomplete="new-password" @if($restoreInput && $errors->has('password')) aria-invalid="true" aria-describedby="{{ $formId }}-password-error" @endif>
        @if($restoreInput) @error('password')<span class="user-field-error" id="{{ $formId }}-password-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-password-confirmation">{{ $editingUser ? 'Confirm new password' : 'Confirm password' }}
        <input id="{{ $formId }}-password-confirmation" name="password_confirmation" type="password" @required(!$editingUser) autocomplete="new-password">
    </label>
</div>
@if($editingUser)
    <p class="user-password-help">Leave both password fields blank to keep the current password. Manage roles on the <a href="{{ route('admin.user-organisation-roles.index') }}">role assignments</a> page.</p>
@else
    <p class="user-password-help">Super Admin and Provincial Admin apply platform-wide; every other role is limited to the chosen diocese.</p>
@endif
