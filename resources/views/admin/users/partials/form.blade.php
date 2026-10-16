<div class="form-grid">
    <label for="{{ $formId }}-name">Name
        <input id="{{ $formId }}-name" name="name" value="{{ $restoreInput ? old('name') : $editingUser?->name }}" maxlength="255" required autocomplete="name" autofocus @if($restoreInput && $errors->has('name')) aria-invalid="true" aria-describedby="{{ $formId }}-name-error" @endif>
        @if($restoreInput) @error('name')<span class="user-field-error" id="{{ $formId }}-name-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-email">Email
        <input id="{{ $formId }}-email" name="email" type="email" value="{{ $restoreInput ? old('email') : $editingUser?->email }}" maxlength="255" required autocomplete="email" @if($restoreInput && $errors->has('email')) aria-invalid="true" aria-describedby="{{ $formId }}-email-error" @endif>
        @if($restoreInput) @error('email')<span class="user-field-error" id="{{ $formId }}-email-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-password">{{ $editingUser ? 'New password (optional)' : 'Password' }}
        <input id="{{ $formId }}-password" name="password" type="password" @required(!$editingUser) autocomplete="new-password" @if($restoreInput && $errors->has('password')) aria-invalid="true" aria-describedby="{{ $formId }}-password-error" @endif>
        @if($restoreInput) @error('password')<span class="user-field-error" id="{{ $formId }}-password-error">{{ $message }}</span>@enderror @endif
    </label>
    <label for="{{ $formId }}-password-confirmation">{{ $editingUser ? 'Confirm new password' : 'Confirm password' }}
        <input id="{{ $formId }}-password-confirmation" name="password_confirmation" type="password" @required(!$editingUser) autocomplete="new-password">
    </label>
</div>
@if($editingUser)<p class="user-password-help">Leave both password fields blank to keep the current password.</p>@endif
